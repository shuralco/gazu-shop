<?php

namespace App\Support;

/**
 * Resolves a "part image" for a product.
 *
 * Priority chain:
 *   1. Real product photo (if uploaded) — $record->image / explicit URL
 *   2. Per-kind pool from public/img/parts/{kind}/*.webp — picked by seed
 *   3. Single per-kind file public/img/parts/{kind}.webp
 *   4. Monogram SVG data: URI (initials + deterministic HSL color)
 *
 * Used by both:
 *   - Admin Filament ProductResource ImageColumn::getStateUsing
 *   - Storefront <x-gazu.part-image> component (storefront keeps its
 *     own kind-detection logic; this helper is the canonical fallback)
 */
class PartImage
{
    /** @var array<string,list<string>> Cached pool listing per kind. */
    private static array $poolCache = [];

    /**
     * @param  string|null  $explicit Direct image URL if uploaded (takes priority)
     * @param  string|null  $kind     Slug like "filter", "battery", "brake-disc"
     * @param  int|string|null  $seed Stable id for picking from the pool (product_id usually)
     * @param  string       $title    Used for the monogram fallback
     */
    public static function resolve(?string $explicit, ?string $kind, int|string|null $seed, string $title): string
    {
        if (! empty($explicit)) {
            return $explicit;
        }

        if ($kind) {
            $pool = self::pool($kind);
            if (! empty($pool)) {
                $idx = $seed !== null ? abs(crc32((string) $seed)) % count($pool) : 0;
                return asset("img/parts/{$kind}/".$pool[$idx]);
            }

            $single = public_path("img/parts/{$kind}.webp");
            if (is_file($single)) {
                return asset("img/parts/{$kind}.webp");
            }
        }

        return self::monogram($title, $seed);
    }

    /**
     * Deterministic monogram placeholder. Always lookups the SAME color
     * for the same seed, so a re-rendered product keeps its visual identity.
     */
    public static function monogram(string $title, int|string|null $seed = null): string
    {
        $seed ??= $title;
        $words = preg_split('/\s+/u', trim($title)) ?: ['?'];
        $initials = mb_strtoupper(
            mb_substr($words[0] ?? '?', 0, 1).
            mb_substr($words[1] ?? '', 0, 1),
            'UTF-8'
        ) ?: '·';

        $hue = hexdec(substr(md5((string) $seed), 0, 6)) % 360;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
            .'<rect width="100" height="100" fill="hsl(%d,42%%,93%%)"/>'
            .'<text x="50%%" y="50%%" font-family="Inter,system-ui,sans-serif" font-size="36" font-weight="600" text-anchor="middle" dominant-baseline="central" fill="hsl(%d,38%%,36%%)">%s</text>'
            .'</svg>',
            $hue, $hue, htmlspecialchars($initials, ENT_QUOTES | ENT_XML1)
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * @return list<string> filenames (just basename — full URL via asset()).
     */
    private static function pool(string $kind): array
    {
        if (array_key_exists($kind, self::$poolCache)) {
            return self::$poolCache[$kind];
        }
        $dir = public_path("img/parts/{$kind}");
        $files = is_dir($dir) ? (glob($dir.'/*.webp') ?: []) : [];
        sort($files);
        return self::$poolCache[$kind] = array_map('basename', $files);
    }

    /**
     * Derive kind-slug from a category title — heuristic.
     * Returns null if nothing maps.
     */
    public static function kindFromCategory(?string $categoryTitle): ?string
    {
        if (! $categoryTitle) {
            return null;
        }
        $t = mb_strtolower($categoryTitle);

        $map = [
            'філь' => 'filter',
            'оливн' => 'oil',
            'олив' => 'oil',
            'свіч' => 'spark',
            'запалю' => 'spark',
            'гальм' => 'brake-disc',
            'колодк' => 'pad',
            'амортиз' => 'shock',
            'підвіск' => 'spring',
            'пружин' => 'spring',
            'лампа' => 'bulb',
            'акумул' => 'battery',
            'генерат' => 'alternator',
            'ремен' => 'belt',
            'двигун' => 'oil',
            'електр' => 'sensor',
            'дзеркал' => 'mirror',
            'фара' => 'headlight',
            'кузов' => 'mirror',
            'диск' => 'tire',
            'шин' => 'tire',
            'клатч' => 'clutch',
            'зчепленн' => 'clutch',
            'трансм' => 'cv-joint',
            'двз' => 'oil',
            'охолод' => 'coolant',
            'антифр' => 'coolant',
            'шкло' => 'wiper',
            'двірник' => 'wiper',
            'інструм' => 'tool',
            'аксесуар' => 'mat',
            'килим' => 'mat',
        ];

        foreach ($map as $needle => $kind) {
            if (mb_strpos($t, $needle) !== false) {
                return $kind;
            }
        }

        return null;
    }
}
