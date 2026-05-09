# Clone-to-new-shop guide

> Як з нуля підняти новий магазин на SimpleShop core за **~10 хвилин**.

---

## Шаблонність

Один core (Laravel 12 + Filament 3 + Livewire 3 + Multi-warehouse + Theme tokens) → **N магазинів**:

| Шар | Спільне між магазинами | Унікальне |
|---|---|---|
| Core PHP | Models, Services, Livewire класи, Filament admin | — |
| Schema | Migrations + foundation | — |
| UI logic | `<x-ui.*>` компоненти + Blade templates | — |
| **Стилі** | контракт CSS-змінних | `tokens/{theme}.css` |
| **Дані** | контракт DisplaySetting | shop name, contacts, API keys |
| **Склади** | архітектура | code, address, sender refs |

---

## 1. Передумови

- PHP 8.3+ із розширеннями `mbstring`, `intl`, `pdo_mysql`, `redis`, `gd`, `bcmath`, `zip`
- Composer 2
- Node.js 20+
- MySQL 8 або MariaDB 10.11
- Redis 7 (cache/session/queue)
- Meilisearch 1.6 (для пошуку)
- (опц.) Docker + docker-compose

---

## 2. Швидкий старт

```bash
# 1. Склонуйте репо
git clone <upstream-repo> auto-parts-shop
cd auto-parts-shop

# 2. Залежності
composer install
npm install

# 3. .env
cp .env.example .env
php artisan key:generate
# Відредагуйте DB_*, REDIS_*, MEILISEARCH_*, MAIL_*, APP_URL

# 4. База
php artisan migrate

# 5. Базовий онбординг — wizard
php artisan shop:init
# Запитає: shop name, phone, email, city, default warehouse code/name, theme

# 6. Зібрати CSS
npm run build

# 7. (опц.) Sample-data
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=BrandSeeder
php artisan db:seed --class=CouponSeeder
```

Після цього сайт доступний за `APP_URL`. Адмін за `/admin/login`.

---

## 3. Налаштування інтеграцій

Усі через адмінку — без CLI.

### Нова Пошта
1. `/admin/shipping-providers` → Нова Пошта → введіть `api_key` у JSON-конфігурацію.
2. `/admin/np-sync` (або CLI: `php artisan np:sync`) — підтягне довідники міст і відділень.
3. `/admin/merchant-warehouses/1/edit` → tab «Нова Пошта sender» → введіть `np_sender_ref`, `np_sender_city_ref`, `np_sender_warehouse_ref`, `np_contact_person_ref`, `np_sender_phone`. Перевірте у вашому НП-кабінеті.

### УкрПошта
1. `/admin/ukr-poshta-settings` → введіть `up_ecom_bearer`, `up_counterparty_token`, `up_sender_uuid`, `up_sender_address_uuid`. Email/документи на ecom — окремий контракт з УП.
2. `php artisan up:sync-references` — завантажить області/міста/post offices у локальний кеш.

### Платежі
- `/admin/payment-gateway-settings` — LiqPay public/private keys, WayForPay merchant_account/secret_key.

### Telegram
- `/admin/shop-settings` → tab «Telegram» — bot token + chat_id.

---

## 4. Створення кастомної теми

```bash
# 1. Скопіюйте brutal як шаблон
cp resources/css/tokens/brutal.css resources/css/tokens/auto-parts.css

# 2. Відредагуйте значення (зберігайте усі імена змінних!)
$EDITOR resources/css/tokens/auto-parts.css

# 3. Активуйте через UI або CLI
php artisan theme:use auto-parts
# або: /admin/theme-settings → click "Активувати"

# 4. Перебудуйте
npm run build
```

**Контракт CSS-змінних:** `docs/THEMES.md`. Усі компоненти `<x-ui.*>` автоматично адаптуються — кнопки, картки, badges, inputs, modals, alerts, sections, product cards.

---

## 5. Створення додаткових складів

### Через адмінку
`/admin/merchant-warehouses` → «Створити склад» → 4 таби: Основне / Адреса / NP-sender / UP-sender.

### Через tinker
```php
\App\Models\MerchantWarehouse::create([
    'code' => 'LVIV-1',
    'name' => 'Львів Центральний',
    'type' => 'own',
    'city' => 'Львів',
    'address' => 'вул. Городоцька, 5',
    'pickup_supported' => true,
    'np_sender_ref' => '...',
    'np_sender_city_ref' => '...',
    'np_sender_warehouse_ref' => '...',
    'np_sender_phone' => '+380...',
    'is_active' => true,
]);
```

Потім додайте інвентар через `/admin/products/{id}/edit` → tab «Інвентар».

---

## 6. Робочий цикл магазину

