# Architecture Overview

SimpleShop is a **modular Laravel 12 + Filament 3 e-commerce engine** built around a
plugin-style architecture inspired by WordPress and OpenCart. The same codebase can
power radically different storefronts (auto-parts, cosmetics, generic e-commerce)
by toggling modules, switching themes, and applying presets.

## The three layers

```
┌─────────────────────────────────────────────────────────────┐
│                        PRESETS                              │
│       presets/{name}.php — one-command business profile     │
│  (theme + module toggles + display settings)                │
└──────────────────────┬──────────────────────────────────────┘
                       │ applies
        ┌──────────────┴──────────────┐
        ▼                             ▼
┌────────────────────┐      ┌────────────────────┐
│      THEMES        │      │     MODULES        │
│  themes/{name}/    │      │  modules/{name}/   │
│  • CSS tokens      │      │  • Models, Services│
│  • View overrides  │      │  • Filament res.   │
│  • theme.json      │      │  • Migrations      │
│                    │      │  • Routes          │
│  ONE active theme  │      │  • module.json     │
│  at a time         │      │                    │
└────────────────────┘      │  ANY subset can    │
                            │  be active         │
                            └────────────────────┘
                                      │
                                      ▼
                       ┌────────────────────────────┐
                       │           CORE             │
                       │  app/ — controllers, base  │
                       │  models, shared services,  │
                       │  ModuleManager, ThemeMgr   │
                       └────────────────────────────┘
```

- **Core** (`app/`) — the engine itself. Base models that every shop needs
  (User, Order, Product, Category, ...). Shared services. Module/theme
  resolution and discovery. Never specific to a business type.
- **Modules** (`modules/{name}/`) — opt-in business features. Each module
  is self-contained (its own models, migrations, Filament resources, routes).
  Currently 14 modules ship with the engine.
- **Themes** (`themes/{name}/`) — visual identity. CSS tokens + view overrides.
  Exactly one theme is active at a time.
- **Presets** (`presets/{name}.php`) — declarative business-type bundles:
  "Auto-parts shop" = theme `gazu` + modules `[novaposhta, ukrposhta, garage, ...]`
  + display settings `{show_car_selector: true}`. Apply with one command.

## Module lifecycle

```
        ┌───────────────────────────────────────┐
        │   Boot AppServiceProvider.register()  │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │   ModuleDiscovery::registerProviders  │
        │   scans modules/*/module.json         │
        │   registers Provider IF likely-on     │
        │   (config/ENV only, no DB calls)      │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │   Boot AppServiceProvider.boot()      │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │   ModuleDiscovery::bootModuleResources│
        │   for each manifest where             │
        │   ModuleManager::enabled() = true:    │
        │     • addNamespace(views)             │
        │     • migrator->path(migrations)      │
        │     • Route::group(routes/web.php)    │
        │     • translator->addNamespace(lang)  │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │   ThemeDiscovery::bootActiveTheme     │
        │   prepends themes/{active}/resources  │
        │   /views/ to view finder              │
        └───────────────────────────────────────┘
```

`ModuleManager::enabled()` resolves in this order — **first match wins**:

1. `modules` DB table (UI/CLI toggle, no redeploy)
2. `MODULE_{KEY}` env var (CI / per-environment)
3. `config/modules.{key}.enabled` (manifest default)

Disabled module = its Provider never registers + its routes/views/migrations
never load. Data stays intact (re-enabling restores everything).

## Theme lifecycle

`ThemeManager::active()` resolves in this order:

1. `DisplaySetting::get('active_theme')` (UI/preset toggle)
2. `env('THEME')` (per-environment override)
3. `config('themes.default')` fallback (currently `gazu`)

Active theme's `themes/{name}/resources/views/` is prepended to the blade
view finder. So `view('gazu.layout')` first looks at the theme's directory;
falls back to `resources/views/gazu/layout.blade.php` if missing.

## Where each kind of file lives

