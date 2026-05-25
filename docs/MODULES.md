# Modules — Developer Guide

Modules are **plugin-style features** under `modules/{name}/`. Any module can be
turned ON or OFF independently without touching code or removing files.

This doc covers everything you need to author, debug, and ship modules. For the
30-second overview, see [ARCHITECTURE.md](ARCHITECTURE.md).

## Anatomy of a module

```
modules/loyalty/
├── module.json                 # ① manifest (required)
├── README.md                   # human description (optional)
├── src/
│   ├── Models/                 # ② Eloquent models
│   ├── Services/               # business logic
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Filament/
│   │   ├── Resources/          # CRUD admin
│   │   ├── Pages/              # custom admin pages
│   │   └── Widgets/            # dashboard widgets
│   ├── Console/Commands/       # artisan commands
│   ├── Events/
│   ├── Mail/
│   └── Observers/
├── database/
│   ├── migrations/             # ③ run when module is enabled
│   └── seeders/
├── routes/
│   └── web.php                 # ④ auto-included (web middleware group)
├── resources/
│   ├── views/                  # accessible as view('loyalty::tier.show')
│   ├── css/
│   └── lang/                   # i18n
└── tests/Feature/
```

## ① The manifest (`module.json`)

The single source of truth that tells the engine what this module provides.
Every module **must** have one. Full schema:

```json
{
  "name": "loyalty",
  "label": "Програма лояльності",
  "description": "Бонусні бали, рівні клієнтів, історія транзакцій.",
  "version": "1.0.0",
  "author": "Lionex",
  "engine": ">=2.0",

  "requires_modules": [],
  "composer_packages": [],

  "providers": [],

  "filament_resources": [
    "App\\Filament\\Resources\\LoyaltyTierResource",
    "App\\Filament\\Resources\\LoyaltyTransactionResource"
  ],
  "filament_pages": [],
  "filament_widgets": [],

  "migrations_path": "database/migrations",
  "seeders_path": "database/seeders",
  "views_path": "resources/views",
  "views_namespace": "loyalty",
  "routes": "routes/web.php",
  "translations_path": "resources/lang",

  "settings_schema": {
    "default_rate": { "type": "int", "default": 1 },
    "min_redemption": { "type": "int", "default": 50 }
  },

  "enabled_by_default": true
}
```

### Field reference

| Field | Required | What it does |
|---|---|---|
| `name` | yes | Unique identifier. snake_case. Must match folder name. |
| `label` | yes | Human-readable name shown in admin UI |
| `description` | yes | One-line summary |
| `version` | yes | Semver — bumped when migrations or behavior change |
| `engine` | recommended | Required engine version (`">=2.0"`) |
| `requires_modules` | optional | Names of modules this one depends on |
| `providers` | optional | Service providers to register (FQCN array) |
| `filament_resources` | optional | Filament resource classes to add to admin |
| `filament_pages` | optional | Custom Filament Page classes |
| `filament_widgets` | optional | Dashboard widget classes |
| `migrations_path` | optional | Relative path scanned by `migrate` (default: `database/migrations`) |
| `views_namespace` | optional | Namespace for `view('namespace::view')` lookup |
| `routes` | optional | Relative path to routes file (loaded with `web` middleware) |
| `settings_schema` | optional | Per-module config; persisted in `modules.settings` JSON column |
| `enabled_by_default` | optional | Boolean fallback if no DB row or ENV present |

## ② Models

Two namespace strategies — pick one **per module**:

### Strategy A: keep `App\*` (recommended for migrated modules)

Composer `classmap` directive (`"classmap": ["modules/"]`) auto-loads files
regardless of their PSR-4 namespace. So `modules/loyalty/src/Models/LoyaltyTier.php`
can declare `namespace App\Models;` and existing callers still work unchanged.

```php
// modules/loyalty/src/Models/LoyaltyTier.php
namespace App\Models;

class LoyaltyTier extends Model { /* ... */ }
```

