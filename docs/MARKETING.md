# SimpleShop / GAZU — Marketing & Selling Points

> Хто, чому і за скільки. Версія: 2026-05-09.

Документ для **продажу платформи замовнику** (власник магазину, маркетплейс-агрегатор, власник СТО). Описує переваги, типові кейси, цінність і структуру пропозиції.

---

## 1. Кому це продаємо

### A. Власник інтернет-магазину з 1+ складом
**Біль:** OpenCart / WordPress / Хорошоп не підтримують різні ціни на один товар з різних складів. На кожному складі — свій залишок, своя логіка доставки, свої терміни. На фронті користувач бачить лише одну ціну та `qty`. Якщо товар на одному складі скінчився, треба руками переключати.

**Рішення:** Один SKU — N складів. На product-page селектор з ціною та терміном доставки. Замовлення розбивається по складах автоматично.

### B. Дилерський магазин автозапчастин (GAZU)
**Біль:** Запчастина буває оригіналом ($3500 з оф. дилера) і аналогом ($1900 з Китаю). Хочеться продавати обидва, не показуючи що це різні постачальники. Покупцеві потрібен вибір «дешевше повільніше vs дорожче швидше».

**Рішення:** Кожен «постачальник» = ваш virtual warehouse у системі. На фронті — селектор: «Київ · 1 день · 3 850 ₴ / Львів · 3 дні · 1 950 ₴». Покупець обирає. Ваша маржа фіксована per warehouse.

### C. Маркетплейс-агрегатор
**Біль:** Зібрати кілька дрібних магазинів під один бренд, але не повноцінний marketplace (де продавці окремі юрособи). Потрібна архітектура single-tenant з виглядом multi-source.

**Рішення:** Кожен «магазин» = warehouse у вашій БД. Sender-конфігурація НП/УП — окрема per warehouse. ТТН формуються з реального складу. Звітність по продажу — per warehouse у Filament.

### D. Власник СТО / автосервісу з продажем запчастин (B2C+B2B)
**Біль:** Свій «склад на полицях» + drop-ship від брендових дистриб'юторів. Хочеться єдиний UX без розкриття складу.

**Рішення:** Виставив 1 SKU, прив'язав 2 inventory-rows: own warehouse (самовивіз) + drop-ship warehouse (3-5 днів). Покупець обирає. Адмінка показує звідки конкретно треба відвантажити.

---

## 2. Унікальні переваги (USP)

### 🏬 Multi-vendor pricing per warehouse
Те, чого **немає** у Хорошоп / OpenCart / Prom-білдерах: різна ціна на той самий товар з різних складів, з UI-селектором та авто-переключенням ціни. Ні плагіна, ні платної модифікації — це частина ядра SimpleShop.

### 🚚 Per-warehouse Нова Пошта / Укрпошта sender
Кожен склад має свій `np_sender_ref` + `np_sender_warehouse_ref` + `up_sender_uuid`. ТТН створюються від імені реального складу. Ніяких помилок «адреса відправника не збігається» від кур'єра.

### 📊 Stock movements audit log
Кожен зрушення `quantity` (приход, списання, інвентаризація, трансфер) пишеться у `stock_movements` з ким, коли, чому. Бухгалтерія / податкова — happy. Жоден існуючий ecommerce-конструктор так не вміє.

### 🔒 Race-safe inventory ops
`InventoryService` обкладає кожну операцію `DB::transaction()` + `lockForUpdate()`. 100 паралельних checkout'ів не зламають залишок. Phase 2 — додасть резервування (Sept 2026).

### 🎨 Theme-portable storefront
Brutal-style + GAZU-style + auto-parts theme — на тих самих контролерах, БД, моделях. Заміна теми = swap CSS-tokens. Один кодекс — N магазинів під різними брендами на різних доменах.

### 🌐 Module-toggle архітектура
`config/modules.php` дозволяє вмикати/вимикати фічі per deploy: loyalty, wholesale, comparison, coupons, reviews, NP, UP, Rozetka, Meest, Quick-fill, Feed export, GAZU garage. Один кодекс — кастомізація per клієнт без форку.

### ⚡ GAZU theme — готовий магазин автозапчастин
Forked codebase на окремому домені. /catalog, /cart, /vin, /search — спецдизайн під auto-parts. Demo-каталог (11 товарів × 7 категорій) seedиться однією командою.

---

## 3. Що отримує клієнт «з коробки»

