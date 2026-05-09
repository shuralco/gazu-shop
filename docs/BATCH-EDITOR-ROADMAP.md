# Batch Editor — Roadmap доробки

**Еталон:** Edixon (edixon.net) — модуль масового редагування для OpenCart 3
**Поточний стан:** ~75% функціоналу, 4 таби, 15 batch actions, CSV import/export

---

## Що є зараз

### Таби
- ✅ Товари (inline grid, 13 editable полів)
- ✅ Категорії (inline title, sort_order, active, parent)
- ✅ Замовлення (status filter, batch status change, export)
- ✅ Відгуки (approve/reject bulk)

### Batch Actions (товари)
- ✅ Масова зміна ціни (set / + / - / +% / -%)
- ✅ Встановити акцію (old_price = current, new price)
- ✅ Зняти акцію
- ✅ Гуртові ціни по групах клієнтів
- ✅ Зміна статусу (is_active, is_hit, is_new, stock_status)
- ✅ Зміна категорії
- ✅ Зміна бренду/виробника
- ✅ Управління характеристиками (attach/detach фільтри)
- ✅ Пошук і заміна (6 полів, preview після виконання)
- ✅ Зміна ваги/розмірів
- ✅ Дублювання товарів
- ✅ CSV Export
- ✅ CSV Import (upload, mapping, preview)
- ✅ Масове SEO (шаблон з плейсхолдерами)
- ✅ Масове видалення

### Фільтри (товари)
- ✅ Категорія (select)
- ✅ Бренд (select)
- ✅ Статус (active/inactive/hit/new/sale)
- ✅ Ціна від/до
- ✅ Текстовий пошук (title/SKU)
- ✅ Stock status (in_stock/out_of_stock/preorder)
- ✅ Виробник (текст)

---

## Що потрібно додати (порівняння з Edixon)

### Пріоритет 1: КРИТИЧНІ (без них не конкурентоспроможний)

#### 1.1 Preview перед КОЖНОЮ масовою дією
**Edixon:** Preview → Підтвердження → Execute (3 кроки для КОЖНОЇ операції)
**Зараз:** Preview тільки для Search/Replace, інші дії застосовуються одразу

**Реалізація:**
- Кожна модалка отримує 2 стани: "налаштування" і "preview"
- Крок 1: Користувач вводить параметри (наприклад, +10% ціна)
- Крок 2: Натискає "Переглянути" → бачить таблицю "до → після" для перших 20 товарів
- Крок 3: Натискає "Застосувати" або "Скасувати"
- Метод `previewBatchPrice(ids, type, value)` → повертає array [{id, title, old_value, new_value}]

**Файли:**
- `BatchEditorService.php` — додати preview* методи
- `BatchEditor.php` — додати previewData property, 2-step modal flow
- `batch-editor.blade.php` — preview таблиця в кожній модалці

#### 1.2 Журнал дій (Action Log)
**Edixon:** Повний лог: хто, коли, який фільтр, що змінив, в яких товарах. Rollback.
**Зараз:** Відсутній

**Реалізація:**
- Нова міграція: `batch_editor_logs` table (user_id, action_type, filter_params JSON, changes JSON, affected_ids JSON, rollback_data JSON, created_at)
- Модель: `BatchEditorLog`
- Кожна batch операція → зберігає лог перед виконанням
- Окрема вкладка "ЖУРНАЛ" в batch editor
- Кнопка "Відмінити" → rollback з збережених даних

**Файли:**
- Міграція `create_batch_editor_logs_table.php`
- `app/Models/BatchEditorLog.php`
- `BatchEditorService.php` — логування кожної операції
- `BatchEditor.php` — таб "ЖУРНАЛ", rollback метод
- `batch-editor.blade.php` — таблиця логів

#### 1.3 Розширені фільтри (AND/OR, 21 умова)
**Edixon:** 21 умова з AND/OR комбінаціями
**Зараз:** 7 простих фільтрів (тільки AND)

**Додати фільтри:**
- По атрибутам/фільтрам (має/не має конкретний фільтр)
- Без фото (image IS NULL)
- Без описy (content IS NULL OR content = '')
- Без SEO (meta_title IS NULL)
- По рейтингу (від/до)
- По даті створення (від/до)
- По кількості на складі (від/до)
- По наявності варіантів (має/не має)
- По old_price > 0 (акційні)
- По groupPrice (має/не має гуртову ціну)

