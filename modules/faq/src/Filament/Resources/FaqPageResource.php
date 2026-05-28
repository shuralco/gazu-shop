<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqPageResource\Pages;
use App\Models\FaqPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqPageResource extends Resource
{
    protected static ?string $model = FaqPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?string $modelLabel = 'FAQ Сторінка';

    protected static ?string $pluralModelLabel = 'FAQ Сторінки';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Назва сторінки')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                $urlService = new \App\Services\UrlRouterService;
                                $set('slug', $urlService->generateSlug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('URL slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Опис сторінки')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортування')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('FAQ питання')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->label('Питання та відповіді')
                            ->schema([
                                Forms\Components\TextInput::make('question')
                                    ->label('Питання')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('answer')
                                    ->label('Відповідь')
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold', 'italic', 'link', 'orderedList', 'unorderedList',
                                    ]),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? 'Нове питання')
                            ->addActionLabel('+ Додати питання')
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('SEO налаштування')
                    ->schema([
                        Forms\Components\TextInput::make('seo_title')
                            ->label('SEO Заголовок')
                            ->maxLength(60)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate_seo')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Згенерувати автоматично')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        $title = $get('title');
                                        $set('seo_title', "Часті питання - {$title} | SimpleShop");
                                        $set('seo_description', "Відповіді на найпоширеніші питання про {$title}. Детальна інформація та поради від експертів SimpleShop.");
                                        $set('seo_keywords', "faq, часті питання, {$title}, питання, відповіді, довідка");
                                    })
                            ),
                        Forms\Components\Textarea::make('seo_description')
                            ->label('SEO Опис')
                            ->maxLength(155)
                            ->rows(3),
                        Forms\Components\TextInput::make('seo_keywords')
                            ->label('SEO Ключові слова')
                            ->helperText('Розділяйте комами'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),
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
                    ->label('URL')
                    ->searchable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('questions')
                    ->label('Кількість питань')
                    ->formatStateUsing(fn ($state) => count($state ?? []))
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна'),
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
            ->defaultSort('sort_order');
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
            'index' => Pages\ListFaqPages::route('/'),
            'create' => Pages\CreateFaqPage::route('/create'),
            'view' => Pages\ViewFaqPage::route('/{record}'),
            'edit' => Pages\EditFaqPage::route('/{record}/edit'),
        ];
    }
}