| Категорія | Фічі |
|---|---|
| **Магазин** | Каталог, фільтри, пошук Meilisearch, мега-меню, brand-сторінки, comparison, wishlist |
| **Оплата** | LiqPay, WayForPay, Monobank — webhooks реалізовані |
| **Доставка** | Нова Пошта (відділення/адреса/Поштомат), Укрпошта (eCom), tracking pages, scan-sheets |
| **Адмінка Filament** | Products / Orders / Categories / Brands / Coupons / Reviews / Customers / Loyalty / Settings |
| **Multi-warehouse** | Per-warehouse stock + price + sender + transfers + receiving |
| **Loyalty / B2B** | Бонусні бали, рівні клієнтів, гуртові ціни, customer groups |
| **SEO / Feeds** | Sitemap auto-gen, robots, Rozetka/Prom/OLX/Google YML feeds |
| **GAZU theme** | VIN-decoder, OEM-search, garage (мої авто), part-image SVG-set |
| **Multi-language** | UA/EN з spatie translatable, locale switching |
| **Performance** | Redis cache, fragment cache на product-card, Vite build optimized |

---

## 4. Тарифи (suggested)

### 🥉 Starter — 1 магазин, 1 склад
**$2 500** одноразово + **$80/міс** хостинг&підтримка.

- Деплой на Coolify / VPS
- Brutal або custom theme
- Налаштування доставки/оплати
- 5 годин training
- 3 місяці підтримки включено

### 🥈 Pro — multi-warehouse + multi-vendor
**$4 500** + **$150/міс**.

- Все з Starter
- N власних складів (із своїми sender-конфігами)
- Multi-vendor pricing на product-page
- Inventory transfers + receiving orders
- Stock movements audit log
- Filament-розширення під ваш робочий процес
- 6 місяців підтримки

### 🥇 GAZU / Auto-parts — спец-конфіг
**$6 000** + **$200/міс**.

- Все з Pro
- GAZU theme (VIN-decoder, garage, OEM-search)
- Auto-parts-seed демо-каталог
- Compatibility & analogs Filament-resources
- Quick-fill з 1688/AliExpress (CNY/USD pricing з авто-розрахунком)
- Feed export Rozetka/Prom/OLX/Google
- 12 місяців підтримки

### 🏢 White-label / Marketplace agreggator
**$10 000+** custom proposal.

- Multi-tenant architecture
- N брендів на одному кодексі
- Custom theme per tenant
- API-інтеграції з 1С/SAP/CRM
- SLA 99.9%, dedicated support

---

## 5. Pain Points → Solutions (sales scripts)

### «У нас 3 склади і кожен має свою ціну закупівлі»
> SimpleShop multi-vendor pricing: один SKU, 3 inventory rows із власною `price`. На фронті селектор з вибором. Покупець бачить найкраще для себе. Ваша маржа окремо контролюється per warehouse.

### «У нас замовлення з 5 товарів, 3 з них на одному складі, 2 на іншому. Як кур'єр знатиме звідки забирати?»
> `order_products.warehouse_id` зберігається per лінія. Адмінка автоматично групує. Phase 5 додасть split TTN: одне замовлення → 2 ТТН з відповідних складів автоматично.

### «У нас Хорошоп / OpenCart / WP, можна туди додати multi-warehouse?»
> Це фундаментальна архітектурна відмінність — те, що ми пишемо в core моделей, у тих платформах вирішується платним плагіном за $300/рік + ручні правки коду + помилки на race conditions. Перехід на SimpleShop economically доцільний від 2 складів вгору.

### «Ми хочемо замінити нашого існуючого розробника / агентство»
> Ми деплоюємо за 1-2 тижні (Starter), 3-4 тижні (Pro), 6 тижнів (GAZU). Кодекс ваш — Laravel 12 + Filament 3 (індустрі-стандарт), будь-який ваш майбутній dev це підхопить. Жодних proprietary lock-ins.

### «А якщо нам потрібна тільки доставка/оплата без multi-warehouse?»
> `MODULE_MULTI_WAREHOUSE=false` в .env вимикає feature. Все інше працює як 1-warehouse magazin. Ми не змушуємо платити за фічі що не потрібні.

---

## 6. Конкурентна позиція

| Платформа | Multi-warehouse | Multi-vendor pricing | NP/UP per склад | Stock audit log | Race-safe inv | Open-source core |
|---|---|---|---|---|---|---|
| **SimpleShop** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Laravel |
| Хорошоп | ⚠️ платний модуль | ❌ | ❌ | ❌ | ⚠️ | ❌ |
| Prom Marketplace | ⚠️ tenants | ✅ але як marketplace | ⚠️ | ❌ | ❌ | ❌ |
| OpenCart + plugins | ⚠️ платний | ⚠️ платний (~$300/р) | ⚠️ | ❌ | ❌ | ✅ |
| WP/WooCommerce | ⚠️ плагіни | ⚠️ плагіни | ⚠️ | ❌ | ❌ | ✅ |
| Shopify | ⚠️ Plus only | ❌ (1 ціна на SKU) | ❌ | ⚠️ | ✅ | ❌ |
| Hublo / Rozetka builder | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 7. Demo & Lead Magnet

