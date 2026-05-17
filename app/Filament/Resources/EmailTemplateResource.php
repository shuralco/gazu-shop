<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static ?string $navigationLabel = 'Шаблони листів';
    protected static ?string $modelLabel = 'Шаблон листа';
    protected static ?string $pluralModelLabel = 'Шаблони листів';
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Метадані')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->label('Ключ')
                        ->required()
                        ->maxLength(60)
                        ->disabledOn('edit')
                        ->helperText('Незмінний ID шаблону (order.created, callback.received, тощо)'),
                    Forms\Components\TextInput::make('name')
                        ->label('Назва (для адміна)')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Select::make('to_kind')
                        ->label('Кому надсилається')
                        ->options([
                            EmailTemplate::TO_CUSTOMER => 'Клієнту',
                            EmailTemplate::TO_ADMIN    => 'Адміну',
                            EmailTemplate::TO_MANAGER  => 'Менеджеру',
                        ])
                        ->required()
                        ->default(EmailTemplate::TO_CUSTOMER),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Активний (надсилається)')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Контент листа')
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->label('Тема')
                        ->required()
                        ->maxLength(200)
                        ->helperText('Можна використовувати {{variables}}'),
                    Forms\Components\RichEditor::make('body_html')
                        ->label('Тіло листа (HTML)')
                        ->required()
                        ->toolbarButtons([
                            'h2', 'h3', 'bold', 'italic', 'link',
                            'bulletList', 'orderedList',
                            'undo', 'redo',
                        ])
                        ->helperText('HTML контент. Використовуй {{var.path}} для підстановки.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Sender override (опціонально)')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('from_email')
                        ->label('From Email')
                        ->email()
                        ->placeholder('orders@gazu.uno')
                        ->helperText('Залиш порожнім для використання дефолту .env MAIL_FROM_ADDRESS'),
                    Forms\Components\TextInput::make('from_name')
                        ->label('From Name')
                        ->placeholder('GAZU Orders'),
                ])->columns(2),

            Forms\Components\Section::make('Доступні змінні')
                ->collapsed()
                ->schema([
                    Forms\Components\View::make('filament.email-template-variables')
                        ->viewData(fn ($record) => ['help' => $record?->variables_help ?? []]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Тема')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('to_kind')
                    ->label('Кому')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EmailTemplate::TO_CUSTOMER => 'success',
                        EmailTemplate::TO_ADMIN    => 'warning',
                        EmailTemplate::TO_MANAGER  => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        EmailTemplate::TO_CUSTOMER => 'Клієнту',
                        EmailTemplate::TO_ADMIN    => 'Адмін',
                        EmailTemplate::TO_MANAGER  => 'Менеджер',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('key', 'asc')
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(function ($record) {
                        $sample = static::sampleVariables();
                        $rendered = $record->render($sample);
                        return view('filament.email-template-preview', [
                            'subject' => $rendered['subject'],
                            'body' => $rendered['body'],
                        ]);
                    })
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false),
                Tables\Actions\Action::make('sendTest')
                    ->label('Тест-лист')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email отримувача')
                            ->email()
                            ->required()
                            ->default(fn () => auth()->user()?->email),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            \Mail::to($data['email'])->send(
                                new \App\Mail\TemplatedMail($record->key, static::sampleVariables())
                            );
                            Notification::make()->title('Тест надіслано на '.$data['email'])->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Помилка: '.$e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\EditAction::make()->label(''),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    /**
     * Sample variables для preview / test send — realistic data.
     */
    public static function sampleVariables(): array
    {
        return [
            'order' => [
                'id' => 12345,
                'customer_name' => 'Іван Петренко',
                'phone' => '+380501234567',
                'email' => 'test@example.com',
                'total' => '1 580',
                'delivery_method' => 'Нова Пошта',
                'delivery_city' => 'Київ',
                'url' => url('/kabinet/zamovlennya/12345'),
                'admin_url' => url('/admin/orders/12345/edit'),
                'ttn' => '20451234567890',
                'carrier' => 'Нова Пошта',
                'tracking_url' => 'https://novaposhta.ua/tracking/20451234567890',
                'expected_date' => now()->addDays(2)->format('d.m.Y'),
            ],
            'callback' => [
                'phone' => '+380501234567',
                'name' => 'Іван',
                'source' => 'footer',
                'referrer_url' => url('/'),
                'created_at' => now()->format('d.m.Y H:i'),
                'admin_url' => url('/admin/callback-requests/1/edit'),
            ],
            'user' => [
                'name' => 'Іван Петренко',
                'email' => 'test@example.com',
            ],
            'site_url' => url('/'),
        ];
    }
}
