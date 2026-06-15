<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Користувачі';

    protected static ?string $modelLabel = 'Користувач';

    protected static ?string $pluralModelLabel = 'Користувачі';

    protected static ?int $navigationSort = 70;

    public static function getNavigationBadge(): ?string
    {
        $new7d = static::getModel()::where('created_at', '>=', now()->subDays(7))->count();
        return $new7d > 0 ? '+'.$new7d : (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $new7d = static::getModel()::where('created_at', '>=', now()->subDays(7))->count();
        return $new7d > 0 ? 'success' : 'gray';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Нові за 7 днів / усього '.static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Інформація про користувача')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Аватар')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('150')
                            ->imageResizeTargetHeight('150')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Імʼя')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Електронна пошта')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->revealable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Дозволи')
                    ->schema([
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Супер-адмін')
                            ->helperText('Повний доступ до всього (обходить пресети). Лишай вимкненим для звичайного персоналу.')
                            ->live(),
                        Forms\Components\Select::make('access_preset_id')
                            ->label('Пресет доступу (роль)')
                            ->relationship('accessPreset', 'name')
                            ->searchable()->preload()
                            ->helperText('Що бачить і може робити цей співробітник в адмінці. Ігнорується для супер-адміна.')
                            ->visible(fn (Forms\Get $get) => ! $get('is_admin'))
                            ->required(fn (Forms\Get $get) => ! $get('is_admin')),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Пошта підтверджена')
                            ->helperText('Залишить порожнім для непідтвердженої пошти'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Профіль')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                        Forms\Components\DatePicker::make('birthdate')
                            ->label('Дата народження'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Група та лояльність')
                    ->schema([
                        Forms\Components\Select::make('customer_group_id')
                            ->label('Група клієнтів')
                            ->relationship('customerGroup', 'display_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('loyalty_tier')
                            ->label('Рівень лояльності')
                            ->disabled(),
                        Forms\Components\TextInput::make('loyalty_points')
                            ->label('Бали лояльності')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_spent')
                            ->label('Загальна сума покупок')
                            ->disabled()
                            ->prefix('₴'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Аватар')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label('Імʼя')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Адмін')
                    ->boolean(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customerGroup.display_name')
                    ->label('Група')
                    ->badge(),
                Tables\Columns\TextColumn::make('loyalty_tier')
                    ->label('Рівень лояльності')
                    ->badge()
                    ->color(fn ($record) => match ($record?->loyalty_tier) {
                        'gold' => 'warning',
                        'silver' => 'gray',
                        'platinum' => 'primary',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('Бали')
                    ->numeric(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Пошта підтверджена')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\LoyaltyTransactionsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Eager-load customerGroup — інакше колонка customerGroup.display_name
        // довантажувала зв'язок щорядка (N+1).
        return parent::getEloquentQuery()->with(['customerGroup']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