Callers anywhere: `\App\Models\LoyaltyTier::find($id)` — works.

### Strategy B: `Modules\{Name}\*` PSR-4 (recommended for new modules)

PSR-4 mapping `"Modules\\": "modules/"` resolves clean namespaces:

```php
// modules/loyalty/src/Models/LoyaltyTier.php
namespace Modules\Loyalty\Models;

class LoyaltyTier extends Model { /* ... */ }
```

Pros: clearer separation, no name collisions.
Cons: requires updating all callers when porting from `app/Models/`.

After deciding, run `composer dump-autoload` once.

## ③ Migrations

Place migrations under `modules/{name}/database/migrations/`. The engine
registers the path with Laravel's migrator at boot **only when the module
is enabled** — so:

```bash
php artisan migrate         # picks up enabled modules' migrations
php artisan migrate:status  # shows them mixed with core migrations
```

If a module is disabled when you run `migrate`, its tables are NOT created.
Enabling later → running migrate picks them up retroactively.

**Naming.** Use timestamp prefixes like core (`2026_03_28_100003_create_loyalty_tiers_table.php`).
The engine sorts ALL migration paths together, so cross-module foreign
keys must be ordered correctly via timestamps.

## ④ Routes

Module routes go in `modules/{name}/routes/web.php`. They are wrapped in the
`web` middleware group automatically (session + CSRF):

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('gazu.')->group(function () {
    Route::get('/garazh', [\App\Http\Controllers\Gazu\GarageController::class, 'index'])
        ->name('garage');
    // ...
});
```

When the module is disabled, this file is NOT included → routes don't exist
→ requests return 404.

## ⑤ Filament resources, pages, widgets

Declare classes in `module.json` — `filament_resources`, `filament_pages`,
`filament_widgets`. The `AdminPanelProvider` calls `collectModuleResources()`
which iterates manifests of **enabled** modules and merges them into the panel.

Files can live anywhere under `modules/{name}/src/Filament/`; their location
doesn't matter — only the FQCN in the manifest matters.

```json
{
  "filament_resources": [
    "App\\Filament\\Resources\\LoyaltyTierResource"
  ]
}
```

When disabled, classes are skipped → no menu entry, no /admin URL.

## Lifecycle hooks

There are no `install()` / `uninstall()` hooks **yet** (planned for Phase 3).
For now:

- **Migration on first enable** — run `php artisan migrate` after toggling on.
- **Seed data** — invoke seeders manually: `php artisan db:seed --class=AutoPartsSeeder`.
- **Cleanup on disable** — disable preserves data. Hard-delete tables: drop them via a follow-up migration in the module.

## Creating a new module

```bash
php artisan make:module my_feature --label="My Feature"
```

Generates:

```
modules/my_feature/
├── module.json           # filled with name+label, rest empty
├── README.md
├── src/Models/
├── src/Services/
├── src/Filament/Resources/
├── routes/web.php        # empty stub
├── database/migrations/
└── tests/Feature/
```

Then:

1. Edit `module.json` — list providers, resources, pages, widgets you'll add.
2. Add models / migrations / Filament code.
3. `composer dump-autoload`
4. `php artisan module:enable my_feature`
5. `php artisan migrate`

## Toggling modules

### Via Filament UI

`/admin/modules` (when `gazu_admin` user logged in) — list of all modules with
on/off switches. Persists to DB (`modules` table) immediately, cache invalidates
via `ModuleObserver`.

### Via CLI

```bash
php artisan module:list                # show all + status
php artisan module:enable loyalty      # turn ON (DB row)
php artisan module:disable loyalty     # turn OFF (DB row, data preserved)
```

### Via ENV

`.env`:

```ini
MODULE_LOYALTY=false
```

Then `php artisan config:clear`. ENV overrides DB.

### Programmatically

```php
\App\Models\Module::updateOrCreate(
    ['key' => 'loyalty'],
    ['enabled' => true, 'enabled_at' => now()]
);
\App\Support\ModuleManager::clearCache();
```

Or via preset (see [PRESETS.md](PRESETS.md)).

## Checking module state in code

```php
use App\Support\ModuleManager;

