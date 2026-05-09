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

    protected static ?string $modelLabel = 'Категорія';

    protected static ?string $pluralModelLabel = 'Категорії';

    protected static ?int $navigationSort = 2;

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
                                    ->options(function () {
                                        return Category::query()
                                            ->where('parent_id', 0)
                                            ->pluck('title', 'id')
                                            ->prepend('Немає батьківської', 0);
                                    })
                                    ->searchable()
                                    ->default(0)
                                    ->columnSpan(1),
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
                                                    $set('seo_title', $seoTitle);

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
                                                    $set('seo_description', $seoDescription);

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
                                                    $set('seo_keywords', $keywords);

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
                                            $set('seo_title', $seoTitle);

                                            // Генеруємо SEO description
                                            $descriptionTemplate = \App\Models\DisplaySetting::get('seo_category_description_template', 'Великий вибір товарів у категорії %s. Швидка доставка по Україні. Гарантія якості.');
                                            $seoDescription = sprintf($descriptionTemplate, $title);
                                            $set('seo_description', $seoDescription);

                                            // Генеруємо keywords
                                            $keywords = [
                                                strtolower($title),
                                                'купити '.strtolower($title),
                                                strtolower($title).' ціна',
                                                'каталог',
                                                'україна',
                                            ];
                                            $set('seo_keywords', $keywords);

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

                                Forms\Components\TextInput::make('seo_title')
                                    ->label('SEO Заголовок')
                                    ->maxLength(60)
                                    ->helperText('Оптимальна довжина: 50-60 символів'),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('SEO Опис')
                                    ->maxLength(155)
                                    ->helperText('Оптимальна довжина: 150-160 символів')
                                    ->rows(3),

                                Forms\Components\TagsInput::make('seo_keywords')
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
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Посилання')
                    ->searchable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Батьківська категорія')
                    ->badge()
                    ->color('success')
                    ->default('Коренева категорія'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Товари')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Підкатегорії')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(function () {
                        return Category::query()
                            ->where('parent_id', 0)
                            ->pluck('title', 'id')
                            ->prepend('All Categories', null)
                            ->prepend('Root Categories Only', 0);
                    }),
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
            ->defaultSort('parent_id')
            ->striped();
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
