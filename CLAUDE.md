# Claude Code — Project Context

Quick orientation for AI assistants working on this codebase.

## What this is

**SimpleShop** — a Laravel 12 + Filament 3 e-commerce **engine** that can power
many different shops by toggling modules and applying presets. One canonical
deployment: [gazu.uno](https://gazu.uno) (auto-parts retail, Ukrainian market).

For human-facing intro: see [README.md](README.md).
For architecture: see [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Stack snapshot

- Laravel 12, PHP 8.2+, Filament 3, Livewire 3, Tailwind 4
- Octane (Swoole) in production
- MySQL 8, Redis, Meilisearch
- Coolify deployment to Hetzner

## Module / Theme / Preset architecture

This codebase has **THREE plugin layers** the user references constantly:

### Modules (`modules/{name}/`)
14 self-contained business features. Each has:
- `module.json` manifest (providers, filament_resources, filament_pages, filament_widgets, migrations_path, routes, etc.)
- `src/` with PHP classes
- `database/migrations/` (only run when enabled)
- `routes/web.php` (auto-loaded with web middleware)
- Toggle: DB → ENV → config waterfall

When module is OFF: provider doesn't register, routes return 404, migrations don't run, Filament hides resources. Data is preserved.

### Themes (`themes/{name}/`)
Visual layer. Currently 1 theme: `gazu`. Each has:
- `theme.json` manifest
- `resources/css/{name}.css` (Tailwind 4 entry, listed in `vite.config.js` input)
- `resources/views/` (prepended to view finder — overrides core)

Switch via `theme:set` or `/admin/theme-settings`.

### Presets (`presets/{name}.php`)
PHP array returns `{label, description, theme, modules_on[], modules_off[], display_settings}`. One command sets up a whole business profile:

```bash
php artisan preset:apply auto-parts
```

3 ship: `auto-parts`, `cosmetics`, `general-shop`.

## Key support classes

- `app/Support/ModuleManager.php` — module enabled-state resolver
- `app/Support/ModuleDiscovery.php` — boot-time scanner of `modules/*/module.json`
- `app/Support/ThemeManager.php` — active theme + theme discovery
- `app/Support/ThemeDiscovery.php` — view-finder prepend at boot
- `app/Models/Module.php` — DB persistence of toggle state
- `app/Observers/ModuleObserver.php` — cache invalidation on toggle

## Important conventions

### Namespace
Migrated existing modules **keep `App\*` namespace** via Composer classmap.
NEW modules can use `Modules\{Name}\*` PSR-4 — both work. After any move:
`composer dump-autoload`.

### Toggle priority
`ModuleManager::for('x')->enabled()` resolves in order:
1. `modules` DB table (UI/CLI toggle)
2. `MODULE_X` env var
3. `config('modules.x.enabled')` fallback

### Theme view resolution
`view('gazu.layout')` first looks in `themes/{active}/resources/views/gazu/layout.blade.php`, falls back to `resources/views/gazu/layout.blade.php`.

### Module Filament resources
Declared in `module.json` `filament_resources[]` — `AdminPanelProvider::collectModuleResources()` merges them based on enabled state.

### Routes
Module-specific routes live in `modules/{name}/routes/web.php`. Auto-loaded by `ModuleDiscovery::bootModuleResources` when module is on. Core routes stay in `routes/web.php`.

## Common operations

```bash
# Make new module
php artisan make:module my_feature

# List/toggle
php artisan module:list
php artisan module:enable loyalty
php artisan module:disable wholesale

# Theme
php artisan theme:set gazu
npm run build

# Preset
php artisan preset:apply auto-parts --dry-run
php artisan preset:apply auto-parts

# Tests
php artisan test                                # full suite (some legacy fails — see TESTING-GUIDE.md)
php artisan test tests/Feature/Modules/         # module system + theme/preset (14 tests, all pass)
php artisan test tests/Feature/Gazu/            # storefront tests (31, all pass)

# After moving files between dirs
composer dump-autoload

# After enabling/disabling modules with Octane running
php artisan octane:reload                       # NOT pkill (kills workers permanently)
```

## Recently completed (2026-05-24/25)

- **Phase 1** — Module system foundation (ModuleManager, Discovery, DB toggle, AdminPanel hooks, make:module). Pilot: `gazu_garage`.
- **Phase 1.5** — Ported all 14 modules to `modules/{name}/` via 3 batches.
- **Phase 2** — Theme system + presets. `themes/gazu/` extracted; `presets/{auto-parts,cosmetics,general-shop}.php`; `theme:set` + `preset:apply` commands.

Local commits (`f58a7bcb`, `de1164e0`, `c2720278`, `e6f6dd52`, `301729b5`, `9f73be04`) — pushed to GitHub through `e6f6dd52`; Phase 2 + hotfix held locally per user "доробляємо локально" instruction.

## Watch-outs (от memory)

- **NEVER** `pkill swoole_http_server` — kills workers without respawn → site down. Use `octane:reload` or `docker restart`.
- **opcache_reset via `php -r`** runs in CLI SAPI — does NOT clear Swoole's class cache. After PHP changes: `docker restart` or graceful Octane reload.
- **Trust strip icons** source = `GazuVisualSettings::$defaults` in PHP, not blade fallback.
- **Migrations had MySQL-only syntax** in spots; tests use sqlite — add driver guards before SHOW INDEX, SET FOREIGN_KEY_CHECKS=0, etc.
- **`RESPONSE_CACHE_DRIVER=redis`** is prod default; phpunit needs `RESPONSE_CACHE_DRIVER=array` override.
- **bootstrap/cache/routes-v7.php** may be root-owned (docker writeback). `sudo rm -f` if stale routes show after toggle.
- **NEVER** `migrate:fresh` / `migrate:reset` / `db:wipe` without explicit user permission — lost prod data once.

## User context

- vladpowerpro@gmail.com — owner, Ukrainian, builds Laravel e-commerce sites
- Has TWO local clones: `~/projects/gazu-shop` (primary), `~/simpleshop` (synced)
- One GitHub remote: `github.com/shuralco/gazu-shop`
- Continuous-iteration preference: don't stall for user-prompt during deploys/bg-pollers
- Self-tests UI via MCP Chrome — never asks user to run curl/DevTools
- Coolify server: `localhost` (Hetzner 23.88.115.55)

## Where to find recent decisions

Memory files: `/home/lionex/.claude/projects/-home-lionex-simpleshop/memory/MEMORY.md` (index).

Recent ones worth knowing:
- `gazu_perf_stack.md` — 6-layer perf optimization (catalog 18.6s → 0.6s)
- `gazu_octane_cache_hotfix.md` — Octane gotchas + swoole opcache behavior
- `feedback_destructive_db_ops.md` — explicit ban on destructive DB ops
- `coolify_deploy_procedure.md` — 9-step deploy via MCP
