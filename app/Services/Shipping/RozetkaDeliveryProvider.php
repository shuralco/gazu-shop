<?php

namespace App\Services\Shipping;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RozetkaDeliveryProvider
{
    private Client $client;

    private string $apiUrl;

    private string $apiKey;

    private bool $sandbox;

    private string $username;

    private string $password;

    public function __construct()
    {
        $this->apiUrl = config('rozetka.api_url', 'https://api-seller.rozetka.com.ua/');
        $this->apiKey = config('rozetka.api_key') ?? '';
        $this->username = config('rozetka.username') ?? '';
        $this->password = config('rozetka.password') ?? '';
        $this->sandbox = config('rozetka.sandbox', true);

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 30,
            'verify' => ! $this->sandbox,
            'http_errors' => false,
        ]);
    }

    private function getAuthToken(): ?string
    {
        $cacheKey = 'rozetka_auth_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        if (empty($this->username) || empty($this->password)) {
            Log::error('Rozetka username or password not configured');

            return null;
        }

        try {
            $response = $this->client->post('auth/login', [
                'json' => [
                    'phone' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
                Log::error('Rozetka authentication failed', [
                    'status' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                ]);

                return null;
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $token = $data['access_token'] ?? null;

            if ($token) {
                Cache::put($cacheKey, $token, now()->addHours(23));
            }

            return $token;

        } catch (GuzzleException $e) {
            Log::error('Rozetka authentication error: '.$e->getMessage());

            return null;
        }
    }

    private function makeAuthenticatedRequest(string $method, string $endpoint, array $options = []): ?\Psr\Http\Message\ResponseInterface
    {
        $token = $this->getAuthToken();

        if (! $token) {
            return null;
        }

        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        return $this->client->request($method, $endpoint, $options);
    }

    public function getCities(?string $search = null): Collection
    {
        try {
            $params = [];
            if ($search) {
                $params['search'] = $search;
            }

            $response = $this->makeAuthenticatedRequest('GET', 'cities', [
                'query' => $params,
            ]);

            if (! $response || $response->getStatusCode() !== 200) {
                Log::warning('Rozetka API cities request failed', [
                    'status' => $response?->getStatusCode(),
                    'body' => $response?->getBody()?->getContents(),
                    'endpoint' => 'cities',
                    'search' => $search,
                    'api_url' => $this->apiUrl,
                ]);

                return collect([]);
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $cities = $data['data'] ?? $data ?? [];

            if (empty($cities)) {
                Log::info('Rozetka API returned empty cities list', ['search' => $search]);

                return collect([]);
            }

            return collect($cities)->map(function ($city) {
                return [
                    'id' => $city['id'] ?? $city['city_id'] ?? null,
                    'name' => $city['name'] ?? $city['city_name'] ?? 'Unknown',
                    'region' => $city['region'] ?? $city['region_name'] ?? null,
                    'ref' => $city['id'] ?? $city['city_id'] ?? null,
                ];
            });

        } catch (GuzzleException $e) {
            Log::error('Rozetka cities error: '.$e->getMessage());

            return collect([]);
        }
    }

    public function getPickupPoints(int $cityId): Collection
    {
        try {
            $response = $this->makeAuthenticatedRequest('GET', "cities/{$cityId}/pickup-points");

            if (! $response || $response->getStatusCode() !== 200) {
                Log::warning('Rozetka API pickup points request failed', [
                    'status' => $response?->getStatusCode(),
                    'city_id' => $cityId,
                    'body' => $response?->getBody()?->getContents(),
                    'endpoint' => "cities/{$cityId}/pickup-points",
                    'api_url' => $this->apiUrl,
                ]);

                return collect([]);
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $points = $data['data'] ?? $data ?? [];

            if (empty($points)) {
                Log::info('Rozetka API returned empty pickup points list', ['city_id' => $cityId]);

                return collect([]);
            }

            return collect($points)->map(function ($point) {
                return [
                    'id' => $point['id'] ?? $point['pickup_point_id'] ?? null,
                    'name' => $point['name'] ?? $point['pickup_point_name'] ?? 'Пункт видачі',
                    'address' => $point['address'] ?? $point['full_address'] ?? '',
                    'phone' => $point['phone'] ?? $point['contact_phone'] ?? null,
                    'working_hours' => $point['working_hours'] ?? $point['schedule'] ?? null,
                ];
            });

        } catch (GuzzleException $e) {
            Log::error('Rozetka pickup points error: '.$e->getMessage());

            return collect([]);
        }
    }

    public function calculateShippingCost($order, array $destination): float
    {
        try {
            $payload = [
                'city_id' => $destination['city_id'] ?? null,
                'pickup_point_id' => $destination['pickup_point_id'] ?? null,
                'weight' => $this->calculateWeight($order),
                'dimensions' => $this->calculateDimensions($order),
                'declared_value' => $order->total ?? 0,
                'payment_method' => $destination['payment_method'] ?? 'cash',
            ];

            $response = $this->makeAuthenticatedRequest('POST', 'calculate-shipping', [
                'json' => $payload,
            ]);

            if (! $response || $response->getStatusCode() !== 200) {
                Log::warning('Rozetka shipping calculation failed', [
                    'status' => $response?->getStatusCode(),
                    'payload' => $payload,
                ]);

                return 45.0; // fallback cost
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return (float) ($data['cost'] ?? $data['price'] ?? 45.0);

        } catch (GuzzleException $e) {
            Log::error('Rozetka shipping calculation error: '.$e->getMessage());

            return 45.0;
        }
    }

    public function createShipment($order, array $destination): ?string
    {
        try {
            $payload = [
                'order_id' => $order->id,
                'recipient' => [
                    'name' => $order->first_name.' '.$order->last_name,
                    'phone' => $order->phone,
                    'email' => $order->email,
                ],
                'destination' => $destination,
                'items' => $this->formatItems($order),
                'payment_method' => $order->payment_method,
                'declared_value' => $order->total,
            ];

            $response = $this->client->post('shipments', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['tracking_number'] ?? null;

        } catch (GuzzleException $e) {
            Log::error('Rozetka create shipment error: '.$e->getMessage());

            return null;
        }
    }

    public function trackShipment(string $trackingNumber): array
    {
        try {
            $response = $this->client->get("shipments/{$trackingNumber}/track");

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['tracking_info'] ?? [];

        } catch (GuzzleException $e) {
            Log::error('Rozetka track shipment error: '.$e->getMessage());

            return [];
        }
    }

    private function calculateWeight($order): float
    {
        if (method_exists($order, 'orderProducts')) {
            return $order->orderProducts->sum(function ($item) {
                return ($item->product->weight ?? 0.5) * $item->quantity;
            });
        }

        return 1.0; // Default weight
    }

    private function calculateDimensions($order): array
    {
        return [
            'length' => 20,
            'width' => 15,
            'height' => 10,
        ];
    }

    private function formatItems($order): array
    {
        if (method_exists($order, 'orderProducts')) {
            return $order->orderProducts->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'weight' => $item->product->weight ?? 0.5,
                ];
            })->toArray();
        }

        return [];
    }
}
