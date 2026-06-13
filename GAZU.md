# GAZU — Технічна документація

> **Інтернет-магазин автозапчастин для китайських авто.** Laravel 12 + Filament 3 + Livewire 3 + Tailwind 4 + Octane (Swoole) + Redis + MySQL.

---

## 📋 Зміст

1. [Стек](#стек)
2. [Локальний запуск](#локальний-запуск)
3. [Архітектура](#архітектура)
4. [URLs та маршрути](#urls-та-маршрути)
5. [Адмінка](#адмінка)
6. [Кеш-стратегія](#кеш-стратегія)
7. [Email templates](#email-templates)
8. [Деплой Coolify](#деплой)
9. [Performance](#performance)
10. [Безпека](#безпека)
11. [Тестування](#тестування)
12. [Troubleshooting](#troubleshooting)

---

## Стек

| Шар | Технологія |
|---|---|
| **Backend** | PHP 8.3 + Laravel 12 |
| **Frontend** | Blade + Livewire 3 + Alpine 3 (через Livewire bundle) + Tailwind 4 |
| **Admin** | Filament 3 (`/admin`) |
| **Server** | Laravel Octane + Swoole (8 workers + 4 task workers) |
| **DB** | MySQL 8 |
| **Cache** | Redis (responsecache + view + tagged Cache) |
| **Queue** | Redis |
| **Mail** | SMTP (configurable у `.env`) |
| **Hosting** | Coolify v4 + Docker + nginx → Octane :8000 |

---

## Локальний запуск

```bash
docker compose up -d           # build + start
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan storage:link
```

Сайт: `http://localhost:8089`
Admin: `http://localhost:8089/admin` (логін `admin@admin.com` / `admin`)

### Hot reload
```bash
docker compose exec app npm run dev    # vite HMR
docker compose exec app php artisan octane:reload    # після зміни Controller/Service
```

---

## Архітектура

### Каталоги
- **`app/Http/Controllers/Gazu/`** — storefront контролери (Store, Cart, Checkout, Auth, Wishlist, Callback, StockNotification, Garage)
- **`app/Services/Gazu/`** — domain services (CatalogQuery, MegaMenuBuilder)
- **`app/Filament/`** — adminка (Resources + Pages)
- **`app/Models/`** — Eloquent моделі
- **`app/Observers/`** — Order/Product/Brand/Category/ShipmentNotification
- **`app/Mail/TemplatedMail.php`** — template-driven mailable
- **`resources/views/gazu/`** — storefront views
- **`resources/views/components/gazu/`** — Blade components (cart-icon, product-card, callback-popover, stock-notify, etc.)
- **`resources/css/themes/gazu/`** — окремий Tailwind entry point для storefront (admin використовує власний)

### Маршрутизація
**`routes/web.php`** — group `gazu` namespace. Catch-all `/{slug}` resolves продукт vs категорію (Rozetka-style URLs).

### Locale
UA-only. Translatable поля (Product.title, Category.title) зберігаються у JSON `{"uk":"..."}`.

---

## URLs та маршрути

### Публічні
| URL | Опис |
|---|---|
| `/` | Головна (`StoreController::home`) |
| `/catalog` | Загальний каталог з фільтрами (категорія/бренд/ціна/стан/наявність/сорт) |
| `/{category-slug}` | Сторінка категорії (`engine`, `brakes`, `engine/oil-filters`) |
| `/{product-slug}` | Сторінка товару (наприклад `/akumulyator-varta-black-dynamic-45-ah-13179`) |
| `/novynky` | Новинки (filter `?new=1`) |
| `/khity` | Хіти продажу |
| `/akcii` | Акції |
| `/brand/{slug}` | Бренд (`/brand/mahle`) |
| `/zapchastyny/{make}/{model?}/{engine?}` | По авто (пр. `/zapchastyny/geely/coolray`) |
| `/blog`, `/blog/{slug}` | Блог |
| `/sto`, `/contacts`, `/about`, `/delivery`, `/warranty`, `/faq` | Info pages |
| `/optom` | Гуртом (UA canonical) |
| `/bonusy` | Лояльність |
| `/search?q=...` | Пошук (плюс fuzzy OEM matching) |
| `/sitemap.xml`, `/sitemap-{main,categories,products,brands}.xml` | XML sitemaps |
| `/robots.txt` | robots |

### Authenticated (`auth` middleware)
| URL | Опис |
|---|---|
| `/login` (UA canonical from `/auth` 301) | Логін / реєстрація |
| `/kabinet` | Особистий кабінет |
| `/kabinet/zamovlennya/{id}` | Деталі замовлення |
| `/zamovlennya/{id}/oplata` | Платіжна сторінка (stub) |
| `/garazh` | Гараж (мої авто) |
| `/wishlist` | Обране |

### API
| URL | Опис |
|---|---|
| `GET /api/search/suggest?q=` | Autocomplete з fuzzy OEM (W712/93 = "W712 93") |
| `GET /api/wishlist/ids` | IDs обраних (hydration) |
| `GET /api/products/by-ids?ids=` | Batch products fetch (recently-viewed) |
| `GET /api/cars/{makes,models,engines}` | Car-selector cascade |
| `GET /api/compat/check?product_id=X&make=&model=&engine=` | Compat check |
| `POST /api/callback` | Заявка на дзвінок (throttled 3/IP/10min) |
| `POST /api/stock-notify` | Notify-when-in-stock (throttled 5/IP/10min) |
| `POST /cart/add,update,remove,clear` | Cart ops (throttle 60/IP/min) |
| `POST /cart/coupon/{apply,remove}` | Купони |
| `POST /wishlist/toggle` | Toggle wishlist |
| `POST /login,register,logout` | Auth |

### Legacy 301 redirects (для SEO без втрати backlinks)
- `/auth` → `/login`
- `/account*` → `/kabinet*`
- `/orders/{id}/payment` → `/zamovlennya/{id}/oplata`
- `/garage*` → `/garazh*`
- `/loyalty` → `/bonusy`
- `/wholesale` → `/optom`
- `/brendy*` → `/brand*`

### Hidden (404 без `?dev=1` + production)
- `/v2`, `/v3`, `/catalog/v2`, `/catalog/v3` — dev variants
- `/m/{page}` — test mobile (тільки `APP_DEBUG=true`)

---

## Адмінка

### Шлях: `/admin`

### Найважливіші Resources
| Resource | Опис |
|---|---|
| **Catalog** → Products, Categories, Brands, CarMakes/Models/Engines | Каталог |
| **Orders** → Orders, NpShipments, UpShipments, ReceivingOrders, PaymentLogs | Замовлення + ТТН |
| **Marketing** → Coupons, EmailTemplate | Промо + emailи |
| **Налаштування** → CallbackRequests, SearchQueries, CustomerGroup, Coupons | Leads + analytics |

### Editable settings
**`/admin/gazu-visual-settings`** — централізована панель:
- SEO/Privacy (noindex toggle)
- Верхня смуга (cities/hours/links)
- Hero (заголовок, підзаголовок, опис, кольори акцентів)
- Trust-блок (4 бенефіти)
- Назви секцій
- VIN-блок
- Реєстрація / бонуси
- СТО / Доставка
- Hero V2/V3 (alt variants)
- Footer (колонки, payments, social)
- SEO-текст головна (RichEditor)
- Картка товару (тексти Доставка+Оплата + closest-warehouse label)
- Порожні стани (404 title/desc)

**`/admin/email-templates`** — 6 default + ad-hoc:
- `order.created` / `order.admin_new`
- `order.paid` / `order.shipped`
- `callback.received` / `auth.welcome`

Preview action (sample data) + Send Test (на свій email).

**`/admin/cache-manager`** + **`/admin/cache-settings`** — granular flush + TTL settings + Redis hit-rate live.

**`/admin/shop-settings`** — Telegram bot token + chat ID, base URL, mail config.

---

## Кеш-стратегія

3-шарова + cookie:

### 1. Spatie ResponseCache (full-page HTML)
- Redis-tagged `gazu-response`
- Кешує тільки **guests** (auth users → bypass)
- TTL: 1 година (`responsecache.cache_lifetime_in_seconds`)
- Замінювачі: `CsrfTokenReplacer` (per-request CSRF token swap)
- Cache key includes:
  - locale
  - query string
  - **Vite manifest hash** (auto-invalidate між deploys без stale CSS)

### 2. Eloquent observers (auto-flush)
`AppServiceProvider::boot()` reгеструє `ResponseCacheObserver` на:
- Product, Category, Brand, CarMake, CarModel, CarEngine
- Order створення → flush + Telegram notification
- NpShipment/UpShipment створення з TTN → email клієнту з `order.shipped`

### 3. Cache::tags() (DB-derived data)
- `tags(['catalog'])` для recommended products, megamenu, popular categories
- `tags(['settings'])` для DisplaySettings
- Flush через AppServiceProvider observers або CacheManager admin page

### 4. Cookie-based (client-only)
- `gazu_recent` — recently viewed (CSV product IDs, 24 max, 30d)
- `gazu_compare` — **видалено** (Etap 83)
- Wishlist hydration через `/api/wishlist/ids` + `window.GAZU_WISHLIST_IDS` Set

---

## Email templates

**Engine:** `App\Mail\TemplatedMail($key, $vars)` — reads `EmailTemplate` by key з cache 5min + auto-flush.

**Variables interpolation:** `{{var.path}}` — dot notation для nested array/object access.

**View wrapper:** `resources/views/emails/templated.blade.php` — header (GAZU dark) + body html + footer (phone + url).

**Default templates** (seeded):
| Key | When triggered | Default subject |
|---|---|---|
| `order.created` | CheckoutController после save Order | "Дякуємо за замовлення №{{order.id}} — GAZU" |
| `order.admin_new` | Same place, копія адміну | "НОВЕ замовлення №{{order.id}} · {{order.total}} ₴" |
| `order.paid` | Manual / future hook | "Замовлення №{{order.id}} оплачено" |
| `order.shipped` | NpShipment/UpShipment created з TTN (Etap 80 observer) | "Замовлення №{{order.id}} в дорозі · ТТН {{order.ttn}}" |
| `callback.received` | CallbackController::store | "НОВА заявка на дзвінок · {{callback.phone}}" |
| `auth.welcome` | AuthController::register | "Ласкаво просимо в GAZU!" |

**Adding new template:** create row у `email_templates` (key + name + subject + body_html) → call `Mail::send(new TemplatedMail('your.key', $vars))`.

---

## Деплой

### Coolify (production)
- **Server:** Hetzner Helsinki (23.88.115.55) — keep alive 24/7
- **Domain:** gazu.uno (+ www) з Let's Encrypt SSL via Coolify proxy
- **Application UUID:** `bgkgc8ww0co8w4wo0kw0osck`
- **Workflow:** push to `main` → GitHub Actions / Coolify webhook → docker build no-cache → swap container
- **Container lifecycle:** ~5-15s downtime per deploy (HTTP 502 window)
- **Manual deploy:** `mcp__coolify__deployments` (force=false уникає docker no-cache якщо можливо)

### SSH hotfix flow
⛔ **НЕ роби голий `view:clear`** — лишає storefront холодним (рекомпіляція ~394
blade ≈500ms на перший хіт кожної сторінки). Використовуй безпечний скрипт:
```bash
scripts/blade-hotfix.sh resources/views/.../file.blade.php \
                        /var/www/html/resources/views/.../file.blade.php
# робить: cp → view:cache (НЕ голий view:clear) → responsecache:clear → cache:warm
```
Краще взагалі — повний `Coolify deploy` (контейнер image-based; docker cp зникає
на наступному деплої). PHP code зміни потребують Coolify deploy (свіжий opcache)
або `docker restart $CID`.

### Migrations
```bash
docker exec $CID php artisan migrate --force
docker exec $CID php artisan db:seed --class=Seeder --force
```

### Cache flush на prod
⚠️ Після clear ОБОВʼЯЗКОВО re-cache+warm (інакше cold storefront ~500ms).
В адмінці кнопка «Очистити ВЕСЬ кеш» уже робить це правильно (clear→optimize→
octane:reload→cache:warm). Вручну:
```bash
docker exec $CID php artisan view:cache       # НЕ голий view:clear
docker exec $CID php artisan route:clear
docker exec $CID php artisan responsecache:clear
docker exec $CID php artisan cache:clear
docker exec $CID php artisan octane:reload   # реload без full restart
docker restart $CID                          # full restart (clean state)
```

---

## Performance

### Octane (Swoole)
- 8 workers + 4 task workers + max-requests 1000 (`docker/supervisord.conf`)
- Workers recycle після 1000 requests (memory leak protection)
- App keeps booted у пам'яті (no cold start per request)

### Поточні metrics (виміряно)
| Page | Cold TTFB | Warm TTFB | Throughput |
|---|---|---|---|
| `/` | 167ms | 167ms | ~6 req/s |
| `/catalog` | 957ms | 763ms | 7.87 req/s (20 concurrent) |
| `/engine` | 814ms | — | — |
| `/brand/mahle` | 593ms | — | — |
| `/api/search/suggest` | 224ms | — | — |

### Compression
- nginx gzip enabled (`docker/nginx.conf`) — 812KB → 86KB (10.5%)
- Static `/build/*` з `Cache-Control: public, immutable, max-age=31536000`

### Optimization layers
1. **Prefetch on hover/touchstart/mousedown/viewport** — wire:navigate links заздалегідь у HTTP cache (Etap 49)
2. **Image instant feedback** — bg placeholder uncss
3. **View Transitions disabled** — `@view-transition { navigation: none }` для instant DOM swap (mobile-app feel)
4. **`.gazu-stagger > * { opacity: 1 }`** — no stagger animation на SPA navigate (визивав flash)
5. **Tactile press feedback** — `:active scale(0.97)` + `touch-action: manipulation` (kill 300ms tap delay)

---

## Безпека

### Реалізовано
- HSTS `max-age=31536000; includeSubDomains`
- `x-frame-options: SAMEORIGIN`
- `x-content-type-options: nosniff`
- `x-xss-protection: 1; mode=block`
- CSRF token — meta tag + form auto-refresh (`window.GAZU_CSRF` global, Spatie CsrfTokenReplacer per-request)
- Bcrypt password hashing
- Sanctum для admin sessions
- Throttle middleware на POST endpoints:
  - cart/add 60/min
  - callback 3/IP/10min
  - stock-notify 5/IP/10min
  - auth login/register 10/min
- Auth middleware на /admin, /kabinet, /garazh, /wishlist toggle, /orders/.../payment
- SQL injection захист через Eloquent / prepared statements
- HTML escaping default Blade `{{ }}`, raw `{!! !!}` тільки для admin-edited HTML (через RichEditor)

### Audit results
- `'OR 1=1--` → 0 results (escaped) ✓
- XSS у q → JSON encoded ok ✓
- /admin → 302 redirect (auth required) ✓

---

## Тестування

### Smoke test
```bash
./scripts/smoke.sh   # 50+ endpoints
```

### Load test (concurrent)
```bash
# 20× /catalog parallel
for i in {1..20}; do
  curl -sk "https://gazu.uno/catalog?cb=$i" -o /dev/null -w '%{http_code}\n' &
done; wait
```

**Expected:** all 20 → 200 у ~2.5s (8 workers handle).

### Functional flows
1. **Add to cart:** GET / (acquire CSRF), POST /cart/add з cookie+token
2. **Coupon apply:** add to cart → POST /cart/coupon/apply з code
3. **Callback:** POST /api/callback з phone
4. **Stock notify:** POST /api/stock-notify з email+product_id
5. **Wishlist:** auth user → POST /wishlist/toggle

### Stress test target
- 7+ req/s sustained для /catalog
- TTFB < 1s cold, < 500ms warm

---

## Troubleshooting

### HTTP 502 (Bad Gateway)
- Coolify container restart in progress (5-15s window)
- Octane crashed → check `docker logs $CID` for stack trace

### HTTP 419 (CSRF mismatch)
1. Перевірити `<meta name="csrf-token" content="...">` у HTML
2. Перевірити Spatie CsrfTokenReplacer у `config/responsecache.php`
3. Cache flush: `responsecache:clear` + restart
4. Сесія: cookie `gazu_session` + `XSRF-TOKEN` мають бути set

### /catalog timeout
- Перевірити Octane workers running: `docker exec $CID ps aux | grep swoole | wc -l` має бути ~14
- Логи: `docker exec $CID tail storage/logs/laravel.log`
- N+1 у CatalogQuery — `enableQueryLog` + count

### Email не відправляється
1. Перевірити `EmailTemplate::findByKey('key')` повертає row
2. `is_active = true` для template
3. SMTP config у `.env`
4. Logs: `Mail` channel + `production.ERROR`

### Telegram бот не повідомляє
- `/admin/shop-settings` → telegram_bot_token + telegram_chat_id заповнені
- TelegramService::isConfigured() returns true

### Storefront cache не оновлюється
1. `responsecache:clear` + `view:clear`
2. Vite manifest hash змінився при `npm run build` → автоматично новий cache key (Etap 35)
3. Manual: change `RESPONSE_CACHE_ENABLED=false` у `.env` (debug only)

### Octane reload vs restart
- `octane:reload` — soft, workers закінчують current request → recycle. Безпечно для config змін.
- `docker restart $CID` — hard, drops connections. Потрібно для view/class structure changes.

---

## Етапи розробки

Project розвинувся через 90+ ітеративних etaps з трекерами `Etap NN` у commit messages. Major milestones:

- **24** Octane Swoole
- **28** Spatie ResponseCache
- **35** Vite manifest hash у cache key
- **36** Observer auto-invalidation
- **45-48** UX polish (icons, frameless cards, SPA)
- **49** 5-layer prefetch
- **50, 53** CSRF root cause fixes
- **59-60** SEO block + premium design
- **61-63** Callback popup
- **64-68** SEO URL canonicalization
- **69-71** 404 + Schema.org + sitemap
- **72** Email template engine + admin
- **73-74** Coupons checkout + stock notify
- **75** OG images + Search OEM fuzzy
- **76** Accessibility
- **78** Welcome email
- **80** Auto TTN email observer
- **81** Search analytics admin
- **82** Category filter sidebar
- **85** Editable closest label + gear placeholder + lightbox
- **87** Octane workers 4→8

---

**Maintained by:** Claude Opus 4.7 / vladpowerpro@gmail.com
**Last update:** 2026-05-19
