# GAZU — інфраструктура та продуктивність (аналіз)

> Аналіз живого продакшену **gazu.uno** (Coolify, Hetzner `23.88.115.55`).
> Зібрано з працюючого контейнера. Дата: 2026-06-12.
> Коротка відповідь: **OPcache — так, Redis — так, Octane(Swoole) — так.** Усі три активні й працюють.

---

## 1. Загальна схема

```
            ┌─────────────────────────── Docker-контейнер застосунку ───────────────────────────┐
Internet →  │  nginx (8 worker-процесів)  →  Octane / Swoole :8000  (PHP 8.3.31 + OPcache)       │
 (Coolify   │        ↑ статика, проксі            ├─ 8 × HTTP worker                              │
  proxy)    │                                     └─ 4 × task-worker                              │
            │  + queue:work (черга)   + scheduler (cron-loop)                                     │
            └───────────────┬───────────────────────────────────┬─────────────────────────────────┘
                            │                                   │
                   ┌────────▼────────┐                 ┌────────▼────────┐
                   │  Redis 7.2.14    │                 │  MySQL 8         │
                   │  cache+session+  │                 │  основні дані    │
                   │  queue+respcache │                 └─────────────────┘
                   └─────────────────┘
```

- **Середовище:** `APP_ENV=production`, `APP_DEBUG=false` ✅
- **PHP:** 8.3.31, `memory_limit=512M`
- **Розширення:** Swoole `6.2.1`, phpredis `6.3.0`

---

## 2. Octane (Swoole) — ✅ працює

Реальна команда запуску (з `supervisord.conf`):

```
php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000 \
    --workers=8 --task-workers=4 --max-requests=1000
```

| Параметр | Значення | Що означає |
|---|---|---|
| server | **swoole** | Хоча `config/octane.php` за замовчуванням `roadrunner`, **реально запущено Swoole** (явний `--server=swoole`). У контейнері видно `swoole_http_server: master/manager/worker`. |
| workers | **8** | 8 процесів обробляють HTTP паралельно. |
| task-workers | **4** | окремі воркери під фонові task (`Octane::concurrently`, dispatch). |
| max-requests | **1000** | кожен воркер сам перезапускається після 1000 запитів → захист від витоків памʼяті. |
| перед Octane | **nginx** (8 worker) | віддає статику й проксує динаміку на `:8000`. |

### Наслідки (важливо розуміти)
Octane тримає **завантажений PHP-застосунок у памʼяті воркерів між запитами** (на відміну від php-fpm, де все вмирає після кожного запиту). Це дає швидкість, але:

- **Зміни PHP-коду НЕ підхоплюються автоматично** — воркери тримають старі класи в памʼяті. Потрібен **reload/restart** (див. §6).
- **Реєстрація роутів/панелі Filament відбувається 1 раз при boot воркера.** Якщо вмикаєш модуль через UI (пише в БД), роути зʼявляться лише після перезапуску воркерів. (Саме через це нещодавно «не відкривалось додавання авто» — модуль увімкнули після boot.)
- **Стан між запитами треба чистити** — статичні властивості, singletons можуть «протікати». Тут це враховано (`modules:state` кешується в Redis, не в статиці воркера).

---

## 3. OPcache — ✅ працює (production-режим)

| Директива | Значення | Коментар |
|---|---|---|
| `opcache.enable` | **1** | увімкнено у воркерах. |
| `opcache.enable_cli` | 0 | у CLI вимкнено (норма; тому `tinker` показує opcache-статус як null). |
| `opcache.validate_timestamps` | **0** | **ключове:** OPcache НЕ перевіряє чи змінився файл → не перечитує код з диска. Максимальна швидкість, але **зміни коду вимагають рестарту** (а не просто заливки файлу). |
| `opcache.memory_consumption` | **256 MB** | буфер байткоду. |
| `opcache.max_accelerated_files` | **20000** | стелаж під ~20k файлів (вистачає). |
| `opcache.interned_strings_buffer` | 16 MB | |
| `opcache.jit` | `tracing` | режим заданий, **АЛЕ…** |
| `opcache.jit_buffer_size` | **0** | …буфер 0 → **JIT фактично ВИМКНЕНО**. Див. §7 (рекомендація). |

