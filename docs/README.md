# SimpleShop — Український E-Commerce

## Огляд

SimpleShop — повнофункціональний інтернет-магазин, побудований на Laravel 12 + Filament 3. Проєкт реалізує весь стандартний цикл роздрібної та оптової торгівлі: каталог із ієрархічними категоріями, кошик і checkout, три платіжних шлюзи, чотири служби доставки, програму лояльності, гуртові ціни, купони та SEO. Адмін-панель повністю на Filament, фронтенд — Livewire 3 + Tailwind CSS 4.

---

## Стек технологій

| Компонент | Версія |
|-----------|--------|
| PHP | 8.3 |
| Laravel | 12 |
| Filament | 3 |
| Livewire | 3 |
| Tailwind CSS | 4 |
| MySQL | 8.0 |
| Redis | 7 |
| Nginx + PHP-FPM | Alpine-based Docker образ |
| Supervisor | Черга та планувальник у Docker |

---

## Функціонал

### Каталог товарів

- **Категорії** — ієрархічна структура з необмеженою глибиною, сортуванням, мета-полями та статусом активності.
- **Бренди** — окремі сторінки брендів з фільтрами.
- **Фільтри** — групи фільтрів, прив'язані до категорій і брендів; чекбокси та select на сторінці категорії.
- **Пошук** — окрема сторінка `/search` з пошуком по назві та опису.
- **Сортування та пагінація** — ціна зростання/спадання, новинки, популярні.
- **Спеціальні добірки** — `/specials`, `/hits`, `/new`.
- **Відгуки** — рейтинг і текст відгуків прив'язані до товару та користувача.

### Система замовлень

- **Кошик** — реалізований на Livewire, зберігається в сесії/БД.
- **Checkout** — форма з вибором адреси, служби доставки, способу оплати та полем для купону.
- **Платіжні шлюзи** — LiqPay, WayForPay, Monobank (кожен із webhook-обробником та sandbox-режимом).
- **Служби доставки** — Нова Пошта, УкрПошта, Meest Express, Rozetka Delivery.
- **Статуси замовлень** — відстеження через `Shipment` і `TrackingUpdate`.
- **Сторінка успішного замовлення** — `/orders/{order}/success` (GET + POST для webhook-редиректів).

### Особистий кабінет

| Маршрут | Компонент | Призначення |
|---------|-----------|-------------|
| `/account` | `AccountComponent` | Дашборд зі статистикою |
| `/orders` | `OrderComponent` | Список замовлень із фільтром по статусу |
| `/order-show/{id}` | `OrderShowComponent` | Деталі замовлення |
| `/wishlist` | `WishlistComponent` | Список бажань |
| `/addresses` | `AddressBookComponent` | Адресна книга (CRUD, адреса за замовчуванням) |
| `/loyalty` | `LoyaltyComponent` | Програма лояльності |
| `/settings` | `ProfileSettingsComponent` | Налаштування профілю |
| `/change-account` | `ChangeAccountComponent` | Зміна пошти/паролю |

### Система лояльності

- **4 рівні** — Бронзовий (x1.0), Срібний (x1.5), Золотий (x2.0), Платиновий (x3.0).
- **Нарахування балів** — `LoyaltyService::awardPoints()` після підтвердження замовлення; курс `loyalty_points_per_uah` налаштовується в адмін-панелі.
- **Списання балів** — `LoyaltyService::redeemPoints()` під час checkout.
- **Термін дії** — `loyalty_points_expiration_months` (за замовчуванням 12 місяців).
- **Birthday-бонус** — щоденна задача нараховує бонусні бали іменинникам.
- **Автоматичний перерахунок рівнів** — щотижнева задача переводить користувачів між рівнями на основі `total_spent`.
- **Модель `LoyaltyTransaction`** — фіксує тип (earned / redeemed / expired / adjusted / birthday), кількість балів, баланс після операції та `expires_at`.

