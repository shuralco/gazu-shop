<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\TransliterationService;
use Illuminate\Console\Command;

class TranslateContent extends Command
{
    protected $signature = 'content:translate {--locale=en : Target locale}';

    protected $description = 'Auto-translate database content to target locale';

    // Simple Ukrainian → English dictionary for common e-commerce terms
    private array $dictionary = [
        // Categories
        'Електроніка' => 'Electronics',
        'Смартфони' => 'Smartphones',
        'Ноутбуки' => 'Laptops',
        'Планшети' => 'Tablets',
        'Телевізори' => 'TVs',
        'Навушники' => 'Headphones',
        'Фотокамери' => 'Cameras',
        'Ігрові консолі' => 'Gaming Consoles',
        'Аксесуари' => 'Accessories',
        'Одяг' => 'Clothing',
        'Чоловічий одяг' => 'Men\'s Clothing',
        'Жіночий одяг' => 'Women\'s Clothing',
        'Дитячий одяг' => 'Kids\' Clothing',
        'Спортивний одяг' => 'Sports Clothing',
        'Взуття' => 'Shoes',
        'Дім і сад' => 'Home & Garden',
        'Меблі' => 'Furniture',
        'Декор' => 'Decor',
        'Кухонне приладдя' => 'Kitchen Accessories',
        'Садівництво' => 'Gardening',
        'Освітлення' => 'Lighting',
        'Текстиль' => 'Textiles',
        'Спорт' => 'Sports',
        'Фітнес' => 'Fitness',
        'Футбол' => 'Football',
        'Баскетбол' => 'Basketball',
        'Туризм' => 'Tourism',
        'Водний спорт' => 'Water Sports',
        'Зимовий спорт' => 'Winter Sports',
        'Краса' => 'Beauty',
        'Косметика' => 'Cosmetics',
        'Парфумерія' => 'Perfume',
        'Догляд за шкірою' => 'Skin Care',
        'Догляд за волоссям' => 'Hair Care',
        'Манікюр' => 'Nail Care',
        'Авто' => 'Auto',
        'Запчастини' => 'Spare Parts',
        'Автохімія' => 'Car Chemistry',
        'Інструменти' => 'Tools',
        'Шини' => 'Tires',
        'Масла' => 'Oils',
        // Products common words
        'Смартфон' => 'Smartphone',
        'Ноутбук' => 'Laptop',
        'Планшет' => 'Tablet',
        'Телевізор' => 'TV',
        'Пилосос' => 'Vacuum Cleaner',
        'Диван' => 'Sofa',
        'Куртка' => 'Jacket',
        'Кросівки' => 'Sneakers',
        'Годинник' => 'Watch',
        'Навушники' => 'Headphones',
        'місний' => 'seater',
        'сірий' => 'Gray',
        'чорний' => 'Black',
        'білий' => 'White',
        'червоний' => 'Red',
        'синій' => 'Blue',
        'зелений' => 'Green',
        'Чоловіча' => 'Men\'s',
        'Жіноча' => 'Women\'s',
        'зимова' => 'Winter',
        'літня' => 'Summer',
        'Ультратонкий' => 'Ultra-thin',
        'Новітній' => 'Latest',
        'флагманський' => 'flagship',
        'Бездротові' => 'Wireless',
        'Професійні' => 'Professional',
        'Преміальні' => 'Premium',
    ];

    public function handle(TransliterationService $transliterator): int
    {
        $locale = $this->option('locale');
        $this->info("Translating content to '{$locale}'...");

        // Translate using direct DB updates to avoid model override issues
        $this->translateTable('categories', ['title'], $locale);
        $this->translateTable('brands', ['name'], $locale);
        $this->translateTable('products', ['title', 'excerpt'], $locale);

        // Translate horizontal menu items
        $this->info('Horizontal menu...');
        $menuSetting = \App\Models\DisplaySetting::where('key', 'horizontal_menu_items')->first();
        if ($menuSetting) {
            $items = is_string($menuSetting->value) ? json_decode($menuSetting->value, true) : $menuSetting->value;
            if (is_array($items)) {
                foreach ($items as &$item) {
                    if (isset($item['text'])) {
                        $item['text_' . $locale] = $this->translateText($item['text']);
                    }
                }
                $menuSetting->value = json_encode($items);
                $menuSetting->save();
            }
        }

        $this->info("Done! Content translated to '{$locale}'.");

        return self::SUCCESS;
    }

    private function translateTable(string $table, array $columns, string $locale): void
    {
        $this->info(ucfirst($table) . '...');
        $rows = \Illuminate\Support\Facades\DB::table($table)->get();
        $bar = $this->output->createProgressBar($rows->count());

        foreach ($rows as $row) {
            $updates = [];
            foreach ($columns as $col) {
                $raw = $row->{$col} ?? '';
                if (empty($raw)) {
                    $bar->advance();
                    continue;
                }

                // Parse existing JSON or wrap plain string
                $decoded = json_decode($raw, true);
                if (! is_array($decoded)) {
                    // Plain string — treat as uk
                    $decoded = ['uk' => $raw];
                }

                // Only translate if target locale missing
                if (empty($decoded[$locale])) {
                    $ukText = $decoded['uk'] ?? $raw;
                    $decoded[$locale] = $this->translateText($ukText);
                    $updates[$col] = json_encode($decoded, JSON_UNESCAPED_UNICODE);
                }
            }

            if (! empty($updates)) {
                \Illuminate\Support\Facades\DB::table($table)->where('id', $row->id)->update($updates);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function translateText(string $text): string
    {
        // Try exact match first
        if (isset($this->dictionary[$text])) {
            return $this->dictionary[$text];
        }

        // Try word-by-word replacement
        $translated = $text;
        // Sort by length desc to replace longer phrases first
        $sorted = $this->dictionary;
        uksort($sorted, fn ($a, $b) => mb_strlen($b) - mb_strlen($a));

        foreach ($sorted as $uk => $en) {
            $translated = str_replace($uk, $en, $translated);
        }

        return $translated;
    }
}
