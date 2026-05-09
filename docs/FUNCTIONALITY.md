# SimpleShop — Огляд функціоналу

> Швидкий навігатор по можливостях системи. Для глибших деталей дивись посилання у кожному розділі.

---

## 1. Каталог та товари

| Можливість | Деталі | Файл |
|---|---|---|
| Товари + категорії + бренди | Translatable (uk/en), Spatie | `app/Models/{Product,Category,Brand}.php` |
| Варіанти товару | Розмір, колір, з власним SKU | `ProductResource/RelationManagers/VariantsRelationManager` |
| Фільтри/групи фільтрів | Динамічні характеристики | `app/Models/Filter*.php` |
| SEO-meta | Per-сторінка, sitemap, hreflang | `app/Traits/HasSeoMeta.php` |
| Пошук Meilisearch | Lemmatization для UA | `app/Services/LemmatizationService.php` |
| Reviews | Модерація з адмінки | `app/Models/Review.php` |
| Recently viewed | Per-session | `app/Livewire/Product/RecentlyViewedComponent` |
| Wholesale ціни | Customer-group-based | `app/Models/CustomerGroup.php`, `GroupPrice` |

---

## 2. Multi-warehouse

Фази 1, 3, 4, 5 виконано. Деталі: `docs/MULTI-WAREHOUSE-PLAN.md` + `docs/INVENTORY-LOGIC.md`.

### Storage layer
| Можливість | Стан | Файл |
|---|---|---|
| Власні склади (MerchantWarehouse) | ✅ | `app/Models/MerchantWarehouse.php` |
| Per-warehouse інвентар (Inventory pivot) | ✅ | `app/Models/Inventory.php` |
| Append-only audit log руху запасів | ✅ | `app/Models/StockMovement.php` |

### Services
| Сервіс | Призначення | Файл |
|---|---|---|
| `InventoryService` | add/subtract/reserve/release/ship/move/adjust з row-locking | `app/Services/Warehouse/InventoryService.php` |
| `TransferService` | стейт-машина переміщень draft→sent→received | `app/Services/Warehouse/TransferService.php` |
| `ReceivingService` | приходування від постачальника draft→received | `app/Services/Warehouse/ReceivingService.php` |
| `OrderFulfillmentService` | автосписання запасу при створенні TTN (idempotent) | `app/Services/Warehouse/OrderFulfillmentService.php` |

### Workflow & integrations
| Подія | Що відбувається |
|---|---|
| Адмін створює `ReceivingOrder` + `Прийняти` | `+inventory @{warehouse}` (income movement) |
| Адмін створює `InventoryTransfer` + `Відправити` | `−inventory @{from}` (transfer_out) |
| Адмін `Прийняти` отриманий transfer | `+inventory @{to}` (transfer_in) |
| Адмін `Скасувати` відправлений transfer | `+inventory @{from}` (revert) |
| TTN створено через NP/UP | `OrderFulfillmentService::shipOrder()` → `−inventory @{order.warehouse}` (ship) |
| Інвентаризація через ProductResource Action | `set quantity = N` (adjustment delta) |

### Admin UIs
| Resource | Path |
|---|---|
| MerchantWarehouseResource | `/admin/merchant-warehouses` |
| InventoryRelationManager | `/admin/products/{id}/edit` → tab «Інвентар» |
| InventoryTransferResource | `/admin/inventory-transfers` |
| ReceivingOrderResource | `/admin/receiving-orders` |

### Frontend
| Компонент | Файл |
|---|---|
| Per-warehouse availability badge | `resources/views/components/warehouse-availability.blade.php` |

### Per-warehouse senders (NP/UP)
| Поле на MerchantWarehouse | Призначення |
|---|---|
| `np_sender_ref`, `np_sender_city_ref`, `np_sender_warehouse_ref`, `np_contact_person_ref`, `np_sender_phone` | Для NP TTN creation |
| `up_sender_uuid`, `up_sender_address_uuid`, `up_counterparty_token`, `up_ecom_bearer` | Для UP eCom TTN creation |

`NovaPoshtaTtnCreator::resolveSender()` + `UkrPoshtaEcomService::forWarehouse()` беруть credentials з warehouse замість глобального DisplaySetting.

