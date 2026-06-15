<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresModule;
use App\Filament\Resources\UserCarResource\Pages;
use App\Models\UserCar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserCarResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    use RequiresModule;

    protected static string $moduleKey = 'gazu_garage';

    protected static ?string $model = UserCar::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Гараж клієнтів';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $modelLabel = 'Авто клієнта';

    protected static ?string $pluralModelLabel = 'Авто клієнтів';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Власник')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Користувач')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ])->columns(1),

            Forms\Components\Section::make('Авто')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('make')->label('Марка')->required()->maxLength(60),
                    Forms\Components\TextInput::make('model')->label('Модель')->required()->maxLength(80),
                    Forms\Components\TextInput::make('year')->label('Рік')->numeric()->minValue(1950)->maxValue((int) date('Y') + 1),
                    Forms\Components\TextInput::make('engine')->label('Двигун')->maxLength(80)->placeholder('2.0 TDI · CKFC'),
                    Forms\Components\TextInput::make('body_type')->label('Кузов')->maxLength(60),
                    Forms\Components\TextInput::make('color')->label('Колір')->maxLength(40),
                    Forms\Components\TextInput::make('vin')->label('VIN')->maxLength(30)->placeholder('17-знач код'),
                    Forms\Components\TextInput::make('plate')->label('Держ. номер')->maxLength(20),
                ]),
                Forms\Components\Toggle::make('is_primary')->label('Основне авто користувача'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Власник')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('make')->label('Марка')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('model')->label('Модель')->searchable(),
                Tables\Columns\TextColumn::make('year')->label('Рік')->sortable()->size('sm'),
                Tables\Columns\TextColumn::make('engine')->label('Двигун')->size('sm')->toggleable(),
                Tables\Columns\TextColumn::make('vin')->label('VIN')->size('sm')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plate')->label('Номер')->size('sm')->toggleable(),
                Tables\Columns\IconColumn::make('is_primary')->label('Основне')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Додано')->dateTime('d.m.Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')->label('Основне'),
                Tables\Filters\SelectFilter::make('make')
                    ->label('Марка')
                    ->options(fn () => UserCar::query()->distinct()->orderBy('make')->pluck('make', 'make')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Eager-load user — колонка user.name інакше = N+1 щорядка.
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserCars::route('/'),
        ];
    }
}
