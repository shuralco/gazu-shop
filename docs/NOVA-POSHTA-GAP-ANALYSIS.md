# Nova Poshta — Gap Analysis & Roadmap

**Базис порівняння:** OpenCart-модуль `kvant-atletiko/src/.../novaposhta` (комерційний, ionCube-протектований). Має ~10 000 LOC адмін-контролера, 9 табів налаштувань, повний CRUD ТТН і реєстрів.

**Поточний стан SimpleShop:** ~1 500 LOC, 1 settings-page (4 таби), базовий TTN-flow. Працює мінімальний customer flow, але не вистачає 60-70% функціоналу для production-grade магазину з НП.

---

## 🟢 Що вже працює

- API-ключ + connection check (`/admin/nova-poshta-settings`)
- Live пошук міст (`Address.getCities`)
- Live пошук відділень (`AddressGeneral.getWarehouses`)
- Розрахунок ціни доставки (`InternetDocument.getDocumentPrice`)
- Спроба створити ТТН (`InternetDocument.save`) — skip якщо sender не налаштований
- 3 UI-режими: warehouse / poshtomat / courier
- Таблиця `np_shipments` для відстеження
- Базовий tracking (`TrackingDocument.getStatusDocuments`)
- Frontend autocomplete-flow для checkout

---

## 🔴 Чого критично не вистачає (порівняно з OpenCart)

### 1. Локальна БД довідників — **відсутня повністю**

OpenCart тримає 3 таблиці з усім імпортованим:

| Таблиця | Записів | Що дає |
|---|---|---|
| `np_regions` | 24 | області для UI-фільтрів |
| `np_cities` | ~30 000 | instant search без API; поля `Delivery1-7`, `IsBranch`, `SettlementType`, `SpecialCashCheck` |
| `np_departments` (warehouses) | ~13 000 | координати, графік, обмеження ваги/розмірів |

**Вплив:**
- Швидкість: ~150ms (live API) → ~5ms (локальний LIKE)
- Робота при падінні NP API
- Точні фільтри: "тільки відділення з POS-терміналом", "тільки ті де можна > 30 кг"

### 2. Поля відділень з API, які ми ігноруємо

З `getWarehouses` приходить великий обсяг даних. Ми зараз зберігаємо лише `Number, Description, ShortAddress`. Втрачаємо:

```
Longitude, Latitude              — координати → мапа в checkout
Phone                            — телефон відділення
TotalMaxWeightAllowed            — макс. вага посилки
PlaceMaxWeightAllowed            — макс. вага одного місця
SendingLimitationsOnDimensions   — макс розміри (L/W/H) при відправленні
ReceivingLimitationsOnDimensions — макс розміри при отриманні
Reception_monday-sunday          — графік прийому посилок
Delivery_monday-sunday           — графік видачі
Schedule_monday-sunday           — загальний графік
PostFinance, BicycleParking      — додатковий сервіс
PaymentAccess, POSTerminal       — оплата на місці
InternationalShipping            — міжнародні відправлення
SelfServiceWorkplacesCount       — самообслуговування
DistrictCode, RegionCity         — район/територія
WarehouseStatus, CategoryOfWarehouse — статус, категорія
TypeOfWarehouse                  — звичайне/поштомат/відділення-склад
```

### 3. Поля посилки (parcels) — зараз hardcoded одним об'ємом

| OpenCart дозволяє | У нас |
|---|---|
| **Кілька місць** в одному замовленні (`parcels[]`) | ❌ завжди 1 |
| Розміри (`length`, `width`, `height`) для кожного місця | ❌ Weight=0.5 hardcoded |
| **Об'ємна вага** (volumeWeight = L×W×H/4000) — NP бере MAX(weight, volumeWeight) | ❌ нема |
| Автовизначення типу упаковки | ❌ нема |
| Тип упаковки (бокс, конверт, паллета, шини, документи...) | ❌ нема |
| `tires_and_wheels` — окрема логіка для шин | ❌ нема |
| `pack_type`, `pack_general` | ❌ нема |
| `manual_processing` (немашинна обробка) | ❌ нема |
| `avia_delivery` (авіадоставка) | ❌ нема |
| Загальна вага / об'єм / об'ємна вага замовлення | ⚠️ часткові |

### 4. Розміри товарів у БД