// In a controller / service:
if (ModuleManager::for('loyalty')->enabled()) {
    // Apply loyalty discount
}

// In a blade view:
@if(module('loyalty')->enabled())
    <x-loyalty.badge :user="$user" />
@endif

// Read settings from manifest schema:
$rate = ModuleManager::for('loyalty')->setting('default_rate', 1);
```

In Filament resources, use the `RequiresModule` concern:

```php
use App\Filament\Concerns\RequiresModule;

class LoyaltyTierResource extends Resource
{
    use RequiresModule;
    protected static string $requiredModule = 'loyalty';
}
```

The resource hides itself when the module is off.

In HTTP routes:

```php
Route::get('/loyalty', 'LoyaltyController@index')
    ->middleware('module:loyalty');
```

## Dependencies between modules

```json
{
  "name": "advanced_loyalty",
  "requires_modules": ["loyalty"]
}
```

`ModuleManager::enabled()` returns `false` if any required module is off,
even when the module itself is ON. This prevents "half-loaded" states.

## Testing modules

Put feature tests under `modules/{name}/tests/Feature/`. Laravel's PHPUnit
config will pick them up if the `tests/` directory is added to phpunit.xml,
or if you use Pest with auto-discovery.

For module-aware tests, override state explicitly:

```php
public function test_loyalty_award(): void
{
    \App\Models\Module::create(['key' => 'loyalty', 'enabled' => true]);
    \App\Support\ModuleManager::clearCache();

    // ... test body
}
```

The 7 ModuleSystem tests in `tests/Feature/Modules/ModuleSystemTest.php`
are a good template — they exercise toggle priority, route registration,
observer cache invalidation, etc.

## Best practices

- **Self-contained.** Don't reach into other modules' models. If you need
  cross-module data, listen for events (`OrderCreated`, etc.).
- **Defensive against disabling.** If your module references another model
  (e.g. `User`), guard `class_exists` only when ambiguous; assume Core
  models always exist.
- **Idempotent migrations.** Use `Schema::hasTable()` / `hasColumn()` checks
  so a re-enable + re-migrate doesn't blow up on existing tables.
- **No global state.** Don't mutate `config()` or static vars at boot.
- **Document `requires_modules`.** If your module needs `loyalty`, declare it.

## Anti-patterns

- ❌ Direct `\App\Models\Loyalty\X` references when `loyalty` module is optional
- ❌ Hard-coded routes in core `routes/web.php` to module controllers
- ❌ Forgetting `composer dump-autoload` after creating files
- ❌ Putting module-exclusive migrations in core `database/migrations/`

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| "Class App\Models\X not found" | Classmap stale | `composer dump-autoload` |
| Route 404 for module URL | Module is OFF, or routes-cache stale | `module:enable` + `php artisan route:clear` |
| Filament resource not in menu | Manifest missing entry, or class doesn't exist | check `module.json`, then `class_exists()` |
| Migration not running | Module disabled at `migrate` time | enable first, then migrate |
| Test fails: "table X doesn't exist" | RefreshDatabase doesn't pick up module migrations | Make sure `migrations_path` is in manifest |

## Reference

- `app/Support/ModuleManager.php` — enabled-state resolver
- `app/Support/ModuleDiscovery.php` — manifest scanner + boot hooks
- `app/Models/Module.php` — DB persistence
- `app/Observers/ModuleObserver.php` — cache invalidation
- `app/Console/Commands/MakeModuleCommand.php` — scaffold generator
- `config/modules.php` — registry of known modules (mirror of folder)
- `modules/_example/module.json` — empty template
