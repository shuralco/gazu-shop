<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Support\DashboardMetrics;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Налаштування дашборду: видимість карток, порядок (drag, серверно для всіх
 * адмінів), групи, період за замовчуванням. Зберігає в DisplaySetting; дашборд
 * читає через DashboardMetrics::arrangedGroups().
 */
class DashboardSettings extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Налаштування дашборду';

    protected static ?string $title = 'Налаштування дашборду';

    protected static ?string $navigationGroup = 'Система';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.dashboard-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $cfg = DisplaySetting::get('dashboard_cards');
        $cfg = is_array($cfg) ? $cfg : [];
        $metrics = collect(DashboardMetrics::all())->keyBy('id');

        // Список усіх карток у поточному (конфіг) порядку, по групах.
        $items = [];
        foreach (DashboardMetrics::GROUPS as $gkey => [$glabel, $ids]) {
            foreach ($ids as $id) {
                if (! $metrics->has($id)) {
                    continue;
                }
                $items[] = [
                    'id' => $id,
                    'label' => $metrics->get($id)['label'] ?? $id,
                    'group' => $glabel,
                    'visible' => ! isset($cfg[$id]['visible']) || (bool) $cfg[$id]['visible'],
                    '_order' => $cfg[$id]['order'] ?? array_search($id, $ids, true),
                ];
            }
        }
        usort($items, fn ($a, $b) => ($a['_order'] <=> $b['_order']));

        $this->data = [
            'default_period' => DisplaySetting::get('dashboard_default_period') ?: '7d',
            'cards' => array_map(fn ($i) => [
                'id' => $i['id'], 'label' => $i['label'], 'group' => $i['group'], 'visible' => $i['visible'],
            ], $items),
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('default_period')
                ->label('Період метрик за замовчуванням')
                ->options(['today' => 'Сьогодні', '7d' => '7 днів', '30d' => '30 днів'])
                ->native(false)
                ->helperText('Використовується картками/віджетами, що залежать від періоду.'),

            Forms\Components\Repeater::make('cards')
                ->label('Картки дашборду')
                ->reorderable()->reorderableWithButtons()
                ->addable(false)->deletable(false)
                ->itemLabel(fn (array $state): ?string => ($state['label'] ?? '').' · '.($state['group'] ?? ''))
                ->collapsible()->collapsed()
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('label'),
                    Forms\Components\Hidden::make('group'),
                    Forms\Components\Toggle::make('visible')->label('Показувати на дашборді')->inline(false),
                ])
                ->columns(1)
                ->helperText('Перетягуйте, щоб задати порядок (для всіх адмінів). Вимкніть, щоб сховати картку.'),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $cards = [];
        foreach (array_values((array) ($data['cards'] ?? [])) as $i => $row) {
            $id = $row['id'] ?? null;
            if (! $id) {
                continue;
            }
            $cards[$id] = ['visible' => (bool) ($row['visible'] ?? true), 'order' => $i];
        }

        DisplaySetting::set('dashboard_cards', $cards, 'Налаштування карток дашборду');
        DisplaySetting::set('dashboard_default_period', (string) ($data['default_period'] ?? '7d'), 'Період дашборду за замовч.');
        DisplaySetting::flushSettingsCache();

        Notification::make()->title('Збережено')->body('Налаштування дашборду оновлено.')->success()->send();
    }
}
