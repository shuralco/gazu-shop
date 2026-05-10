# Multi-Warehouse + Multi-Vendor Pricing

> Стан: **PRODUCTION-READY foundation** (Phase 1 + 3 + Multi-Vendor pricing).
> Phase 2 (reservations) — у роботі. Останнє оновлення: 2026-05-09.

Цей документ описує фактичний стан системи — що вже працює, як це адмінити та куди дивитись у коді. План до реалізації: див. [`MULTI-WAREHOUSE-PLAN.md`](MULTI-WAREHOUSE-PLAN.md) (історичний).

---

## 1. Що це дає бізнесу

Один товар може жити одночасно на декількох власних складах із **різною ціною та залишком на кожному**. Покупець бачить товар на product-page із селектором складу:

- Київ · 1 день · 15 шт · **3 799 ₴**
- Львів · 2 дні · 8 шт · ~~3 799 ₴~~ **3 571 ₴** (-6%)
- Дніпро · 2-3 дні · 3 шт · **3 685 ₴**
- + Показати ще N складів

Перші 3 видно одразу, решта згортаються. UI **не розкриває**, що це різні постачальники — користувач бачить просто «склад у місті».

Замовлення зберігає `warehouse_id` per лінія. На сторінці замовлення товари групуються по складах — клієнт розуміє, що частину доставлять із Києва за день, частину з Львова за 2 дні.

---

## 2. Схема БД

### `merchant_warehouses` — наші власні склади

| Колонка | Тип | Що зберігає |
|---|---|---|
| `code` (unique) | string | `MAIN-01`, `LVIV-01`, `DNIPRO-01` |
| `name` | string | «Головний склад», «Склад Львів» |
| `type` | enum | `own` / `drop_ship` / `virtual` |
| `city`, `region`, `address`, `postcode` | strings | адреса |
| `latitude`, `longitude` | decimal(10,7) | для геолокації |
| `manager_user_id` | FK users | відповідальний менеджер |
| `phone`, `email` | strings | контакти |
| `working_hours` | json | `{"пн-пт":"09:00-18:00"}` |
| `delivery_eta` | string(64) | **«1 день» / «2-3 дні»** — лейбл для селектора |
| `is_active`, `is_default` | bool | дефолтний обирається у міграції |
| `pickup_supported` | bool | чи доступний самовивіз |
| `np_sender_*`, `up_sender_*` | strings | API-токени Нової Пошти / Укрпошти **per warehouse** |

**Sender per warehouse** — це дозволяє створювати ТТН з реального місця відправлення для кожного складу окремо (Phase 3, готово).

### `inventory` — pivot product↔warehouse + ціна

| Колонка | Тип | Що зберігає |
|---|---|---|
| `product_id` | FK products | |
| `warehouse_id` | FK merchant_warehouses | |
| `quantity` | int | фізичний залишок |
| `reserved_quantity` | int | заброньовано (Phase 2) |
| `price` | decimal(12,2) **nullable** | ціна на цьому складі; NULL → fallback на `products.price` |
| `compare_at_price` | decimal(12,2) nullable | стара/акційна ціна (закреслена) |
| `reorder_point`, `reorder_quantity` | int | low-stock alert |
| `last_counted_at` | timestamp | дата останньої інвентаризації |

Унікальний індекс `(product_id, warehouse_id)`. Доступний залишок = `quantity - reserved_quantity`.

### `stock_movements` — append-only журнал

| Колонка | Що |
|---|---|
| `type` | `income` / `reserve` / `release` / `ship` / `transfer_out` / `transfer_in` / `adjustment` |
| `quantity` | signed int (приход = +, списання = −) |
| `reference_type` + `reference_id` | morphTo: Order, Transfer, Receiving |
| `user_id` | хто зробив рух |

Фактично — audit log для бухгалтерії та звірок.

### `orders.warehouse_id`, `order_products.warehouse_id`

Замовлення може мати головний склад (`orders.warehouse_id`), а конкретні позиції — свій склад (`order_products.warehouse_id`). Це дозволяє розбивати ТТН по складам автоматично.

### Міграції

```
2026_05_06_120000_create_merchant_warehouses_table.php
2026_05_06_120100_create_inventory_table.php
2026_05_06_120200_create_stock_movements_table.php
2026_05_06_120300_add_warehouse_id_to_orders_and_order_products.php
2026_05_06_120400_seed_default_merchant_warehouse.php
2026_05_06_140000_create_inventory_transfers_table.php
2026_05_06_140100_create_receiving_orders_table.php
2026_05_09_120000_add_pricing_to_inventory_and_warehouses.php
```

