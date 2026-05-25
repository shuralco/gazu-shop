# SimpleShop — Modular Laravel E-commerce Engine

> **A pluggable Laravel 12 + Filament 3 storefront** powering Ukrainian online
> retailers (currently live: [gazu.uno](https://gazu.uno) auto-parts).
> The same engine can spin up an auto-parts shop, a cosmetics store, or a
> generic e-commerce site by toggling modules and applying presets — no code
> changes per business type.

## At a glance

- **14 self-contained modules** — opt in/out via UI, ENV, or DB toggle
- **Theme system** — swap visual identity without touching business logic
- **Presets** — one-command business profile (`auto-parts`, `cosmetics`, ...)
- **Bilingual** (UA + EN) via Spatie Translatable
- **Production stack** — Laravel Octane (Swoole), Redis, Spatie ResponseCache, Meilisearch

## Tech stack

| Layer | Tech |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Admin | Filament 3 + Livewire 3 |
| Frontend | Tailwind 4 + Alpine (via Livewire) |
| DB | MySQL 8 (prod), SQLite (tests) |
| Cache | Redis (+ Spatie ResponseCache for HTML) |
| Search | Meilisearch + Laravel Scout |
| Runtime | Octane (Swoole) |
| Deploy | Coolify (Docker) |

## Quick start

```bash
git clone https://github.com/shuralco/gazu-shop my-shop
cd my-shop
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate

# Pick a business profile:
php artisan preset:apply auto-parts    # or cosmetics, general-shop
php artisan migrate                     # runs newly-enabled modules' migrations
npm run build
php artisan serve
```

Visit `http://localhost:8000` — storefront should render.

Full walkthrough: **[docs/QUICKSTART.md](docs/QUICKSTART.md)**.

## Architecture overview

```
                ┌────────────────────┐
                │      PRESETS       │  declarative business profile
                │  presets/*.php     │  (theme + modules + settings)
                └──────────┬─────────┘
                           ▼ applies
            ┌──────────────┴──────────────┐
            ▼                             ▼
    ┌────────────────┐          ┌────────────────────┐
    │     THEMES     │          │      MODULES       │
    │  themes/*/     │          │   modules/*/       │
    │  CSS + views   │          │   features         │
    └────────────────┘          └────────────────────┘
            │                             │
            └──────────────┬──────────────┘
                           ▼
                   ┌───────────────┐
                   │     CORE      │
                   │   app/        │
                   └───────────────┘
```

Read more: **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)**.

## The 14 modules

Shipping: `novaposhta`, `ukrposhta`, `rozetka_delivery`, `meest_express`
Inventory: `multi_warehouse`
Marketing: `loyalty`, `coupons`, `reviews`, `comparison`, `feed_export`
B2B: `wholesale`
Auto-specific: `gazu_garage`, `auto_parts_seed`
Admin tools: `quick_fill`

Toggle any module on/off via:

```bash
php artisan module:list
php artisan module:enable loyalty
php artisan module:disable wholesale
```

Or via Filament admin: `/admin/modules`.

Full module developer guide: **[docs/MODULES.md](docs/MODULES.md)**.

## The 3 presets

| Preset | For | Apply |
|---|---|---|
| `auto-parts` | Full auto-parts retailer (GAZU profile) | `php artisan preset:apply auto-parts` |
| `cosmetics` | Beauty / cosmetics shop | `php artisan preset:apply cosmetics` |
| `general-shop` | Minimal generic e-commerce | `php artisan preset:apply general-shop` |

Add `--dry-run` to preview without persisting.

Authoring your own presets: **[docs/PRESETS.md](docs/PRESETS.md)**.

## The theme system

Currently one theme ships (`gazu` — brutal-style with auto-parts focus).
Themes control CSS tokens + blade view overrides. Switch with:

```bash
php artisan theme:set gazu
npm run build
```

Or via Filament: `/admin/theme-settings`.

Creating themes: **[docs/THEMES.md](docs/THEMES.md)**.

## Documentation index

| Topic | Doc |
|---|---|
| Architecture overview | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| Quick start | [docs/QUICKSTART.md](docs/QUICKSTART.md) |
| Module developer guide | [docs/MODULES.md](docs/MODULES.md) |
| Theme developer guide | [docs/THEMES.md](docs/THEMES.md) |
| Preset authoring | [docs/PRESETS.md](docs/PRESETS.md) |
| Multi-client history | [docs/MULTI-CLIENT-ARCHITECTURE.md](docs/MULTI-CLIENT-ARCHITECTURE.md) |
| Multi-warehouse logic | [docs/MULTI-WAREHOUSE.md](docs/MULTI-WAREHOUSE.md) |
| Nova Poshta integration | [docs/NOVA-POSHTA.md](docs/NOVA-POSHTA.md) |
| Inventory logic | [docs/INVENTORY-LOGIC.md](docs/INVENTORY-LOGIC.md) |
| Testing | [docs/TESTING-GUIDE.md](docs/TESTING-GUIDE.md) |
| Coolify deploy | [docs/DEPLOY.md](docs/DEPLOY.md) |
| Cloning a new shop | [docs/CLONE-NEW-SHOP.md](docs/CLONE-NEW-SHOP.md) |

## Local development

```bash
docker compose -f docker-compose.coolify-final.yml up -d
# Visit http://localhost:8089
```

Hot-reload via bind-mounts is configured for `app/`, `resources/`, `routes/`,
`config/`, `database/`, `public/`, `bootstrap/`, `modules/`, `themes/`, `presets/`.

For tests:

```bash
php artisan test                            # all tests
php artisan test tests/Feature/Modules/     # module system suite
```

## Status

| Component | State |
|---|---|
| Module system (Phase 1) | ✅ shipped — 14 modules ported |
| Theme system (Phase 2) | ✅ shipped — gazu theme + 3 presets |
| Hooks registry (Phase 3) | 🚧 in progress |
| Setup wizard | 📋 planned |
| `make:theme` artisan | 📋 planned |

## License

Proprietary (commercial). Contact author for licensing.

## Credits

Built by [Lionex](https://github.com/shuralco) with [Claude Code](https://claude.com/claude-code).
