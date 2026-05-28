<?php

namespace App\Filament\Pages;

use App\Models\ShopSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Error404Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Налаштування 404';

    protected static ?string $navigationGroup = 'Контент та SEO';

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.pages.error404-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'error_404_title' => ShopSettings::get('error_404_title', 'СТОРІНКА НЕ ЗНАЙДЕНА'),
            'error_404_subtitle' => ShopSettings::get('error_404_subtitle', 'На жаль, сторінка, яку ви шукаєте, не існує або була переміщена.'),
            'error_404_phone' => ShopSettings::get('error_404_phone', '0-800-123-456'),
            'error_404_email' => ShopSettings::get('error_404_email', 'support@simpleshop.ua'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('error_404_title')
                    ->label('Заголовок помилки')
                    ->required()
                    ->default('СТОРІНКА НЕ ЗНАЙДЕНА'),

                Textarea::make('error_404_subtitle')
                    ->label('Підзаголовок помилки')
                    ->required()
                    ->rows(3)
                    ->default('На жаль, сторінка, яку ви шукаєте, не існує або була переміщена.'),

                TextInput::make('error_404_phone')
                    ->label('Телефон підтримки')
                    ->required()
                    ->default('0-800-123-456'),

                TextInput::make('error_404_email')
                    ->label('Email підтримки')
                    ->required()
                    ->email()
                    ->default('support@simpleshop.ua'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            ShopSettings::set($key, $value, 'string', 'error_404', 'Налаштування сторінки 404');
        }

        Notification::make()
            ->title('Налаштування збережено')
            ->success()
            ->send();
    }
}