> `validate_timestamps=0` + Octane in-memory = **подвійна причина**, чому hotfix PHP вимагає `docker restart`, а не лише `docker cp`.

---

## 4. Redis — ✅ працює (cache + session + queue + responsecache)

Окремий контейнер (`REDIS_HOST=aw4okk04…`), клієнт **phpredis**, пароль заданий.

```
CACHE_STORE=redis     SESSION_DRIVER=redis     QUEUE_CONNECTION=redis
responsecache: enabled=true, store=redis, lifetime=604800s (7 днів)
```

### Жива статистика (на момент аналізу)
| Метрика | Значення | Оцінка |
|---|---|---|
| версія | 7.2.14 | |
| `maxmemory` | **1.50 GB** | ліміт виставлено явно. |
| `maxmemory-policy` | **volatile-lru** | витісняє лише ключі з TTL — сесії/кеш, не «вічні» дані. |
| `used_memory` | 930 MB / 1.5 GB (peak 945 MB) | ~62% — є запас. |
| `evicted_keys` | **0** | памʼяті вистачає, нічого не витісняється. ✅ |
| `keyspace_hits / misses` | 6 735 293 / 114 001 | **hit-rate ≈ 98.3%** — відмінно. ✅ |
| `connected_clients` | 21 | |
| db0 | 1514 ключів (cache, короткий TTL) | префікс `gazu_database_gazu_cache_*` |
| db1 | 1492 ключі (session + responsecache, довгий TTL) | |

> Redis обслуговує 4 ролі одночасно: **кеш застосунку, сесії, чергу задач і повний HTTP-кеш сторінок (Spatie ResponseCache)**. Це і дає швидкий фронт.

---

## 5. Черга й планувальник — ✅ працюють

- **Queue worker:** `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` (драйвер redis).
  → черга **реально обробляється** окремим процесом (не `sync`). Задачі: листи, фонові експорти тощо.
- **Scheduler:** `while true; do php artisan schedule:run; sleep 60; done`
  → cron-задачі Laravel запускаються щохвилини (cron-loop замість системного crontab — нормально для контейнера).

---

## 6. Як правильно деплоїти / робити hotfix

> **Оновлено 2026-06-12:** `octane:reload` полагоджено + Coolify health-check
> увімкнено → деплої тепер фактично **zero-downtime** (див. §7).

**Золоте правило:** для повного деплою — **Coolify `deploy`** (rolling: новий
контейнер healthy → свап → старий стоп = свіжий код + свіжий OPcache, без
даунтайму). `docker restart` — лише крайній випадок (дає ~12–18 с даунтайму).

| Тип зміни | Рекомендований шлях | Чому |
|---|---|---|
| **PHP-код / конфіг / module.json** | `git push` → **Coolify `deploy`** (zero-downtime) | новий контейнер = свіжий OPcache (`validate_timestamps=0`) + новий autoload. Reload сам по собі НЕ підхопить PHP (opcache віддає старий байткод із SHM). |
| **Стан БД без зміни коду** (toggle модуля, налаштування) | зміна → **`octane:reload`** (zero-downtime) | воркери re-boot'яться й перечитують стан із БД. Код не змінився → opcache не заважає. |
| **Blade / view** | `docker cp` → `php artisan view:clear` | blade-кеш скидається, новий процес компілює заново. |
| **Статика / public-asset** (svg, png) | `docker cp` → готово | віддає nginx напряму. |
| **CSS/JS через Vite** | новий бандл → `docker cp build/` → **`octane:reload`** | reload скидає закешований Vite-manifest у памʼяті воркера. |