### Pending
| Можливість | Стан |
|---|---|
| Cart reservation з TTL | ⏳ Phase 2 (потребує persistent cart layer) |
| Picking-list / Pack-slip PDF | ⏳ Phase 6 |
| Geo-detect closest warehouse | ⏳ Phase 5 advanced |
| Multi-warehouse dashboard widget | ⏳ Phase 6 |
| Pest tests for Transfer/Receiving | ⏳ |
| Permissions: warehouse-manager scoping | ⏳ Phase 6 |

**Інваріанти** (покриті 17 PHPUnit-тестами):
1. `quantity ≥ 0`
2. `reserved_quantity ≥ 0` і `≤ quantity`
3. `available = quantity − reserved` (accessor)
4. `SUM(stock_movements.quantity) == inventory.quantity`
5. `SUM(stock_movements.reserved_delta) == inventory.reserved_quantity`

---

## 3. Доставка

### Нова Пошта

| Можливість | Файл |
|---|---|
| API клієнт + логування | `app/Services/NovaPoshtaApiService.php` |
| Cities/Warehouses cache | `NpCity`, `NpWarehouse` + sync command |
| Selector компонент (frontend) | `app/Livewire/Shipping/NovaPoshtaSelector.php` |
| Map view з marker-clusters (Leaflet) | view: `nova-poshta-selector.blade.php` |
| Selected warehouse green pin + auto-zoom | (новий) `views/livewire/shipping/nova-poshta-selector.blade.php:170-260` |
| Street autocomplete (admin + frontend) | `AddressGeneral.getStreet` API |
| TTN creator (з 2-кроковим payload) | `app/Services/Shipping/NovaPoshtaTtnCreator.php` |
| ResolveSender з warehouse | `NovaPoshtaTtnCreator::resolveSender()` |
| Webhook listener | `app/Http/Controllers/NpWebhookController.php` |
| Scan sheets (реєстри) | `app/Services/Shipping/NpScanSheet*.php` |
| Admin: NpShipmentResource + Pages | `app/Filament/Resources/NpShipmentResource.php` |

### УкрПошта

| Можливість | Файл |
|---|---|
| AddressClassifier (public API) | `app/Services/UkrPoshtaApiService.php` |
| eCom клієнт (Bearer + ?token) | `app/Services/UkrPoshtaEcomService.php` |
| Selector компонент | `app/Livewire/Shipping/UkrPoshtaSelector.php` |
| Street autocomplete (frontend + admin) | (новий) — STREET_ID збережено |
| 3-step TTN flow (address → client → shipment) | `app/Services/Shipping/UkrPoshtaTtnCreator.php` |
| Per-warehouse sender (forWarehouse()) | `UkrPoshtaEcomService::forWarehouse()` |
| Local cache: cities, regions, post offices | `up:sync-references` command |
| Admin: UpShipmentResource | `app/Filament/Resources/UpShipmentResource.php` |

### Інші перевізники
- Rozetka Delivery — `app/Services/Shipping/RozetkaDeliveryProvider.php`
- Meest Express — `app/Services/Shipping/MeestExpressProvider.php`
- Самовивіз / per-warehouse pickup

### Спільне
- Уніфікований лог `shipping_api_logs` (NP+UP+інші) — `app/Models/ShippingApiLog.php`
- Health-check widget на dashboard
- Generic `IntegrationConfigPage` для модулів
- Persisted last selection in session — `np_last_delivery`, `up_last_delivery`

---

## 4. Замовлення та checkout

| Можливість | Файл |
|---|---|
| Single-page checkout (Livewire) | `app/Livewire/Cart/CheckoutComponent.php` |
| Order + OrderProduct з warehouse_id | `app/Models/{Order,OrderProduct}.php` |
| Fulfillment status workflow | enum `pending|reserved|picking|packed|shipped` |
| Loyalty points + redemption | `app/Services/LoyaltyService.php` |
| Coupon system | `app/Models/Coupon.php` |
| Free-shipping threshold + discount % | `DisplaySetting::shipping_*` |
| Telegram-сповіщення про нове замовлення | `app/Services/TelegramService.php` |
| Email-сповіщення (новий + статус) | `app/Mail/*.php` |

### Платежі

| Provider | Адаптер |
|---|---|
| LiqPay | `app/Services/Payment/LiqPayProvider.php` |
| WayForPay | `app/Services/Payment/WayForPayProvider.php` |
| Готівка / накладений платіж / банк | gateway-less |

---

## 5. Користувачі та auth

