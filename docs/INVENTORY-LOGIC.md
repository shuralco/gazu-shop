# Inventory Logic — Multi-Warehouse Stock Management

> **Версія:** Phase 1 (Foundation) — впроваджено 2026-05-06.
> **Зв'язана документація:** `docs/MULTI-WAREHOUSE-PLAN.md`.

---

## 1. Концепти

| Сутність | Призначення | Файл |
|---|---|---|
| `MerchantWarehouse` | Власний склад магазину з адресою + NP/UP-sender'ом | `app/Models/MerchantWarehouse.php` |
| `Inventory` | Кількість одного товару на одному складі (pivot) | `app/Models/Inventory.php` |
| `StockMovement` | Append-only журнал усіх змін інвентарю | `app/Models/StockMovement.php` |
| `InventoryService` | **Єдина точка входу** для всіх операцій з інвентарем | `app/Services/Warehouse/InventoryService.php` |

`MerchantWarehouse` ≠ `ShippingWarehouse`. Останній — це cache відділень НП/УП (carrier branches), не наші склади.

---

## 2. Інваріанти

`InventoryService` гарантує:

1. `Inventory.quantity ≥ 0` — фізична кількість ніколи не від'ємна
2. `Inventory.reserved_quantity ≥ 0` — резерви ніколи не від'ємні
3. `Inventory.reserved_quantity ≤ Inventory.quantity` — не можна зарезервувати більше, ніж фізично є
4. `Available = Inventory.quantity − Inventory.reserved_quantity` (computed accessor)
5. `SUM(StockMovement.quantity для warehouse+product) == Inventory.quantity`
6. `SUM(StockMovement.reserved_delta для warehouse+product) == Inventory.reserved_quantity`

Будь-яка операція, що порушила б інваріант, кидає `RuntimeException` (без часткового стану — обгорнуто в `DB::transaction`).

---

## 3. Типи рухів (StockMovement.type)

| Type | Сценарій | quantity | reserved_delta |
|---|---|---|---|
| `income` | Приход від постачальника | `+N` | `0` |
| `reserve` | Користувач додав у кошик / оформив checkout | `0` | `+N` |
| `release` | Кошик закинули / TTL минув / cancel | `0` | `−N` |
| `ship` | Створено ТТН, товар фізично відвантажено | `−N` | `−N` |
| `transfer_out` | Передача на інший склад (фізичний вихід) | `−N` | `0` |
| `transfer_in` | Прихід з іншого складу | `+N` | `0` |
| `adjustment` | Інвентаризація (correction) | `±delta` | `0` |

**Причина двох колонок (`quantity` + `reserved_delta`):**
- `reserve` не торкається фізичної кількості — лише блокує резервом.
- `ship` синхронно зменшує обидві: знімає резерв і списує фізично.
- Це дозволяє simple SUM-перевірку інваріантів окремо для quantity і reserved.

---

## 4. Сервісні методи (API)

```php
$svc = app(\App\Services\Warehouse\InventoryService::class);

// === Stock-in ===
$svc->add($product, $warehouse, 50, type: 'income', reference: $receivingOrder);

// === Reserve / Release ===
$svc->reserve($product, $warehouse, 2, reference: $cart);  // блокує 2 шт
$svc->release($product, $warehouse, 2, reference: $cart);  // знімає блок

// === Ship ===
$svc->ship($product, $warehouse, 2, reference: $order);    // конвертує резерв у відвантаження

// === Manual subtract (без попереднього резерву) ===
$svc->subtract($product, $warehouse, 1, type: 'ship');     // прямий списання

// === Transfer ===
$svc->move($product, $fromWarehouse, $toWarehouse, 10, reference: $transfer);

// === Inventory audit ===
$svc->adjust($product, $warehouse, 47, reason: 'фактично нарахували');

// === Read ===
$inv = $svc->get($product->id, $warehouse->id);
echo $inv->available_quantity; // accessor
```

Усі методи:
- Беруть row-level lock (`SELECT ... FOR UPDATE`) на `Inventory` рядок
- Обгорнуті в `DB::transaction`
- Записують `StockMovement` (audit log)
- Throw `RuntimeException` якщо порушується інваріант
- Throw `InvalidArgumentException` для qty ≤ 0

---

## 5. Лайф-цикл замовлення (full picture)

```
[Cart add]
  ├─ FulfillmentRouter обирає warehouse (Phase 2)
  ├─ InventoryService::reserve(product, warehouse, qty, reference: $cart)
  │   └─ +reserved_quantity, log type=reserve
  └─ Користувач бачить «у наявності»

[Cart abandoned (TTL minute)]
  └─ Cron-job → InventoryService::release(product, warehouse, qty, reference: $cart)
      └─ −reserved_quantity, log type=release

[Checkout success]
  ├─ Order created з warehouse_id
  ├─ Reservation→Order перетворено: reference тепер $order
  └─ Status: pending → reserved (fulfillment_status)

[Order paid + TTN created (admin)]
  ├─ NpShipment created
  ├─ NovaPoshtaTtnCreator → Sender з MerchantWarehouse (не з DisplaySetting)
  ├─ InventoryService::ship(product, warehouse, qty, reference: $order)
  │   └─ −quantity AND −reserved_quantity, log type=ship
  └─ Status: reserved → shipped

[Refund / cancellation after ship]
  └─ InventoryService::add(product, warehouse, qty, type='income', reference: $order)
      (manual, через admin Action)
```

