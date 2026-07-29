<?php

namespace Tests\Feature;

use App\Services\NovaPoshtaApiService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Services\Shipping\NovaPoshtaProvider;
use App\Services\Shipping\NovaPoshtaReferenceSync;
use Tests\TestCase;

/**
 * Ключ НП більше не зашитий у config. Магазин без ключа мусить працювати
 * (просто без доставки НП), а не падати TypeError.
 *
 * Пастка, на яку вже наступили: config('novaposhta.api_key', '') віддає НЕ '',
 * а null — бо ключ у конфізі існує, він просто порожній. Другий аргумент
 * config() спрацьовує лише коли ключа немає взагалі.
 */
class NovaPoshtaMissingKeyTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'novaposhta.api_key' => null,
            'services.nova_poshta.api_key' => null,
        ]);

        \App\Models\ShippingProvider::where('code', 'novaposhta')->delete();
    }

    public function test_api_service_builds_without_key(): void
    {
        $this->assertInstanceOf(NovaPoshtaApiService::class, new NovaPoshtaApiService);
    }

    public function test_shipping_provider_builds_without_key(): void
    {
        $this->assertInstanceOf(NovaPoshtaProvider::class, new NovaPoshtaProvider);
    }

    public function test_reference_sync_builds_without_key(): void
    {
        $this->assertInstanceOf(NovaPoshtaReferenceSync::class, new NovaPoshtaReferenceSync);
    }

    public function test_city_search_without_key_sends_no_request(): void
    {
        Http::fake();

        $result = (new NovaPoshtaApiService)->searchCities('Київ');

        // Без ключа сервіс повертає зрозумілу відмову, а не кидає виняток
        $this->assertFalse($result['success']);
        $this->assertSame([], $result['data']);
        Http::assertNothingSent();
    }

    public function test_config_has_no_hardcoded_key(): void
    {
        $this->assertStringNotContainsString(
            '737254fe131eca6c3ab91925ef9eff45',
            file_get_contents(config_path('novaposhta.php'))
        );
    }
}
