# Multi-Warehouse Scaling Plan

> **Мета:** магазин SimpleShop має масштабуватись на кілька власних складів (Київ, Львів, Одеса, drop-shipping партнери) із незалежним обліком запасів, призначенням замовлень на склади, окремими sender-настройками для кожної доставки та багатоскладовим UI.

---

## 1. Поточний стан (як є)

### Що вже є

| Компонент | Стан |
|---|---|
| `products.quantity` (single column) | Один лічильник на товар без розрізнення per-warehouse |
| `products.stock_status` (in_stock / out_of_stock / preorder) | Глобальний прапорець |
| `min_quantity`, `wholesale_min_quantity` | Лімити на товар, не на склад |
| `ShippingWarehouse` model | **Це НЕ власні склади** — це cache відділень НП/УП (carrier branches) |
| `np_sender_warehouse_ref` (DisplaySetting) | Один hardcoded sender для всіх НП-TTN |
| `up_sender_uuid`, `up_sender_address_uuid` (DisplaySetting) | Один hardcoded sender для УП |
| `LowStockAlertMail` + `CheckLowStock` | Один email, дивиться лише на products.quantity |
| Замовлення → продукти | `order_products` без `warehouse_id` |

### Що принципово відсутнє

1. **Власна модель «склад магазину»** (MerchantWarehouse) — окрема від ShippingWarehouse (carrier-branch) сутність з адресою, відповідальним менеджером, NP/UP-конфігурацією sender'а.
2. **Інвентар per warehouse** — pivot таблиця `inventory` (product_id, warehouse_id, qty, reserved_qty, reorder_point).
3. **Stock movements / журнал руху** — income (приход), transfer (між складами), reserve (резерв), release (зняття резерву), ship (відвантажено), adjustment (інвентаризація).
4. **Резерви запасів** — як тільки товар у кошику клієнта, qty має блокуватись (з TTL).
5. **Призначення замовлення на склад** — `orders.warehouse_id` + логіка fulfillment-router'а: closest, cheapest, with-stock, manual-override.
6. **Per-warehouse sender ref для НП/УП** — кожен склад → свій sender_ref + sender_city_ref + sender_warehouse_ref. TTN-creator має брати з warehouse, не з global DisplaySetting.
7. **Multi-warehouse UI на frontend** — позначка «в наявності у Києві / Львові», geo-detection користувача → показувати найближчий склад.
8. **Фільтрація/сортинг каталога per warehouse** — «показувати тільки те, що є на київському складі».
9. **Picking-list / Pack-slip** — друкований документ для warehouse-staff: що зібрати, куди надіслати.
10. **Inter-warehouse transfers** — переміщення запасів між складами + логіка статусів (in_transit → received).
11. **Receiving / приходування** — приймання товарів від постачальника на конкретний склад.
12. **Inventory audit / інвентаризація** — періодичний підрахунок з reconcile-операцією.
13. **Multi-warehouse low-stock alerts** — окремий поріг для кожного складу.
14. **Permissions** — менеджер складу X бачить лише замовлення зі свого складу.
15. **Reports / Dashboards** — окремий звіт per warehouse + global rollup.
16. **API для warehouse-staff (PWA / mobile)** — сканер штрих-кодів, list-mode для зборки.
17. **Drop-shipping warehouses** — псевдо-склад постачальника, без фізичної інвентаризації, з API-перевіркою.

---

## 2. Архітектура цільова (TO-BE)

### Нові моделі

