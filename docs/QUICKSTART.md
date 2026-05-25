# Quickstart — New Shop in 10 Minutes

This guide takes you from `git clone` to a running shop with a chosen
business profile. For deep dives on each subsystem, see
[ARCHITECTURE.md](ARCHITECTURE.md), [MODULES.md](MODULES.md),
[THEMES.md](THEMES.md), [PRESETS.md](PRESETS.md).

## Prerequisites

- PHP 8.2+
- Composer 2
- Node.js 18+
- A database (MySQL 8 / PostgreSQL / SQLite for local dev)
- Redis (recommended; SQLite cache also works for local)

## Local-first walkthrough

This is the path **per a clean clone**. For deploying to Coolify or another
hosting provider, see [DEPLOY.md](DEPLOY.md) after step 9.

### 1. Clone

```bash
git clone https://github.com/shuralco/gazu-shop my-shop
cd my-shop
```

### 2. Install deps

```bash
composer install
npm install
```

### 3. Bootstrap environment

```bash
cp .env.example .env
php artisan key:generate

# Local SQLite — fastest for dev
touch database/database.sqlite
# (or configure MySQL/Postgres in .env)
```

### 4. Run migrations

```bash
php artisan migrate
```

This creates **core** tables (users, products, orders, ...) but skips
module-specific migrations until those modules are enabled.

### 5. Pick a business profile

Available presets:

| Preset | For |
|---|---|
| `auto-parts` | Auto-parts retailer (full GAZU profile) |
| `cosmetics` | Beauty / cosmetics |
| `general-shop` | Generic e-commerce |

Apply one:

```bash
php artisan preset:apply auto-parts --dry-run    # preview first
php artisan preset:apply auto-parts              # commit
```

This sets the theme, enables/disables modules, and writes default display
settings.

### 6. Run module migrations

Module migrations are only registered AFTER the module is enabled. After
applying a preset, run migrate again:

```bash
php artisan migrate
```

You'll see migrations from `modules/loyalty/`, `modules/multi_warehouse/`,
etc. run if they weren't already.

### 7. Build assets

```bash
npm run build
```

Generates `public/build/manifest.json` with hashed CSS/JS bundles for each
theme listed in `vite.config.js`.

### 8. Seed demo data (optional)

For `auto-parts`:

```bash
php artisan db:seed --class=AutoPartsSeeder
```

For others, see the module-specific seeders under `modules/{name}/database/seeders/`.

### 9. Create admin user

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'first_name' => 'Admin',
    'last_name'  => 'User',
    'email'      => 'admin@example.com',
    'phone'      => '+380...',
    'password'   => bcrypt('your-secure-password'),
    'is_admin'   => true,
]);
exit
```

### 10. Run dev server

```bash
php artisan serve
# In another terminal:
npm run dev
```

Visit `http://localhost:8000` — storefront should render with your chosen
theme.

Visit `http://localhost:8000/admin/login` — admin panel with only the modules
your preset enabled.

## What "preset" did under the hood

```
preset:apply auto-parts
   │
   ├─→ ThemeManager::setActive('gazu')
   │        ↳ writes DisplaySetting('active_theme', 'gazu')
   │
   ├─→ For each modules_on key:
   │        Module::updateOrCreate(['key' => 'X'], ['enabled' => true])
   │
   ├─→ For each modules_off key:
   │        Module::updateOrCreate(['key' => 'X'], ['enabled' => false])
   │
   ├─→ For each display_settings entry:
   │        DisplaySetting::set('shop_brand_name', 'GAZU')
   │        ...
   │
   └─→ Caches cleared (ModuleManager + ThemeManager)
```

After this, the engine boots into your business profile. Subsequent requests
see the new state immediately.

## Switching profiles later

You can re-apply at any time without re-cloning:

```bash
php artisan preset:apply cosmetics
php artisan migrate                    # if new modules' tables don't exist yet
php artisan octane:reload              # if Octane is running
```

Data from other modules is **preserved** — disabling `gazu_garage` doesn't
drop the `user_cars` table. Re-enabling restores everything.

## Customizing the shop

Once you have a base, the typical customization path is:

1. **Display settings via Filament admin** at `/admin/display-settings`
   (or programmatic `DisplaySetting::set()`)
2. **Module-by-module tweaks** at `/admin/modules` — turn things on/off,
   or per-module settings in the manifest's `settings_schema`
3. **Theme overrides** — copy `themes/gazu/resources/views/gazu/home/`
   files into your own `themes/myshop/resources/views/gazu/home/`. See
   [THEMES.md](THEMES.md).
4. **New modules** for shop-specific features — `php artisan make:module myfeature`.
   See [MODULES.md](MODULES.md).
5. **New preset** if you want to repeat this shop's recipe for another
   client — copy `presets/auto-parts.php` to `presets/my-shop.php` and edit.

## Common gotchas

| Symptom | Fix |
|---|---|
| "Class App\Models\NpShipment not found" | `composer dump-autoload` |
| 500 with "Unable to locate file in Vite manifest" | `npm run build` |
| Module's route returns 404 even after `module:enable` | `php artisan route:clear` |
| Migration says "table already exists" on enable | Module data is preserved from prior enable — that's by design |
| Tests fail with "table 'X' doesn't exist" | RefreshDatabase + module migrations: enable in `setUp()` |
| Theme switch doesn't show new look | Browser cache — hard refresh. CSS is hashed so should bust. |
| Octane caching old class state | `php artisan octane:reload` after enabling/disabling modules |

## Production checklist

Before deploying to production:

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_URL` set to real domain
- [ ] DB credentials configured
- [ ] Redis configured (`CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`)
- [ ] Octane workers set (`OCTANE_SERVER=swoole`)
- [ ] HTTPS at proxy layer (Traefik / Caddy / nginx)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan optimize` (config/route/view caches)
- [ ] `npm run build` (NOT `npm run dev`)
- [ ] Admin password is strong (not `password`)
- [ ] Test order flow end-to-end

See [DEPLOY.md](DEPLOY.md) for full Coolify procedure.

## What's next

Once you have a working shop:

- Configure shipping providers (`/admin/nova-poshta-settings`, `/admin/ukr-poshta-settings`)
- Add products via `/admin/products` or import CSV via `/admin/quick-fill-products`
- Customize emails under `resources/views/emails/` or `themes/{n}/resources/views/emails/`
- Set up cron for scheduled commands (`* * * * * php artisan schedule:run`)

Welcome aboard.
