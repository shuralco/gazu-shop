<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use App\Models\Product;
use App\Models\SearchQuery;
use App\Services\AiContentGenerator;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Meilisearch\Client;

class SearchManagement extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Управління пошуком';

    protected static ?string $title = 'Управління пошуком';

    protected static ?string $navigationGroup = 'Налаштування';

    protected static ?int $navigationSort = 98;

    protected static string $view = 'filament.pages.search-management';

    protected static ?string $slug = 'search-management';

    public string $activeTab = 'analytics';

    // Synonyms form
    public array $synonymGroups = [];

    // Stop words form
    public string $stopWordsText = '';

    // Index settings form
    public bool $typoToleranceEnabled = true;
    public int $minWordOneTypo = 3;
    public int $minWordTwoTypos = 6;

    // Searchable/Filterable attributes (editable)
    public array $searchableAttrs = [];
    public array $filterableAttrs = [];
    public array $allAvailableAttrs = [
        'title', 'title_en', 'sku', 'brand', 'manufacturer',
        'excerpt', 'excerpt_en', 'category_title', 'category_title_en',
        'options_text', 'search_tags', 'title_lemmas', 'content',
    ];
    public array $allFilterableOptions = [
        'category_id', 'brand', 'brand_id', 'price', 'old_price',
        'is_hit', 'is_new', 'is_active', 'is_special',
        'discount_percent', 'rating', 'reviews_count', 'created_at',
    ];

    // Zero-result synonym modal
    public bool $showSynonymModal = false;
    public string $synonymModalQuery = '';
    public string $synonymModalSynonyms = '';

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'analytics');
        $this->loadSynonyms();
        $this->loadStopWords();
        $this->loadIndexSettings();
    }

    public function updatedActiveTab(): void
    {
        // Tab change is handled by Livewire reactivity
    }

    // ─── Analytics Stats ──────────────────────────────────────────────

    public function getAnalyticsStatsProperty(): array
    {
        $total = SearchQuery::sum('search_count');
        $unique = SearchQuery::count();
        $zeroResultCount = SearchQuery::where('results_count', 0)->count();
        $zeroResultPercent = $unique > 0 ? round(($zeroResultCount / $unique) * 100, 1) : 0;

        $totalClicks = SearchQuery::sum('click_count');
        $avgCtr = $total > 0 ? round(($totalClicks / $total) * 100, 1) : 0;

        return [
            'total_searches' => number_format($total),
            'unique_queries' => number_format($unique),
            'zero_result_percent' => $zeroResultPercent,
            'avg_ctr' => $avgCtr,
        ];
    }

    // ─── Analytics Table ──────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(SearchQuery::query())
            ->defaultSort('search_count', 'desc')
            ->columns([
                TextColumn::make('query')
                    ->label('Запит')
                    ->searchable()
                    ->limit(50)
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('search_count')
                    ->label('Пошуків')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('results_count')
                    ->label('Результатів')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),

                TextColumn::make('click_count')
                    ->label('Кліків')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('ctr')
                    ->label('CTR')
                    ->alignCenter()
                    ->state(function (SearchQuery $record): string {
                        if ($record->search_count === 0) return '0%';
                        return round(($record->click_count / $record->search_count) * 100, 1) . '%';
                    })
                    ->color(function (SearchQuery $record): string {
                        if ($record->search_count === 0) return 'gray';
                        $ctr = ($record->click_count / $record->search_count) * 100;
                        if ($ctr >= 30) return 'success';
                        if ($ctr >= 10) return 'warning';
                        return 'danger';
                    }),

                TextColumn::make('last_searched_at')
                    ->label('Останній пошук')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Filter::make('zero_results')
                    ->label('Без результатів')
                    ->query(fn (Builder $query) => $query->where('results_count', 0))
                    ->toggle(),

                Filter::make('min_searches')
                    ->label('Мін. пошуків')
                    ->form([
                        TextInput::make('min_count')
                            ->label('Мінімум пошуків')
                            ->numeric()
                            ->default(2),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['min_count']) {
                            $query->where('search_count', '>=', (int) $data['min_count']);
                        }
                    }),

                Filter::make('date_range')
                    ->label('Період')
                    ->form([
                        TextInput::make('date_from')
                            ->label('Від')
                            ->type('date'),
                        TextInput::make('date_to')
                            ->label('До')
                            ->type('date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['date_from']) {
                            $query->where('last_searched_at', '>=', $data['date_from']);
                        }
                        if ($data['date_to']) {
                            $query->where('last_searched_at', '<=', $data['date_to'] . ' 23:59:59');
                        }
                    }),
            ])
            ->actions([
                TableAction::make('add_synonym')
                    ->label('Додати синонім')
                    ->icon('heroicon-o-plus-circle')
                    ->color('warning')
                    ->visible(fn (SearchQuery $record) => $record->results_count === 0)
                    ->action(function (SearchQuery $record) {
                        $this->synonymModalQuery = $record->query;
                        $this->synonymModalSynonyms = '';
                        $this->showSynonymModal = true;
                    }),
            ])
            ->headerActions([
                TableAction::make('export_csv')
                    ->label('Експорт CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        return $this->exportCsv();
                    }),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->emptyStateHeading('Немає даних пошуку')
            ->emptyStateDescription('Дані з\'являться після того, як користувачі почнуть шукати товари')
            ->emptyStateIcon('heroicon-o-magnifying-glass');
    }

    public function exportCsv()
    {
        $queries = SearchQuery::orderByDesc('search_count')->get();

        $csv = "Запит,Пошуків,Результатів,Кліків,CTR,Останній пошук\n";
        foreach ($queries as $q) {
            $ctr = $q->search_count > 0
                ? round(($q->click_count / $q->search_count) * 100, 1) . '%'
                : '0%';
            $csv .= '"' . str_replace('"', '""', $q->query) . '",'
                . $q->search_count . ','
                . $q->results_count . ','
                . $q->click_count . ','
                . $ctr . ','
                . ($q->last_searched_at?->format('d.m.Y H:i') ?? '') . "\n";
        }

        $filename = 'search_analytics_' . now()->format('Y-m-d') . '.csv';

        return Response::streamDownload(function () use ($csv) {
            echo "\xEF\xBB\xBF" . $csv; // BOM for Excel UTF-8
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ─── Synonyms ─────────────────────────────────────────────────────

    protected function loadSynonyms(): void
    {
        $stored = DisplaySetting::where('key', 'search_synonyms')->first();
        $synonymsData = null;

        if ($stored && $stored->value) {
            $raw = $stored->value;
            $synonymsData = is_string($raw) ? json_decode($raw, true) : $raw;
        }

        if (!$synonymsData) {
            $synonymsData = $this->getDefaultSynonyms();
        }

        $groups = [];
        foreach ($synonymsData as $mainWord => $synonymsList) {
            $groups[] = [
                'main_word' => $mainWord,
                'synonyms' => is_array($synonymsList) ? implode(', ', $synonymsList) : (string) $synonymsList,
            ];
        }

        $this->synonymGroups = $groups;
    }

    protected function getDefaultSynonyms(): array
    {
        return [
            'айфон' => ['iphone', 'apple', 'эпл', 'епл'],
            'самсунг' => ['samsung', 'галаксі', 'galaxy'],
            'сяомі' => ['xiaomi', 'ксіаомі', 'редмі', 'redmi'],
            'хуавей' => ['huawei', 'хуавєй'],
            'макбук' => ['macbook', 'мак', 'mac'],
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
            'футболка' => ['майка', 'tshirt', 'тішка'],
            'штани' => ['штаны', 'брюки', 'pants', 'джинси'],
            'куртка' => ['jacket', 'пуховик', 'вітровка'],
            'взуття' => ['обувь', 'shoes', 'кросівки', 'кроссовки'],
            'купити' => ['замовити', 'придбати', 'buy'],
            'дешево' => ['недорого', 'бюджетний', 'cheap', 'доступний'],
            'якісний' => ['хороший', 'кращий', 'quality', 'топ'],
            'новий' => ['новинка', 'new', 'свіжий'],
            'акція' => ['знижка', 'sale', 'розпродаж', 'скидка'],
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
    }

    public function addSynonymGroup(): void
    {
        $this->synonymGroups[] = [
            'main_word' => '',
            'synonyms' => '',
        ];
    }

    public function removeSynonymGroup(int $index): void
    {
        unset($this->synonymGroups[$index]);
        $this->synonymGroups = array_values($this->synonymGroups);
    }

    public function saveSynonyms(): void
    {
        $synonymsMap = [];

        foreach ($this->synonymGroups as $group) {
            $mainWord = mb_strtolower(trim($group['main_word'] ?? ''));
            $synonymsStr = $group['synonyms'] ?? '';

            if ($mainWord === '') continue;

            $synonymsList = array_filter(
                array_map(
                    fn ($s) => mb_strtolower(trim($s)),
                    preg_split('/[,;]+/', $synonymsStr)
                ),
                fn ($s) => $s !== ''
            );

            if (count($synonymsList) > 0) {
                $synonymsMap[$mainWord] = array_values($synonymsList);
            }
        }

        DisplaySetting::updateOrCreate(
            ['key' => 'search_synonyms'],
            [
                'value' => json_encode($synonymsMap, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'group' => 'search',
                'title' => 'Синоніми пошуку',
                'is_active' => true,
            ]
        );

        try {
            Artisan::call('search:setup');

            Notification::make()
                ->success()
                ->title('Синоніми збережено')
                ->body('Індекс успішно переналаштовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка переіндексації')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ─── Synonym Modal (from zero-result queries) ─────────────────────

    public function saveSynonymFromModal(): void
    {
        $mainWord = mb_strtolower(trim($this->synonymModalQuery));
        $synonyms = $this->synonymModalSynonyms;

        if ($mainWord === '' || $synonyms === '') {
            Notification::make()
                ->warning()
                ->title('Заповніть обидва поля')
                ->send();
            return;
        }

        $synonymsList = array_filter(
            array_map(
                fn ($s) => mb_strtolower(trim($s)),
                preg_split('/[,;]+/', $synonyms)
            ),
            fn ($s) => $s !== ''
        );

        // Add to existing synonym groups
        $found = false;
        foreach ($this->synonymGroups as &$group) {
            if (mb_strtolower(trim($group['main_word'])) === $mainWord) {
                $existing = array_filter(
                    array_map('trim', preg_split('/[,;]+/', $group['synonyms'])),
                    fn ($s) => $s !== ''
                );
                $merged = array_unique(array_merge($existing, $synonymsList));
                $group['synonyms'] = implode(', ', $merged);
                $found = true;
                break;
            }
        }
        unset($group);

        if (!$found) {
            $this->synonymGroups[] = [
                'main_word' => $mainWord,
                'synonyms' => implode(', ', $synonymsList),
            ];
        }

        $this->showSynonymModal = false;
        $this->synonymModalQuery = '';
        $this->synonymModalSynonyms = '';

        // Auto-save
        $this->saveSynonyms();
    }

    public function closeSynonymModal(): void
    {
        $this->showSynonymModal = false;
        $this->synonymModalQuery = '';
        $this->synonymModalSynonyms = '';
    }

    // ─── Stop Words ───────────────────────────────────────────────────

    protected function loadStopWords(): void
    {
        $stored = DisplaySetting::where('key', 'search_stop_words')->first();
        $stopWords = null;

        if ($stored && $stored->value) {
            $raw = $stored->value;
            $stopWords = is_string($raw) ? json_decode($raw, true) : $raw;
        }

        if (!$stopWords) {
            $stopWords = $this->getDefaultStopWords();
        }

        $this->stopWordsText = implode("\n", $stopWords);
    }

    protected function getDefaultStopWords(): array
    {
        return [
            'і', 'в', 'на', 'з', 'до', 'за', 'від', 'для', 'та', 'що', 'як',
            'це', 'не', 'але', 'або', 'при', 'без', 'під', 'над', 'між',
            'щось', 'якийсь', 'якась', 'якесь', 'будь', 'який', 'яка', 'яке',
            'мені', 'мене', 'собі', 'потрібно', 'треба', 'хочу',
            'the', 'a', 'an', 'is', 'in', 'on', 'for', 'of', 'and', 'or', 'with',
        ];
    }

    public function saveStopWords(): void
    {
        $lines = preg_split('/[\n,;]+/', $this->stopWordsText);
        $stopWords = array_values(array_filter(
            array_map(fn ($w) => mb_strtolower(trim($w)), $lines),
            fn ($w) => $w !== ''
        ));

        DisplaySetting::updateOrCreate(
            ['key' => 'search_stop_words'],
            [
                'value' => json_encode($stopWords, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'group' => 'search',
                'title' => 'Stop-слова пошуку',
                'is_active' => true,
            ]
        );

        try {
            Artisan::call('search:setup');

            Notification::make()
                ->success()
                ->title('Stop-слова збережено')
                ->body('Індекс успішно переналаштовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка переіндексації')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ─── Index Settings ───────────────────────────────────────────────

    protected function loadIndexSettings(): void
    {
        $this->typoToleranceEnabled = (bool) DisplaySetting::get('search_typo_tolerance', true);
        $this->minWordOneTypo = (int) DisplaySetting::get('search_min_word_1_typo', 3);
        $this->minWordTwoTypos = (int) DisplaySetting::get('search_min_word_2_typos', 6);

        $savedSearchable = DisplaySetting::get('search_searchable_attrs');
        $this->searchableAttrs = $savedSearchable
            ? (is_string($savedSearchable) ? json_decode($savedSearchable, true) : $savedSearchable)
            : $this->allAvailableAttrs;

        $savedFilterable = DisplaySetting::get('search_filterable_attrs');
        $this->filterableAttrs = $savedFilterable
            ? (is_string($savedFilterable) ? json_decode($savedFilterable, true) : $savedFilterable)
            : ['category_id', 'brand', 'price', 'is_hit', 'is_new', 'is_active', 'is_special', 'discount_percent', 'rating'];
    }

    public function moveAttrUp(int $index): void
    {
        if ($index <= 0 || $index >= count($this->searchableAttrs)) return;
        $temp = $this->searchableAttrs[$index - 1];
        $this->searchableAttrs[$index - 1] = $this->searchableAttrs[$index];
        $this->searchableAttrs[$index] = $temp;
    }

    public function moveAttrDown(int $index): void
    {
        if ($index < 0 || $index >= count($this->searchableAttrs) - 1) return;
        $temp = $this->searchableAttrs[$index + 1];
        $this->searchableAttrs[$index + 1] = $this->searchableAttrs[$index];
        $this->searchableAttrs[$index] = $temp;
    }

    public function toggleSearchableAttr(string $attr): void
    {
        if (in_array($attr, $this->searchableAttrs)) {
            $this->searchableAttrs = array_values(array_filter($this->searchableAttrs, fn($a) => $a !== $attr));
        } else {
            $this->searchableAttrs[] = $attr;
        }
    }

    public function toggleFilterableAttr(string $attr): void
    {
        if (in_array($attr, $this->filterableAttrs)) {
            $this->filterableAttrs = array_values(array_filter($this->filterableAttrs, fn($a) => $a !== $attr));
        } else {
            $this->filterableAttrs[] = $attr;
        }
    }

    public function saveIndexSettings(): void
    {
        $settings = [
            'search_typo_tolerance' => ['value' => $this->typoToleranceEnabled ? 'true' : 'false', 'type' => 'boolean'],
            'search_min_word_1_typo' => ['value' => (string) $this->minWordOneTypo, 'type' => 'integer'],
            'search_min_word_2_typos' => ['value' => (string) $this->minWordTwoTypos, 'type' => 'integer'],
            'search_searchable_attrs' => ['value' => json_encode($this->searchableAttrs), 'type' => 'json'],
            'search_filterable_attrs' => ['value' => json_encode($this->filterableAttrs), 'type' => 'json'],
        ];

        foreach ($settings as $key => $data) {
            DisplaySetting::updateOrCreate(['key' => $key], [
                'value' => $data['value'],
                'type' => $data['type'],
                'group' => 'search',
                'title' => $key,
                'is_active' => true,
            ]);
        }

        try {
            Artisan::call('search:setup');

            Notification::make()
                ->success()
                ->title('Налаштування збережено')
                ->body('Індекс переналаштовано з новими параметрами')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getMeilisearchInfoProperty(): array
    {
        try {
            $host = config('scout.meilisearch.host', 'http://meilisearch:7700');
            $key = config('scout.meilisearch.key', '');
            $client = new Client($host, $key);

            $health = $client->health();
            $version = $client->version();
            $index = $client->index('products');
            $stats = $index->stats();

            return [
                'status' => $health['status'] ?? 'unknown',
                'version' => $version['pkgVersion'] ?? 'N/A',
                'documents' => number_format($stats['numberOfDocuments'] ?? 0),
                'is_indexing' => $stats['isIndexing'] ?? false,
                'field_distribution' => $stats['fieldDistribution'] ?? [],
                'connected' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unavailable',
                'version' => 'N/A',
                'documents' => '0',
                'is_indexing' => false,
                'field_distribution' => [],
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getSearchableAttributesProperty(): array
    {
        return [
            'title',
            'title_en',
            'sku',
            'brand',
            'manufacturer',
            'excerpt',
            'excerpt_en',
            'category_title',
            'category_title_en',
            'options_text',
            'search_tags',
            'title_lemmas',
            'content',
        ];
    }

    public function getFilterableAttributesProperty(): array
    {
        return [
            'category_id',
            'brand',
            'price',
            'is_hit',
            'is_new',
            'is_active',
            'is_special',
            'discount_percent',
            'rating',
        ];
    }

    public function handleReindex(): void
    {
        try {
            Artisan::call('scout:flush', ['model' => 'App\\Models\\Product']);
            Artisan::call('scout:import', ['model' => 'App\\Models\\Product']);

            Cache::put('search_last_sync', now()->format('d.m.Y H:i:s'));

            Notification::make()
                ->success()
                ->title('Переіндексацію завершено')
                ->body('Всі товари переіндексовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка переіндексації')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function handleClearIndex(): void
    {
        try {
            Artisan::call('scout:flush', ['model' => 'App\\Models\\Product']);

            Notification::make()
                ->success()
                ->title('Індекс очищено')
                ->body('Всі документи видалено з індексу')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function handleFullRebuild(): void
    {
        try {
            Artisan::call('search:setup', ['--fresh' => true]);

            Cache::put('search_last_sync', now()->format('d.m.Y H:i:s'));

            Notification::make()
                ->success()
                ->title('Повну перебудову завершено')
                ->body('Індекс перестворено та всі товари імпортовано')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ─── Zero-result queries ──────────────────────────────────────────

    public function getZeroResultQueriesProperty()
    {
        return SearchQuery::where('results_count', 0)
            ->orderByDesc('search_count')
            ->limit(50)
            ->get();
    }

    public function ignoreZeroResultQuery(int $id): void
    {
        $query = SearchQuery::find($id);
        if ($query) {
            $query->delete();

            Notification::make()
                ->success()
                ->title('Запит видалено')
                ->send();
        }
    }

    public function openSynonymModalForQuery(int $id): void
    {
        $query = SearchQuery::find($id);
        if ($query) {
            $this->synonymModalQuery = $query->query;
            $this->synonymModalSynonyms = '';
            $this->showSynonymModal = true;
        }
    }

    // ─── AI Search Enhancement ───────────────────────────────────────

    public bool $showAiTagsModal = false;
    public string $aiTagsPrompt = '';
    public string $aiTagsResult = '';
    public int $aiTagsProgress = 0;
    public int $aiTagsTotal = 0;

    public bool $showAiSynonymsModal = false;
    public string $aiSynonymsPrompt = '';
    public string $aiSynonymsResult = '';

    /**
     * Generate AI search tags prompt for products without tags
     */
    public function generateAiTagsPrompt(): void
    {
        $products = Product::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('search_tags')->orWhere('search_tags', '');
            })
            ->with(['category', 'brandModel'])
            ->limit(20)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::where('is_active', true)
                ->with(['category', 'brandModel'])
                ->inRandomOrder()
                ->limit(10)
                ->get();
        }

        $this->aiTagsTotal = $products->count();

        $productsList = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->getTranslation('title', 'uk', false) ?: $p->title,
                'category' => $p->category?->getTranslation('title', 'uk', false) ?? '',
                'brand' => $p->brandModel?->name ?? '',
                'price' => (float) $p->price,
                'is_hit' => $p->is_hit,
                'is_new' => $p->is_new,
                'has_discount' => $p->old_price > $p->price,
            ];
        })->toArray();

        $json = json_encode($productsList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $this->aiTagsPrompt = <<<PROMPT
# ЗАДАЧА
Згенеруй пошукові теги (search_tags) для кожного товару інтернет-магазину.
Теги допомагають знайти товар навіть якщо людина шукає не точну назву, а:
- Синоніми (ноут, лептоп, мобілка)
- Призначення (для ігор, для роботи, для дому, подарунок)
- Цінову категорію (бюджетний, преміум, дорогий, дешевий)
- Характеристики (потужний, легкий, великий екран)
- Розмовні назви (айфон, самсунг, макбук)

# ТОВАРИ
{$json}

# ФОРМАТ ВІДПОВІДІ
Поверни ТІЛЬКИ JSON масив:
```json
[
  {"id": 1, "search_tags": "тег1, тег2, тег3, тег4, тег5, тег6, тег7, тег8"},
  {"id": 2, "search_tags": "..."}
]
```

# ВИМОГИ
- 8-15 тегів на товар через кому
- Українською, в нижньому регістрі
- Включай синоніми бренду (apple→еппл, samsung→самсунг)
- Включай розмовні назви (ноутбук→ноут, телефон→мобілка)
- Включай призначення (для ігор, для роботи, для навчання)
- Включай цінову категорію (бюджетний/середній/преміум за ціною)
- is_hit=true → додай: популярний, хіт, бестселер, рекомендований
- is_new=true → додай: новий, новинка
- has_discount=true → додай: акція, знижка, вигідно
- Поверни ТІЛЬКИ JSON, без пояснень
PROMPT;

        $this->showAiTagsModal = true;
    }

    /**
     * Apply AI-generated tags from JSON response
     */
    public function applyAiTags(): void
    {
        $json = $this->aiTagsResult;
        if (empty($json)) {
            Notification::make()->warning()->title('Вставте JSON результат')->send();
            return;
        }

        // Clean markdown
        $json = preg_replace('/```json\s*/i', '', $json);
        $json = preg_replace('/```\s*/', '', $json);
        $json = trim($json);

        $data = json_decode($json, true);
        if (!is_array($data)) {
            Notification::make()->danger()->title('Невалідний JSON')->body(json_last_error_msg())->send();
            return;
        }

        $updated = 0;
        $errors = 0;
        foreach ($data as $item) {
            $id = $item['id'] ?? null;
            $tags = $item['search_tags'] ?? null;
            if (!$id || !$tags) { $errors++; continue; }

            $product = Product::find($id);
            if (!$product) { $errors++; continue; }

            $product->search_tags = $tags;
            $product->saveQuietly();
            $product->searchable(); // re-index in Meilisearch
            $updated++;
        }

        $this->showAiTagsModal = false;
        $this->aiTagsResult = '';

        Notification::make()
            ->success()
            ->title("Теги оновлено: {$updated} товарів")
            ->body($errors > 0 ? "Помилок: {$errors}" : '')
            ->send();
    }

    /**
     * Auto-generate tags via API (if configured)
     */
    public function generateAiTagsViaApi(): void
    {
        $generator = app(AiContentGenerator::class);
        $provider = DisplaySetting::get('ai_provider', 'none');

        if ($provider === 'none' || empty($generator->getApiKey($provider))) {
            Notification::make()->warning()->title('API не налаштований')->body('Перейдіть в AI Генератор → Налаштування API')->send();
            return;
        }

        $response = $generator->callLlm($this->aiTagsPrompt, $provider);
        if (!$response) {
            Notification::make()->danger()->title('Помилка API')->send();
            return;
        }

        $this->aiTagsResult = $response;
        Notification::make()->success()->title('Відповідь отримана')->body('Перевірте та натисніть "Застосувати"')->send();
    }

    /**
     * Generate synonyms prompt from zero-result queries
     */
    public function generateAiSynonymsPrompt(): void
    {
        $zeroResults = SearchQuery::where('results_count', 0)
            ->orderByDesc('search_count')
            ->limit(30)
            ->pluck('query')
            ->toArray();

        if (empty($zeroResults)) {
            Notification::make()->info()->title('Немає запитів без результатів')->send();
            return;
        }

        $existingProducts = Product::where('is_active', true)
            ->limit(50)
            ->pluck('title')
            ->map(fn($t) => mb_strtolower($t))
            ->toArray();

        $queriesList = implode("\n", array_map(fn($q) => "- \"{$q}\"", $zeroResults));
        $productsList = implode(", ", array_slice($existingProducts, 0, 20));

        $this->aiSynonymsPrompt = <<<PROMPT
# ЗАДАЧА
Проаналізуй пошукові запити які не дали результатів в інтернет-магазині.
Для кожного запиту визнач: чи можна його вирішити синонімом до існуючого слова.

# ЗАПИТИ БЕЗ РЕЗУЛЬТАТІВ
{$queriesList}

# ПРИКЛАДИ ІСНУЮЧИХ ТОВАРІВ
{$productsList}

# ФОРМАТ ВІДПОВІДІ
Поверни ТІЛЬКИ JSON:
```json
[
  {"query": "запит", "main_word": "основне_слово", "synonyms": "синонім1, синонім2", "reason": "пояснення"},
  {"query": "запит2", "action": "skip", "reason": "такого товару немає в каталозі"}
]
```

# ВИМОГИ
- Якщо запит можна вирішити синонімом → вкажи main_word та synonyms
- Якщо товару реально немає → action: "skip"
- main_word має бути слово яке ВЖЕ є в назвах товарів
- synonyms — це те що людина ввела (query) або його варіації
- Все в нижньому регістрі
- Поверни ТІЛЬКИ JSON
PROMPT;

        $this->showAiSynonymsModal = true;
    }

    /**
     * Apply AI-generated synonyms
     */
    public function applyAiSynonyms(): void
    {
        $json = $this->aiSynonymsResult;
        if (empty($json)) {
            Notification::make()->warning()->title('Вставте JSON результат')->send();
            return;
        }

        $json = preg_replace('/```json\s*/i', '', $json);
        $json = preg_replace('/```\s*/', '', $json);
        $data = json_decode(trim($json), true);

        if (!is_array($data)) {
            Notification::make()->danger()->title('Невалідний JSON')->body(json_last_error_msg())->send();
            return;
        }

        $added = 0;
        foreach ($data as $item) {
            if (($item['action'] ?? '') === 'skip') continue;
            $mainWord = mb_strtolower(trim($item['main_word'] ?? ''));
            $synonyms = $item['synonyms'] ?? '';
            if (empty($mainWord) || empty($synonyms)) continue;

            // Add to synonym groups
            $found = false;
            foreach ($this->synonymGroups as &$group) {
                if (mb_strtolower(trim($group['main_word'])) === $mainWord) {
                    $existing = array_filter(array_map('trim', preg_split('/[,;]+/', $group['synonyms'])));
                    $new = array_filter(array_map('trim', preg_split('/[,;]+/', $synonyms)));
                    $group['synonyms'] = implode(', ', array_unique(array_merge($existing, $new)));
                    $found = true;
                    break;
                }
            }
            unset($group);

            if (!$found) {
                $this->synonymGroups[] = ['main_word' => $mainWord, 'synonyms' => $synonyms];
            }
            $added++;
        }

        $this->showAiSynonymsModal = false;
        $this->aiSynonymsResult = '';

        if ($added > 0) {
            $this->saveSynonyms();
            Notification::make()->success()->title("Додано {$added} груп синонімів")->body('Індекс переналаштовано')->send();
        } else {
            Notification::make()->info()->title('Нових синонімів не знайдено')->send();
        }
    }

    public function generateAiSynonymsViaApi(): void
    {
        $generator = app(AiContentGenerator::class);
        $provider = DisplaySetting::get('ai_provider', 'none');

        if ($provider === 'none' || empty($generator->getApiKey($provider))) {
            Notification::make()->warning()->title('API не налаштований')->send();
            return;
        }

        $response = $generator->callLlm($this->aiSynonymsPrompt, $provider);
        if (!$response) {
            Notification::make()->danger()->title('Помилка API')->send();
            return;
        }

        $this->aiSynonymsResult = $response;
        Notification::make()->success()->title('Відповідь отримана')->send();
    }
}