| File type | Core | Module | Theme |
|---|---|---|---|
| Eloquent models | `app/Models/{Generic}.php` | `modules/{n}/src/Models/{Specific}.php` | — |
| Filament resources | `app/Filament/Resources/{Generic}.php` | `modules/{n}/src/Filament/Resources/` | — |
| Controllers | `app/Http/Controllers/` | `modules/{n}/src/Http/Controllers/` | — |
| Routes | `routes/web.php` (core routes) | `modules/{n}/routes/web.php` | — |
| Migrations | `database/migrations/` (core) | `modules/{n}/database/migrations/` | — |
| Blade views (storefront) | `resources/views/` (defaults) | — | `themes/{n}/resources/views/` (overrides) |
| Blade components | `resources/views/components/` (shared) | — | (currently not theme-overridable) |
| CSS (admin) | `resources/css/app.css` | — | — |
| CSS (storefront) | — | — | `themes/{n}/resources/css/{n}.css` |
| Services | `app/Services/{Generic}/` | `modules/{n}/src/Services/` | — |
| Observers | `app/Observers/` | `modules/{n}/src/Observers/` | — |
| Commands | `app/Console/Commands/{Generic}/` | `modules/{n}/src/Console/Commands/` | — |

**Namespace strategy.** Migrated existing modules keep their original
`App\*` namespace — Composer `classmap` autoload resolves them from their
new location. This means **zero code changes** in callers when a class moves
from `app/Models/X.php` to `modules/y/src/Models/X.php`. New modules can use
the cleaner `Modules\{Name}\*` PSR-4 namespace if they prefer.

## The 14 current modules

| Module | Type | Highlights |
|---|---|---|
| `gazu_garage` | Auto-specific | User car list + filter |
| `multi_warehouse` | Inventory | N warehouses + transfers |
| `novaposhta` | Shipping (UA) | API, TTN, scan sheets, tracking |
| `ukrposhta` | Shipping (UA) | API, TTN, post offices |
| `rozetka_delivery` | Shipping | Provider stub |
| `meest_express` | Shipping | Provider stub |
| `loyalty` | Marketing | Tiers, bonus points, expiration |
| `coupons` | Marketing | Promo codes + checkout discounts |
| `wholesale` | B2B | Customer groups, group pricing |
| `reviews` | Marketing | Product reviews + moderation |
| `comparison` | UX | Product compare UI |
| `feed_export` | Marketing | Rozetka/Prom/OLX XML feeds |
| `quick_fill` | Admin tool | Excel-style bulk product entry |
| `auto_parts_seed` | Demo | Sample auto-parts catalog seeders |

See [MODULES.md](MODULES.md) for full details on each.

## The 3 current presets

| Preset | For | Modules ON | Modules OFF |
|---|---|---|---|
| `auto-parts` | Auto-parts shop (GAZU) | NP+UP+garage+wholesale+warehouse+feed+seed+quick_fill+all marketing | rozetka+meest |
| `cosmetics` | Beauty / cosmetics shop | NP+UP+loyalty+coupons+reviews+comparison+feed | garage+wholesale+warehouse+seed |
| `general-shop` | Generic e-commerce | NP+UP+reviews+coupons+comparison | most extras |

See [PRESETS.md](PRESETS.md) for full configs and how to create your own.

## Adjacent docs

- **[QUICKSTART.md](QUICKSTART.md)** — clone a new shop in 10 minutes
- **[MODULES.md](MODULES.md)** — module developer guide (create / extend / debug)
- **[THEMES.md](THEMES.md)** — theme developer guide
- **[PRESETS.md](PRESETS.md)** — preset structure + how to author one
- **[MULTI-CLIENT-ARCHITECTURE.md](MULTI-CLIENT-ARCHITECTURE.md)** — historical context for why this exists
- **[DEPLOY.md](DEPLOY.md)** — Coolify deploy procedure
- **[TESTING-GUIDE.md](TESTING-GUIDE.md)** — test setup + how to run

## Glossary

- **Engine** — the SimpleShop codebase itself, agnostic of any particular shop
- **Shop** — one deployment of the engine for one business (e.g. `gazu.uno`)
- **Module** — opt-in feature self-contained under `modules/{name}/`
- **Theme** — visual identity under `themes/{name}/`
- **Preset** — declarative business-type bundle under `presets/{name}.php`
- **Manifest** — `module.json` or `theme.json` describing the package
- **Toggle** — turn a module ON/OFF via DB, ENV, or config (data preserved)
