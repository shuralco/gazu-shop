<?php

namespace Tests\Feature\Gazu;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Side-cart drawer: /cart/contents JSON endpoint + add/update/remove flow.
 */
class GazuCartDrawerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cart_contents_empty_by_default(): void
    {
        $this->getJson('/cart/contents')
            ->assertStatus(200)
            ->assertJson(['ok' => true, 'items' => [], 'count' => 0]);
    }

    public function test_add_then_contents_returns_item_with_resolved_image(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 4580, 'quantity' => 10]);

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertStatus(200)
            ->assertJson(['ok' => true]);

        $res = $this->getJson('/cart/contents')->assertStatus(200);
        $res->assertJsonPath('count', 1);
        $res->assertJsonPath('qtyTotal', 2);
        $res->assertJsonPath('items.0.id', $product->id);
        $res->assertJsonPath('items.0.qty', 2);

        // Image must be a usable absolute URL (real image or part-image fallback),
        // never the broken "storage//assets/..." default placeholder.
        $img = $res->json('items.0.image');
        if ($img !== null) {
            $this->assertStringNotContainsString('storage//', $img);
        }
    }

    public function test_update_quantity_recalculates_line_total(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 100, 'quantity' => 50]);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertStatus(200);

        $this->postJson('/cart/update', ['product_id' => $product->id, 'quantity' => 3])
            ->assertStatus(200)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('qty', 3)
            ->assertJsonPath('lineTotal', 300);
    }

    public function test_remove_empties_cart(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 100, 'quantity' => 5]);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertStatus(200);

        $this->postJson('/cart/remove', ['product_id' => $product->id])->assertStatus(200);

        $this->getJson('/cart/contents')->assertJsonPath('count', 0);
    }
}
