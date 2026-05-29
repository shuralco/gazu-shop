<?php

namespace App\Support;

use App\Models\Product;
use App\Support\PartImage;
use Illuminate\Support\Str;

/**
 * Normalizes a Product model into the shape expected by
 * resources/views/components/gazu/product-card.blade.php.
 *
 * Used by StoreController, FilterLandingController та інші, що рендерять
 * картки. Підмінює сирі Eloquent relations (наприклад `$p->reviews`,
 * який повертає HasMany Collection) на скалярні поля.
 */
class ProductCardDecorator
{
    private const IMAGE_KINDS = ['filter', 'pad', 'shock', 'bulb', 'oil', 'spark', 'bearing', 'wiper'];

    /** Category-slug needle → image kind. */
    private const CATEGORY_IMAGE_KINDS = [
        'oil-filter' => 'filter', 'air-filter' => 'filter', 'fuel-filter' => 'filter', 'cabin-filter' => 'filter',
        'spark-plug' => 'spark', 'glow-plug' => 'spark', 'ignition-coil' => 'spark', 'high-voltage' => 'spark',
        'water-pump' => 'belt', 'thermostat' => 'sensor', 'radiator' => 'filter', 'cooling-fan' => 'alternator',
        'timing-belt' => 'belt', 'timing-kit' => 'belt', 'timing-chain' => 'belt',
        'oxygen-sensor' => 'sensor', 'maf-sensor' => 'sensor', 'knock-sensor' => 'sensor', 'crank-sensor' => 'sensor',
        'abs-sensor' => 'sensor', 'tpms' => 'sensor', 'rain-sensor' => 'sensor',
        'brake-pads-front' => 'pad', 'brake-pads-rear' => 'pad', 'brake-pad' => 'pad',
        'brake-discs' => 'brake-disc', 'brake-disc' => 'brake-disc',
        'brake-caliper' => 'cv-joint', 'brake-cylinder' => 'cv-joint', 'brake-hose' => 'wiper',
        'brake-fluid' => 'oil',
        'shocks' => 'shock', 'shock' => 'shock',
        'spring' => 'spring',
        'ball-joint' => 'cv-joint', 'tie-rod' => 'cv-joint', 'stabilizer' => 'cv-joint', 'silentblock' => 'bearing',
        'hub-bearing' => 'bearing',
        'batter' => 'battery', 'starter' => 'alternator', 'alternator' => 'alternator', 'voltage' => 'sensor',
        'bulbs-h4' => 'bulb', 'bulbs-h7' => 'bulb', 'bulbs-led' => 'bulb', 'bulbs-fog' => 'bulb', 'led-strip' => 'bulb', 'xenon' => 'bulb',
        'fuse' => 'sensor', 'relay' => 'sensor', 'wiring' => 'belt', 'connector' => 'sensor',
        'horn' => 'horn', 'speaker' => 'horn', 'alarm' => 'sensor', 'parking-sensor' => 'sensor',
        'ignition-switch' => 'sensor', 'window-switch' => 'sensor', 'wiper-switch' => 'sensor',
        'clutch' => 'clutch', 'release-bearing' => 'bearing', 'clutch-cable' => 'belt',
        'pressure-plate' => 'clutch',
        'cv-outer' => 'cv-joint', 'cv-inner' => 'cv-joint', 'cv-boot' => 'wiper', 'drive-shaft' => 'cv-joint',
        'transmission-mount' => 'bearing', 'gearbox' => 'cv-joint', 'shifter' => 'cv-joint',
        'cardan' => 'cv-joint', 'center-bearing' => 'bearing',
        'oils-' => 'oil', 'transmission-oil' => 'oil',
        'coolant' => 'coolant',
        'windshield-fluid' => 'oil',
        'headlight' => 'headlight', 'taillight' => 'taillight', 'fog-light' => 'headlight', 'side-mirror' => 'mirror', 'mirror-glass' => 'mirror',
        'fender' => 'taillight', 'bumper' => 'taillight', 'grille' => 'filter', 'hood' => 'taillight', 'door' => 'taillight',
        'windshield' => 'mirror', 'side-window' => 'mirror',
        'wiper' => 'wiper', 'wiper-motor' => 'wiper', 'washer-nozzle' => 'wiper',
        'molding' => 'belt', 'clip' => 'bearing', 'badge' => 'taillight',
        'mat' => 'mat', 'seat-cover' => 'mat', 'organizer' => 'mat', 'sun-shade' => 'mat', 'air-freshener' => 'oil',
        'dashcam' => 'sensor', 'phone-holder' => 'sensor', 'charger' => 'sensor', 'gps-tracker' => 'sensor', 'multimedia' => 'sensor',
        'tool' => 'tool', 'jack' => 'tool', 'compressor' => 'tool', 'jumper-cable' => 'belt',
        'fire-extinguisher' => 'tool', 'first-aid' => 'tool', 'warning-triangle' => 'taillight',
        'cleaner' => 'oil', 'polish' => 'oil', 'tire-care' => 'tire', 'tire' => 'tire',
    ];

