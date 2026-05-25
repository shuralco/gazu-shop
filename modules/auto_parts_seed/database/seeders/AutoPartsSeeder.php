<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo catalog for auto-parts shop.
 *
 * Idempotent: skips already-existing categories/brands/products by slug.
 * Creates inventory rows in the default warehouse.
 *
 * Usage:
 *   php artisan db:seed --class=AutoPartsSeeder
 */
class AutoPartsSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = MerchantWarehouse::default();
        if (! $warehouse) {
            $this->command->error('No default warehouse found. Run `php artisan shop:init` first.');

            return;
        }

        $this->command->info('Seeding auto-parts catalog...');

        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $this->seedProducts($categories, $brands, $warehouse);

        $this->command->info('Done!');
    }

    private function seedCategories(): array
    {
        $defs = [
            ['title' => 'Акумулятори', 'slug' => 'auto-batteries'],
            ['title' => 'Фільтри', 'slug' => 'auto-filters'],
            ['title' => 'Гальмівні колодки', 'slug' => 'brake-pads'],
            ['title' => 'Шини', 'slug' => 'tires'],
            ['title' => 'Моторні масла', 'slug' => 'engine-oils'],
            ['title' => 'Свічки запалювання', 'slug' => 'spark-plugs'],
            ['title' => 'Аксесуари', 'slug' => 'auto-accessories'],
        ];

        // After the translatable migration both `slug` and `title` are
        // JSON columns ({"uk":"..."}). Eloquent's firstOrCreate(['slug' =>
        // 'auto-batteries']) doesn't match the JSON-wrapped value, so
        // every re-seed creates a new duplicate. Match by JSON-unwrapped
        // title via in-memory dedup instead.
        $existingCats = Category::query()->select(['id', 'title', 'slug'])->get();
        $catByTitle = [];
        foreach ($existingCats as $row) {
            $key = mb_strtolower(trim($this->unwrapJson((string) $row->title)));
            $catByTitle[$key] = $row;
        }

        $map = [];
        foreach ($defs as $i => $def) {
            $key = mb_strtolower(trim($def['title']));
            $cat = $catByTitle[$key] ?? null;
            if (! $cat) {
                $cat = Category::create([
                    'slug' => $def['slug'],
                    'title' => $def['title'],
                    'is_active' => true,
                    'sort_order' => $i + 1,
                    'parent_id' => null,
                ]);
                $catByTitle[$key] = $cat;
            }
            $map[$def['slug']] = $cat->id;
            $this->command->line("  ✓ Category: {$def['title']}");
        }

        return $map;
    }

    private function unwrapJson(string $val): string
    {
        if ($val === '' || $val[0] !== '{') return $val;
        $decoded = json_decode($val, true);
        if (! is_array($decoded)) return $val;
        return (string) ($decoded['uk'] ?? $decoded['en'] ?? reset($decoded) ?? '');
    }

    private function seedBrands(): array
    {
        $defs = [
            ['name' => 'Bosch', 'slug' => 'bosch'],
            ['name' => 'Mann Filter', 'slug' => 'mann-filter'],
            ['name' => 'Brembo', 'slug' => 'brembo'],
            ['name' => 'Michelin', 'slug' => 'michelin'],
            ['name' => 'Castrol', 'slug' => 'castrol'],
            ['name' => 'NGK', 'slug' => 'ngk'],
            ['name' => 'Mobil 1', 'slug' => 'mobil-1'],
            ['name' => 'Continental', 'slug' => 'continental'],
            ['name' => 'Varta', 'slug' => 'varta'],
        ];

        // Same JSON-aware lookup as categories.
        $existingBrands = Brand::query()->select(['id', 'name', 'slug'])->get();
        $brandByName = [];
        foreach ($existingBrands as $row) {
            $key = mb_strtolower(trim($this->unwrapJson((string) $row->name)));
            $brandByName[$key] = $row;
        }

        $map = [];
        foreach ($defs as $i => $def) {
            $key = mb_strtolower(trim($def['name']));
            $b = $brandByName[$key] ?? null;
            if (! $b) {
                $b = Brand::create([
                    'slug' => $def['slug'],
                    'name' => $def['name'],
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]);
                $brandByName[$key] = $b;
            }
            $map[$def['slug']] = $b->id;
            $this->command->line("  ✓ Brand: {$def['name']}");
        }

        return $map;
    }

    private function seedProducts(array $categories, array $brands, MerchantWarehouse $warehouse): void
    {
        $defs = [
            // Акумулятори
            ['title' => 'Акумулятор Varta Blue Dynamic 60 Ah', 'cat' => 'auto-batteries', 'brand' => 'varta', 'price' => 3450, 'old_price' => 3800, 'qty' => 12, 'is_hit' => true],
            ['title' => 'Акумулятор Bosch S5 silver 74 Ah', 'cat' => 'auto-batteries', 'brand' => 'bosch', 'price' => 4690, 'old_price' => 5100, 'qty' => 8, 'is_new' => true],
            ['title' => 'Акумулятор Varta Black Dynamic 45 Ah', 'cat' => 'auto-batteries', 'brand' => 'varta', 'price' => 2390, 'qty' => 20],

            // Фільтри
            ['title' => 'Фільтр оливний Mann W 712/95', 'cat' => 'auto-filters', 'brand' => 'mann-filter', 'price' => 320, 'qty' => 50, 'is_hit' => true],
            ['title' => 'Фільтр повітряний Mann C 27 154/1', 'cat' => 'auto-filters', 'brand' => 'mann-filter', 'price' => 580, 'qty' => 35],
            ['title' => 'Фільтр салону Bosch P 3796 (вугільний)', 'cat' => 'auto-filters', 'brand' => 'bosch', 'price' => 740, 'qty' => 28],
            ['title' => 'Фільтр паливний Bosch F 026 402 851', 'cat' => 'auto-filters', 'brand' => 'bosch', 'price' => 890, 'qty' => 15],

            // Гальмівні колодки
            ['title' => 'Гальмівні колодки Brembo P 06 030 (передні)', 'cat' => 'brake-pads', 'brand' => 'brembo', 'price' => 1980, 'qty' => 18, 'is_hit' => true],
            ['title' => 'Гальмівні колодки Brembo P 23 142 (задні)', 'cat' => 'brake-pads', 'brand' => 'brembo', 'price' => 1450, 'qty' => 22],
            ['title' => 'Гальмівні колодки Bosch BP1290', 'cat' => 'brake-pads', 'brand' => 'bosch', 'price' => 1290, 'old_price' => 1500, 'qty' => 30, 'is_new' => true],

            // Шини
            ['title' => 'Michelin Primacy 4 205/55 R16 91V', 'cat' => 'tires', 'brand' => 'michelin', 'price' => 4250, 'qty' => 16, 'is_hit' => true],
            ['title' => 'Michelin CrossClimate 2 215/55 R17 98W', 'cat' => 'tires', 'brand' => 'michelin', 'price' => 5890, 'qty' => 8],
            ['title' => 'Continental ContiPremiumContact 5 195/65 R15 91H', 'cat' => 'tires', 'brand' => 'continental', 'price' => 3290, 'qty' => 24],
            ['title' => 'Continental WinterContact TS 870 205/55 R16 91T', 'cat' => 'tires', 'brand' => 'continental', 'price' => 3850, 'qty' => 12, 'is_new' => true],

            // Моторні масла
            ['title' => 'Castrol Edge 5W-30 LL 4л', 'cat' => 'engine-oils', 'brand' => 'castrol', 'price' => 1890, 'qty' => 45, 'is_hit' => true],
            ['title' => 'Castrol Magnatec 10W-40 4л', 'cat' => 'engine-oils', 'brand' => 'castrol', 'price' => 1290, 'qty' => 60],
            ['title' => 'Mobil 1 ESP 5W-30 5л', 'cat' => 'engine-oils', 'brand' => 'mobil-1', 'price' => 2650, 'old_price' => 2900, 'qty' => 32],

            // Свічки
            ['title' => 'Свічки запалювання NGK ILZKAR7B11 (4 шт.)', 'cat' => 'spark-plugs', 'brand' => 'ngk', 'price' => 1640, 'qty' => 38],
            ['title' => 'Свічки запалювання Bosch FR7DPP30T (4 шт.)', 'cat' => 'spark-plugs', 'brand' => 'bosch', 'price' => 980, 'qty' => 55, 'is_new' => true],
        ];

        // Build lookup of existing products by JSON-unwrapped title.
        // Same translatable-JSON workaround as categories+brands above.
        $existingProducts = Product::query()->select(['id', 'title'])->get();
        $productByTitle = [];
        foreach ($existingProducts as $row) {
            $key = mb_strtolower(trim($this->unwrapJson((string) $row->title)));
            $productByTitle[$key] = $row;
        }

        foreach ($defs as $def) {
            $titleKey = mb_strtolower(trim($def['title']));
            if (isset($productByTitle[$titleKey])) {
                continue;
            }

            $slug = Str::slug($def['title']);
            $product = Product::create([
                'title' => $def['title'],
                'slug' => $slug,
                'sku' => 'AP-'.strtoupper(Str::random(6)),
                'category_id' => $categories[$def['cat']],
                'brand_id' => $brands[$def['brand']] ?? null,
                'price' => $def['price'],
                'old_price' => $def['old_price'] ?? 0,
                'quantity' => $def['qty'],
                'stock_status' => 'in_stock',
                'min_quantity' => 1,
                'is_hit' => $def['is_hit'] ?? false,
                'is_new' => $def['is_new'] ?? false,
                'is_active' => true,
                'content' => '<p>Оригінальна автозапчастина <strong>'.$def['title'].'</strong>. Висока якість, тривалий ресурс роботи, підходить для більшості сучасних авто. Доставка по всій Україні Новою Поштою або УкрПоштою.</p>',
            ]);
            $productByTitle[$titleKey] = $product;

            // Mirror to multi-warehouse inventory
            Inventory::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $def['qty'],
                'reserved_quantity' => 0,
            ]);

            $this->command->line("  ✓ Product: {$def['title']} ({$def['qty']} шт.)");
        }
    }
}
