# Presets — Business-Profile Bundles

Presets are **declarative bundles** that turn a fresh engine clone into a
working shop with one command. A preset declares:

- Which theme to activate
- Which modules to turn ON
- Which to turn OFF
- Initial `display_settings` (homepage hero template, brand name, feature flags)

Apply a preset with:

```bash
php artisan preset:apply auto-parts
```

Idempotent — re-applying the same preset is a no-op. Add `--dry-run` to
preview without persisting.

## Anatomy of a preset

```php
// presets/auto-parts.php
<?php

return [
    'label' => 'Auto-Parts Shop',
    'description' => 'Магазин автозапчастин (як GAZU)',
    'theme' => 'gazu',
    'modules_on' => [
        'novaposhta',
        'ukrposhta',
        'multi_warehouse',
        'gazu_garage',
        'wholesale',
        'reviews',
        'comparison',
        'coupons',
        'loyalty',
        'feed_export',
        'quick_fill',
        'auto_parts_seed',
    ],
    'modules_off' => [
        'rozetka_delivery',
        'meest_express',
    ],
    'display_settings' => [
        'shop_brand_name' => 'GAZU',
        'show_car_selector' => true,
        'show_oem_search' => true,
        'hero_template' => 'car-selector',
    ],
];
```

That's the whole file. Just a PHP array — no class, no schema.

## Field reference

| Key | Type | What it does |
|---|---|---|
| `label` | string | Human label shown in admin / command output |
| `description` | string | One-line summary |
| `theme` | string | Theme name to activate (must exist under `themes/`) |
| `modules_on` | string[] | Modules to turn ON (idempotent — sets `enabled=true` in DB) |
| `modules_off` | string[] | Modules to turn OFF (sets `enabled=false`) |
| `display_settings` | array | Key/value pairs written via `DisplaySetting::set()` |

Any key not in `modules_on` or `modules_off` is **left untouched** — so a
preset doesn't have to enumerate all 14 modules.

## The 3 shipped presets

### `auto-parts`

The original GAZU profile. Heavy auto-specific configuration.

| | |
|---|---|
| Theme | `gazu` |
| ON | novaposhta, ukrposhta, multi_warehouse, gazu_garage, wholesale, reviews, comparison, coupons, loyalty, feed_export, quick_fill, auto_parts_seed |
| OFF | rozetka_delivery, meest_express |
| Hero | `car-selector` (с make→model→engine drop-downs) |

### `cosmetics`

Beauty / cosmetics shop. Marketing-heavy, no auto.

| | |
|---|---|
| Theme | `gazu` (until `cosmetics` theme exists) |
| ON | novaposhta, ukrposhta, loyalty, reviews, comparison, coupons, feed_export |
| OFF | gazu_garage, auto_parts_seed, wholesale, multi_warehouse, rozetka_delivery, meest_express, quick_fill |
| Hero | `product-grid` |

### `general-shop`

Minimal e-commerce. Start small, add features later.

| | |
|---|---|
| Theme | `gazu` |
| ON | novaposhta, ukrposhta, reviews, comparison, coupons |
| OFF | gazu_garage, auto_parts_seed, wholesale, multi_warehouse, rozetka_delivery, meest_express, quick_fill, loyalty, feed_export |
| Hero | `product-grid` |

## What `preset:apply` actually does

```
php artisan preset:apply auto-parts
       │
       ▼
1. Validates: presets/auto-parts.php exists, returns array
2. Validates: theme listed in themes/* directory
3. For theme:                 ThemeManager::setActive('gazu')
4. For each modules_on key:   Module::updateOrCreate([...], ['enabled'=>true])
5. For each modules_off key:  Module::updateOrCreate([...], ['enabled'=>false])
6. For each display_settings: DisplaySetting::set(key, value)
7. Clears caches:             ModuleManager + ThemeManager
       │
       ▼
✓ Preset 'auto-parts' applied.
```

After applying:

- Restart Octane workers (if running) so module providers reload:
  `php artisan octane:reload`
- Rebuild Vite if theme changed: `npm run build`
- Run migrations to create any newly-enabled module tables:
  `php artisan migrate`
- Seed demo data if relevant: `php artisan db:seed --class=AutoPartsSeeder`

## Dry-run

Always preview before persisting on a real environment:

```bash
php artisan preset:apply cosmetics --dry-run
```

Output shows every change that WOULD happen. No DB writes.

## Creating your own preset

Step 1: copy an existing one.

```bash
cp presets/general-shop.php presets/coffee-shop.php
```

Step 2: edit the file. Set theme, modules, display settings to match
your business model.

Step 3: apply.

```bash
php artisan preset:apply coffee-shop
```

That's it. No registration step — presets are auto-discovered from `presets/*.php`.

## Tips for authoring presets

- **Don't try to be exhaustive.** Leave modules you don't care about
  unmentioned — keep their default state. Smaller presets are easier
  to reason about.
- **Pair with a theme.** A preset that activates `gazu_garage` but
  uses a `cosmetics` theme is jarring. Match them.
- **Document the rationale** in the file header. Future-you will thank
  you when you're tweaking 9 months later.
- **Use ENV overrides for secrets.** A preset shouldn't write API keys
  to `display_settings`. Those go in `.env`.

## Display settings examples

Common settings you might want a preset to set:

```php
'display_settings' => [
    // Branding
    'shop_brand_name'  => 'My Shop',
    'shop_logo'        => null,           // FileUpload — leave for admin
    'shop_phone'       => '+380...',

    // Storefront features
    'show_car_selector' => false,
    'show_oem_search'   => false,
    'show_brand_filter' => true,

    // Hero/homepage
    'hero_template'    => 'product-grid',  // or 'car-selector', 'big-banner'
    'hero_subtitle'    => 'Усе для краси',

    // Trust strip on storefront
    'trust_warranty'    => '14 днів на повернення',
    'trust_delivery'    => 'Доставка по Україні',

    // Module-specific toggles
    'loyalty_default_rate'   => 5,
    'gazu_payment_enabled'   => false,
],
```

Settings are stored in the `display_settings` table (`{key, value}`).
Read in code via `DisplaySetting::get('hero_template', 'default-value')`.

## Switching presets later

Re-running `preset:apply` is safe — it overwrites prior state. So you can
start with `general-shop`, run for a month, then switch:

```bash
php artisan preset:apply auto-parts
```

This will:
- Switch theme `general-shop`'s theme → `gazu`
- Enable everything in auto-parts' `modules_on`
- Disable everything in auto-parts' `modules_off`
- Overwrite `display_settings` with auto-parts' values

Modules NOT mentioned by the new preset stay in their current state. So
if you'd enabled `gazu_garage` manually earlier, switching to `general-shop`
(which doesn't mention it) leaves `gazu_garage` ON.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| "Preset 'X' not found" | Filename typo | `ls presets/` |
| "Theme 'Y' not installed" | Theme dir missing | Check `themes/Y/theme.json` exists |
| Module toggle has no effect | Cache not cleared | Manager::clearCache() runs at end — verify Octane restarted |
| Display settings show old values | `DisplaySetting` model has cache | `Cache::tags('display_settings')->flush()` |

## Reference

- `app/Console/Commands/PresetApplyCommand.php` — the implementation
- `app/Support/ThemeManager.php` — theme activation
- `app/Support/ModuleManager.php` — module toggle resolution
- `app/Models/DisplaySetting.php` — settings storage
- `presets/auto-parts.php`, `cosmetics.php`, `general-shop.php` — reference presets
