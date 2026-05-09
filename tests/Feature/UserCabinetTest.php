<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserCabinetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_account_requires_auth(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_wishlist_requires_auth(): void
    {
        $this->get('/wishlist')->assertRedirect('/login');
    }

    public function test_addresses_requires_auth(): void
    {
        $this->get('/addresses')->assertRedirect('/login');
    }

    public function test_loyalty_requires_auth(): void
    {
        $this->get('/loyalty')->assertRedirect('/login');
    }

    public function test_settings_requires_auth(): void
    {
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_account_loads_for_auth_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/account')->assertStatus(200);
    }

    public function test_orders_loads_for_auth_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/orders')->assertStatus(200);
    }
}
