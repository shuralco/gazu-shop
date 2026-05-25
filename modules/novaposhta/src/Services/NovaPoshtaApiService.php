<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NovaPoshtaApiService
{
    private string $apiKey;

    private string $apiUrl;

    public function __construct()
    {
        // 1. ShippingProvider DB config
        $provider = \App\Models\ShippingProvider::where('code', 'novaposhta')->first();
        $cfg = $provider->configuration ?? [];

        // 2. Fallback to config files (services.nova_poshta or novaposhta)
        // Use ?: (not ??) so empty strings fall through to the next source.
        $this->apiKey = ($cfg['api_key'] ?? null)
            ?: config('services.nova_poshta.api_key')
            ?: config('novaposhta.api_key', '');

        $this->apiUrl = config('services.nova_poshta.api_url')
            ?: config('novaposhta.api_url', 'https://api.novaposhta.ua/v2.0/json/');
    }

    // ==========================================
    // Address Methods
    // ==========================================

    /**
     * Пошук міст за рядком
     */
    public function searchCities(string $query, int $limit = 20): array
    {
        return $this->callApi('Address', 'getCities', [
            'FindByString' => $query,
            'Limit' => $limit,
        ]);
    }

    /**
     * Отримати міста (за ref або всі)
     */
    public function getCities(string $ref = '', int $page = 1, int $limit = 150): array
    {
        $params = ['Page' => (string) $page, 'Limit' => (string) $limit];

        if ($ref !== '') {
            $params['Ref'] = $ref;
        }

        return $this->callApi('Address', 'getCities', $params);
    }

    /**
     * Пошук населених пунктів (для кур'єрської доставки)
     */
    public function searchSettlements(string $query, int $limit = 20): array
    {
        return $this->callApi('Address', 'searchSettlements', [
            'CityName' => $query,
            'Limit' => (string) $limit,
        ]);
    }

    /**
     * Отримати області
     */
    public function getAreas(): array
    {
        return $this->callApi('Address', 'getAreas');
    }

    /**
     * Отримати відділення/поштомати для міста
     */
    public function getWarehouses(string $cityRef, string $typeRef = '', int $limit = 500, int $page = 1): array
    {
        $params = [
            'CityRef' => $cityRef,
            'Limit' => (string) $limit,
            'Page' => (string) $page,
        ];

        if ($typeRef !== '') {
            $params['TypeOfWarehouseRef'] = $typeRef;
        }

        return $this->callApi('Address', 'getWarehouses', $params);
    }

    /**
     * Пошук відділень за рядком
     */
    public function searchWarehouses(string $cityRef, string $query): array
    {
        return $this->callApi('Address', 'getWarehouses', [
            'CityRef' => $cityRef,
            'FindByString' => $query,
        ]);
    }

    /**
     * Типи відділень (відділення, поштомат, вантажне)
     */
    public function getWarehouseTypes(): array
    {
        return $this->callApi('Address', 'getWarehouseTypes');
    }

    /**
     * Отримати вулиці міста (модель AddressGeneral, не Address — Address повертає
     * порожній набір для FindByString-пошуку без settlementRef).
     */
    public function getStreets(string $cityRef, string $query): array
    {
        return $this->callApi('AddressGeneral', 'getStreet', [
            'CityRef' => $cityRef,
            'FindByString' => $query,
            'Page' => '1',
            'Limit' => '20',
        ]);
    }

    // ==========================================
    // Shipment (InternetDocument) Methods
    // ==========================================

    /**
     * Створити ТТН (експрес-накладну)
     */
    public function createShipment(array $data): array
    {
        return $this->callApi('InternetDocument', 'save', $data);
    }

    /**
     * Оновити ТТН
     */
    public function updateShipment(array $data): array
    {
        return $this->callApi('InternetDocument', 'update', $data);
    }

    /**
     * Видалити ТТН
     */
    public function deleteShipment(string $documentRef): array
    {
        return $this->callApi('InternetDocument', 'delete', [
            'DocumentRefs' => $documentRef,
        ]);
    }

    /**
     * Отримати список ТТН за датами
     */
    public function getShipmentDocuments(string $dateFrom, string $dateTo): array
    {
        return $this->callApi('InternetDocument', 'getDocumentList', [
            'DateTimeFrom' => $dateFrom,
            'DateTimeTo' => $dateTo,
        ]);
    }

    /**
     * Отримати статус відправлення за ТТН
     */
    public function getShipmentTrackingInfo(string $ttn): array
    {
        return $this->callApi('TrackingDocument', 'getStatusDocuments', [
            'Documents' => [
                ['DocumentNumber' => $ttn, 'Phone' => ''],
            ],
        ]);
    }

    /**
     * Send SMS to recipient about TTN status (NP gateway).
     * Phone format: +380XXXXXXXXX
     */
    public function sendSmsNotification(string $documentNumber, string $phone): array
    {
        return $this->callApi('AdditionalServiceGeneral', 'CheckPossibilityForRedirecting', [
            'Number' => $documentNumber,
            'Phone' => $phone,
        ]);
    }

    /**
     * Trigger NP-side SMS notification for a TTN.
     * NP sends own SMS to recipient when TTN arrives at branch.
     * (Default behavior of NP — toggle in admin cabinet.)
     * For custom message use saveSms.
     */
    public function saveSms(string $documentNumber, string $message): array
    {
        return $this->callApi('Tracking', 'sendSmsForRecipient', [
            'Number' => $documentNumber,
            'Message' => $message,
        ]);
    }

    /**
     * Create scan-sheet (registry) for a list of shipment Refs.
     */
    public function createScanSheet(array $documentRefs, $date = null): array
    {
        return $this->callApi('ScanSheet', 'insertDocuments', [
            'DocumentRefs' => $documentRefs,
            'Date' => $date ? \Illuminate\Support\Carbon::parse($date)->format('d.m.Y') : null,
        ]);
    }

    /**
     * Delete scan-sheet by Ref.
     */
    public function deleteScanSheet(string $scanSheetRef): array
    {
        return $this->callApi('ScanSheet', 'deleteScanSheet', [
            'ScanSheetRefs' => [$scanSheetRef],
        ]);
    }

    /**
     * Bulk-track up to 100 documents in single request.
     * @param array $documents [['DocumentNumber' => 'ttn', 'Phone' => '']]
     */
    public function trackDocuments(array $documents): array
    {
        return $this->callApi('TrackingDocument', 'getStatusDocuments', [
            'Documents' => $documents,
        ]);
    }

    /**
     * Отримати історію статусів ТТН
     */
    public function getShipmentStatusHistory(string $ttn): array
    {
        return $this->callApi('TrackingDocument', 'getStatusDocuments', [
            'Documents' => [
                ['DocumentNumber' => $ttn, 'Phone' => ''],
            ],
            'GetFullList' => 1,
        ]);
    }

    // ==========================================
    // Cost Calculation
    // ==========================================

    /**
     * Розрахувати вартість доставки
     *
     * @param  array  $params  Keys: CitySender, CityRecipient, Weight, ServiceType, Cost, CargoType, SeatsAmount
     */
    public function calculateShippingCost(array $params): array
    {
        $defaults = [
            'ServiceType' => 'WarehouseWarehouse',
            'CargoType' => config('services.nova_poshta.default_cargo_type', 'Parcel'),
            'Weight' => config('services.nova_poshta.default_weight', 0.5),
            'SeatsAmount' => 1,
        ];

        return $this->callApi('InternetDocument', 'getDocumentPrice', array_merge($defaults, $params));
    }

    // ==========================================
    // Delivery Date
    // ==========================================

    /**
     * Отримати орієнтовну дату доставки
     */
    public function getEstimatedDeliveryDate(string $citySender, string $cityRecipient, string $serviceType = 'WarehouseWarehouse'): array
    {
        return $this->callApi('InternetDocument', 'getDocumentDeliveryDate', [
            'CitySender' => $citySender,
            'CityRecipient' => $cityRecipient,
            'ServiceType' => $serviceType,
            'DateTime' => now()->format('d.m.Y'),
        ]);
    }

    // ==========================================
    // Counterparties
    // ==========================================

    /**
     * Отримати контрагентів
     */
    public function getCounterparties(string $type = 'Recipient', int $page = 1): array
    {
        return $this->callApi('Counterparty', 'getCounterparties', [
            'CounterpartyProperty' => $type,
            'Page' => $page,
        ]);
    }

    /**
     * Створити контакт контрагента
     */
    public function createCounterpartyContact(array $data): array
    {
        return $this->callApi('Counterparty', 'save', $data);
    }

    /**
     * Отримати контактних осіб контрагента
     */
    public function getCounterpartyContactPersons(string $counterpartyRef, int $page = 1): array
    {
        return $this->callApi('Counterparty', 'getCounterpartyContactPersons', [
            'Ref' => $counterpartyRef,
            'Page' => $page,
        ]);
    }

    /**
     * Sender addresses (warehouses) for a counterparty.
     */
    public function getCounterpartyAddresses(string $counterpartyRef, string $type = 'Sender'): array
    {
        return $this->callApi('Counterparty', 'getCounterpartyAddresses', [
            'Ref' => $counterpartyRef,
            'CounterpartyProperty' => $type,
        ]);
    }

    /**
     * Отримати інформацію про відправника (по API ключу)
     */
    public function getSenderInfo(): array
    {
        return $this->callApi('Counterparty', 'getCounterparties', [
            'CounterpartyProperty' => 'Sender',
            'Page' => 1,
        ]);
    }

    // ==========================================
    // Print
    // ==========================================

    /**
     * Отримати URL друку маркування
     *
     * @param  array  $documentRefs  Масив Ref документів
     */
    public function getPrintMarkup(array $documentRefs, string $type = 'pdf'): string
    {
        $refs = implode(',', $documentRefs);

        return rtrim($this->apiUrl, '/json/')."/../printMarkup/{$type}/{$refs}";
    }

    /**
     * Отримати URL друку документа (ТТН)
     *
     * @param  array  $documentRefs  Масив Ref документів
     */
    public function getPrintDocument(array $documentRefs): string
    {
        $refs = implode(',', $documentRefs);

        return rtrim($this->apiUrl, '/json/')."/../printDocument/pdf/{$refs}";
    }

    // ==========================================
    // References
    // ==========================================

    /**
     * Типи вантажу
     */
    public function getCargoTypes(): array
    {
        return $this->callApi('Common', 'getCargoTypes');
    }

    /**
     * Типи упаковок
     */
    public function getPackTypes(): array
    {
        return $this->callApi('Common', 'getPackList');
    }

    /**
     * Форми оплати
     */
    public function getPaymentForms(): array
    {
        return $this->callApi('Common', 'getPaymentForms');
    }

    /**
     * Типи послуг доставки
     */
    public function getServiceTypes(): array
    {
        return $this->callApi('Common', 'getServiceTypes');
    }

    /**
     * Типи платників
     */
    public function getTypesOfPayers(): array
    {
        return $this->callApi('Common', 'getTypesOfPayers');
    }

    // ==========================================
    // Core API Call
    // ==========================================

    /**
     * Виконати запит до API Нової Пошти
     */
    private function callApi(string $model, string $method, array $properties = []): array
    {
        if (empty($this->apiKey)) {
            Log::error('Nova Poshta API key is not configured');
            $this->persistApiLog($model, $method, $properties, null, false, null, ['API key is not configured'], 0);

            return [
                'success' => false,
                'data' => [],
                'errors' => ['API key is not configured'],
                'warnings' => [],
            ];
        }

        $startedAt = microtime(true);

        try {
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->retry(2, 1000)
                ->post($this->apiUrl, [
                    'apiKey' => $this->apiKey,
                    'modelName' => $model,
                    'calledMethod' => $method,
                    'methodProperties' => $properties,
                ]);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            if (! $response->successful()) {
                Log::error('Nova Poshta API HTTP error', [
                    'status' => $response->status(),
                    'model' => $model,
                    'method' => $method,
                ]);
                $this->persistApiLog($model, $method, $properties, null, false, $response->status(), ["HTTP error: {$response->status()}"], $durationMs);

                return [
                    'success' => false,
                    'data' => [],
                    'errors' => ["HTTP error: {$response->status()}"],
                    'warnings' => [],
                ];
            }

            $data = $response->json();

            if (! ($data['success'] ?? false)) {
                Log::warning('Nova Poshta API error', [
                    'model' => $model,
                    'method' => $method,
                    'errors' => $data['errors'] ?? [],
                    'warnings' => $data['warnings'] ?? [],
                ]);
            }

            $this->persistApiLog($model, $method, $properties, $data, (bool) ($data['success'] ?? false), $response->status(), $data['errors'] ?? [], $durationMs, $data['warnings'] ?? []);

            return $data;

        } catch (\Exception $e) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            Log::error('Nova Poshta API exception', [
                'model' => $model,
                'method' => $method,
                'message' => $e->getMessage(),
            ]);
            $this->persistApiLog($model, $method, $properties, null, false, null, [$e->getMessage()], $durationMs);

            return [
                'success' => false,
                'data' => [],
                'errors' => [$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    /**
     * Persist API call to np_api_logs when debug mode is on OR call failed.
     * Failed calls are always logged so the admin can diagnose later;
     * successful calls only when np_debug_mode is enabled.
     */
    private function persistApiLog(string $model, string $method, array $properties, ?array $response, bool $success, ?int $httpStatus, array $errors, int $durationMs, array $warnings = []): void
    {
        try {
            if (! \Schema::hasTable('shipping_api_logs')) {
                return;
            }

            $debugOn = (bool) \App\Models\DisplaySetting::get('np_debug_mode', false);
            if ($success && ! $debugOn) {
                return;
            }

            $caller = '';
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
                $cls = $frame['class'] ?? '';
                $fn = $frame['function'] ?? '';
                if ($cls === self::class || $fn === 'persistApiLog' || $fn === 'callApi') {
                    continue;
                }
                if ($cls && $fn) {
                    $caller = "{$cls}::{$fn}";
                    break;
                }
            }

            \App\Models\ShippingApiLog::create([
                'provider' => 'novaposhta',
                'endpoint_model' => $model,
                'endpoint_method' => $method,
                'success' => $success,
                'http_status' => $httpStatus,
                'duration_ms' => $durationMs,
                'request_payload' => $properties,
                'response_payload' => $response,
                'errors' => $errors ?: null,
                'warnings' => $warnings ?: null,
                'caller' => $caller ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist NP API log: '.$e->getMessage());
        }
    }
}
