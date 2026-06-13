# Теми GAZU — як створювати

Тема = **візуальна особистість** вітрини (кольори, заокруглення, шрифти). Бізнес-логіка
(модулі) від теми не залежить — будь-який модуль працює під будь-якою темою.

Є **три рівні** кастомізації:

| Рівень | Що міняє | Потрібен `npm run build`? | Складність |
|---|---|---|---|
| **1. Токени теми** (рекомендований) | Кольори + заокруглення + шрифти — через один `theme.json` | **НІ** (рантайм-інжекція) | тривіальна |
| **2. Глибока тема** | Власний CSS-entry, перевизначення blade-шаблонів, розкладка | так | висока |
| **3. Кешування** (за потреби) | Своя кеш-інтеграція: теги, виключення шляхів, `url()`-контракт | ні (config) | середня |

> Для «натягування дизайну» (зокрема згенерованого Claude) майже завжди достатньо **Рівня 1** —
> один файл, миттєвий ре-скін без перебудови. Рівень 3 потрібен лише якщо тема має іншу
> структуру сторінок/роутів і кеш має інвалідуватись по-своєму.

---

## Рівень 1 — Тема через `theme.json` (швидкий шлях)

### Як це працює

1. Активна тема зберігається в БД: `DisplaySetting('active_theme')`.
2. Layout вітрини інжектить у `<head>` `<style>` з токенами активної теми
   (`ThemeManager::cssVarOverrides()`), **після** зібраного CSS.
3. Уся вітрина стилізована через CSS-змінні (`var(--gazu-*)`, Tailwind `bg-gazu-*`,
   `rounded-*`, шрифти) → пізніший override цих змінних **перефарбовує наживо, без білда**.
4. Зміна теми в адмінці автоматично скидає кеш вітрини (Spatie ResponseCache).

### Кроки створення

```bash
# 1. Скопіювати дефолтну тему (або шаблон)
cp -r themes/gazu themes/my-shop          # або: cp -r themes/_template themes/my-shop

# 2. У themes/my-shop/theme.json змінити name + label + значення tokens/radii/fonts.
#    css_entry ЛИШИТИ на themes/gazu/resources/css/gazu.css (спільна збірка).

# 3. Готово. Тема зʼявиться в /admin/theme-settings → «Активувати».
```

Жодного `npm run build`, редагування `vite.config.js` чи `app.css` — **не треба**.

> 📋 Готовий шаблон з коментарями до кожного ключа: **`themes/_template/theme.json`**
> (теки з префіксом `_` дискавері ігнорує — це чистий зразок, у перемикач не потрапляє).

### Контракт токенів `theme.json`

```jsonc
{
  "name": "my-shop",                         // унікальний slug = імʼя теки
  "label": "Мій магазин",                    // назва в адмінці
  "description": "...",
  "version": "1.0.0",
  "css_entry": "themes/gazu/resources/css/gazu.css",   // лишай спільну збірку

  "tokens": { ...кольори... },               // → --gazu-<key> + --color-gazu-<key>
  "radii":  { ...заокруглення... },          // → Tailwind --radius-<key>
  "fonts":  { ...шрифти... },                // → --gazu-font-<key> + --font-<key>
  "font_links": [ "https://fonts.googleapis.com/..." ]  // зовнішні шрифти (опц.)
}
```

#### `tokens` — кольори (обовʼязкові)

| Ключ | Семантика |
|---|---|
| `ink` | основний текст / темний бренд |
| `ink-2` | hover темного (кнопки) |
| `steel`, `blue`, `blue-600`, `blue-700`, `azure` | акцент-палітра (у gazu — синя гама) |
| `mist` | нейтральний світлий тінт (плейсхолдери зображень) |
| `bone`, `paper` | фони сторінки; **`paper` — головний фон `body`** |
| `surface` | фон **карток / панелей / хедера / модалок / дропдаунів** |
| `on-brand` | текст + іконки **на темних brand-поверхнях** (кнопки ink/blue, футер, тости) |
| `line`, `line-2` | бордери / роздільники |
| `graphite`, `muted` | вторинний / приглушений текст |
| `success`, `warn`, `danger` | кольори статусів |
| `success-bg`, `warn-bg`, `danger-bg` | світлі підкладки бейджів статусів |

