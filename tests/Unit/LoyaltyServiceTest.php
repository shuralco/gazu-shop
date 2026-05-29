<?php

namespace Tests\Unit;

use App\Models\LoyaltyTier;
use App\Models\Order;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private LoyaltyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // У цьому деплої модуль лояльності вимкнено через env (MODULE_LOYALTY=false),
        // тож App\Support\ModuleDiscovery НЕ реєструє його шлях міграцій і таблиці
        // loyalty_* не створюються у свіжій sqlite :memory:. Сервіс LoyaltyService
        // гейтиться окремим shopSetting('loyalty_enabled', true) (default true), а не
        // module-тогглом, тому його логіка валідна — бракує лише схеми. Реєструємо
        // міграції модуля явно, щоб таблиці існували незалежно від env-тоггла.
        //
        // LazilyRefreshDatabase запускає базові міграції (users, orders, ...) при
        // першому зверненні до БД — Schema::hasTable нижче його тригерить, тож
        // users вже існує до того як loyalty_transactions створює FK на нього.
        if (! Schema::hasTable('loyalty_tiers')) {
            Artisan::call('migrate', [
                '--path' => 'modules/loyalty/database/migrations',
                '--realpath' => false,
                '--force' => true,
            ]);
        }

        $this->service = app(LoyaltyService::class);

        LoyaltyTier::create(['name' => 'bronze', 'display_name' => 'Бронзовий', 'min_points' => 0, 'points_multiplier' => 1.0, 'discount_percentage' => 0, 'is_active' => true, 'sort_order' => 0]);
        LoyaltyTier::create(['name' => 'silver', 'display_name' => 'Срібний', 'min_points' => 1000, 'points_multiplier' => 1.5, 'discount_percentage' => 3, 'is_active' => true, 'sort_order' => 1]);
    }

    public function test_award_points_for_order(): void
    {
        $user = User::factory()->create(['loyalty_points' => 0, 'loyalty_tier' => 'bronze']);
        $order = Order::factory()->create(['user_id' => $user->id, 'total' => 1000]);

        $points = $this->service->awardPoints($user, $order);

        $this->assertGreaterThan(0, $points);
        $this->assertEquals($points, $user->fresh()->loyalty_points);
    }

    public function test_redeem_points(): void
    {
        $user = User::factory()->create(['loyalty_points' => 500]);

        $discount = $this->service->redeemPoints($user, 100);

        $this->assertGreaterThan(0, $discount);
        $this->assertEquals(400, $user->fresh()->loyalty_points);
    }

    public function test_cannot_redeem_more_than_available(): void
    {
        $user = User::factory()->create(['loyalty_points' => 50]);

        $discount = $this->service->redeemPoints($user, 100);

        $this->assertEquals(0, $discount);
    }
}
