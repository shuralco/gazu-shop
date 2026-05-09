<?php

namespace App\Services;

use App\Models\DisplaySetting;
use App\Models\ShippingApiLog;
use App\Models\UpShipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * UkrPoshta eCom client (TTN creation, status, archive).
 *
 * Auth model:
 *   - Bearer header: ecom_bearer (issued per contract)
 *   - Query string: ?token=user_token (counterparty token)
 *
 * Endpoints used:
 *   GET  /ecom/0.0.1/clients/{uuid}                         — counterparty info
 *   POST /ecom/0.0.1/shipments                              — create shipment (TTN)
 *   GET  /ecom/0.0.1/shipments/{uuid}                       — get shipment
 *   DELETE /ecom/0.0.1/shipments/{uuid}                     — cancel
 *   GET  /status-tracking/0.0.1/statuses/last?barcode=...   — last status (uses tracking_bearer)
 */
class UkrPoshtaEcomService
{
    private string $base = 'https://www.ukrposhta.ua/ecom/0.0.1';

    private string $trackingBase = 'https://www.ukrposhta.ua/status-tracking/0.0.1';

    private ?\App\Models\MerchantWarehouse $warehouse = null;

    public function __construct() {}

    /**
     * Bind a warehouse — overrides DisplaySetting fallbacks for sender/credentials.
     * Used by UkrPoshtaTtnCreator when an order's warehouse is known.
     */
    public function forWarehouse(?\App\Models\MerchantWarehouse $warehouse): self
    {
        $clone = clone $this;
        $clone->warehouse = $warehouse;

        return $clone;
    }

    public function getEcomBearer(): string
    {
        return (string) ($this->warehouse?->up_ecom_bearer
            ?: DisplaySetting::get('up_ecom_bearer', ''));
    }

    public function getTrackingBearer(): string
    {
        return (string) DisplaySetting::get('up_tracking_bearer', '');
    }

    public function getUserToken(): string
    {
        return (string) ($this->warehouse?->up_counterparty_token
            ?: DisplaySetting::get('up_counterparty_token', ''));
    }

    public function getSenderUuid(): string
    {
        return (string) ($this->warehouse?->up_sender_uuid
            ?: DisplaySetting::get('up_sender_uuid', ''));
    }

    public function getSenderAddressUuid(): string
    {
        return (string) ($this->warehouse?->up_sender_address_uuid
            ?: DisplaySetting::get('up_sender_address_uuid', ''));
    }

    /**
     * Get counterparty info by UUID.
     */
    public function getCounterparty(string $uuid): array
    {
        return $this->get("/clients/{$uuid}");
    }

    /**
     * Create a new recipient client (counterparty).
     *
     * @return array{success:bool,uuid:?string,errors:array}
     */
    public function createClient(array $payload): array
    {
        $r = $this->post('/clients', $payload);

        return [
            'success' => $r['success'] && ! empty($r['response']['uuid']),
            'uuid' => $r['response']['uuid'] ?? null,
            'errors' => $r['errors'],
        ];
    }

    /**
     * Create a shipment (TTN). Recipient is created on the fly from address fields.
     *
     * @param  array  $payload  Pre-built shipment payload (sender, recipient, parcels, options).
     * @return array{success:bool,uuid:?string,barcode:?string,errors:array,response:array}
     */
    public function createShipment(array $payload): array
    {
        $r = $this->post('/shipments', $payload);

        if ($r['success'] && ! empty($r['response']['uuid'])) {
            return [
                'success' => true,
                'uuid' => $r['response']['uuid'],
                'barcode' => $r['response']['barcode'] ?? null,
                'errors' => [],
                'response' => $r['response'],
            ];
        }

        return [
            'success' => false,
            'uuid' => null,
            'barcode' => null,
            'errors' => $r['errors'],
            'response' => $r['response'] ?? [],
        ];
    }

    /**
     * Build UkrPoshta-API payload from a stored UpShipment model.
     */
    /**
     * Create address (POST /addresses) — first step before client creation.
     *
     * @return array{success:bool, address_id:?int, errors:array}
     */
    public function createAddress(array $payload): array
    {
        $r = $this->post('/addresses', $payload);

        return [
            'success' => $r['success'] && ! empty($r['response']['id']),
            'address_id' => $r['response']['id'] ?? null,
            'errors' => $r['errors'],
        ];
    }

    /**
     * Build address payload for POST /addresses.
     */
    public function buildAddressPayload(UpShipment $sh): array
    {
        $cityName = $sh->recipient_city_name ?: '';
        $regionName = '';
        if ($sh->recipient_city_id) {
            $city = \App\Models\UpCity::find((int) $sh->recipient_city_id);
            if ($city) {
                $cityName = $cityName ?: $city->name_ua;
                $region = \App\Models\UpRegion::find((int) $city->region_id);
                $regionName = $region?->name_ua ?? '';
            }
        }

        return array_filter([
            'postcode' => $sh->recipient_postcode ?: '',
            'region' => $regionName,
            'city' => $cityName,
            'street' => $sh->recipient_street ?: ($sh->recipient_branch_address ?: 'до запитання'),
            'houseNumber' => $sh->recipient_building ?: '1',
            'apartmentNumber' => $sh->recipient_apartment ?: '',
        ], fn ($v) => $v !== '' && $v !== null);
    }

