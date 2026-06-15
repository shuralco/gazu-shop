<?php

namespace App\Filament\Resources\AccessPresetResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

/**
 * Персонал, призначений на цей пресет доступу (users.access_preset_id).
 * Дозволяє привʼязати наявного користувача, створити нового співробітника
 * одразу з цим пресетом, або відвʼязати (скине роль → втратить доступ до панелі).
 */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Користувачі (персонал)';

    // Рендеримо одразу (не lazy) — користувачів на пресет небагато, зате блок
    // видно без прокрутки/очікування підвантаження.
    protected static bool $isLazy = false;

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([
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
                // Обовʼязковий лише при створенні; при редагуванні — лишити порожнім.
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->maxLength(255),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Імʼя')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_admin')->label('Супер-адмін')->boolean(),
            ])
            ->headerActions([
                // Привʼязати наявного користувача → проставить access_preset_id.
                Tables\Actions\AssociateAction::make()
                    ->label('Привʼязати користувача')
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->preloadRecordSelect(),
                // Створити нового співробітника одразу з цим пресетом.
                Tables\Actions\CreateAction::make()
                    ->label('Новий співробітник'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Відвʼязати = users.access_preset_id → null (втратить доступ до панелі).
                Tables\Actions\DissociateAction::make()
                    ->label('Відвʼязати'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ]);
    }
}
