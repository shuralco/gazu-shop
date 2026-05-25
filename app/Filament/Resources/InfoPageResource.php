<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfoPageResource\Pages;
use App\Models\InfoPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InfoPageResource extends Resource
{
    protected static ?string $model = InfoPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Інфо сторінки';

    protected static ?string $pluralModelLabel = 'Інфо сторінки';

    protected static ?string $modelLabel = 'Інфо сторінка';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Контент')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')->required()->maxLength(180),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')->required()
                            ->helperText('URL: /<slug> — наприклад /about, /delivery')
                            ->maxLength(120)->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    ]),
                    Forms\Components\Textarea::make('intro')
                        ->label('Короткий вступ')->rows(2)->maxLength(500),
                    Forms\Components\RichEditor::make('content_html')
                        ->label('Основний текст')->toolbarButtons([
                            'bold','italic','underline','strike','link','bulletList','orderedList','blockquote','h2','h3','codeBlock','redo','undo',
                        ])->columnSpanFull(),
                    Forms\Components\Repeater::make('sections')
                        ->label('Структуровані секції (опціонально)')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Підзаголовок')->required(),
                            Forms\Components\Textarea::make('body')->label('Текст')->rows(3),
                            Forms\Components\TagsInput::make('list')->label('Перелік (буде список)')->separator('|'),
                        ])
                        ->collapsed()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->columnSpanFull(),
                ])->collapsible(),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('meta_title')->label('Meta title')->maxLength(180),
                    Forms\Components\Textarea::make('meta_description')->label('Meta description')->rows(2)->maxLength(300),
                ])->collapsed(),

            Forms\Components\Section::make('Відображення')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Toggle::make('is_active')->label('Активна')->default(true),
                        Forms\Components\Toggle::make('show_in_footer')->label('У футері')->default(true),
                        Forms\Components\Toggle::make('show_in_topbar')->label('У топ-барі')->default(false),
                        Forms\Components\TextInput::make('sort_order')->label('Сортування')->numeric()->default(100),
                    ]),
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Заголовок')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('slug')->label('URL')->fontFamily('mono')->formatStateUsing(fn ($state) => "/{$state}")->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label('Активна')->boolean(),
                Tables\Columns\IconColumn::make('show_in_footer')->label('Футер')->boolean(),
                Tables\Columns\IconColumn::make('show_in_topbar')->label('Топ-бар')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Сорт.')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Оновлено')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('open')
                    ->label('Відкрити на сайті')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (InfoPage $r) => url('/'.$r->slug), shouldOpenInNewTab: true),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfoPages::route('/'),
            'create' => Pages\CreateInfoPage::route('/create'),
            'edit' => Pages\EditInfoPage::route('/{record}/edit'),
        ];
    }
}
