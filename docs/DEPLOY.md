# Deploy guide — Coolify / Docker Compose

> Цільова інфраструктура: **Coolify** (Self-hosted PaaS на VPS) або straight Docker Compose. Тут — кроки для повного prod-deploy SimpleShop / GAZU. Тестова конфігурація зібрана у `docker-compose.coolify-final.yml` у GAZU-форку.

---

## 1. Стек

| Сервіс | Image | Призначення |
|---|---|---|
| `app` | `simpleshop:local` (built from `Dockerfile`) | nginx + php-fpm 8.3, FrankenPHP-style serve |
| `mysql` | `mysql:8.0` | Primary DB |
| `redis` | `redis:7-alpine` | Cache + sessions + queue |
| `meilisearch` | `getmeili/meilisearch:v1.6` | Product search |
| `queue` | `simpleshop:local` | `php artisan queue:work` for shipments + emails |
| `scheduler` | `simpleshop:local` | `php artisan schedule:run` loop for low-stock alerts + sitemap regen |

---

## 2. .env у production

Скопіюй `.env.example` → `.env` і заповни:

```bash
# Core
APP_NAME=GAZU                          # бренд клієнта
APP_ENV=production
APP_KEY=base64:...                     # php artisan key:generate
APP_URL=https://gazu.example.com
APP_DEBUG=false
APP_THEME=gazu                         # 'brutal' / 'gazu' / custom

# DB
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=gazu_shop
DB_USERNAME=simpleshop
DB_PASSWORD=...                        # сильний пароль!

# Sessions + cache + queue → Redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=...                     # окремий пароль для Redis

# Search
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=...

# === Multi-warehouse module flags ===
MODULE_MULTI_WAREHOUSE=true            # Phase 1-7
MODULE_LOYALTY=false                   # вимкни якщо клієнту не треба
MODULE_WHOLESALE=true
MODULE_NOVAPOSHTA=true
MODULE_UKRPOSHTA=true
MODULE_GAZU_GARAGE=false               # ON для auto-parts magazinів

# === Phase 6: trust proxies ===
# Coolify ставить тебе за Traefik → треба trust той proxy щоб
# request->ip() = real IP (для geo-detect closest warehouse).
# '*' OK коли єдиний публічний entrypoint — reverse proxy.
TRUSTED_PROXIES=*

# Logging — у prod пиши тільки error+
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

---

## 3. Coolify проєкт-конфіг

1. **New Resource → Docker Compose** → завантаж `docker-compose.coolify-final.yml` з repo.
2. **Environment Variables** → встав весь `.env` блок вище.
3. **Build pack** → Dockerfile (root).
4. **Domain** → `gazu.example.com` (Coolify авто-видасть Let's Encrypt cert через Traefik).
5. **Persistent storage** — три named volumes:
   - `mysql_data` → `/var/lib/mysql`
   - `redis_data` → `/data`
   - `meilisearch_data` → `/meili_data`
   - `app_storage` → `/var/www/html/storage/app` (uploads)
   - `app_logs` → `/var/www/html/storage/logs`

Coolify автоматично перетягне Traefik labels у compose і виставить TLS.

---

## 4. Перший запуск

Після того як стек піднявся:

```bash
# Migrate
docker compose exec app php artisan migrate --force

# Cache config + routes для prod
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Optimize composer (run inside container)
docker compose exec app composer install --no-dev --optimize-autoloader --classmap-authoritative

# Seed (опційно — demo-каталог)
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan db:seed --class=AutoPartsSeeder --force   # 38 auto-parts

# Створити admin-юзера
docker compose exec app php artisan tinker --execute='
\App\Models\User::create([
    "name"=>"Admin","email"=>"admin@gazu.com",
    "password"=>bcrypt("changeme123"),
    "is_admin"=>true,
]);'

# Створити перший склад через адмінку → /admin/merchant-warehouses
# Або через tinker:
docker compose exec app php artisan tinker --execute='
\App\Models\MerchantWarehouse::create([
    "code"=>"MAIN-01","name"=>"Головний склад","city"=>"Київ",
    "type"=>"own","is_active"=>true,"is_default"=>true,
    "delivery_eta"=>"1 день","shipping_cost"=>60,"free_shipping_threshold"=>2000,
    "latitude"=>50.4501,"longitude"=>30.5234,
]);'
```

---

## 5. Per-warehouse конфіг для replication

Якщо магазин має 4 склади (Київ / Львів / Дніпро / Харків) — повтори tinker-блок з лат/лонг та ставками. Або краще — `database/seeders/MerchantWarehousesSeeder.php` (TODO для коробки).

Для кожного складу у адмінці заповни:

1. **Основне** — code, name, type=own, manager, telephone.
2. **Адреса** — city, address, lat/lng, working_hours, **delivery_eta** («1 день»), **shipping_cost** (₴), **free_shipping_threshold** (₴ або порожньо).
3. **Нова Пошта sender** — `np_sender_ref`, `np_sender_city_ref`, `np_sender_warehouse_ref`, `np_contact_person_ref`, `np_sender_phone`. Звідси НП TTN-creator резолвить sender.
4. **Укрпошта sender** — `up_sender_uuid`, `up_sender_address_uuid`, `up_counterparty_token`, `up_ecom_bearer`.

---

## 6. Geo-detect (Phase 6) — production checklist

Без `TRUSTED_PROXIES=*` `request->ip()` повертатиме internal docker IP (172.20.x.x), і `WarehouseLocator` буде fallback'ити до default warehouse замість найближчого.

- [x] `.env`: `TRUSTED_PROXIES=*` (або список IP/CIDR proxy)
- [x] У `MerchantWarehouse` є `latitude` + `longitude` для всіх активних складів
- [x] ip-api.com доступний з production VPS (`curl http://ip-api.com/json/8.8.8.8`)
- [x] Cache::driver('redis') ('geo:ip:{IP}' зберігається 24h щоб не бити quota)

