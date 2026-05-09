# Theme System

> SimpleShop підтримує кілька візуальних тем поверх єдиного core. Активна тема — це CSS-файл токенів, що завантажується перед усіма іншими стилями.

---

## 1. Архітектура

```
resources/css/
  ├── app.css                  # Імпортує @import './tokens/{theme}.css'
  ├── components.css           # Спільні компоненти, читають var(--token)
  └── tokens/
      ├── brutal.css           # Тема за замовчуванням
      └── auto-parts.css       # Автозапчастини (приклад)
```

Кожен файл `tokens/*.css` — це `@theme {}` блок Tailwind 4 з фіксованим набором CSS-змінних. Усі компоненти у `app.css`/`components.css` використовують `var(--…)` замість літералів.

---

## 2. Перемикання тем

```bash
# Показати доступні
php artisan theme:use --list

# Перемкнути
php artisan theme:use auto-parts
npm run build         # обов'язково перебудувати CSS після

# Назад
php artisan theme:use brutal
npm run build
```

Команда `theme:use` редагує одну строку `@import './tokens/X.css'` у `resources/css/app.css`. Перебудова Vite вшиває нові значення в `public/build/assets/app-*.css`.

---

## 3. Контракт CSS-змінних

Кожна тема **зобов'язана** оголошувати весь нижче набір змінних у `@theme {}`:

### Typography
```
--font-display
--font-body
--font-weight-bold
--font-weight-display
```

### Кольори (палітра)
```
--color-fg              текст за замовчуванням
--color-bg              фон сторінки
--color-card            фон карток
--color-brand           основний акцент бренду
--color-accent          вторинний акцент (badge, CTA)
--color-muted           приглушений текст
--color-success         green-700
--color-danger          red-700
--color-warning         amber-700
--color-info            blue-700
--color-surface-muted   приглушений фон секції
--color-surface-divider колір розділювачів
```

### Бордери
```
--border-thin           1px subtle
--border-strong         2px бренд
--border-thick          3px brutal
--border-color-strong
--border-color-subtle
```

### Радіуси
```
--radius-sm             повсюдні елементи (input, button)
--radius-card           картки, panels
--radius-pill           pill-кнопки, badges
```

### Тіні
```
--shadow-btn-rest       спокій
--shadow-btn-hover      :hover
--shadow-btn-active     :active
--shadow-card-elevated  картка з елевацією
--shadow-input-focus    focus-ring
```

### Spacing scale
```
--gap-xs  --gap-sm  --gap-md  --gap-lg  --gap-xl  --gap-2xl
```

### Hero / pattern
```
--pattern-hero-color
--pattern-hero-opacity
--pattern-grid-line-color
--pattern-grid-size
--pattern-stripes-size
```

### Scrollbar / Z-index (службові)
```
--scrollbar-track-bg, --scrollbar-thumb-bg, --scrollbar-thumb-hover-bg, --scrollbar-width
--z-toast, --z-progress-bar, --z-modal, --z-dropdown
```

---

## 4. Створення нової теми

```bash
# 1. Скопіюйте brutal.css як шаблон
cp resources/css/tokens/brutal.css resources/css/tokens/my-theme.css

# 2. Відредагуйте значення (зберігаючи усі змінні з контракту)

# 3. Активуйте
php artisan theme:use my-theme
npm run build

# 4. Перевірте візуально
```

**Best practices:**
- Дотримуйтесь контракту змінних з §3 — будь-який `var(--missing)` =fallback на default
- Тестуйте на product-page, cart, checkout, blog — всі секції
- Перевіряйте на light + dark prefers-color-scheme якщо актуально
- Filament admin **ізольований** і не зачіпається — admin завжди має свій вигляд

---

## 5. Поточні теми

### `brutal` (default)
- Pure black/white контраст
- Sharp corners (radius=0)
- 3D box-shadow `0 6px 0 #000` для кнопок
- Inter font, weight 900 для display
- Hero pattern: жирні чорні смуги 40px

### `auto-parts`
- Slate-900 текст на slate-50 фоні
- Blue-700 brand + orange-600 accent
- Rounded corners (radius=4–8px)
- Soft elevation shadows
- Roboto Condensed для display, weight 800
- Hero pattern: blue grid 24px, opacity 0.04

---

## 6. Що залишається статичним між темами

- HTML-структура blade-templates
- Livewire-компоненти (логіка)
- Filament admin (ізольована тема)
- JavaScript (`public/assets/js/*.js`)
- Multi-locale (uk/en) — теми не локалізовані

Для переходу на нову тему НЕ потрібно міняти код PHP/Livewire/Blade — лише `tokens/*.css`.