    public static function decorate(Product $p): Product
    {
        $p->oem = $p->sku ?: ($p->barcode ?: '');

        // Translatable title (JSON column) → localized string.
        $rawTitle = $p->getRawOriginal('title');
        $localizedTitle = is_string($rawTitle) && str_starts_with($rawTitle, '{')
            ? (json_decode($rawTitle, true)['uk'] ?? null)
            : $rawTitle;
        $p->name = $localizedTitle ?: ($p->name ?? '');

        // Brand: prefer eager-loaded Brand model, fall back to legacy 'brand'/manufacturer.
        $brandName = null;
        if ($p->relationLoaded('brand') && ($brandModel = $p->getRelation('brand'))) {
            $brandName = $brandModel->name;
            if (! $brandName) {
                $raw = $brandModel->getRawOriginal('name');
                if (is_string($raw) && str_starts_with($raw, '{')) {
                    $decoded = json_decode($raw, true);
                    $brandName = $decoded['uk'] ?? $decoded['en'] ?? null;
                } else {
                    $brandName = $raw;
                }
            }
        }
        $brandName = $brandName ?: $p->getRawOriginal('brand');
        $p->brand = (string) ($brandName ?: $p->manufacturer ?: 'GAZU');

        $p->brand_slug = null;
        if ($p->relationLoaded('brand') && ($bm = $p->getRelation('brand')) && $bm->slug) {
            $p->brand_slug = (string) $bm->slug;
        } elseif (! empty($p->manufacturer)) {
            $p->brand_slug = (string) $p->manufacturer;
        } elseif ($brandName) {
            $p->brand_slug = Str::slug((string) $brandName);
        }

        $p->image_kind = self::imageKindFor($p);

        $p->qty = method_exists($p, 'totalAvailableQuantity')
            ? (int) $p->totalAvailableQuantity()
            : (int) ($p->quantity ?? 0);
        if (! $p->qty) {
            $p->qty = (int) ($p->quantity ?? 0);
        }

        // CRITICAL: $p->reviews is a HasMany relation → would return Collection.
        // Overwrite with scalar so blade `(int) $p->reviews` does not crash.
        $p->reviews = (int) ($p->reviews_count ?? 0);

        $excerpt = $p->excerpt ?? null;
        $isBoilerplate = is_string($excerpt) && str_contains($excerpt, 'Якісна автозапчастина від офіційного дилера');
        $p->fits = $isBoilerplate ? null : $excerpt;
        $p->condition = $p->is_new ? 'Новий' : 'Новий';
        $p->discount = ($p->old_price && $p->price && $p->old_price > $p->price)
            ? (int) round((($p->old_price - $p->price) / $p->old_price) * 100)
            : null;
        $p->url = route('gazu.product.show', ['slug' => $p->slug ?? $p->id]);

        return $p;
    }

    /**
     * Resolve the image-kind slug for a product (single source of truth,
     * also used by the products:assign-photos audit command).
     */
    public static function imageKindFor(Product $p): string
    {
        if ($p->relationLoaded('category') && ($cat = $p->getRelation('category'))) {
            // 1. Slug-needle map (en slugs) — primary, deterministic.
            $slug = (string) ($cat->slug ?? '');
            foreach (self::CATEGORY_IMAGE_KINDS as $needle => $kind) {
                if ($slug !== '' && str_contains($slug, $needle)) return $kind;
            }

            // 2. Ukrainian title heuristic — covers categories whose slug is not
            //    in the map above (canonical PartImage helper). Returns a kind
            //    that is guaranteed to have a photo pool.
            $rawTitle = $cat->getRawOriginal('title');
            $title = (is_string($rawTitle) && str_starts_with($rawTitle, '{'))
                ? (json_decode($rawTitle, true)['uk'] ?? $rawTitle)
                : ($cat->title ?? $rawTitle);
            if ($kind = PartImage::kindFromCategory(is_string($title) ? $title : null)) {
                return $kind;
            }
        }

        // 3. Last-resort: deterministic pick from kinds that all have pools.
        return self::IMAGE_KINDS[($p->id ?? 0) % count(self::IMAGE_KINDS)];
    }
}
