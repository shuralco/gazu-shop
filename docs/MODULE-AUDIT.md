# Module System — Audit & Migration Plan

> Аудит core-артефактів + план переносу.
> Версія: 2026-05-28 — **REFACTOR ЗАВЕРШЕНО (Sprint 1-11)**

## ✅ Статус: модуляризацію завершено

**39 модулів** зареєстровано. Великий refactor (Sprint 1-11) переніс
~120 артефактів з `app/` у відповідні модулі. Фінальні метрики:

| Метрика | До | Після | Зміна |
|---|---|---|---|
| Модулів | 15 | **39** | +160% |
| Core моделей | 45 | **13** | −71% |
| Core Filament Resources | 26 | **8** | −69% |
| Core Filament Pages | 22 | **4** | −82% |
| Core Gazu Controllers | 7 | **4** | −43% |
| Core Services (top-level) | ~16 | **4** | −75% |

**Verified:** 32 модулі × disable+enable = 0 failures. Всі regression URL 200.

Інфраструктура модульної системи (працює):
- ZIP installer/exporter з compatibility matrix
- Hook API + 10 lifecycle events + Blade `@hookAction`
- CLI (`module:install/uninstall/preview/export/safe-mode`) + web safe-mode
- Transactional install + auto-backup + dry-run preview
- Dependency auto-resolver + cascade disable

### Що залишилось у core (правильно — це справді ядро)

- **Models (13):** Product, Brand, Category, Filter, FilterGroup, Order,
  OrderProduct, User, UserAddress, Module, ModuleActivityLog, DisplaySetting, ShopSettings
- **Filament Resources (8):** Brand, Category, FilterGroup, Filter, Order,
  Product, ShopSettings, User
- **Filament Pages (4):** Dashboard, ModuleSettings, ModuleDetail, ShopSettings
- **Gazu Controllers (4):** Store, Cart, Checkout, Auth
- **Services (4):** AddressService, PricingService, TransliterationService, UrlRouterService

Це checkout/auth/catalog/order — переносити не треба.

---

> Нижче — оригінальний план (історичний, виконано).

Але багато логіки що **належить конкретним фічам** ще живе в `app/`. Цей документ — план переносу.

## Аудит за категорією

### 🔴 Critical (треба перенести в найближчий sprint)

#### related_products — 6 артефактів

| Артефакт | Where | Куди |
|---|---|---|
| `FilterLanding` model | `app/Models/FilterLanding.php` | `modules/related_products/src/Models/` |
| `FilterLandingResource` | `app/Filament/Resources/FilterLandingResource.php` | `modules/related_products/src/Filament/Resources/` |
| `ProductOption` model | `app/Models/ProductOption.php` | `modules/related_products/src/Models/` |
| `ProductOptionValue` model | `app/Models/ProductOptionValue.php` | `modules/related_products/src/Models/` |
| `ProductVariant` model | `app/Models/ProductVariant.php` | `modules/related_products/src/Models/` |
| `OptionsRelationManager`, `VariantsRelationManager` | `app/Filament/Resources/ProductResource/RelationManagers/` | `modules/related_products/...` |
| 6 migrations (product_options, _values, _variants, related_products, filter_landings, add_show_applied_filters) | `database/migrations/` | `modules/related_products/database/migrations/` |

**Чому це critical:** core моделі не повинні залежати від конкретного модуля. Якщо disabled — `ProductResource` посилається на класи що не існують (зараз gated by `module()->enabled()`, але це fragile).

**Migration script:** ~30 файлів, потрібно `composer dump-autoload` + перевірити ProductResource::getRelations() guards.

### 🟡 High-priority (нові модулі що треба створити)

#### gazu_garage (вже існує — добавити решту)

| Артефакт | Where |
|---|---|
| `CarMake`, `CarModel`, `CarEngine` models | `app/Models/` |
| `CarMakeResource`, `CarModelResource` | `app/Filament/Resources/` |
| Користувач-`UserCar` | вже в модулі |
| Migrations: create_car_makes, create_car_models, create_car_engines | `database/migrations/` |

#### payments (новий модуль)

| Артефакт | Where |
|---|---|
| `Payment`, `PaymentGatewaySettings`, `PaymentLog` models | `app/Models/` |
| `PaymentResource`, `PaymentGatewaySettingsResource` | `app/Filament/Resources/` |
| `Services/Gateways/LiqPayGateway` | `app/Services/Gateways/` |
| `Services/Gateways/WayForPayGateway` | `app/Services/Gateways/` |
| `Services/Gateways/MonobankGateway` | `app/Services/Gateways/` |
| `PaymentService` | `app/Services/` |

