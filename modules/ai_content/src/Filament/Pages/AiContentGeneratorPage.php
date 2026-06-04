<?php

namespace App\Filament\Pages;

use App\Models\AiGenerationLog;
use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Product;
use App\Services\AiContentGenerator;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiContentGeneratorPage extends Page
{
    use \App\Filament\Concerns\GatedPage;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'AI-генератор';

    protected static ?string $title = 'AI Генератор контенту';

    protected static ?string $navigationGroup = 'Обслуговування';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.pages.ai-content-generator';

    protected static ?string $slug = 'ai-content-generator';

    // ─── Tab state ───────────────────────────────────────────────────
    public string $activeTab = 'products';

    // ─── Tab 1: Product Generator ────────────────────────────────────
    public ?int $genCategoryId = null;
    public int $genCount = 5;
    public ?int $genPriceFrom = 500;
    public ?int $genPriceTo = 50000;
    public string $genLanguage = 'both';
    public string $genStyle = 'professional';
    public string $genInstructions = '';
    public string $generatedPrompt = '';
    public string $generatedJson = '';
    public array $previewProducts = [];
    public bool $showPreview = false;
    public bool $isGenerating = false;

    // ─── Tab 2: Enrichment ───────────────────────────────────────────
    public array $enrichProductIds = [];
    public string $enrichType = 'all'; // description, seo, tags, translate, all
    public string $enrichTargetLocale = 'en';
    public string $enrichPrompt = '';
    public string $enrichJson = '';
    public bool $isEnriching = false;

    // ─── Tab 3: API Settings ─────────────────────────────────────────
    public string $apiProvider = 'none';
    public string $apiKey = '';
    public string $apiModelOpenai = 'gpt-4o';
    public string $apiModelAnthropic = 'claude-sonnet-4-20250514';
    public float $apiTemperature = 0.7;
    public int $apiMaxTokens = 4000;
    public bool $isTesting = false;

    // ─── Tab 4: History ──────────────────────────────────────────────
    // (rendered inline from DB)

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'products');
        $this->loadApiSettings();
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    protected function getService(): AiContentGenerator
    {
        return app(AiContentGenerator::class);
    }

    protected function loadApiSettings(): void
    {
        $this->apiProvider = DisplaySetting::get('ai_provider', 'none') ?: 'none';
        $this->apiModelOpenai = DisplaySetting::get('ai_openai_model', 'gpt-4o') ?: 'gpt-4o';
        $this->apiModelAnthropic = DisplaySetting::get('ai_anthropic_model', 'claude-sonnet-4-20250514') ?: 'claude-sonnet-4-20250514';
        $this->apiTemperature = (float) (DisplaySetting::get('ai_temperature', 0.7) ?: 0.7);
        $this->apiMaxTokens = (int) (DisplaySetting::get('ai_max_tokens', 4000) ?: 4000);
        // Do not load API key into state for security; field starts empty
        $this->apiKey = '';
    }

    public function getIsApiConfiguredProperty(): bool
    {
        return $this->getService()->isApiConfigured();
    }

    public function getCategoriesProperty(): array
    {
        return Category::getHierarchicalOptions();
    }

    public function getProductsForEnrichmentProperty(): array
    {
        return Product::where('is_active', true)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Product $p) => [
                $p->id => "#{$p->id} — " . ($p->getTranslation('title', 'uk', false) ?: $p->title) . " ({$p->sku})",
            ])
            ->toArray();
    }

    public function getHistoryLogsProperty()
    {
        return AiGenerationLog::orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════════
    // TAB 1: PRODUCT GENERATOR
    // ═══════════════════════════════════════════════════════════════════

    public function handleGeneratePrompt(): void
    {
        if (!$this->genCategoryId) {
            Notification::make()
                ->warning()
                ->title('Оберіть категорію')
                ->send();
            return;
        }

        $this->generatedPrompt = $this->getService()->generateProductPrompt([
            'category_id' => $this->genCategoryId,
            'count' => $this->genCount,
            'price_from' => $this->genPriceFrom,
            'price_to' => $this->genPriceTo,
            'language' => $this->genLanguage,
            'style' => $this->genStyle,
            'instructions' => $this->genInstructions,
        ]);

        $this->generatedJson = '';
        $this->previewProducts = [];
        $this->showPreview = false;

        Notification::make()
            ->success()
            ->title('Промт згенеровано')
            ->body('Скопіюйте промт та вставте в ChatGPT, Claude або інший AI')
            ->send();
    }

    public function handleGenerateViaApi(): void
    {
        $service = $this->getService();

        if (!$service->isApiConfigured()) {
            Notification::make()
                ->warning()
                ->title('API не налаштовано')
                ->body('Перейдіть на вкладку "Налаштування API" для конфігурації')
                ->send();
            return;
        }

        if (!$this->genCategoryId) {
            Notification::make()
                ->warning()
                ->title('Оберіть категорію')
                ->send();
            return;
        }

        $this->isGenerating = true;

        $prompt = $this->generatedPrompt;
        if (!$prompt) {
            $prompt = $service->generateProductPrompt([
                'category_id' => $this->genCategoryId,
                'count' => $this->genCount,
                'price_from' => $this->genPriceFrom,
                'price_to' => $this->genPriceTo,
                'language' => $this->genLanguage,
                'style' => $this->genStyle,
                'instructions' => $this->genInstructions,
            ]);
            $this->generatedPrompt = $prompt;
        }

        try {
            $provider = $service->getProvider();
            $response = $service->callLlm($prompt, $provider);

            $this->generatedJson = $response;
            $this->previewProducts = $service->parseProductsFromJson($response);
            $this->showPreview = true;

            $service->logGeneration([
                'type' => 'products',
                'provider' => $provider,
                'model' => $service->getModel($provider),
                'prompt' => mb_substr($prompt, 0, 10000),
                'response' => mb_substr($response, 0, 50000),
                'tokens_used' => $this->estimateTokens($prompt . $response),
                'products_created' => 0,
                'status' => 'success',
            ]);

            Notification::make()
                ->success()
                ->title('Товари згенеровано')
                ->body(count($this->previewProducts) . ' товарів готові до перегляду')
                ->send();
        } catch (\Exception $e) {
            $service->logGeneration([
                'type' => 'products',
                'provider' => $service->getProvider(),
                'model' => $service->getModel($service->getProvider()),
                'prompt' => mb_substr($prompt, 0, 10000),
                'errors' => [$e->getMessage()],
                'status' => 'error',
            ]);

            Notification::make()
                ->danger()
                ->title('Помилка генерації')
                ->body($e->getMessage())
                ->send();
        }

        $this->isGenerating = false;
    }

    public function handleParseJson(): void
    {
        if (!$this->generatedJson) {
            Notification::make()
                ->warning()
                ->title('Вставте JSON')
                ->body('Вставте JSON відповідь від AI в текстове поле')
                ->send();
            return;
        }

        try {
            $this->previewProducts = $this->getService()->parseProductsFromJson($this->generatedJson);
            $this->showPreview = true;

            Notification::make()
                ->success()
                ->title('JSON розпарсено')
                ->body(count($this->previewProducts) . ' товарів готові до імпорту')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка парсингу JSON')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function handleImportProducts(): void
    {
        if (empty($this->previewProducts)) {
            Notification::make()
                ->warning()
                ->title('Немає товарів для імпорту')
                ->send();
            return;
        }

        $service = $this->getService();
        $result = $service->importProducts($this->previewProducts);

        $service->logGeneration([
            'type' => 'products',
            'provider' => 'manual',
            'products_created' => $result['created'],
            'errors' => $result['errors'] ?: null,
            'status' => empty($result['errors']) ? 'success' : 'error',
        ]);

        if ($result['created'] > 0) {
            Notification::make()
                ->success()
                ->title("Імпортовано {$result['created']} товарів")
                ->body(
                    !empty($result['errors'])
                        ? 'Помилки: ' . implode('; ', array_slice($result['errors'], 0, 3))
                        : 'Всі товари успішно створено'
                )
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Жоден товар не імпортовано')
                ->body(implode('; ', array_slice($result['errors'], 0, 5)))
                ->send();
        }

        $this->previewProducts = [];
        $this->showPreview = false;
    }

    public function removePreviewProduct(int $index): void
    {
        unset($this->previewProducts[$index]);
        $this->previewProducts = array_values($this->previewProducts);

        if (empty($this->previewProducts)) {
            $this->showPreview = false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // TAB 2: ENRICHMENT
    // ═══════════════════════════════════════════════════════════════════

    public function handleEnrichPrompt(): void
    {
        if (empty($this->enrichProductIds)) {
            Notification::make()
                ->warning()
                ->title('Оберіть товари')
                ->send();
            return;
        }

        $service = $this->getService();
        $prompts = [];

        foreach ($this->enrichProductIds as $productId) {
            $product = Product::find($productId);
            if (!$product) continue;

            $productTitle = $product->getTranslation('title', 'uk', false) ?: "#{$product->id}";

            $prompt = match ($this->enrichType) {
                'description' => $service->generateEnrichmentPrompt($product),
                'seo' => $service->generateSeoPrompt($product),
                'tags' => $service->generateSearchTagsPrompt($product),
                'translate' => $service->generateTranslationPrompt($product, $this->enrichTargetLocale),
                default => $service->generateEnrichmentPrompt($product),
            };

            $prompts[] = "=== ТОВАР: {$productTitle} (ID: {$productId}) ===\n\n{$prompt}";
        }

        $this->enrichPrompt = implode("\n\n" . str_repeat('─', 80) . "\n\n", $prompts);
        $this->enrichJson = '';

        Notification::make()
            ->success()
            ->title('Промт згенеровано')
            ->body(count($prompts) . ' промтів для обраних товарів')
            ->send();
    }

    public function handleEnrichViaApi(): void
    {
        $service = $this->getService();

        if (!$service->isApiConfigured()) {
            Notification::make()
                ->warning()
                ->title('API не налаштовано')
                ->send();
            return;
        }

        if (empty($this->enrichProductIds)) {
            Notification::make()
                ->warning()
                ->title('Оберіть товари')
                ->send();
            return;
        }

        $this->isEnriching = true;
        $provider = $service->getProvider();
        $updated = 0;
        $errors = [];

        foreach ($this->enrichProductIds as $productId) {
            $product = Product::find($productId);
            if (!$product) continue;

            $prompt = match ($this->enrichType) {
                'description' => $service->generateEnrichmentPrompt($product),
                'seo' => $service->generateSeoPrompt($product),
                'tags' => $service->generateSearchTagsPrompt($product),
                'translate' => $service->generateTranslationPrompt($product, $this->enrichTargetLocale),
                default => $service->generateEnrichmentPrompt($product),
            };

            try {
                $response = $service->callLlm($prompt, $provider);
                $data = $service->parseUpdateFromJson($response);
                $service->applyUpdateToProduct($product, $data);
                $updated++;
            } catch (\Exception $e) {
                $errors[] = "#{$productId}: {$e->getMessage()}";
            }
        }

        $type = $this->enrichType === 'translate' ? 'translation' : $this->enrichType;
        $service->logGeneration([
            'type' => $type,
            'provider' => $provider,
            'model' => $service->getModel($provider),
            'products_updated' => $updated,
            'errors' => $errors ?: null,
            'status' => empty($errors) ? 'success' : ($updated > 0 ? 'success' : 'error'),
        ]);

        $this->isEnriching = false;

        if ($updated > 0) {
            Notification::make()
                ->success()
                ->title("Оновлено {$updated} товарів")
                ->body(!empty($errors) ? 'Деякі помилки: ' . implode('; ', array_slice($errors, 0, 3)) : '')
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Жоден товар не оновлено')
                ->body(implode('; ', array_slice($errors, 0, 5)))
                ->send();
        }
    }

    public function handleApplyEnrichJson(): void
    {
        if (!$this->enrichJson || empty($this->enrichProductIds)) {
            Notification::make()
                ->warning()
                ->title('Вставте JSON та оберіть товари')
                ->send();
            return;
        }

        $service = $this->getService();

        try {
            $data = $service->parseUpdateFromJson($this->enrichJson);
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Помилка парсингу JSON')
                ->body($e->getMessage())
                ->send();
            return;
        }

        // Apply to first selected product (single-product enrichment)
        $productId = $this->enrichProductIds[0] ?? null;
        $product = $productId ? Product::find($productId) : null;

        if (!$product) {
            Notification::make()
                ->danger()
                ->title('Товар не знайдено')
                ->send();
            return;
        }

        $service->applyUpdateToProduct($product, $data);

        $service->logGeneration([
            'type' => $this->enrichType,
            'provider' => 'manual',
            'products_updated' => 1,
            'status' => 'success',
        ]);

        Notification::make()
            ->success()
            ->title('Товар оновлено')
            ->body($product->getTranslation('title', 'uk', false) ?: "#{$product->id}")
            ->send();
    }

    // ═══════════════════════════════════════════════════════════════════
    // TAB 3: API SETTINGS
    // ═══════════════════════════════════════════════════════════════════

    public function handleSaveApiSettings(): void
    {
        $settings = [
            'ai_provider' => ['value' => $this->apiProvider, 'type' => 'string'],
            'ai_openai_model' => ['value' => $this->apiModelOpenai, 'type' => 'string'],
            'ai_anthropic_model' => ['value' => $this->apiModelAnthropic, 'type' => 'string'],
            'ai_temperature' => ['value' => (string) $this->apiTemperature, 'type' => 'number'],
            'ai_max_tokens' => ['value' => (string) $this->apiMaxTokens, 'type' => 'integer'],
        ];

        // Only save API key if provided (non-empty)
        if ($this->apiKey !== '') {
            $keyField = "ai_{$this->apiProvider}_api_key";
            $settings[$keyField] = ['value' => $this->apiKey, 'type' => 'string'];
        }

        foreach ($settings as $key => $data) {
            DisplaySetting::updateOrCreate(['key' => $key], [
                'value' => $data['value'],
                'type' => $data['type'],
                'group' => 'ai',
                'title' => $key,
                'is_active' => true,
            ]);
        }

        DisplaySetting::flushSettingsCache();

        $this->apiKey = '';

        Notification::make()
            ->success()
            ->title('Налаштування збережено')
            ->send();
    }

    public function handleTestConnection(): void
    {
        $this->isTesting = true;

        // Save settings first if key is provided
        if ($this->apiKey !== '') {
            $this->handleSaveApiSettings();
        }

        $service = $this->getService();
        $provider = $this->apiProvider;

        if ($provider === 'none') {
            Notification::make()
                ->warning()
                ->title('Оберіть провайдера')
                ->send();
            $this->isTesting = false;
            return;
        }

        $result = $service->testConnection($provider);

        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Підключення успішне')
                ->body($result['message'])
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Помилка підключення')
                ->body($result['message'])
                ->send();
        }

        $this->isTesting = false;
    }

    // ─── Utility ─────────────────────────────────────────────────────

    protected function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token for mixed content
        return (int) ceil(mb_strlen($text) / 4);
    }
}
