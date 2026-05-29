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
        // Registration is part of the combined auth page at /login
        // (standalone GET /register was removed in the "/uk storefront"
        // cleanup; only POST /register remains for the form submit).
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('action="'.route('gazu.auth.register').'"', false);
    }

    public function test_account_requires_auth(): void
    {
        // Canonical cabinet URL is /kabinet; guests are bounced to /login.
        $response = $this->get('/kabinet');
        $response->assertRedirect('/login');

        // Legacy /account 301s to the canonical /kabinet (SEO redirect chain:
        // /account -> 301 -> /kabinet -> 302 -> /login).
        $this->get('/account')->assertRedirect('/kabinet');
    }

    public function test_legal_pages_load(): void
    {
        // /returns was folded into /warranty ("Гарантія та повернення") during
        // the "/uk storefront" cleanup; the standalone route no longer exists.
        $this->get('/privacy')->assertStatus(200);
        $this->get('/terms')->assertStatus(200);
        $this->get('/warranty')->assertStatus(200);
        $this->get('/offer')->assertStatus(200);
    }
}