---

## 3. Backend — як працює

### Cart (`app/Helpers/Cart/Cart.php`)

Cart key включає warehouse_id, варіант:

```
"104"               — товар без variant і warehouse
"104_v5"            — з variant
"104_w59"           — з warehouse
"104_v5_w59"        — з обома
```

Той самий товар із 2 різних складів — **2 окремі лінії** у кошику з власною ціною. `Cart::add2Cart(productId, qty, variantId, warehouseId)` дивиться `inventory.price` і override базову ціну якщо є.

`Cart::removeProductFromCart` / `updateItemQuantity` приймають `int|string $key`:
- повний ключ `"104_w59"` → точкова операція;
- голий productId `"104"` → broadcast (всі лінії з продуктом видаляються/оновлюються) для backwards-compat.

### Frontend selector (Brutal /uk + GAZU /:8089)

**Brutal Livewire (`app/Livewire/Product/ProductComponent.php`):**

```php
public ?int $selectedWarehouseId = null;

public function getWarehouseStocksProperty()  // Inventory rows + warehouse, sorted in-stock first
public function selectWarehouse(int $id): void
private function getWarehousePrice(): ?float  // price override
```

Mount() preselects first in-stock warehouse. `getTotalPriceProperty` повертає override ціну якщо є.

**GAZU Blade (`resources/views/components/gazu/buy-panel.blade.php`):**

Alpine x-data з lookup map — змінюється warehouseId без перезавантаження:

```js
x-data="{
  q: 1,
  warehouseId: 1,
  stocks: { 1: {price, compare, qty, city, eta}, 2: {...} },
  get price() { return this.stocks[this.warehouseId].price; },
  get available() { return this.stocks[this.warehouseId].qty; }
}"
```

Form post передає `warehouse_id` поряд із `product_id` + `quantity`.

### Checkout

Brutal `app/Livewire/Cart/CheckoutComponent.php` і GAZU `app/Http/Controllers/Gazu/CheckoutController.php` парсять cart key (`104_v5_w59` → productId=104, warehouse_id=59) і пишуть `warehouse_id` у `order_products`.

### Order display (GAZU)

`resources/views/gazu/account/order-details.blade.php` групує `orderProducts` по `warehouse_id` і малює banner «📍 City · ETA» над кожною групою. Single-warehouse orders рендеряться flat.

### Filament admin

- **MerchantWarehouseResource** — CRUD складу з табами «Основне / Адреса / NP sender / UP sender». Поле `delivery_eta` на табі «Адреса».
- **ProductResource → InventoryRelationManager** — таблиця складів цього товару з полями `quantity`, `reserved`, `available`, **`price`**, **`compare_at_price`**. Action «Інвентаризація» викликає `InventoryService::adjust()` з reason → пише `stock_movement`.

---

## 4. InventoryService

`app/Services/Warehouse/InventoryService.php`. Усі операції в `DB::transaction()` з `lockForUpdate()` на `Inventory` row → race-safe.

| Метод | Призначення |
|---|---|
| `add(product, warehouse, qty, ref?, note?)` | приходування |
| `subtract(...)` | списання (guarded ≥ 0) |
| `reserve(...)` | + reserved_quantity (Phase 2 — у roadmap) |
| `release(...)` | − reserved_quantity |
| `adjust(product, warehouse, newQty, reason)` | інвентаризація |
| `move(product, fromWh, toWh, qty, transferRef)` | трансфер між складами |

Кожен метод append'ить `StockMovement` row для audit.

---

## 5. Як заведа склад у адмінці

1. **Створи склад**: `/admin/merchant-warehouses` → Create. Заповни code, name, city, **delivery_eta** («1 день»), NP/UP sender refs (Phase 3).
2. **Зайди у товар**: `/admin/products/{id}/edit` → таб «Інвентар». Натисни «Додати склад» → вибери склад → встанови qty + опц. price/compare_at_price.
3. **Готово**. На /uk product-page та /:8089 product-page одразу з'явиться селектор складу.

NULL у `inventory.price` = використовувати базову `products.price`. Зручно якщо ціна одна — заповнюй тільки `quantity`.

---

## 6. Тести

