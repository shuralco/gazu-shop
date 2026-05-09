# SimpleShop — Лог нових фіч

Кожна фіча документується тут при створенні.

---

## TinyPNG Image Optimization (коміт: 3c7e29f7)

### Призначення
Автоматична оптимізація зображень товарів через TinyPNG API. Зменшує розмір до 80% без втрати якості + конвертація в WebP.

### Файли
- `config/tinypng.php` — конфігурація (api_key, max_width, quality, convert_to_webp)
- `app/Services/TinyPng/TinyPngService.php` — сервіс (compress, compressAndSave, batchCompress, getUsage)
- `app/Console/Commands/OptimizeImages.php` — artisan команда
- `app/Services/Integrations/Concrete/TinyPngIntegration.php` — інтеграція в admin panel

### Налаштування
```env
TINYPNG_API_KEY=your_api_key
TINYPNG_ENABLED=true
TINYPNG_MAX_WIDTH=1920
TINYPNG_CONVERT_WEBP=true
```

### Використання
```bash
php artisan images:optimize                     # всі в products/
php artisan images:optimize --path=gallery      # конкретна папка
php artisan images:optimize --limit=100         # ліміт кількості
```

### API ліміти
500 безкоштовних компресій/місяць. Перевірка: `$service->getUsage()`

---

## Mega Menu Editor (коміт: 417d6fa4 + поточний)

### Призначення
Повноцінне управління навігацією магазину: горизонтальне меню (чорна стрічка) та мега-меню (dropdown під "КАТАЛОГ").

### Файли
- `app/Filament/Pages/MegaMenuEditor.php` — Filament Page
- `resources/views/filament/pages/mega-menu-editor.blade.php` — UI
- `app/Services/HeaderService.php` — сервіс конфігурації (модифікований)

### URL
`/admin/mega-menu-editor` (група: Контент та SEO)

### Можливості

**Горизонтальне меню:**
- Додати/видалити пункти (назва + URL)
- Перемістити вгору/вниз (стрілки)
- Авто-генерація з категорій
- Toggle увімк/вимк

**Мега-меню:**
- Додати/видалити колонки
- В кожній колонці: додати категорію (з dropdown) або кастомне посилання
- Видалити елемент з колонки
- Перемістити колонку вліво/вправо
- Авто-генерація з категорій (4-колонковий layout)
- Редагування кастомних посилань inline (назва + URL)
- Перегляд підкатегорій кожної категорії

**Промо-блок:**
- Toggle увімк/вимк
- Заголовок, опис, текст кнопки, URL кнопки

### Зберігання
Дані зберігаються в таблиці `display_settings` з ключами:
- `enable_horizontal_menu` (boolean)
- `horizontal_menu_items` (json)
- `mega_menu_enabled` (boolean)
- `main_mega_menu_structure` (json)
- `main_show_promo` (boolean)
- `main_mega_menu_promo_*` (string)

---

## Batch Editor — Compact Filters + Searchable Dropdowns (коміт: 882162d1)

### Зміни
- Фільтри: inline flex-wrap замість 7-row grid
- Селекти: правильні кольори (не білий на білому)
- Категорія/Бренд в таблиці: Alpine.js combobox з live search
- Stock status: нормальні border і background

---

## Batch Editor — Filament Native Redesign (коміт: 9c60144e)

### Зміни
- Toolbar: 2-row layout (Save + count + Columns toggle)
- Action buttons: scrollable colored pills (fi-badge)
- Column visibility: dropdown з 17 checkboxes
- 17 editable колонок (id, title, sku, price, old_price, qty, stock, active, hit, new, category, brand, manufacturer, weight, rating, reviews, created)

---

## Batch Editor — Audit Fixes (коміт: a9a4790a)

### 8 виправлень
1. Boolean casting для статусів
2. Pagination reset при зміні табу
3. Escape key на всіх модалках
4. Логування всіх batch операцій
5. Dark mode consistency
6. Responsive таблиці
7. Import result feedback
8. Preview для Group Price

---

## Batch Editor — Sprint 1+2 (коміт: edff16e2)

### Нові фічі
- 15+ фільтрів з collapsible панеллю
- Preview перед КОЖНОЮ дією (3-step: Preview → Confirm → Execute)
- Журнал дій (batch_editor_logs table)

---

## Admin Panel Restructure (коміт: fba8ae8c)

### 5 груп навігації
- Каталог (7 пунктів)
- Продажі (6 пунктів)
- Доставка та оплата (5 пунктів)
- Контент та SEO (9 пунктів)
- Система (4 пункти)

Видалено DisplaySettingResource (дублікат), MegaMenuBuilder (замінений).
