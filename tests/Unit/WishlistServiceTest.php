<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WishlistServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private WishlistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WishlistService::class);
    }

    public function test_toggle_adds_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $result = $this->service->toggle($user, $product->id);

        $this->assertTrue($result);
        $this->assertTrue($this->service->isInWishlist($user, $product->id));
    }

    public function test_toggle_removes_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->service->toggle($user, $product->id);
        $result = $this->service->toggle($user, $product->id);

        $this->assertFalse($result);
        $this->assertFalse($this->service->isInWishlist($user, $product->id));
    }

    public function test_count(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            $this->service->toggle($user, $product->id);
        }

        $this->assertEquals(3, $this->service->getCount($user));
    }

    public function test_wishlist_is_per_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        $this->service->toggle($user1, $product->id);

        $this->assertTrue($this->service->isInWishlist($user1, $product->id));
        $this->assertFalse($this->service->isInWishlist($user2, $product->id));
    }
}
