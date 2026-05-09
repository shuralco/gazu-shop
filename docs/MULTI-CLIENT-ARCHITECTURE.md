# Multi-client architecture

> Як розробляти в одному репо й виокремлювати клієнтів: дизайни, модулі, права доступу. Модель — **один core, N клієнтів**.

---

## 1. Сутності

```
┌──────────────────────────────────────────────────────────┐
│ CORE (один спільний репозиторій, всі розробляють тут)    │
│ • app/Models  • app/Services  • app/Livewire  • Filament │
│ • migrations  • routes  • base layouts                   │
│ Не змінюється під клієнта. Update через git pull.        │
└──────────────────────────────────────────────────────────┘
        │
        ├── overlay 1 ──► ТЕМА (per-client visual identity)
        │   • resources/css/tokens/{client}.css
        │   • (опц.) resources/views/themes/{client}/ overrides
        │
        ├── overlay 2 ──► МОДУЛІ (опціональні фічі-пакети)
        │   • Loyalty, MultiWarehouse, AutoParts, Wholesale, etc.
        │   • вкл/викл через config('modules.{name}.enabled')
        │   • кожен модуль ховає свої admin pages, frontend widgets,
        │     CLI commands, scheduled jobs коли disabled
        │
        ├── overlay 3 ──► CLIENT PROFILE (.env або client.php)
        │   • API keys (NP, UP, LiqPay, Telegram)
        │   • DB credentials (per-tenant DB) АБО tenant_id (shared DB)
        │   • Domain / subdomain
        │   • Active theme name
        │   • Active modules list
        │   • Shop name, contacts, default warehouse
        │
        └── overlay 4 ──► ПРАВА ДОСТУПУ
            • Якщо клієнт не платить — `php artisan module:disable loyalty`
              автоматично прибирає UI, скидає міграції в "soft-disabled" state
              (дані лишаються, але feature заблокована)
```

---

## 2. Три практичні моделі деплою

### Модель A: Окремий деплой на клієнта (рекомендовано)

**Один core repo → N окремих deploy із власним .env**

```
shop1.com  ──► simpleshop:v1.42 ──► .env_shop1   ──► DB shop1
shop2.com  ──► simpleshop:v1.42 ──► .env_shop2   ──► DB shop2
shop3.com  ──► simpleshop:v1.42 ──► .env_shop3   ──► DB shop3
```

| Pros | Cons |
|---|---|
| Повна ізоляція (дані, кеш, queue) | N серверів/контейнерів |
| Деплой одного клієнта не зачіпає інших | N разів запускати міграції |
| API ключі ізольовані | Інфра-тенгер |
| Простий бекап на клієнта | |

Update workflow: `git pull && composer install --no-dev && php artisan migrate && php artisan modules:resync && npm run build && supervisord restart` — однаково на всіх інстанціях.

### Модель B: Один deploy, кілька doменів (multi-tenant shared DB)

**Один процес обслуговує всі домени, ідентифікація через `Host`**

Потрібно:
- `tenant_id` колонка скрізь де є дані
- Middleware `IdentifyTenant` ставить `tenant()->id`
- Кожен Eloquent-запит фільтрується (TenantScope global scope)

| Pros | Cons |
|---|---|
| Один deploy — один update | Складніше тестувати |
| Дешевше інфра | Ризик "leak" даних між клієнтами |
| Спільний кеш Redis | Складна аналітика per-tenant |

Для SimpleShop поки **не реалізовано** — можна додати через пакет `stancl/tenancy` коли буде ≥10 клієнтів.

### Модель C: Hybrid — субдомен → окрема DB

Один процес, але DB-connection вибирається динамічно за `Host`.

```
client1.shop.com ──┐
client2.shop.com ──┼──► simpleshop:v1.42 ──► (db лучшается з tenants config)
client3.shop.com ──┘
```

Менше деплою, ізольовані дані. Реалізовується через `php artisan tenants:run`.

---

## 3. Module system — головна абстракція

### Концепт

