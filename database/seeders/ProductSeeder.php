<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    private array $sampleImages = [
        '/assets/img/products/demo/electronics-1.jpg',
        '/assets/img/products/demo/electronics-2.jpg',
        '/assets/img/products/demo/electronics-3.jpg',
        '/assets/img/products/demo/electronics-4.jpg',
        '/assets/img/products/demo/electronics-5.jpg',
        '/assets/img/products/demo/fashion-1.jpg',
        '/assets/img/products/demo/fashion-2.jpg',
        '/assets/img/products/demo/fashion-3.jpg',
        '/assets/img/products/demo/fashion-4.jpg',
        '/assets/img/products/demo/home-1.jpg',
        '/assets/img/products/demo/home-2.jpg',
        '/assets/img/products/demo/home-3.jpg',
        '/assets/img/products/demo/sports-1.jpg',
        '/assets/img/products/demo/sports-2.jpg',
        '/assets/img/products/demo/sports-3.jpg',
        '/assets/img/products/demo/product-1.jpg',
        '/assets/img/products/demo/product-2.jpg',
        '/assets/img/products/demo/product-3.jpg',
        '/assets/img/products/demo/product-4.jpg',
        '/assets/img/products/demo/product-5.jpg',
    ];

    private array $brands = [
        'Apple', 'Samsung', 'Sony', 'LG', 'Nike', 'Adidas', 'H&M', 'Zara',
        'IKEA', 'Xiaomi', 'Asus', 'HP', 'Dell', 'Canon', 'Nikon',
    ];

    public function run(): void
    {
        $this->createSampleImages();

        $categories = Category::all();

        foreach ($categories as $category) {
            $this->createProductsForCategory($category);
        }
    }

    private function createSampleImages(): void
    {
        $demoDir = public_path('assets/img/products/demo');
        if (! File::exists($demoDir)) {
            File::makeDirectory($demoDir, 0755, true);
        }

        // Create simple colored placeholder images
        foreach ($this->sampleImages as $imagePath) {
            $filename = basename($imagePath);
            $fullPath = $demoDir.'/'.$filename;

            if (! File::exists($fullPath)) {
                // Create a simple 800x600 colored rectangle as placeholder
                $this->createPlaceholderImage($fullPath, $filename);
            }
        }
    }

    private function createPlaceholderImage(string $path, string $filename): void
    {
        // Create simple text file as placeholder (will be replaced with actual images)
        File::put($path, "Placeholder image: {$filename}");
    }

    private function createProductsForCategory(Category $category): void
    {
        $productCount = match ($category->title) {
            'Електроніка' => 15,
            'Одяг' => 20,
            'Дім і сад' => 12,
            'Спорт' => 10,
            default => 8
        };

        for ($i = 0; $i < $productCount; $i++) {
            $this->createRichProduct($category);
        }
    }

    private function createRichProduct(Category $category): void
    {
        $productData = $this->getDetailedProductData($category);
        $mainImage = fake()->randomElement($this->sampleImages);

        // Create 3-5 additional gallery images
        $galleryImages = fake()->randomElements($this->sampleImages, fake()->numberBetween(3, 5));

        $brand = Brand::where('name->uk', $productData['brand'])->first()
                ?? Brand::firstOrCreate(
                    ['slug' => \Str::slug($productData['brand'])],
                    [
                        'name' => $productData['brand'],
                        'is_active' => true,
                        'sort_order' => 0,
                    ]
                );

        Product::create([
            'title' => $productData['title'],
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'manufacturer' => $productData['manufacturer'],
            'price' => $productData['price'],
            'old_price' => $productData['old_price'],
            'weight' => $productData['weight'],
            'dimensions' => $productData['dimensions'],
            'excerpt' => $productData['excerpt'],
            'content' => $productData['content'],
            'quantity' => fake()->numberBetween(5, 100),
            'stock_status' => fake()->randomElement(['in_stock', 'in_stock', 'in_stock', 'out_of_stock']),
            'min_quantity' => fake()->numberBetween(1, 3),
            'image' => $mainImage,
            'gallery' => $galleryImages,
            'is_hit' => fake()->boolean(25),
            'is_new' => fake()->boolean(20),
            'is_active' => true,
            'rating' => fake()->randomFloat(1, 3.5, 5.0),
            'reviews_count' => fake()->numberBetween(5, 200),
            'meta_title' => $productData['title'].' - купити в Україні | SimpleShop',
            'meta_description' => $productData['meta_description'],
            'meta_keywords' => $productData['keywords'],
        ]);
    }

    private function generateProductTitle(Category $category): string
    {
        $products = [
            'Електроніка' => [
                'iPhone 15 Pro Max 256GB', 'Samsung Galaxy S24 Ultra', 'MacBook Air M3 13"',
                'iPad Pro 12.9" M2', 'Sony WH-1000XM5', 'Nintendo Switch OLED',
                'Apple Watch Series 9', 'AirPods Pro 2', 'PlayStation 5',
                'Canon EOS R6 Mark II', 'LG OLED55C3', 'Xiaomi 13 Pro',
            ],
            'Одяг' => [
                'Чоловіча куртка Nike', 'Жіночі джинси Levi\'s', 'Спортивні кросівки Adidas',
                'Светр з вовни Uniqlo', 'Плаття H&M', 'Чоловічі штани Zara',
                'Жіноча сорочка', 'Дитяча футболка', 'Зимова шапка',
                'Шкіряна сумка', 'Годинник Casio', 'Окуляри Ray-Ban',
            ],
            'Дім і сад' => [
                'Диван IKEA EKTORP', 'Стіл обідній дерев\'яний', 'Крісло офісне',
                'Лампа настільна LED', 'Фен Dyson', 'Пилосос Bosch',
                'Мультиварка Redmond', 'Чайник електричний', 'Тостер',
                'Набір посуду', 'Подушка ортопедична', 'Ковдра пухова',
            ],
            'Спорт' => [
                'Велосипед гірський', 'Гантелі набірні', 'Килимок для йоги',
                'Тренажер домашній', 'М\'яч футбольний', 'Ракетка тенісна',
                'Ролики інлайн', 'Скейтборд', 'Самокат дорослий',
                'Рюкзак туристичний', 'Спальний мішок', 'Палатка 2-місна',
            ],
        ];

        $categoryProducts = $products[$category->title] ?? $products['Електроніка'];
        $baseTitle = fake()->randomElement($categoryProducts);

        // Add some variation
        $variations = ['Pro', 'Max', 'Plus', 'Mini', 'Classic', 'Premium'];
        if (fake()->boolean(30)) {
            $baseTitle .= ' '.fake()->randomElement($variations);
        }

        return $baseTitle;
    }

    private function getDetailedProductData(Category $category): array
    {
        $detailedProducts = [
            'Електроніка' => [
                [
                    'title' => 'Смартфон iPhone 15 Pro Max 256GB Titanium',
                    'brand' => 'Apple',
                    'manufacturer' => 'Apple Inc.',
                    'price' => 54999.00,
                    'old_price' => 59999.00,
                    'weight' => 0.221,
                    'dimensions' => '159.9×76.7×8.25 мм',
                    'excerpt' => 'Новітній флагманський смартфон Apple з титановим корпусом, камерою 48MP та процесором A17 Pro.',
                    'content' => 'iPhone 15 Pro Max представляє собою вершину мобільних технологій від Apple. Оснащений потужним чіпом A17 Pro, який забезпечує неперевершену продуктивність для всіх задач. Професійна камерна система з головним датчиком 48MP дозволяє створювати фото та відео студійної якості. Титановий дизайн робить пристрій міцнішим та легшим.',
                    'description' => 'Характеристики: дисплей Super Retina XDR 6.7", Face ID, бездротова зарядка MagSafe, захист від води IP68.',
                    'meta_description' => 'Купити iPhone 15 Pro Max 256GB за найкращою ціною в Україні. Офіційна гарантія, швидка доставка.',
                    'keywords' => ['iphone', 'apple', 'смартфон', 'pro max', 'титан'],
                ],
                [
                    'title' => 'Ноутбук MacBook Air M3 13" Space Gray 8/256GB',
                    'brand' => 'Apple',
                    'manufacturer' => 'Apple Inc.',
                    'price' => 42999.00,
                    'old_price' => 0,
                    'weight' => 1.24,
                    'dimensions' => '304×215×11.3 мм',
                    'excerpt' => 'Ультратонкий ноутбук з чіпом M3 для професійної роботи та навчання.',
                    'content' => 'MacBook Air з чіпом M3 поєднує неймовірну продуктивність з тишиною роботи. Відсутність вентиляторів забезпечує абсолютно тиху роботу, а час роботи від батареї до 18 годин дозволяє працювати цілий день без підзарядки.',
                    'description' => 'Дисплей Liquid Retina 13.6", клавіатура Magic Keyboard, Touch ID, два порти Thunderbolt.',
                    'meta_description' => 'MacBook Air M3 13" - потужний та легкий ноутбук для роботи та творчості. Замовляйте з доставкою.',
                    'keywords' => ['macbook', 'air', 'm3', 'ноутбук', 'apple'],
                ],
            ],
            'Одяг' => [
                [
                    'title' => 'Чоловіча зимова куртка Nike Therma-FIT',
                    'brand' => 'Nike',
                    'manufacturer' => 'Nike Inc.',
                    'price' => 3499.00,
                    'old_price' => 4299.00,
                    'weight' => 0.8,
                    'dimensions' => 'Розмір M (chest 96-104 см)',
                    'excerpt' => 'Тепла зимова куртка з технологією Therma-FIT для активного відпочинку.',
                    'content' => 'Чоловіча куртка Nike Therma-FIT створена для холодних днів. Технологія Therma-FIT зберігає тепло тіла, а водовідштовхувальне покриття захищає від легкого дощу. Зручні кишені та регульований капюшон.',
                    'description' => 'Матеріал: 100% поліестер, утеплювач синтетичний, технологія Dri-FIT.',
                    'meta_description' => 'Чоловіча зимова куртка Nike - тепло, комфорт та стиль. Великий вибір розмірів.',
                    'keywords' => ['nike', 'куртка', 'зимова', 'чоловіча', 'therma-fit'],
                ],
                [
                    'title' => 'Жіночі джинси Levi\'s 501 Original Straight',
                    'brand' => 'Levi\'s',
                    'manufacturer' => 'Levi Strauss & Co.',
                    'price' => 2799.00,
                    'old_price' => 0,
                    'weight' => 0.6,
                    'dimensions' => 'Розмір 28/30 (талія 70 см)',
                    'excerpt' => 'Класичні жіночі джинси прямого крою з натурального деніму.',
                    'content' => 'Легендарні джинси Levi\'s 501 - це символ американського стилю та якості. Виготовлені з 100% бавовни, вони стають м\'якшими з кожним пранням. Класичний прямий крій підходить для будь-якої фігури.',
                    'description' => '100% бавовна, класична посадка, п\'ять кишень, застібка на ґудзики.',
                    'meta_description' => 'Жіночі джинси Levi\'s 501 - якість та комфорт від легендарного бренду.',
                    'keywords' => ['levis', 'джинси', 'жіночі', '501', 'денім'],
                ],
            ],
            'Дім і сад' => [
                [
                    'title' => 'Диван IKEA EKTORP 3-місний сірий',
                    'brand' => 'IKEA',
                    'manufacturer' => 'IKEA AB',
                    'price' => 18999.00,
                    'old_price' => 21999.00,
                    'weight' => 45.0,
                    'dimensions' => '218×88×88 см',
                    'excerpt' => 'Комфортний 3-місний диван з знімними чохлами для вітальні.',
                    'content' => 'Диван EKTORP втілює скандинавський стиль та комфорт. Глибокі сидіння та м\'які подушки забезпечують максимальний релакс. Знімні чохли легко пратися в пральній машині. Міцний каркас з масиву сосни гарантує довговічність.',
                    'description' => 'Каркас: масив сосни, наповнювач: поліуретанова піна, тканина: бавовна/поліестер.',
                    'meta_description' => 'Диван IKEA EKTORP - скандинавський комфорт для вашого дому. Доставка по Україні.',
                    'keywords' => ['ikea', 'диван', 'ektorp', 'меблі', 'вітальня'],
                ],
                [
                    'title' => 'Пилосос Dyson V15 Detect Absolute',
                    'brand' => 'Dyson',
                    'manufacturer' => 'Dyson Ltd.',
                    'price' => 16999.00,
                    'old_price' => 0,
                    'weight' => 3.1,
                    'dimensions' => '1232×250×166 мм',
                    'excerpt' => 'Бездротовий пилосос з лазерним виявленням пилу та потужною всмоктувальною силою.',
                    'content' => 'Dyson V15 Detect використовує лазерну технологію для виявлення мікроскопічного пилу. Цифровий дисплей показує кількість та розмір зібраних частинок. Час роботи до 60 хвилин, 5 різних насадок для всіх поверхонь.',
                    'description' => 'Двигун Hyperdymium, циклонна технологія, HEPA фільтр, бездротова робота.',
                    'meta_description' => 'Пилосос Dyson V15 Detect - революційне прибирання з лазерним виявленням пилу.',
                    'keywords' => ['dyson', 'пилосос', 'бездротовий', 'лазер', 'v15'],
                ],
            ],
            'Спорт' => [
                [
                    'title' => 'Кросівки Nike Air Max 270 чоловічі чорні',
                    'brand' => 'Nike',
                    'manufacturer' => 'Nike Inc.',
                    'price' => 3799.00,
                    'old_price' => 4499.00,
                    'weight' => 0.5,
                    'dimensions' => 'Розмір 42 EU (26.5 см)',
                    'excerpt' => 'Спортивні кросівки з максимальною амортизацією для бігу та повсякденного носіння.',
                    'content' => 'Nike Air Max 270 забезпечують неперевершений комфорт завдяки найбільшій повітряній подушці в лінійці Max. М\'яка сітчаста верхня частина забезпечує вентиляцію, а гумова підошва - надійне зчеплення з поверхнею.',
                    'description' => 'Верх: текстиль/синтетика, підошва: гума, технологія Air Max, дизайн lifestyle.',
                    'meta_description' => 'Кросівки Nike Air Max 270 - комфорт та стиль для активного життя. Оригінал з гарантією.',
                    'keywords' => ['nike', 'air max', 'кросівки', 'чоловічі', 'спорт'],
                ],
                [
                    'title' => 'Велосипед гірський Trek Marlin 7 29"',
                    'brand' => 'Trek',
                    'manufacturer' => 'Trek Bicycle Corporation',
                    'price' => 24999.00,
                    'old_price' => 0,
                    'weight' => 14.5,
                    'dimensions' => 'Рама M (173-178 см)',
                    'excerpt' => 'Гірський велосипед для трейлів та міських поїздок з алюмінієвою рамою.',
                    'content' => 'Trek Marlin 7 - ідеальний вибір для початківців та досвідчених велосипедистів. Легка алюмінієва рама Alpha Gold Aluminum, передня підвіска SR Suntour та 21 швидкість Shimano забезпечують комфортні поїздки будь-якою місцевістю.',
                    'description' => 'Колеса 29", перемикачі Shimano Altus, гальма дискові механічні, підвіска 100мм.',
                    'meta_description' => 'Гірський велосипед Trek Marlin 7 - якість та надійність для активного відпочинку.',
                    'keywords' => ['trek', 'велосипед', 'гірський', 'marlin', '29'],
                ],
            ],
        ];

        $categoryProducts = $detailedProducts[$category->title] ?? $detailedProducts['Електроніка'];
        $productTemplate = fake()->randomElement($categoryProducts);

        // Add some randomization to avoid duplicates
        $productTemplate['title'] .= ' '.fake()->randomElement(['Black', 'White', 'Blue', 'Red', 'Silver', 'Gray']);

        return $productTemplate;
    }
}
