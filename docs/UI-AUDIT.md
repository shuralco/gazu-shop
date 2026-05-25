# UI Audit — Frontend + Admin

**Date:** 2026-05-25
**Build:** `eb20d27d` (Phase 3 hooks)
**DB:** Production dump imported (1278 products / 165 categories / 38 brands)
**Theme:** `gazu` active
**Preset:** `auto-parts` applied

## 📊 Summary

| Layer | Total tested | ✅ OK | 🔁 Redirect | ❌ Broken |
|---|---|---|---|---|
| **Storefront** | 35 URLs | 31 | 4 | 0 |
| **Admin (Filament)** | 60 URLs | 57 | 0 | 3 |
| **Both totals** | **95** | **88 (93%)** | **4** | **3 (3%)** |

## ⚠️ Broken — needs fixing

| URL | Status | Error | Severity |
|---|---|---|---|
| `/admin/info-pages` | 500 | `Closure for TextColumn but $s unresolvable` in `InfoPageResource.php` columns | medium — нема доступу до CRUD info-pages в admin (але є through другий route — `admin/info-pages/create`) |
| `/admin/error404-settings` | 500 | `count(): Argument #1 must be Countable\|array, null given` (form/actions.blade.php) | low — окрема settings page, не критично |
| `/admin/np-api-logs` | 404 | Route не зареєстрований | low — `NpApiLogResource` маб. видалений на прод |

Both 500s — likely **also present on prod** (тести як неавторизований показували 302, але код у repo той самий).

## ✅ Storefront — Public Pages (31/35)

### Home + Catalog
| URL | Status | Notes |
|---|---|---|
| `/` | 200 | Hero + 20 product cards · 7 h2 sections (Усе для авто, Акції тижня, Новинки, Хіти, Дивились, Топ-бренди, SEO-text) |
| `/catalog` | 200 | h1: «Каталог» · 24 product cards |
| `/akcii` | 200 | h1: «Акції та знижки» · 24 cards |
| `/khity` | 200 | h1: «Хіти продажу» · 24 cards |
| `/novynky` | 200 | h1: «Новинки» · 24 cards |
| `/brand` | 200 | h1: «Бренди» · grid 38 брендів |
| `/blog` | 200 | h1: «Блог» · post list |
| `/contacts` | 200 | h1: «Контакти» · мапа + контакти |

### Categories (root URL pattern — без `/category/` prefix)
| URL | Status | h1 | Cards |
|---|---|---|---|
| `/engine` | 200 | Двигун | 24 |
| `/engine-filters` | 200 | (subcategory) | + |
| `/brakes` | 200 | Гальмівна система | 24 |
| `/suspension` | 200 | Підвіска | 24 |
| `/electrics` | 200 | Електрика | 24 |
| `/transmission` | 200 | Трансмісія | 24 |
| `/fluids` | 200 | Рідини | 24 |
| `/body` | 200 | Кузов | 24 |

### Detail pages
| URL pattern | Example tested | Status |
|---|---|---|
| `/{product-slug}` | `/filtr-olyvnyy-mann-w-71293-chery-geely-12781` | 200 ✅ |
| `/brand/{slug}` | `/brand/bosch` | 200 ✅ |
| `/blog/{slug}` | `/blog/yak-vybraty-olyvnyy-filtr-kytayska-marka` | 200 ✅ |

### Info pages (root URL pattern)
| URL | Status |
|---|---|
| `/privacy` | 200 — «Політика конфіденційності» |
| `/terms` | 200 — «Умови користування» |
| `/faq` | 200 — «Часті питання» |
| `/wholesale` | 301 → redirect to /sto |
| `/loyalty` | 301 → redirect |
| `/offer` | 200 — «Публічна оферта» |
| `/careers` | 200 — «Вакансії» |
| `/certificates` | 200 — «Сертифікати» |

### User account
| URL | Status | Notes |
|---|---|---|
| `/login` | 200 | Логін форма |
| `/cart` | 200 | h1: «Кошик порожній» (без товарів) |
| `/wishlist` | 200 | h1: «Обране» |
| `/checkout` | 🔁 302 | Redirect to login/cart |
| `/kabinet` | 🔁 302 | Auth-gated |
| `/garazh` | 🔁 302 | Auth-gated · module `gazu_garage` ON |

### Search
| URL | Status |
|---|---|
| `/search?q=oil` | 200 · h1: «Пошук: oil» |
| `/search?q=байдек` | 200 — Юнікод працює ✅ |

### System
| URL | Status |
|---|---|
| `/sitemap.xml` | 200 · 702 bytes |
| `/robots.txt` | 200 |
| `/favicon.ico` | 200 |
| `/favicon.png` | 200 |
| `/api/np-webhook` | 200 (POST only — 405 for GET, route exists ✅) |

## ✅ Admin (Filament) — 57/60

Login: `vladpowerpro@gmail.com / qaqa1234` (admin reset via tinker)

### Catalog management (all 200 ✅)
- `/admin/products` — product CRUD
- `/admin/categories` — categories tree
- `/admin/brands` — 38 brands
- `/admin/filters` — product filters
- `/admin/filter-groups`

