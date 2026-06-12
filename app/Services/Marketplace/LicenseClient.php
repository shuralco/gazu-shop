<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Клієнт ліцензійного сервера Lionex для магазину розширень.
 *
 * СТУБ-РЕЖИМ (зараз): catalog() віддає config('marketplace.catalog'),
 * purchase() повертає «скоро». Уся мережева логіка інкапсульована тут —
 * коли зʼявиться реальний сервер, достатньо заповнити MARKETPLACE_* env і
 * розкоментувати HTTP-гілки; UI сторінки «Розширення» не зміниться.
 *
 * Купівля в майбутньому: purchase() → сервер віддає підписаний ZIP →
 * \App\Support\Modules\ModuleInstaller::installFromZip() (вже існує).
 */
class LicenseClient
{
    public function isConfigured(): bool
    {
        return filled(config('marketplace.license_key'));
    }

    public function serverUrl(): string
    {
        return rtrim((string) config('marketplace.server_url'), '/');
    }

    /**
     * Каталог доступних-для-встановлення розширень.
     *
     * @return array<int,array{key:string,name:string,description:string,category:string,price:string,icon:string,status:string}>
     */
    public function catalog(): array
    {
        // --- Реальний сервер (вмикається, коли заданий license_key) ---
        if ($this->isConfigured()) {
            try {
                $resp = Http::timeout((int) config('marketplace.timeout', 8))
                    ->withToken((string) config('marketplace.license_key'))
                    ->acceptJson()
                    ->get($this->serverUrl().'/catalog');

                if ($resp->successful() && is_array($resp->json('data'))) {
                    return $resp->json('data');
                }
                Log::warning('[LicenseClient] catalog HTTP '.$resp->status());
            } catch (\Throwable $e) {
                Log::warning('[LicenseClient] catalog failed: '.$e->getMessage());
            }
            // fallthrough на стуб, якщо сервер недоступний
        }

        // --- Стуб-каталог ---
        return (array) config('marketplace.catalog', []);
    }

    /**
     * Придбати/встановити розширення. Зараз — стуб.
     *
     * @return array{ok:bool,message:string}
     */
    public function purchase(string $key): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Купівля з ліцензійного сервера Lionex буде доступна найближчим часом. Підключіть ліцензійний ключ у налаштуваннях.',
            ];
        }

        // TODO (коли зʼявиться сервер): POST /purchase {key} → отримати signed ZIP
        // → ModuleInstaller::installFromZip($zip) → Module::updateOrCreate(enabled=false).
        return [
            'ok' => false,
            'message' => "Розширення «{$key}»: купівля скоро буде доступна.",
        ];
    }
}
