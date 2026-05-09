<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_wishlist_requires_auth(): void
    {
        $response = $this->get('/wishlist');
        // Auth middleware should not allow access - either redirect or error
        $this->assertNotEquals(200, $response->getStatusCode(), 'Wishlist should require authentication');
    }

    public function test_wishlist_model_creation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_remove_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlistItem = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $wishlistItem->delete();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($user->id, $wishlist->user->id);
        $this->assertEquals($product->id, $wishlist->product->id);
    }
}
