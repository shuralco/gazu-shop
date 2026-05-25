<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 1 modularity smoke tests.
 *
 * Verifies:
 *   - ModuleDiscovery scans modules/*\/module.json
 *   - ModuleManager DB → ENV → config waterfall
 *   - Disabled module routes do NOT register
 *   - Enabled module routes DO register
 *   - DB toggle invalidates cache via Module observer
 */
class ModuleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ModuleManager::clearCache();
        ModuleDiscovery::clearCache();
    }

    public function test_module_discovery_finds_pilot_manifest(): void
    {
        $manifests = ModuleDiscovery::manifests();

        $this->assertArrayHasKey('gazu_garage', $manifests, 'Pilot module manifest must be discoverable');
        $this->assertEquals('gazu_garage', $manifests['gazu_garage']['name']);
        $this->assertContains(
            'App\\Filament\\Resources\\UserCarResource',
            $manifests['gazu_garage']['filament_resources'] ?? []
        );
    }

    public function test_module_discovery_skips_underscore_prefixed_dirs(): void
    {
        $manifests = ModuleDiscovery::manifests();

        $this->assertArrayNotHasKey('_example', $manifests);
        $this->assertArrayNotHasKey('example', $manifests);
    }

    public function test_module_manager_db_takes_priority_over_config(): void
    {
        // Config default for gazu_garage is false. DB row with enabled=true should win.
        Module::create(['key' => 'gazu_garage', 'enabled' => true, 'enabled_at' => now()]);
        ModuleManager::clearCache();

        $this->assertTrue(ModuleManager::for('gazu_garage')->enabled());
    }

    public function test_module_manager_db_disable_hides_module(): void
    {
        // Even if config says enabled, DB enabled=false wins.
        Module::create(['key' => 'gazu_garage', 'enabled' => false]);
        ModuleManager::clearCache();

        $this->assertFalse(ModuleManager::for('gazu_garage')->enabled());
    }

    public function test_disabled_module_routes_not_registered(): void
    {
        Module::create(['key' => 'gazu_garage', 'enabled' => false]);
        ModuleManager::clearCache();

        // Reboot app so AppServiceProvider re-runs with fresh state.
        $this->refreshApplication();

        $routes = collect(app('router')->getRoutes())->map->getName()->filter();
        $this->assertFalse($routes->contains('gazu.garage'));
    }

    public function test_enabled_module_routes_register(): void
    {
        // Use config override (resolved at bootModuleResources time) — DB
        // toggle takes effect after refreshApplication, but config is more
        // deterministic in test env.
        config()->set('modules.gazu_garage.enabled', true);
        ModuleManager::clearCache();

        $this->refreshApplication();
        config()->set('modules.gazu_garage.enabled', true);
        ModuleManager::clearCache();
        \App\Support\ModuleDiscovery::bootModuleResources(app());

        $routes = collect(app('router')->getRoutes())->map->getName()->filter();
        $this->assertTrue(
            $routes->contains('gazu.garage'),
            "Expected gazu.garage route, got: ".$routes->implode(', ')
        );
        $this->assertTrue($routes->contains('gazu.garage.store'));
    }

    public function test_observer_clears_cache_on_save(): void
    {
        Module::create(['key' => 'gazu_garage', 'enabled' => false]);
        ModuleManager::clearCache();

        // Prime cache
        $this->assertFalse(ModuleManager::for('gazu_garage')->enabled());

        // Toggle via DB — observer should auto-invalidate
        Module::where('key', 'gazu_garage')->update(['enabled' => true]);
        Module::where('key', 'gazu_garage')->first()->save(); // trigger observer

        $this->assertTrue(ModuleManager::for('gazu_garage')->enabled());
    }
}
