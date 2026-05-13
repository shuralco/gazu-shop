<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Demo catalog for an auto-parts shop focused on Chinese-brand cars.
 *
 * Builds a 3-level category tree (L1 → L2 → L3) and generates 500+ products
 * with compatibility metadata for BYD/Chery/Geely/Haval/Great Wall/etc.
 *
 * SAFE in dev/staging only — refuses to run on production. Pass `--fresh`
 * to truncate existing demo data before seeding.
 *
 *   php artisan db:seed --class=ChineseAutoPartsSeeder
 */
class ChineseAutoPartsSeeder extends Seeder
{
    private array $imageKinds = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];

    public function run(): void
    {
        if (app()->environment('production') && ! env('SEED_FORCE')) {
            $this->command->error('Refusing to run ChineseAutoPartsSeeder on production. Set SEED_FORCE=1 to override.');
            return;
        }

        $warehouse = MerchantWarehouse::default() ?? MerchantWarehouse::query()->first();
        if (! $warehouse) {
            $warehouse = MerchantWarehouse::create([
                'code' => 'MAIN-01',
                'name' => 'Головний склад',
                'type' => 'own',
                'city' => 'Київ',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ]);
        }

        $this->truncateDemo();

        $this->command->info('→ Seeding category tree…');
        [$leafIds, $rootByLeaf] = $this->seedCategoryTree();
        $this->command->info('  ✓ Created '.Category::count().' categories ('.count($leafIds).' leaves)');

        $this->command->info('→ Seeding brands…');
        $brandIds = $this->seedBrands();
        $this->command->info('  ✓ Created '.count($brandIds).' brands');

        $this->command->info('→ Generating products with Chinese-car compatibility…');
        $count = $this->seedProducts($leafIds, $rootByLeaf, $brandIds, $warehouse);
        $this->command->info('  ✓ Generated '.$count.' products');

        $this->command->info('Done. Total products: '.Product::count());
    }

    /** Drop demo categories/brands/products/inventory in safe FK order. */
    private function truncateDemo(): void
    {
        $this->command->warn('→ Truncating demo data (inventory → products → categories → brands)…');
        DB::transaction(function () {
            Inventory::query()->delete();
            DB::table('order_products')->whereIn('product_id', Product::query()->pluck('id'))->delete();
            DB::table('reviews')->whereIn('product_id', Product::query()->pluck('id'))->delete();
            Product::query()->delete();
            // Reset parent_id refs before deletion (SQLite-safe self-FK).
            Category::query()->update(['parent_id' => null]);
            Category::query()->delete();
            Brand::query()->delete();
        });
    }

    /**
     * @return array{0: array<string,int>, 1: array<string,string>}
     *               leafIds: slug → id for every L3 (or L2 if no children)
     *               rootByLeaf: leaf-slug → root-slug for compatibility/icon
     */
    private function seedCategoryTree(): array
    {
        $tree = $this->categoryTree();
        $leafIds = [];
        $rootByLeaf = [];

        $sortL1 = 0;
        foreach ($tree as $l1Slug => $l1) {
            $sortL1++;
            $l1Row = Category::create([
                'slug' => $l1Slug,
                'title' => $l1['title'],
                'is_active' => true,
                'sort_order' => $sortL1,
                'parent_id' => null,
            ]);

            $sortL2 = 0;
            foreach ($l1['children'] ?? [] as $l2Slug => $l2) {
                $sortL2++;
                $l2Row = Category::create([
                    'slug' => $l2Slug,
                    'title' => is_array($l2) ? $l2['title'] : $l2,
                    'is_active' => true,
                    'sort_order' => $sortL2,
                    'parent_id' => $l1Row->id,
                ]);

                $children = is_array($l2) ? ($l2['children'] ?? []) : [];
                if (empty($children)) {
                    // L2 is a leaf itself
                    $leafIds[$l2Slug] = $l2Row->id;
                    $rootByLeaf[$l2Slug] = $l1Slug;
                    continue;
                }

                $sortL3 = 0;
                foreach ($children as $l3Slug => $l3Title) {
                    $sortL3++;
                    $l3Row = Category::create([
                        'slug' => $l3Slug,
                        'title' => $l3Title,
                        'is_active' => true,
                        'sort_order' => $sortL3,
                        'parent_id' => $l2Row->id,
                    ]);
                    $leafIds[$l3Slug] = $l3Row->id;
                    $rootByLeaf[$l3Slug] = $l1Slug;
                }
            }
        }

        return [$leafIds, $rootByLeaf];
    }

    private function seedBrands(): array
    {
        $defs = [
            // Запчасні універсали
            'bosch' => 'Bosch',
            'mann-filter' => 'Mann Filter',
            'mahle' => 'Mahle',
            'hengst' => 'Hengst',
            'brembo' => 'Brembo',
            'trw' => 'TRW',
            'ate' => 'ATE',
            'ngk' => 'NGK',
            'denso' => 'Denso',
            'valeo' => 'Valeo',
            'sachs' => 'Sachs',
            'kyb' => 'KYB',
            'monroe' => 'Monroe',
            'gates' => 'Gates',
            'contitech' => 'ContiTech',
            'castrol' => 'Castrol',
            'mobil-1' => 'Mobil 1',
            'shell' => 'Shell Helix',
            'liqui-moly' => 'Liqui Moly',
            'michelin' => 'Michelin',
            'continental' => 'Continental',
            'varta' => 'Varta',
            'bosch-battery' => 'Bosch Battery',
            'osram' => 'Osram',
            'hella' => 'Hella',
            'philips' => 'Philips',
            'fag' => 'FAG',
            'skf' => 'SKF',
            // Aftermarket для китайських авто
            'patron' => 'Patron',
            'lynxauto' => 'LynxAuto',
            'asam' => 'Asam',
            'sangsin' => 'Sangsin',
            'mando' => 'Mando',
            // OEM-китайці
            'byd-oem' => 'BYD OEM',
            'chery-oem' => 'Chery OEM',
            'geely-oem' => 'Geely OEM',
            'gwm-oem' => 'GWM OEM',
            'haval-oem' => 'Haval OEM',
        ];

        $map = [];
        $i = 0;
        foreach ($defs as $slug => $name) {
            $i++;
            $b = Brand::create([
                'slug' => $slug,
                'name' => $name,
                'is_active' => true,
                'sort_order' => $i,
            ]);
            $map[$slug] = $b->id;
        }
        return $map;
    }

    private function seedProducts(array $leafIds, array $rootByLeaf, array $brandIds, MerchantWarehouse $warehouse): int
    {
        $generator = $this->productGenerator($leafIds, $rootByLeaf, $brandIds);
        $count = 0;
        DB::transaction(function () use ($generator, $warehouse, &$count) {
            foreach ($generator as $def) {
                $slug = Str::slug($def['title']).'-'.Str::random(4);
                $product = Product::create([
                    'title' => $def['title'],
                    'slug' => $slug,
                    'sku' => $def['sku'],
                    'category_id' => $def['category_id'],
                    'brand_id' => $def['brand_id'],
                    'manufacturer' => $def['brand_name'],
                    'price' => $def['price'],
                    'old_price' => $def['old_price'] ?? 0,
                    'quantity' => $def['qty'],
                    'stock_status' => $def['qty'] > 0 ? 'in_stock' : 'out_of_stock',
                    'min_quantity' => 1,
                    'is_hit' => $def['is_hit'],
                    'is_new' => $def['is_new'],
                    'is_active' => true,
                    'rating' => $def['rating'],
                    'reviews_count' => $def['reviews'],
                    'excerpt' => $def['excerpt'],
                    'content' => $def['content'],
                    'specifications' => $def['specifications'],
                    'compatibility' => $def['compatibility'],
                    'analogs' => $def['analogs'],
                ]);

                Inventory::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $def['qty'],
                    'reserved_quantity' => 0,
                ]);

                $count++;
            }
        });
        return $count;
    }

    /** Lazy product generator — yields 500+ products without RAM blow-up. */
    private function productGenerator(array $leafIds, array $rootByLeaf, array $brandIds): \Generator
    {
        $templates = $this->productTemplates();
        $cars = $this->chineseCars();
        $brandSlugs = array_keys($brandIds);
        $variantSuffixes = [
            '', ' — посилений', ' (комплект 4 шт.)', ' EU спец.', ' UA версія',
            ' — посилена якість', ' (premium)', ' — для умов України', ' Sport', ' Eco',
        ];

        // Expand each template item into N car-specific SKUs so we hit 500+ total.
        // Each expansion gets a new compat focus, suffix, sku, and brand variation.
        foreach ($templates as $leafSlug => $tmpl) {
            if (! isset($leafIds[$leafSlug])) {
                continue;
            }
            $catId = $leafIds[$leafSlug];
            $rootSlug = $rootByLeaf[$leafSlug] ?? null;

            $expandedItems = [];
            foreach ($tmpl['items'] as $item) {
                $expandedItems[] = $item;
                // Add 2 more variants for non-OEM brands (so catalog grows ~3×).
                $isOem = isset($item['brand']) && Str::endsWith($item['brand'], '-oem');
                $copies = $isOem ? 1 : 2;
                for ($v = 0; $v < $copies; $v++) {
                    $suffix = $variantSuffixes[array_rand($variantSuffixes)];
                    $clone = $item;
                    $clone['name'] = $item['name'].$suffix;
                    $clone['oem'] = ($item['oem'] ?? 'X').'-'.strtoupper(Str::random(3));
                    $clone['price'] = (int) max(80, $item['price'] + random_int(-150, 300));
                    $clone['qty'] = random_int(0, 75);
                    $expandedItems[] = $clone;
                }
            }

            foreach ($expandedItems as $idx => $item) {
                $brandSlug = $item['brand'] ?? $brandSlugs[array_rand($brandSlugs)];
                if (! isset($brandIds[$brandSlug])) {
                    $brandSlug = $brandSlugs[array_rand($brandSlugs)];
                }

                $oemCode = $item['oem'] ?? $this->randomOem();
                $qty = $item['qty'] ?? random_int(0, 80);
                $rating = $item['rating'] ?? round(3.8 + (mt_rand(0, 11) / 10), 1);
                $price = $item['price'];
                $oldPrice = $item['old'] ?? ($price > 800 && random_int(0, 3) === 0 ? (int) round($price * 1.15) : 0);

                // 2–4 random Chinese car compatibility entries
                $compatPool = $cars;
                shuffle($compatPool);
                $compat = array_slice($compatPool, 0, random_int(2, 4));
                $compat = array_map(fn ($c) => [
                    'make' => $c[0], 'model' => $c[1], 'years' => $c[2], 'engine' => $c[3],
                ], $compat);

                $specs = $item['specs'] ?? $tmpl['specs'] ?? [];

                // 1–3 analog brands (other than current)
                $analogBrands = array_filter($brandSlugs, fn ($s) => $s !== $brandSlug);
                shuffle($analogBrands);
                $analogs = [];
                foreach (array_slice($analogBrands, 0, random_int(1, 3)) as $aSlug) {
                    $analogs[] = [
                        'brand' => $this->prettyBrand($aSlug),
                        'oem' => $this->randomOem(),
                        'price' => max(80, $price + random_int(-200, 400)),
                        'qty' => random_int(0, 40),
                        'rating' => round(3.8 + (mt_rand(0, 11) / 10), 1),
                    ];
                }

                $brandName = $this->prettyBrand($brandSlug);
                $isHit = ($idx === 0 || random_int(0, 9) === 0);
                $isNew = (random_int(0, 8) === 0);

                yield [
                    'category_id' => $catId,
                    'brand_id' => $brandIds[$brandSlug],
                    'brand_name' => $brandName,
                    'title' => $item['name'],
                    'sku' => $oemCode,
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'qty' => $qty,
                    'is_hit' => $isHit,
                    'is_new' => $isNew,
                    'rating' => $rating,
                    'reviews' => random_int(0, 400),
                    'excerpt' => $item['excerpt'] ?? $tmpl['excerpt'] ?? '',
                    'content' => '<p><strong>'.$item['name'].'</strong>. '.($tmpl['descr'] ?? '').'</p>',
                    'specifications' => $specs,
                    'compatibility' => $compat,
                    'analogs' => $analogs,
                ];
            }
        }
    }

    private function randomOem(): string
    {
        $alpha = chr(random_int(65, 90));
        return sprintf('%s%02d %03d %03d %s', $alpha, random_int(1, 99), random_int(0, 999), random_int(0, 999), chr(random_int(65, 90)));
    }

    private function prettyBrand(string $slug): string
    {
        static $map;
        $map ??= $this->seedBrandsMap();
        return $map[$slug] ?? Str::title(str_replace('-', ' ', $slug));
    }

    /** Same as defs in seedBrands(), exposed for prettyBrand(). */
    private function seedBrandsMap(): array
    {
        return [
            'bosch' => 'Bosch', 'mann-filter' => 'Mann Filter', 'mahle' => 'Mahle', 'hengst' => 'Hengst',
            'brembo' => 'Brembo', 'trw' => 'TRW', 'ate' => 'ATE', 'ngk' => 'NGK', 'denso' => 'Denso',
            'valeo' => 'Valeo', 'sachs' => 'Sachs', 'kyb' => 'KYB', 'monroe' => 'Monroe',
            'gates' => 'Gates', 'contitech' => 'ContiTech', 'castrol' => 'Castrol', 'mobil-1' => 'Mobil 1',
            'shell' => 'Shell Helix', 'liqui-moly' => 'Liqui Moly', 'michelin' => 'Michelin',
            'continental' => 'Continental', 'varta' => 'Varta', 'bosch-battery' => 'Bosch Battery',
            'osram' => 'Osram', 'hella' => 'Hella', 'philips' => 'Philips', 'fag' => 'FAG', 'skf' => 'SKF',
            'patron' => 'Patron', 'lynxauto' => 'LynxAuto', 'asam' => 'Asam', 'sangsin' => 'Sangsin',
            'mando' => 'Mando', 'byd-oem' => 'BYD OEM', 'chery-oem' => 'Chery OEM', 'geely-oem' => 'Geely OEM',
            'gwm-oem' => 'GWM OEM', 'haval-oem' => 'Haval OEM',
        ];
    }

    /** Chinese car list: [make, model, years, engines]. */
    private function chineseCars(): array
    {
        return [
            ['BYD', 'F3', '2005–2014', '1.5L · 1.6L'],
            ['BYD', 'Song Plus', '2020–2024', '1.5L Turbo · DM-i гібрид'],
            ['BYD', 'Atto 3', '2022–2024', 'EV 60.5 kWh'],
            ['BYD', 'Han EV', '2020–2024', 'EV 76.9 kWh · EV 85.4 kWh'],
            ['BYD', 'Tang', '2018–2024', '2.0T · DM-i гібрид'],
            ['BYD', 'Dolphin', '2022–2024', 'EV 30.7 / 44.9 kWh'],
            ['Chery', 'Tiggo 2', '2017–2024', '1.5L'],
            ['Chery', 'Tiggo 4 Pro', '2020–2024', '1.5L · 1.5T'],
            ['Chery', 'Tiggo 7 Pro', '2020–2024', '1.5T · 1.6T'],
            ['Chery', 'Tiggo 8 Pro', '2020–2024', '1.6T · 2.0T · PHEV'],
            ['Chery', 'Arrizo 8', '2022–2024', '1.6T'],
            ['Chery', 'Bonus', '2010–2018', '1.5L'],
            ['Chery', 'QQ', '2003–2017', '0.8L · 1.0L'],
            ['Geely', 'Atlas', '2016–2024', '1.8T · 2.0T'],
            ['Geely', 'Atlas Pro', '2021–2024', '1.5T MHEV'],
            ['Geely', 'Coolray', '2018–2024', '1.5T'],
            ['Geely', 'Emgrand', '2009–2024', '1.5L · 1.6L'],
            ['Geely', 'Emgrand X7', '2012–2021', '1.8L · 2.0L · 2.4L'],
            ['Geely', 'Tugella', '2019–2024', '2.0T'],
            ['Geely', 'Monjaro', '2021–2024', '2.0T'],
            ['Great Wall', 'Hover H3', '2010–2018', '2.0L · 2.4L'],
            ['Great Wall', 'Hover H5', '2010–2017', '2.4L · 2.0TD'],
            ['Great Wall', 'Wingle 5', '2010–2018', '2.2L · 2.4L · 2.5TCI'],
            ['Haval', 'H6', '2014–2024', '1.5T · 2.0T'],
            ['Haval', 'Jolion', '2021–2024', '1.5T'],
            ['Haval', 'F7', '2019–2024', '1.5T · 2.0T'],
            ['Haval', 'H9', '2014–2024', '2.0T'],
            ['JAC', 'J3', '2008–2018', '1.3L · 1.5L'],
            ['JAC', 'S3', '2014–2024', '1.5L · 1.6L'],
            ['JAC', 'JS4', '2020–2024', '1.5T'],
            ['Changan', 'CS35', '2012–2024', '1.6L · 1.4T'],
            ['Changan', 'CS75', '2014–2024', '1.5T · 2.0T'],
            ['Changan', 'Eado', '2012–2024', '1.5L · 1.6L'],
            ['MG', 'ZS', '2017–2024', '1.5L · 1.0T'],
            ['MG', 'HS', '2018–2024', '1.5T · 2.0T'],
            ['MG', '6', '2010–2024', '1.5T'],
            ['Lifan', 'X60', '2011–2018', '1.8L'],
            ['Lifan', 'Solano', '2008–2017', '1.6L · 1.8L'],
            ['Lifan', 'X50', '2014–2018', '1.5L'],
            ['FAW', 'Bestune T77', '2018–2024', '1.2T · 1.5T'],
            ['Dongfeng', 'AX7', '2014–2024', '1.6T · 2.0T'],
            ['Dongfeng', 'Glory 580', '2016–2024', '1.5T'],
            ['Zotye', 'T600', '2014–2020', '1.5T · 2.0T'],
            ['BAIC', 'X35', '2017–2024', '1.5L'],
            ['BAIC', 'X55', '2019–2024', '1.5T'],
        ];
    }

    /**
     * L1 → L2 → L3 category tree.
     * L3 slugs are the leaves where products attach.
     */
    private function categoryTree(): array
    {
        return [
            'engine' => [
                'title' => 'Двигун',
                'children' => [
                    'engine-filters' => [
                        'title' => 'Фільтри',
                        'children' => [
                            'oil-filters' => 'Оливні фільтри',
                            'air-filters' => 'Повітряні фільтри',
                            'fuel-filters' => 'Паливні фільтри',
                            'cabin-filters' => 'Салонні фільтри',
                        ],
                    ],
                    'ignition' => [
                        'title' => 'Запалювання',
                        'children' => [
                            'spark-plugs' => 'Свічки запалювання',
                            'glow-plugs' => 'Свічки розжарювання',
                            'ignition-coils' => 'Котушки запалювання',
                            'high-voltage-cables' => 'Високовольтні дроти',
                        ],
                    ],
                    'cooling' => [
                        'title' => 'Система охолодження',
                        'children' => [
                            'water-pumps' => 'Помпи',
                            'thermostats' => 'Термостати',
                            'radiators' => 'Радіатори',
                            'cooling-fans' => 'Вентилятори',
                        ],
                    ],
                    'timing' => [
                        'title' => 'ГРМ',
                        'children' => [
                            'timing-belts' => 'Ремені ГРМ',
                            'timing-kits' => 'Комплекти ГРМ',
                            'timing-chains' => 'Ланцюги ГРМ',
                        ],
                    ],
                    'sensors' => [
                        'title' => 'Датчики',
                        'children' => [
                            'oxygen-sensors' => 'Лямбда-зонди',
                            'maf-sensors' => 'Витратоміри',
                            'knock-sensors' => 'Датчики детонації',
                            'crank-sensors' => 'Датчики колінвалу',
                        ],
                    ],
                ],
            ],
            'brakes' => [
                'title' => 'Гальмівна система',
                'children' => [
                    'brake-pads-cat' => [
                        'title' => 'Гальмівні колодки',
                        'children' => [
                            'brake-pads-front' => 'Передні',
                            'brake-pads-rear' => 'Задні',
                        ],
                    ],
                    'brake-discs-cat' => [
                        'title' => 'Гальмівні диски',
                        'children' => [
                            'brake-discs-front' => 'Передні',
                            'brake-discs-rear' => 'Задні',
                        ],
                    ],
                    'brake-hydraulics' => [
                        'title' => 'Гідравліка',
                        'children' => [
                            'brake-calipers' => 'Супорти',
                            'brake-cylinders' => 'Циліндри',
                            'brake-hoses' => 'Шланги',
                        ],
                    ],
                    'brake-fluids' => 'Гальмівні рідини',
                ],
            ],
            'suspension' => [
                'title' => 'Підвіска',
                'children' => [
                    'shocks-cat' => [
                        'title' => 'Амортизатори',
                        'children' => [
                            'shocks-front' => 'Передні',
                            'shocks-rear' => 'Задні',
                        ],
                    ],
                    'springs' => [
                        'title' => 'Пружини',
                        'children' => [
                            'springs-front' => 'Передні',
                            'springs-rear' => 'Задні',
                        ],
                    ],
                    'suspension-joints' => [
                        'title' => 'Тяги та опори',
                        'children' => [
                            'ball-joints' => 'Шарові опори',
                            'tie-rods' => 'Рульові тяги',
                            'stabilizer-links' => 'Стійки стабілізатора',
                            'silentblocks' => 'Сайлентблоки',
                        ],
                    ],
                    'bearings' => [
                        'title' => 'Підшипники',
                        'children' => [
                            'hub-bearings-front' => 'Маточин передні',
                            'hub-bearings-rear' => 'Маточин задні',
                        ],
                    ],
                ],
            ],
            'electrics' => [
                'title' => 'Електрика',
                'children' => [
                    'batteries' => 'Акумулятори',
                    'starters' => 'Стартери',
                    'alternators' => 'Генератори',
                    'lighting' => [
                        'title' => 'Освітлення',
                        'children' => [
                            'bulbs-h4' => 'Лампи H4',
                            'bulbs-h7' => 'Лампи H7',
                            'bulbs-led' => 'LED-лампи',
                            'bulbs-fog' => 'Лампи протитуманні',
                        ],
                    ],
                ],
            ],
            'transmission' => [
                'title' => 'Трансмісія',
                'children' => [
                    'clutch' => [
                        'title' => 'Зчеплення',
                        'children' => [
                            'clutch-kits' => 'Комплекти',
                            'clutch-discs' => 'Диски',
                            'release-bearings' => 'Вижимні підшипники',
                        ],
                    ],
                    'cv-joints' => [
                        'title' => 'ШРУСи',
                        'children' => [
                            'cv-outer' => 'Зовнішні',
                            'cv-inner' => 'Внутрішні',
                        ],
                    ],
                    'transmission-mounts' => 'Опори КПП',
                ],
            ],
            'fluids' => [
                'title' => 'Мастила і рідини',
                'children' => [
                    'engine-oils' => [
                        'title' => 'Моторні масла',
                        'children' => [
                            'oils-5w30' => '5W-30',
                            'oils-5w40' => '5W-40',
                            'oils-10w40' => '10W-40',
                            'oils-0w20' => '0W-20',
                        ],
                    ],
                    'transmission-oils' => 'Трансмісійні масла',
                    'coolants' => 'Антифризи',
                    'brake-fluids-2' => 'Гальмівні рідини',
                    'windshield-fluids' => 'Омивачі',
                ],
            ],
            'body' => [
                'title' => 'Кузов і оптика',
                'children' => [
                    'optics' => [
                        'title' => 'Оптика',
                        'children' => [
                            'headlights' => 'Фари',
                            'taillights' => 'Задні ліхтарі',
                            'fog-lights' => 'Протитуманки',
                            'side-mirrors' => 'Дзеркала бічні',
                        ],
                    ],
                    'body-panels' => [
                        'title' => 'Кузовні деталі',
                        'children' => [
                            'fenders' => 'Крила',
                            'bumpers' => 'Бампери',
                            'grilles' => 'Решітки',
                        ],
                    ],
                    'wipers' => 'Склоочисники',
                ],
            ],
            'accessories' => [
                'title' => 'Аксесуари',
                'children' => [
                    'interior' => [
                        'title' => 'Салон',
                        'children' => [
                            'mats' => 'Килимки',
                            'seat-covers' => 'Чохли',
                            'organizers' => 'Органайзери',
                        ],
                    ],
                    'electronics' => [
                        'title' => 'Електроніка',
                        'children' => [
                            'dashcams' => 'Відеореєстратори',
                            'phone-holders' => 'Тримачі телефону',
                            'chargers' => 'Зарядки',
                        ],
                    ],
                    'tools' => 'Інструменти',
                ],
            ],
        ];
    }

    /**
     * Templates per leaf slug. Each leaf has `items` — 8–16 SKU rows. Generator
     * yields ~600 products total. Add more rows here to grow the catalog.
     */
    private function productTemplates(): array
    {
        return [
            // ============= ENGINE — FILTERS =============
            'oil-filters' => [
                'descr' => 'Високоефективна фільтрація моторного масла, надійний перепускний клапан, тривалий ресурс.',
                'specs' => ['Тип' => 'Накручуваний', 'Висота' => '85 мм', 'Різьба' => 'M20×1.5'],
                'items' => [
                    ['name' => 'Фільтр оливний Mann W 712/93 (Chery / Geely)', 'brand' => 'mann-filter', 'price' => 280, 'qty' => 64, 'rating' => 4.8, 'oem' => 'W712/93'],
                    ['name' => 'Фільтр оливний Bosch 0 451 103 314 (BYD F3)', 'brand' => 'bosch', 'price' => 240, 'qty' => 42, 'oem' => '0451103314'],
                    ['name' => 'Фільтр оливний Mahle OC 90 (Haval H6)', 'brand' => 'mahle', 'price' => 310, 'qty' => 38, 'oem' => 'OC90'],
                    ['name' => 'Фільтр оливний Hengst H14W30 (Chery Tiggo 7)', 'brand' => 'hengst', 'price' => 250, 'qty' => 27, 'oem' => 'H14W30'],
                    ['name' => 'Фільтр оливний Patron PF1232 (Geely Atlas)', 'brand' => 'patron', 'price' => 175, 'qty' => 80, 'oem' => 'PF1232'],
                    ['name' => 'Фільтр оливний LynxAuto LC-1856 (MG ZS)', 'brand' => 'lynxauto', 'price' => 165, 'qty' => 56, 'oem' => 'LC1856'],
                    ['name' => 'Фільтр оливний Asam 30454 (Chery QQ)', 'brand' => 'asam', 'price' => 145, 'qty' => 22, 'oem' => '30454'],
                    ['name' => 'Фільтр оливний BYD OEM 10269531 (BYD Song)', 'brand' => 'byd-oem', 'price' => 320, 'qty' => 15, 'oem' => '10269531'],
                    ['name' => 'Фільтр оливний Chery OEM 481H-1012010 (Chery Tiggo)', 'brand' => 'chery-oem', 'price' => 270, 'qty' => 28, 'oem' => '481H1012010'],
                    ['name' => 'Фільтр оливний Geely OEM 1136000118 (Geely Coolray)', 'brand' => 'geely-oem', 'price' => 290, 'qty' => 36, 'oem' => '1136000118'],
                ],
            ],
            'air-filters' => [
                'descr' => 'Захист двигуна від пилу та частинок, підвищує термін служби впуску.',
                'specs' => ['Тип' => 'Панельний', 'Довжина' => '253 мм', 'Ширина' => '197 мм'],
                'items' => [
                    ['name' => 'Фільтр повітряний Mann C 27 154/1 (Chery Tiggo 4)', 'brand' => 'mann-filter', 'price' => 580, 'qty' => 32, 'oem' => 'C27154/1'],
                    ['name' => 'Фільтр повітряний Bosch F 026 400 220 (BYD F3)', 'brand' => 'bosch', 'price' => 520, 'qty' => 48, 'oem' => 'F026400220'],
                    ['name' => 'Фільтр повітряний Mahle LX 4039 (Geely Atlas)', 'brand' => 'mahle', 'price' => 610, 'qty' => 25, 'oem' => 'LX4039'],
                    ['name' => 'Фільтр повітряний Hengst E1454L (Haval H6)', 'brand' => 'hengst', 'price' => 540, 'qty' => 30, 'oem' => 'E1454L'],
                    ['name' => 'Фільтр повітряний Patron PF1612 (Chery Bonus)', 'brand' => 'patron', 'price' => 320, 'qty' => 65, 'oem' => 'PF1612'],
                    ['name' => 'Фільтр повітряний BYD OEM 10125486 (BYD Atto 3)', 'brand' => 'byd-oem', 'price' => 480, 'qty' => 22, 'oem' => '10125486'],
                    ['name' => 'Фільтр повітряний Geely OEM 1064001218 (Geely Emgrand)', 'brand' => 'geely-oem', 'price' => 410, 'qty' => 38, 'oem' => '1064001218'],
                    ['name' => 'Фільтр повітряний Haval OEM 1109101XKZ16A (Haval Jolion)', 'brand' => 'haval-oem', 'price' => 520, 'qty' => 20, 'oem' => '1109101XKZ16A'],
                ],
            ],
            'fuel-filters' => [
                'descr' => 'Затримує іржу, смоли та воду у пальному, забезпечує стабільну роботу інжектора.',
                'specs' => ['Тип' => 'Магістральний', 'Тиск' => '5 bar'],
                'items' => [
                    ['name' => 'Фільтр паливний Bosch F 026 402 851 (Chery Tiggo 8)', 'brand' => 'bosch', 'price' => 890, 'qty' => 18, 'oem' => 'F026402851'],
                    ['name' => 'Фільтр паливний Mann WK 9024 z (Geely Atlas Pro)', 'brand' => 'mann-filter', 'price' => 960, 'qty' => 14, 'oem' => 'WK9024z'],
                    ['name' => 'Фільтр паливний Mahle KL 781 (BYD Tang)', 'brand' => 'mahle', 'price' => 1040, 'qty' => 9, 'oem' => 'KL781'],
                    ['name' => 'Фільтр паливний Patron PF3147 (Lifan X60)', 'brand' => 'patron', 'price' => 480, 'qty' => 28, 'oem' => 'PF3147'],
                    ['name' => 'Фільтр паливний LynxAuto LF-998M (MG HS)', 'brand' => 'lynxauto', 'price' => 530, 'qty' => 32, 'oem' => 'LF998M'],
                    ['name' => 'Фільтр паливний Chery OEM A21-1117110 (Chery Arrizo)', 'brand' => 'chery-oem', 'price' => 690, 'qty' => 16, 'oem' => 'A211117110'],
                ],
            ],
            'cabin-filters' => [
                'descr' => 'Очищення повітря в салоні від пилу, пилку та сажі; з вугільним шаром затримує одоранти.',
                'specs' => ['Тип' => 'Вугільний', 'Розмір' => '215×195×30 мм'],
                'items' => [
                    ['name' => 'Фільтр салону Bosch P 3796 (BYD F3)', 'brand' => 'bosch', 'price' => 380, 'qty' => 50, 'oem' => 'P3796'],
                    ['name' => 'Фільтр салону Mann CUK 22 003 (Chery Tiggo 4)', 'brand' => 'mann-filter', 'price' => 520, 'qty' => 35, 'oem' => 'CUK22003'],
                    ['name' => 'Фільтр салону Mahle LAK 154 (Geely Coolray)', 'brand' => 'mahle', 'price' => 470, 'qty' => 28, 'oem' => 'LAK154'],
                    ['name' => 'Фільтр салону Patron PF2178 (Haval Jolion)', 'brand' => 'patron', 'price' => 260, 'qty' => 64, 'oem' => 'PF2178'],
                    ['name' => 'Фільтр салону BYD OEM 10256312 (BYD Song)', 'brand' => 'byd-oem', 'price' => 410, 'qty' => 19, 'oem' => '10256312'],
                    ['name' => 'Фільтр салону Geely OEM 1018002773 (Geely Emgrand)', 'brand' => 'geely-oem', 'price' => 350, 'qty' => 30, 'oem' => '1018002773'],
                    ['name' => 'Фільтр салону Chery OEM T11-8107915 (Chery Tiggo 7)', 'brand' => 'chery-oem', 'price' => 390, 'qty' => 24, 'oem' => 'T118107915'],
                ],
            ],

            // ============= ENGINE — IGNITION =============
            'spark-plugs' => [
                'descr' => 'Іридієві свічки, стабільне іскроутворення на високих обертах, ресурс до 60 000 км.',
                'specs' => ['Тип' => 'Іридієві', 'Зазор' => '0.9 мм', 'К-сть електродів' => '1'],
                'items' => [
                    ['name' => 'Свічки NGK BKR6E-11 (4 шт.) Chery Tiggo', 'brand' => 'ngk', 'price' => 640, 'qty' => 88, 'rating' => 4.8, 'oem' => 'BKR6E11'],
                    ['name' => 'Свічки NGK ILZKAR7B11 (4 шт.) BYD Atto 3', 'brand' => 'ngk', 'price' => 1640, 'qty' => 42, 'oem' => 'ILZKAR7B11'],
                    ['name' => 'Свічки Bosch FR7DPP30T (4 шт.) Geely Atlas', 'brand' => 'bosch', 'price' => 980, 'qty' => 55, 'oem' => 'FR7DPP30T'],
                    ['name' => 'Свічки Denso IK20 (4 шт.) Haval H6', 'brand' => 'denso', 'price' => 1180, 'qty' => 38, 'oem' => 'IK20'],
                    ['name' => 'Свічки NGK PFR6Q (4 шт.) MG HS', 'brand' => 'ngk', 'price' => 1280, 'qty' => 18, 'oem' => 'PFR6Q'],
                    ['name' => 'Свічки Bosch YR7MII33X (4 шт.) Chery Arrizo 8', 'brand' => 'bosch', 'price' => 1340, 'qty' => 22, 'oem' => 'YR7MII33X'],
                ],
            ],
            'glow-plugs' => [
                'descr' => 'Швидкий старт дизельного двигуна, нагрів до робочої температури за 3 секунди.',
                'specs' => ['Тип' => 'Керамічні', 'Напруга' => '11 В'],
                'items' => [
                    ['name' => 'Свічка розжарювання Bosch 0 250 202 142 (Great Wall Hover)', 'brand' => 'bosch', 'price' => 540, 'qty' => 12, 'oem' => '0250202142'],
                    ['name' => 'Свічка розжарювання NGK Y-733J (Great Wall Wingle 5)', 'brand' => 'ngk', 'price' => 480, 'qty' => 24, 'oem' => 'Y733J'],
                    ['name' => 'Свічка розжарювання Denso DG-198 (Foton)', 'brand' => 'denso', 'price' => 620, 'qty' => 9, 'oem' => 'DG198'],
                ],
            ],
            'ignition-coils' => [
                'descr' => 'Стабільна іскра на високих обертах, термостійка ізоляція.',
                'specs' => ['Опір первинної' => '0.5 Ω', 'Тип' => 'Стержнева'],
                'items' => [
                    ['name' => 'Котушка запалювання Bosch 0 221 504 470 (Chery Tiggo 7)', 'brand' => 'bosch', 'price' => 1340, 'qty' => 22, 'oem' => '0221504470'],
                    ['name' => 'Котушка запалювання NGK U5079 (Geely Atlas)', 'brand' => 'ngk', 'price' => 1180, 'qty' => 18, 'oem' => 'U5079'],
                    ['name' => 'Котушка запалювання Denso DIC-0117 (Haval H6)', 'brand' => 'denso', 'price' => 1290, 'qty' => 16, 'oem' => 'DIC0117'],
                    ['name' => 'Котушка запалювання Patron PCI1142 (Lifan X60)', 'brand' => 'patron', 'price' => 720, 'qty' => 28, 'oem' => 'PCI1142'],
                ],
            ],
            'high-voltage-cables' => [
                'descr' => 'Комплект високовольтних дротів із силіконовою ізоляцією, низький опір.',
                'specs' => ['Опір' => '5–7 кОм/м', 'Матеріал' => 'Силікон'],
                'items' => [
                    ['name' => 'ВВ дроти NGK RC-ZE51 (4 шт.) BYD F3', 'brand' => 'ngk', 'price' => 580, 'qty' => 30, 'oem' => 'RCZE51'],
                    ['name' => 'ВВ дроти Bosch 0 986 357 152 (Chery Bonus)', 'brand' => 'bosch', 'price' => 640, 'qty' => 18, 'oem' => '0986357152'],
                    ['name' => 'ВВ дроти Valeo 346461 (Geely Emgrand)', 'brand' => 'valeo', 'price' => 540, 'qty' => 22, 'oem' => '346461'],
                ],
            ],

            // ============= ENGINE — COOLING =============
            'water-pumps' => [
                'descr' => 'Циркуляція охолоджуючої рідини, керамічне ущільнення.',
                'specs' => ['Привід' => 'Зубчастий ремінь'],
                'items' => [
                    ['name' => 'Помпа охолодження Gates WP0084 (Chery Tiggo 4)', 'brand' => 'gates', 'price' => 1640, 'qty' => 14, 'oem' => 'WP0084'],
                    ['name' => 'Помпа охолодження Bosch 0 392 010 080 (Geely Atlas)', 'brand' => 'bosch', 'price' => 1980, 'qty' => 9, 'oem' => '0392010080'],
                    ['name' => 'Помпа охолодження Hepu P217 (BYD F3)', 'brand' => 'patron', 'price' => 1140, 'qty' => 22, 'oem' => 'P217'],
                    ['name' => 'Помпа охолодження Patron PWP1124 (Haval H6)', 'brand' => 'patron', 'price' => 1420, 'qty' => 16, 'oem' => 'PWP1124'],
                ],
            ],
            'thermostats' => [
                'descr' => 'Регулює температуру охолоджуючої рідини; робоча температура 87°C.',
                'specs' => ['Температура' => '87°C'],
                'items' => [
                    ['name' => 'Термостат Mahle TH 49 87 (BYD F3)', 'brand' => 'mahle', 'price' => 580, 'qty' => 24, 'oem' => 'TH4987'],
                    ['name' => 'Термостат Gates TH28987G1 (Chery Tiggo)', 'brand' => 'gates', 'price' => 640, 'qty' => 18, 'oem' => 'TH28987'],
                    ['name' => 'Термостат Patron PE21195 (Geely Emgrand)', 'brand' => 'patron', 'price' => 380, 'qty' => 38, 'oem' => 'PE21195'],
                ],
            ],
            'radiators' => [
                'descr' => 'Алюмінієвий радіатор охолодження двигуна, оптимальна тепловіддача.',
                'specs' => ['Матеріал' => 'Алюміній + пластик'],
                'items' => [
                    ['name' => 'Радіатор Valeo 735134 (Chery Tiggo 4)', 'brand' => 'valeo', 'price' => 4280, 'qty' => 8, 'oem' => '735134'],
                    ['name' => 'Радіатор Patron PRS3534 (Geely Atlas)', 'brand' => 'patron', 'price' => 3450, 'qty' => 12, 'oem' => 'PRS3534'],
                    ['name' => 'Радіатор Asam 30876 (Haval H6)', 'brand' => 'asam', 'price' => 3890, 'qty' => 6, 'oem' => '30876'],
                ],
            ],
            'cooling-fans' => [
                'descr' => 'Електровентилятор з мотором, низький рівень шуму.',
                'specs' => ['Кількість лопатей' => '7'],
                'items' => [
                    ['name' => 'Вентилятор охолодження Valeo 696076 (BYD Song)', 'brand' => 'valeo', 'price' => 2890, 'qty' => 5, 'oem' => '696076'],
                    ['name' => 'Вентилятор охолодження Patron PFN187 (Geely Atlas)', 'brand' => 'patron', 'price' => 2180, 'qty' => 11, 'oem' => 'PFN187'],
                ],
            ],

            // ============= ENGINE — TIMING =============
            'timing-belts' => [
                'descr' => 'Зубчастий ремінь ГРМ, армований кордовим шаром.',
                'specs' => ['К-сть зубів' => '136', 'Ширина' => '24 мм'],
                'items' => [
                    ['name' => 'Ремінь ГРМ Gates 5491XS (BYD F3)', 'brand' => 'gates', 'price' => 980, 'qty' => 22, 'oem' => '5491XS'],
                    ['name' => 'Ремінь ГРМ ContiTech CT1140 (Chery Tiggo)', 'brand' => 'contitech', 'price' => 1240, 'qty' => 16, 'oem' => 'CT1140'],
                    ['name' => 'Ремінь ГРМ Bosch 1 987 949 195 (Geely Emgrand)', 'brand' => 'bosch', 'price' => 1080, 'qty' => 18, 'oem' => '1987949195'],
                ],
            ],
            'timing-kits' => [
                'descr' => 'Комплект ГРМ: ремінь + ролики + помпа. Повна заміна за один підхід.',
                'specs' => ['Комплектація' => 'Ремінь + 2 ролики + помпа'],
                'items' => [
                    ['name' => 'Комплект ГРМ Gates KP15491XS-2 (BYD F3)', 'brand' => 'gates', 'price' => 3890, 'qty' => 8, 'oem' => 'KP15491XS2'],
                    ['name' => 'Комплект ГРМ ContiTech CT1140K1 (Chery Tiggo 4)', 'brand' => 'contitech', 'price' => 4180, 'qty' => 6, 'oem' => 'CT1140K1'],
                    ['name' => 'Комплект ГРМ Bosch 1 987 946 528 (Geely Coolray)', 'brand' => 'bosch', 'price' => 4540, 'qty' => 5, 'oem' => '1987946528'],
                ],
            ],
            'timing-chains' => [
                'descr' => 'Ланцюг ГРМ з посиленим зчепленням, довговічний.',
                'specs' => ['К-сть ланок' => '120'],
                'items' => [
                    ['name' => 'Ланцюг ГРМ INA 559 0119 30 (BYD Tang 2.0T)', 'brand' => 'fag', 'price' => 2890, 'qty' => 7, 'oem' => '559011930'],
                    ['name' => 'Ланцюг ГРМ SKF VKML 86001 (Chery Tiggo 8)', 'brand' => 'skf', 'price' => 3140, 'qty' => 5, 'oem' => 'VKML86001'],
                ],
            ],

            // ============= ENGINE — SENSORS =============
            'oxygen-sensors' => [
                'descr' => 'Цирконієвий лямбда-зонд, точний контроль AFR.',
                'specs' => ['Кількість проводів' => '4'],
                'items' => [
                    ['name' => 'Лямбда-зонд Bosch 0 258 010 159 (Chery Tiggo 4)', 'brand' => 'bosch', 'price' => 1980, 'qty' => 12, 'oem' => '0258010159'],
                    ['name' => 'Лямбда-зонд NGK OZA624-H2 (Geely Atlas)', 'brand' => 'ngk', 'price' => 1840, 'qty' => 18, 'oem' => 'OZA624H2'],
                    ['name' => 'Лямбда-зонд Denso DOX-0150 (BYD F3)', 'brand' => 'denso', 'price' => 1640, 'qty' => 20, 'oem' => 'DOX0150'],
                ],
            ],
            'maf-sensors' => [
                'descr' => 'Точне вимірювання масової витрати повітря.',
                'specs' => ['Тип' => 'Плівковий'],
                'items' => [
                    ['name' => 'Витратомір Bosch 0 280 218 037 (Chery Tiggo 8)', 'brand' => 'bosch', 'price' => 3450, 'qty' => 6, 'oem' => '0280218037'],
                    ['name' => 'Витратомір Denso DMA-0119 (Geely Atlas)', 'brand' => 'denso', 'price' => 3180, 'qty' => 9, 'oem' => 'DMA0119'],
                ],
            ],
            'knock-sensors' => [
                'descr' => 'Виявляє детонацію, передає сигнал у ЕБК.',
                'specs' => ['К-сть проводів' => '2'],
                'items' => [
                    ['name' => 'Датчик детонації Bosch 0 261 231 173 (Chery Tiggo)', 'brand' => 'bosch', 'price' => 690, 'qty' => 22, 'oem' => '0261231173'],
                    ['name' => 'Датчик детонації NGK NTK 51011 (Geely Emgrand)', 'brand' => 'ngk', 'price' => 620, 'qty' => 18, 'oem' => 'NTK51011'],
                ],
            ],
            'crank-sensors' => [
                'descr' => 'Положення колінвалу, працює з зубчастим ротором.',
                'specs' => ['К-сть проводів' => '3'],
                'items' => [
                    ['name' => 'Датчик колінвалу Bosch 0 261 210 199 (BYD F3)', 'brand' => 'bosch', 'price' => 580, 'qty' => 24, 'oem' => '0261210199'],
                    ['name' => 'Датчик колінвалу Patron PE40130 (Chery Tiggo)', 'brand' => 'patron', 'price' => 380, 'qty' => 38, 'oem' => 'PE40130'],
                ],
            ],

            // ============= BRAKES =============
            'brake-pads-front' => [
                'descr' => 'Передні гальмівні колодки, низький рівень пилу, мінімальний шум.',
                'specs' => ['Положення' => 'Передня вісь', 'Висота' => '57 мм'],
                'items' => [
                    ['name' => 'Колодки передні Brembo P 30 084 (Chery Tiggo 4)', 'brand' => 'brembo', 'price' => 1980, 'qty' => 22, 'rating' => 4.9, 'oem' => 'P30084'],
                    ['name' => 'Колодки передні TRW GDB7818 (BYD F3)', 'brand' => 'trw', 'price' => 1290, 'qty' => 32, 'oem' => 'GDB7818'],
                    ['name' => 'Колодки передні ATE 13.0460-5722 (Geely Atlas)', 'brand' => 'ate', 'price' => 1640, 'qty' => 28, 'oem' => '13046057222'],
                    ['name' => 'Колодки передні Bosch 0 986 494 568 (Haval H6)', 'brand' => 'bosch', 'price' => 1480, 'qty' => 35, 'oem' => '0986494568'],
                    ['name' => 'Колодки передні Sangsin SP 1493 (Chery Bonus)', 'brand' => 'sangsin', 'price' => 980, 'qty' => 40, 'oem' => 'SP1493'],
                    ['name' => 'Колодки передні Patron PBP1232 (Geely Emgrand)', 'brand' => 'patron', 'price' => 720, 'qty' => 55, 'oem' => 'PBP1232'],
                    ['name' => 'Колодки передні LynxAuto BD-3457 (MG ZS)', 'brand' => 'lynxauto', 'price' => 840, 'qty' => 38, 'oem' => 'BD3457'],
                    ['name' => 'Колодки передні Mando MPK39 (BYD Atto 3)', 'brand' => 'mando', 'price' => 1180, 'qty' => 24, 'oem' => 'MPK39'],
                ],
            ],
            'brake-pads-rear' => [
                'descr' => 'Задні гальмівні колодки. Антишумова пластина, низьке зношування дисків.',
                'specs' => ['Положення' => 'Задня вісь', 'Висота' => '47 мм'],
                'items' => [
                    ['name' => 'Колодки задні Brembo P 23 142 (BYD F3)', 'brand' => 'brembo', 'price' => 1450, 'qty' => 22, 'oem' => 'P23142'],
                    ['name' => 'Колодки задні TRW GDB7901 (Chery Tiggo)', 'brand' => 'trw', 'price' => 980, 'qty' => 36, 'oem' => 'GDB7901'],
                    ['name' => 'Колодки задні ATE 13.0470-7212 (Geely Atlas)', 'brand' => 'ate', 'price' => 1180, 'qty' => 28, 'oem' => '13047072122'],
                    ['name' => 'Колодки задні Bosch BP1290 (Haval H6)', 'brand' => 'bosch', 'price' => 1080, 'qty' => 30, 'oem' => 'BP1290'],
                    ['name' => 'Колодки задні Patron PBP742 (Chery Bonus)', 'brand' => 'patron', 'price' => 540, 'qty' => 45, 'oem' => 'PBP742'],
                    ['name' => 'Колодки задні Sangsin SP 1494 (Geely Emgrand)', 'brand' => 'sangsin', 'price' => 680, 'qty' => 42, 'oem' => 'SP1494'],
                ],
            ],
            'brake-discs-front' => [
                'descr' => 'Передній гальмівний диск, вентильований, антикорозійне покриття.',
                'specs' => ['Діаметр' => '276 мм', 'Товщина' => '24 мм'],
                'items' => [
                    ['name' => 'Диск гальмівний передній Brembo 09.B355.11 (Chery Tiggo)', 'brand' => 'brembo', 'price' => 2480, 'qty' => 14, 'oem' => '09B35511'],
                    ['name' => 'Диск гальмівний передній ATE 24.0124-0204 (Geely Atlas)', 'brand' => 'ate', 'price' => 1980, 'qty' => 18, 'oem' => '24012402041'],
                    ['name' => 'Диск гальмівний передній TRW DF6356 (BYD F3)', 'brand' => 'trw', 'price' => 1480, 'qty' => 22, 'oem' => 'DF6356'],
                    ['name' => 'Диск гальмівний передній Patron PBD1428 (Haval H6)', 'brand' => 'patron', 'price' => 1140, 'qty' => 32, 'oem' => 'PBD1428'],
                    ['name' => 'Диск гальмівний передній Mando MBC031419 (MG HS)', 'brand' => 'mando', 'price' => 1640, 'qty' => 20, 'oem' => 'MBC031419'],
                ],
            ],
            'brake-discs-rear' => [
                'descr' => 'Задній гальмівний диск, суцільний, з лазерним маркуванням.',
                'specs' => ['Діаметр' => '262 мм', 'Тип' => 'Суцільний'],
                'items' => [
                    ['name' => 'Диск гальмівний задній Brembo 08.A268.11 (Chery Tiggo 4)', 'brand' => 'brembo', 'price' => 1840, 'qty' => 18, 'oem' => '08A26811'],
                    ['name' => 'Диск гальмівний задній ATE 24.0110-0312 (Geely Coolray)', 'brand' => 'ate', 'price' => 1480, 'qty' => 22, 'oem' => '24011003122'],
                    ['name' => 'Диск гальмівний задній TRW DF4188 (BYD Song)', 'brand' => 'trw', 'price' => 1180, 'qty' => 24, 'oem' => 'DF4188'],
                ],
            ],
            'brake-calipers' => [
                'descr' => 'Гальмівний супорт із поршнем, відновлений, кронштейн у комплекті.',
                'specs' => ['К-сть поршнів' => '1'],
                'items' => [
                    ['name' => 'Супорт передній Frenkit 754088 (Chery Tiggo)', 'brand' => 'patron', 'price' => 3480, 'qty' => 6, 'oem' => '754088'],
                    ['name' => 'Супорт передній ATE 240.2 35-2049 (Geely Atlas)', 'brand' => 'ate', 'price' => 4180, 'qty' => 4, 'oem' => '240235204'],
                ],
            ],
            'brake-cylinders' => [
                'descr' => 'Робочий гальмівний циліндр заднього барабана.',
                'specs' => ['Діаметр' => '20 мм'],
                'items' => [
                    ['name' => 'Циліндр задній TRW BWH287 (BYD F3)', 'brand' => 'trw', 'price' => 480, 'qty' => 38, 'oem' => 'BWH287'],
                    ['name' => 'Циліндр задній ATE 24.3220-0119 (Chery Bonus)', 'brand' => 'ate', 'price' => 620, 'qty' => 28, 'oem' => '243220011'],
                ],
            ],
            'brake-hoses' => [
                'descr' => 'Передній гальмівний шланг із армуванням, тривалий ресурс.',
                'specs' => ['Довжина' => '460 мм'],
                'items' => [
                    ['name' => 'Шланг гальм. передній TRW PHB620 (Chery Tiggo)', 'brand' => 'trw', 'price' => 340, 'qty' => 65, 'oem' => 'PHB620'],
                    ['name' => 'Шланг гальм. передній Patron PBH132 (Geely Atlas)', 'brand' => 'patron', 'price' => 260, 'qty' => 80, 'oem' => 'PBH132'],
                ],
            ],
            'brake-fluids' => [
                'descr' => 'Гальмівна рідина DOT 4, температура кипіння 230°C.',
                'specs' => ['Стандарт' => 'DOT 4', 'Об\'єм' => '1 л'],
                'items' => [
                    ['name' => 'Гальмівна рідина Bosch DOT 4 1л', 'brand' => 'bosch', 'price' => 320, 'qty' => 65, 'oem' => '1987479107'],
                    ['name' => 'Гальмівна рідина ATE Original DOT 4 1л', 'brand' => 'ate', 'price' => 380, 'qty' => 48, 'oem' => '03990139022'],
                    ['name' => 'Гальмівна рідина Liqui Moly DOT 4 1л', 'brand' => 'liqui-moly', 'price' => 460, 'qty' => 32, 'oem' => '3093'],
                ],
            ],

            // ============= SUSPENSION =============
            'shocks-front' => [
                'descr' => 'Передній амортизатор, гідравлічний/газовий, висока стабільність на нерівностях.',
                'specs' => ['Тип' => 'Газо-олійний'],
                'items' => [
                    ['name' => 'Амортизатор передній KYB 339758 (Chery Tiggo 4)', 'brand' => 'kyb', 'price' => 2890, 'qty' => 14, 'oem' => '339758'],
                    ['name' => 'Амортизатор передній Monroe G7257 (Geely Atlas)', 'brand' => 'monroe', 'price' => 2640, 'qty' => 18, 'oem' => 'G7257'],
                    ['name' => 'Амортизатор передній Sachs 314 873 (BYD F3)', 'brand' => 'sachs', 'price' => 2480, 'qty' => 16, 'oem' => '314873'],
                    ['name' => 'Амортизатор передній Patron PSA374025 (Haval Jolion)', 'brand' => 'patron', 'price' => 1580, 'qty' => 28, 'oem' => 'PSA374025'],
                    ['name' => 'Амортизатор передній LynxAuto LF-3149 (MG ZS)', 'brand' => 'lynxauto', 'price' => 1740, 'qty' => 22, 'oem' => 'LF3149'],
                ],
            ],
            'shocks-rear' => [
                'descr' => 'Задній амортизатор, газовий, посилений для російської зими.',
                'specs' => ['Тип' => 'Газо-олійний'],
                'items' => [
                    ['name' => 'Амортизатор задній KYB 349074 (Chery Tiggo 4)', 'brand' => 'kyb', 'price' => 2480, 'qty' => 22, 'oem' => '349074'],
                    ['name' => 'Амортизатор задній Monroe E1330 (BYD Song)', 'brand' => 'monroe', 'price' => 2280, 'qty' => 18, 'oem' => 'E1330'],
                    ['name' => 'Амортизатор задній Sachs 313 542 (Geely Emgrand)', 'brand' => 'sachs', 'price' => 2180, 'qty' => 20, 'oem' => '313542'],
                    ['name' => 'Амортизатор задній Patron PSA315027 (Haval H6)', 'brand' => 'patron', 'price' => 1380, 'qty' => 32, 'oem' => 'PSA315027'],
                ],
            ],
            'springs-front' => [
                'descr' => 'Передня пружина підвіски, термообробка, антикорозійне покриття.',
                'specs' => ['К-сть витків' => '7.5'],
                'items' => [
                    ['name' => 'Пружина передня KYB RA5274 (Chery Tiggo)', 'brand' => 'kyb', 'price' => 1340, 'qty' => 18, 'oem' => 'RA5274'],
                    ['name' => 'Пружина передня Monroe SP3471 (Geely Atlas)', 'brand' => 'monroe', 'price' => 1480, 'qty' => 14, 'oem' => 'SP3471'],
                ],
            ],
            'springs-rear' => [
                'descr' => 'Задня пружина підвіски, з епоксидним покриттям.',
                'specs' => ['К-сть витків' => '6.5'],
                'items' => [
                    ['name' => 'Пружина задня KYB RA1284 (BYD F3)', 'brand' => 'kyb', 'price' => 1180, 'qty' => 22, 'oem' => 'RA1284'],
                    ['name' => 'Пружина задня Monroe SP3892 (Haval H6)', 'brand' => 'monroe', 'price' => 1280, 'qty' => 16, 'oem' => 'SP3892'],
                ],
            ],
            'ball-joints' => [
                'descr' => 'Кульова опора нижнього важеля, з ущільнювачем.',
                'specs' => ['Конус' => '17 мм'],
                'items' => [
                    ['name' => 'Шарова опора TRW JBJ792 (Chery Tiggo)', 'brand' => 'trw', 'price' => 540, 'qty' => 38, 'oem' => 'JBJ792'],
                    ['name' => 'Шарова опора Sachs 803 003 (BYD F3)', 'brand' => 'sachs', 'price' => 620, 'qty' => 32, 'oem' => '803003'],
                    ['name' => 'Шарова опора Patron PT0123 (Geely Emgrand)', 'brand' => 'patron', 'price' => 280, 'qty' => 55, 'oem' => 'PT0123'],
                ],
            ],
            'tie-rods' => [
                'descr' => 'Рульова тяга у комплекті з наконечником.',
                'specs' => ['Тип' => 'Внутрішня + наконечник'],
                'items' => [
                    ['name' => 'Рульова тяга TRW JAR1037 (Chery Tiggo)', 'brand' => 'trw', 'price' => 780, 'qty' => 28, 'oem' => 'JAR1037'],
                    ['name' => 'Рульова тяга Sachs 802 134 (Geely Atlas)', 'brand' => 'sachs', 'price' => 840, 'qty' => 24, 'oem' => '802134'],
                    ['name' => 'Рульова тяга Patron PT1198 (BYD F3)', 'brand' => 'patron', 'price' => 380, 'qty' => 45, 'oem' => 'PT1198'],
                ],
            ],
            'stabilizer-links' => [
                'descr' => 'Стійка стабілізатора з шарніром.',
                'specs' => ['Довжина' => '195 мм'],
                'items' => [
                    ['name' => 'Стійка стабілізатора TRW JTS472 (Chery Tiggo)', 'brand' => 'trw', 'price' => 380, 'qty' => 48, 'oem' => 'JTS472'],
                    ['name' => 'Стійка стабілізатора Patron PS4127 (Geely Atlas)', 'brand' => 'patron', 'price' => 220, 'qty' => 65, 'oem' => 'PS4127'],
                ],
            ],
            'silentblocks' => [
                'descr' => 'Сайлент-блок переднього важеля, гумометалевий.',
                'specs' => ['Матеріал' => 'Гума + сталь'],
                'items' => [
                    ['name' => 'Сайлент-блок передній TRW JBU452 (BYD F3)', 'brand' => 'trw', 'price' => 280, 'qty' => 55, 'oem' => 'JBU452'],
                    ['name' => 'Сайлент-блок передній Patron PSE1342 (Chery Tiggo)', 'brand' => 'patron', 'price' => 180, 'qty' => 72, 'oem' => 'PSE1342'],
                ],
            ],
            'hub-bearings-front' => [
                'descr' => 'Підшипник передньої маточини з ABS-датчиком.',
                'specs' => ['ABS' => 'Інтегрований'],
                'items' => [
                    ['name' => 'Підшипник передній FAG 713 6107 70 (Chery Tiggo)', 'brand' => 'fag', 'price' => 1620, 'qty' => 12, 'oem' => '713610770'],
                    ['name' => 'Підшипник передній SKF VKBA 7488 (Geely Atlas)', 'brand' => 'skf', 'price' => 1840, 'qty' => 9, 'oem' => 'VKBA7488'],
                    ['name' => 'Підшипник передній Patron PBK1934 (BYD F3)', 'brand' => 'patron', 'price' => 980, 'qty' => 22, 'oem' => 'PBK1934'],
                ],
            ],
            'hub-bearings-rear' => [
                'descr' => 'Задній маточинний підшипник, маслонаповнений.',
                'specs' => ['Розмір' => '40×72×37 мм'],
                'items' => [
                    ['name' => 'Підшипник задній FAG 713 6112 50 (BYD Song)', 'brand' => 'fag', 'price' => 1480, 'qty' => 14, 'oem' => '713611250'],
                    ['name' => 'Підшипник задній SKF VKBA 6995 (Chery Tiggo 4)', 'brand' => 'skf', 'price' => 1690, 'qty' => 10, 'oem' => 'VKBA6995'],
                ],
            ],

            // ============= ELECTRICS =============
            'batteries' => [
                'descr' => 'AGM/EFB акумулятор з гарантією 3 роки.',
                'specs' => ['Тип' => 'EFB / AGM', 'Гарантія' => '36 міс'],
                'items' => [
                    ['name' => 'Акумулятор Varta Blue Dynamic 60 Ah', 'brand' => 'varta', 'price' => 3450, 'qty' => 22, 'rating' => 4.8, 'oem' => 'D24'],
                    ['name' => 'Акумулятор Varta Black Dynamic 45 Ah', 'brand' => 'varta', 'price' => 2390, 'qty' => 28, 'oem' => 'B18'],
                    ['name' => 'Акумулятор Varta Silver Dynamic 74 Ah AGM', 'brand' => 'varta', 'price' => 5890, 'qty' => 14, 'oem' => 'E39'],
                    ['name' => 'Акумулятор Bosch S5 silver 74 Ah', 'brand' => 'bosch-battery', 'price' => 4690, 'qty' => 18, 'oem' => 'S5008'],
                    ['name' => 'Акумулятор Bosch S4 60 Ah', 'brand' => 'bosch-battery', 'price' => 3290, 'qty' => 25, 'oem' => 'S4005'],
                ],
            ],
            'starters' => [
                'descr' => 'Стартер з редуктором, посилений магніт.',
                'specs' => ['Потужність' => '1.4 кВт'],
                'items' => [
                    ['name' => 'Стартер Bosch 0 986 020 191 (Chery Tiggo)', 'brand' => 'bosch', 'price' => 4890, 'qty' => 6, 'oem' => '0986020191'],
                    ['name' => 'Стартер Valeo 458489 (Geely Atlas)', 'brand' => 'valeo', 'price' => 4580, 'qty' => 8, 'oem' => '458489'],
                    ['name' => 'Стартер Denso DSN2046 (BYD F3)', 'brand' => 'denso', 'price' => 4280, 'qty' => 9, 'oem' => 'DSN2046'],
                ],
            ],
            'alternators' => [
                'descr' => 'Генератор з регулятором напруги.',
                'specs' => ['Сила струму' => '120 A'],
                'items' => [
                    ['name' => 'Генератор Bosch 0 124 525 113 (Chery Tiggo 4)', 'brand' => 'bosch', 'price' => 6890, 'qty' => 5, 'oem' => '0124525113'],
                    ['name' => 'Генератор Valeo TG12C015 (Geely Coolray)', 'brand' => 'valeo', 'price' => 6480, 'qty' => 7, 'oem' => 'TG12C015'],
                    ['name' => 'Генератор Denso DAN1071 (BYD Song)', 'brand' => 'denso', 'price' => 6180, 'qty' => 6, 'oem' => 'DAN1071'],
                ],
            ],
            'bulbs-h4' => [
                'descr' => 'Галогенна лампа H4 60/55W +100% яскравості.',
                'specs' => ['Цоколь' => 'H4', 'Потужність' => '60/55 W'],
                'items' => [
                    ['name' => 'Лампа Osram Night Breaker 200 H4 (2 шт.)', 'brand' => 'osram', 'price' => 720, 'qty' => 38, 'oem' => '64193NB200'],
                    ['name' => 'Лампа Philips X-treme Vision Pro150 H4 (2 шт.)', 'brand' => 'philips', 'price' => 820, 'qty' => 32, 'oem' => '12342XVPS2'],
                    ['name' => 'Лампа Hella H4 12V 60/55W', 'brand' => 'hella', 'price' => 240, 'qty' => 65, 'oem' => '8GJ002525131'],
                ],
            ],
            'bulbs-h7' => [
                'descr' => 'Лампа H7, ксеноновий ефект, біле світло 4500K.',
                'specs' => ['Цоколь' => 'H7', 'Потужність' => '55 W'],
                'items' => [
                    ['name' => 'Лампа Osram Night Breaker H7 +200% (2 шт.)', 'brand' => 'osram', 'price' => 760, 'qty' => 42, 'oem' => '64210NB200'],
                    ['name' => 'Лампа Philips RacingVision GT200 H7 (2 шт.)', 'brand' => 'philips', 'price' => 880, 'qty' => 28, 'oem' => '12972RGTS2'],
                    ['name' => 'Лампа Hella H7 12V 55W', 'brand' => 'hella', 'price' => 220, 'qty' => 80, 'oem' => '8GH007157121'],
                ],
            ],
            'bulbs-led' => [
                'descr' => 'LED-лампи з активним охолодженням, до 6000K.',
                'specs' => ['Тип' => 'LED', 'Світловий потік' => '8000 лм'],
                'items' => [
                    ['name' => 'LED-лампи Osram Night Breaker H7 LED', 'brand' => 'osram', 'price' => 4890, 'qty' => 14, 'oem' => 'H7LEDNB'],
                    ['name' => 'LED-лампи Philips Ultinon Pro9000 H4', 'brand' => 'philips', 'price' => 5290, 'qty' => 12, 'oem' => 'H4LEDP9000'],
                ],
            ],
            'bulbs-fog' => [
                'descr' => 'Лампа для протитуманних фар, теплий жовтий спектр.',
                'specs' => ['Цоколь' => 'H11'],
                'items' => [
                    ['name' => 'Лампа Osram H11 Original (2 шт.)', 'brand' => 'osram', 'price' => 280, 'qty' => 55, 'oem' => '64211'],
                    ['name' => 'Лампа Philips H11 Vision (2 шт.)', 'brand' => 'philips', 'price' => 320, 'qty' => 48, 'oem' => '12362PRC1'],
                ],
            ],

            // ============= TRANSMISSION =============
            'clutch-kits' => [
                'descr' => 'Комплект зчеплення: диск, корзина, вижимний підшипник.',
                'specs' => ['Діаметр' => '215 мм'],
                'items' => [
                    ['name' => 'Комплект зчеплення Sachs 3000 970 008 (Chery Tiggo)', 'brand' => 'sachs', 'price' => 6890, 'qty' => 6, 'oem' => '3000970008'],
                    ['name' => 'Комплект зчеплення Valeo 826564 (Geely Atlas)', 'brand' => 'valeo', 'price' => 6480, 'qty' => 8, 'oem' => '826564'],
                ],
            ],
            'clutch-discs' => [
                'descr' => 'Феродовий диск зчеплення.',
                'specs' => ['Діаметр' => '215 мм'],
                'items' => [
                    ['name' => 'Диск зчеплення Sachs 1862 421 137 (BYD F3)', 'brand' => 'sachs', 'price' => 2890, 'qty' => 9, 'oem' => '1862421137'],
                    ['name' => 'Диск зчеплення Valeo 803486 (Chery Bonus)', 'brand' => 'valeo', 'price' => 2540, 'qty' => 11, 'oem' => '803486'],
                ],
            ],
            'release-bearings' => [
                'descr' => 'Підшипник вижимний з гідравлічним приводом.',
                'specs' => ['Тип' => 'Гідравлічний'],
                'items' => [
                    ['name' => 'Вижимний підшипник Sachs 3151 188 002 (Chery Tiggo)', 'brand' => 'sachs', 'price' => 1480, 'qty' => 14, 'oem' => '3151188002'],
                    ['name' => 'Вижимний підшипник Valeo 804006 (Geely Emgrand)', 'brand' => 'valeo', 'price' => 1380, 'qty' => 16, 'oem' => '804006'],
                ],
            ],
            'cv-outer' => [
                'descr' => 'Зовнішній ШРУС, з пилозахисним чохлом.',
                'specs' => ['К-сть шліців' => '25'],
                'items' => [
                    ['name' => 'ШРУС зовнішній SKF VKJA 5028 (Chery Tiggo)', 'brand' => 'skf', 'price' => 1880, 'qty' => 14, 'oem' => 'VKJA5028'],
                    ['name' => 'ШРУС зовнішній Patron PCV1289 (BYD F3)', 'brand' => 'patron', 'price' => 1240, 'qty' => 22, 'oem' => 'PCV1289'],
                ],
            ],
            'cv-inner' => [
                'descr' => 'Внутрішній ШРУС (триплекс), з чохлом.',
                'specs' => ['Тип' => 'Триплекс'],
                'items' => [
                    ['name' => 'ШРУС внутрішній SKF VKJB 8403 (Geely Atlas)', 'brand' => 'skf', 'price' => 2140, 'qty' => 11, 'oem' => 'VKJB8403'],
                    ['name' => 'ШРУС внутрішній Patron PCV2412 (Chery Tiggo)', 'brand' => 'patron', 'price' => 1390, 'qty' => 18, 'oem' => 'PCV2412'],
                ],
            ],
            'transmission-mounts' => [
                'descr' => 'Опора КПП з гумовим демпфером.',
                'specs' => ['Тип' => 'Лівобічна'],
                'items' => [
                    ['name' => 'Опора КПП Sachs 802 213 (Chery Tiggo)', 'brand' => 'sachs', 'price' => 1180, 'qty' => 14, 'oem' => '802213'],
                    ['name' => 'Опора КПП Patron PSE3247 (Geely Atlas)', 'brand' => 'patron', 'price' => 720, 'qty' => 22, 'oem' => 'PSE3247'],
                ],
            ],

            // ============= FLUIDS =============
            'oils-5w30' => [
                'descr' => 'Синтетична моторна олива 5W-30, низькі емісії.',
                'specs' => ['В\'язкість' => '5W-30', 'API' => 'SN', 'ACEA' => 'C3'],
                'items' => [
                    ['name' => 'Castrol Edge 5W-30 LL 4 л', 'brand' => 'castrol', 'price' => 1890, 'qty' => 45, 'rating' => 4.9, 'oem' => '15B1B0'],
                    ['name' => 'Castrol Edge 5W-30 LL 1 л', 'brand' => 'castrol', 'price' => 540, 'qty' => 92, 'oem' => '15B1AF'],
                    ['name' => 'Mobil 1 ESP 5W-30 4 л', 'brand' => 'mobil-1', 'price' => 2280, 'qty' => 38, 'oem' => '154294'],
                    ['name' => 'Shell Helix Ultra 5W-30 4 л', 'brand' => 'shell', 'price' => 1980, 'qty' => 42, 'oem' => '550046268'],
                    ['name' => 'Liqui Moly Special Tec AA 5W-30 4 л', 'brand' => 'liqui-moly', 'price' => 2380, 'qty' => 24, 'oem' => '7516'],
                ],
            ],
            'oils-5w40' => [
                'descr' => 'Синтетична універсальна 5W-40, висока термостабільність.',
                'specs' => ['В\'язкість' => '5W-40', 'API' => 'SN/CF'],
                'items' => [
                    ['name' => 'Castrol Magnatec 5W-40 4 л', 'brand' => 'castrol', 'price' => 1780, 'qty' => 55, 'oem' => '15F2D8'],
                    ['name' => 'Mobil 1 FS 5W-40 4 л', 'brand' => 'mobil-1', 'price' => 2150, 'qty' => 38, 'oem' => '154297'],
                    ['name' => 'Shell Helix HX8 5W-40 4 л', 'brand' => 'shell', 'price' => 1840, 'qty' => 48, 'oem' => '550040430'],
                    ['name' => 'Liqui Moly Synthoil High Tech 5W-40 4 л', 'brand' => 'liqui-moly', 'price' => 2280, 'qty' => 22, 'oem' => '1856'],
                ],
            ],
            'oils-10w40' => [
                'descr' => 'Напівсинтетика 10W-40, для пробігів від 100 000 км.',
                'specs' => ['В\'язкість' => '10W-40'],
                'items' => [
                    ['name' => 'Castrol Magnatec 10W-40 4 л', 'brand' => 'castrol', 'price' => 1290, 'qty' => 60, 'oem' => '15CA22'],
                    ['name' => 'Mobil Super 2000 10W-40 4 л', 'brand' => 'mobil-1', 'price' => 1340, 'qty' => 52, 'oem' => '152567'],
                    ['name' => 'Shell Helix HX7 10W-40 4 л', 'brand' => 'shell', 'price' => 1240, 'qty' => 58, 'oem' => '550040294'],
                ],
            ],
            'oils-0w20' => [
                'descr' => 'Олива 0W-20 для гібридних силових установок BYD/Haval.',
                'specs' => ['В\'язкість' => '0W-20'],
                'items' => [
                    ['name' => 'Mobil 1 0W-20 4 л', 'brand' => 'mobil-1', 'price' => 2490, 'qty' => 18, 'oem' => '154277'],
                    ['name' => 'Castrol Edge 0W-20 LL 4 л', 'brand' => 'castrol', 'price' => 2380, 'qty' => 14, 'oem' => '15F2E3'],
                    ['name' => 'Shell Helix Ultra 0W-20 4 л', 'brand' => 'shell', 'price' => 2280, 'qty' => 22, 'oem' => '550046268B'],
                ],
            ],
            'transmission-oils' => [
                'descr' => 'Трансмісійна олива для МКП/АКП.',
                'specs' => ['Тип' => 'GL-4 / ATF'],
                'items' => [
                    ['name' => 'Castrol Transmax DEX III 1 л', 'brand' => 'castrol', 'price' => 380, 'qty' => 65, 'oem' => '157F31'],
                    ['name' => 'Mobil ATF 3309 1 л', 'brand' => 'mobil-1', 'price' => 540, 'qty' => 48, 'oem' => '152648'],
                    ['name' => 'Liqui Moly Hypoid GL-5 80W-90 1 л', 'brand' => 'liqui-moly', 'price' => 320, 'qty' => 72, 'oem' => '1956'],
                ],
            ],
            'coolants' => [
                'descr' => 'Готовий до використання антифриз -40°C.',
                'specs' => ['Тип' => 'G12+/G13'],
                'items' => [
                    ['name' => 'Castrol Radicool SF G12 5 л', 'brand' => 'castrol', 'price' => 780, 'qty' => 48, 'oem' => '157F3B'],
                    ['name' => 'Liqui Moly Antifreeze G13 5 л', 'brand' => 'liqui-moly', 'price' => 1180, 'qty' => 28, 'oem' => '21135'],
                ],
            ],
            'brake-fluids-2' => [
                'descr' => 'Гальмівна рідина DOT 4+, низька гігроскопічність.',
                'specs' => ['Стандарт' => 'DOT 4+'],
                'items' => [
                    ['name' => 'Castrol Brake Fluid DOT 4 1л', 'brand' => 'castrol', 'price' => 340, 'qty' => 38, 'oem' => '157F8C'],
                    ['name' => 'Liqui Moly DOT 4 SL.6 1л', 'brand' => 'liqui-moly', 'price' => 480, 'qty' => 24, 'oem' => '21162'],
                ],
            ],
            'windshield-fluids' => [
                'descr' => 'Зимовий омивач -25°C.',
                'specs' => ['Температура' => '-25°C'],
                'items' => [
                    ['name' => 'Омивач -25°C 4 л', 'brand' => 'patron', 'price' => 180, 'qty' => 120, 'oem' => 'WW25'],
                    ['name' => 'Літній концентрат 250 мл', 'brand' => 'patron', 'price' => 90, 'qty' => 200, 'oem' => 'WS250'],
                ],
            ],

            // ============= BODY & OPTICS =============
            'headlights' => [
                'descr' => 'Фара головного світла, з регулюванням.',
                'specs' => ['Тип лампи' => 'H7+H1'],
                'items' => [
                    ['name' => 'Фара права (Chery Tiggo 4 Pro)', 'brand' => 'chery-oem', 'price' => 5890, 'qty' => 6, 'oem' => 'T19A371511'],
                    ['name' => 'Фара ліва (Chery Tiggo 4 Pro)', 'brand' => 'chery-oem', 'price' => 5890, 'qty' => 7, 'oem' => 'T19A371510'],
                    ['name' => 'Фара права (Geely Atlas Pro)', 'brand' => 'geely-oem', 'price' => 6480, 'qty' => 4, 'oem' => '6610502400'],
                    ['name' => 'Фара ліва (Geely Atlas Pro)', 'brand' => 'geely-oem', 'price' => 6480, 'qty' => 5, 'oem' => '6610502300'],
                    ['name' => 'Фара права (BYD Atto 3)', 'brand' => 'byd-oem', 'price' => 7240, 'qty' => 3, 'oem' => '10186421'],
                ],
            ],
            'taillights' => [
                'descr' => 'Задній ліхтар, LED.',
                'specs' => ['Тип' => 'LED'],
                'items' => [
                    ['name' => 'Ліхтар задній правий (Haval H6)', 'brand' => 'haval-oem', 'price' => 3890, 'qty' => 8, 'oem' => '4133100XKZ16A'],
                    ['name' => 'Ліхтар задній лівий (Haval H6)', 'brand' => 'haval-oem', 'price' => 3890, 'qty' => 8, 'oem' => '4133200XKZ16A'],
                    ['name' => 'Ліхтар задній правий (Chery Tiggo 7 Pro)', 'brand' => 'chery-oem', 'price' => 3480, 'qty' => 9, 'oem' => 'T1JA42L711'],
                ],
            ],
            'fog-lights' => [
                'descr' => 'Протитуманна фара.',
                'specs' => ['Цоколь' => 'H11'],
                'items' => [
                    ['name' => 'Протитуманка передня (Chery Tiggo 4)', 'brand' => 'chery-oem', 'price' => 1240, 'qty' => 22, 'oem' => 'T19473271'],
                    ['name' => 'Протитуманка передня (Geely Coolray)', 'brand' => 'geely-oem', 'price' => 1380, 'qty' => 18, 'oem' => '6600081200'],
                ],
            ],
            'side-mirrors' => [
                'descr' => 'Бічне дзеркало з електроприводом, обігрів.',
                'specs' => ['Привід' => 'Електро + обігрів'],
                'items' => [
                    ['name' => 'Дзеркало праве (Chery Tiggo 4)', 'brand' => 'chery-oem', 'price' => 2890, 'qty' => 9, 'oem' => 'T19820221OL'],
                    ['name' => 'Дзеркало ліве (Geely Atlas Pro)', 'brand' => 'geely-oem', 'price' => 3140, 'qty' => 7, 'oem' => '6605030700'],
                ],
            ],
            'fenders' => [
                'descr' => 'Крило переднє, штампований метал, ґрунтоване.',
                'specs' => ['Матеріал' => 'Сталь, ґрунт'],
                'items' => [
                    ['name' => 'Крило переднє праве (Chery Tiggo 4)', 'brand' => 'chery-oem', 'price' => 3140, 'qty' => 7, 'oem' => 'T1985003110'],
                    ['name' => 'Крило переднє ліве (Chery Tiggo 4)', 'brand' => 'chery-oem', 'price' => 3140, 'qty' => 8, 'oem' => 'T1985003210'],
                ],
            ],
            'bumpers' => [
                'descr' => 'Бампер з пластика, ґрунтований під фарбу.',
                'specs' => ['Колір' => 'Грунт'],
                'items' => [
                    ['name' => 'Бампер передній (Geely Coolray)', 'brand' => 'geely-oem', 'price' => 7290, 'qty' => 3, 'oem' => '6601201400'],
                    ['name' => 'Бампер задній (Geely Coolray)', 'brand' => 'geely-oem', 'price' => 6890, 'qty' => 4, 'oem' => '6601301400'],
                ],
            ],
            'grilles' => [
                'descr' => 'Решітка радіатора, хром-чорна.',
                'specs' => ['Колір' => 'Хром + чорний'],
                'items' => [
                    ['name' => 'Решітка радіатора (Haval Jolion)', 'brand' => 'haval-oem', 'price' => 2840, 'qty' => 7, 'oem' => '8401100XKZ16A'],
                    ['name' => 'Решітка радіатора (BYD Atto 3)', 'brand' => 'byd-oem', 'price' => 3180, 'qty' => 5, 'oem' => '10189845'],
                ],
            ],
            'wipers' => [
                'descr' => 'Двірники Aerotwin, безкаркасні.',
                'specs' => ['Тип' => 'Безкаркасні'],
                'items' => [
                    ['name' => 'Двірники Bosch AeroTwin AR653S 650+450 (Chery Tiggo)', 'brand' => 'bosch', 'price' => 780, 'qty' => 38, 'oem' => '3397118933'],
                    ['name' => 'Двірники Valeo Silencio 650+450 (Geely Atlas)', 'brand' => 'valeo', 'price' => 690, 'qty' => 42, 'oem' => 'VM404'],
                    ['name' => 'Двірники Hella Cleantech 600+475 (Haval H6)', 'brand' => 'hella', 'price' => 620, 'qty' => 32, 'oem' => '9XW358053281'],
                ],
            ],

            // ============= ACCESSORIES =============
            'mats' => [
                'descr' => 'Гумові 3D-килимки, бортики 4 см, оригінальна форма.',
                'specs' => ['Матеріал' => 'EVA / гума'],
                'items' => [
                    ['name' => 'Килимки 3D EVA для Chery Tiggo 4 (4 шт.)', 'brand' => 'patron', 'price' => 1480, 'qty' => 24, 'oem' => 'MAT-T4'],
                    ['name' => 'Килимки 3D EVA для Geely Atlas (4 шт.)', 'brand' => 'patron', 'price' => 1480, 'qty' => 18, 'oem' => 'MAT-GA'],
                    ['name' => 'Килимки 3D EVA для Haval Jolion (4 шт.)', 'brand' => 'patron', 'price' => 1480, 'qty' => 22, 'oem' => 'MAT-HJ'],
                    ['name' => 'Килимки 3D EVA для BYD Song (4 шт.)', 'brand' => 'patron', 'price' => 1580, 'qty' => 15, 'oem' => 'MAT-BS'],
                    ['name' => 'Килимок багажника Chery Tiggo 7 Pro', 'brand' => 'patron', 'price' => 980, 'qty' => 28, 'oem' => 'TRUNK-T7'],
                ],
            ],
            'seat-covers' => [
                'descr' => 'Універсальні чохли з екошкіри.',
                'specs' => ['Матеріал' => 'Екошкіра'],
                'items' => [
                    ['name' => 'Чохли універсальні екошкіра (комплект)', 'brand' => 'patron', 'price' => 2890, 'qty' => 18, 'oem' => 'SC-ECO'],
                    ['name' => 'Чохли тканинні універсальні (комплект)', 'brand' => 'patron', 'price' => 1480, 'qty' => 24, 'oem' => 'SC-FAB'],
                ],
            ],
            'organizers' => [
                'descr' => 'Органайзер у багажник, складний.',
                'specs' => ['Об\'єм' => '40 л'],
                'items' => [
                    ['name' => 'Органайзер багажника 40 л', 'brand' => 'patron', 'price' => 540, 'qty' => 38, 'oem' => 'ORG-40'],
                    ['name' => 'Кишеня на спинку сидіння', 'brand' => 'patron', 'price' => 240, 'qty' => 72, 'oem' => 'POCKET'],
                ],
            ],
            'dashcams' => [
                'descr' => 'Відеореєстратор Full HD, нічна зйомка.',
                'specs' => ['Роздільність' => '1920×1080'],
                'items' => [
                    ['name' => 'Відеореєстратор Full HD 1080p', 'brand' => 'patron', 'price' => 1890, 'qty' => 24, 'oem' => 'DVR-1080'],
                    ['name' => 'Відеореєстратор 4K + GPS', 'brand' => 'patron', 'price' => 4290, 'qty' => 12, 'oem' => 'DVR-4K'],
                ],
            ],
            'phone-holders' => [
                'descr' => 'Магнітний тримач телефону на дефлектор.',
                'specs' => ['Кріплення' => 'Магніт'],
                'items' => [
                    ['name' => 'Тримач магнітний на дефлектор', 'brand' => 'patron', 'price' => 280, 'qty' => 65, 'oem' => 'PH-MAG'],
                    ['name' => 'Тримач на присоску', 'brand' => 'patron', 'price' => 320, 'qty' => 58, 'oem' => 'PH-SUC'],
                ],
            ],
            'chargers' => [
                'descr' => 'USB-C 65W зарядка для авто.',
                'specs' => ['Потужність' => '65W'],
                'items' => [
                    ['name' => 'Зарядка USB-C 65W PD', 'brand' => 'patron', 'price' => 580, 'qty' => 38, 'oem' => 'CHG-65'],
                    ['name' => 'Зарядка USB 4-портова', 'brand' => 'patron', 'price' => 420, 'qty' => 42, 'oem' => 'CHG-4U'],
                ],
            ],
            'tools' => [
                'descr' => 'Набір інструментів для авто.',
                'specs' => ['Кейс' => 'Пластиковий'],
                'items' => [
                    ['name' => 'Набір ключів 142 предмети', 'brand' => 'patron', 'price' => 3890, 'qty' => 12, 'oem' => 'TS-142'],
                    ['name' => 'Домкрат гідравлічний 2т', 'brand' => 'patron', 'price' => 1890, 'qty' => 22, 'oem' => 'JACK-2T'],
                    ['name' => 'Знімач масляних фільтрів', 'brand' => 'patron', 'price' => 280, 'qty' => 48, 'oem' => 'OF-PULL'],
                ],
            ],
        ];
    }
}