При high traffic (>40k unique IP per day) — підключи платний tier ip-api або кешуй у Redis з longer TTL. Або переходь на офлайн `geoip2/geoip2` з локальним MaxMind .mmdb file (не вимагає external API).

---

## 7. Schedule + queue (важливо)

`scheduler` контейнер вже у compose. Що він запускає:

- `low-stock:check` (щоранку 9:00) — email менеджеру при `quantity ≤ reorder_point`
- `np:fetch-cities` / `np:fetch-warehouses` (раз у тиждень) — оновлення кешу НП відділень
- `up:fetch-references` (раз у тиждень) — Укрпошта
- `sitemap:generate` (щодня)
- `feed:generate` (4 рази на день)

`queue` контейнер обробляє:

- `OrderClient` / `OrderManager` mail
- НП API виклики (createTtn, fetchTracking)
- УП API виклики
- LowStockAlertMail

Перевір що обидва контейнери `Up` (зелений у Coolify):

```bash
docker compose ps queue scheduler
```

---

## 8. Backups

Coolify може automated backup запланувати:

- **MySQL**: `docker compose exec mysql mysqldump -u root -p$MYSQL_ROOT_PASSWORD gazu_shop > backup-$(date +%F).sql`
- **storage/app**: tar з volume daily.

Recommended retention: 7 daily + 4 weekly.

---

## 9. Health check

`/up` — Laravel built-in health endpoint, повертає 200 якщо app boots.
`/admin/login` — 200 если Filament admin рендериться.
`/feed/google.xml` — 200 якщо feed-generator working.

Coolify авто-моніторить через `healthcheck` блок з compose. При 3 fails підряд — перезапуск.

---

## 10. Smoke test після першого deploy

```bash
# Smoke (substitute domain)
DOMAIN=https://gazu.example.com

curl -sS -o /dev/null -w "/                     %{http_code}\n" $DOMAIN/
curl -sS -o /dev/null -w "/catalog              %{http_code}\n" $DOMAIN/catalog
curl -sS -o /dev/null -w "/cart                 %{http_code}\n" $DOMAIN/cart
curl -sS -o /dev/null -w "/admin/login          %{http_code}\n" $DOMAIN/admin/login
curl -sS -o /dev/null -w "/feed/google.xml      %{http_code}\n" $DOMAIN/feed/google.xml
curl -sS -o /dev/null -w "/sitemap.xml          %{http_code}\n" $DOMAIN/sitemap.xml

# Geo-detect (з зовнішнього IP має preselect closest warehouse у селекторі)
curl -sS $DOMAIN/product/{slug} | grep -oE 'ближче вам' | head -1
```

Очікуваний вивід: `/` → 200/302, `/catalog` 200, `/cart` 200, `/admin/login` 200, `/feed/google.xml` 200 з XML.

---

## 11. Troubleshooting

| Симптом | Причина | Лікування |
|---|---|---|
| `/admin/login` показує 403 | юзер у session не is_admin | надай `is_admin=true` у БД |
| Geo selector завжди = default warehouse | proxy не trusted | `TRUSTED_PROXIES=*` у env |
| "Bus error" / SIGBUS на докер-host | OOM, замало RAM | bump VPS до 2GB+ або обмеж `php memory_limit` |
| /admin/products list повільний | відсутній eager-load | `php artisan optimize` + перевір N+1 у telescope |
| ipapi rate-limit | 45 req/min hit | подовжи `Cache::remember` з 24h до 7d, або купи тариф |
| TTN sender = global default | `merchant_warehouses.np_sender_ref` пустий | заповни у /admin/merchant-warehouses |

---

## 12. Окремий деплой GAZU-форку

GAZU — це окремий Git-repo (`/home/lionex/projects/gazu-shop`), синхронізується з upstream `simpleshop` через cherry-pick або rebase. У форку:

- Прибрано `/uk` Brutal storefront — root URL веде до GAZU storefront.
- Без префіксу `/gazu` — `/catalog`, `/cart`, `/checkout` напряму.
- Окрема БД `gazu_shop`, окремі volumes — не конфліктує з основним SimpleShop deploy.

```bash
# Push GAZU фркок на ваш GitHub
cd /home/lionex/projects/gazu-shop
git remote add origin git@github.com:youraccount/gazu-shop.git
git push -u origin main

# Coolify pulls звідти, deploy окремий проєкт.
```

---

## 13. Що читати далі

- `MULTI-WAREHOUSE.md` — поточний стан фічі (схема, моделі, services, API)
- `MARKETING.md` — позиціонування для замовника
- `INVENTORY-LOGIC.md` — invariants InventoryService
- `NOVA-POSHTA.md` / `NOVA-POSHTA-GAP-ANALYSIS.md` — інтеграція з НП
- `SETUP.md` — local dev setup
