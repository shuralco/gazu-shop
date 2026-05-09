<?php

namespace App\Services;

use Illuminate\Support\Str;

class UrlRouterService
{
    private array $ukrainianToEnglish = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g',
        'д' => 'd', 'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z',
        'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'i', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
        'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '', 'ю' => 'iu', 'я' => 'ia',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G',
        'Д' => 'D', 'Е' => 'E', 'Є' => 'Ie', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'Y', 'І' => 'I', 'Ї' => 'I', 'Й' => 'I', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
        'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
        'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
        'Ь' => '', 'Ю' => 'Iu', 'Я' => 'Ia',
    ];

    private array $specialCases = [
        'зг' => 'zgh', 'Зг' => 'Zgh', 'ЗГ' => 'ZGH',
        'кг' => 'kgh', 'Кг' => 'Kgh', 'КГ' => 'KGH',
    ];

    public function transliterateToEnglish(string $text): string
    {
        // Спочатку обробляємо спеціальні випадки
        foreach ($this->specialCases as $uk => $en) {
            $text = str_replace($uk, $en, $text);
        }

        // Потім стандартна транслітерація
        foreach ($this->ukrainianToEnglish as $uk => $en) {
            $text = str_replace($uk, $en, $text);
        }

        return $text;
    }

    public function generateSlug(string $title): string
    {
        // Транслітерація українського тексту
        $transliterated = $this->transliterateToEnglish($title);

        // Створення slug
        $slug = Str::slug($transliterated);

        // Додаткове очищення для SEO
        $slug = $this->cleanSlugForSeo($slug);

        return $slug;
    }

    public function generateUniqueSlug(string $title, string $modelClass, ?int $excludeId = null): string
    {
        $baseSlug = $this->generateSlug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $modelClass, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function generateCategoryUrl(string $slug, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return "/{$locale}/{$slug}";
    }

    public function generateProductUrl(string $slug, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return "/{$locale}/{$slug}";
    }

    public function generateCanonicalUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $path = ltrim($path, '/');

        return "{$baseUrl}/{$path}";
    }

    public function generateBreadcrumbStructuredData(array $breadcrumbs): array
    {
        $items = [];

        foreach ($breadcrumbs as $index => $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $this->generateCanonicalUrl($breadcrumb['url']),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private function cleanSlugForSeo(string $slug): string
    {
        // Видаляємо зайві символи
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        // Видаляємо повторювані дефіси
        $slug = preg_replace('/-+/', '-', $slug);

        // Видаляємо дефіси на початку та в кінці
        $slug = trim($slug, '-');

        return $slug;
    }

    private function slugExists(string $slug, string $modelClass, ?int $excludeId = null): bool
    {
        $locale = app()->getLocale();
        $query = $modelClass::where("slug->{$locale}", $slug)
            ->orWhere('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function validateSlug(string $slug): array
    {
        $errors = [];

        if (empty($slug)) {
            $errors[] = 'Slug не може бути порожнім';
        }

        if (strlen($slug) > 255) {
            $errors[] = 'Slug не може бути довше 255 символів';
        }

        if (! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $errors[] = 'Slug може містити тільки малі латинські літери, цифри та дефіси';
        }

        if (str_starts_with($slug, '-') || str_ends_with($slug, '-')) {
            $errors[] = 'Slug не може починатися або закінчуватися дефісом';
        }

        if (strpos($slug, '--') !== false) {
            $errors[] = 'Slug не може містити повторювані дефіси';
        }

        return $errors;
    }

    public function generateRobotsTxt(array $sitemapUrls = []): string
    {
        $content = "User-agent: *\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /login/\n";
        $content .= "Disallow: /register/\n";
        $content .= "Disallow: /cart/\n";
        $content .= "Disallow: /checkout/\n";
        $content .= "Disallow: /account/\n";
        $content .= "Disallow: /orders/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /*?*\n";
        $content .= "\n";

        foreach ($sitemapUrls as $sitemapUrl) {
            $content .= "Sitemap: {$sitemapUrl}\n";
        }

        return $content;
    }

    public function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public function normalizeUrl(string $url): string
    {
        // Видаляємо зайві слеші
        $url = preg_replace('#/+#', '/', $url);

        // Забезпечуємо слеш на початку
        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return $url;
    }
}