    /**
     * Build a recipient-client payload referencing an existing addressId.
     */
    public function buildClientPayload(UpShipment $sh, int $addressId): array
    {
        return [
            'lastName' => $this->extractLastName($sh->recipient_name),
            'firstName' => $this->extractFirstName($sh->recipient_name),
            'phoneNumber' => $this->normalizePhone($sh->recipient_phone),
            'type' => 'INDIVIDUAL',
            'addresses' => [['addressId' => $addressId]],
        ];
    }

    public function buildPayloadFromShipment(UpShipment $sh): array
    {
        $weightGrams = (int) round((float) $sh->weight * 1000);

        // Resolve city info for the recipient address block
        $cityName = $sh->recipient_city_name ?: '';
        $regionName = '';
        if ($sh->recipient_city_id) {
            $city = \App\Models\UpCity::find((int) $sh->recipient_city_id);
            if ($city) {
                $cityName = $cityName ?: $city->name_ua;
                $region = \App\Models\UpRegion::find((int) $city->region_id);
                $regionName = $region?->name_ua ?? '';
            }
        }

        $address = [
            'postcode' => $sh->recipient_postcode ?: '',
            'region' => $regionName,
            'city' => $cityName,
            'street' => $sh->recipient_street ?: ($sh->recipient_branch_address ?? ''),
            'houseNumber' => $sh->recipient_building ?: '',
            'apartmentNumber' => $sh->recipient_apartment ?: '',
        ];

        $payload = [
            'sender' => ['uuid' => $this->getSenderUuid()],
            'recipient' => [
                'lastName' => $this->extractLastName($sh->recipient_name),
                'firstName' => $this->extractFirstName($sh->recipient_name),
                'phoneNumber' => $this->normalizePhone($sh->recipient_phone),
                'type' => 'INDIVIDUAL',
                'address' => array_filter($address, fn ($v) => $v !== '' && $v !== null),
            ],
            'deliveryType' => match ($sh->service_type) {
                'courier' => 'W2D',
                'express' => 'D2D',
                default => 'W2W',
            },
            'paidByRecipient' => true,
            'description' => $sh->description ?: "Замовлення #{$sh->order_id}",
            // Parcel dimensions per UkrPoshta:
            //   1 side ≤120, 2 sides ≤70 mm
            //   AND face area (longest × second-longest) ≤2700 mm² for EXPRESS
            // Defaults below stay within both limits (100×25×25, area=2500).
            'parcels' => [[
                'weight' => $weightGrams ?: 500,
                'length' => 100,
                'width' => 25,
                'height' => 25,
                'declaredPrice' => (float) $sh->declared_value,
            ]],
        ];

        if ($sh->cod_amount && $sh->cod_amount > 0) {
            $payload['postPay'] = [
                'amount' => (float) $sh->cod_amount,
                'paymentType' => 'CASH_ON_DELIVERY',
            ];
        }

        return $payload;
    }

    /**
     * Get latest status of a TTN (barcode).
     */
    public function getLastStatus(string $barcode): array
    {
        return $this->get('/statuses/last', ['barcode' => $barcode], $this->trackingBase, $this->getTrackingBearer());
    }

    public function getStatuses(string $barcode): array
    {
        return $this->get('/statuses', ['barcode' => $barcode], $this->trackingBase, $this->getTrackingBearer());
    }

    /**
     * Download a PDF sticker for one shipment. Returns binary PDF or empty string.
     *
     * @return array{success:bool,pdf:?string,errors:array}
     */
    public function downloadSticker(string $shipmentUuid, string $size = '85x85'): array
    {
        $endpoint = match ($size) {
            '100x100' => "/shipments/{$shipmentUuid}/sticker.pdf",
            'a4' => "/shipments/{$shipmentUuid}/forms.pdf",
            default => "/shipments/{$shipmentUuid}/sticker.pdf",
        };

        return $this->binary($endpoint, ['size' => $size]);
    }

    /**
     * ScanSheet (registry) operations.
     */
    public function createRegistry(): array
    {
        return $this->post('/registries', [
            'name' => 'Реєстр від '.now()->format('d.m.Y H:i'),
        ]);
    }

    public function addShipmentToRegistry(string $registryUuid, string $shipmentUuid): array
    {
        return $this->post("/registries/{$registryUuid}/shipments/{$shipmentUuid}", []);
    }

    public function removeShipmentFromRegistry(string $registryUuid, string $shipmentUuid): array
    {
        return $this->call('DELETE', "/registries/{$registryUuid}/shipments/{$shipmentUuid}", [], null);
    }