### Гуртові ціни та групи клієнтів

- **4 групи** — Роздрібний (0%), Оптовий (5%), VIP (10%), Дистриб'ютор (15%).
- **Модель `ProductGroupPrice`** — індивідуальна ціна для конкретного товару та конкретної групи.
- **`wholesale_min_quantity`** — поле товару; гуртова ціна застосовується лише від цієї кількості.
- **`PricingService`** — вибирає найкращу з доступних цін: групова ціна → знижка групи → знижка рівня лояльності → роздрібна ціна.

### Купони та знижки

- Типи: процентна знижка, фіксована сума, безкоштовна доставка.
- `max_uses` — загальний ліміт активацій.
- `max_uses_per_user` — ліміт на одного користувача.
- Термін дії (`expires_at`).
- `CouponService` — перевірка валідності та застосування знижки.

### SEO

- **`SeoMeta`** — meta title, description, OpenGraph, JSON-LD; прив'язана до URL або типу сторінки.
- **`SeoMetaGenerator`** — сервіс автоматичної генерації мета-тегів для товарів і категорій.
- **Sitemap XML** — `/sitemap.xml` (індекс), `/sitemap-main.xml`, `/sitemap-categories.xml`, `/sitemap-products.xml`.
- **`robots.txt`** — динамічний, генерується з БД.

### Адмін-панель (Filament 3)

Адмін-панель розташована за адресою `/admin`. Доступна лише користувачам із `is_admin = 1`.

**Управління каталогом**

| Ресурс | Можливості |
|--------|-----------|
| `ProductResource` | Створення/редагування товарів, вкладка "Ціноутворення" (wholesale_min_quantity), RelationManager "Групові ціни" |
| `CategoryResource` | Ієрархія, SEO, сортування, статус |
| `BrandResource` | Сторінки брендів, фільтри |
| `FilterGroupResource` / `FilterResource` | Групи фільтрів та значення |

**Управління замовленнями та платежами**

| Ресурс | Можливості |
|--------|-----------|
| `OrderResource` | Список і деталі замовлень, зміна статусу |
| `PaymentResource` | Платежі та їх статуси |
| `PaymentGatewaySettingsResource` | Налаштування LiqPay / WayForPay / Monobank |

**Управління доставкою**

| Ресурс | Можливості |
|--------|-----------|
| `ShippingProviderResource` | Провайдери доставки |
| `ShippingMethodResource` | Методи доставки |
| `ShippingWarehouseResource` | Склади та відділення |

**Групи клієнтів та лояльність**

| Ресурс | Можливості |
|--------|-----------|
| `CustomerGroupResource` | Групи клієнтів, вкладка "Users" для прив'язки користувачів |
| `LoyaltyTierResource` | Рівні з множниками і кольором |
| `LoyaltyTransactionResource` | Всі транзакції балів, кнопка "Коригувати бали" |
| `UserResource` | Поля: `customer_group_id`, `loyalty_tier`, `loyalty_points` |

**Налаштування та SEO**

| Ресурс | Можливості |
|--------|-----------|
| `ShopSettingsResource` | Назва, логотип, контакти, налаштування лояльності |
| `DisplaySettingResource` | Hero, header, mega menu, горизонтальне меню |
| `MegaMenuResource` | Структура mega menu |
| `SeoMetaResource` | Meta-теги для сторінок |
| `CouponResource` | Купони та обмеження |
| `ReviewResource` | Модерація відгуків |
| `FaqPageResource` | Сторінки FAQ |

---

## Структура проєкту