**На Phase 1 (зараз)** реалізовано лише сервіс + інфраструктура. Інтеграція з checkout/observer — Phase 2.

---

## 6. Concurrency: race-condition guarantee

Сценарій: два користувачі одночасно кладуть в кошик останню одиницю.

```
T1: BEGIN
T1: SELECT * FROM inventory WHERE product_id=42 AND warehouse_id=1 FOR UPDATE
T1: -- blocks T2 if it tries to lock the same row
T2: BEGIN
T2: SELECT * FROM inventory WHERE product_id=42 AND warehouse_id=1 FOR UPDATE
T2: -- waits for T1
T1: UPDATE reserved_quantity=quantity (last unit reserved)
T1: COMMIT
T2: -- now sees updated row
T2: -- available=0, throws RuntimeException
T2: ROLLBACK
```

Один з користувачів отримує помилку «Cannot reserve N — only 0 available», що handler у CheckoutComponent перетворює на user-friendly «На жаль, цей товар щойно купили».

---

## 7. Backfill та historical data

При запуску міграції `2026_05_06_120400_seed_default_merchant_warehouse`:
- Створюється `MerchantWarehouse` з code=`MAIN-01` і `is_default=true`
- Копіюються NP/UP sender refs з `display_settings` у відповідні поля warehouse'а
- Кожен `Product.quantity > 0` отримує `Inventory` рядок у цьому складі
- Усі існуючі `Order` та `OrderProduct` отримують `warehouse_id=MAIN-01`

**Ризик при downgrade:** `down()` НЕ видаляє warehouse + inventory. Тільки nullify-ить FK у orders. Це навмисно — щоб уникнути втрати історії руху.

---

## 8. Як додати новий склад

### Через Filament admin
1. `/admin/merchant-warehouses` → «Створити»
2. Вкладки: Основне, Адреса, NP-sender, UP-sender
3. Зберегти

### Через tinker (для seed/factory)
```php
\App\Models\MerchantWarehouse::create([
    'code' => 'KYIV-1',
    'name' => 'Київ Лівобережний',
    'type' => 'own',
    'city' => 'Київ',
    'address' => 'вул. Бажана, 30',
    'np_sender_ref' => '...',
    'np_sender_city_ref' => '...',
    'np_sender_warehouse_ref' => '...',
    'np_sender_phone' => '+380...',
    'is_active' => true,
]);
```

Після створення — Filament на ProductResource → tab «Інвентар» → Adjust qty per склад.

---

## 9. Integration points (хто читає Inventory)

Поточні (після Phase 1):
- `Product::inventoryFor($warehouse)` — accessor
- `Product::totalAvailableQuantity()` — sum across warehouses (fallback на legacy `products.quantity`)
- Filament `InventoryRelationManager` — UI
- `MerchantWarehouseResource` — admin

Майбутні (Phase 2-5):
- `CheckoutComponent::saveOrder()` → `InventoryService::reserve()`
- `OrderObserver` → `InventoryService::ship()` коли TTN створено
- Frontend `ProductComponent` → `Product::totalAvailableQuantity()` для show «in stock at X»
- `LowStockAlertService` → email per warehouse-manager
- `NovaPoshtaTtnCreator` → Sender з `MerchantWarehouse` (Phase 3 ✅)

---

## 10. Тестування

`tests/Feature/Inventory/InventoryServiceTest.php` покриває:
- `add → quantity збільшується`
- `subtract → quantity зменшується, throw якщо < reserved`
- `reserve → reserved_quantity збільшується, throw якщо available < qty`
- `release → reserved_quantity зменшується (clamped to 0)`
- `ship → обидва зменшуються синхронно`
- `move → парні transfer_out + transfer_in`
- `adjust → set absolute, log delta`
- `Concurrent reserve → один з двох throws RuntimeException`
- `Sum invariant → SUM(movements) == inventory.quantity`

Запуск:
```bash
docker compose exec app php artisan test --filter=Inventory
```

---

## 11. Roadmap (наступні фази)

Див. `docs/MULTI-WAREHOUSE-PLAN.md` секція 3.

Phase 2: Reservation lifecycle + cart→DB persistence + TTL cron
Phase 3: ✅ Per-warehouse senders (виконано разом з Phase 1)
Phase 4: InventoryTransfer + ReceivingOrder UI/workflow
Phase 5: Frontend per-warehouse availability badges
Phase 6: Reports, picking-lists, role-based scoping
