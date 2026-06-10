<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsTemplateResource\Pages;
use App\Models\SmsTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsTemplateResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;
    use \App\Filament\Concerns\RequiresModule;

    protected static ?string $model = SmsTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static ?string $navigationLabel = 'Шаблони SMS/Viber';
    protected static ?string $modelLabel = 'шаблон SMS';
    protected static ?string $pluralModelLabel = 'Шаблони SMS/Viber';
    protected static ?int $navigationSort = 31; // одразу після «Шаблони листів» (30)

    public static function moduleEnabled(): bool
    {
        return \App\Support\ModuleManager::for('turbosms')->enabled();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Шаблон')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Назва')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('key')
                        ->label('Ключ (системний)')
                        ->helperText('Подія, на яку реагує модуль. Не змінюйте після створення.')
                        ->required()
                        ->regex('/^[a-z0-9_\.]+$/')
                        ->unique(ignoreRecord: true)
                        ->disabledOn('edit'),
                    Forms\Components\Select::make('channel')
                        ->label('Канал')
                        ->options(SmsTemplate::CHANNELS)
                        ->default(SmsTemplate::CHANNEL_HYBRID)
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Гібрид: спершу Viber, при недоставці TurboSMS сам шле SMS (тарифікується доставлений канал)'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Активний')
                        ->default(true)
                        ->inline(false),
                ])->columns(2),

            Forms\Components\Section::make('Тексти')
                ->description('Плейсхолдери: {{order.id}}, {{order.total}}, {{order.ttn}}, {{order.status_label}}, {{order.customer_name}} — як у шаблонах листів.')
                ->schema([
                    Forms\Components\Textarea::make('text')
                        ->label('Текст SMS')
                        ->required()
                        ->rows(3)
                        ->maxLength(661)
                        ->helperText('До 661 символів кирилицею. Використовується і для Viber, якщо нижче порожньо.')
                        ->live(debounce: 400)
                        ->hint(fn ($state) => mb_strlen((string) $state).' симв. ≈ '.max(1, (int) ceil(mb_strlen((string) $state) / 70)).' SMS'),
                    Forms\Components\Textarea::make('viber_text')
                        ->label('Текст Viber (опційно)')
                        ->rows(4)
                        ->maxLength(1000)
                        ->helperText('Окремий довший текст для Viber (емодзі, переноси). Порожньо = текст SMS.'),
                ]),

            Forms\Components\Section::make('Viber-опції')
                ->description('Кнопка з лінком, картинка, пріоритет. URL кнопки підтримує плейсхолдери — напр. https://novaposhta.ua/tracking/?cargo_number={{order.ttn}}')
                ->visible(fn (Forms\Get $get) => $get('channel') !== SmsTemplate::CHANNEL_SMS)
                ->collapsible()
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('viber_button_text')
                            ->label('Текст кнопки')
                            ->placeholder('Відстежити 📦')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('viber_button_url')
                            ->label('URL кнопки')
                            ->placeholder('https://… ({{order.ttn}} підставиться)')
                            ->maxLength(500)
                            ->requiredWith('viber_button_text'),
                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('viber_image_url')
                            ->label('Картинка (URL)')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\Toggle::make('viber_transactional')
                            ->label('Транзакційне')
                            ->helperText('Вищий пріоритет доставки')
                            ->default(true)
                            ->inline(false),
                        Forms\Components\TextInput::make('viber_ttl')
                            ->label('TTL, сек')
                            ->numeric()
                            ->minValue(60)
                            ->maxValue(86400)
                            ->placeholder('3600 (дефолт шлюзу)'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable(),
                Tables\Columns\TextColumn::make('key')->label('Ключ')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('channel')->label('Канал')
                    ->badge()
                    ->formatStateUsing(fn ($state) => SmsTemplate::CHANNELS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        SmsTemplate::CHANNEL_VIBER => 'info',
                        SmsTemplate::CHANNEL_HYBRID => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('text')->label('Текст')->limit(60)->wrap(),
                Tables\Columns\IconColumn::make('is_active')->label('Активний')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsTemplates::route('/'),
            'create' => Pages\CreateSmsTemplate::route('/create'),
            'edit' => Pages\EditSmsTemplate::route('/{record}/edit'),
        ];
    }
}
