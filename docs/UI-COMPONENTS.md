# UI Components Library

> Token-driven Blade-компоненти для шаблонної темізації. Зміна `tokens/{theme}.css` → автоматичний візуальний swap для всіх використовуваних компонентів.

---

## Філософія

Compose = **HTML structure (blade) + Style (CSS via tokens)**. PHP-логіка ніколи не змінюється між темами; зміна теми лише підставляє інші значення для `var(--btn-primary-bg)`, `var(--card-radius)` тощо.

Дві шари токенів:

1. **Primitives** (`brutal.css` / `auto-parts.css` `@theme {}`) — `--color-fg`, `--radius-sm`, `--shadow-btn-rest`, etc
2. **Semantic** (там же) — `--btn-primary-bg`, `--card-padding`, `--badge-padding-y` — composed з primitives

`<x-ui.*>` компоненти споживають **виключно semantic** токени.

---

## Компоненти

### `<x-ui.button>`

```blade
<x-ui.button>За замовчуванням</x-ui.button>
<x-ui.button variant="secondary">Другорядна</x-ui.button>
<x-ui.button variant="ghost">Прозора</x-ui.button>
<x-ui.button size="lg" type="submit">Великий submit</x-ui.button>
<x-ui.button href="/cart">Як посилання</x-ui.button>
<x-ui.button :disabled="!$canBuy">Купити</x-ui.button>
```

| Prop | Default | Values |
|---|---|---|
| `variant` | `primary` | `primary` / `secondary` / `ghost` |
| `size` | `md` | `sm` / `md` / `lg` |
| `href` | — | URL → `<a>` instead of `<button>` |
| `type` | `button` | стандартні HTML типи |
| `disabled` | `false` | bool |

Tokens consumed: `--btn-padding-y`, `--btn-padding-x`, `--btn-font-weight`, `--btn-text-transform`, `--btn-border-radius`, `--btn-{primary|secondary|ghost}-{bg|fg|border}`, `--btn-{primary}-shadow{|-hover}`.

---

### `<x-ui.card>`

```blade
<x-ui.card>
    <h3>Заголовок картки</h3>
    <p>Контент</p>
</x-ui.card>

<x-ui.card :padded="false">Без padding</x-ui.card>
<x-ui.card :elevated="true">З тінню</x-ui.card>
<x-ui.card as="article">Як <article></x-ui.card>
```

Tokens: `--card-bg`, `--card-border`, `--card-radius`, `--card-shadow`, `--card-padding`.

---

### `<x-ui.input>`

```blade
<x-ui.input
    name="email"
    type="email"
    label="Email"
    placeholder="your@email.com"
    required
    :value="old('email')"
    :error="$errors->first('email')"
/>
```

| Prop | Default |
|---|---|
| `type` | `text` |
| `label` | nullable |
| `error` | nullable (показує під полем червоним) |
| `required` | `false` |
| `autocomplete` | `off` |

Tokens: `--input-bg`, `--input-fg`, `--input-border`, `--input-radius`, `--input-padding-{x,y}`, `--input-focus-shadow`.

---

### `<x-ui.badge>`

```blade
<x-ui.badge>За замовчуванням</x-ui.badge>
<x-ui.badge variant="success">В наявності</x-ui.badge>
<x-ui.badge variant="danger">Немає</x-ui.badge>
<x-ui.badge variant="warning">Залишилось 3 шт.</x-ui.badge>
<x-ui.badge variant="accent">НОВИНКА</x-ui.badge>
```

Variants: `default`, `success`, `danger`, `warning`, `info`, `accent`.

---

### `<x-ui.modal>`

```blade
<x-ui.modal id="my-modal" title="Підтвердіть дію" size="md">
    <p>Ви впевнені що хочете видалити це?</p>

    <x-slot name="footer">
        <x-ui.button variant="secondary" @click="open = false">Скасувати</x-ui.button>
        <x-ui.button wire:click="delete">Видалити</x-ui.button>
    </x-slot>
</x-ui.modal>

<!-- Trigger -->
<x-ui.button @click="$dispatch('open-modal', { id: 'my-modal' })">Open</x-ui.button>
```