**Live demo:**
- Brutal storefront: `https://shop.demo.lionex.com.ua/uk` (single-warehouse demo)
- GAZU storefront: `https://gazu.demo.lionex.com.ua` (multi-vendor demo на autoparts)
- Filament admin: `https://shop.demo.lionex.com.ua/admin` (login: `demo@simpleshop.ua` / `demo`)

**Lead magnet PDF:** «Як піднести 3 свої склади до 1 інтернет-магазину» — 12-ст брошура з кейсами + декомпозицією за тиждень роботи. Пропонувати з landing-page.

**Free audit:**
> Ми проаналізуємо ваш чинний магазин (URL + admin-доступ) і покажемо точково, де multi-warehouse / multi-vendor добавив би до конверсії і середнього чека. Безкоштовно, 30 хв дзвінок. Не зобов'язує до співпраці.

---

## 8. Pitch deck — основні слайди

1. **Title** — SimpleShop: ecommerce-платформа з multi-warehouse у ядрі
2. **Проблема** — bullet points з sales scripts (розділ 5)
3. **Що ми зробили** — multi-vendor pricing UI на product-page (screenshot)
4. **Як це робиться у нас** — простий діаграмний flow inventory.price → cart_key → order_products.warehouse_id
5. **Demo** — посилання на live
6. **Конкуренти** — таблиця з розділу 6
7. **Тарифи** — 3 plate (Starter / Pro / GAZU)
8. **Roadmap** — Phase 2 reservations, Phase 5 split TTN, Phase 6 geo-detect
9. **Тех. стек** — Laravel 12 + Filament 3 + Livewire 3 + Alpine + Tailwind 4 + Meilisearch + Redis (індустрі-стандарт)
10. **Кейси** — 1-2 успішні впровадження (як зʼявляться)
11. **CTA** — free audit + посилання на calendar для дзвінка

---

## 9. SEO + контент-маркетинг (suggested)

### Блог-теми (для кожної — 1500+ слів)

- «3 ознаки що ваш OpenCart не масштабується далі. І що з цим робити»
- «Multi-warehouse у Хорошоп vs Laravel: реальна вартість на 2 роки»
- «Як 4 склади у 4 містах піднімають конверсію магазину автозапчастин на 28%»
- «Stock movements audit log: чому це не nice-to-have, а must-have для Українського ринку 2026»
- «Per-warehouse Нова Пошта sender: як перестати ловити нерози від кур'єра»
- «GAZU storefront за 6 тижнів: декомпозиція реалізації» (case study)

### Лонг-тейл ключові слова

- мульти-складовий ecommerce україна
- одна ціна різні склади opencart
- multi-vendor pricing laravel
- нова пошта sender per склад
- хорошоп vs laravel multi warehouse
- ecommerce platform ukraine 2026
- inventory tracking laravel filament

---

## 10. Як замовити / FAQ

**1. Скільки часу на впровадження?**
- Starter: 1-2 тижні (з нуля, з демо-каталогом).
- Pro: 3-4 тижні (з міграцією існуючої БД).
- GAZU: 6 тижнів.

**2. Хто володіє кодом?**
- Замовник. SimpleShop пишеться як Laravel-проєкт у вашому Git-репо. Жодних SaaS-залежностей крім стандартних (Meilisearch, Redis).

**3. Чи можна змігрувати з існуючого магазину?**
- Так. Робимо одноразовий ETL: товари / категорії / клієнти / замовлення. Старі URL-и редиректимо 301 для SEO.

**4. Хостинг ваш чи наш?**
- Coolify-готова конфігурація. Можемо тримати на наших VPS (від $80/міс) або деплоїмо до вашого хмарного провайдера (AWS / DigitalOcean / Hetzner).

**5. Чи буде підтримка після запуску?**
- 3 / 6 / 12 місяців включено залежно від тарифу. Далі — $80-200/міс.

**6. А якщо ми переростемо вашу платформу?**
- Не повинно — ядро Laravel 12 масштабується до десятків тисяч SKU. Якщо ж потрібен marketplace із незалежними продавцями (юрособами) — це інша архітектура; ми чесно скажемо.

---

## 11. Контакти

- Web: `https://lionex.com.ua`
- Email: `vladpowerpro@gmail.com`
- Telegram: `@lionex_dev`
- Demo + аудит: записуйтесь через calendar: TBD

> SimpleShop — ваш магазин без обмежень платних плагінів.