OpenCart products: `length, width, height, weight, weight_class_id, length_class_id`.
SimpleShop products: тільки `weight`, `dimensions` (json).

**Наслідок:** не можна порахувати об'ємну вагу → ціна доставки часто **занижена** для великих/легких посилок. NP за замовчуванням стягує MAX(вага, об'ємна_вага).

### 5. ТТН-функціонал в адмінці

| OpenCart має | SimpleShop |
|---|---|
| Список накладних з 13 фільтрами (дата, статус, реєстр, тип) | базовий `/admin/np-shipments` |
| Форма створення накладної з 50+ полями | дуже базова |
| **Реєстр (ScanSheet)** — група накладних на день для здачі НП | ❌ |
| **Друк** TTN: формат A4/100x100, шаблон, кількість копій | ❌ |
| Редагування накладної | ❌ |
| Видалення накладної | ❌ |
| Присвоєння накладної замовленню | ❌ |
| Колонка `np_cn_number` в `orders` | ✅ |

### 6. Cron-завдання

| OpenCart має | SimpleShop |
|---|---|
| Auto-update довідників (regions/cities/departments) щотижня | ❌ |
| **Tracking статусів** накладних — opnodaily polling | ❌ |
| **Email-сповіщення** клієнту про зміну статусу (templates) | ❌ |
| **SMS-сповіщення** через NP gateway | ❌ |
| Окремі шаблони для admin/customer notifications | ❌ |
| Cron-ключ для безпечного виклику | ❌ |

### 7. Тарифний розрахунок (Tariffs tab)

| OpenCart | SimpleShop |
|---|---|
| API розрахунок (default) | ✅ |
| **Власні тарифи** (table вага×зона) — без API | ❌ |
| Тарифні зони: місто/область/Україна | ❌ |
| Передача у відділенні (доплата) | ❌ |
| Адресна доставка (надбавка %) | ❌ |
| **Знижка** на доставку (%/грн) | ❌ |
| **Комісія** за оголошену вартість | ❌ |
| Мін/макс сума замовлення | ⚠️ є min, нема max |
| **Безкоштовна доставка** від суми | ⚠️ налаштування є, в UI не реалізовано |
| Поріг + текст безкоштовної | ❌ |
| Геозона / податковий клас | ❌ |

### 8. Оплата і документи

| OpenCart | SimpleShop |
|---|---|
| **Платник доставки**: відправник/отримувач/третя особа | ⚠️ hardcoded "Recipient" |
| **Третя особа** (ЄДРПОУ) | ❌ |
| **Контроль оплати** (післяплата) — гроші перед видачею | ❌ |
| **Накладений платіж** (COD) | ❌ |
| Метод оплати накладним платежем | ❌ |
| **Зворотна доставка** (документи назад) | ❌ |
| Платник зворотної доставки | ❌ |

### 9. Форма checkout (frontend)

| OpenCart | SimpleShop |
|---|---|
| Адресна доставка з `region` / `settlement` / `street` / `house` / `flat` | ⚠️ courier — лише назва міста |
| Підйом на поверх / ліфт (`rise_on_floor` / `elevator`) | ❌ |
| Бажана дата і час доставки (`preferred_delivery_date/time`) | ❌ |
| ЄДРПОУ для юр.осіб | ❌ |
| Контактна особа окремо від платника | ❌ |
| Мапа відділень з координат | ❌ |

---

## 📦 Roadmap впровадження

### 🥇 Phase 1 — швидкі wins (1-2 дні) — START

#### 1.1 Локальна БД довідників
- Міграції: `np_regions`, `np_cities`, `np_warehouses` з повним набором полів
- Eloquent моделі: `NpRegion`, `NpCity`, `NpWarehouse`
- `NpReferenceSyncService` з 3 методами: `syncRegions()`, `syncCities()`, `syncWarehouses()`
- Artisan команда `np:sync-references` з опціями `--regions --cities --warehouses --all`
- Schedule: щодня 03:00 UTC
- Прогрес-бар у CLI (chunks по 500 для пагінації)

#### 1.2 Об'ємна вага
- Додати `length`, `width`, `height` в `products` (`decimal(8,2)`)
- Утиліта `Product::getVolumeWeight()`: `($length * $width * $height) / 4000`
- В `NovaPoshtaProvider::calculateShippingCost()`: `Weight = MAX(actualWeight, volumeWeight)`
- Заповнити дефолтні розміри для існуючих товарів (на базі категорії)

