<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_page_loads(): void
    {
        // Checkout redirects to the empty-cart page when the session cart is
        // empty (correct behaviour). Arrange a cart line so the page renders.
        $response = $this->withSession(['cart' => [
            '1' => [
                'title' => 'Test Product',
                'slug' => 'test-product',
                'image' => null,
                'price' => 100,
                'quantity' => 1,
                'variant_id' => null,
                'warehouse_id' => null,
            ],
        ]])->get('/checkout');

        $response->assertStatus(200);
    }

    public function test_homepage_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_search_page_loads(): void
    {
        $response = $this->get('/search');
        $response->assertStatus(200);
    }
}
