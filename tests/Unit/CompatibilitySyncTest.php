<?php

namespace Tests\Unit;

use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Product;
use App\Services\Gazu\CompatibilitySync;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Тест фічі «Додати всі варіації» (договір Подолужний): синк адмінського JSON
 * products.compatibility → pivot product_compatibility (compatibleEngines), який
 * читає фільтр підбору по авто.
 */
class CompatibilitySyncTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** Створює марку + модель + N двигунів. @return array{CarModel, list<CarEngine>} */
    private function makeVehicle(string $make, string $model, array $engines): array
    {
        $mk = CarMake::create(['slug' => \Str::slug($make), 'name' => $make, 'is_active' => true]);
        $md = CarModel::create(['make_id' => $mk->id, 'slug' => \Str::slug($model), 'name' => $model, 'is_active' => true]);
        $engs = [];
        foreach ($engines as $e) {
            $engs[] = CarEngine::create([
                'model_id' => $md->id, 'code' => $e['code'], 'label' => $e['label'],
                'is_active' => $e['active'] ?? true,
            ]);
        }

        return [$md, $engs];
    }

    public function test_all_engines_flag_links_every_engine_of_model(): void
    {
        [$model, $engs] = $this->makeVehicle('Volkswagen', 'ID Unyx 06', [
            ['code' => 'AWD', 'label' => '80,2 kWh Max / Ultra'],
            ['code' => 'RWD Pure', 'label' => '54 kWh Pure'],
            ['code' => 'RWD Pro', 'label' => '80,2 kWh Pro'],
        ]);

        $product = Product::factory()->create(['compatibility' => [
            ['make' => 'Volkswagen', 'model' => 'ID Unyx 06', 'years' => '2024-', 'engine' => '', 'all_engines' => true],
        ]]);

        CompatibilitySync::syncProduct($product);

        $linked = $product->compatibleEngines()->pluck('car_engines.id')->sort()->values()->all();
        $this->assertEquals(collect($engs)->pluck('id')->sort()->values()->all(), $linked, 'галочка «усі варіації» має злінкувати ВСІ двигуни моделі');
        $this->assertCount(3, $linked);
    }

    public function test_empty_engine_is_treated_as_all_engines(): void
    {
        [, $engs] = $this->makeVehicle('BYD', 'Han', [
            ['code' => 'EV', 'label' => 'EV'],
            ['code' => 'DM-i', 'label' => 'DM-i'],
        ]);

        // Без прапорця, але й БЕЗ двигуна → рівень моделі = всі двигуни.
        $product = Product::factory()->create(['compatibility' => [
            ['make' => 'BYD', 'model' => 'Han', 'years' => '2022-', 'engine' => ''],
        ]]);
        CompatibilitySync::syncProduct($product);

        $this->assertCount(2, $product->compatibleEngines()->get());
    }

    public function test_specific_engine_links_only_that_engine(): void
    {
        [, $engs] = $this->makeVehicle('Volkswagen', 'ID 4 Crozz', [
            ['code' => 'RWD', 'label' => '45–82 kWh Pure / Pro'],
            ['code' => 'AWD', 'label' => '82 kWh Prime AWD'],
        ]);

        $product = Product::factory()->create(['compatibility' => [
            ['make' => 'Volkswagen', 'model' => 'ID 4 Crozz', 'years' => '2021-', 'engine' => '82 kWh Prime AWD'],
        ]]);
        CompatibilitySync::syncProduct($product);

        $linked = $product->compatibleEngines()->pluck('car_engines.id')->all();
        $this->assertEquals([$engs[1]->id], $linked, 'конкретний двигун → лише він');
    }

    public function test_dash_normalization_matches_specific_engine(): void
    {
        // У БД en-dash «45–82», у введенні клієнта звичайний дефіс «45-82».
        [, $engs] = $this->makeVehicle('Volkswagen', 'ID 4 Crozz', [
            ['code' => 'RWD', 'label' => '45–82 kWh Pure / Pro'],
        ]);

        $product = Product::factory()->create(['compatibility' => [
            ['make' => 'Volkswagen', 'model' => 'ID 4 Crozz', 'years' => '2021-', 'engine' => '45-82 kWh Pure / Pro'],
        ]]);
        CompatibilitySync::syncProduct($product);

        $this->assertEquals([$engs[0]->id], $product->compatibleEngines()->pluck('car_engines.id')->all(), 'дефіс vs тире мають збігатись');
    }

    public function test_unknown_model_links_nothing_and_does_not_crash(): void
    {
        $product = Product::factory()->create(['compatibility' => [
            ['make' => 'Tesla', 'model' => 'Model Z', 'years' => '2030-', 'engine' => '', 'all_engines' => true],
        ]]);
        CompatibilitySync::syncProduct($product);

        $this->assertCount(0, $product->compatibleEngines()->get());
    }
}
