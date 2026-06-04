<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterLandingResource\Pages;
use App\Models\FilterLanding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FilterLandingResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = FilterLanding::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationLabel = 'SEO-лендінги';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = 'Лендінг';

    protected static ?string $pluralModelLabel = 'SEO лендінги';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('tabs')->tabs([
                Forms\Components\Tabs\Tab::make('Основне')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Заголовок (внутрішня назва)')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get) =>
                            empty($get('slug')) ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),

                    Forms\Components\TextInput::make('slug')
                        ->label('URL-slug')
                        ->required()
                        ->maxLength(180)
                        ->unique(ignoreRecord: true)
                        ->prefix('/lp/')
                        ->helperText('URL: /lp/{slug}'),

                    Forms\Components\TextInput::make('h1')
                        ->label('H1 заголовок')
                        ->maxLength(255)
                        ->helperText('Якщо порожньо — використовується title'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Активна')
                        ->default(true),

                    Forms\Components\Toggle::make('show_applied_filters')
                        ->label('Показувати застосовані фільтри на сторінці')
                        ->helperText('Покаже chip-и з категорією, брендом та фільтрами під H1 (як в адмінці)')
                        ->default(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),

                Forms\Components\Tabs\Tab::make('Фільтри товарів')->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Категорія')
                        ->relationship('category', 'title')
                        ->searchable()
                        ->preload()
                        ->placeholder('— Усі категорії —')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_path ?? (is_array($record->title) ? ($record->title['uk'] ?? '') : $record->title)),

                    Forms\Components\Select::make('brand_id')
                        ->label('Бренд')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('— Усі бренди —'),

                    Forms\Components\Select::make('filter_ids')
                        ->label('Фільтри (характеристики)')
                        ->multiple()
                        ->searchable()
                        ->options(function () {
                            $filters = \App\Models\Filter::with('filterGroup')
                                ->where('is_active', true)
                                ->orderBy('filter_group_id')
                                ->orderBy('title')
                                ->get();
                            return $filters->mapWithKeys(function ($f) {
                                $g = $f->filterGroup;
                                $gTitle = $g ? (is_array($g->title) ? ($g->title['uk'] ?? '') : $g->title) : 'Без групи';
                                $fTitle = is_array($f->title) ? ($f->title['uk'] ?? '') : $f->title;
                                return [$f->id => trim($gTitle).': '.$fTitle];
                            })->toArray();
                        })
                        ->helperText('Товари мають відповідати ВСІМ обраним фільтрам'),
                ]),

                Forms\Components\Tabs\Tab::make('SEO + Контент')->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(70)
                        ->helperText('60-70 символів — оптимально для Google'),

                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta description')
                        ->maxLength(160)
                        ->rows(2),

                    Forms\Components\RichEditor::make('intro_html')
                        ->label('Intro (вгорі сторінки)')
                        ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'h2', 'h3'])
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('outro_html')
                        ->label('SEO-текст (внизу сторінки)')
                        ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'h2', 'h3'])
                        ->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')->label('')->boolean(),
                Tables\Columns\TextColumn::make('title')->label('Назва')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->prefix('/lp/')
                    ->copyable()
                    ->url(fn ($record) => url('/lp/'.$record->slug), shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категорія')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['uk'] ?? '') : $state)
                    ->placeholder('—')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->placeholder('—')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('filter_ids')
                    ->label('Фільтрів')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->badge(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Перегляди')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Статус'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => url('/lp/'.$record->slug), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make()->label('')->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()->label('')->icon('heroicon-o-trash'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFilterLandings::route('/'),
            'create' => Pages\CreateFilterLanding::route('/create'),
            'edit' => Pages\EditFilterLanding::route('/{record}/edit'),
        ];
    }
}