`tests/Feature/Inventory/`:
- `MerchantWarehouseTest.php` — model + crud + default flag (47 assertions)
- `InventoryServiceTest.php` — invariants (qty ≥ reserved, sum stock_movements == quantity, race condition)
- `MigrationDataIntegrityTest.php` — products.quantity preserved у inventory[default_warehouse]

Запуск: `docker compose exec app php artisan test --filter=Inventory`. Стан 2026-05-09: **47/47 тестів green, 106 assertions**.

---

## 7. Roadmap (на черзі)

| Phase | Що | Стан |
|---|---|---|
| 1 | Schema + Models + Service skeleton + Filament admin | ✅ |
| 3 | Per-warehouse NP/UP senders | ✅ |
| **+** | Multi-vendor pricing (inventory.price) + storefront selector | ✅ 2026-05-09 |
| 2 | Reservations: reserve у InventoryService на checkout, release on cancel, ship → decrement qty | ✅ 2026-05-09 |
| 5 | Split TTN: одне замовлення → N ТТН (по одному на склад) з відповідним sender | ✅ 2026-05-09 |
| 4 | Inventory transfers UI: міжсклад переміщення з approve flow | ✅ 2026-05-09 |
| 7 | Per-warehouse shipping cost: окрема ставка від кожного складу | ✅ 2026-05-10 |
| 6 | Geo-detect склад: показувати найближчий зверху селектора | 🔜 |

### Split TTN (Phase 5 — як працює)

`np_shipments.warehouse_id` + `up_shipments.warehouse_id` (FK на `merchant_warehouses`) пінять конкретну shipment до її source складу.

**`OrderShipmentSplitter`** (`app/Services/Shipping/OrderShipmentSplitter.php`):

```php
$shipments = app(OrderShipmentSplitter::class)->splitNova($order);  // або splitUkr / split($order, 'nova')
```

Метод групує `order_products` по `warehouse_id`, створює одну draft-shipment на група (status `draft`, без виклику NP API). Recipient + delivery prefs копіюються з order. Sender — резолвиться пізніше при створенні TTN.

`NovaPoshtaTtnCreator::resolveSender()` тепер дивиться:
1. `$shipment->sender_*` (explicit override)
2. `$shipment->warehouse->np_sender_*` (split-TTN)
3. `$shipment->order->warehouse->np_sender_*` (single-warehouse order)
4. `DisplaySetting` (legacy global)

**Filament UI**: на `/admin/orders` біля кожного замовлення з 2+ складами зʼявляється кнопка `[]` (rectangle-stack). Натискання → modal з вибором перевізника (НП/УП) → створює N draft-shipments. Idempotent: повторний клік не дублює — пропускає склади що вже мають shipment.

**Verified end-to-end на /gazu fork:**
```
Order #1 (3 lines from 3 warehouses):
splitNova() → 3 NpShipments created
  shipment #1 | warehouse=1 (Київ)   | weight=0.500 | cost=3450
  shipment #2 | warehouse=2 (Львів)  | weight=0.500 | cost=3209
  shipment #3 | warehouse=3 (Дніпро) | weight=0.500 | cost=3312
Re-run: 0 new shipments (idempotent)
```

### Per-warehouse shipping (Phase 7 — як працює)

`merchant_warehouses.shipping_cost` (decimal, default 0) + `merchant_warehouses.free_shipping_threshold` (decimal, nullable). Якщо в кошику зі складу набрано на ≥ threshold — доставка з нього безкоштовна.

**`ShippingCalculator`** (`app/Services/Cart/ShippingCalculator.php`):

```php
$breakdown = app(ShippingCalculator::class)->breakdown();
// Returns:
//   groups: [{warehouse, subtotal, shipping, free, items}, ...]
//   subtotal, shipping_total, grand_total
```

Cart line groupBy `warehouse_id` → per-group subtotal → free? subtotal ≥ threshold : false → shipping = free ? 0 : warehouse.shipping_cost.

**Filament admin** (`MerchantWarehouseResource` → tab «Адреса»):
- `shipping_cost` (₴, default 0)
- `free_shipping_threshold` (₴, nullable — порожньо = поріг вимкнено)

**GAZU Cart UI** (`gazu/cart/index.blade.php`):
- Окремий блок «Доставка» з рядком per warehouse: city + сума (або «безкоштовно»)
- Підказка «+ N ₴ до безкоштовної» для груп нижче threshold
- Підсумок «Разом доставка» + «До сплати» (subtotal + shipping)