#### 1.3 Free shipping + знижка
- В `CheckoutComponent` після `getCartTotal()`:
  - Якщо `total >= free_shipping_threshold` → `shippingCost = 0`, прапор `freeShipping = true`
  - В UI: badge "Безкоштовна доставка"
- В DisplaySetting (admin): `shipping_discount_percent` + `shipping_discount_amount`
- В розрахунку: `final = max(0, cost * (1 - %/100) - amount)`

### 🥈 Phase 2 — TTN CRUD в адмінці (3-5 днів)

- Filament resource `NpShipmentResource` з повним розширенням:
  - Sender section: counterparty, contact, city, warehouse, address, phone
  - Recipient section: ім'я, телефон, ЄДРПОУ (опц), city, warehouse/poshtomat/address
  - Parcels section: repeater з length/width/height/weight/pack_type/manual_processing
  - Payment: payer, payment_method, declared_cost, COD amount
  - Optional services: backward_delivery, payment_control, avia
- Action `Створити TTN` (calls `InternetDocument.save`)
- Action `Редагувати` (`InternetDocument.update`)
- Action `Видалити` (`InternetDocument.delete`)
- Action `Друк` — PDF через `printDocument` (формат, кількість копій)
- **Реєстр (ScanSheet)**: окремий resource:
  - Створити реєстр на дату (`ScanSheet.insertDocuments`)
  - Додати/видалити TTN з реєстру
  - Друк реєстру PDF
  - Видалити реєстр
- Bulk-action на orders: "Створити TTN для обраних"

### 🥉 Phase 3 — Auto-tracking + сповіщення (2-3 дні)

- Cron `np:sync-tracking` (кожні 30 хв) — `TrackingDocument.getStatusDocuments` для не-доставлених
- Зберігати lifecycle в `np_shipment_status_history`:
  - status_code, status_text, datetime, location
- Email-сповіщення клієнту через Mailables:
  - `OrderShipped` — TTN створено
  - `OrderInWarehouse` — прибуло у відділення
  - `OrderReceived` — отримано
- SMS через NP gateway (`InternetDocument.printSticker`) опціонально
- Шаблони повідомлень в DisplaySetting (per-status)
- Cron-ключ у `.env` для зовнішнього виклику

### 🏅 Phase 4 — Frontend deluxe (2 дні)

- **Мапа відділень** Leaflet (free OSM) з координат `Longitude/Latitude`
  - Маркери з popup: номер, адреса, графік, обмеження
  - Filter за обмеженнями ваги/розмірів посилки
- Поле "Бажана дата доставки" (date picker, через NP `getDocumentDeliveryDate`)
- Поле "Бажаний час" (slot 9-18 / 18-22)
- Адресна доставка з повним street/house/flat
- Кур'єр з `rise_on_floor` / `elevator`
- Юр.особа toggle → field `ЄДРПОУ`
- "Окремий контактний телефон отримувача"
- Графік відділення в tooltip (open/close зараз)

### Phase 5 (nice-to-have, low priority)

- Власні тарифи (без API) — power-users
- Тарифні зони (місто / область / Україна)
- Знижки/комісії на доставку
- Геозона + податковий клас
- Тип упаковки (бокс/конверт/палет/шини)
- Multi-parcel розрахунок
- Підтримка `tires_and_wheels` для авто-категорій

---

## Метрики реалізації

```
OpenCart NP модуль ≈ 10 000 LOC, 9 табів, 50+ полів TTN, локальна БД 30k міст / 13k відділень,
                    cron tracking, реєстри, друк PDF, email/sms сповіщення

SimpleShop NP    ≈  1 500 LOC, 1 таб, ~5 полів TTN, без локальних таблиць,
                    без cron, без друку, без сповіщень
```

**Покриття функціоналу:** ~30%. Customer flow працює, admin-flow і operational tools відсутні.

---

## Status легенда

| Знак | Значення |
|---|---|
| ✅ | Реалізовано |
| ⚠️ | Частково реалізовано / є але не повністю |
| ❌ | Відсутнє |

_Дата аналізу: 2026-05-04_
