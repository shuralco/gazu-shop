# Module Installer — Complete Reference

> Як встановлювати, експортувати, оновлювати та видаляти модулі через UI або CLI.

## Зміст

1. [Як отримати модуль](#як-отримати-модуль)
2. [Install через UI](#install-через-ui)
3. [Install через CLI](#install-через-cli)
4. [Export modules](#export-modules)
5. [Update / Reinstall](#update--reinstall)
6. [Uninstall](#uninstall)
7. [Emergency safe-mode](#emergency-safe-mode)
8. [Compatibility matrix](#compatibility-matrix)
9. [Hook-events для tracking](#hook-events-для-tracking)
10. [Файл-структура архіву](#файл-структура-архіву)

---

## Як отримати модуль

3 джерела:

1. **Development**: розкласти папку у `modules/X/` напряму (для розробників)
2. **ZIP від іншого shop**: export через `module:export` → передати → import
3. **Marketplace** (планується)

---

## Install через UI

### Шаг 1: Відкрити інсталер

Перейти у `/admin/modules` → натиснути banner **«⬆ Встановити модуль з .zip»**.

### Шаг 2: Завантажити файл

- Drag-drop ZIP або клік «Choose File»
- Ліміт **10 MB**
- Допустимі layout-и:
  - `module.json` у root архіву
  - `module.json` у єдиній обгортковій папці (`my_module/module.json`)

### Шаг 3 (опц): Preview

Натисніть **«Preview»** — система парсить ZIP **без extract** і показує:
- Які таблиці буде створено (з `Schema::create()` regex)
- Які routes зареєструє
- Які Filament Resources додасть
- Які hooks буде слухати
- Якщо `requires_modules` — список залежностей

### Шаг 4: Confirm install

- ☑ Force-reinstall (overwrite existing) — auto-backup створиться
- Натисніть **«Встановити»**

**Що відбувається під капотом:**

1. `Hooks::do('module.installing', $filename, $force)`
2. Compatibility check (engine/PHP/Laravel/requires_modules)
3. Auto-backup існуючого модуля у `storage/app/backups/modules/{key}-{timestamp}.zip`
4. ZIP extract у `modules/{name}/`
5. `composer dump-autoload`
6. `view:clear` + `filament:cache-components`
7. `Hooks::do('module.installed', $name, $result)`

При failure — папка модуля видаляється (transactional rollback), fire `module.install_failed`.

### Шаг 5: Activate

Після install — модуль **disabled by default** (safety). Натисніть **«Увімкнути»**:

1. `preEnableCheck()` — health-gate
2. Auto-resolver recursive enables required deps
3. `Artisan::call('migrate', '--path=modules/X/database/migrations')`
4. `Lifecycle.install()` (first-time) або `upgrade(from, to)` → `boot()`
5. `installed_version` оновлюється
6. composer dump-autoload + кеші
7. `Hooks::do('module.enabled', $key, $report)`

Telegram notify (якщо `TELEGRAM_BOT_TOKEN` + `TELEGRAM_ADMIN_CHAT_ID` у .env).

---

## Install через CLI

WP-CLI style headless:

```bash
# Install + auto-enable за один крок
php artisan module:install /path/to/module.zip --enable

# Just install (manual enable later)
php artisan module:install /path/to/module.zip

# Reinstall existing module (overwrite)
php artisan module:install /path/to/new-version.zip --force

# Combine
php artisan module:install /path/to/module.zip --force --enable
```

Useful для:
- Headless deploy (Ansible/CI/CD pipeline)
- Bulk install кількох модулів
- Recovery з backup ZIP

### Preview перед install

```bash
php artisan module:preview /path/to/module.zip
```

Виведе:
```
Hello World v1.0.0
  key:     hello_world
  desc:    Простий приклад модуля

Routes registered:
  • GET /api/hello/ping

Service Providers:
  • Modules\HelloWorld\HelloWorldServiceProvider

Hook listeners:
  • product.page.variants

Requires modules: related_products
```

### Enable + disable

```bash
php artisan module:enable hello_world
php artisan module:enable mod1 mod2 mod3       # batch
php artisan module:disable hello_world
php artisan module:list                        # current state
```

---

## Export modules

Через UI: на кожній картці модуля кнопка **«.zip»** — клік → download.

Через CLI:
```bash
php artisan module:export related_products
# → exports to storage/app/tmp/modules/related_products-{timestamp}.zip

php artisan module:export related_products --out=/tmp/myzip.zip
# → custom path

php artisan module:export related_products --out=/backups
# → /backups/related_products-{timestamp}.zip
```

Exclude rules — `.git`, `.idea`, `node_modules`, `vendor`, `.DS_Store`.

---

## Update / Reinstall

Workflow:

```bash
# 1. (Optional) Backup current state
php artisan module:export related_products --out=/backups/

# 2. Install нову версію
php artisan module:install /path/to/new-version.zip --force

# 3. Toggle off → on для migrate upgrade()
php artisan module:disable related_products
php artisan module:enable related_products
```

Або через UI:
1. `/admin/modules` → Preview new ZIP
2. Force-reinstall checkbox + Install
3. Disable+Enable (це triggers Lifecycle.upgrade(oldVersion, newVersion))

**Auto-backup** — попередня версія автоматично архівується у `storage/app/backups/modules/`. Якщо нова версія зламана:

```bash
# Restore
php artisan module:install /var/www/html/storage/app/backups/modules/related_products-20260528-163505.zip --force
```

---

## Uninstall

**3 рівні видалення:**

### 1. Soft disable (default toggle)
```bash
php artisan module:disable hello_world
```
- Папка залишається
- Routes/views/Filament приховуються
- Дані в БД лишаються

### 2. Disable з rollback migrations
Через UI: натиснути «Вимкнути» → checkbox «☑ Скинути міграції (drop tables)»

Або CLI:
```bash
php artisan tinker --execute='\App\Support\Modules\ModuleLifecycleRunner::onDisable("hello_world", true);'
```
- Папка залишається
- `migrate:rollback` → drops tables
- `installed_version` обнуляється

### 3. Hard uninstall (видалити папку + дані)
```bash
# Soft (тільки папка)
php artisan module:uninstall hello_world --yes

# Hard (папка + drop tables + БД)
php artisan module:uninstall hello_world --purge --yes
```

**Guards:**
- Refuses коли модуль enabled (треба disable first)
- Refuses коли інші enabled модулі require цей

---

## Emergency safe-mode

Коли модуль ламає сайт настільки, що `/admin` теж не вантажиться:

### Опція А: CLI (якщо є SSH)

```bash
php artisan module:safe-mode --yes
```

Disable всі non-core модулі. Core (`multi_warehouse`) залишається.

### Опція Б: Web (без SSH)

URL з токеном:
```
GET /safe-mode?token={sha1(APP_KEY).substr(0,16)}
```

Token = `sha1(APP_KEY)` перші 16 символів — admin зі знанням `.env`.

Curl-shortcut:
```bash
TOKEN=$(php -r "echo substr(sha1(getenv('APP_KEY') ?: file_get_contents('.env')), 0, 16);")
curl "https://yourshop.example/safe-mode?token=$TOKEN"
```

### Що робить safe-mode

- Disable всі модулі окрім `core_modules` (зараз `['multi_warehouse']`)
- Не запускає lifecycle hooks (бо вони можуть бути broken)
- Усі artisan-calls у try/catch + fallback на raw DB query
- Throttle 10/min
- Показує success-сторінку з переліком вимкнутих модулів

---

## Compatibility matrix

Manifest fields що блокують install при невідповідності:

```json
{
    "engine": ">=2.0",      ← Версія SHOP runtime
    "php": ">=8.2",         ← PHP_VERSION
    "laravel": ">=12.0",    ← Laravel framework
    "requires_modules": ["multi_warehouse"]
}
```

Constraint syntax (composer-style):

| Operator | Example | Meaning |
|---|---|---|
| `>=` | `>=8.2` | greater-or-equal |
| `<=` | `<=8.4` | less-or-equal |
| `>` | `>8.0` | strictly greater |
| `<` | `<9.0` | strictly less |
| `=` | `=8.2.0` | exact |
| `^` | `^8.2` | caret — `>=8.2 <9.0` |
| `~` | `~8.2.5` | tilde — `>=8.2.5 <8.3.0` |
| `*` | `*` | any |

При failure:
```
Модуль не сумісний з цією системою:
  Engine 2.0.0 не відповідає вимозі '>=99.0'
  PHP 8.3.30 не відповідає вимозі '>=99.0'
```

Fire `module.install_failed` → Telegram alert.

---

## Hook-events для tracking

Built-in subscriber слухає всі lifecycle events і робить:

1. **Maintenance mode** (production-only): `artisan down --render=errors::503 --retry=10`
2. **Telegram alerts** (якщо TELEGRAM_BOT_TOKEN set)
3. **Local log** (info/warning/error per level)

Власні listener-и підпишіть у ServiceProvider.boot():

```php
use App\Support\Hooks;

Hooks::on('module.enabled', function ($key, $report) {
    AuditTrail::record('module.enabled', [
        'key' => $key,
        'version' => $report['to_version'],
        'actions' => $report['actions'],
    ]);
}, priority: 20, source: 'my_audit_module');

Hooks::on('module.install_failed', function ($filename, $info) {
    Mail::to('devops@example.com')->send(
        new ModuleInstallFailedMail($filename, $info['error'])
    );
}, priority: 10, source: 'my_notifications');
```

Повний список events → [MODULE-HOOKS.md](MODULE-HOOKS.md#module--theme-lifecycle).

---

## Файл-структура архіву

ZIP должен містити **щонайменше** `module.json` у root або у єдиній обгортковій папці.

Валідний приклад:
```
my_module.zip
├── module.json               ← REQUIRED у root
├── src/
│   ├── MyServiceProvider.php
│   └── Models/
├── database/migrations/
├── resources/views/
└── routes/web.php
```

Або з обгорткою (single root-folder):
```
my_module.zip
└── my_module/
    ├── module.json           ← REQUIRED у обгортковій папці
    ├── src/
    └── ...
```

`ModuleInstaller::detectZipPrefix()` strip-ає обгортку автоматично — extracted name береться з `module.json` field `name`, не з ZIP filename.

### Файли що **ігноруються** при extract

- `__MACOSX/*` — macOS metadata
- `*.DS_Store` — macOS Finder
- (Будь-який path з `..` — security guard)

### Файли що **ігноруються** при export

- `.git/`, `.idea/`, `.vscode/`
- `node_modules/`, `vendor/`
- `.DS_Store`

### Security

- Module name regex: `^[a-z][a-z0-9_]{1,40}$`
- Архів size limit: 10 MB
- Path-traversal guard: refuses entries з `..`
- Web `/safe-mode` throttled 10/min

---

## Reference

- `/admin/modules` — UI
- `/admin/modules/view?key=X` — деталі + health
- `app/Support/Modules/ModuleInstaller.php` — installer logic
- `app/Console/Commands/Module*.php` — CLI commands
- [MODULES.md](MODULES.md) — overall guide
- [MODULE-HOOKS.md](MODULE-HOOKS.md) — events reference
- [MODULE-AUDIT.md](MODULE-AUDIT.md) — план переносу