```
MerchantWarehouse              — наш склад
  ├── id
  ├── code (KYIV-1, LVIV-2, DROP-AUTOPARTS)
  ├── name, type (own | drop_ship | virtual)
  ├── address, city, region, postcode, lat, lng
  ├── manager_user_id
  ├── is_active, is_default, sort_order
  ├── np_sender_ref, np_sender_city_ref, np_sender_warehouse_ref
  ├── np_sender_phone, np_contact_person_ref
  ├── up_sender_uuid, up_sender_address_uuid, up_counterparty_token
  ├── working_hours (json)
  ├── pickup_supported (boolean) — самовивіз
  └── timestamps

Inventory (pivot products↔warehouses)
  ├── id
  ├── product_id, warehouse_id
  ├── quantity (фізично є)
  ├── reserved_quantity (заброньовано)
  ├── available_quantity (computed: quantity - reserved)
  ├── reorder_point (мінімум для алерту)
  ├── reorder_quantity (скільки замовляти)
  ├── last_counted_at
  └── timestamps + index unique(product_id, warehouse_id)

StockMovement (журнал)
  ├── id
  ├── warehouse_id
  ├── product_id
  ├── type (income | reserve | release | ship | transfer_out | transfer_in | adjustment)
  ├── quantity (signed: +приход, −витрата)
  ├── reference_type / reference_id (Order, Transfer, Receiving)
  ├── user_id (хто оформив)
  ├── note
  └── created_at

InventoryTransfer (переміщення)
  ├── id, from_warehouse_id, to_warehouse_id
  ├── status (draft | sent | in_transit | received | cancelled)
  ├── tracking_number (опціонально, через НП між власними складами)
  ├── shipped_at, received_at
  ├── created_by_user_id
  └── items (transfer_items pivot з product_id, qty)

ReceivingOrder (приходування від постачальника)
  ├── id, warehouse_id, supplier_id (опц.), status
  ├── invoice_number, invoice_date
  ├── items (receiving_items: product_id, qty, cost_price)
  └── timestamps
```

### Зміни в існуючих моделях

```
products
  − DROP quantity (лишити для legacy migration, потім remove)
  + computed total_quantity = SUM(inventory.available_quantity)

orders
  + warehouse_id (nullable, призначається router'ом або вручну)
  + fulfillment_status (pending | reserved | picking | packed | shipped)

order_products
  + warehouse_id (на випадок split-shipment з кількох складів)
  + reservation_id (FK до stock_movements з type=reserve)
```

### Нові сервіси

```
App\Services\Warehouse\
  ├── InventoryService           — get/set qty, reserve, release, adjust
  ├── FulfillmentRouter          — який склад призначити замовленню
  ├── TransferService             — створення/підтвердження transfer
  ├── ReceivingService           — приходування від постачальника
  ├── StockReservationService    — TTL-резерв при cart/checkout
  └── LowStockAlertService       — per-warehouse сповіщення

App\Services\Shipping\
  ├── NovaPoshtaTtnCreator       — приймає Warehouse параметром
  └── UkrPoshtaTtnCreator        — те саме
```

### Жорсткі інваріанти (тестувати)

1. `Inventory.quantity ≥ reserved_quantity` завжди.
2. Сума `StockMovement.quantity` за warehouse+product = `Inventory.quantity`.
3. Order у статусі `reserved` має N `StockMovement` записів `type=reserve`.
4. Order у `shipped` має N `type=ship` записів і їх сума == orderProducts.quantity.
5. Transfer у `received` має парні записи `transfer_out` + `transfer_in` на однакову qty.

---

## 3. Phased план імплементації

### Phase 1 — Foundation (foundation, 2-3 дні)

**Migrations + Models**
- `merchant_warehouses` table (з полями вище)
- `inventory` pivot
- `stock_movements` table
- Перенести existing `products.quantity` у `inventory` для default warehouse (data migration)
- FK: `orders.warehouse_id`, `order_products.warehouse_id`

**Services (skeleton, без бізнес-логіки)**
- `InventoryService::add()`, `subtract()`, `reserve()`, `release()`
- `FulfillmentRouter::route(Order $order): MerchantWarehouse`

**Filament admin**
- `MerchantWarehouseResource` — CRUD складів
- `Inventory` relation manager на ProductResource (per-warehouse stock editor)

**Тести**
- Pest для invariants 1-2
- Migration test (проста перевірка переносу даних)

### Phase 2 — Order routing + reservations (3 дні)

- При `createOrder()` → `FulfillmentRouter` обирає склад
- При cart-update → `StockReservationService::reserve()` з TTL=30хв
- При checkout-success → перетворити reservation → ship-movement
- При cart-abandonment / TTL → release
- Admin може вручну перевизначити warehouse у Order edit

**Тести invariants 3, 4**

### Phase 3 — NP/UP per-warehouse senders (1 день)

- Видалити global `DisplaySetting('np_sender_*')`, перенести у `merchant_warehouses.np_sender_*`
- `NovaPoshtaTtnCreator` приймає `MerchantWarehouse` (резолвить через `Order::warehouse`)
- Те саме для UkrPoshtaTtnCreator
- Migration: створити default warehouse з поточними settings → preserve existing TTNs

### Phase 4 — Transfers + Receiving (2 дні)

- `InventoryTransfer` UI + workflow (draft→sent→received)
- `ReceivingOrder` UI + auto-update inventory
- Audit log + reports