    public function getRegistry(string $registryUuid): array
    {
        return $this->get("/registries/{$registryUuid}");
    }

    public function downloadRegistryForm(string $registryUuid): array
    {
        return $this->binary("/registries/{$registryUuid}/form.pdf");
    }

    private function binary(string $path, array $query = []): array
    {
        $bearer = $this->getEcomBearer();
        if (empty($bearer)) {
            return ['success' => false, 'pdf' => null, 'errors' => ['Bearer not configured']];
        }
        $query['token'] = $this->getUserToken();

        $startedAt = microtime(true);

        try {
            $response = Http::withToken($bearer)
                ->timeout(30)
                ->get($this->base.$path, $query);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $success = $response->successful();
            $errors = $success ? [] : ['HTTP '.$response->status().' '.($response->json('message') ?? '')];

            // Sanitized log (don't store binary PDF content)
            $this->log('GET', $path, $query, null, $success ? ['_binary' => 'pdf', 'bytes' => strlen($response->body())] : $response->json(),
                $success, $response->status(), $errors, $durationMs);

            return [
                'success' => $success,
                'pdf' => $success ? $response->body() : null,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'pdf' => null, 'errors' => [$e->getMessage()]];
        }
    }

    private function get(string $path, array $query = [], ?string $base = null, ?string $bearer = null): array
    {
        return $this->call('GET', $path, $query, null, $base, $bearer);
    }

    private function post(string $path, array $body, ?string $base = null, ?string $bearer = null): array
    {
        return $this->call('POST', $path, [], $body, $base, $bearer);
    }

    private function call(string $method, string $path, array $query, ?array $body, ?string $base = null, ?string $bearer = null): array
    {
        $base = $base ?: $this->base;
        $bearer = $bearer ?: $this->getEcomBearer();

        if (empty($bearer)) {
            return $this->fail(['UkrPoshta bearer not configured'], $path, $query, $body);
        }

        // Tracking endpoints don't need user_token; ecom always does.
        if ($base === $this->base) {
            $query['token'] = $this->getUserToken();
        }

        $startedAt = microtime(true);

        try {
            $req = Http::withToken($bearer)
                ->acceptJson()
                ->timeout(30);

            $url = $base.$path;
            $response = $method === 'POST'
                ? $req->post($url.'?'.http_build_query($query), $body ?? [])
                : $req->get($url, $query);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $data = $response->json() ?: [];
            $success = $response->successful();

            $errors = [];
            if (! $success) {
                $errors[] = $data['message'] ?? "HTTP {$response->status()}";
            }

            $this->log($method, $path, $query, $body, $data, $success, $response->status(), $errors, $durationMs);

            return [
                'success' => $success,
                'response' => is_array($data) ? $data : [],
                'errors' => $errors,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $this->log($method, $path, $query, $body, null, false, null, [$e->getMessage()], $durationMs);

            return $this->fail([$e->getMessage()], $path, $query, $body);
        }
    }

    private function fail(array $errors, string $path, array $query, ?array $body): array
    {
        return [
            'success' => false,
            'response' => [],
            'errors' => $errors,
            'http_status' => null,
        ];
    }

    private function log(string $method, string $path, array $query, ?array $body, ?array $response, bool $success, ?int $httpStatus, array $errors, int $durationMs): void
    {
        try {
            if (! Schema::hasTable('shipping_api_logs')) {
                return;
            }
            $debugOn = (bool) DisplaySetting::get('up_debug_mode', false);
            if ($success && ! $debugOn) {
                return;
            }

            $sanitizedQuery = $query;
            if (isset($sanitizedQuery['token'])) {
                $sanitizedQuery['token'] = '***';
            }

            $caller = '';
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
                $cls = $frame['class'] ?? '';
                $fn = $frame['function'] ?? '';
                if ($cls === self::class) {
                    continue;
                }
                if ($cls && $fn) {
                    $caller = "{$cls}::{$fn}";
                    break;
                }
            }

            ShippingApiLog::create([
                'provider' => 'ukrposhta',
                'endpoint_model' => 'eCom',
                'endpoint_method' => "{$method} {$path}",
                'success' => $success,
                'http_status' => $httpStatus,
                'duration_ms' => $durationMs,
                'request_payload' => ['query' => $sanitizedQuery, 'body' => $body],
                'response_payload' => $response,
                'errors' => $errors ?: null,
                'caller' => $caller ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('UP eCom log failed: '.$e->getMessage());
        }
    }

    private function extractLastName(string $full): string
    {
        $parts = preg_split('/\s+/', trim($full));

        return $parts[0] ?? 'Клієнт';
    }

    private function extractFirstName(string $full): string
    {
        $parts = preg_split('/\s+/', trim($full));

        return $parts[1] ?? 'Замовлення';
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '380')) {
            return '+'.$digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+38'.$digits;
        }

        return $phone;
    }
}
