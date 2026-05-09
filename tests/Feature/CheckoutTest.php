<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_page_loads(): void
    {
        $response = $this->get('/checkout');
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