**Module** = пакет фічі (моделі + сервіси + Filament-ресурси + frontend-компоненти + CLI + scheduled jobs), який вмикається/вимикається одним прапорцем.

```php
// config/modules.php
return [
    'loyalty' => [
        'name' => 'Програма лояльності',
        'description' => 'Бонусні бали + рівні + транзакції',
        'enabled' => env('MODULE_LOYALTY', true),
        'requires' => [],         // інші модулі що мають бути активні
    ],

    'multi_warehouse' => [
        'name' => 'Декілька складів',
        'description' => 'Multi-warehouse + transfers + receiving',
        'enabled' => env('MODULE_MULTI_WAREHOUSE', true),
        'requires' => [],
    ],

    'auto_parts' => [
        'name' => 'Auto-parts catalog seeder',
        'description' => 'Demo каталог автозапчастин',
        'enabled' => env('MODULE_AUTO_PARTS', false),  // off за замовчуванням
        'requires' => ['multi_warehouse'],
    ],

    'wholesale' => [
        'name' => 'Гуртові ціни',
        'description' => 'Customer groups + group prices',
        'enabled' => env('MODULE_WHOLESALE', true),
        'requires' => [],
    ],

    'loyalty_referrals' => [
        'name' => 'Реферальна програма',
        'description' => 'Запрошувальні коди + бонуси',
        'enabled' => env('MODULE_REFERRALS', false),  // платний модуль
        'requires' => ['loyalty'],
    ],
];
```

### Як модуль "ховається" коли disabled

#### a) Filament resources / pages
```php
class LoyaltyTransactionResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return module('loyalty')->enabled();
    }

    public static function canAccess(): bool
    {
        return module('loyalty')->enabled();
    }
}
```

#### b) Frontend (Livewire / blade)
```blade
@if(module('loyalty')->enabled())
    <livewire:loyalty.balance-widget />
@endif
```

#### c) Scheduled jobs
```php
// app/Console/Kernel.php
$schedule->command('loyalty:expire-points')
    ->dailyAt('03:00')
    ->when(fn () => module('loyalty')->enabled());
```

#### d) Routes
```php
Route::middleware('module:loyalty')->group(function () {
    Route::get('/cabinet/loyalty', LoyaltyController::class);
});
```

#### e) Migrations
**НЕ скидати**. Дані клієнта залишаються — раптом він заплатить й хоче історію назад.

### Workflow коли клієнт не заплатив

```bash
# Адмін магазину пропускає платіж
php artisan module:disable loyalty
# → MODULE_LOYALTY=false у .env
# → cache:clear
# → Filament navigation: «Транзакції балів», «Рівні лояльності» зникли
# → Frontend cabinet: вкладка "Бали" зникла
# → Livewire-балансу не рендериться
# → CLI loyalty:expire-points не запускається
# → Дані БД: лишаються

# Клієнт заплатив
php artisan module:enable loyalty
# → все знову на місці без втрати даних
```

---

## 4. Theme overlay system

### Поточний стан (вже реалізовано)

- `resources/css/tokens/{theme}.css` — кольори, радіуси, тіні, типографіка
- `<x-ui.*>` компоненти споживають токени
- `php artisan theme:use {name}` + admin UI `/admin/theme-settings`
- Theme swap = swap токенів + `npm run build`

### Розширення для view overrides (опц., якщо потрібно більше)

```
resources/views/themes/
  ├── default/      ← fallback (= поточний resources/views/)
  ├── auto-parts/
  │   └── livewire/product/product-component.blade.php   ← override
  └── fashion/
      └── components/header/main-header.blade.php
```

`AppServiceProvider`:
```php
public function boot()
{
    $theme = config('shop.theme', 'default');
    if ($theme !== 'default' && File::exists(resource_path("views/themes/{$theme}"))) {
        View::prependLocation(resource_path("views/themes/{$theme}"));
    }
}
```

Blade resolver спочатку шукає у `themes/{active}/`, fallback на `resources/views/`.

---

## 5. Client Profile

### Структура
```
clients/
  ├── default.env       (template)
  ├── shop1.env
  ├── shop2.env
  └── shop3.env
```