**Тести invariant 5**

### Phase 5 — Frontend (2 дні)

- Product page: «Наявно: Київ ✓ / Львів ✗ / Одеса 3 шт.»
- Catalog filter «тільки в наявності у [my city]»
- Geo-detect → запропонувати найближчий склад
- Самовивіз: список доступних warehouse → adresa в order

### Phase 6 — Operations & Reports (2 дні)

- Dashboard widgets per warehouse
- Picking-list PDF/print
- Pack-slip QR
- Low-stock alerts per warehouse
- Inventory audit screen + reconcile workflow
- Permissions: warehouse-manager role + scoping

### Phase 7 — Optional / Future

- PWA для warehouse staff (scanner integration)
- Drop-ship integration (1С, постачальницькі API)
- Multi-tier warehouses (regional → local)
- Reservations queue (waitlist коли немає в наявності)

---

## 4. Чого зараз не вистачає (Gap List)

Швидкий чеклист — що треба додати, щоб multi-warehouse запрацював **взагалі**:

### Database
- [ ] `merchant_warehouses` таблиця
- [ ] `inventory` (product↔warehouse pivot)
- [ ] `stock_movements` журнал
- [ ] `inventory_transfers` + items
- [ ] `receiving_orders` + items
- [ ] `orders.warehouse_id`
- [ ] `order_products.warehouse_id`
- [ ] `users.warehouse_id` (для warehouse-manager)
- [ ] Data migration: `products.quantity` → `inventory[default_warehouse]`

### Models
- [ ] `MerchantWarehouse`
- [ ] `Inventory`
- [ ] `StockMovement`
- [ ] `InventoryTransfer` + `InventoryTransferItem`
- [ ] `ReceivingOrder` + `ReceivingItem`
- [ ] Update `Product` — relations to inventory
- [ ] Update `Order` — relation to warehouse + auto-route

### Services
- [ ] `InventoryService` (add/sub/reserve/release/adjust)
- [ ] `StockReservationService` (TTL-резерви)
- [ ] `FulfillmentRouter` (4+ стратегії)
- [ ] `TransferService`
- [ ] `ReceivingService`
- [ ] `LowStockAlertService` (per warehouse)
- [ ] Refactor `NovaPoshtaTtnCreator` — sender per warehouse
- [ ] Refactor `UkrPoshtaTtnCreator` — sender per warehouse
- [ ] Refactor `CheckoutComponent::saveOrder()` — викликати reservation
- [ ] Refactor `Order` lifecycle hooks (paid → ship-movement)

### Admin (Filament)
- [ ] `MerchantWarehouseResource` CRUD
- [ ] `Inventory` Filament Relation Manager на Product (qty editor)
- [ ] `InventoryTransferResource`
- [ ] `ReceivingOrderResource`
- [ ] `StockMovement` viewer (read-only audit)
- [ ] Update `OrderResource` — Select поля warehouse + manual override action
- [ ] Multi-warehouse low-stock report widget
- [ ] Picking-list bulk action (select N orders → print picking list)
- [ ] Pack-slip print
- [ ] Roles + permissions (warehouse_manager scoping)
- [ ] Dashboard widgets — stock-by-warehouse, pending-fulfillment

### Frontend (storefront)
- [ ] Product page — список наявності per warehouse
- [ ] «В наявності у моєму місті» badge
- [ ] Geo-detect (IP/geolocation API)
- [ ] Catalog filter «тільки на найближчому складі»
- [ ] Самовивіз — UI вибору warehouse (з мапою)
- [ ] Cart — показати warehouse-routing breakdown
- [ ] Estimated delivery per warehouse (різний час)

### Integrations
- [ ] НП — sender per warehouse (наявний creator повністю переписати)
- [ ] УП — sender per warehouse
- [ ] (опц.) 1С / API постачальників (Drop-shipping warehouses)
- [ ] (опц.) Облік — bidirectional sync з бухгалтерією
- [ ] (опц.) RFID/barcode scanners для Warehouse Mobile App

### Tests
- [ ] Pest invariants 1-5
- [ ] Migration roundtrip test
- [ ] FulfillmentRouter strategies (closest / cheapest / has-stock / manual)
- [ ] Reservation TTL test
- [ ] Concurrent order race-condition (2 покупці беруть останній товар)
- [ ] Transfer happy path + cancellation

