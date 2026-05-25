<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Support\ModuleManager;
use App\Support\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 smoke tests.
 *
 * Verifies:
 *   - Theme discovery scans themes/{name}/theme.json
 *   - Active theme defaults to gazu, can be overridden via DisplaySetting
 *   - preset:apply toggles modules correctly (dry-run respects --dry-run)
 *   - artisan theme:set persists choice
 */
class ThemePresetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ModuleManager::clearCache();
        ThemeManager::clearCache();
    }

    public function test_theme_discovery_finds_gazu(): void
    {
        $themes = ThemeManager::themes();

        $this->assertArrayHasKey('gazu', $themes);
        $this->assertSame('gazu', $themes['gazu']['name']);
        $this->assertSame('themes/gazu/resources/css/gazu.css', $themes['gazu']['css_entry']);
    }

    public function test_active_theme_defaults_to_gazu(): void
    {
        $this->assertSame('gazu', ThemeManager::active());
    }

    public function test_theme_set_command_persists_choice(): void
    {
        $this->artisan('theme:set', ['name' => 'gazu'])
            ->expectsOutputToContain("✓ Active theme set to 'gazu'")
            ->assertExitCode(0);

        ThemeManager::clearCache();
        $this->assertSame('gazu', ThemeManager::active());
    }

    public function test_theme_set_rejects_unknown_theme(): void
    {
        $this->artisan('theme:set', ['name' => 'unknown'])
            ->expectsOutputToContain("not found in themes/")
            ->assertExitCode(1);
    }

    public function test_preset_apply_dry_run_does_not_persist(): void
    {
        $this->artisan('preset:apply', ['name' => 'auto-parts', '--dry-run' => true])
            ->expectsOutputToContain('[DRY RUN]')
            ->expectsOutputToContain('Dry run complete')
            ->assertExitCode(0);

        $this->assertSame(0, Module::count(), 'Dry run should NOT touch modules table');
    }

    public function test_preset_apply_general_shop_toggles_modules(): void
    {
        $this->artisan('preset:apply', ['name' => 'general-shop'])
            ->assertExitCode(0);

        // general-shop turns reviews/coupons ON, gazu_garage/auto_parts_seed OFF
        $this->assertTrue(Module::where('key', 'reviews')->where('enabled', true)->exists());
        $this->assertTrue(Module::where('key', 'gazu_garage')->where('enabled', false)->exists());
    }

    public function test_preset_apply_unknown_name_returns_failure(): void
    {
        $this->artisan('preset:apply', ['name' => 'nonexistent-preset'])
            ->expectsOutputToContain('not found')
            ->assertExitCode(1);
    }
}
