# SimpleShop — Повна документація

**Версія:** 3.0 | **Дата:** 30 березня 2026
**Стек:** Laravel 12 + Filament 3 + Livewire 3 + Tailwind CSS 4 + MySQL 8 + Redis 7

**Статистика:** 315 PHP файлів | 104 Blade views | 37 моделей | 45 сервісів | 48 Livewire | 111 Filament | 71 міграція | 39 комітів

---

## Зміст

1. [Каталог товарів](#1-каталог-товарів)
2. [Варіанти товарів](#2-варіанти-товарів)
3. [Порівняння товарів](#3-порівняння-товарів)
4. [Кошик і Checkout](#4-кошик-і-checkout)
5. [Платіжні системи](#5-платіжні-системи)
6. [Доставка](#6-доставка)
7. [Особистий кабінет](#7-особистий-кабінет)
8. [Система лояльності](#8-система-лояльності)
9. [Групи клієнтів і гуртові ціни](#9-групи-клієнтів-і-гуртові-ціни)
10. [Купони та знижки](#10-купони-та-знижки)
11. [Конструктор головної сторінки](#11-конструктор-головної-сторінки)
12. [Пакетний редактор](#12-пакетний-редактор)
13. [TinyPNG оптимізація зображень](#13-tinypng-оптимізація-зображень)
14. [Mega Menu Editor](#14-mega-menu-editor)
15. [Система інтеграцій](#15-система-інтеграцій)
16. [Email сповіщення](#16-email-сповіщення)
17. [SEO та XML фіди](#17-seo-та-xml-фіди)
18. [Фіскальні чеки та Telegram](#18-фіскальні-чеки-та-telegram)
19. [Юридичні сторінки](#19-юридичні-сторінки)
20. [Безпека](#20-безпека)
21. [Адмін-панель](#21-адмін-панель)
22. [CI/CD, тести, Docker](#22-cicd-тести-docker)

---

## 1. Каталог товарів

**Моделі:** Product, Category, Brand, Filter, FilterGroup

| Сторінка | URL | Компонент |
|----------|-----|-----------|
| Головна | `/` | HomeComponent (динамічні модулі) |
| Категорія | `/{category:slug}` | CategoryComponent (фільтри, сортування) |
| Товар | `/{product:slug}` | ProductComponent (галерея, відгуки, варіанти) |
| Бренди | `/brands` | BrandsComponent (алфавітне групування) |
| Бренд | `/brands/{brand:slug}` | BrandComponent |
| Пошук | `/search` | SearchComponent |
| Акції | `/specials` | SpecialsComponent |
| Хіти | `/hits` | HitsComponent |
| Новинки | `/new` | NewProductsComponent |

**Картка товару:** галерея, ціна/стара ціна, SKU, бренд, варіанти, вкладки (опис, характеристики, відгуки, доставка), пов'язані товари.

---

## 2. Варіанти товарів

**Архітектура (як Rozetka/Jabko):**

```
Product → ProductOption (Колір, Розмір, Пам'ять)
            → ProductOptionValue (Червоний, S, 128GB)
                → ProductVariant (конкретна комбінація з власним SKU/ціною/залишком)
```

**Моделі:** ProductOption, ProductOptionValue, ProductVariant

**Типи опцій:**
- `select` — випадаючий список
- `color` — кольорові свотчі (з color_hex)
- `button` — кнопки

**Ціноутворення:** `variant.price ?? (product.price + sum(option_value.price_modifier))`

**Адмін (Filament):**
- Вкладка "Варіанти" з nested Repeater (Опції → Значення)
- VariantsRelationManager з CRUD таблицею
- Кнопка **"Генерувати варіанти"** — cartesian product всіх комбінацій, авто-SKU

**Frontend:**
- Реактивний selector (кольорові свотчі, кнопки з модифікатором ціни)
- Real-time оновлення ціни, SKU, stock status
- Out-of-stock індикатор per variant

**Кошик:** composite key `{productId}_v{variantId}` — різні варіанти = окремі позиції

**Демо:** 29 варіантів для 5 товарів (Color+Size=12, Memory+Color=6, Size=3)

---

## 3. Порівняння товарів

**Session-based** (без авторизації), максимум 4 товари.

**Компоненти:**
- `ComparisonButtonComponent` — кнопка "ПОРІВНЯТИ" на картках і сторінці товару
- `ComparisonBarComponent` — floating bar внизу екрану з вибраними товарами
- `ComparisonComponent` — повна сторінка `/comparison`

**Таблиця порівняння:**
- Фото, назва, ціна/стара ціна
- Базові атрибути: бренд, SKU, вага, наявність, рейтинг
- Всі характеристики з filter_groups
- Toggle **"ТІЛЬКИ ВІДМІННОСТІ"**
- Кнопки "КУПИТИ" per product

**ComparisonService:** add, remove, clear, getComparisonData, isInComparison

---

## 4. Кошик і Checkout

**Процес:** Кошик (session) → Checkout `/checkout` → Оплата → Success

**Checkout включає:**
- Вибір збереженої адреси (dropdown з адресної книги)
- 4 служби доставки з динамічним вибором відділення/кур'єра
- 3 платіжні шлюзи + готівка
- Купон + бали лояльності (списання)
- Нарахування балів після замовлення
- Rate limiting: 5 спроб / 60 сек

**Total:** `(subtotal - couponDiscount - loyaltyDiscount) + shippingCost`

---

## 5. Платіжні системи

| Шлюз | Можливості | Webhook |
|------|-----------|---------|
| LiqPay | Оплата, refund, recurring | `/webhooks/liqpay` |
| WayForPay | Оплата, refund, partial refund | `/webhooks/wayforpay` |
| Monobank | Invoice API, OpenSSL verification | `/webhooks/monobank` |

---

## 6. Доставка

| Провайдер | Методи |
|-----------|--------|
| Нова Пошта | Відділення, кур'єр, поштомат |
| УкрПошта | Відділення |
| Meest Express | Відділення |

Автоматичний розрахунок вартості за вагою. Безкоштовна доставка від порогу.

---

## 7. Особистий кабінет

| URL | Сторінка |
|-----|----------|
| `/account` | Dashboard (статистика, рівень, останні замовлення) |
| `/orders` | Замовлення (фільтри по статусу, повторне замовлення) |
| `/order-show/{id}` | Деталі замовлення |
| `/wishlist` | Список бажань |
| `/addresses` | Адресна книга (CRUD, default) |
| `/loyalty` | Програма лояльності (рівень, прогрес, історія) |
| `/settings` | Профіль (телефон, birthdate, аватар, пароль) |
| `/comparison` | Порівняння товарів |

---

## 8. Система лояльності

| Рівень | Поріг | Множник | Знижка |
|--------|-------|---------|--------|
| Бронзовий | 0 | x1.0 | 0% |
| Срібний | 1000 | x1.5 | 3% |
| Золотий | 5000 | x2.0 | 5% |
| Платиновий | 10000 | x3.0 | 10% |

**LoyaltyService:** awardPoints, redeemPoints, recalculateTier, expireOldPoints, birthdayBonus, adjustPoints

---

## 9. Групи клієнтів і гуртові ціни

| Група | Знижка | Мін. замовлення |
|-------|--------|-----------------|
| Роздрібний | 0% | 0 |
| Оптовий | 5% | 5000₴ |
| VIP | 10% | 0 |
| Дистриб'ютор | 15% | 10000₴ |

**Пріоритет:** ProductGroupPrice > GroupDiscount% > Regular price

---

## 10. Купони та знижки

3 типи: percentage, fixed_amount, free_shipping. Ліміти per user + загальні. CouponService.

---

## 11. Конструктор головної сторінки

**Адмін:** `/admin/homepage-builder`

| Модуль | Налаштування |
|--------|-------------|
| Hero банер | 2 рядки заголовку, підзаголовок, CTA, колір |
| Сітка товарів | Фільтр (hits/new/specials/all), ліміт, колонки |
| Категорії | Ліміт, стиль (grid/list) |
| Банер | Текст, кнопка, кольори фону/тексту |
| Текст | Вільний HTML |
| Бренди | Ліміт логотипів |
| Переваги | Масив іконка+заголовок+текст |
| Підписка | Заголовок, опис, кнопка |
| Відгуки | Кількість |
| Таймер акції | Дата закінчення, заголовок |

Drag & drop (стрілки), toggle on/off, кеш 30 хв.

---

## 12. Пакетний редактор

**Адмін:** `/admin/batch-editor`

**15+ фільтрів (collapsible панель):**
- Категорія, бренд, статус, ціна (від/до), пошук (назва/SKU)
- Наявність, виробник
- Без зображення, без опису, без SEO
- Має варіанти, має гуртову ціну
- Рейтинг (від/до), дата створення (від/до), кількість (від/до)

**4 вкладки:** Товари, Категорії, Замовлення, Відгуки

**14 масових дій:**
1. Ціна (встановити/+/-/+%/-%) з preview
2. Акція (% або фікс, зняти акцію) з preview
3. Гуртові ціни per group з preview
4. Статус (active/hit/new/stock)
5. Категорія
6. Бренд/виробник
7. Характеристики (attach/detach фільтри)
8. Пошук і заміна (preview, regex, case-sensitive)
9. Вага/розміри
10. SEO шаблони ({title}, {brand}, {category}, {price}, {sku})
11. Дублювання
12. Генерація варіантів
13. CSV експорт
14. CSV імпорт (preview, column mapping)

**Preview:** 3-step workflow (Preview > Confirm > Execute) для всіх критичних дій

**Журнал дій:** batch_editor_logs з user, action_type, affected_ids, changes_data

**Column visibility:** 17 колонок з dropdown toggle (id, title, sku, price, old_price, qty, stock, active, hit, new, category, brand, manufacturer, weight, rating, reviews, created)

**Hover preview:** наведення на назву товару показує мініатюру зображення

**Видалення:** з підтвердженням та логуванням

---

## 13. TinyPNG оптимізація зображень

**Сервіс:** `App\Services\TinyPng\TinyPngService`

**Можливості:**
- Автоматична компресія зображень (до 80% зменшення без втрати якості)
- Конвертація в WebP формат
- Batch компресія через artisan команду
- Перевірка API лімітів (500 безкоштовних/місяць)

**Конфігурація (`config/tinypng.php`):**
| Параметр | Опис | Default |
|----------|------|---------|
| `api_key` | TinyPNG API ключ | env |
| `max_width` | Максимальна ширина | 1920 |
| `quality` | Якість компресії | 80 |
| `convert_to_webp` | Конвертувати в WebP | true |

**Artisan команда:**
```bash
php artisan images:optimize                 # всі в products/
php artisan images:optimize --path=gallery  # конкретна папка
php artisan images:optimize --limit=100     # ліміт кількості
```

**Інтеграція:** доступна через адмін-панель `/admin/integrations-page` з toggle on/off

---

## 14. Mega Menu Editor

**Адмін:** `/admin/mega-menu-editor` (група: Контент та SEO)

**Горизонтальне меню (чорна стрічка):**
- Додати/видалити пункти (назва + URL)
- Перемістити вгору/вниз (стрілки)
- Авто-генерація з категорій
- Toggle увімк/вимк

**Мега-меню (dropdown під "КАТАЛОГ"):**
- Додати/видалити колонки
- В кожній колонці: категорія (з dropdown) або кастомне посилання
- Видалити елемент з колонки
- Перемістити колонку вліво/вправо
- Авто-генерація з категорій (4-колонковий layout)
- Inline редагування кастомних посилань (назва + URL)
- Перегляд підкатегорій кожної категорії

**Промо-блок:**
- Toggle увімк/вимк
- Заголовок, опис, текст кнопки, URL кнопки

**Зберігання:** `display_settings` таблиця (JSON поля: `horizontal_menu_items`, `main_mega_menu_structure`)

---

## 15. Система інтеграцій

**Адмін:** `/admin/integrations-page`

13 інтеграцій з toggle on/off та налаштуваннями:
- **Платежі:** LiqPay, WayForPay, Monobank
- **Доставка:** Нова Пошта, УкрПошта, Meest
- **Фіскалізація:** Checkbox.ua
- **Аналітика:** Google Analytics, Facebook Pixel
- **Комунікація:** Telegram Bot
- **Маркетплейси:** Google Shopping Feed
- **Пошук:** Meilisearch
- **Зображення:** TinyPNG

Додавання нових: створити клас extends AbstractIntegration, зареєструвати в AppServiceProvider.

---

## 16. Email сповіщення

| Notification | Подія | Отримувач |
|-------------|-------|-----------|
| OrderCreatedNotification | Нове замовлення | Клієнт |
| OrderStatusChangedNotification | Зміна статусу | Клієнт |
| OrderShippedNotification | Відправлення | Клієнт |
| NewOrderAdminNotification | Нове замовлення | Адмін |

OrderObserver автоматично. Mail drivers: log, smtp, resend.

---

## 17. SEO та XML фіди

- SeoMeta per page, OpenGraph, sitemap.xml, robots.txt
- GA, GTM, FB Pixel, Yandex Metrika (config-based)
- XML фіди: `/feed/google.xml`, `/feed/rozetka.xml`, `/feed/prom.xml`

---

## 18. Фіскальні чеки та Telegram

**Checkbox.ua:** createReceipt (auto on payment_status=paid), openShift/closeShift (cron)

**Telegram:** notifyNewOrder, notifyOrderStatusChanged (auto via OrderObserver)

---

## 19. Юридичні сторінки

`/privacy`, `/terms`, `/returns`, `/offer` — повний українськомовний текст з посиланнями на закони.

---

## 20. Безпека

SecurityHeaders (X-Content-Type-Options, X-Frame-Options, HSTS), CSRF, rate limiting, secure cookies, password validation (12+ chars), $fillable on all models, Policies on all CRUD.

---

## 21. Адмін-панель

**20+ ресурсів:** Product, Category, Order, User, Brand, Coupon, Review, Filter, FilterGroup, CustomerGroup, LoyaltyTier, LoyaltyTransaction, Payment, PaymentGatewaySettings, ShippingMethod, ShippingWarehouse, SeoMeta, FaqPage, DisplaySettings, ShopSettings, MegaMenu

**Кастомні сторінки:** Dashboard, HomepageBuilder, BatchEditor, IntegrationsPage, SeoManagement, ShippingDashboard, CacheManagement, MegaMenuEditor

**5 груп навігації:**
- **Каталог** (7 пунктів): Товари, Категорії, Бренди, Фільтри, Групи фільтрів, Опції, Варіанти
- **Продажі** (6 пунктів): Замовлення, Оплати, Купони, Групи клієнтів, Рівні лояльності, Транзакції лояльності
- **Доставка та оплата** (5 пунктів): Методи доставки, Склади, Налаштування оплати, Доставка Dashboard
- **Контент та SEO** (9 пунктів): Сторінки, Відгуки, FAQ, SEO мета, SEO управління, Mega Menu Editor, Конструктор головної, Налаштування вигляду, Налаштування магазину
- **Система** (4 пункти): Користувачі, Пакетний редактор, Інтеграції, Кеш

---

## 22. CI/CD, тести, Docker

**GitHub Actions:** test (PHP 8.3 + MySQL + Redis) + build (Node.js 20)

**Тести:** AuthTest, CheckoutTest, LoyaltyServiceTest, PricingServiceTest

**Docker:** app + MySQL 8 + Redis 7 + Meilisearch + queue + scheduler

**Cron:** loyalty:expire-points (02:00), loyalty:birthday-bonuses (08:00), loyalty:recalculate-tiers (нд 03:00), checkbox:open-shift (08:00), checkbox:close-shift (23:00), feeds:generate (04:00)

**Тестові акаунти:** admin@mail.com / 123456 | user@test.com / password
