<?php

namespace App\Filament\Pages;

use App\Models\LayoutBlock;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Layout Builder — OpenCart-стиль призначення блоків у зони storefront.
 *
 * Один Repeater = весь список призначень блок→зона. Зберігається у
 * таблицю layout_blocks. Рендер виконує LayoutBuilderServiceProvider через
 * @hookAction('layout.*') у темі.
 */
class LayoutBuilderPage extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Зони layout';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Конструктор зон layout (OpenCart-стиль)';

    protected static ?string $slug = 'layout-builder';

    protected static string $view = 'layout_builder::page';

    public ?array $data = [];

    public function mount(): void
    {
        $blocks = LayoutBlock::query()
            ->orderBy('zone')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (LayoutBlock $b) => [
                'id' => $b->id,
                'zone' => $b->zone,
                'type' => $b->type,
                'title' => $b->title,
                'content' => $b->content,
                'config' => $b->config ?? [],
                'sort_order' => $b->sort_order,
                'is_active' => (bool) $b->is_active,
            ])
            ->toArray();

        $this->form->fill(['blocks' => $blocks]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Блоки у зонах storefront')
                    ->description('Призначте блоки (HTML / банер / товари) у іменовані зони. Зони рендеряться через @hookAction у темі. Порядок усередині зони — за «Сортуванням» (ASC).')
                    ->schema([
                        Repeater::make('blocks')
                            ->label('Призначення блок → зона')
                            ->schema([
                                Select::make('zone')
                                    ->label('Зона')
                                    ->options(LayoutBlock::ZONES)
                                    ->required()
                                    ->live()
                                    ->native(false),
                                Select::make('type')
                                    ->label('Тип блоку')
                                    ->options(LayoutBlock::TYPES)
                                    ->default('html')
                                    ->required()
                                    ->live()
                                    ->native(false),
                                TextInput::make('title')
                                    ->label('Заголовок / назва')
                                    ->maxLength(255),
                                RichEditor::make('content')
                                    ->label('HTML-вміст')
                                    ->visible(fn (callable $get) => $get('type') === 'html')
                                    ->columnSpanFull(),
                                KeyValue::make('config')
                                    ->label('Налаштування')
                                    ->keyLabel('Ключ')
                                    ->valueLabel('Значення')
                                    ->helperText(function (callable $get) {
                                        $hint = match ($get('type')) {
                                            'banner' => 'image_url, link_url, alt',
                                            'featured' => 'limit (1-12), source (new|promo|latest)',
                                            default => 'Додаткові параметри блоку',
                                        };
                                        if (str_starts_with((string) $get('zone'), 'page.')) {
                                            $hint .= ' · pages — обмежити сторінками: "about-company, dostavka" (порожньо = всі CMS-сторінки)';
                                        }

                                        return $hint;
                                    })
                                    ->visible(fn (callable $get) => in_array($get('type'), ['banner', 'featured'], true)
                                        || str_starts_with((string) $get('zone'), 'page.'))
                                    ->columnSpanFull(),
                                TextInput::make('sort_order')
                                    ->label('Сортування')
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Активний')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => trim(
                                ($state['zone'] ?? '?').' · '.($state['type'] ?? 'html')
                                .(filled($state['title'] ?? null) ? ' · '.$state['title'] : '')
                            ))
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->addActionLabel('Додати блок')
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $rows = $state['blocks'] ?? [];

        $keepIds = [];

        foreach ($rows as $i => $row) {
            $payload = [
                'zone' => $row['zone'] ?? 'home.top',
                'type' => $row['type'] ?? 'html',
                'title' => $row['title'] ?? null,
                'content' => $row['content'] ?? null,
                'config' => is_array($row['config'] ?? null) ? $row['config'] : [],
                'sort_order' => (int) ($row['sort_order'] ?? $i),
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];

            if (! empty($row['id']) && LayoutBlock::whereKey($row['id'])->exists()) {
                LayoutBlock::whereKey($row['id'])->update($payload);
                $keepIds[] = (int) $row['id'];
            } else {
                $created = LayoutBlock::create($payload);
                $keepIds[] = $created->id;
            }
        }

        // Видалити блоки, прибрані з репітера.
        LayoutBlock::query()
            ->when(! empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->when(empty($keepIds), fn ($q) => $q)
            ->delete();

        // Скинути storefront-кеш, щоб зміни одразу побачились.
        try {
            \Illuminate\Support\Facades\Artisan::call('responsecache:clear');
        } catch (\Throwable $e) {
            // no-op
        }

        $this->mount();

        Notification::make()
            ->title('Збережено')
            ->body('Блоки оновлено. Кеш storefront скинуто.')
            ->success()
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