#### shipping_core (новий модуль — основа для НП/УП)

| Артефакт | Where |
|---|---|
| `Shipment` model | `app/Models/` |
| `ShippingApiLog` model | `app/Models/` |
| `ShippingMethod`, `ShippingProvider`, `ShippingRate`, `ShippingZone` models | `app/Models/` |
| `ShippingWarehouse` model | `app/Models/` |
| `TrackingUpdate` model | `app/Models/` |
| `ShippingMethodResource`, `ShippingProviderResource`, `ShippingWarehouseResource` | `app/Filament/Resources/` |
| `Services/Shipping/OrderShipmentSplitter` | `app/Services/Shipping/` |
| `Services/Shipping/ShippingOrchestrator` | `app/Services/Shipping/` |
| `Services/Shipping/CityPostcodesMap` | `app/Services/Shipping/` |
| `ShipmentNotificationObserver` | `app/Observers/` |

### 🟢 Medium-priority (потенційні нові модулі)

#### blog
- `Blog`, `BlogCategory` models
- `BlogResource`, `BlogCategoryResource`
- Routes + views

#### faq
- `FaqPage` model + `FaqPageResource`

#### info_pages
- `InfoPage` model + `InfoPageResource`

#### cms_pages
- `Page` model + `PageResource`

#### seo
- `SeoMeta` model + `SeoMetaResource`
- 4 Pages: SeoManagement, SeoTemplates, SeoLimitsPage, SitemapSettings
- `Services/SeoMetaGenerator`
- `SearchQuery` model + `SearchQueryResource`
- `ClearSeoCommand`, `GenerateSeoCommand`, `RefreshSeoCommand`

#### email_templates
- `EmailTemplate` model + `EmailTemplateResource`

#### ai_content
- `AiGenerationLog` model
- `AiContentGeneratorPage` Filament page
- `Services/AiContentGenerator`, `Services/DelengineProvider`

#### homepage_builder
- `HomepageModule` model
- `HomepageBuilder` Filament page
- `MegaMenuEditor` Filament page
- `Services/Gazu/MegaMenuBuilder`

#### wishlist
- `Wishlist` model
- `Services/WishlistService`
- Routes `/api/wishlist/*`, `/wishlist`

#### stock_notifications
- `StockNotification` model
- Controller for `/api/stock-notify`

#### search
- `Services/SearchService`, `Services/LemmatizationService`
- `SearchManagement` page
- `SearchIndex`, `SetupMeilisearch`, `GenerateSearchTags` commands

#### callback
- `CallbackRequest` model + `CallbackRequestResource`
- `Services/CallbackController`

#### theme_settings
- `ThemeSettings` page
- `GazuVisualSettings` page
- `Services/HeaderService`

#### cache_manager
- `CacheManagement`, `CacheSettings` pages
- `Services/CacheOptimizationService`
- `Services/CacheService`

#### integrations
- `IntegrationsPage`, `IntegrationConfigPage`

#### fiscal_checkbox
- `Services/Checkbox/CheckboxService`
- `CheckboxOpenShift`, `CheckboxCloseShift` commands

#### image_optimization
- `Media` model
- `Services/TinyPng/TinyPngService`
- `OptimizeImages` command

### 🔵 Low-priority (системні — залишити в core)

| Артефакт | Why core |
|---|---|
| `Product`, `Category`, `Brand`, `Order`, `OrderProduct` | Базові моделі shop |
| `Filter`, `FilterGroup` | Каталог core |
| `User`, `UserAddress` | Auth core |
| `Module`, `ModuleActivityLog` | Сама модульна система |
| `DisplaySetting`, `ShopSettings` | Глобальні налаштування |
| `ProductObserver`, `CategoryObserver`, `BrandObserver` | Cache invalidation для core моделей |
| `OrderObserver`, `OrderNotificationObserver` | Core checkout flow |
| `ResponseCacheObserver` | Cache infrastructure |
| `ModuleObserver` | Модулярна система |
| `Services/Cart/*`, `Services/Pricing/*` | Core checkout |
| `Services/UrlRouterService` | Core routing |

## Дорожна карта (рекомендований порядок)

