# SimpleShop — Хронологія фіч

> Швидкий каталог усіх примочок функціоналу. Кожен запис — `commit hash · короткий опис`. Для глибокої документації див. `docs/FUNCTIONALITY.md` та feature-specific файли в `docs/`.

---

## 2026-05-07 — Theme Phase 2 + onboarding wizard

### Theme system Phase 2
- **(in HEAD)** UI components library: `<x-ui.button>`, `<x-ui.card>`, `<x-ui.input>`, `<x-ui.badge>`, `<x-ui.section>` — token-driven, theme-agnostic
- **(in HEAD)** Semantic component tokens у `tokens/brutal.css` + `tokens/auto-parts.css` (button/card/input/badge/section)
- **(in HEAD)** `docs/UI-COMPONENTS.md` — повний guide з прикладами

### Onboarding
- **(in HEAD)** `php artisan shop:init` — interactive wizard для нових клонів репо: shop name, contacts, default warehouse, theme

### Storefront wired to UI components
- **(in HEAD)** Add-to-cart, Quick-order, Place-order, Hero CTA → `<x-ui.button>` (real visual diff between brutal vs auto-parts theme: black sharp 3D shadow → blue rounded soft elevation)
- **(in HEAD)** `<x-ui.product-card>` — universal product card replacing `incs.brutal-product-card` in 4 catalog blocks (hits, specials, new-products, products_grid). Theme swap now changes product cards across the entire catalog — square badges → pill, sharp buttons → rounded blue.

### Admin theme switcher
- **(in HEAD)** Filament page `/admin/theme-settings` — visual cards-grid of available themes with live mini-preview (button + badge in token colors). One-click «Активувати» swaps active theme (rewrites `@import` in app.css), then user runs `npm run build`. No CLI needed.

### UI library closure
- **(in HEAD)** `<x-ui.modal>` — token-driven, x-data Alpine binding, dispatches `open-modal`/`close-modal` events with `id` filter so multiple modals coexist
- **(in HEAD)** `<x-ui.alert>` — variant=info|success|warning|danger, optional dismissible, optional title + icon. Uses `color-mix()` for variant-tinted background
- **(in HEAD)** `docs/CLONE-NEW-SHOP.md` — full step-by-step guide for spinning up a new instance: prerequisites, 7-step quick start, integrations setup, custom theme creation, multi-warehouse setup, full operational lifecycle, file-structure roadmap, deployment, 3 cloning scenarios (single-warehouse / multi-warehouse / drop-ship)

### Multi-client architecture
- **(in HEAD)** `docs/MULTI-CLIENT-ARCHITECTURE.md` — full architecture (3 deploy models, module overlay system, theme overlay, client profiles, payment gating, file structure, readiness checklist)
- **(in HEAD)** `config/modules.php` — 11 toggleable modules with name + description + enabled flag (env-driven `MODULE_{KEY}`) + requires-chain
- **(in HEAD)** `App\Support\ModuleManager` + `module()` helper, dependency-aware (`enabled()` returns false if required dep is off)
- **(in HEAD)** `php artisan module:list/enable/disable {key*}` — env-mutating CLI; clears caches; warns about cascading dependents on disable
- **(in HEAD)** `App\Filament\Concerns\RequiresModule` trait — declarative gating for Resources/Pages. Applied to `LoyaltyTransactionResource`, `MerchantWarehouseResource`, `CouponResource` as reference (rest mechanical)
- **(in HEAD)** `App\Http\Middleware\RequiresModule` (alias `module`) — route-level gating

### Demo seed pack
- **(in HEAD)** `database/seeders/AutoPartsSeeder.php` — 7 categories + 9 brands + 19 products with realistic UA pricing + inventory rows in default warehouse. Idempotent. `php artisan db:seed --class=AutoPartsSeeder`.