| Можливість | Файл |
|---|---|
| Реєстрація / вхід (email + phone) | `app/Http/Controllers/Auth/*` |
| User cabinet | `app/Livewire/User/*` |
| Saved addresses | `app/Models/UserAddress.php` (Phase 6 → multi-warehouse manager scoping) |
| Loyalty tiers | `app/Models/LoyaltyTier.php`, transactions |
| Customer groups (B2B/wholesale) | `app/Models/CustomerGroup.php` |
| Manager → orders relation | `Order::user_id` |

---

## 6. Адміністрування (Filament 3)

Структура: `app/Filament/Resources/`. Всі resources перекладені на ua.

### Каталог
- ProductResource (з табами: товар, інвентар, варіанти, gallery, SEO, group prices)
- CategoryResource (translatable)
- BrandResource
- FilterResource + FilterGroupResource
- ReviewResource

### Продажі
- OrderResource (з форматованими картками + bulk TTN)
- PaymentResource
- CouponResource
- LoyaltyTransactionResource
- LoyaltyTierResource
- CustomerGroupResource
- UserResource

### Доставка та оплата
- MerchantWarehouseResource (новий!)
- ShippingProviderResource
- ShippingMethodResource
- ShippingWarehouseResource (carrier branches cache)
- NpShipmentResource + Pages
- UpShipmentResource + Pages
- NpScanSheetResource + UpScanSheetResource
- NpApiLogResource (unified shipping logs)
- PaymentGatewaySettingsResource
- IntegrationConfigPage (generic)

### Контент
- PageResource (CMS-сторінки)
- FaqPageResource
- SeoMetaResource
- MegaMenuBuilder (drag-and-drop)
- BatchEditorPage

### Дашборд віджети
- StatOverviewWidget
- TopProductsWidget
- ShippingApiHealthWidget (NP+UP unified)

---

## 7. Theme system

Деталі: `docs/THEMES.md`.

```bash
php artisan theme:use --list
php artisan theme:use auto-parts && npm run build
```

Доступно: `brutal` (default), `auto-parts` (sample).

---

## 8. Тестова інфраструктура

```bash
vendor/bin/phpunit                                # всі тести
vendor/bin/phpunit tests/Feature/Inventory/      # multi-warehouse
```

Існуючі тести:
- `tests/Feature/AuthTest.php`
- `tests/Feature/CatalogTest.php`
- `tests/Feature/CheckoutTest.php`
- `tests/Feature/ComparisonTest.php`
- `tests/Feature/FeedTest.php`
- `tests/Feature/LegalPagesTest.php`
- `tests/Feature/ProductVariantTest.php`
- `tests/Feature/UserCabinetTest.php`
- `tests/Feature/WishlistTest.php`
- `tests/Feature/Inventory/InventoryServiceTest.php` (новий, 17/17 ✅)

---

## 9. Команди (Artisan)

```bash
# Theme
php artisan theme:use {brutal|auto-parts|custom}

# Shipping caches
php artisan np:sync-cities
php artisan np:sync-warehouses
php artisan up:sync-references

# Maintenance
php artisan check:low-stock                       # Ще на products.quantity (Phase 2 → per-warehouse)
php artisan np:track-shipments                    # Періодичне оновлення статусів
```

---

## 10. Інтеграції

| Сервіс | Файл / config |
|---|---|
| Meilisearch | `config/scout.php` |
| Redis (cache, session, queue) | `config/database.php` |
| Telegram | `app/Services/TelegramService.php` + `DisplaySetting::telegram_*` |
| Google Tag Manager | `resources/views/components/seo-meta.blade.php` |
| Cookie consent | `app/Livewire/CookieConsentComponent.php` |
| Sitemap | `app/Http/Controllers/SitemapController.php` |

---

## 11. Документація

- `docs/MULTI-WAREHOUSE-PLAN.md` — план 7-фазного scaling
- `docs/INVENTORY-LOGIC.md` — domain logic, інваріанти, lifecycle
- `docs/THEMES.md` — theme system
- `docs/FUNCTIONALITY.md` — цей файл
- `docs/NOVA-POSHTA.md` — деталі НП-інтеграції
- `docs/NOVA-POSHTA-GAP-ANALYSIS.md`
- `docs/SETUP.md` — first-run setup
- `docs/TESTING-GUIDE.md`
- `docs/MEILISEARCH-SETUP.md`
- `docs/BATCH-EDITOR-ROADMAP.md`
- `docs/FEATURES-LOG.md`
- `docs/FULL-DOCUMENTATION.md`
- `docs/README.md`
