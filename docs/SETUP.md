# Встановлення та запуск SimpleShop

## Вимоги

| Компонент | Версія |
|-----------|--------|
| Docker | 24+ |
| Docker Compose | v2.20+ |
| PHP | 8.3 (лише для локальної розробки без Docker) |
| Node.js | 20+ (лише для збірки фронтенду без Docker) |

---

## Швидкий старт з Docker

### 1. Клонування репозиторію

```bash
git clone <repository-url>
cd simpleshop
```

### 2. Налаштування .env

```bash
cp .env.example .env
```

Відредагувати `.env` для роботи з Docker:

```dotenv
APP_URL=http://localhost:8088

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=simpleshop
DB_USERNAME=simpleshop
DB_PASSWORD=SimpleShop2025!

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=Redis2025!
REDIS_PORT=6379
```

### 3. Збірка Docker образу

```bash
DOCKER_BUILDKIT=0 docker build -t simpleshop:local .
```

> Прапор `DOCKER_BUILDKIT=0` необхідний, оскільки `pecl install redis` може зависати у BuildKit-режимі.

### 4. Запуск контейнерів

```bash
docker compose -f docker-compose.coolify-final.yml up -d
```

Запускаються чотири сервіси: `app` (Nginx + PHP-FPM, порт 8088), `mysql`, `redis`, `queue` та `scheduler`.

### 5. Встановлення залежностей та міграції

```bash
docker exec simpleshop-app composer install
docker exec simpleshop-app php artisan key:generate
docker exec simpleshop-app php artisan migrate:fresh --seed --force
docker exec simpleshop-app php artisan storage:link
```

### 6. Доступ

| Ресурс | URL |
|--------|-----|
| Сайт | http://localhost:8088 |
| Адмін-панель | http://localhost:8088/admin |

---

## Тестові акаунти

| Роль | Email | Пароль |
|------|-------|--------|
| Адміністратор | admin@mail.com | 123456 |
| Звичайний клієнт | user@test.com | password |

---

## Перевірка функціоналу

### Фронтенд і каталог

1. Відкрити `http://localhost:8088` — головна сторінка з hero-блоком та акційними товарами.
2. Навести курсор на **КАТАЛОГ** у шапці — з'являється mega menu з категоріями.
3. Горизонтальне меню під шапкою — категорії верхнього рівня (Електроніка, Одяг, Дім і сад...).
4. Клік на категорію — список товарів з фільтрами в сайдбарі та сортуванням.
5. `/search` — пошук товарів за назвою.
6. `/brands` — список усіх брендів.
7. `/specials`, `/hits`, `/new` — спеціальні добірки товарів.

### Авторизація

1. `/login` — вхід як `admin@mail.com` / `123456`.
2. `/register` — реєстрація нового акаунту.
3. Після входу в шапці з'являється **АККАУНТ** з dropdown-меню.

### Особистий кабінет

1. `/account` — дашборд зі статистикою: кількість замовлень, сума витрат, кількість балів, поточний рівень лояльності.
2. `/orders` — список замовлень із фільтром по статусу; кнопка "Повторити замовлення".
3. `/wishlist` — список бажань (додати товари через кнопку на картці).
4. `/addresses` — адресна книга: створення, редагування, видалення адрес; позначка "за замовчуванням".
5. `/loyalty` — поточний рівень, прогрес до наступного, таблиця транзакцій, опис усіх рівнів.
6. `/settings` — зміна телефону, дати народження, аватару, пароля та налаштувань сповіщень.

### Система лояльності (адмін)

1. Увійти в `/admin` як `admin@mail.com` / `123456`.
2. **Управління магазином → Рівні лояльності** — перегляд чотирьох рівнів із множниками балів.
3. **Замовлення та користувачі → Транзакції балів** — повна історія нарахувань і списань.
4. У списку транзакцій — кнопка **Коригувати бали**: ручне нарахування або списання для будь-якого користувача.
5. **Користувачі → Edit** — поля `customer_group_id`, `loyalty_tier`, `loyalty_points`.

### Гуртові ціни та групи клієнтів