### Ops / Docs
- [ ] User guide для warehouse-manager
- [ ] Cron schedule: release expired reservations
- [ ] Backup-policy для inventory snapshots
- [ ] Audit-log retention rules
- [ ] Setup guide для нового магазину з 0 → N складів

---

## 5. Mini decision log (відкриті питання)

| Питання | Варіанти | Рекомендація |
|---|---|---|
| Як реагувати на out-of-stock у кошику? | (a) видалити; (b) пропонувати альтернативу зі складу; (c) переключити на drop-ship | (b) автоматично, з fallback на (c) |
| Split shipment (1 order — 2 склади)? | (a) забороняти; (b) дозволяти + розділяти TTN | (b), але опційно (admin-config) |
| Coupling реєстру НП-TTN зі складом? | окремий журнал per warehouse vs global | per warehouse — для аудиту |
| Геолокація користувача | (a) IP → city; (b) browser geolocation; (c) ручний вибір | (a) як hint + (c) override |
| Drop-ship as «virtual warehouse» | вірт. склад без фізичних qty + API-stock check | так, окремий type='drop_ship' |
| Reservation TTL | 30 хв / 1 год / customizable | 30 хв default + admin-setting |

---

## 6. Estimated effort

| Phase | Size | Complexity | Dependencies |
|---|---|---|---|
| 1. Foundation | 2-3 дні | medium | — |
| 2. Order routing + reservations | 3 дні | high (concurrency) | 1 |
| 3. NP/UP per-warehouse senders | 1 день | low | 1 |
| 4. Transfers + Receiving | 2 дні | medium | 1 |
| 5. Frontend | 2 дні | medium | 2 |
| 6. Ops & Reports | 2 дні | medium | 2 |
| **Total MVP** | **12-13 днів** | | |
| 7. Future (PWA, drop-ship) | 5+ днів | high | All |

---

## 7. Acceptance criteria (Definition of Done для MVP)

1. ✅ Адмін може створити N власних складів з адресою і своїм НП-sender'ом.
2. ✅ Інвентар ведеться окремо per warehouse; одна `Edit Product` сторінка показує таблицю qty per warehouse.
3. ✅ При оформленні замовлення auto-router обирає склад на основі configurable стратегії.
4. ✅ Admin може вручну переназначити склад замовлення.
5. ✅ Стоки коректно резервуються при checkout і знімаються при відвантаженні (зі TTL для невдалих).
6. ✅ TTN створюється з правильними sender-ref (з призначеного складу).
7. ✅ Inter-warehouse transfer працює, відображається в журналі, оновлює інвентар обох складів.
8. ✅ Receiving від постачальника збільшує інвентар призначеного складу.
9. ✅ Frontend показує per-warehouse availability на товарній сторінці.
10. ✅ Самовивіз має список warehouse + мапу для вибору.
11. ✅ Менеджер складу X бачить лише замовлення своїх / замовлення розподілені на його склад.
12. ✅ Усі інваріанти 1-5 покриті Pest-тестами.

---

## 8. Risks & mitigations

| Ризик | Mitigation |
|---|---|
| Race condition: 2 покупці одночасно резервують останній товар | DB-level row-locking (`SELECT ... FOR UPDATE`) у InventoryService::reserve() |
| Помилка sync між Inventory та StockMovement → неузгоджений стан | Daily reconciliation job + invariant assertion test |
| Великий продуктовий каталог × N складів = повільні запити | Eager-load + composite index (product_id, warehouse_id), кеш Redis |
| Адмін випадково прив'язує global sender до неіснуючого складу | Migration script-creator для default warehouse + validation у NovaPoshtaTtnCreator |
| Існуючі замовлення без warehouse_id після deploy | Backfill migration: assign existing orders to default warehouse |
| Drop-ship постачальник падає / змінює API | Cache last-known stock + circuit breaker pattern |

---

## 9. Як це підключається до auto-parts redesign

Multi-warehouse + theme-portability на одному рівні архітектурної важливості. Обидва — підготовка core до повторного використання:

- **Multi-warehouse** = масштабування **бізнес-логіки** (один core → N магазинів × N складів)
- **Theme tokens** = масштабування **UI-шару** (один core → N брендів)

Коли обидва зроблені, новий магазин запускається як: 
```
composer create simpleshop my-autoparts
APP_THEME=auto-parts
php artisan warehouse:make "Київ-Лівобережний"
```

---

*Створено: $(date), draft for review*
