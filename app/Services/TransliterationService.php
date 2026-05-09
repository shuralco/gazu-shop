<?php

namespace App\Services;

use Illuminate\Support\Str;

class TransliterationService
{
    public function transliterate(string $text, string $fromLocale = 'uk'): string
    {
        $map = config("transliteration.{$fromLocale}", []);
        if (empty($map)) {
            return Str::slug($text);
        }

        $transliterated = strtr($text, $map);

        return Str::slug($transliterated);
    }

    public function generateSlug(string $title, string $locale = 'uk'): string
    {
        if ($locale === 'uk') {
            return $this->transliterate($title, 'uk');
        }

        return Str::slug($title);
    }

    /**
     * Generate locale-specific slugs from translated titles.
     *
     * @param array<string, string> $titles Keyed by locale, e.g. ['uk' => '...', 'en' => '...']
     * @param int|null $id Model ID to append for uniqueness
     * @return array<string, string> Keyed by locale
     */
    public function generateLocaleSlugs(array $titles, ?int $id = null): array
    {
        $slugs = [];
        $separator = config('slugs.separator', '-');
        $maxLen = config('slugs.max_length', 100);

        foreach ($titles as $locale => $title) {
            if (!$title) {
                continue;
            }

            $slugs[$locale] = $this->generateSlug($title, $locale);
        }

        if ($id && config('slugs.append_id', true)) {
            foreach ($slugs as $locale => &$slug) {
                $slug .= $separator . $id;
            }
            unset($slug);
        }

        foreach ($slugs as &$slug) {
            $slug = Str::limit($slug, $maxLen, '');
        }
        unset($slug);

        return $slugs;
    }
}
