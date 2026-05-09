<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\DisplaySetting;
use App\Services\TransliterationService;
use Illuminate\Console\Command;
use Meilisearch\Client;

class SetupMeilisearch extends Command
{
    protected $signature = 'search:setup {--fresh : Drop and recreate index}';
    protected $description = 'Setup Meilisearch index with typo tolerance, synonyms, and filterable attributes';

    public function handle(): int
    {
        $host = config('scout.meilisearch.host', 'http://meilisearch:7700');
        $key = config('scout.meilisearch.key', '');

        $this->info("Connecting to Meilisearch at {$host}...");

        try {
            $client = new Client($host, $key);
            $health = $client->health();
            $this->info("Meilisearch status: {$health['status']}");
        } catch (\Exception $e) {
            $this->error("Cannot connect to Meilisearch: {$e->getMessage()}");
            return 1;
        }

        $indexName = 'products';

        // Drop index if --fresh
        if ($this->option('fresh')) {
            $this->warn("Dropping index '{$indexName}'...");
            try {
                $task = $client->deleteIndex($indexName);
                $client->waitForTask($task['taskUid']);
            } catch (\Exception $e) {
                // Index might not exist
            }
        }

        // Create or get index
        $this->info("Creating/updating index '{$indexName}'...");
        $task = $client->createIndex($indexName, ['primaryKey' => 'id']);
        $client->waitForTask($task['taskUid']);

        $index = $client->index($indexName);

        // Searchable attributes (priority order from admin or defaults)
        $this->info('Setting searchable attributes...');
        $customSearchable = DisplaySetting::get('search_searchable_attrs');
        $searchableAttrs = $customSearchable
            ? (is_string($customSearchable) ? json_decode($customSearchable, true) : $customSearchable)
            : ['title', 'title_en', 'sku', 'brand', 'manufacturer', 'excerpt', 'excerpt_en',
               'category_title', 'category_title_en', 'options_text', 'search_tags', 'title_lemmas', 'content'];
        $index->updateSearchableAttributes($searchableAttrs);

        // Filterable attributes (from admin or defaults)
        $this->info('Setting filterable attributes...');
        $customFilterable = DisplaySetting::get('search_filterable_attrs');
        $filterableAttrs = $customFilterable
            ? (is_string($customFilterable) ? json_decode($customFilterable, true) : $customFilterable)
            : ['category_id', 'brand', 'price', 'is_hit', 'is_new', 'is_active', 'is_special', 'discount_percent', 'rating'];
        $index->updateFilterableAttributes($filterableAttrs);

        // Sortable attributes
        $this->info('Setting sortable attributes...');
        $index->updateSortableAttributes([
            'price',
            'created_at',
            'rating',
            'reviews_count',
            'title',
        ]);

        // Typo tolerance settings (from admin or defaults)
        $this->info('Configuring typo tolerance...');
        $typoEnabled = DisplaySetting::get('search_typo_tolerance');
        $typoOneTypo = (int) DisplaySetting::get('search_min_word_1_typo', 3);
        $typoTwoTypos = (int) DisplaySetting::get('search_min_word_2_typos', 6);
        $index->updateTypoTolerance([
            'enabled' => $typoEnabled === null ? true : (bool) $typoEnabled,
            'minWordSizeForTypos' => [
                'oneTypo' => max(1, $typoOneTypo),
                'twoTypos' => max(2, $typoTwoTypos),
            ],
        ]);

        // Load synonyms: from DisplaySetting first, fallback to hardcoded defaults
        $this->info('Setting synonyms...');
        $customSynonyms = DisplaySetting::where('key', 'search_synonyms')->first();
        if ($customSynonyms && $customSynonyms->value) {
            $raw = $customSynonyms->value;
            $manualSynonyms = is_string($raw) ? json_decode($raw, true) : $raw;
            if (!is_array($manualSynonyms)) {
                $manualSynonyms = [];
            }
            $this->info('  Loaded ' . count($manualSynonyms) . ' synonym groups from database');
        } else {
            $manualSynonyms = [
                // Electronics - brands
                'айфон' => ['iphone', 'apple', 'эпл', 'епл'],
                'самсунг' => ['samsung', 'галаксі', 'galaxy'],
                'сяомі' => ['xiaomi', 'ксіаомі', 'редмі', 'redmi'],
                'хуавей' => ['huawei', 'хуавєй'],
                'макбук' => ['macbook', 'мак', 'mac'],
                'аір' => ['air'],
                'про' => ['pro'],
                'макс' => ['max'],
                'міні' => ['mini'],
                'ультра' => ['ultra'],
                'галаксі' => ['galaxy'],
                'редмі' => ['redmi'],
                'поко' => ['poco'],
                // Electronics - products
                'ноутбук' => ['ноут', 'лептоп', 'laptop', 'ноутбуки', 'нотбук'],
                'телефон' => ['смартфон', 'мобільний', 'мобілка', 'phone', 'smartphone'],
                'навушники' => ['наушники', 'headphones', 'гарнітура', 'earbuds'],
                'планшет' => ['таблет', 'tablet', 'планшети'],
                'телевізор' => ['телевизор', 'tv', 'телек'],
                'камера' => ['фотоапарат', 'фотокамера', 'camera'],
                'клавіатура' => ['клавиатура', 'keyboard'],
                'мишка' => ['мишь', 'mouse', 'маніпулятор'],
                'монітор' => ['монитор', 'monitor', 'дисплей', 'екран'],
                'принтер' => ['printer', 'мфу'],
                // Clothing
                'футболка' => ['майка', 'tshirt', 'тішка'],
                'штани' => ['штаны', 'брюки', 'pants', 'джинси'],
                'куртка' => ['jacket', 'пуховик', 'вітровка'],
                'взуття' => ['обувь', 'shoes', 'кросівки', 'кроссовки'],
                // General
                'купити' => ['замовити', 'придбати', 'buy'],
                'дешево' => ['недорого', 'бюджетний', 'cheap', 'доступний'],
                'якісний' => ['хороший', 'кращий', 'quality', 'топ'],
                'новий' => ['новинка', 'new', 'свіжий'],
                'акція' => ['знижка', 'sale', 'розпродаж', 'скидка'],
                // Intent-based synonyms (semantic search support)
                'дешевий' => ['бюджетний', 'недорого', 'економ', 'доступний', 'cheap'],
                'для ігор' => ['gaming', 'геймінг', 'ігровий', 'гра', 'ігри'],
                'ігор' => ['gaming', 'геймінг', 'ігровий', 'гра', 'ігри', 'для ігор'],
                'ігри' => ['gaming', 'геймінг', 'ігровий', 'гра', 'для ігор', 'ігор'],
                'геймінг' => ['gaming', 'ігровий', 'гра', 'для ігор', 'ігри'],
                'для роботи' => ['офісний', 'бізнес', 'робочий', 'professional'],
                'для навчання' => ['студентський', 'навчальний', 'школа', 'student'],
                'для дому' => ['домашній', 'побутовий', 'home'],
                'подарунок' => ['gift', 'ідея подарунка', 'презент'],
                'преміум' => ['дорогий', 'елітний', 'люкс', 'premium', 'топ'],
            ];
            $this->info('  Using default synonym groups');
        }

        // Auto-generate brand synonyms (Ukrainian name <-> transliterated Latin)
        $this->info('Generating brand synonyms from database...');
        $transliterator = app(TransliterationService::class);
        $brandSynonyms = [];

        foreach (Brand::all() as $brand) {
            $name = mb_strtolower($brand->name);
            $transliterated = $transliterator->transliterate($name);

            if ($name !== $transliterated && mb_strlen($name) >= 3) {
                $brandSynonyms[$transliterated] = [$name];
                $brandSynonyms[$name] = [$transliterated];
            }
        }

        $this->info('  Generated ' . count($brandSynonyms) . ' brand synonym pairs');
        $index->updateSynonyms(array_merge($manualSynonyms, $brandSynonyms));

        // Load stop words: from DisplaySetting first, fallback to hardcoded defaults
        $this->info('Setting stop words...');
        $customStopWords = DisplaySetting::where('key', 'search_stop_words')->first();
        if ($customStopWords && $customStopWords->value) {
            $raw = $customStopWords->value;
            $stopWords = is_string($raw) ? json_decode($raw, true) : $raw;
            if (!is_array($stopWords)) {
                $stopWords = [];
            }
            $this->info('  Loaded ' . count($stopWords) . ' stop words from database');
        } else {
            $stopWords = [
                // Ukrainian
                'і', 'в', 'на', 'з', 'до', 'за', 'від', 'для', 'та', 'що', 'як',
                'це', 'не', 'але', 'або', 'при', 'без', 'під', 'над', 'між',
                'щось', 'якийсь', 'якась', 'якесь', 'будь', 'який', 'яка', 'яке',
                'мені', 'мене', 'собі', 'потрібно', 'треба', 'хочу',
                // English
                'the', 'a', 'an', 'is', 'in', 'on', 'for', 'of', 'and', 'or', 'with',
            ];
            $this->info('  Using default stop words');
        }
        $index->updateStopWords($stopWords);

        // Ranking rules
        $this->info('Setting ranking rules...');
        $index->updateRankingRules([
            'words',
            'typo',
            'proximity',
            'attribute',
            'sort',
            'exactness',
        ]);

        // Pagination settings
        $index->updatePagination(['maxTotalHits' => 10000]);

        $this->info('Products index configured successfully!');

        // Import products
        $this->info('Importing products...');
        $this->call('scout:import', ['model' => 'App\\Models\\Product']);

        // Setup categories index
        $this->info('Setting up categories index...');
        $catIndexName = 'categories';

        if ($this->option('fresh')) {
            $this->warn("Dropping index '{$catIndexName}'...");
            try {
                $task = $client->deleteIndex($catIndexName);
                $client->waitForTask($task['taskUid']);
            } catch (\Exception $e) {
                // Index might not exist
            }
        }

        $task = $client->createIndex($catIndexName, ['primaryKey' => 'id']);
        $client->waitForTask($task['taskUid']);
        $catIndex = $client->index($catIndexName);

        $this->info('Setting categories searchable attributes...');
        $catIndex->updateSearchableAttributes(['title', 'title_en']);

        $this->info('Setting categories filterable attributes...');
        $catIndex->updateFilterableAttributes(['is_active', 'parent_id']);

        $this->info('Importing categories...');
        $this->call('scout:import', ['model' => 'App\\Models\\Category']);

        $this->info('Meilisearch setup complete!');

        // Show stats
        $stats = $index->stats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Index', 'products'],
                ['Documents', $stats['numberOfDocuments']],
                ['Is indexing', $stats['isIndexing'] ? 'Yes' : 'No'],
            ]
        );

        $catStats = $catIndex->stats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Index', 'categories'],
                ['Documents', $catStats['numberOfDocuments']],
                ['Is indexing', $catStats['isIndexing'] ? 'Yes' : 'No'],
            ]
        );

        return 0;
    }
}
