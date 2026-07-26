<?php

namespace App\Filament\Pages;

use App\Models\CustomerGroup;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Modules\PricingMarkup\Services\MarkupPricing;

/**
 * Керування націнками (модуль pricing_markup).
 *
 * Одна сторінка, де видно все одразу: список груп із % націнки, яка з них
 * стандартна (для неавторизованих), і живий приклад — скільки коштуватиме
 * товар кожній групі. Групи можна додавати/видаляти тут же.
 */
class PricingMarkupSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Націнки по групах';

    protected static ?string $title = 'Націнки по групах клієнтів';

    protected static ?string $navigationGroup = 'Продажі';

    protected static ?int $navigationSort = 25;

    protected static string $view = 'pricing_markup::pages.pricing-markup-settings';

    public ?array $data = [];

    /** Базова ціна для прикладу-калькулятора (не зберігається). */
    public float $sample = 1000.0;

    public function mount(): void
    {
        $this->form->fill([
            'groups' => CustomerGroup::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (CustomerGroup $g) => [
                    'id' => $g->id,
                    'display_name' => $g->display_name ?: $g->name,
                    'markup_percentage' => (float) $g->markup_percentage,
                    'is_default' => (bool) $g->is_default,
                    'is_active' => (bool) $g->is_active,
                ])
                ->all(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Групи клієнтів і націнка')
                    ->description('Ціна в картці товару — базова (ваша закупка). Клієнт бачить базову + % націнки своєї групи. Неавторизований або клієнт без групи отримує ту групу, що позначена «стандартна».')
                    ->schema([
                        Forms\Components\Repeater::make('groups')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('display_name')
                                    ->label('Назва групи')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(4),
                                Forms\Components\TextInput::make('markup_percentage')
                                    ->label('Націнка, %')
                                    ->helperText('Напр. 35 = +35 %. Можна 0 (ціна = базова) або відʼємне значення (дешевше за базову).')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(-100)
                                    ->maxValue(1000)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->columnSpan(3),
                                Forms\Components\Toggle::make('is_default')
                                    ->label('Стандартна')
                                    ->helperText('Для неавторизованих. Може бути лише одна.')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function (bool $state, Forms\Set $set, Forms\Get $get, $component) {
                                        if (! $state) {
                                            return;
                                        }
                                        // Знімаємо позначку з решти рядків одразу у формі,
                                        // щоб було видно: стандартна завжди одна.
                                        $path = $component->getStatePath();
                                        $rows = $get('../../groups') ?? [];
                                        foreach ($rows as $key => $row) {
                                            if (! str_contains($path, ".{$key}.")) {
                                                $set("../../groups.{$key}.is_default", false);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активна')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(2),
                                Forms\Components\Hidden::make('id'),
                            ])
                            ->columns(11)
                            ->addActionLabel('Додати групу')
                            ->reorderable(false)
                            ->itemLabel(fn (array $state): ?string => ($state['display_name'] ?? 'Група')
                                .'  ·  '.(($state['markup_percentage'] ?? 0) > 0 ? '+' : '').($state['markup_percentage'] ?? 0).' %'
                                .(! empty($state['is_default']) ? '  ·  стандартна' : ''))
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                            ),
                    ]),
            ])
            ->statePath('data');
    }

    /** Приклад: скільки коштуватиме товар кожній групі. Рахує той самий сервіс. */
    public function previewRows(): array
    {
        $base = max(0, (float) $this->sample);
        $rows = [];

        foreach (($this->data['groups'] ?? []) as $row) {
            if (! ($row['is_active'] ?? true)) {
                continue;
            }
            $percent = (float) ($row['markup_percentage'] ?? 0);
            $rows[] = [
                'name' => $row['display_name'] ?? '—',
                'percent' => $percent,
                'price' => round(max(0, $base * (1 + $percent / 100)), 2),
                'is_default' => (bool) ($row['is_default'] ?? false),
            ];
        }

        return $rows;
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $rows = $state['groups'] ?? [];

        if (empty($rows)) {
            Notification::make()->danger()
                ->title('Потрібна хоча б одна група')
                ->body('Інакше неавторизованим покупцям нічого не показувати.')
                ->send();

            return;
        }

        $active = array_filter($rows, fn ($r) => ! empty($r['is_active']));
        if (empty($active)) {
            Notification::make()->danger()
                ->title('Хоча б одна група має бути активною')
                ->send();

            return;
        }

        // Стандартна — рівно одна, і обовʼязково активна.
        $defaultKey = null;
        foreach ($rows as $key => $row) {
            if (! empty($row['is_default']) && ! empty($row['is_active'])) {
                $defaultKey = $key;
                break;
            }
        }
        if ($defaultKey === null) {
            $defaultKey = array_key_first($active);
            Notification::make()->warning()
                ->title('Стандартну групу вибрано автоматично')
                ->body('Не було позначено жодної активної стандартної групи.')
                ->send();
        }

        $keptIds = [];
        $sort = 0;

        DB::transaction(function () use ($rows, $defaultKey, &$keptIds, &$sort) {
            foreach ($rows as $key => $row) {
                $payload = [
                    'display_name' => (string) $row['display_name'],
                    'markup_percentage' => (float) ($row['markup_percentage'] ?? 0),
                    'is_default' => $key === $defaultKey,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'sort_order' => $sort++,
                ];

                if (! empty($row['id']) && ($group = CustomerGroup::find($row['id']))) {
                    $group->update($payload);
                } else {
                    // `name` — технічний ключ групи, лишаємо унікальним.
                    $group = CustomerGroup::create($payload + [
                        'name' => \Str::slug($payload['display_name']) ?: 'group-'.uniqid(),
                    ]);
                }

                $keptIds[] = $group->id;
            }

            // Видалені рядки: групу можна прибрати лише якщо в ній нема клієнтів.
            $removed = CustomerGroup::query()->whereNotIn('id', $keptIds)->get();
            foreach ($removed as $group) {
                if ($group->users()->exists()) {
                    Notification::make()->warning()
                        ->title("Групу «{$group->display_name}» не видалено")
                        ->body('У ній є клієнти — спершу перенесіть їх в іншу групу.')
                        ->send();

                    continue;
                }
                $group->delete();
            }
        });

        MarkupPricing::flush();
        \App\Models\Filter::flushCatalogCache();

        $this->mount();

        Notification::make()->success()
            ->title('Націнки збережено')
            ->body('Ціни на сайті оновляться одразу.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Зберегти')
                ->submit('save'),
        ];
    }
}
