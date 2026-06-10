<?php

namespace App\Filament\Resources\SmsTemplateResource\Pages;

use App\Filament\Resources\SmsTemplateResource;
use App\Jobs\SendTemplatedSms;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSmsTemplate extends EditRecord
{
    protected static string $resource = SmsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test_send')
                ->label('Тестова відправка')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\TextInput::make('phone')
                        ->label('Телефон')
                        ->placeholder('380671234567')
                        ->required()
                        ->tel(),
                ])
                ->action(function (array $data) {
                    // Тестові значення плейсхолдерів, щоб у SMS не світились {{...}}
                    SendTemplatedSms::dispatch($this->record->key, $data['phone'], [
                        'order' => [
                            'id' => 'TEST',
                            'total' => '1 234',
                            'ttn' => '20450000000000',
                            'status_label' => 'Тестовий статус',
                            'customer_name' => 'Тест',
                        ],
                    ]);

                    Notification::make()
                        ->title('Поставлено в чергу')
                        ->body('Перевірте журнал SMS за хвилину — там буде статус відправки.')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
