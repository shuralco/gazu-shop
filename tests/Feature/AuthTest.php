<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_register_page_loads(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_account_requires_auth(): void
    {
        $response = $this->get('/account');
        $response->assertRedirect('/login');
    }

    public function test_legal_pages_load(): void
    {
        $this->get('/privacy')->assertStatus(200);
        $this->get('/terms')->assertStatus(200);
        $this->get('/returns')->assertStatus(200);
        $this->get('/offer')->assertStatus(200);
    }
}
