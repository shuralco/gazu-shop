<?php

namespace App\Filament\Resources\NpApiLogResource\Pages;

use App\Filament\Resources\NpApiLogResource;
use App\Models\NpApiLog;
use App\Services\NovaPoshtaApiService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewNpApiLog extends ViewRecord
{
    protected static string $resource = NpApiLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_shipment')
                ->label(function () {
                    $sh = $this->record->getRelatedShipment();

                    return $sh ? ($sh->ttn ? "Shipment TTN {$sh->ttn}" : "Shipment #{$sh->id}") : 'Shipment не знайдено';
                })
                ->icon('heroicon-o-link')
                ->color('primary')
                ->visible(fn () => (bool) $this->record->getRelatedShipment())
                ->url(fn () => \App\Filament\Resources\NpShipmentResource::getUrl('edit', ['record' => $this->record->getRelatedShipment()->id])),

            Actions\Action::make('replay_request')
                ->label('Повторити запит')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Повторити цей API запит?')
                ->modalDescription('Той самий payload буде надіслано до НП API. Результат буде записано як новий лог.')
                ->visible(fn () => is_array($this->record->request_payload))
                ->action(function () {
                    $payload = is_array($this->record->request_payload) ? $this->record->request_payload : [];
                    /** @var NovaPoshtaApiService $api */
                    $api = app(NovaPoshtaApiService::class);

                    // Use reflection to call the private callApi() with original endpoint
                    $r = new \ReflectionClass($api);
                    $m = $r->getMethod('callApi');
                    $m->setAccessible(true);
                    $result = $m->invoke($api, $this->record->endpoint_model, $this->record->endpoint_method, $payload);

                    if ($result['success'] ?? false) {
                        Notification::make()
                            ->title('Запит виконано успішно')
                            ->body('Перевірте новий лог у списку.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Запит знов невдалий')
                            ->body(implode('; ', $result['errors'] ?? ['невідома помилка']))
                            ->danger()
                            ->duration(15000)
                            ->send();
                    }

                    $latest = NpApiLog::latest('id')->first();
                    if ($latest && $latest->id !== $this->record->id) {
                        $this->redirect(NpApiLogResource::getUrl('view', ['record' => $latest->id]));
                    }
                }),

            Actions\Action::make('copy_payload')
                ->label('Копіювати payload')
                ->icon('heroicon-o-clipboard')
                ->color('gray')
                ->extraAttributes(fn () => [
                    'x-on:click' => "navigator.clipboard.writeText(".json_encode(json_encode($this->record->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).")",
                ])
                ->visible(fn () => is_array($this->record->request_payload)),

            Actions\DeleteAction::make()
                ->label('Видалити')
                ->color('danger'),
        ];
    }
}