**Checkout** копіює `shipping_cost` (з breakdown) у `orders.shipping_cost`. `subtotal` пишеться окремо від `total` (= subtotal + shipping_cost).

**Verified end-to-end на /gazu fork:**
```
Cart: Kharkiv 1×3622.50 + Kyiv 1×3450 = 7072.50
  → Kharkiv: ship=110 ₴ (no threshold)
  → Kyiv: subtotal 3450 ≥ 2000 → free
  → Grand: 7182.50 ₴

Cart: Kyiv 1×17250 (5×3450)
  → Kyiv: subtotal 17250 ≥ 2000 → free → grand 17250
```

### Inventory Transfers (Phase 4 — як працює)

`inventory_transfers` (з міграції `2026_05_06_140000`) + `inventory_transfer_items` зберігають заявки на міжсклад переміщення. State machine: `draft → sent → received` або `cancelled` на будь-якому етапі.

**`TransferService`** (`app/Services/Warehouse/TransferService.php`):

```php
$svc = app(TransferService::class);
$transfer = $svc->createDraft($fromWh, $toWh, userId: 1, note: '...');
$svc->addItem($transfer, $product, qty: 5);
$svc->ship($transfer, userId: 1);     // списує з $from
$svc->receive($transfer, userId: 1);  // приходує на $to
$svc->cancel($transfer, userId: 1, reason: '...');  // повертає на $from якщо було shipped
```

Кожна операція через `InventoryService::subtract/add` з `lockForUpdate()` → race-safe. Append `StockMovement` (`transfer_out` / `transfer_in`).

**Filament admin** (`app/Filament/Resources/InventoryTransferResource.php`):

- `/admin/inventory-transfers` — list з статусом-badge (Чернетка / У дорозі / Отримано / Скасовано), filters per status / from / to.
- Create: вибір from/to + items relation manager (кнопка «Додати позицію»).
- Per-row actions: ✈ Ship (visible на draft), 📥 Receive (visible на sent), ❌ Cancel (visible на draft/sent з reason form).
- Edit заблоковано після ship — поля from/to/items immutable.

**Verified end-to-end на /gazu fork:**
```
Kyiv qty=18, Lviv qty=6 (before)
→ createDraft(Kyiv→Lviv) + addItem(product, qty=5)
→ ship() → Kyiv qty=13, Lviv qty=6
→ receive() → Kyiv qty=13, Lviv qty=11
status: received
```

### Reservations flow (Phase 2 — як працює)

1. **Checkout** → `Order::create()` + `OrderProduct::create()` для кожної лінії з cart key. Якщо `warehouse_id` присутній: `InventoryService::reserve()` всередині тієї ж транзакції. На out-of-stock → `RuntimeException` → транзакція rollback'ить замовлення повністю.
2. **Order updated → status='cancelled' / 'refunded'**: `OrderObserver` слухає `updated`, викликає `InventoryService::release()` для кожної лінії з `warehouse_id`. `reserved_quantity` повертається у avail-pool.
3. **Order updated → status='shipped'**: `OrderObserver` викликає `InventoryService::ship()` — decrement БОТ `reserved_quantity` ТА `quantity`. Лінії з `warehouse_id=null` пропускаються (legacy fallback).
4. Кожна операція пише `StockMovement` (`reserve` / `release` / `ship`) для audit log.

Концурентний захист: усі мутації — `DB::transaction()` + `Inventory::lockForUpdate()`. 100 паралельних checkout'ів на 1 склад дають коректне `reserved_quantity` без overdraw.

---

## 8. Файли (швидке посилання)

```
database/migrations/2026_05_06_*               — schema
database/migrations/2026_05_09_120000_*        — inventory.price + delivery_eta
app/Models/MerchantWarehouse.php               — модель складу
app/Models/Inventory.php                       — pivot + effectivePrice() accessor
app/Models/StockMovement.php                   — audit log
app/Services/Warehouse/InventoryService.php    — операції з лок-ом
app/Filament/Resources/MerchantWarehouseResource.php
app/Filament/Resources/ProductResource/RelationManagers/InventoryRelationManager.php
app/Helpers/Cart/Cart.php                      — cart key + per-warehouse price
app/Livewire/Product/ProductComponent.php      — Brutal storefront selector
resources/views/livewire/product/product-component.blade.php
resources/views/components/gazu/buy-panel.blade.php  — GAZU selector
resources/views/gazu/account/order-details.blade.php  — order grouping by warehouse
```
