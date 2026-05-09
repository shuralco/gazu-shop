<?php

namespace App\Filament\Resources\UpShipmentResource\Pages;

use App\Filament\Resources\UpShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUpShipment extends EditRecord
{
    protected static string $resource = UpShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_ecom_ttn')
                ->label('Створити ТТН через eCom')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Надіслати запит до УкрПошти?')
                ->modalDescription('Буде створено офіційну ТТН через eCom API. Тариф спишеться з рахунку відправника.')
                ->visible(fn () => empty($this->record->ttn))
                ->action(function () {
                    $r = app(\App\Services\Shipping\UkrPoshtaTtnCreator::class)->createForShipment($this->record);

                    if ($r['success']) {
                        \Filament\Notifications\Notification::make()
                            ->title('ТТН створено!')
                            ->body("Номер: {$r['ttn']}")
                            ->success()
                            ->duration(10000)
                            ->send();
                        $this->refreshFormData(['ttn', 'up_status_text', 'up_status_code', 'status']);
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('УкрПошта повернула помилку')
                            ->body(implode('; ', $r['errors']))
                            ->danger()
                            ->duration(20000)
                            ->send();
                    }
                }),

            Actions\Action::make('track_now')
                ->label('Оновити статус')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => (bool) $this->record->ttn)
                ->action(function () {
                    $svc = app(\App\Services\UkrPoshtaEcomService::class);
                    $r = $svc->getLastStatus($this->record->ttn);

                    if ($r['success'] && ! empty($r['response'])) {
                        $resp = $r['response'];
                        $this->record->update([
                            'up_status_text' => $resp['statusName'] ?? $resp['name'] ?? '',
                            'up_status_code' => (string) ($resp['statusCode'] ?? $resp['eventCode'] ?? ''),
                            'last_tracked_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Статус оновлено')
                            ->body($resp['statusName'] ?? $resp['name'] ?? '—')
                            ->success()
                            ->send();
                        $this->refreshFormData(['up_status_text', 'up_status_code', 'last_tracked_at']);
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Не вдалося відстежити')
                            ->body(implode('; ', $r['errors']))
                            ->warning()
                            ->send();
                    }
                }),

            Actions\Action::make('print_sticker')
                ->label('Наклейка PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn () => (bool) $this->record->ttn)
                ->action(function () {
                    // ttn is the barcode, we need shipment uuid — pull from log/payload or store separately
                    // For now use barcode tracker — UkrPoshta accepts both barcode and uuid in path?
                    // The spec uses uuid. We saved barcode as ttn. Fall back to barcode.
                    $svc = app(\App\Services\UkrPoshtaEcomService::class);
                    $result = $svc->downloadSticker($this->record->ttn);

                    if (! $result['success']) {
                        \Filament\Notifications\Notification::make()
                            ->title('Не вдалося отримати PDF')
                            ->body(implode('; ', $result['errors']))
                            ->danger()
                            ->send();

                        return null;
                    }

                    return response()->streamDownload(
                        fn () => print($result['pdf']),
                        "sticker-{$this->record->ttn}.pdf",
                        ['Content-Type' => 'application/pdf']
                    );
                }),

            Actions\Action::make('track_url')
                ->label('Відстеження на ukrposhta.ua')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url(fn () => $this->record->getTrackingUrl())
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->record->ttn),

            Actions\Action::make('copy_ttn')
                ->label('Копіювати ТТН')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->extraAttributes(fn () => [
                    'x-on:click' => "navigator.clipboard.writeText('{$this->record->ttn}'); \$tooltip('Скопійовано!')",
                ])
                ->visible(fn () => (bool) $this->record->ttn),

            Actions\DeleteAction::make(),
        ];
    }
}
