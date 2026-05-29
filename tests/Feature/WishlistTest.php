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

    public function test_wishlist_page_is_public_for_guests(): void
    {
        // Wishlist page is intentionally public: guests build a wishlist in
        // localStorage (client-side) and it merges into the account on login.
        // The page renders an empty wishlist for guests rather than redirecting.
        $response = $this->get('/wishlist');
        $response->assertOk();
    }

    public function test_wishlist_toggle_requires_auth(): void
    {
        // Mutating the server-side wishlist still requires authentication:
        // an AJAX toggle by a guest must be rejected with 401 and redirect hint.
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->postJson('/wishlist/toggle', ['product_id' => $product->id]);

        $response->assertStatus(401);
        $response->assertJson(['ok' => false]);
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