Кожен ключ → `--gazu-<key>` (компонентний CSS + `var()` у блейдах) **і** `--color-gazu-<key>`
(Tailwind-утиліти `bg-gazu-<key>`, `text-gazu-<key>`).

#### `radii` — заокруглення (опційно)

Ключі = Tailwind-шкала: `sm`, `md`, `lg`, `xl`, `2xl`. Впливають на всі `rounded-sm/md/lg/xl/2xl`
у блейдах + картки/кнопки/чипи. `rounded-full` **не чіпається** (пігулки/аватари лишаються круглими).
Менші значення = «брутальніше/гостріше», більші = «мʼякше».

```json
"radii": { "sm": "0.25rem", "md": "0.375rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem" }
```

#### `fonts` — шрифти (опційно)

| Ключ | Де |
|---|---|
| `display` | заголовки (`.gazu-display`) |
| `text` | основний / body |
| `mono` | моноширинний (артикули, `.gazu-mono`) |
| `archivo` | надважкі акценти (`.gazu-archivo`) |

Якщо шрифт **не системний** — додай його у `font_links` (Google Fonts `<link>`), інакше
спрацює `system-ui` fallback зі списку `font-family`.

```json
"fonts": { "display": "'Manrope', system-ui, sans-serif", "text": "'Manrope', system-ui, sans-serif" },
"font_links": [ "https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&display=swap" ]
```

### Темна тема — поради

Для темної теми «перевертаєш» ролі:

```json
"tokens": {
  "paper": "#0b1220", "surface": "#16203a", "bone": "#16203a",   // фони — темні
  "ink": "#e8edf5", "ink-2": "#cdd6e4", "on-brand": "#0b1220",   // текст — світлий; on-brand — темний (на світлому акценті)
  "blue": "#ffd200", "azure": "#ffd200",                         // акцент — яскравий
  "line": "#2a3656", "line-2": "#37456a",                        // бордери — темно-сірі
  "graphite": "#9fb0cc", "muted": "#7c89a6", "mist": "#16203a"
}
```

> ⚠️ Декоративні білі оверлеї на фото/scrim (lightbox, тости, плитки) навмисно лишаються
> білими для контрасту — це нормально й у темній темі.

### Промпт для генерації теми (Claude)

> «Згенеруй `themes/<slug>/theme.json` за зразком `themes/_template/theme.json` для магазину
> `<опис: ніша, настрій, бренд-кольори, світла/темна>`. Заповни всі `tokens`, за потреби `radii`
> (гостріше/мʼякше) і `fonts` (+`font_links`). `css_entry` лиши на `themes/gazu/resources/css/gazu.css`.»

Далі — кинути файл у `themes/`, активувати в адмінці. Готово.

---

## Активація теми

| Спосіб | Команда |
|---|---|
| **Адмінка** | `/admin/theme-settings` → «Активувати» (зберігає в БД, чистить кеш, миттєво) |
| **CLI** | `php artisan theme:set my-shop` |
| **ENV** (fallback) | `THEME=my-shop` у `.env` (БД-перемикач має пріоритет) |
| **Код** | `\App\Support\ThemeManager::setActive('my-shop')` |

Резолв активної теми: `DisplaySetting('active_theme')` → `env('THEME')` → `config('themes.default','gazu')`.

---

## Коли ПОТРІБЕН `npm run build`

Лише для **Рівня 2** (глибокі зміни):

- зміна самих `.css`-файлів теми (`gazu.css`/`tokens.css`) або додавання нових Tailwind-класів
  у blade, яких ще не було (arbitrary-утиліти `bg-[var(--gazu-...)]` Tailwind генерує при білді);
- новий CSS-entry для теми + його реєстрація у `vite.config.js`.

Для Рівня 1 (тільки `theme.json`) білд **не потрібен** — значення інжектяться у рантаймі.

> `public/build` у `.gitignore` → після `git pull`/деплою запусти `npm run build` (Node 20 на хості;
> у контейнері node немає).

---

## Рівень 2 — Глибока тема (власний вигляд/розкладка)

Якщо одних токенів мало (інша структура сторінок, унікальні компоненти):

