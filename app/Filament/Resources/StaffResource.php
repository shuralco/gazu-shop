<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

/**
 * Персонал адмінки — користувачі з доступом до панелі (супер-адміни або
 * з призначеним пресетом). Окремо від клієнтів сайту (UserResource), бо це
 * принципово різні сутності: клієнти купують, персонал керує.
 */
class StaffResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'Персонал';

    protected static ?string $modelLabel = 'Співробітник';

    protected static ?string $pluralModelLabel = 'Персонал';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    /** Лише персонал: супер-адміни АБО з призначеним пресетом доступу. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $q) => $q->where('is_admin', true)->orWhereNotNull('access_preset_id'))
            ->with('accessPreset');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Обліковий запис')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Імʼя')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->label('Пароль')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText('При редагуванні лишіть порожнім, щоб не міняти.')
                        ->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('Доступ до адмінки')
                ->schema([
                    Forms\Components\Toggle::make('is_admin')
                        ->label('Супер-адмін')
                        ->helperText('Повний доступ до всього (обходить пресети). Для звичайного персоналу — вимкнено.')
                        ->live(),
                    Forms\Components\Select::make('access_preset_id')
                        ->label('Пресет доступу (роль)')
                        ->relationship('accessPreset', 'name')
                        ->searchable()->preload()
                        ->helperText('Що бачить і може робити цей співробітник. Ігнорується для супер-адміна.')
                        ->visible(fn (Forms\Get $get) => ! $get('is_admin'))
                        ->required(fn (Forms\Get $get) => ! $get('is_admin')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Імʼя')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_admin')->label('Супер-адмін')->boolean(),
                Tables\Columns\TextColumn::make('accessPreset.name')
                    ->label('Роль (пресет)')
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Додано')->dateTime('d.m.Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')->label('Супер-адміни'),
                Tables\Filters\SelectFilter::make('access_preset_id')
                    ->label('Пресет доступу')
                    ->relationship('accessPreset', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
