<?php

namespace App\Filament\Pages;

use App\Models\Module;
use App\Models\Product;
use App\Support\ModuleManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

/**
 * Налаштування зв'язку товарів по характеристиках (аналог HPM PRO в OpenCart):
 * які характеристики групують товари-варіанти і як вони відображаються на
 * сторінці товару (кнопки / випадаючий список / картинки / картинка+кнопка),
 * показувати чи приховати. Зберігається в modules.settings (related_products).
 */
class VariantPickerSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    public const DISPLAY_OPTIONS = [
        'button' => 'Кнопки (pills)',
        'dropdown' => 'Випадаючий список',
        'image' => 'Картинки',
        'image_button' => 'Картинка + кнопка',
    ];

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $navigationLabel = 'Зв\'язки товарів';

    protected static ?string $title = 'Зв\'язки товарів по характеристиках';

    protected static string $view = 'related_products::filament.variant-picker-settings';

    protected static ?int $navigationSort = 95;

    public ?array $data = [];

    public function mount(): void
    {
        $module = module('related_products');

        $this->form->fill([
            'picker_enabled' => (bool) $module->setting('picker_enabled', true),
            'picker_characteristics' => array_values((array) $module->setting('picker_characteristics', [])),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Variant picker на сторінці товару')
                    ->description('Товари, зв\'язані між собою (тип «related»), групуються за характеристикою, що відрізняється — покупець перемикається між варіантами без пошуку. Тут обираєте, які характеристики беруть участь і як саме відображаються.')
                    ->schema([
                        Toggle::make('picker_enabled')
                            ->label('Показувати блок варіантів на сторінці товару')
                            ->helperText('Глобальний вимикач. Якщо вимкнено — блок прихований навіть для зв\'язаних товарів.'),

                        Repeater::make('picker_characteristics')
                            ->label('Характеристики')
                            ->helperText('Порожній список = показуються ВСІ характеристики, за якими товари відрізняються (поточна поведінка). Додайте записи, щоб показувати лише вибрані — у заданому порядку й вигляді.')
                            ->schema([
                                Grid::make(4)->schema([
                                    Select::make('spec_key')
                                        ->label('Характеристика')
                                        ->options(fn () => self::specKeyOptions())
                                        ->searchable()
                                        ->required(),
                                    TextInput::make('label')
                                        ->label('Підпис (опційно)')
                                        ->placeholder('Як у характеристиці'),
                                    Select::make('display')
                                        ->label('Вид відображення')
                                        ->options(self::DISPLAY_OPTIONS)
                                        ->default('button')
                                        ->required(),
                                    Toggle::make('show')
                                        ->label('Показувати')
                                        ->default(true)
                                        ->inline(false),
                                ]),
                            ])
                            ->itemLabel(fn (array $state) => ($state['label'] ?: ($state['spec_key'] ?? null)) ?: 'Характеристика')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    /** Унікальні ключі характеристик з products.specifications (вибірка). */
    public static function specKeyOptions(): array
    {
        $keys = [];
        Product::query()
            ->whereNotNull('specifications')
            ->where('is_active', true)
            ->limit(500)
            ->pluck('specifications')
            ->each(function ($specs) use (&$keys) {
                $specs = is_array($specs) ? $specs : (json_decode((string) $specs, true) ?: []);
                foreach (array_keys($specs) as $k) {
                    $keys[(string) $k] = (string) $k;
                }
            });
        ksort($keys);

        return $keys;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('auto_relate')
                ->label('🔗 Запустити авто-зв\'язування')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Авто-зв\'язати товари за характеристиками?')
                ->modalDescription('Пройде по всіх активних товарах із характеристиками і створить зв\'язки «related» між схожими (та сама категорія, спільні характеристики, 1-3 відмінності). Існуючі зв\'язки не перезаписуються.')
                ->action(function () {
                    try {
                        Artisan::call('products:auto-relate', ['--per-product' => (int) module('related_products')->setting('auto_relate_per_product', 12)]);
                        $tail = collect(explode("\n", trim(Artisan::output())))->last();

                        Notification::make()
                            ->title('Авто-зв\'язування завершено')
                            ->body($tail ?: 'Готово')
                            ->success()
                            ->duration(10000)
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Помилка авто-зв\'язування')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('save')
                ->label('💾 Зберегти')
                ->color('success')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $module = Module::query()->firstOrCreate(['key' => 'related_products']);
        $settings = is_array($module->settings) ? $module->settings : (json_decode((string) $module->settings, true) ?: []);
        $settings['picker_enabled'] = (bool) ($data['picker_enabled'] ?? true);
        $settings['picker_characteristics'] = array_values($data['picker_characteristics'] ?? []);
        $module->settings = $settings;
        $module->save();

        ModuleManager::clearCache();

        Notification::make()
            ->title('Налаштування збережено')
            ->body('Зв\'язки товарів оновлено — зміни вже на сторінках товарів.')
            ->success()
            ->send();
    }
}