| Prop | Default | Values |
|---|---|---|
| `id` | uniqid | string — для адресації множинних модалок |
| `title` | nullable | заголовок (header показується з кнопкою закриття) |
| `size` | `md` | `sm` / `md` / `lg` / `xl` |
| `show` | `false` | початковий стан |

Open/close через Alpine events: `$dispatch('open-modal', { id: '...' })` + `close-modal`. ESC закриває. Backdrop клік закриває.

---

### `<x-ui.alert>`

```blade
<x-ui.alert variant="success" title="Готово!">
    Замовлення №1234 успішно створено.
</x-ui.alert>

<x-ui.alert variant="danger" :icon="true" dismissible>
    Помилка валідації — перевірте поля.
</x-ui.alert>
```

| Prop | Default | Values |
|---|---|---|
| `variant` | `info` | `info` / `success` / `warning` / `danger` |
| `title` | nullable | bold-заголовок |
| `icon` | nullable | `true` (auto з variant) / emoji string |
| `dismissible` | `false` | додає кнопку закриття |

Використовує CSS `color-mix()` для tinted background — кожен variant виглядає консистентно у будь-якій темі.

---

### `<x-ui.section>`

```blade
<x-ui.section title="Хіти продажів" subtitle="Найпопулярніше за тиждень" centered>
    <div class="grid grid-cols-4 gap-6">
        @foreach($products as $p)
            <x-ui.card>...</x-ui.card>
        @endforeach
    </div>
</x-ui.section>
```

Props: `title`, `subtitle`, `as`, `centered`. Tokens: `--section-padding-y`, `--section-padding-x`, `--section-title-{weight,transform,letter-spacing}`.

---

## Як додати нову variant / token

1. Додай semantic token у `resources/css/tokens/brutal.css` (наприклад `--btn-danger-bg`)
2. Дублюй у `auto-parts.css` (різні значення)
3. Додай CSS-клас у `resources/css/components.css` (наприклад `.btn-ui--danger`)
4. У component blade: `'danger' => 'btn-ui--danger'`
5. `npm run build`

---

## Як створити нову тему

```bash
cp resources/css/tokens/brutal.css resources/css/tokens/my-theme.css
# редагуй значення (зберігаючи усі змінні)
php artisan theme:use my-theme
npm run build
```

Усі `<x-ui.*>` компоненти автоматично адаптуються.

---

## Roadmap

Сьогодні:
- ✅ `<x-ui.button>`, `<x-ui.card>`, `<x-ui.input>`, `<x-ui.badge>`, `<x-ui.section>`

Наступні ітерації:
- [ ] `<x-ui.modal>` — заміна для cart/filter modal-ів
- [ ] `<x-ui.dropdown>` — для autocomplete suggestions
- [ ] `<x-ui.alert>` — для повідомлень
- [ ] Прогресивна заміна inline-class у `livewire/cart/checkout-component.blade.php`, `livewire/product/product-component.blade.php`
- [ ] Створити `<x-ui.product-card>` як уніфіковану заміну для `incs/brutal-product-card.blade.php` та `incs/product-card.blade.php`

---

## Швидкий smoke-test нового деплою (auto-parts theme)

```bash
git clone <repo> auto-parts-shop
cd auto-parts-shop
composer install --no-dev
npm install
php artisan migrate
php artisan shop:init --theme=auto-parts \
    --shop-name="Auto Parts UA" \
    --shop-email="info@autoparts.ua" \
    --warehouse-code="KYIV-1" \
    --warehouse-name="Київ Лівобережний"
npm run build
php artisan db:seed --class=ProductSeeder
```

Через ~2 хв нова інстанція готова з auto-parts темою. Налаштуйте API ключі у `/admin/shipping-providers` і `/admin/ukr-poshta-settings`.
