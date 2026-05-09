<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_privacy_page_loads(): void
    {
        $this->get('/privacy')->assertStatus(200);
    }

    public function test_terms_page_loads(): void
    {
        $this->get('/terms')->assertStatus(200);
    }

    public function test_returns_page_loads(): void
    {
        $this->get('/returns')->assertStatus(200);
    }

    public function test_offer_page_loads(): void
    {
        $this->get('/offer')->assertStatus(200);
    }

    public function test_sitemap_loads(): void
    {
        $this->get('/sitemap.xml')->assertStatus(200);
    }
}