### `octane:reload` — тепер працює (zero-downtime)
```
php artisan octane:reload            # працює, бо config('octane.server')=swoole
php artisan octane:reload --server=swoole   # явно, на будь-який випадок
```
Надсилає SIGUSR1 swoole-майстру → graceful recycle воркерів (in-flight запити
доживають). Перевірено: сайт тримає 200 під час reload. **Важливо:** reload НЕ
оновлює PHP-код при `validate_timestamps=0` — для коду потрібен Coolify deploy
(свіжий контейнер) або `docker restart`.

### ⚠️ Заборонено / не варто
- ❌ `pkill swoole_http_server` — вбиває воркери, master лишається без них → **сайт падає (HTTP 000)**.
- ❌ `docker restart` як рутинний деплой — дає даунтайм; використовуй Coolify `deploy` (rolling).
- ❌ `php -r opcache_reset()` у CLI — це окремий CLI-OPcache, на воркери Swoole не впливає.

---

## 7. Статус проблем (що полагоджено 2026-06-12)

1. ✅ **`octane:reload` полагоджено** (commit `5986fe74`). Причина бага `Undefined
   array key "rpcPort"`: `octane:reload` обирає стратегію за `config('octane.server')`,
   а той був `roadrunner` → йшов RoadRunner-інспектором по swoole-стані. Фікс: дефолт
   `config/octane.php` → `swoole` + `OCTANE_SERVER=swoole` у Coolify env. Тепер reload
   працює zero-downtime (перевірено: 200 під час reload).

2. ✅ **Coolify zero-downtime увімкнено.** Раніше `health_check_enabled=false` →
   Coolify не вмів rolling. Увімкнув health-check (`GET / → 200`, start_period 30s).
   Тест деплою: **299/300 запитів = 200**, 1 короткий 502 у мить свапу Traefik
   (проти 12–18 с даунтайму при `docker restart`). Новий контейнер «healthy» перед
   свапом. *Лишається 1 блип на свапі Traefik — якщо потрібен абсолютний 0, налаштувати
   connection-draining у Traefik (низький пріоритет).*

3. ✅ **Toggle модуля авто-reload'ить воркери** (commit `5986fe74`). У
   `ModuleMarketplace`/`ModuleSettings` після зміни стану викликається
   `octane:reload --server=swoole`. Більше немає пастки «увімкнув модуль → 404 до
   рестарту» — роути зʼявляються одразу, без даунтайму.

4. ✅ **Конфіг вирівняно** — `config/octane.php` дефолт тепер `swoole` (відповідає факту).

5. ⏸️ **JIT вимкнено** (`jit=tracing`, `jit_buffer_size=0`) — **навмисно лишено**.
   Для web-навантаження Laravel JIT майже не дає виграшу (виграє CPU-bound код).
   Вмикати лише за наявності бенчмарку (`opcache.jit_buffer_size=64M`, `opcache.jit=1255`).

6. ✅ **Redis здоровий** — 0 evictions, 98% hit, 62% памʼяті. Дій не потрібно.

---

## 8. Підсумок (TL;DR)

| Шар | Стан | Деталі |
|---|---|---|
| **Octane (Swoole)** | ✅ працює | 8 workers + 4 task, max-requests=1000, за nginx |
| **OPcache** | ✅ працює | 256MB, `validate_timestamps=0` (prod), JIT де-факто off |
| **Redis** | ✅ працює | 1.5G/volatile-lru, 98% hit, 0 evict; cache+session+queue+responsecache |
| **Queue** | ✅ працює | `queue:work` redis (не sync) |
| **Scheduler** | ✅ працює | cron-loop щохвилини |
| **MySQL 8** | ✅ працює | основне сховище |
| **Env** | ✅ prod | `APP_DEBUG=false` |

Стек налаштований правильно для продакшену. Головне операційне правило (після фіксів 2026-06-12):
- **PHP-код/конфіг** → `git push` + **Coolify `deploy`** (zero-downtime, свіжий контейнер).
- **Стан БД / toggle модуля** → **`octane:reload`** (zero-downtime, без зміни коду).
- `docker restart` — лише крайній випадок (дає даунтайм).