### Sales (all 200 ✅)
- `/admin/orders`
- `/admin/payments`
- `/admin/customer-groups` (wholesale module)
- `/admin/coupons`
- `/admin/loyalty-tiers`
- `/admin/loyalty-transactions`

### Content (200 except `/admin/info-pages`)
- `/admin/blogs` ✅
- `/admin/blog-categories` ✅
- `/admin/pages` ✅
- `/admin/info-pages` ❌ **500**
- `/admin/faq-pages` ✅
- `/admin/email-templates` ✅
- `/admin/callback-requests` ✅
- `/admin/reviews` ✅

### Shipping (all 200 ✅)
- `/admin/np-shipments` (Nova Poshta)
- `/admin/np-scan-sheets`
- `/admin/np-webhook-logs`
- `/admin/np-api-logs` ❌ **404** (route не існує)
- `/admin/up-shipments` (Ukrposhta)
- `/admin/up-scan-sheets`
- `/admin/shipping-warehouses`
- `/admin/shipping-providers`
- `/admin/shipping-methods`
- `/admin/shipping-api-logs`
- `/admin/shipping-dashboard`
- `/admin/nova-poshta-settings`
- `/admin/ukr-poshta-settings`

### Inventory (multi_warehouse module — all 200 ✅)
- `/admin/merchant-warehouses`
- `/admin/inventory-transfers`
- `/admin/receiving-orders`

### Vehicle (gazu_garage + car-makes — all 200 ✅)
- `/admin/car-makes`
- `/admin/car-models`
- `/admin/user-cars`

### SEO (all 200 ✅)
- `/admin/seo-metas`
- `/admin/seo-templates`
- `/admin/seo-management`
- `/admin/seo-limits-page`
- `/admin/sitemap-settings`
- `/admin/search-management`
- `/admin/search-queries`

### System (200 except `/admin/error404-settings`)
- `/admin/modules` — 14 modules toggle ✅
- `/admin/theme-settings` — theme switch ✅
- `/admin/gazu-visual` — visual settings ✅
- `/admin/shop-settings` ✅
- `/admin/store-configuration` ✅
- `/admin/cache-management` ✅
- `/admin/cache-settings` ✅
- `/admin/integrations-page` ✅
- `/admin/payment-gateway-settings` ✅
- `/admin/error404-settings` ❌ **500**

### Tools (all 200 ✅)
- `/admin/feed-export` — Rozetka/Prom/OLX feeds
- `/admin/quick-fill` — bulk product entry
- `/admin/demo-catalog-generator` — seed UI
- `/admin/homepage-builder`
- `/admin/ai-content-generator`

## 🎨 Visual integrity check

Confirmed working through both direct (localhost:8089) and tunnel (red-phones-taste.loca.lt):

| Element | Value | Status |
|---|---|---|
| Theme CSS loaded | `themes/gazu/resources/css/gazu.css` (133 KB) | ✅ HTTP 200 |
| Tailwind utility | `bg-red-500` → `oklch(0.637 0.237 25.331)` | ✅ |
| H1 font | Space Grotesk · 52px · weight 600 | ✅ |
| Body bg | `rgb(251, 250, 247)` — `--gazu-paper` | ✅ |
| Body font | Inter Tight | ✅ |
| Debugbar in HTML | 0 mentions | ✅ (disabled) |
| sf-dump leaks | 0 | ✅ |
| Document ready | `complete` | ✅ |
| Mixed-content via HTTPS tunnel | No | ✅ |

## 🔥 Errors fixed during audit

| What | Cause | Fix |
|---|---|---|
| 0 car_makes/0 settings/0 modules after first start | Local DB was empty seed-data | Imported prod dump via mcp__coolify + ssh root@23.88.115.55 |
| HTML had 470KB of debug code | `APP_DEBUG=true` + debugbar enabled | Set `APP_DEBUG=false` + `DEBUGBAR_ENABLED=false` in compose, force-recreated container |
| `/garazh` returned 404 | `gazu_garage` module disabled after prod-import wiped modules table | `php artisan preset:apply auto-parts` |
| Tunnel served wrong CSS hash | Cached `routes-v7.php` root-owned | `sudo rm bootstrap/cache/routes*` |
| `ChineseAutoPartsSeeder` truncateDemo FK error | Didn't know about `inventory_transfer_items` (added later by multi_warehouse module) | Patched `truncateDemo()` to dynamically drop FK dependents via `Schema::hasTable()`/`hasColumn()` |

## 🎯 Recommended action items

| Priority | Item | Effort |
|---|---|---|
| **P1** | Fix `InfoPageResource` table column closure `$s` unresolvable | 15 min |
| **P1** | Fix `Error404Settings` page: form actions null | 15 min |
| **P2** | Remove or restore `NpApiLogResource` (404 route) | 10 min |
| **P3** | Add automatic `module:list` sync to `preset:apply` so modules table doesn't get wiped on dump import | 20 min |
| **P4** | Add visual screenshot tests (Playwright) for regression detection | several hours |

## Reference

- HTTP smoke commands: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8089/...`
- Admin requires login: `vladpowerpro@gmail.com / qaqa1234`
- Production source-of-truth: `https://gazu.uno`
- Tunnel URL: `https://red-phones-taste.loca.lt`