Кожен `.env` містить:
- API ключі (NP, UP, LiqPay, ...)
- `APP_URL`, `APP_THEME`, `APP_NAME`
- `DB_DATABASE`, `DB_USERNAME`
- `MODULE_*` прапорці
- `MAIL_*` SMTP

### Деплой
```bash
# на новий клієнт
git clone <repo> /var/www/shop1
cd /var/www/shop1
ln -s clients/shop1.env .env
php artisan migrate
php artisan shop:init --non-interactive ...
php artisan module:enable multi_warehouse loyalty wholesale
npm run build
```

### CLI swap між профілями
```bash
php artisan client:use shop1
# Симлінкує clients/shop1.env → .env, перебудовує cache
```

---

## 6. Платіжний (subscription) gating

### Опція A: ручний контроль
Адмін агентства запускає `module:disable {name}` коли клієнт пропустив платіж.

### Опція B: cron + DB
```php
Schedule::command('subscriptions:check')->daily();

// app/Console/Commands/SubscriptionsCheck.php
public function handle()
{
    foreach (config('modules') as $key => $cfg) {
        if (! $cfg['paid_until'] ?? null) continue;
        if (Carbon::parse($cfg['paid_until'])->isPast()) {
            $this->call('module:disable', ['name' => $key]);
        }
    }
}
```

### Опція C: webhook from billing system
Stripe / WayForPay webhook → `module:enable/disable`.

---

## 7. Рекомендована стартова реалізація

Для SimpleShop достатньо **Модель A** (per-client deploy) + module system.

### Що зробити зараз
1. ✅ ModuleManager service + `module()` helper
2. ✅ `config/modules.php` registry з 4-5 модулями
3. ✅ `php artisan module:enable/disable/list` commands
4. ✅ Filament admin для перегляду статусу всіх модулів
5. ✅ Wrap 1-2 існуючих фіч як модулі (Loyalty, MultiWarehouse) як референс
6. ⏳ Решта існуючих опціональних фіч (Wholesale, Comparison, Coupons) — поступово
7. ⏳ Theme view overlays — коли потрібно більше ніж токени

### Що відкласти
- Multi-tenant shared DB (Modell B) — якщо клієнтів буде ≥10
- Subdomain routing — окрема задача
- Subscription billing automation — opcionalno

---

## 8. Файлова структура

```
config/
  ├── modules.php              ← module registry
  └── shop.php                 ← active theme, shop identity

app/
  ├── Modules/
  │   ├── Module.php           ← base class
  │   └── ModuleManager.php    ← service
  ├── Console/Commands/
  │   ├── ModuleEnableCommand.php
  │   ├── ModuleDisableCommand.php
  │   └── ModuleListCommand.php
  └── Helpers/
      └── module.php           ← module('name') helper

resources/
  ├── css/tokens/{theme}.css   ← кольори/радіуси/тіні
  └── views/themes/{theme}/    ← (опц.) view overrides

clients/                       ← per-client .env files
docs/MULTI-CLIENT-ARCHITECTURE.md  ← цей файл
```

---

## 9. Чеклист готовності клієнта

Перед запуском нового клієнта:
- [ ] Окремий `.env` з його API ключами
- [ ] Окрема DB (або tenant_id у shared DB)
- [ ] `php artisan module:enable {paid modules}`
- [ ] `php artisan theme:use {client-theme}`
- [ ] `php artisan shop:init --non-interactive ...`
- [ ] DNS налаштовано
- [ ] SSL cert
- [ ] Backup policy
- [ ] Договір зберігає список увімкнених модулів та umowлений плата
- [ ] Контакти (телефон, email) у `header_*` settings

---

## 10. Цей документ оновлюється коли:

- Додається новий модуль (запис в `config/modules.php`)
- Змінюється спосіб toggling (новий middleware/scope)
- Появляється новий клієнтський сценарій (drop-ship, marketplace, B2B-only)

Тримай актуальним поряд з `docs/CHANGELOG-FEATURES.md`.
