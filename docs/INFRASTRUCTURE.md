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

Через `validate_timestamps=0` + Octane in-memory:

| Тип зміни | Що достатньо | Чому |
|---|---|---|
| **PHP-код** (класи, провайдери, конфіг, module.json) | `docker cp` → **`docker restart <container>`** | і OPcache (timestamps off), і воркери Octane тримають старе в памʼяті. |
| **Blade / view** | `docker cp` → `php artisan view:clear` (рестарт не обовʼязковий) | blade компілюється в кеш, який можна скинути. |
| **Статика / public-asset** (svg, png) | `docker cp` → готово | віддає nginx напряму. |
| **CSS/JS через Vite** | новий бандл (хеш) → `docker cp build/` → **`docker restart`** | Vite-manifest кешується в памʼяті воркера → без рестарту HTML тягне старий хеш. |
| **Вмикання/вимикання модуля** | зміна в БД → **`docker restart`** | роути/Filament-панель реєструються лише при boot воркера. |
| **Чистий повний деплой** | Coolify `deploy` (за потреби `force_rebuild:true`) | пересборка образу + `composer install` (оновлює autoload) + `npm build`. |

### ⚠️ Заборонено / не працює
- ❌ `pkill swoole_http_server` — вбиває воркери, master лишається без них → **сайт падає (HTTP 000)**.
- ❌ `php artisan octane:reload` — на цьому setup кидає `Undefined array key "rpcPort"` (баг ServerProcessInspector). **Не використовувати** — замість нього `docker restart`.
- ❌ `php -r opcache_reset()` у CLI — це окремий CLI-OPcache, на воркери Swoole не впливає.

---

## 7. Знайдені проблеми / рекомендації

1. **JIT вимкнено попри `jit=tracing`** — `jit_buffer_size=0`.
   Якщо хочемо спробувати JIT: виставити `opcache.jit_buffer_size=64M` (+ `opcache.jit=1255`).
   *Застереження:* для типового web-навантаження Laravel JIT майже не дає виграшу (виграє CPU-bound код), тож це **низький пріоритет**. Без бенчмарку вмикати не варто.

2. **`octane:reload` зламаний (`rpcPort`)** — поки лагодити нема потреби (рестарт працює), але це причина, чому деплої роблять `docker restart` із коротким даунтаймом ~12–18 с. Якщо критично прибрати даунтайм — варто полагодити reload або перейти на graceful USR1.

3. **Вмикання модуля через UI не перереєстровує роути** до рестарту воркера (див. §2) — потенційна пастка для клієнта: «увімкнув модуль, а нічого не зʼявилось». Варто, щоб toggle модуля автоматично тригерив reload воркерів.

4. **`config/octane.php` каже `roadrunner`, а реально Swoole** — неузгодженість конфігу й факту. Не критично (старт явно задає `--server=swoole`), але краще вирівняти дефолт у конфізі на `swoole`, щоб не вводити в оману.

5. **Redis здоровий** — 0 evictions, 98% hit, 62% памʼяті. Дій не потрібно. Якщо ключів стане суттєво більше — підняти `maxmemory` або рознести cache/session по різних інстансах.

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

Стек налаштований правильно для продакшену. Головне операційне правило: **PHP-зміни та вмикання модулів вимагають `docker restart`** (через OPcache `validate_timestamps=0` + in-memory Octane).