```
themes/{name}/
├── theme.json
└── resources/
    ├── css/{name}.css      # власний Tailwind-entry (став css_entry на нього)
    └── views/gazu/...      # перевизначення будь-якого blade (дзеркалить resources/views/gazu/)
```

- **Blade-оверрайди**: `themes/{name}/resources/views/` препендиться у view-finder →
  `view('gazu.layout')` спершу шукає у темі, потім у core. Копіюй лише ті файли, що міняєш.
  Шлях береться з маніфест-ключа **`views_path`** (дефолт `resources/views`) — можна тримати
  views де завгодно (`ThemeDiscovery::bootActiveTheme` → `ThemeManager::viewsPath()`).
- **Власний CSS**: додай у `vite.config.js` `input`, постав `css_entry` на нього, `npm run build`.
- Деталі — `themes/README.md` + код `app/Support/ThemeDiscovery.php`.

---

## Рівень 3 — Кешування для нової теми (портативність)

Storefront-кеш (повносторінковий Spatie ResponseCache + DB-derived `Cache::remember`)
**розчеплений від GAZU** — нова тема інтегрує його БЕЗ правки core-файлів кешу. Усе
керується через `config/storefront.php` (дефолти = поточний GAZU).

### `config/storefront.php` — що перевизначає тема

| Ключ | Для чого | ENV |
|---|---|---|
| `excluded_cache_prefixes` | Префікси шляхів, які НЕ кешуються повносторінково (cart/checkout/кабінет тощо). Інша тема з іншими slug'ами задає свій набір. | `STOREFRONT_EXCLUDED_PREFIXES` (csv) |
| `warm_probe_paths` | Ключові сторінки, які guard `gazu:ensure-warm` пробує на «холод» (дефолт `/,/catalog`). | `STOREFRONT_PROBE_PATHS` (csv) |
| `derived_cache_keys` | Явні `Cache::remember`-ключі, що стають stale при зміні storefront-моделі (мега-меню, статистика, featured). | — |
| `derived_cache_tag` | Тег для derived-кешів теми (дефолт `storefront`). | `STOREFRONT_DERIVED_TAG` |
| `menu_cache_tag` | Тег fragment-кешу навігації. | `STOREFRONT_MENU_TAG` |

Споживачі (читають config, fallback = GAZU-дефолти):
`GazuCacheProfile` (виключення) · `EnsureWarm` (probe) · `ResponseCacheObserver` (інвалідація).

### Як інтегрувати кеш у нову тему — 2 шляхи

**A. Теги (рекомендовано — без списку ключів).**
Будь-який кеш, який рендерить дані вітрини, тегуй `storefront`-тегом:
```php
Cache::tags([config('storefront.derived_cache_tag')])->remember('my-theme:hero', 600, fn () => …);
```
`ResponseCacheObserver` автоматично флашить цей тег на будь-яку зміну storefront-моделі —
**нічого не треба додавати у core**. (Меню → тег `menu_cache_tag`.)

**B. Явний список ключів** (якщо кеш без тегів): додай ключі у `derived_cache_keys`
свого `config/storefront.php`.

### Контракт `$model->url()` (scoped-інвалідація)

`ResponseCacheObserver` при зміні товару/складу робить **точкову** інвалідацію (forget лише
сторінки товару + його категорій, а не весь storefront) через **`$model->url()`**:
- `App\Models\Product::url()` і `Category::url()` повертають канонічний URL.
- Якщо нова тема має ІНШІ роути сторінок — перевизнач `url()` у моделі (або підмінь модель).
  Observer лишається theme-agnostic (викликає `$model->url()`, не хардкод-роут).

### Прогрів кешу (працює для будь-якої теми)

| Механізм | Що робить |
|---|---|
| `php artisan cache:warm [--products]` | гріє URL із sitemap (тема-agnostic) — home/категорії/бренди (+товари) |
| `php artisan gazu:ensure-warm` (cron щохв) | детектить cold (`warm_probe_paths`) і сам доварює ≤60с |
| `docker-entrypoint.sh` | `view:cache` + фоновий `cache:warm` після кожного деплою |
| кнопка «Очистити ВЕСЬ кеш» (адмінка) | clear → `optimize` → `octane:reload` → `cache:warm` (не лишає холодним) |

