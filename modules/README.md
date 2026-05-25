# Modules — self-contained features

SimpleShop engine has a **plugin-style module system**: each feature lives
in its own folder under `modules/{name}/` and can be enabled/disabled
independently via UI, ENV, or DB toggle.

## Folder layout

```
modules/{name}/
├── module.json                 # manifest (required)
├── src/
│   ├── Providers/              # ServiceProvider(s) — loaded ONLY when enabled
│   ├── Models/                 # Eloquent models
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Filament/               # Filament resources / pages / widgets
│   ├── Services/
│   ├── Console/Commands/
│   └── Observers/
├── database/
│   ├── migrations/             # run when module is enabled
│   └── seeders/
├── routes/
│   └── web.php                 # auto-included if exists
├── resources/
│   ├── views/                  # accessible as view('{name}::path.to.view')
│   ├── css/                    # optional theme tokens / styles
│   └── lang/                   # i18n
└── tests/
    └── Feature/                # module-specific tests
```

## Namespace strategy

**For migrated existing modules (backwards-compat):**
Files keep their original `App\*` namespace. Composer `classmap`
auto-loads `modules/` so moving a file from `app/Models/UserCar.php` to
`modules/gazu_garage/src/Models/UserCar.php` requires **zero code
changes** in callers — references to `App\Models\UserCar` still work.

**For new modules (recommended going forward):**
Use `Modules\{ModuleName}\*` PSR-4 namespace. Cleaner separation, no
risk of name collisions.

## module.json schema

```json
{
  "name": "loyalty",
  "label": "Програма лояльності",
  "description": "Бонусні бали, рівні клієнтів, історія транзакцій",
  "version": "1.0.0",
  "author": "Lionex",
  "engine": ">=2.0",
  "requires_modules": [],
  "composer_packages": [],

  "providers": [
    "App\\Providers\\Modules\\LoyaltyServiceProvider"
  ],

  "filament_resources": [
    "App\\Filament\\Resources\\LoyaltyTierResource",
    "App\\Filament\\Resources\\LoyaltyTransactionResource"
  ],

  "migrations_path": "database/migrations",
  "views_path": "resources/views",
  "views_namespace": "loyalty",
  "routes": "routes/web.php",
  "translations_path": "resources/lang",

  "settings_schema": {
    "loyalty_default_rate": { "type": "int", "default": 1 },
    "loyalty_min_redemption": { "type": "int", "default": 50 }
  },

  "enabled_by_default": true
}
```

## Lifecycle

1. **Discovery** — `ModuleDiscovery` scans `modules/*/module.json` at boot.
2. **Toggle check** — `ModuleManager` resolves enabled state: DB → ENV → config fallback.
3. **Registration** — for each enabled module:
   - load `providers[]`
   - merge `routes/web.php` (if present, wrapped in `Route::middleware('web')`)
   - register views with namespace from manifest
   - register migrations path (so `php artisan migrate` picks them up)
4. **Disabled modules**:
   - Providers NOT loaded → no routes, no Filament resources, no observers
   - Migrations stay (DB state preserved, re-enable restores everything)
   - Module-specific DB rows preserved unless explicit `module:purge` artisan

## Common commands

```bash
php artisan make:module {name}        # scaffold new module
php artisan module:list               # show all modules + status
php artisan module:enable {name}      # turn on
php artisan module:disable {name}     # turn off (data preserved)
php artisan module:purge {name}       # drop tables + delete folder (DANGEROUS)
```

## Migration of existing 14 modules (in progress)

Status as of 2026-05-24:

| Module | In `modules/` | Has `module.json` | Self-contained |
|---|---|---|---|
| comparison        | ⏳ | ⏳ | — |
| coupons           | ⏳ | ⏳ | — |
| feed_export       | ⏳ | ⏳ | — |
| gazu_garage       | 🚧 (pilot) | 🚧 | — |
| loyalty           | ⏳ | ⏳ | — |
| meest_express     | ⏳ | ⏳ | — |
| multi_warehouse   | ⏳ | ⏳ | — |
| novaposhta        | ⏳ | ⏳ | — |
| quick_fill        | ⏳ | ⏳ | — |
| reviews           | ⏳ | ⏳ | — |
| rozetka_delivery  | ⏳ | ⏳ | — |
| ukrposhta         | ⏳ | ⏳ | — |
| wholesale         | ⏳ | ⏳ | — |
| auto_parts_seed   | ⏳ | ⏳ | — |

Pilot module: **gazu_garage** (1 model, 1 Filament resource, 1 controller, 1 migration — covers all layers, default off → safe).

After pilot validates structure, remaining 13 modules migrate by mechanical move.
