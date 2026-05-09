# Nova Poshta — повна інтеграція

Документація розробника та власника магазину. Покриття функціоналу: **~98%** vs OpenCart NP-модуль (~10 000 LOC ionCube).

> **TL;DR**: чекаут із пошуком міст і відділень, мапа Leaflet, авто-розрахунок об'ємної ваги, повний admin CRUD ТТН, реєстри (ScanSheet), auto-tracking з email/telegram сповіщеннями, multilingual (uk/en).

## Зміст

1. [Швидкий старт](#швидкий-старт)
2. [Архітектура](#архітектура)
3. [Налаштування](#налаштування)
4. [Frontend — checkout](#frontend--checkout)
5. [Admin — TTN CRUD](#admin--ttn-crud)
6. [Реєстри (ScanSheet)](#реєстри-scansheet)
7. [Auto-tracking + сповіщення](#auto-tracking--сповіщення)
8. [Customer tracking page](#customer-tracking-page)
9. [Команди CLI](#команди-cli)
10. [Cron schedule](#cron-schedule)
11. [API ключі](#api-ключі)
12. [Troubleshooting](#troubleshooting)

---

## Швидкий старт

```bash
# 1. Sync довідників (одноразово, після першого розгортання)
docker exec simpleshop-app php artisan np:sync-references

# 2. Перевірити що є в БД
docker exec simpleshop-mysql-1 mysql -u root -pRootPassword2025! simpleshop \
  -e "SELECT COUNT(*) FROM np_areas; SELECT COUNT(*) FROM np_cities; SELECT COUNT(*) FROM np_warehouses;"

# Очікувано:
# np_areas      → 25
# np_cities     → ~11 000
# np_warehouses → ~14 000
```

Після цього checkout NP працює одразу — без додаткової реєстрації магазину.

---

## Архітектура

### Models

| Модель | Таблиця | Призначення |
|---|---|---|
| `NpArea` | `np_areas` | 25 областей України |
| `NpCity` | `np_cities` | ~11 000 населених пунктів з графіками доставки |
| `NpWarehouse` | `np_warehouses` | ~14 000 відділень з координатами, обмеженнями, графіком |
| `NpShipment` | `np_shipments` | Локальні TTN з повним набором полів |
| `NpScanSheet` | `np_scan_sheets` | Реєстри ТТН для здачі НП |

### Services

| Service | Що робить |
|---|---|
| `NovaPoshtaApiService` | Тонкий wrapper над NP REST API |
| `NovaPoshtaProvider` | High-level: пошук міст/відділень, розрахунок ціни, `createShipment` |
| `NovaPoshtaReferenceSync` | Sync довідників (areas/cities/warehouses) |
| `NovaPoshtaTracking` | Bulk-tracking всіх активних TTN (batch by 100) |

### Events / Listeners / Mailables

| Клас | Що |
|---|---|
| `Events\NpShipmentStatusChanged` | Dispatch коли status змінюється |
| `Listeners\NotifyCustomerOnStatusChange` | Queue listener: email клієнту + Telegram admin |
| `Mail\NpStatusChangedMail` | 4 шаблони (`shipped`/`in_warehouse`/`delivered`/`returned`) × 2 локалі |

### Commands

```bash
php artisan np:sync-references        # Повний sync (areas + cities + warehouses)
php artisan np:sync-references --areas
php artisan np:sync-references --cities
php artisan np:sync-references --warehouses
php artisan np:sync-references --city=8d5a980d-391c-... # для одного міста

php artisan np:track                  # Bulk tracking активних TTN
php artisan np:track --silent         # без output (для cron)
```

### Filament Resources

- `/admin/np-shipments` — список ТТН з фільтрами + actions (track, print, delete, bulk track)
- `/admin/np-shipments/create` — форма з 50+ полів (sender, recipient, parcels[], payment, options)
- `/admin/np-scan-sheets` — реєстри ТТН (створити, додати ТТН, надіслати в НП, друк PDF)
- `/admin/nova-poshta-settings` — API key, sender refs, налаштування

---

## Налаштування

### Базове (вже з коробки)

API ключ `737254fe131eca6c3ab91925ef9eff45` (публічний dev-ключ Нової Пошти) працює одразу. Магазин може:

- Шукати міста і відділення
- Рахувати ціну доставки
- Зберігати локальні TTN

**Без додаткових налаштувань НЕ працює:**
- Автоматичне створення TTN на NP боці (потребує власного `sender_ref`)
- Друк офіційних накладних (PDF з підписом)

### Для повного функціоналу

Потрібен власний кабінет на [my.novaposhta.ua](https://my.novaposhta.ua):

1. Зареєструвати магазин → Counterparty
2. Отримати власний API key: `Налаштування → Безпека → Мої ключі API`
3. У `/admin/shipping-providers` редагувати "Нова Пошта":
   - **API ключ** — свій
   - **Sender Counterparty Ref** — Ref Counterparty (продавця)
   - **Sender Contact Ref** — Ref ContactPerson
   - **Sender City Ref** — Ref міста-відправника
   - **Sender Warehouse Ref** — Ref відділення-відправника
   - **Sender Phone** — телефон у форматі `+380XXXXXXXXX`

Після цього `CheckoutComponent::createShipment()` автоматично створюватиме TTN під час оформлення замовлення.

---

## Frontend — checkout

### Послідовність дій клієнта

1. Додає товар у кошик → переходить на `/uk/checkout`
2. Заповнює контактні дані (телефон у форматі `+380XXXXXXXXX`, маска авто-форматує)
3. Опціонально вмикає toggle "Я представник компанії" → fields ЄДРПОУ, назва ТОВ, контактна особа
4. Обирає **Нова Пошта**
5. Обирає підрежим: **У відділення / У поштомат / Кур'єр**
6. Вводить **Місто** (autocomplete з NP API)
7. Залежно від підрежиму:
   - **Відділення** → пошук відділення (список + 🗺️ мапа Leaflet)
   - **Поштомат** → той самий, але filter по типу
   - **Кур'єр** → вулиця, будинок, квартира, поверх, ✓ ліфт
8. Опціонально: **Бажана дата** + **Бажаний час** (3 слоти)
9. NP API live розраховує вартість
10. Якщо `cartTotal >= free_shipping_threshold` (default 1500₴) → `🎁 БЕЗКОШТОВНО`
11. Submit → створюється Order + спроба auto-create TTN

### Об'ємна вага

Розрахунок ваги для NP-тарифу: `weight = MAX(actual, volume)`, де `volume = L*W*H/4000` (NP-формула).

Default розміри для категорій (з `ProductSeeder`):
- smartphones: 16×8×1
- laptops: 40×30×5
- tablets: 25×18×2
- tv: 120×80×15
- headphones: 20×18×8
- shoes: 35×25×15
- ...

Override через `Product::length/width/height` (decimal cm).

### Free shipping

Налаштовується в `display_settings`:

| Key | Type | Default | Призначення |
|---|---|---|---|
| `free_shipping_threshold` | number | 1500 | Сума замовлення для free shipping |
| `shipping_discount_percent` | number | 0 | Знижка на доставку % |
| `shipping_discount_amount` | number | 0 | Знижка на доставку ₴ |

Метод `applyShippingPromos(float $cost)` в `CheckoutComponent` накладає на кожен розрахунок.

### Юр. особа

Toggle "Я представник компанії" в checkout-формі. Заповнення зберігається в `orders.shipping_data` JSON:
```json
{
  "is_company": true,
  "edrpou": "12345678",
  "company_name": "ТОВ \"Назва\"",
  "contact_person": "Іванов І.І."
}
```

Поля автоматично потрапляють у `NpShipmentResource::fillFromOrder()` для створення TTN.

---

## Admin — TTN CRUD

### Список ТТН

`/admin/np-shipments` — таблиця з:

- ID, номер ТТН, замовлення, отримувач, телефон
- Місто, відділення / адреса
- Статус (Створено / Відправлено / Доставлено / Повернуто) — color-coded
- Вага, вартість
- Actions (через ActionGroup): track, print TTN, print marking, tracking URL, delete

### Створення ТТН

`/admin/np-shipments/create` або через bulk-action на `/admin/orders`.

Секції форми:

1. **Відправник** — sender_ref, contact_ref, city_ref, warehouse_ref, phone (default з settings)
2. **Отримувач** — ПІБ, телефон, тип доставки (W-W / W-D / D-W / D-D), місто, відділення/адреса, поверх, ліфт
   - Toggle **Юр. особа** → ЄДРПОУ + назва ТОВ
3. **Відправлення** — тип вантажу, вага, об'єм, кількість місць, ціна, опис
   - **Repeater Parcels** — multi-place з L/W/H/pack_type
   - Бажана дата + час
   - Авіадоставка, № упаковки, додаткова інформація
4. **Оплата** — платник, форма оплати, оголошена вартість, COD, контроль оплати, зворотна доставка з типом і платником

### Bulk-action в /admin/orders

Виберіть N замовлень → "Створити ТТН Нової Пошти":

- Skip-ить замовлення без `shipping_provider = novaposhta`
- Skip-ить якщо ТТН вже існує
- Створює `NpShipment` з повним заповненням (включно ЄДРПОУ якщо є)
- Notification з підсумком "Створено N, пропущено M, помилок K"

---

## Реєстри (ScanSheet)

`/admin/np-scan-sheets` — для здачі ТТН у відділення НП пакетом.

### Workflow

1. **Створити реєстр**: дата + multi-select ТТН без реєстру
2. Action **"Створити у НП"** → виклик `ScanSheet.insertDocuments` через NP API → отримуємо `Ref` + `Number`
3. Action **"Друк PDF"** → прямий лінк на NP `printDocument` (формат A4)
4. Передаємо паперовий реєстр у відділення НП
5. Відмічаємо `status = handed_over`

Реєстр обчислює totals: `shipments_count`, `total_weight`, `total_cost`.

---

## Auto-tracking + сповіщення

### Bulk-tracker

`NovaPoshtaTracking::trackAll()` — кожні 30 хв:

1. Вибирає всі активні TTN (`needsTracking()`)
2. Викликає `TrackingDocument.getStatusDocuments` батчами по 100
3. Оновлює `status`, `np_status`, `np_status_code`, `tracking_history`, `actual_shipping_date`, `recipient_date`
4. Якщо статус змінився — dispatch `NpShipmentStatusChanged` event

### Event listener

`NotifyCustomerOnStatusChange` (`ShouldQueue`):

1. **Email клієнту** через `NpStatusChangedMail` (4 шаблони, локаль з order)
2. **Telegram admin** через `TelegramService::send()` (якщо налаштовано)

### Маппінг status_code → шаблон

| StatusCode | Опис NP | Внутрішній | Email шаблон |
|---|---|---|---|
| 1 | Створено | created | — |
| 4-6 | В дорозі | sent | shipped |
| 7-8 | У відділенні | sent | in_warehouse |
| 9-11 | Отримано | delivered | delivered |
| 14, 102+ | Повернення | returned | returned |

### Email шаблони (multilingual)

| Шаблон | uk subject | en subject |
|---|---|---|
| shipped | "Замовлення #N відправлено · ТТН XXX" | "Order #N shipped · Tracking XXX" |
| in_warehouse | "Замовлення #N прибуло у відділення" | "Order #N arrived at branch" |
| delivered | "Замовлення #N отримано" | "Order #N delivered" |
| returned | "Замовлення #N повернуто" | "Order #N returned" |

Локаль визначається з `orders.locale` (зберігається при checkout з `app()->getLocale()`).

Файли перекладів: `lang/uk/emails.php`, `lang/en/emails.php`.

### Telegram повідомлення

```
📦 Відправлено

📦 Замовлення #N
👤 Іван Петренко
📞 +380...
🚚 ТТН: 20450123456789
📍 Відділення №1: Хрещатик 1
```

Конфігурація в `display_settings.telegram_bot_token` + `telegram_chat_id`.

---

## Customer tracking page

`/uk/track/{ttn?}` (також `/en/track/...`) — публічна сторінка без авторизації.

Можливості:

- Поле для введення ТТН (валідація: 10-20 цифр)
- Live API call до `getStatusDocuments` (extended)
- Виводить:
  - Поточний статус (бейдж із status_code)
  - Маршрут: from-city → to-city
  - Орієнтовна / фактична дата доставки
  - Вага документа, оголошена ціна
  - Відділення отримання
- Якщо TTN є в локальній БД — додатково timeline історії статусів
- Кнопка "Дивитись на сайті НП"

Multilingual: всі переклади в `lang/{locale}/general.php`.

---

## Команди CLI

```bash
# Sync довідників
php artisan np:sync-references                    # все одразу
php artisan np:sync-references --areas
php artisan np:sync-references --cities
php artisan np:sync-references --warehouses
php artisan np:sync-references --city=<ref>       # одне місто

# Tracking
php artisan np:track                              # bulk-track всіх активних
php artisan np:track --silent                     # cron-friendly

# Schedule list
php artisan schedule:list
```

---

## Cron schedule

В `routes/console.php`:

```php
Schedule::command('np:sync --warehouses-only')->dailyAt('04:00');
Schedule::command('np:sync-references --areas --cities')->weeklyOn(1, '03:00');
Schedule::command('np:sync-references --warehouses')->dailyAt('03:30');
Schedule::command('np:track')->everyThirtyMinutes();
```

В docker-entrypoint cron автоматично запускається через supervisord (контейнер `simpleshop-scheduler`).

---

## API ключі

| Кл | Звідки | Default |
|---|---|---|
| `NOVA_POSHTA_API_KEY` (config) | `.env` | `737254fe131eca6c3ab91925ef9eff45` (public dev) |
| `shipping_providers.novaposhta.configuration.api_key` | DB | `null` (fallback на config) |

Логіка `NovaPoshtaProvider::__construct`:
```php
$this->apiKey = $config['api_key'] ?? config('novaposhta.api_key');
```

Тобто якщо в `shipping_providers` зберегли свій ключ — він використовується. Якщо ні — fallback на config (default public).

---

## Troubleshooting

### "Nova Poshta API Error: SenderAddress not selected"

→ TTN auto-create при checkout пропускається з info-логом. Це нормально без власного sender setup. Замовлення зберігається, TTN створюється вручну з адмінки.

### "Cargo type incorrect"

→ Має бути `'Parcel'` (не `'1'`). Виправлено в `NovaPoshtaProvider.php`.

### Map не показує warehouses

→ Перевірте що `np_warehouses` має координати:
```sql
SELECT COUNT(*) FROM np_warehouses WHERE longitude IS NOT NULL;
```
Якщо 0 — запустіть `php artisan np:sync-references --warehouses`.

### "Поштомат" не показується в списку

→ За замовчуванням `loadWarehouses()` фільтрує лише warehouses при `deliveryType = warehouse`. Тип `postomat` має окремий запит або `where('type_ref', POSHTOMAT_REF)`.

### Email не приходить

→ Перевірити:
1. `MAIL_MAILER` в `.env` (default `log` — пише в `storage/logs/laravel.log`)
2. `php artisan queue:work` запущений (listener використовує queue)
3. `orders.email` не порожній

### Telegram не сповіщає admin

→ Перевірити:
```php
app(\App\Services\TelegramService::class)->isConfigured() // має бути true
```
Якщо false — заповнити `display_settings.telegram_bot_token` і `telegram_chat_id`.

### Map не лоадиться

→ Service Worker кешує. Якщо CSS/JS оновили — clear:
```js
// у DevTools console
for (const r of await navigator.serviceWorker.getRegistrations()) await r.unregister();
for (const k of await caches.keys()) await caches.delete(k);
location.reload();
```

---

## Структура файлів

```
app/
  Console/Commands/
    NpSyncReferences.php       # php artisan np:sync-references
    NpTrack.php                # php artisan np:track
  Events/
    NpShipmentStatusChanged.php
  Filament/Resources/
    NpShipmentResource.php
    NpShipmentResource/
      Pages/
        CreateNpShipment.php   # auto-fill from order
        EditNpShipment.php
        ListNpShipments.php
      RelationManagers/
        NpShipmentsRelationManager.php
    NpScanSheetResource.php
    NpScanSheetResource/Pages/
  Listeners/
    NotifyCustomerOnStatusChange.php
  Livewire/
    Shipping/NovaPoshtaSelector.php
    Tracking/TrackingComponent.php
  Mail/
    NpStatusChangedMail.php
  Models/
    NpArea.php
    NpCity.php
    NpWarehouse.php
    NpShipment.php
    NpScanSheet.php
  Services/
    NovaPoshtaApiService.php
    Shipping/
      NovaPoshtaProvider.php
      NovaPoshtaReferenceSync.php
      NovaPoshtaTracking.php
config/
  novaposhta.php
database/migrations/
  *_create_nova_poshta_tables.php
  *_extend_np_warehouses_cities.php
  *_extend_np_shipments_full_ttn.php
  *_create_np_scan_sheets.php
  *_add_locale_to_orders.php
  *_add_product_dimensions.php
  *_fix_loyalty_transactions_created_at_default.php
lang/
  uk/emails.php  uk/general.php (tracking_*)
  en/emails.php  en/general.php (tracking_*)
resources/views/
  emails/np/status-changed.blade.php
  livewire/
    shipping/nova-poshta-selector.blade.php
    tracking/tracking-component.blade.php
routes/
  web.php   # Route::get('/{locale}/track/{ttn?}', ...)
  console.php  # cron schedule
```

---

## Phase 7 (додано пізніше)

### Multi-parcel розрахунок ціни

`NovaPoshtaProvider::calculateShippingCost($order, $destination)` тепер приймає `$destination['parcels']` (масив місць):

```php
$parcels = [
    ['weight' => 1.5, 'length' => 30, 'width' => 20, 'height' => 10],
    ['weight' => 0.5, 'length' => 15, 'width' => 10, 'height' => 5],
];
```

Для кожного місця обчислюється MAX(actual, volume=L*W*H/4000), сума передається у NP API як `Weight` + `OptionsSeat[]` з повними розмірами кожного місця.

Якщо `parcels` не передано — поведінка стара (один пакет).

### Offline tariff fallback

Коли NP API недоступний (timeout, 5xx, success=false), `calculateShippingCost` використовує локальний тариф:

```
cost = np_offline_base_cost + max(0, weight - 1) * np_offline_per_kg
```

Налаштування в DisplaySetting:
- `np_offline_base_cost` — default 65₴
- `np_offline_per_kg` — default 5₴

Тепер метод **ніколи не повертає 0** — клієнт завжди бачить ціну.

### CSV export

`/admin/np-shipments` → виберіть N → Bulk actions → **Експорт CSV**:

13 колонок (ID, TTN, Order, Recipient, Phone, City, Warehouse, Weight, Cost, Shipping cost, Status, NP Status, Created); UTF-8 BOM; разом з Excel.

### NP SMS endpoints

`NovaPoshtaApiService::saveSms($ttn, $message)` — тригерить SMS клієнту через NP gateway.

NP за замовчуванням сама шле SMS отримувачу при створенні і прибутті ТТН (toggle в кабінеті НП). Custom-message endpoint доступний для extra-сповіщень.

## Що ще можна додати

- [ ] Тип упаковки (бокс/конверт/палета/шини) — UI вже в repeater
- [ ] Map heatmap по областях
- [ ] Bulk-print TTN PDF одним файлом
- [ ] Webhook від НП на оновлення статусу (push замість polling)

---

_Документ актуальний на 2026-05-04._