⚠️ **НІКОЛИ не роби голий `view:clear`** (лишає сторінки холодними ~500ms на перший хіт) —
у коді є `gazu:views:refresh` (clear+cache), для прод-hotfix — `scripts/blade-hotfix.sh`.
Деталі: `docs/INFRASTRUCTURE.md` §6.

---

## Чеклист нової теми

1. `cp -r themes/_template themes/<slug>` → заповнити `name`/`label`/`tokens` (+опц. `radii`/`fonts`/`font_links`).
2. (опц.) Override-блейди → `themes/<slug>/resources/views/gazu/...` (дзеркалять core).
3. (опц.) Власний CSS-entry → `vite.config.js` input + `css_entry` + `npm run build`.
4. (опц.) Кеш: тегуй свої `Cache::remember` тегом `storefront`; за потреби — свій `config/storefront.php` (excluded/probe paths).
5. Активувати `/admin/theme-settings` → перевірити в інкогніто.

---

## Що НЕ тематизується (навмисно)

- `rounded-full` — круглі елементи (аватари, пігулки) лишаються круглими.
- Декоративні напівпрозорі білі оверлеї на фото/scrim (lightbox-кнопки, бейджі лічильника,
  плитки-категорії) — лишаються білими для контрасту над зображеннями.
- Адмінка (Filament) — окрема CSS-система, теми вітрини її не чіпають (див.
  `resources/css/filament/admin-utilities.css`).

---

## Перевірка нової теми

1. `/admin/theme-settings` → активувати → відкрити вітрину в **новому інкогніто** (свіжий кеш).
2. Перевірити: фон/текст/акценти, картки й хедер (surface), кнопки (on-brand), заокруглення, шрифт.
3. Якщо щось «світле» в темній темі — це або навмисний оверлей (ок), або пропущений `bg-white`
   у новому кастомному blade (конвертуй у `bg-[var(--gazu-surface)]`).

---

## Прод / Octane

На Octane статичні кеші скидаються щозапиту через `App\Listeners\Octane\FlushPerRequestSettingsState`
(`config/octane.php` → `RequestReceived`), тож зміна теми поширюється на всі воркери без reload.
На локальному php-fpm це неактуально (кожен запит — свіжий процес).

---

## Troubleshooting

| Симптом | Причина | Фікс |
|---|---|---|
| Тема активована, але вітрина не змінилась | кеш | hard-refresh / інкогніто; в адмінці кеш чиститься авто |
| Нова тема не зʼявилась у перемикачі | немає `themes/<name>/theme.json` або імʼя теки з `_` | створити маніфест / прибрати `_` |
| Колір/шрифт не застосувався | невалідне значення в `theme.json` (символи `;{}<>@`) | прибрати — `cssVarOverrides()` відкидає небезпечні значення |
| Кастомний `bg-[var(--gazu-x)]` не працює | новий arbitrary-клас не зібрано | `npm run build` |
| 500 «Unable to locate file in Vite manifest» | не запущено білд після зміни CSS | `npm run build` |

---

## Reference (код)

- `app/Support/ThemeManager.php` — резолв активної теми, `cssVarOverrides()`, `fontLinks()`, `viewsPath()`
- `app/Support/ThemeDiscovery.php` — препенд view-finder (за `views_path` маніфесту)
- `modules/theme_settings/src/Console/Commands/ThemeSetCommand.php` — `theme:set`
- `modules/theme_settings/src/Filament/Pages/ThemeSettings.php` — адмін-перемикач
- `resources/views/gazu/layout.blade.php` — інжекція `<style id="gazu-theme-vars">` + `font_links`
- `themes/gazu/theme.json` — еталонний маніфест · `themes/_template/theme.json` — шаблон-спец
- **Кеш (Рівень 3):** `config/storefront.php` (портативні ключі) · `app/Support/Cache/GazuCacheProfile.php` (виключення) · `app/Observers/ResponseCacheObserver.php` (scoped-інвалідація + теги) · `app/Console/Commands/{WarmCache,EnsureWarm,ViewsRefresh}.php` · `scripts/blade-hotfix.sh` · `docs/INFRASTRUCTURE.md` §6
- `config/themes.php` — дефолтна тема