**Реалізація:**
- Додати properties в BatchEditor.php
- Додати умови в getProducts()
- Додати UI елементи в blade (collapsible "Розширені фільтри")

### Пріоритет 2: ВИСОКІ

#### 2.1 Спецціна з датами
**Edixon:** Встановити знижку з date_start і date_end
**Зараз:** Тільки old_price без дат

**Реалізація:**
- Модалка "Акція" → додати поля date_from і date_to
- Якщо Product має поле для спеццін з датами — використати
- Або створити таблицю `product_specials` (product_id, price, date_start, date_end, customer_group_id, priority)

#### 2.2 Нормалізація атрибутів (злиття значень)
**Edixon:** "Коттон", "Cotton", "Котон" → злити в одне значення
**Зараз:** Тільки attach/detach фільтрів

**Реалізація:**
- Модалка "Нормалізація" → показати всі значення фільтру
- Вибрати які злити → вказати канонічне значення
- Оновити filter_products pivot table
- Видалити зайві Filter records

#### 2.3 Модалки для inline-редагування
**Edixon:** Клік на клітинку → модалка для фото, атрибутів, текстів, знижок
**Зараз:** Тільки прості input/checkbox в grid

**Реалізація:**
- Клік на іконку фото → модалка з gallery upload
- Клік на іконку атрибутів → модалка з attach/detach фільтрів для конкретного товару
- Клік на іконку тексту → модалка з textarea для content/excerpt
- Клік на іконку знижок → модалка з спеццінами

#### 2.4 Hover-прев'ю фото
**Edixon:** Навести на товар → popup з фото
**Зараз:** Немає

**Реалізація:**
- Alpine.js `x-on:mouseenter` → показати tooltip з `<img>`
- CSS absolute positioning

#### 2.5 Налаштування видимих колонок
**Edixon:** Вибрати які колонки показувати в таблиці
**Зараз:** Фіксований набір колонок

**Реалізація:**
- Dropdown з checkboxes для кожної колонки
- Зберігати в localStorage або session
- Property `visibleColumns` array

### Пріоритет 3: СЕРЕДНІ

#### 3.1 Regex в Search/Replace
**Зараз:** Тільки plain text
**Додати:** Checkbox "Regex", використати `preg_replace`

#### 3.2 Dry-run preview ДО застосування (Search/Replace)
**Зараз:** Preview показується ПІСЛЯ виконання
**Виправити:** Спочатку показати що зміниться, потім кнопка "Застосувати"

#### 3.3 Чанкова обробка з прогресом
**Edixon:** Прогрес-бар для великих операцій
**Додати:** Livewire dispatch events з прогресом, progress bar в модалці

#### 3.4 Видалення з перевіркою фото
**Зараз:** Просте видалення Product
**Додати:** Опція "Видаляти фото" з перевіркою чи використовується іншими товарами

#### 3.5 Формула від кастомного поля
**Edixon:** Ціна = old_price * 1.2 або ціна = custom_field * коефіцієнт
**Додати:** В модалці ціни → режим "Формула" → вибір базового поля + операція + значення

---

## Порядок реалізації

### Sprint 1: Фільтри + Preview (найбільший ефект)
1. Розширити фільтри до 15+ умов з collapsible панеллю
2. Додати preview таблицю для price/sale/status модалок
3. Виправити S&R preview (до, а не після)

### Sprint 2: Журнал + Rollback
4. Міграція batch_editor_logs
5. Модель BatchEditorLog
6. Логування всіх операцій
7. Таб "ЖУРНАЛ" з таблицею та rollback

### Sprint 3: UX покращення
8. Hover-прев'ю фото
9. Налаштування колонок
10. Модалки для inline (фото, тексти, атрибути)

### Sprint 4: Атрибути + Спецціни
11. Нормалізація атрибутів (злиття)
12. Спецціни з датами
13. Формула від поля

### Sprint 5: Regex + Chunks + Delete
14. Regex в S&R
15. Чанкова обробка з прогресом
16. Видалення з перевіркою фото

---

## Архітектурні принципи

1. **Preview → Confirm → Execute** — 3 кроки для кожної деструктивної дії
2. **Журнал** — кожна масова операція документується
3. **Rollback** — можливість відмінити останню дію
4. **Чанки** — обробка пакетами по 100 для великих каталогів
5. **Без зміни core** — все через Services і Filament Pages
