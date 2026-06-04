<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use Translatable;
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Каталог';
    protected static ?string $navigationLabel = 'Категорії';

    protected static ?string $modelLabel = 'Категорія';

    protected static ?string $pluralModelLabel = 'Категорії';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Основна інформація')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Назва')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('parent_id')
                                    ->label('Батьківська категорія')
                                    ->options(function ($record) {
                                        // Show all categories with their full breadcrumb path —
                                        // "Двигун", "Двигун → Фільтри" etc. — so admin sees
                                        // exactly where each option lives in the tree.
                                        // Translatable `title` field stored as JSON, so we
                                        // can't pluck() directly — would render as JSON string.
                                        $query = Category::query()
                                            ->where('is_active', true)
                                            ->with('parent.parent');
                                        // Prevent self-parenting on edit
                                        if ($record && $record->id) {
                                            $query->where('id', '!=', $record->id);
                                        }
                                        return $query->get()
                                            ->sortBy('full_path')
                                            ->mapWithKeys(fn ($c) => [$c->id => $c->full_path])
                                            ->prepend('— Коренева категорія —', '');
                                    })
                                    ->searchable()
                                    ->placeholder('— Коренева категорія —')
                                    ->dehydrateStateUsing(fn ($state) => $state === '' || $state === '0' ? null : $state)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Порядок сортування')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Активна')
                                    ->default(true)
                                    ->columnSpan(1),
                                Forms\Components\FileUpload::make('image')
                                    ->label('Зображення категорії')
                                    ->helperText('Показується на плитці категорії на головній. PNG/JPG/WEBP. Якщо не задано — використовується стандартне фото.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('categories')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Опис категорії')
                                    ->helperText('SEO-текст, що виводиться на сторінці категорії. Покращує релевантність у пошуку.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_url')
                                                ->label('URL')
                                                ->icon('heroicon-o-link')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Forms\Set $set, Forms\Get $get) {
                                                    $title = $get('title');
                                                    if ($title) {
                                                        $urlService = new \App\Services\UrlRouterService;
                                                        $set('slug', $urlService->generateSlug($title));

                                                        \Filament\Notifications\Notification::make()
                                                            ->title('URL згенеровано')
                                                            ->success()
                                                            ->send();
                                                    }
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_title')
                                                ->label('Title')
                                                ->icon('heroicon-o-document-text')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Forms\Set $set, Forms\Get $get) {
                                                    $title = $get('title');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву категорії')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $titleTemplate = \App\Models\DisplaySetting::get('seo_category_title_template', '%s | SimpleShop');
                                                    $seoTitle = sprintf($titleTemplate, $title);
                                                    $set('meta_title', $seoTitle);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Title згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_description')
                                                ->label('Description')
                                                ->icon('heroicon-o-document')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Forms\Set $set, Forms\Get $get) {
                                                    $title = $get('title');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву категорії')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $descriptionTemplate = \App\Models\DisplaySetting::get('seo_category_description_template', 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.');
                                                    $seoDescription = sprintf($descriptionTemplate, $title);
                                                    $set('meta_description', $seoDescription);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Description згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_keywords')
                                                ->label('Keywords')
                                                ->icon('heroicon-o-hashtag')
                                                ->size('sm')
                                                ->color('gray')
                                                ->action(function ($record, Forms\Set $set, Forms\Get $get) {
                                                    $title = $get('title');

                                                    if (! $title) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Помилка')
                                                            ->body('Спочатку введіть назву категорії')
                                                            ->danger()
                                                            ->send();

                                                        return;
                                                    }

                                                    $keywords = [
                                                        strtolower($title),
                                                        'купити '.strtolower($title),
                                                        strtolower($title).' ціна',
                                                        'каталог',
                                                        'україна',
                                                    ];
                                                    $set('meta_keywords', $keywords);

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Keywords згенеровано')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),
                                    ]),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generate_all_seo')
                                        ->label('Згенерувати всі SEO поля')
                                        ->icon('heroicon-o-bolt')
                                        ->color('primary')
                                        ->size('lg')
                                        ->action(function ($record, Forms\Set $set, Forms\Get $get) {
                                            $title = $get('title');

                                            if (! $title) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Помилка')
                                                    ->body('Спочатку введіть назву категорії')
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            // Генеруємо URL тільки якщо slug порожній
                                            $currentSlug = $get('slug');
                                            if (! $currentSlug) {
                                                $urlService = new \App\Services\UrlRouterService;
                                                $set('slug', $urlService->generateSlug($title));
                                            }

                                            // Генеруємо SEO title
                                            $titleTemplate = \App\Models\DisplaySetting::get('seo_category_title_template', '%s | SimpleShop');
                                            $seoTitle = sprintf($titleTemplate, $title);
                                            $set('meta_title', $seoTitle);

                                            // Генеруємо SEO description
                                            $descriptionTemplate = \App\Models\DisplaySetting::get('seo_category_description_template', 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.');
                                            $seoDescription = sprintf($descriptionTemplate, $title);
                                            $set('meta_description', $seoDescription);

                                            // Генеруємо keywords
                                            $keywords = [
                                                strtolower($title),
                                                'купити '.strtolower($title),
                                                strtolower($title).' ціна',
                                                'каталог',
                                                'україна',
                                            ];
                                            $set('meta_keywords', $keywords);

                                            \Filament\Notifications\Notification::make()
                                                ->title('Всі SEO поля згенеровано')
                                                ->body('URL, Title, Description та Keywords оновлено')
                                                ->success()
                                                ->send();
                                        }),
                                ])->fullWidth(),

                                Forms\Components\TextInput::make('slug')
                                    ->label('SEO URL (slug)')
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->helperText('SEO дружній URL для категорії. Автоматично генерується при збереженні якщо порожній. Це поле перекладається для кожної мови.'),

                                Forms\Components\TextInput::make('meta_title')
                                    ->label('SEO Заголовок')
                                    ->maxLength(60)
                                    ->helperText('Оптимальна довжина: 50-60 символів'),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('SEO Опис')
                                    ->maxLength(155)
                                    ->helperText('Оптимальна довжина: 150-160 символів')
                                    ->rows(3),

                                Forms\Components\TagsInput::make('meta_keywords')
                                    ->label('SEO Ключові слова')
                                    ->helperText('Натисніть Enter після введення кожного слова'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tree-style title — indent by depth + tree-branch glyph
                Tables\Columns\TextColumn::make('title')
                    ->label('Категорія')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($state, $record) {
                        $depth = $record->depth ?? 0;
                        $indent = str_repeat('  ', $depth);
                        $prefix = $depth > 0 ? '└─ ' : '';
                        $title = is_array($state) ? ($state['uk'] ?? $state['en'] ?? '') : $state;
                        return $indent.$prefix.$title;
                    })
                    ->html()
                    ->extraAttributes(['class' => 'font-mono whitespace-pre']),

                // Full breadcrumb path (для children — щоб зразу видно куди веде)
                Tables\Columns\TextColumn::make('full_path')
                    ->label('Шлях у дереві')
                    ->color('gray')
                    ->size('sm')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->full_path)
                    ->getStateUsing(fn ($record) => $record->full_path),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->fontFamily('mono')
                    ->size('xs')
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => '/'.(is_array($state) ? ($state['uk'] ?? '') : $state))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Recursive product count over the whole subtree (category +
                // all descendants). Direct ->counts('products') showed 0 for
                // parents because products hang off leaf sub-categories.
                // getStateUsing reads the accessor, which uses two request-
                // static maps (no N+1). Not DB-sortable, so no ->sortable().
                Tables\Columns\TextColumn::make('descendant_products_count')
                    ->label('Товарів (з підкатег.)')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'gray')
                    ->getStateUsing(fn ($record) => $record->descendant_products_count),

                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Підкатегорій')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\Filter::make('roots_only')
                    ->label('Лише кореневі')
                    ->query(fn ($query) => $query->whereNull('parent_id')->orWhere('parent_id', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('children_only')
                    ->label('Лише підкатегорії')
                    ->query(fn ($query) => $query->whereNotNull('parent_id')->where('parent_id', '!=', 0))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Належить до батька')
                    ->relationship('parent', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Усі')
                    ->trueLabel('Лише активні')
                    ->falseLabel('Лише вимкнені'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Змінити'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->size('lg')
                    ->tooltip('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Sort: roots first → then children grouped under parent → then by sort_order
            ->defaultSort('parent_id', 'asc')
            ->modifyQueryUsing(fn ($query) => $query
                ->with('parent.parent.parent')
                ->orderByRaw('COALESCE(parent_id, 0)')
                ->orderBy('sort_order')
                ->orderBy('id')
            )
            ->striped()
            ->paginated([25, 50, 100, 'all']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
