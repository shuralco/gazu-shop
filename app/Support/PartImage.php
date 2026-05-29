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

        // ORDER MATTERS: more-specific needles MUST come before generic ones
        // (e.g. "колодк"/"диск" before "гальм"; "свіч розжар" handled by "свіч").
        // Every kind below is guaranteed to have a photo pool in
        // public/img/parts/<kind>/ — so any category title that contains one of
        // these Ukrainian stems gets a real photo instead of a monogram.
        $map = [
            // --- Brakes ---
            'колодк' => 'pad',              // гальмівні колодки
            'гальмівн диск' => 'brake-disc',
            'гальмівн рідин' => 'oil',      // brake fluid → bottle/oil pool
            'супорт' => 'cv-joint',         // brake calipers
            'циліндр' => 'cv-joint',
            'шланг' => 'wiper',
            'гальм' => 'brake-disc',        // generic brakes → disc

            // --- Filters ---
            'філь' => 'filter',
            'фільтр' => 'filter',

            // --- Engine oils / fluids ---
            'оливн' => 'oil',               // oil filters land on filter via "філь" first; standalone "оливн" → oil
            'мотор масл' => 'oil',
            'трансмісійн масл' => 'oil',
            'трансмісійн' => 'oil',
            'масл' => 'oil',                // масла
            'олив' => 'oil',
            'омивач' => 'oil',              // washer fluid bottle
            'антифр' => 'coolant',
            'охолод' => 'coolant',
            'антифриз' => 'coolant',

            // --- Ignition ---
            'свіч' => 'spark',              // свічки запалювання / розжарювання
            'котушк' => 'spark',            // ignition coils
            'високовольтн' => 'spark',      // HV wires
            'замк запалюв' => 'sensor',     // ignition switch
            'запалю' => 'spark',

            // --- Cooling system ---
            'помп' => 'belt',
            'термостат' => 'sensor',
            'радіатор' => 'filter',
            'вентилятор' => 'alternator',

            // --- Timing (ГРМ) ---
            'ремен грм' => 'belt',
            'ланцюг грм' => 'belt',
            'комплект грм' => 'belt',
            'грм' => 'belt',
            'ремен' => 'belt',

            // --- Sensors ---
            'лямбда' => 'sensor',
            'витратомір' => 'sensor',
            'датчик' => 'sensor',
            'парктронік' => 'sensor',
            'сенсор' => 'sensor',

            // --- Suspension ---
            'амортиз' => 'shock',
            'пружин' => 'spring',
            'шаров опор' => 'cv-joint',
            'рульов тяг' => 'cv-joint',
            'стійк стабіл' => 'cv-joint',
            'стабіліз' => 'cv-joint',
            'сайлентблок' => 'bearing',
            'тяг' => 'cv-joint',
            'опор' => 'bearing',
            'підвіск' => 'spring',

            // --- Bearings ---
            'маточин' => 'bearing',
            'підшипник' => 'bearing',

            // --- Electrics / power ---
            'акумул' => 'battery',
            'стартер' => 'alternator',
            'генерат' => 'alternator',
            'реле-регулятор' => 'sensor',
            'реле' => 'sensor',
            'запобіжник' => 'sensor',
            'жгут' => 'belt',
            'провод' => 'belt',
            'дроти' => 'belt',
            'розʼєм' => 'sensor',
            'розʼєми' => 'sensor',
            "роз'єм" => 'sensor',

            // --- Lighting ---
            'led' => 'bulb',
            'ксенон' => 'bulb',
            'лампа' => 'bulb',
            'лампи' => 'bulb',
            'стрічк' => 'bulb',             // LED-стрічки / DRL
            'протитуман' => 'headlight',
            'фара' => 'headlight',
            'фари' => 'headlight',
            'задн ліхтар' => 'taillight',
            'ліхтар' => 'taillight',
            'освітл' => 'bulb',

            // --- Audio / alarm ---
            'сигнал' => 'horn',             // car horns / сигналізації
            'динамік' => 'horn',
            'клаксон' => 'horn',

            // --- Switches ---
            'перемикач' => 'sensor',
            'кнопк' => 'sensor',
            'замк' => 'sensor',

            // --- Clutch / transmission ---
            'комплект зчепл' => 'clutch',
            'диск зчепл' => 'clutch',
            'кошик зчепл' => 'clutch',
            'вижимн' => 'bearing',
            'тросик' => 'belt',
            'зчепленн' => 'clutch',
            'клатч' => 'clutch',

            // --- CV joints / driveline ---
            'шрус' => 'cv-joint',
            'піввіс' => 'cv-joint',
            'пильовик' => 'wiper',
            'кардан' => 'cv-joint',
            'хрестовин' => 'cv-joint',
            'підвісн підшип' => 'bearing',
            'опор кпп' => 'bearing',
            'сальник' => 'wiper',
            'ручк кпп' => 'cv-joint',
            'коробк передач' => 'cv-joint',
            'кпп' => 'cv-joint',
            'трансм' => 'cv-joint',

            // --- Body / optics ---
            'дзеркал' => 'mirror',
            'скло дзеркал' => 'mirror',
            'лобов скло' => 'mirror',
            'бок скло' => 'mirror',
            'задн скло' => 'mirror',
            'скло' => 'mirror',
            'крил' => 'taillight',
            'бампер' => 'taillight',
            'решітк' => 'filter',
            'капот' => 'taillight',
            'двер' => 'taillight',
            'молдинг' => 'belt',
            'кліпс' => 'bearing',
            'емблем' => 'taillight',
            'кузов' => 'mirror',

            // --- Wipers / washers ---
            'двірник' => 'wiper',
            'склоочисн' => 'wiper',
            'моторчик двірник' => 'wiper',
            'форсунк омивач' => 'wiper',
            'форсунк' => 'wiper',
            'шкло' => 'wiper',

            // --- Interior / accessories ---
            'килим' => 'mat',
            'чохл' => 'mat',
            'органайзер' => 'mat',
            'шторк' => 'mat',
            'ароматизатор' => 'oil',

            // --- Electronics ---
            'відеореєстратор' => 'sensor',
            'тримач' => 'sensor',
            'зарядк' => 'sensor',
            'gps' => 'sensor',
            'трекер' => 'sensor',
            'мультимед' => 'sensor',

            // --- Tools / safety ---
            'набор інструмент' => 'tool',
            'інструм' => 'tool',
            'домкрат' => 'tool',
            'компресор' => 'tool',
            'пуск дрот' => 'belt',
            'вогнегасник' => 'tool',
            'аптечк' => 'tool',
            'знак авар' => 'taillight',
            'знак' => 'taillight',

            // --- Car care ---
            'очисник' => 'oil',
            'полірол' => 'oil',
            'засоб для шин' => 'tire',
            'догляд' => 'oil',

            // --- Tires / wheels ---
            'шин' => 'tire',
            'диск' => 'tire',
            'колес' => 'tire',

            // --- Misc engine ---
            'двигун' => 'oil',
            'двз' => 'oil',
            'електр' => 'sensor',
            'аксесуар' => 'mat',
        ];

        foreach ($map as $needle => $kind) {
            if (mb_strpos($t, $needle) !== false) {
                return $kind;
            }
        }

        return null;
    }
}
