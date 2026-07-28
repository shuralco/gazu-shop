<?php

namespace Modules\PricingMarkup;

use App\Models\CustomerGroup;
use App\Support\Hooks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Modules\PricingMarkup\Filament\CustomerGroupFields;
use Modules\PricingMarkup\Services\MarkupPricing;

/**
 * Модуль pricing_markup.
 *
 * Ядро нічого не знає про націнку — воно лише прогоняє базову ціну через
 * фільтр `pricing.base_price`. Модуль підписується на цей фільтр і додає %
 * націнки групи. Вимкнули модуль → фільтра немає → ціна лишається базовою.
 */
class PricingMarkupServiceProvider extends ServiceProvider
{
    /** Подія-фільтр, на яку підписуємось. */
    private const FILTER = 'pricing.base_price';

    public function boot(): void
    {
        // Дві реєстрації з РІЗНИМ часом життя, тому окремо:
        //  - Hooks::$filters статичний і переживає перестворення застосунку;
        //  - слухачі моделі живуть у диспетчері подій, який створюється заново.
        $this->registerPricingFilter();
        $this->registerAdminFields();
        $this->guardSingleDefaultGroup();
        $this->warnIfColumnNotFillable();
    }

    /**
     * Єдина умова, яку модуль не може виконати сам: `markup_percentage` мусить
     * бути дозволений для масового заповнення в моделі CustomerGroup (вона
     * належить модулю wholesale). Інакше адмінка мовчки не збереже відсоток.
     * Пишемо в лог один раз на процес, щоб install на старішому движку
     * не виглядав як «нічого не працює без причини».
     */
    private function warnIfColumnNotFillable(): void
    {
        static $checked = false;
        if ($checked || ! class_exists(CustomerGroup::class)) {
            return;
        }
        $checked = true;

        if (! in_array('markup_percentage', (new CustomerGroup)->getFillable(), true)) {
            \Illuminate\Support\Facades\Log::warning(
                '[pricing_markup] Додайте "markup_percentage" у $fillable моделі '
                .CustomerGroup::class.' — інакше відсоток націнки не збережеться з адмінки.'
            );
        }
    }

    /**
     * Поля націнки в картці групи клієнтів — через точки розширення модуля
     * wholesale. Так модуль ставиться в чужий магазин без правки його файлів,
     * а вимкнення модуля прибирає поля з адмінки саме собою.
     */
    private function registerAdminFields(): void
    {
        foreach ([
            'wholesale.customer_group.form' => [CustomerGroupFields::class, 'formSchema'],
            'wholesale.customer_group.columns' => [CustomerGroupFields::class, 'tableColumns'],
        ] as $event => $factory) {
            if (in_array($event, Hooks::eventsBySource('pricing_markup'), true)) {
                continue;
            }

            Hooks::addFilter($event, function (mixed $items) use ($factory) {
                if (! MarkupPricing::enabled()) {
                    return $items;
                }

                return array_merge((array) $items, $factory());
            }, 10, 'pricing_markup');
        }
    }

    private function registerPricingFilter(): void
    {
        // Подвійна підписка означала б, що націнка застосовується КАСКАДОМ
        // (1000 → 1500 → 2250…), тож підписуємось лише один раз на процес.
        if (in_array(self::FILTER, Hooks::eventsBySource('pricing_markup'), true)) {
            return;
        }

        // Базова ціна → ціна для клієнта. Викликається з
        // Product::priceViewForUser (єдина точка ціноутворення).
        Hooks::addFilter(self::FILTER, function (mixed $base, mixed $user = null) {
            if (! MarkupPricing::enabled()) {
                return $base;
            }

            return MarkupPricing::apply((float) $base, $user);
        }, 10, 'pricing_markup');
    }

    /**
     * «Стандартна група» мусить бути рівно одна: щойно якусь позначили
     * стандартною — з решти позначку знімаємо. Інакше гість отримував би
     * націнку випадкової групи (яку першою віддала БД).
     */
    private function guardSingleDefaultGroup(): void
    {
        if (! class_exists(CustomerGroup::class)) {
            return;
        }

        CustomerGroup::saved(function (CustomerGroup $group) {
            if (! $group->is_default) {
                return;
            }

            DB::table('customer_groups')
                ->where('id', '!=', $group->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            MarkupPricing::flush();
        });

        CustomerGroup::deleted(fn () => MarkupPricing::flush());
    }
}