```
[Постачальник]
    ↓ /admin/receiving-orders → «Прийняти»
+inventory @{warehouse}     (income)
    ↓
[Каталог: товари видно з availability per склад]
    ↓
[Клієнт оформляє checkout — frontend warehouse-availability badge показує наявність]
    ↓
[Адмін /admin/orders/{id}/edit → «Створити ТТН»]
    ↓ NP/UP API call
TTN отримано → OrderFulfillmentService::shipOrder() авто-списує
    ↓
−inventory @{order.warehouse}    (ship)
status: shipped
```

Між складами через `/admin/inventory-transfers`: draft → відправити (-source) → прийняти (+dest).

---

## 7. Файлова структура (що чіпати)

| Що | Файл/тека |
|---|---|
| Логотип, favicon | `public/favicon.ico`, `public/icon-*.png` |
| Стиль | `resources/css/tokens/{theme}.css` |
| Frontend views | `resources/views/livewire/`, `resources/views/components/ui/` |
| Email templates | `resources/views/emails/` |
| Категорії товарів | `/admin/categories` |
| Бренди | `/admin/brands` |
| Сторінки CMS | `/admin/pages` |
| FAQ | `/admin/faq-pages` |
| SEO templates | `/admin/seo-templates` |
| Mega menu | `/admin/mega-menu-builder` |
| Локалізація | `lang/uk/`, `lang/en/` (Spatie translatable для моделей) |

---

## 8. Multi-locale

Підтримуються `uk` (default) + `en`. Перемикач у header.

- Translatable полів (Product/Category тощо): автоматично через Spatie HasTranslations
- UI strings: `lang/{locale}/general.php`
- SEO meta: per-locale через HasSeoMeta trait

Додати нову локаль:
1. `lang/{code}/general.php` — скопіюйте з `uk`
2. `config/app.php` → додайте у `available_locales`
3. URL slugs автоматично переключаються

---

## 9. Тестування

```bash
# PHPUnit (in-memory SQLite)
vendor/bin/phpunit

# Конкретні Inventory tests
vendor/bin/phpunit tests/Feature/Inventory/
```

Конфіг у `phpunit.xml`. Для нового магазину — додайте сценарії, специфічні до вашого домену.

---

## 10. Production deploy

### Швидкі команди
```bash
# pull → migrate → cache → restart workers
git pull
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### Crontab
```
# Резерв розморозити (Phase 2 — pending)
* * * * * cd /var/www/shop && php artisan schedule:run >> /dev/null 2>&1

# Sync NP-довідників щотижня
0 3 * * 0 cd /var/www/shop && php artisan np:sync

# Track НП TTN кожні 30хв
*/30 * * * * cd /var/www/shop && php artisan np:track
```

---

## 11. Сценарії клонування

### Сценарій A: Новий магазин одного бренду (auto-parts)
```bash
git clone simpleshop auto-parts-ua && cd auto-parts-ua
composer install && npm install
php artisan migrate
php artisan shop:init --theme=auto-parts \
    --shop-name="Auto Parts UA" \
    --shop-email="info@autoparts.ua" \
    --shop-phone="+380441234567" \
    --warehouse-code=KYIV-1 \
    --warehouse-name="Київ Лівобережний" \
    --shop-city="Київ"
npm run build
# далі — налаштування API ключів та контенту через admin
```

### Сценарій B: Magazin одного власника, кілька складів
1. Запустіть як у Сценарій A
2. Через `/admin/merchant-warehouses` створіть LVIV-1, ODESA-1, тощо
3. Розподіліть товари через `/admin/products/{id}/edit` → «Інвентар» → «Додати склад» для кожного

### Сценарій C: Дроп-шиппінг постачальник-партнер
1. У `/admin/merchant-warehouses` створіть `type='drop_ship'` — без фізичного інвентарю
2. Інвентар через `ReceivingOrder` не оформлюється; інтеграція з API постачальника — окрема задача (todo)

---

## 12. Що **НЕ** треба міняти між магазинами

- `app/` — модельний / сервісний код
- `database/migrations/` — схема
- `routes/` — маршрутизація
- `app/Filament/` — admin (хіба що ви додаєте щось специфічне)
- `app/Livewire/` — інтерактивна логіка

Все це — спільне ядро. Оновлюється з upstream через `git pull` без конфліктів.

---

## 13. Куди йти далі

| Хочу | Дивитись |
|---|---|
| Зрозуміти всю архітектуру | `docs/FUNCTIONALITY.md` |
| Створити нову тему | `docs/THEMES.md` + `/admin/theme-settings` |
| Додати UI-компонент | `docs/UI-COMPONENTS.md` |
| Multi-warehouse | `docs/MULTI-WAREHOUSE-PLAN.md` + `docs/INVENTORY-LOGIC.md` |
| Хроніка фіч | `docs/CHANGELOG-FEATURES.md` |
| НП інтеграція | `docs/NOVA-POSHTA.md` |
| Тести | `docs/TESTING-GUIDE.md` |
| Setup detail | `docs/SETUP.md` |

---

*Цей файл — single source of truth для онбордингу нової інстанції. Тримайте оновленим коли додаєте нові required steps.*