### Test coverage
- **(in HEAD)** PHPUnit: 30 new tests across `TransferServiceTest`, `ReceivingServiceTest`, `OrderFulfillmentServiceTest`. Total inventory test count: **47/47 passing, 106 assertions**. Coverage:
  - State transitions (draft→sent→received, draft→received, idempotent retry)
  - Inventory effects (decrement source / increment dest / no-op cancel from draft / restore from sent)
  - Stock movement audit trail (TYPE_TRANSFER_OUT/IN/INCOME/SHIP with reference_type/_id)
  - Edge cases: empty transfer/receive, cancel-after-received, fallback warehouse, idempotent retry, missing inventory

---

## 2026-05-06 → 07 — Multi-warehouse + theme system + shipping pipeline

### Multi-warehouse architecture
- **`f3ca23ff`** Phase 4 — `InventoryTransfer` + `ReceivingOrder` UI/workflow + services
- **`333cab92`** Phase 5 — `<x-warehouse-availability>` badge на product page
- **`3b1ebe78`** PHPUnit — 17 тестів, 46 assertions для `InventoryService` інваріантів
- **`f57d1319`** Phase 3 — per-warehouse NP/UP senders (`MerchantWarehouse::np_sender_*`, `up_*`) + design tokens
- **`11c475f0`** Phase 1 — foundation: 5 міграцій, 3 моделі, `InventoryService`, Filament admin
- **`a7671a30`** План + gap-list `docs/MULTI-WAREHOUSE-PLAN.md` (371 рядків)
- **(in HEAD)** `OrderFulfillmentService::shipOrder()` — авто-списання запасу при створенні TTN (idempotent)

### Theme system
- **`51a1f940`** Auto-parts theme + `php artisan theme:use` команда + `docs/THEMES.md` + `docs/FUNCTIONALITY.md`
- **`f57d1319`** `resources/css/tokens/brutal.css` — design tokens

### Shipping pipeline (NP + UP)
- **`827ed74f`** Fix grey map after Livewire morph — винесено `np-map.js` глобально
- **`9ab0a8b6`** Persist last delivery selection across reloads (NP+UP)
- **`e3808f1c`** UP courier dispatch — повний sync checkout state
- **`7266e035`** Persist `street_ref/street_id` у `shipping_data` + fix `RecipientHouse/Flat` у TTN
- **`c35be4ab`** UP street autocomplete на frontend checkout
- **`2d8aa560`** Зелений ✓ marker для обраного NP-warehouse + auto-zoom + popup
- **`78f7abc3`** NP/UP street autocomplete у admin + fix grey map (wire:ignore)

### UI / admin polish
- **`6c65d24e`** Pretty product images у Filament tables (placeholder.svg + stacked thumbs)

---

## Earlier (pre-2026-05-06)
Див. `docs/FEATURES-LOG.md` та `git log --oneline`.

---

## Pending (next iterations)

### High value
- [ ] Phase 2 — Cart reservation TTL (потребує persistent cart layer + cron)
- [ ] Picking-list / Pack-slip PDF — для warehouse staff
- [ ] Inventory dashboard widget — низькі залишки per warehouse
- [ ] Geo-detect closest warehouse — IP→місто на product page
- [ ] Building autocomplete для NP/UP courier
- [ ] Pest/PHPUnit для UP courier flow + Transfer/Receiving services

### Medium
- [ ] `<x-ui.button>`, `<x-ui.card>`, `<x-ui.badge>` — Theme Phase 2 components
- [ ] Drop-ship warehouse type (virtual)
- [ ] CSV/Excel імпорт для bulk transfer/receiving
- [ ] Permissions: warehouse-manager role з scoping

### Long-term
- [ ] Pre-fill checkout fields з previous order
- [ ] PWA для warehouse staff (scanner integration)
- [ ] 1С / supplier API integration

---

## Active feature flags / environment

```
APP_THEME=brutal              # default; switch via `php artisan theme:use auto-parts`
SCOUT_DRIVER=null             # in tests
NP_API_KEY                     # in ShippingProvider.configuration JSON, NOT DisplaySetting
```

---

*Файл оновлюється з кожним суттєвим feature commit. Тримай актуальним.*
