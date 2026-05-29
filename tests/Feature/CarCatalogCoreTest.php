<?php

namespace Tests\Feature;

use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Product;
use App\Support\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression guard for cff04df0:
 *   "fix(arch): car-каталог міграції → core (storefront ламався при вимкненому gazu_garage)"
 *
 * The vehicle reference tables (car_makes / car_models / car_engines /
 * product_compatibility) were originally created by a migration that lived in
 * modules/gazu_garage/database/migrations/. Because gazu_garage ships DISABLED
 * by default, ModuleDiscovery never registered that migration path → the
 * tables were never created → the homepage car-selector + product compat-check
 * blew up with "no such table: car_makes", taking down the whole storefront.
 *
 * The fix moved those three migrations into the CORE database/migrations/
 * directory so they always run, independent of the gazu_garage toggle. Only
 * user_cars (a genuine garage feature) stayed inside the module.
 *
 * These tests lock that contract in place:
 *   1. gazu_garage is OFF in the test environment (the regression precondition).
 *   2. All four car-catalog tables exist in the schema regardless.
 *   3. The home page renders 200 even with gazu_garage disabled.
 *   4. The Eloquent models + product_compatibility pivot are usable.
 */
class CarCatalogCoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Precondition for every regression assertion below: the bug only manifests
     * when gazu_garage is disabled (its migration path is not registered). If
     * this module were on in the test env, the test would prove nothing.
     */
    public function test_gazu_garage_module_is_disabled_by_default(): void
    {
        $this->assertFalse(
            ModuleManager::for('gazu_garage')->enabled(),
            'gazu_garage must be OFF by default — that is the exact condition '.
            'under which the car-catalog tables used to go missing.'
        );

        $this->assertFalse(
            (bool) config('modules.gazu_garage.enabled'),
            'config default for gazu_garage must remain false.'
        );
    }

    /**
     * The core regression: every vehicle-reference table must be present in the
     * migrated schema even though gazu_garage is disabled. Before the fix, none
     * of these existed because the migration sat in the disabled module.
     *
     * @dataProvider carCatalogTables
     */
    public function test_car_catalog_table_exists_independent_of_gazu_garage(string $table): void
    {
        $this->assertTrue(
            Schema::hasTable($table),
            "Table `{$table}` must exist via a CORE migration, not the gazu_garage ".
            "module — otherwise the storefront breaks when the module is off."
        );
    }

    public static function carCatalogTables(): array
    {
        return [
            'car_makes' => ['car_makes'],
            'car_models' => ['car_models'],
            'car_engines' => ['car_engines'],
            'product_compatibility' => ['product_compatibility'],
        ];
    }

    /**
     * user_cars is a genuine garage feature and intentionally stayed in the
     * module. We do NOT assert on it here because its presence depends on the
     * module being enabled — documenting the boundary the fix drew.
     */
    public function test_core_car_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('car_makes', ['slug', 'name', 'is_active']));
        $this->assertTrue(Schema::hasColumns('car_models', ['make_id', 'slug', 'name']));
        $this->assertTrue(Schema::hasColumns('car_engines', ['model_id', 'code']));
        $this->assertTrue(Schema::hasColumns('product_compatibility', ['product_id', 'engine_id']));
    }

    /**
     * The homepage controller queries CarMake::query() directly to render the
     * hero car-selector. With gazu_garage off and the old (module) migration,
     * this query threw "no such table: car_makes" and the home page 500'd.
     * It must now render 200.
     */
    public function test_homepage_renders_with_gazu_garage_disabled(): void
    {
        $this->assertFalse(
            ModuleManager::for('gazu_garage')->enabled(),
            'Sanity: this test asserts the *disabled* path.'
        );

        $this->get('/')->assertStatus(200);
    }

    /**
     * Same query path, but with actual brand rows seeded — exercises the
     * Cache::remember('home:hero:makes') branch that maps CarMake records into
     * hero tiles, so we know the table is not just present but queryable end
     * to end through the controller.
     */
    public function test_homepage_renders_with_seeded_car_makes(): void
    {
        CarMake::create([
            'slug' => 'chery',
            'name' => 'Chery',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        CarMake::create([
            'slug' => 'haval',
            'name' => 'Haval',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Chery', false);
    }

    /**
     * The Car* Eloquent models live inside modules/gazu_garage/src/Models but
     * are autoloaded via composer classmap under the App\Models namespace, so
     * they resolve regardless of whether the module is enabled. End-to-end:
     * make → model → engine → product_compatibility pivot must all persist and
     * read back through the relations.
     */
    public function test_car_hierarchy_and_compatibility_pivot_are_usable(): void
    {
        $make = CarMake::create([
            'slug' => 'great-wall',
            'name' => 'Great Wall',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $model = CarModel::create([
            'make_id' => $make->id,
            'slug' => 'haval-h6',
            'name' => 'Haval H6',
            'is_active' => true,
        ]);

        $engine = CarEngine::create([
            'model_id' => $model->id,
            'code' => 'GW4G15B',
            'label' => '1.5T',
            'fuel_type' => 'petrol',
            'is_active' => true,
        ]);

        $product = Product::factory()->create(['is_active' => true]);

        // Attach the part to the engine via the product_compatibility pivot.
        $product->compatibleEngines()->attach($engine->id, ['note' => 'до 2020']);

        // Read the relation graph back the way the storefront compat-check does.
        $this->assertSame($make->id, $model->fresh()->make_id);
        $this->assertSame($model->id, $engine->fresh()->model_id);
        $this->assertTrue($make->models()->whereKey($model->id)->exists());

        $fetched = $product->fresh()->compatibleEngines()->first();
        $this->assertNotNull($fetched, 'product_compatibility pivot must round-trip.');
        $this->assertSame($engine->id, $fetched->id);
        $this->assertSame('до 2020', $fetched->getRelationValue('pivot')->note);

        // And the reverse direction (engine → products).
        $this->assertTrue($engine->products()->whereKey($product->id)->exists());

        $this->assertDatabaseHas('product_compatibility', [
            'product_id' => $product->id,
            'engine_id' => $engine->id,
            'note' => 'до 2020',
        ]);
    }
}
