<?php

namespace App\Filament\Resources\NpShipmentResource\Pages;

use App\Filament\Resources\NpShipmentResource;
use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use App\Services\Shipping\NovaPoshtaTtnCreator;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditNpShipment extends EditRecord
{
    protected static string $resource = NpShipmentResource::class;

    protected function getHeaderActions(): array
    {
        $apiKey = DisplaySetting::get('np_api_key', '');

        return [
            Actions\Action::make('retry_ttn')
                ->label('Повторити запит до НП')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Повторити створення ТТН?')
                ->modalDescription('Зараз дані будуть знову відправлені у Нову Пошту. Якщо все правильно — повернеться номер ТТН.')
                ->action(function () {
                    $result = app(NovaPoshtaTtnCreator::class)->createForShipment($this->record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('ТТН створено!')
                            ->body("Номер: {$result['ttn']}")
                            ->success()
                            ->duration(10000)
                            ->send();
                        $this->refreshFormData(['ttn', 'ref', 'status', 'shipping_cost', 'estimated_delivery_date']);
                    } else {
                        $errors = implode('; ', $result['errors']);
                        $hint = '';
                        if (str_contains($errors, 'RecipientName incorrect')) {
                            $hint = "\n\n💡 Виправте «ПІБ отримувача» — NP приймає лише кирилицю (наприклад: «Чистяков Владислав»).";
                        } elseif (str_contains($errors, 'Max Cost')) {
                            $hint = "\n\n💡 Оголошена вартість > 1 000 000 грн для ваги ≤ 30 кг — буде автоматично обмежено до 999 999.";
                        } elseif (str_contains($errors, 'Sender')) {
                            $hint = "\n\n💡 Перевірте налаштування відправника на сторінці налаштувань НП.";
                        }
                        Notification::make()
                            ->title('НП API повернув помилку')
                            ->body($errors.$hint)
                            ->danger()
                            ->duration(20000)
                            ->send();
                    }
                })
                ->visible(fn () => empty($this->record->ttn)),

            Actions\Action::make('track')
                ->label('Відстежити')
                ->icon('heroicon-o-map-pin')
                ->color('info')
                ->action(function () {
                    NpShipmentResource::trackShipment($this->record);
                    $this->refreshFormData(['status', 'np_status', 'np_status_code', 'shipping_cost', 'estimated_delivery_date', 'tracking_history', 'last_tracked_at']);
                })
                ->visible(fn () => $this->record->ttn && $this->record->needsTracking()),

            Actions\Action::make('print_ttn')
                ->label('Друк ТТН')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => $this->record->ref
                    ? "https://my.novaposhta.ua/orders/printDocument/orders[]/{$this->record->ref}/type/pdf/apiKey/{$apiKey}"
                    : null)
                ->openUrlInNewTab()
                ->visible(fn () => ! empty($this->record->ref)),

            Actions\Action::make('print_marking')
                ->label('Маркування')
                ->icon('heroicon-o-tag')
                ->color('warning')
                ->url(fn () => $this->record->ref
                    ? "https://my.novaposhta.ua/orders/printMarkings/orders[]/{$this->record->ref}/type/pdf/apiKey/{$apiKey}"
                    : null)
                ->openUrlInNewTab()
                ->visible(fn () => ! empty($this->record->ref)),

            Actions\Action::make('tracking_url')
                ->label('Відстеження НП')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => $this->record->getTrackingUrl())
                ->openUrlInNewTab()
                ->visible(fn () => ! empty($this->record->ttn)),

            Actions\Action::make('copy_ttn')
                ->label('Копіювати ТТН')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->extraAttributes([
                    'x-on:click' => "navigator.clipboard.writeText('{$this->record->ttn}'); \$tooltip('Скопійовано!')",
                ])
                ->visible(fn () => ! empty($this->record->ttn)),

            Actions\Action::make('delete_ttn')
                ->label('Видалити ТТН')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Видалити ТТН?')
                ->modalDescription('ТТН буде видалено з Нової Пошти та з системи. Цю дію неможливо скасувати.')
                ->action(function () {
                    $this->deleteFromNp();
                })
                ->visible(fn () => $this->record->canDelete()),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->canDelete()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Resolve city name
        if (! empty($data['recipient_city_ref']) && empty($data['recipient_city_name'])) {
            $city = NpCity::where('ref', $data['recipient_city_ref'])->first();
            $data['recipient_city_name'] = $city?->description ?? '';
        }

        // Build address for courier delivery
        if (in_array($data['service_type'] ?? '', ['WarehouseDoors', 'DoorsDoors'])) {
            $address = trim(implode(', ', array_filter([
                $data['recipient_street'] ?? '',
                $data['recipient_building'] ?? '',
                ! empty($data['recipient_apartment']) ? "кв. {$data['recipient_apartment']}" : '',
            ])));
            $data['recipient_address'] = $address;
        }

        return $data;
    }

    protected function deleteFromNp(): void
    {
        $record = $this->record;

        if ($record->ref) {
            try {
                $service = app(NovaPoshtaApiService::class);
                $result = $service->deleteShipment($record->ref);

                if ($result['success'] ?? false) {
                    $record->delete();

                    Notification::make()
                        ->title('ТТН видалено')
                        ->body("ТТН {$record->ttn} видалено з Нової Пошти")
                        ->success()
                        ->send();

                    $this->redirect(NpShipmentResource::getUrl('index'));
                } else {
                    $errors = implode('; ', $result['errors'] ?? ['Невідома помилка']);
                    Notification::make()
                        ->title('Помилка видалення')
                        ->body($errors)
                        ->danger()
                        ->send();
                }
            } catch (\Exception $e) {
                Log::error('NP delete TTN error: ' . $e->getMessage());
                Notification::make()
                    ->title('Помилка')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        } else {
            $record->delete();
            Notification::make()
                ->title('Запис видалено')
                ->success()
                ->send();

            $this->redirect(NpShipmentResource::getUrl('index'));
        }
    }
}
