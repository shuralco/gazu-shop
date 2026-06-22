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

    private static ?array $pooledKindsCache = null;

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
     * Генеративне демо-фото товару. Детерміноване за seed (id/код/назва) —
     * один товар = завжди ОДНА й та сама картинка, різні товари = різні
     * (колір + візерунок + код/ініціали виводяться з хешу). Без зовнішніх
     * файлів і без «купи однакових дублів».
     *
     * @param  string            $title Назва товару (для ініціалів / 2-го рядка)
     * @param  int|string|null   $seed  Стабільний ключ (зазвичай product_id)
     * @param  string|null       $code  Артикул/OEM — якщо є, стає головним написом
     */
    public static function monogram(string $title, int|string|null $seed = null, ?string $code = null): string
    {
        $seed ??= ($code ?: $title);
        $hash = md5((string) $seed);

        // Палітра з хешу (детермінована), приглушена й «на-бренд».
        $hue = hexdec(substr($hash, 0, 6)) % 360;
        $hue2 = ($hue + 28) % 360;

        // Головний напис: артикул (скорочений) або ініціали назви.
        $codeStr = trim((string) $code);
        $words = preg_split('/\s+/u', trim($title)) ?: [];
        $initials = mb_strtoupper(
            mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1),
            'UTF-8'
        );
        $focus = $codeStr !== '' ? mb_strtoupper($codeStr) : ($initials ?: 'GAZU');
        if (mb_strlen($focus) > 14) {
            $focus = mb_substr($focus, 0, 14);
        }
        $len = mb_strlen($focus);
        $focusSize = $len <= 3 ? 116 : ($len <= 6 ? 64 : ($len <= 10 ? 42 : 30));

        // 2-й рядок — короткий шматок назви (необов'язково).
        $sub = trim(preg_replace('/\s+/u', ' ', $title));
        if (mb_strlen($sub) > 26) {
            $sub = mb_substr($sub, 0, 25).'…';
        }

        // Генеративний візерунок: позиції/поворот із хешу → унікальний кадр.
        $cx = 60 + (hexdec(substr($hash, 6, 2)) % 200);
        $cy = 60 + (hexdec(substr($hash, 8, 2)) % 200);
        $rot = hexdec(substr($hash, 10, 2)) % 360;
        $r = 70 + (hexdec(substr($hash, 12, 2)) % 60);

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320">'
            .'<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            .'<stop offset="0" stop-color="hsl(%1$d,40%%,93%%)"/>'
            .'<stop offset="1" stop-color="hsl(%2$d,34%%,82%%)"/>'
            .'</linearGradient></defs>'
            .'<rect width="320" height="320" fill="url(#g)"/>'
            .'<g fill="none" stroke="hsl(%1$d,60%%,99%%)" stroke-opacity="0.55">'
            .'<circle cx="%3$d" cy="%4$d" r="%5$d" stroke-width="14"/>'
            .'<circle cx="%3$d" cy="%4$d" r="%6$d" stroke-width="6"/>'
            .'<rect x="%7$d" y="%8$d" width="120" height="120" rx="14" transform="rotate(%9$d %3$d %4$d)" stroke-width="5"/>'
            .'</g>'
            .'<text x="160" y="156" font-family="ui-monospace,Menlo,Consolas,monospace" font-size="%10$d" font-weight="700" letter-spacing="1" text-anchor="middle" dominant-baseline="central" fill="hsl(%1$d,48%%,26%%)">%11$s</text>'
            .'<text x="160" y="206" font-family="Inter,system-ui,sans-serif" font-size="17" font-weight="500" text-anchor="middle" fill="hsl(%1$d,30%%,38%%)" fill-opacity="0.85">%12$s</text>'
            .'<text x="160" y="298" font-family="Inter,system-ui,sans-serif" font-size="13" font-weight="700" letter-spacing="3" text-anchor="middle" fill="hsl(%1$d,35%%,42%%)" fill-opacity="0.6">GAZU</text>'
            .'</svg>',
            $hue, $hue2,
            $cx, $cy, $r, max(28, $r - 42),
            $cx - 60, $cy - 60, $rot,
            $focusSize,
            htmlspecialchars($focus, ENT_QUOTES | ENT_XML1),
            htmlspecialchars($sub, ENT_QUOTES | ENT_XML1)
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
     * Гарантовано повертає kind, що МАЄ фото-пул — щоб resolve() ніколи не падав
     * у монограму (порожній SVG) там, де очікується реальне фото (напр. колонка
     * товарів у списку замовлень). Спершу пробуємо мапу за назвою категорії;
     * якщо вона нічого не дала або pool порожній — детермінований вибір за seed
     * із наявних пулів (той самий товар → завжди те саме фото).
     */
    public static function guaranteedKind(?string $categoryTitle, int|string|null $seed): string
    {
        $kind = self::kindFromCategory($categoryTitle);
        if ($kind && ! empty(self::pool($kind))) {
            return $kind;
        }

        $kinds = self::pooledKinds();
        if (empty($kinds)) {
            return $kind ?? 'filter';
        }
        $idx = $seed !== null ? abs(crc32((string) $seed)) % count($kinds) : 0;

        return $kinds[$idx];
    }

    /** Список kind'ів, що мають непорожній фото-пул (public/img/parts/<kind>/*.webp). */
    public static function pooledKinds(): array
    {
        if (self::$pooledKindsCache !== null) {
            return self::$pooledKindsCache;
        }
        $base = public_path('img/parts');
        $dirs = is_dir($base) ? (glob($base.'/*', GLOB_ONLYDIR) ?: []) : [];
        $kinds = [];
        foreach ($dirs as $dir) {
            if (glob($dir.'/*.webp')) {
                $kinds[] = basename($dir);
            }
        }
        sort($kinds);

        return self::$pooledKindsCache = $kinds;
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