1. `/admin` → **Управління магазином → Групи клієнтів** — чотири групи з відсотком знижки.
2. Відкрити групу → вкладка **Users** — прив'язати користувача до групи.
3. **Товари → Edit** → поле **Мінімальна кількість для гурту** (`wholesale_min_quantity`).
4. **Товари → Edit** → RelationManager **Групові ціни** — встановити індивідуальну ціну для групи.
5. На фронтенді залогінитись як користувач з присвоєною групою — перевірити зміну ціни товару.

### Checkout і оплата

1. На сторінці товару — натиснути **Додати в кошик**.
2. `/checkout` — форма з полями: контактні дані, адреса доставки, служба доставки, спосіб оплати.
3. Поле **Купон** — ввести код зі списку нижче для отримання знижки.
4. Оформити замовлення — відбувається перенаправлення на `/orders/{id}/success`.

### Webhook-маршрути (для тестування платежів)

| Шлюз | URL |
|------|-----|
| LiqPay | `POST /webhooks/liqpay` |
| WayForPay | `POST /webhooks/wayforpay` |
| Monobank | `POST /webhooks/monobank` |

---

## Корисні artisan-команди

```bash
# Лояльність
php artisan loyalty:expire-points        # Списати прострочені бали
php artisan loyalty:birthday-bonuses     # Нарахувати birthday-бонуси
php artisan loyalty:recalculate-tiers    # Перерахувати рівні лояльності

# База даних
php artisan migrate:fresh --seed --force # Повний скид БД з demo-даними
php artisan db:seed --class=TestOrdersSeeder  # Лише тестові замовлення

# Кеш
php artisan cache:clear                  # Очистити кеш
php artisan config:cache                 # Кешувати конфігурацію
php artisan route:cache                  # Кешувати маршрути

# Черга та планувальник (у production — через Docker)
php artisan queue:work --tries=3
php artisan schedule:run
```

---

## Демо-дані (після `migrate:fresh --seed`)

| Сутність | Приблизна кількість |
|----------|-------------------|
| Користувачі | ~10 |
| Категорії | 44 |
| Товари | 377 |
| Замовлення | 60 |
| Відгуки | ~2800 |
| Групи клієнтів | 4 |
| Рівні лояльності | 4 |
| Купони | 5 |
| Способи доставки | 4+ |
| Медіафайли | ~377 |
| SEO-записи | ~10 |

---

## Зупинка

```bash
docker compose -f docker-compose.coolify-final.yml down
```

Зупинити та видалити тома з даними (повне очищення):

```bash
docker compose -f docker-compose.coolify-final.yml down -v
```

---

## Локальна розробка без Docker

```bash
# Встановити залежності
composer install
npm install

# Налаштувати .env (DB_CONNECTION=sqlite або локальний MySQL)
cp .env.example .env
php artisan key:generate

# Міграції та seed
php artisan migrate:fresh --seed

# Запустити всі процеси одночасно
composer run dev
```

Команда `composer run dev` запускає паралельно: `php artisan serve`, `queue:listen`, `pail` (логи), `npm run dev` (Vite).

---

## Troubleshooting

**403 на `/admin`**
Користувач повинен мати `is_admin = 1` у таблиці `users`. Перевірити в Tinker:
```bash
docker exec simpleshop-app php artisan tinker
>>> \App\Models\User::where('email','admin@mail.com')->first()->is_admin
```

**500 на головній сторінці**
```bash
docker exec simpleshop-app tail -n 50 storage/logs/laravel.log
```

**Redis: could not connect**
Перевірити, що Redis-контейнер здоровий і `REDIS_HOST=redis` встановлено в `.env`. Образ встановлює `pecl install redis` — якщо розширення відсутнє, зібрати образ заново з `DOCKER_BUILDKIT=0`.

**Міграції падають з `SQLSTATE[HY000]`**
MySQL-контейнер ще не готовий. Почекати, поки `healthcheck` поверне `healthy`:
```bash
docker compose -f docker-compose.coolify-final.yml ps
```

**Зображення не відображаються**
```bash
docker exec simpleshop-app php artisan storage:link
```

**Черга не обробляє задачі**
Перевірити контейнер `queue`:
```bash
docker compose -f docker-compose.coolify-final.yml logs queue
```
