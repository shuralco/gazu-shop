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
        // GAZU has no standalone /returns route — returns info is part of the
        // warranty page ("Гарантія та повернення"). /warranty is the page
        // linked in the footer and topbar for return policy.
        $this->get('/warranty')->assertStatus(200);
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