```
simpleshop/
├── app/
│   ├── Filament/
│   │   └── Resources/          # 20+ ресурсів адмін-панелі
│   ├── Http/
│   │   └── Controllers/        # SitemapController, WebhookController
│   ├── Livewire/
│   │   ├── Cart/               # CheckoutComponent
│   │   ├── Order/              # OrderSuccessComponent
│   │   ├── Payment/            # PaymentMethodSelector
│   │   ├── Product/            # Category, Product, Brands, Search...
│   │   └── User/               # AccountComponent, AddressBook, Loyalty...
│   ├── Models/                 # 30 Eloquent моделей
│   ├── Services/
│   │   ├── Gateways/           # LiqPay, WayForPay, Monobank
│   │   ├── Shipping/           # NovaPoshta, UkrPoshta, Meest, Rozetka
│   │   ├── LoyaltyService.php
│   │   ├── PricingService.php
│   │   ├── WishlistService.php
│   │   ├── AddressService.php
│   │   ├── CouponService.php
│   │   ├── PaymentService.php
│   │   ├── SeoMetaGenerator.php
│   │   ├── HeaderService.php
│   │   ├── MenuBuilderService.php
│   │   └── CacheOptimizationService.php
│   └── Helpers/
│       └── shop_helpers.php    # shopSetting() та інші хелпери
├── database/
│   ├── migrations/             # 60+ міграцій
│   └── seeders/                # Повний набір seeders з demo-даними
├── resources/
│   └── views/                  # Blade шаблони + Livewire компоненти
├── routes/
│   ├── web.php                 # Публічні та авторизовані маршрути
│   └── admin.php               # Маршрути адмін-панелі
├── docker/
│   ├── nginx.conf
│   └── supervisord.conf
├── Dockerfile
└── docker-compose.coolify-final.yml
```

---

## API сервісів

### `LoyaltyService`

- `awardPoints(User, Order)` — нараховує бали після замовлення з урахуванням множника рівня.
- `redeemPoints(User, int)` — списує бали та повертає суму знижки в гривнях.
- `recalculateTier(User)` — переводить користувача на відповідний рівень.
- `expirePoints()` — списує прострочені транзакції (cron).
- `awardBirthdayBonus(User)` — нараховує бонус до дня народження.

### `PricingService`

- `getProductPrice(Product, ?User)` — повертає актуальну ціну товару для користувача (з урахуванням групи, рівня, wholesale).
- `getGroupDiscount(?User)` — відсоток знижки групи клієнта.
- `applyGroupDiscount(float, ?User)` — застосовує групову знижку до суми.
- `getBestDiscount(?User)` — максимальна знижка з групи або рівня лояльності.

### `WishlistService`

- `toggle(User, Product)` — додає або видаляє товар зі списку бажань.
- `getItems(User)` — повертає список товарів бажань.

### `AddressService`

- `create(User, array)` — створює адресу; якщо перша — автоматично стає адресою за замовчуванням.
- `setDefault(User, UserAddress)` — встановлює адресу як основну.
- `delete(UserAddress)` — видаляє; якщо була основною, призначає нову.

### `CouponService`

- `validate(string $code, User, float $total)` — перевіряє валідність купону.
- `apply(Coupon, float $total)` — розраховує знижку.
- `use(Coupon, User, Order)` — фіксує використання в `CouponUsage`.

### `PaymentService`

- `createPayment(Order, string $gateway)` — ініціює платіж через обраний шлюз.
- `handleWebhook(string $gateway, array $data)` — обробляє webhook від платіжного провайдера.
- Шлюзи: `LiqPayGateway`, `WayForPayGateway`, `MonobankGateway`.

---

## Cron-задачі

Планувальник запускається через окремий Docker-контейнер `scheduler` (виклик `php artisan schedule:run` кожну хвилину).

| Команда | Розклад | Призначення |
|---------|---------|-------------|
| `loyalty:expire-points` | Щодня о 02:00 | Списати транзакції з `expires_at` у минулому |
| `loyalty:birthday-bonuses` | Щодня о 08:00 | Нарахувати бонус користувачам-іменинникам |
| `loyalty:recalculate-tiers` | Щонеділі о 03:00 | Перерахувати рівні лояльності на основі `total_spent` |