### Sprint 1: related_products complete (3-5 дн)
- [ ] Перенести 4 моделі (`FilterLanding`, `ProductOption`, `ProductOptionValue`, `ProductVariant`)
- [ ] Перенести 6 migrations
- [ ] Перенести `FilterLandingResource`
- [ ] Перенести `OptionsRelationManager`, `VariantsRelationManager`
- [ ] composer dump-autoload + regression test
- [ ] Перевірити module disable → FilterLanding/Resource зникає з UI

### Sprint 2: gazu_garage complete (2-3 дн)
- [ ] Перенести `CarMake`, `CarModel`, `CarEngine`
- [ ] Перенести Resources
- [ ] Перенести migrations
- [ ] Перевірити compatibility-check на car-selector

### Sprint 3: payments модуль (1 тиждень)
- [ ] Створити `modules/payments/`
- [ ] Перенести моделі, Resources, gateways
- [ ] Routes payment-callback
- [ ] Test з LiqPay sandbox

### Sprint 4: shipping_core (1 тиждень)
- [ ] Створити `modules/shipping_core/`
- [ ] novaposhta + ukrposhta + meest_express + rozetka_delivery → залежать від нього
- [ ] requires_modules: ["shipping_core"]

### Sprint 5-N: Решта (за пріоритетом)

## Метрики готовності

| Категорія | До | Після всіх sprint-ів |
|---|---|---|
| Модулів | 15 | ~25 |
| Active enabled | 12 | ~12-15 (залежить від shop) |
| Core моделі | 45 | ~15-20 |
| Core Filament Resources | 26 | ~5 (Product, Category, Brand, Order, User) |
| Core Filament Pages | 22 | ~4 (Dashboard, ModuleSettings, ModuleDetail, ShopSettings) |
| Core Services | ~30 | ~10 (Cart, Pricing, UrlRouter, ...) |

## Risks

- **Cascade failures:** при переносі моделі ProductOption — будь-який код що `use App\Models\ProductOption` падає (composer classmap зрозумінтий, але cache може бути stale).
- **Migration ordering:** якщо `create_product_options_table` перенесено у `modules/related_products/...` — `Artisan::call('migrate')` без `--path` НЕ запустить його (бо не в default path). Solution: ModuleDiscovery::bootModuleResources вже додає `$app->make('migrator')->path($migDir)`, але при свіжій установці треба `migrate --force` після composer dump.
- **Filament navigation:** Resource перенесений → cache застарілий → 404 на старому URL. Solution: `php artisan filament:cache-components` після переносу.

## Workflow для одного модуля

```bash
# 1. Створити структуру
mkdir -p modules/payments/{src/{Models,Filament/Resources,Services/Gateways},database/migrations,routes,resources/views}

# 2. Перенести через git mv (зберігає історію)
git mv app/Models/Payment.php modules/payments/src/Models/Payment.php
git mv app/Models/PaymentGatewaySettings.php modules/payments/src/Models/PaymentGatewaySettings.php
git mv app/Services/PaymentService.php modules/payments/src/Services/PaymentService.php
git mv app/Services/Gateways/LiqPayGateway.php modules/payments/src/Services/Gateways/LiqPayGateway.php
git mv app/Filament/Resources/PaymentResource.php modules/payments/src/Filament/Resources/PaymentResource.php
git mv database/migrations/*_create_payments_table.php modules/payments/database/migrations/

# 3. Створити module.json
cat > modules/payments/module.json <<'EOF'
{...}
EOF

# 4. Створити ServiceProvider
cat > modules/payments/src/PaymentsServiceProvider.php <<'EOF'
<?php
namespace Modules\Payments;
use Illuminate\Support\ServiceProvider;
class PaymentsServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {}
}
EOF

# 5. Регенерація
composer dump-autoload -q
php artisan config:clear
php artisan view:clear
php artisan filament:cache-components

# 6. Перевірка
php artisan module:list  # payments має з'явитись
curl -I http://localhost:8089/admin/payments  # 302 (auth) — OK

# 7. Якщо все ОК — commit
git add modules/payments
git commit -m "refactor(payments): витягти у модуль"
```

## Зворотна сумісність

Після переносу namespace **залишається `App\Models\Payment`** (через composer classmap у `modules/`). Тому будь-який код що `use App\Models\Payment` продовжує працювати без змін.

Це **єдиний інваріант** який не можна порушувати — інакше десятки call-sites потребують grep+replace.

## Чим я можу допомогти зараз

Загрузи цей файл і скажи:
- «Спробуй sprint 1 (related_products)» — я зроблю переніс related_products complete
- «Документуй ще одну категорію» — наприклад payments roadmap detail
- «Перевір, чи якийсь reference broken після переносу» — regression scan
