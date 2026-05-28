<?php

namespace App\Services;

use App\Models\AiGenerationLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DisplaySetting;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiContentGenerator
{
    /**
     * Generate a detailed prompt that when sent to any LLM returns valid JSON
     * for importing products into SimpleShop.
     */
    public function generateProductPrompt(array $options): string
    {
        $category = null;
        if (!empty($options['category_id'])) {
            $category = Category::find($options['category_id']);
        }

        $count = $options['count'] ?? 5;
        $priceFrom = $options['price_from'] ?? 500;
        $priceTo = $options['price_to'] ?? 50000;
        $language = $options['language'] ?? 'both';
        $style = $options['style'] ?? 'professional';
        $instructions = $options['instructions'] ?? '';

        $categoryName = $category
            ? ($category->getTranslation('title', 'uk', false) ?: $category->title)
            : 'загальна категорія';

        $styleDescription = match ($style) {
            'casual' => 'Стиль: розмовний, дружній, як у блогера-оглядача. Використовуй живу мову, емоції, порівняння.',
            'technical' => 'Стиль: технічний, з детальними характеристиками, порівняннями з конкурентами, бенчмарками.',
            default => 'Стиль: професійний, лаконічний, орієнтований на продаж. Чітко, по суті, з вигодами для покупця.',
        };

        $languageInstruction = match ($language) {
            'uk' => 'Генеруй ТІЛЬКИ українською мовою. Поля _en залишай порожніми рядками "".',
            'en' => 'Генеруй ТІЛЬКИ англійською мовою. Поля _uk залишай порожніми рядками "".',
            default => 'Генеруй ОБОМА мовами: українською (_uk) та англійською (_en). Переклад повинен бути природним, не дослівним.',
        };

        $existingBrands = Brand::where('is_active', true)->pluck('name')->implode(', ');
        $brandNote = $existingBrands
            ? "Існуючі бренди в магазині: {$existingBrands}. Використовуй ці бренди де можливо, або створюй реалістичні нові."
            : 'Використовуй реалістичні відомі бренди для цієї категорії.';

        $prompt = <<<PROMPT
# ЗАДАЧА
Згенеруй JSON масив з {$count} товарами для інтернет-магазину.
Категорія: "{$categoryName}"
{$styleDescription}

# МОВА
{$languageInstruction}

# ВИМОГИ ДО ЦІНOУТВОРЕННЯ
- Ціни в українських гривнях (UAH), цілі числа або з копійками (десяткові до 2 знаків)
- Діапазон цін: від {$priceFrom} до {$priceTo} грн
- old_price (стара ціна) повинна бути на 10-30% вищою за price, або null якщо товар без знижки
- Приблизно 30-40% товарів повинні мати old_price (тобто бути зі знижкою)
- Ціни повинні бути реалістичними для українського ринку 2024-2025 року

# БРЕНДИ
{$brandNote}

# ФОРМАТ SLUG
- Транслітерація з українських літер: а=a, б=b, в=v, г=h, ґ=g, д=d, е=e, є=ye, ж=zh, з=z, и=y, і=i, ї=yi, й=y, к=k, л=l, м=m, н=n, о=o, п=p, р=r, с=s, т=t, у=u, ф=f, х=kh, ц=ts, ч=ch, ш=sh, щ=shch, ю=yu, я=ya
- Тільки маленькі літери, цифри та дефіси
- Без пробілів, без спецсимволів, без крапок
- Приклад: "Смартфон Samsung Galaxy S24" → "smartfon-samsung-galaxy-s24"

# SEO ВИМОГИ
- meta_title_uk/meta_title_en: до 70 символів, містить ключове слово + "купити" / "buy" + бренд
- meta_description_uk/meta_description_en: до 160 символів, містить переваги, заклик до дії
- search_tags: 5-10 ключових слів через кому, українською, в нижньому регістрі

# КОНТЕНТ (content_uk, content_en)
Повинен містити HTML розмітку:
- <h2> для заголовків секцій
- <p> для абзаців
- <ul><li> для списків характеристик/переваг
- Мінімум 3 абзаци або секції
- НЕ використовуй <h1> (він вже є на сторінці)

# EXCERPT (excerpt_uk, excerpt_en)
Короткий опис 1-2 речення, plain text (без HTML), до 200 символів.

# SKU ФОРМАТ
3 літери бренду (великі) + дефіс + коротка модель, наприклад: SAM-GS24U, APP-IP15PM, XIА-RN13

# JSON СХЕМА (строго дотримуйся!)
Поверни ТІЛЬКИ валідний JSON масив без додаткового тексту, без markdown блоків, без пояснень.
Кожен об'єкт масиву повинен мати ТОЧНО такі поля:

```json
[
  {
    "title_uk": "string — назва товару українською",
    "title_en": "string — назва товару англійською",
    "slug_uk": "string — slug українською (транслітерація)",
    "slug_en": "string — slug англійською",
    "excerpt_uk": "string — короткий опис українською (plain text, до 200 символів)",
    "excerpt_en": "string — короткий опис англійською (plain text, до 200 символів)",
    "content_uk": "string — повний HTML опис українською (h2, p, ul/li)",
    "content_en": "string — повний HTML опис англійською (h2, p, ul/li)",
    "sku": "string — артикул у форматі БРН-МОДЕЛЬ",
    "price": 12999,
    "old_price": 15999,
    "category_id": {$options['category_id']},
    "brand": "string — назва бренду",
    "is_active": true,
    "is_hit": false,
    "is_new": true,
    "meta_title_uk": "string — SEO заголовок українською (до 70 символів)",
    "meta_title_en": "string — SEO заголовок англійською (до 70 символів)",
    "meta_description_uk": "string — SEO опис українською (до 160 символів)",
    "meta_description_en": "string — SEO опис англійською (до 160 символів)",
    "search_tags": "string — ключові слова через кому, українською"
  }
]
```

# ПРИКЛАД ОДНОГО ТОВАРУ
```json
{
  "title_uk": "Бездротові навушники Sony WH-1000XM5",
  "title_en": "Sony WH-1000XM5 Wireless Headphones",
  "slug_uk": "bezdrotovi-navushnyky-sony-wh-1000xm5",
  "slug_en": "sony-wh-1000xm5-wireless-headphones",
  "excerpt_uk": "Преміальні бездротові навушники з найкращим шумозаглушенням у своєму класі та до 30 годин автономної роботи.",
  "excerpt_en": "Premium wireless headphones with industry-leading noise cancellation and up to 30 hours of battery life.",
  "content_uk": "<h2>Огляд</h2><p>Sony WH-1000XM5 — це вершина розвитку бездротових навушників.</p><h2>Ключові переваги</h2><ul><li>Найкраще шумозаглушення у класі</li><li>До 30 годин роботи від батареї</li><li>Підтримка LDAC та Hi-Res Audio</li></ul><h2>Комплектація</h2><p>Навушники, кейс для переноски, USB-C кабель, аудіо кабель 3.5 мм.</p>",
  "content_en": "<h2>Overview</h2><p>Sony WH-1000XM5 represents the pinnacle of wireless headphone technology.</p><h2>Key Features</h2><ul><li>Industry-leading noise cancellation</li><li>Up to 30 hours of battery life</li><li>LDAC and Hi-Res Audio support</li></ul><h2>What's in the Box</h2><p>Headphones, carrying case, USB-C cable, 3.5mm audio cable.</p>",
  "sku": "SNY-WH1000XM5",
  "price": 12999,
  "old_price": 14999,
  "category_id": 1,
  "brand": "Sony",
  "is_active": true,
  "is_hit": true,
  "is_new": false,
  "meta_title_uk": "Купити Sony WH-1000XM5 навушники — ціна в Україні",
  "meta_title_en": "Buy Sony WH-1000XM5 Headphones — Best Price",
  "meta_description_uk": "Бездротові навушники Sony WH-1000XM5 з шумозаглушенням. Доставка по Україні. Гарантія 2 роки.",
  "meta_description_en": "Sony WH-1000XM5 wireless headphones with noise cancellation. Free delivery. 2-year warranty.",
  "search_tags": "навушники, бездротові, sony, шумозаглушення, bluetooth, преміум, аудіо"
}
```

# ДОДАТКОВІ ІНСТРУКЦІЇ
- Генеруй {$count} різноманітних товарів, НЕ повторюй моделі
- Кожен товар повинен бути унікальним за назвою, SKU та slug
- is_hit = true для 10-20% товарів (хіт продажів)
- is_new = true для 30-50% товарів (новинки)
- Поверни ТІЛЬКИ JSON масив, нічого більше
PROMPT;

        if ($instructions) {
            $prompt .= "\n\n# ОСОБЛИВІ ВИМОГИ КОРИСТУВАЧА\n{$instructions}";
        }

        return $prompt;
    }

    /**
     * Generate prompt for enriching existing product (better description, SEO, tags).
     */
    public function generateEnrichmentPrompt(Product $product): string
    {
        $titleUk = $product->getTranslation('title', 'uk', false) ?: '';
        $titleEn = $product->getTranslation('title', 'en', false) ?: '';
        $excerptUk = $product->getTranslation('excerpt', 'uk', false) ?: '';
        $contentUk = $product->getTranslation('content', 'uk', false) ?: '';
        $categoryName = $product->category?->getTranslation('title', 'uk', false) ?: '';
        $brandName = $product->brandModel?->name ?? '';
        $price = $product->price;

        return <<<PROMPT
# ЗАДАЧА
Збагати існуючий товар інтернет-магазину: покращи опис, додай SEO мета-дані та пошукові теги.

# ПОТОЧНІ ДАНІ ТОВАРУ
- Назва (UK): {$titleUk}
- Назва (EN): {$titleEn}
- Категорія: {$categoryName}
- Бренд: {$brandName}
- Ціна: {$price} грн
- Поточний короткий опис: {$excerptUk}
- Поточний контент: {$contentUk}

# ЩО ПОТРІБНО ЗГЕНЕРУВАТИ
Поверни JSON об'єкт з наступними полями (тільки ті, що потрібно оновити):

```json
{
  "excerpt_uk": "покращений короткий опис українською (plain text, до 200 символів)",
  "excerpt_en": "покращений короткий опис англійською (plain text, до 200 символів)",
  "content_uk": "покращений HTML опис українською (h2, p, ul/li, мінімум 3 секції)",
  "content_en": "покращений HTML опис англійською (h2, p, ul/li, мінімум 3 секції)",
  "meta_title_uk": "SEO заголовок українською (до 70 символів, з ключовим словом + купити)",
  "meta_title_en": "SEO заголовок англійською (до 70 символів)",
  "meta_description_uk": "SEO опис українською (до 160 символів)",
  "meta_description_en": "SEO опис англійською (до 160 символів)",
  "search_tags": "ключові слова через кому, українською, 5-10 штук"
}
```

# ВИМОГИ
- Контент повинен бути інформативним, з реальними характеристиками товару
- HTML контент: використовуй h2, p, ul/li (НЕ h1)
- SEO: meta_title до 70 символів, meta_description до 160 символів
- search_tags: 5-10 слів через кому, українською, в нижньому регістрі
- Стиль: професійний, орієнтований на продаж
- Поверни ТІЛЬКИ JSON об'єкт, нічого більше
PROMPT;
    }

    /**
     * Generate prompt for translating product to another language.
     */
    public function generateTranslationPrompt(Product $product, string $targetLocale): string
    {
        $sourceLocale = $targetLocale === 'en' ? 'uk' : 'en';
        $sourceLabel = $sourceLocale === 'uk' ? 'українська' : 'англійська';
        $targetLabel = $targetLocale === 'uk' ? 'українську' : 'англійську';

        $title = $product->getTranslation('title', $sourceLocale, false) ?: '';
        $excerpt = $product->getTranslation('excerpt', $sourceLocale, false) ?: '';
        $content = $product->getTranslation('content', $sourceLocale, false) ?: '';
        $metaTitle = $product->getTranslation('meta_title', $sourceLocale, false) ?: '';
        $metaDescription = $product->getTranslation('meta_description', $sourceLocale, false) ?: '';

        return <<<PROMPT
# ЗАДАЧА
Переклади текст товару з {$sourceLabel}ї мови на {$targetLabel} мову.
Переклад повинен бути природним, не дослівним. Адаптуй для цільової аудиторії.

# ВИХІДНИЙ ТЕКСТ
- title: {$title}
- excerpt: {$excerpt}
- content: {$content}
- meta_title: {$metaTitle}
- meta_description: {$metaDescription}

# ФОРМАТ ВІДПОВІДІ
Поверни ТІЛЬКИ JSON об'єкт:

```json
{
  "title_{$targetLocale}": "перекладена назва",
  "slug_{$targetLocale}": "транслітерований slug",
  "excerpt_{$targetLocale}": "перекладений короткий опис (до 200 символів)",
  "content_{$targetLocale}": "перекладений HTML контент (зберігай HTML теги)",
  "meta_title_{$targetLocale}": "перекладений SEO заголовок (до 70 символів)",
  "meta_description_{$targetLocale}": "перекладений SEO опис (до 160 символів)"
}
```

# ВИМОГИ
- Зберігай HTML розмітку у content (h2, p, ul, li)
- meta_title: до 70 символів
- meta_description: до 160 символів
- slug: тільки маленькі літери, цифри та дефіси
- Поверни ТІЛЬКИ JSON об'єкт, нічого більше
PROMPT;
    }

    /**
     * Generate prompt for creating SEO meta for product.
     */
    public function generateSeoPrompt(Product $product): string
    {
        $titleUk = $product->getTranslation('title', 'uk', false) ?: '';
        $titleEn = $product->getTranslation('title', 'en', false) ?: '';
        $categoryName = $product->category?->getTranslation('title', 'uk', false) ?: '';
        $brandName = $product->brandModel?->name ?? '';
        $price = $product->price;

        return <<<PROMPT
# ЗАДАЧА
Згенеруй SEO мета-дані для товару інтернет-магазину.

# ДАНІ ТОВАРУ
- Назва (UK): {$titleUk}
- Назва (EN): {$titleEn}
- Категорія: {$categoryName}
- Бренд: {$brandName}
- Ціна: {$price} грн

# ФОРМАТ ВІДПОВІДІ
Поверни ТІЛЬКИ JSON об'єкт:

```json
{
  "meta_title_uk": "SEO заголовок українською — до 70 символів, формат: Купити [Товар] [Бренд] — ціна в Україні",
  "meta_title_en": "SEO заголовок англійською — до 70 символів, формат: Buy [Product] [Brand] — Best Price",
  "meta_description_uk": "SEO опис українською — до 160 символів, з перевагами та закликом до дії",
  "meta_description_en": "SEO опис англійською — до 160 символів, with benefits and call-to-action"
}
```

# ВИМОГИ
- meta_title: максимум 70 символів, містить ключове слово + купити/buy + бренд
- meta_description: максимум 160 символів, містить переваги, ціну або знижку, заклик до дії
- Для українського ринку: "Доставка по Україні", "Гарантія", "Купити"
- Поверни ТІЛЬКИ JSON об'єкт, нічого більше
PROMPT;
    }

    /**
     * Generate prompt for search tags.
     */
    public function generateSearchTagsPrompt(Product $product): string
    {
        $titleUk = $product->getTranslation('title', 'uk', false) ?: '';
        $titleEn = $product->getTranslation('title', 'en', false) ?: '';
        $categoryName = $product->category?->getTranslation('title', 'uk', false) ?: '';
        $brandName = $product->brandModel?->name ?? '';
        $excerptUk = $product->getTranslation('excerpt', 'uk', false) ?: '';

        return <<<PROMPT
# ЗАДАЧА
Згенеруй пошукові теги для товару інтернет-магазину.

# ДАНІ ТОВАРУ
- Назва (UK): {$titleUk}
- Назва (EN): {$titleEn}
- Категорія: {$categoryName}
- Бренд: {$brandName}
- Опис: {$excerptUk}

# ФОРМАТ ВІДПОВІДІ
Поверни ТІЛЬКИ JSON об'єкт:

```json
{
  "search_tags": "тег1, тег2, тег3, тег4, тег5, тег6, тег7, тег8"
}
```

# ВИМОГИ
- 7-12 тегів через кому
- Українською мовою, в нижньому регістрі
- Включай: назву категорії, бренд (транслітерацію), синоніми, застосування
- Включай як українські, так і латинські варіанти назви бренду
- Включай розмовні та побутові назви (наприклад: "ноут" для ноутбука)
- Поверни ТІЛЬКИ JSON об'єкт, нічого більше
PROMPT;
    }

    /**
     * If API key is configured, send prompt to LLM and get response.
     */
    public function callLlm(string $prompt, string $provider = 'openai'): ?string
    {
        $apiKey = $this->getApiKey($provider);
        if (!$apiKey) {
            return null;
        }

        $model = $this->getModel($provider);
        $temperature = (float) $this->getSetting('ai_temperature', 0.7);
        $maxTokens = (int) $this->getSetting('ai_max_tokens', 4000);

        try {
            if ($provider === 'anthropic') {
                return $this->callAnthropic($apiKey, $model, $prompt, $temperature, $maxTokens);
            }

            return $this->callOpenAi($apiKey, $model, $prompt, $temperature, $maxTokens);
        } catch (\Exception $e) {
            Log::error("AI Content Generator LLM call failed", [
                'provider' => $provider,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function callOpenAi(string $apiKey, string $model, string $prompt, float $temperature, int $maxTokens): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a product content generator for a Ukrainian e-commerce store. Always respond with valid JSON only, no markdown, no explanations.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $errorMsg = $body['error']['message'] ?? $response->body();
            throw new \RuntimeException("OpenAI API error: {$errorMsg}");
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    protected function callAnthropic(string $apiKey, string $model, string $prompt, float $temperature, int $maxTokens): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(120)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'system' => 'You are a product content generator for a Ukrainian e-commerce store. Always respond with valid JSON only, no markdown, no explanations.',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if (!$response->successful()) {
            $body = $response->json();
            $errorMsg = $body['error']['message'] ?? $response->body();
            throw new \RuntimeException("Anthropic API error: {$errorMsg}");
        }

        $data = $response->json();

        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Parse LLM JSON response into product array.
     */
    public function parseProductsFromJson(string $json): array
    {
        // Strip markdown code block if present
        $json = trim($json);
        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```(?:json)?\s*/i', '', $json);
            $json = preg_replace('/\s*```\s*$/', '', $json);
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        // If response is wrapped in an object like {"products": [...]}
        if (is_array($decoded) && !array_is_list($decoded)) {
            foreach ($decoded as $value) {
                if (is_array($value) && array_is_list($value)) {
                    $decoded = $value;
                    break;
                }
            }
        }

        if (!is_array($decoded) || !array_is_list($decoded)) {
            // Might be a single product object
            if (is_array($decoded) && isset($decoded['title_uk'])) {
                $decoded = [$decoded];
            } else {
                throw new \RuntimeException('JSON response must be an array of products');
            }
        }

        return $decoded;
    }

    /**
     * Parse LLM JSON response into a single update object.
     */
    public function parseUpdateFromJson(string $json): array
    {
        $json = trim($json);
        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```(?:json)?\s*/i', '', $json);
            $json = preg_replace('/\s*```\s*$/', '', $json);
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('JSON response must be an object');
        }

        return $decoded;
    }

    /**
     * Import parsed products into database.
     */
    public function importProducts(array $products): array
    {
        $created = 0;
        $errors = [];

        foreach ($products as $index => $data) {
            try {
                $this->importSingleProduct($data);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Product #{$index}: {$e->getMessage()}";
                Log::warning("AI product import error", [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'data' => array_intersect_key($data, array_flip(['title_uk', 'sku'])),
                ]);
            }
        }

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }

    /**
     * Import a single product from parsed data.
     */
    protected function importSingleProduct(array $data): Product
    {
        // Resolve or create brand
        $brandId = null;
        if (!empty($data['brand'])) {
            $brand = Brand::where('name', $data['brand'])->first();
            if (!$brand) {
                $brand = Brand::create([
                    'name' => $data['brand'],
                    'is_active' => true,
                ]);
            }
            $brandId = $brand->id;
        }

        $product = new Product();

        // Set translatable fields
        if (!empty($data['title_uk'])) {
            $product->setTranslation('title', 'uk', $data['title_uk']);
        }
        if (!empty($data['title_en'])) {
            $product->setTranslation('title', 'en', $data['title_en']);
        }
        if (!empty($data['slug_uk'])) {
            $product->setTranslation('slug', 'uk', $data['slug_uk']);
        }
        if (!empty($data['slug_en'])) {
            $product->setTranslation('slug', 'en', $data['slug_en']);
        }
        if (!empty($data['excerpt_uk'])) {
            $product->setTranslation('excerpt', 'uk', $data['excerpt_uk']);
        }
        if (!empty($data['excerpt_en'])) {
            $product->setTranslation('excerpt', 'en', $data['excerpt_en']);
        }
        if (!empty($data['content_uk'])) {
            $product->setTranslation('content', 'uk', $data['content_uk']);
        }
        if (!empty($data['content_en'])) {
            $product->setTranslation('content', 'en', $data['content_en']);
        }
        if (!empty($data['meta_title_uk'])) {
            $product->setTranslation('meta_title', 'uk', $data['meta_title_uk']);
        }
        if (!empty($data['meta_title_en'])) {
            $product->setTranslation('meta_title', 'en', $data['meta_title_en']);
        }
        if (!empty($data['meta_description_uk'])) {
            $product->setTranslation('meta_description', 'uk', $data['meta_description_uk']);
        }
        if (!empty($data['meta_description_en'])) {
            $product->setTranslation('meta_description', 'en', $data['meta_description_en']);
        }

        // Set regular fields
        $product->sku = $data['sku'] ?? null;
        $product->price = $data['price'] ?? 0;
        $product->old_price = $data['old_price'] ?? null;
        $product->category_id = $data['category_id'] ?? null;
        $product->brand_id = $brandId;
        $product->is_active = $data['is_active'] ?? true;
        $product->is_hit = $data['is_hit'] ?? false;
        $product->is_new = $data['is_new'] ?? false;
        $product->search_tags = $data['search_tags'] ?? null;
        $product->quantity = $data['quantity'] ?? 100;
        $product->stock_status = 'in_stock';

        $product->save();

        return $product;
    }

    /**
     * Apply enrichment/update data to a product.
     */
    public function applyUpdateToProduct(Product $product, array $data): void
    {
        $translatableMap = [
            'title_uk' => ['title', 'uk'],
            'title_en' => ['title', 'en'],
            'slug_uk' => ['slug', 'uk'],
            'slug_en' => ['slug', 'en'],
            'excerpt_uk' => ['excerpt', 'uk'],
            'excerpt_en' => ['excerpt', 'en'],
            'content_uk' => ['content', 'uk'],
            'content_en' => ['content', 'en'],
            'meta_title_uk' => ['meta_title', 'uk'],
            'meta_title_en' => ['meta_title', 'en'],
            'meta_description_uk' => ['meta_description', 'uk'],
            'meta_description_en' => ['meta_description', 'en'],
        ];

        foreach ($translatableMap as $key => [$field, $locale]) {
            if (isset($data[$key]) && $data[$key] !== '') {
                $product->setTranslation($field, $locale, $data[$key]);
            }
        }

        if (isset($data['search_tags'])) {
            $product->search_tags = $data['search_tags'];
        }

        $product->save();
    }

    /**
     * Test API connection.
     */
    public function testConnection(string $provider): array
    {
        $apiKey = $this->getApiKey($provider);
        if (!$apiKey) {
            return ['success' => false, 'message' => 'API ключ не налаштовано'];
        }

        try {
            $response = $this->callLlm('Respond with: {"status":"ok"}', $provider);

            return [
                'success' => true,
                'message' => 'Підключення успішне',
                'response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get configured API key for provider.
     */
    public function getApiKey(string $provider): ?string
    {
        $key = DisplaySetting::get("ai_{$provider}_api_key");

        if (!$key || $key === '') {
            return null;
        }

        // Value might be stored as JSON-encoded string via DisplaySetting casts
        if (is_string($key)) {
            return $key;
        }

        return null;
    }

    /**
     * Check if any provider is configured.
     */
    public function isApiConfigured(): bool
    {
        $provider = $this->getProvider();

        return $provider !== 'none' && $this->getApiKey($provider) !== null;
    }

    /**
     * Get configured provider.
     */
    public function getProvider(): string
    {
        return DisplaySetting::get('ai_provider', 'none') ?: 'none';
    }

    /**
     * Get configured model for provider.
     */
    public function getModel(string $provider): string
    {
        $default = $provider === 'anthropic' ? 'claude-sonnet-4-20250514' : 'gpt-4o';

        return DisplaySetting::get("ai_{$provider}_model", $default) ?: $default;
    }

    /**
     * Get a setting value.
     */
    protected function getSetting(string $key, mixed $default = null): mixed
    {
        return DisplaySetting::get($key, $default);
    }

    /**
     * Log a generation action.
     */
    public function logGeneration(array $data): AiGenerationLog
    {
        return AiGenerationLog::create(array_merge($data, [
            'user_id' => Auth::id(),
        ]));
    }
}
