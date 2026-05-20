<?php

namespace Tests\Feature\Gazu;

use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Category;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies the OpenCart-style taxonomy enrichment: image/description columns,
 * SEO meta persistence, and car-taxonomy SEO fields.
 */
class GazuTaxonomyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_categories_table_has_image_and_description_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('categories', 'image'), 'categories.image missing');
        $this->assertTrue(Schema::hasColumn('categories', 'description'), 'categories.description missing');
    }

    public function test_category_seo_meta_persists(): void
    {
        $cat = Category::create([
            'title' => 'Гальмівна система',
            'slug' => 'brakes-test',
            'is_active' => true,
            'meta_title' => 'Гальма — GAZU',
            'meta_description' => 'Купити гальмівні колодки та диски.',
            'description' => '<p>Опис категорії гальм.</p>',
        ]);

        $fresh = Category::find($cat->id);
        $this->assertSame('Гальма — GAZU', $fresh->meta_title);
        $this->assertSame('Купити гальмівні колодки та диски.', $fresh->meta_description);
        $this->assertStringContainsString('Опис категорії', (string) $fresh->description);
    }

    public function test_car_make_has_seo_columns_and_persists(): void
    {
        foreach (['meta_title', 'meta_description', 'description'] as $col) {
            $this->assertTrue(Schema::hasColumn('car_makes', $col), "car_makes.$col missing");
        }

        $make = CarMake::create([
            'slug' => 'byd', 'name' => 'BYD', 'is_active' => true, 'sort_order' => 1,
            'meta_title' => 'Запчастини BYD',
            'meta_description' => 'Оригінали та аналоги BYD.',
        ]);

        $this->assertSame('Запчастини BYD', CarMake::find($make->id)->meta_title);
    }

    public function test_car_make_logo_url_accessor(): void
    {
        $make = CarMake::create([
            'slug' => 'mg', 'name' => 'MG', 'is_active' => true,
            'logo_path' => '/img/car-makes/mg.svg',
        ]);
        $this->assertStringContainsString('/img/car-makes/mg.svg', $make->logo_url);

        $none = CarMake::create(['slug' => 'jac', 'name' => 'JAC', 'is_active' => true]);
        $this->assertNull($none->logo_url);
    }

    public function test_car_model_has_seo_columns(): void
    {
        foreach (['meta_title', 'meta_description', 'description'] as $col) {
            $this->assertTrue(Schema::hasColumn('car_models', $col), "car_models.$col missing");
        }
    }
}
