# Themes — visual layer

SimpleShop engine has a **theme system** parallel to modules. Themes control
the storefront's visual identity (CSS, layout, page templates). Modules
control business features. Both are independent: any module can be active
under any theme.

> 📖 **Повний посібник зі створення тем: [`docs/THEMES.md`](../docs/THEMES.md).**
> Для звичайного ре-скіну (кольори + заокруглення + шрифти) достатньо одного
> `theme.json` — **без `npm run build`** (значення інжектяться у рантаймі).
> Готовий шаблон: [`themes/_template/theme.json`](_template/theme.json).
> Цей файл — лише довідка по структурі теки.

## Folder layout

```
themes/{name}/
├── theme.json                  # manifest (required)
├── resources/
│   ├── css/
│   │   ├── {name}.css          # main entry (Vite input)
│   │   └── tokens.css          # CSS variables / design tokens
│   ├── js/                     # optional theme-scoped JS
│   └── views/                  # blade overrides (prepended to view finder)
│       └── gazu/               # mirrors core resources/views/gazu/ structure
└── README.md
```

## theme.json schema

```json
{
  "name": "gazu",
  "label": "GAZU brutal-style (auto-parts)",
  "description": "Dark accent + bold display fonts. Optimized for auto-parts.",
  "version": "1.0.0",
  "author": "Lionex",
  "engine": ">=2.0",
  "parent": null,

  "vite_inputs": [
    "themes/gazu/resources/css/gazu.css"
  ],
  "views_path": "resources/views",
  "css_entry": "themes/gazu/resources/css/gazu.css",

  "tokens": { "ink": "#0E1B2C", "paper": "#FBFAF7", "surface": "#FFFFFF", "on-brand": "#FFFFFF", "blue": "#2453A6", "...": "усі кольори" },
  "radii":  { "sm": "0.25rem", "md": "0.375rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem" },
  "fonts":  { "display": "'Space Grotesk', system-ui, sans-serif", "text": "'Inter Tight', system-ui, sans-serif" },
  "font_links": [ "https://fonts.googleapis.com/css2?family=...&display=swap" ]
}
```

> Повний контракт з описом кожного ключа — у [`docs/THEMES.md`](../docs/THEMES.md)
> і шаблоні [`themes/_template/theme.json`](_template/theme.json).
> `tokens`→`--gazu-*`/`--color-gazu-*`, `radii`→Tailwind `--radius-*`, `fonts`→`--gazu-font-*`/`--font-*`.

## Lifecycle

1. **Active theme** resolved at boot from:
   - `DisplaySetting::get('active_theme')` — set via UI / preset
   - `THEME` env var
   - `config('themes.default')` fallback
2. **ThemeDiscovery::bootActiveTheme()**:
   - Prepends `themes/{active}/resources/views/` to view finder
     → blade `view('gazu.layout')` first checks theme, then core
3. **Runtime tokens** — `layout.blade.php` injects `ThemeManager::cssVarOverrides()`
   as a `<style>` after the built CSS → the active theme's `tokens`/`radii`/`fonts`
   re-skin the storefront live, **no build**. (This is the primary path.)
4. **CSS** (deep themes only) — a custom `css_entry` must be listed in
   `vite.config.js` input + `npm run build`. Reusing `gazu.css` needs neither.

## Switching themes

```bash
# Via artisan
php artisan theme:set gazu

# Or via preset
php artisan preset:apply auto-parts  # also sets theme

# Via Filament admin
/admin/theme-settings  → dropdown
```

## Creating a new theme

Немає `make:theme` — копіюєш руками (швидкий шлях, без білда):

```bash
cp -r themes/_template themes/cosmetics     # або cp -r themes/gazu themes/cosmetics
# Відредагуй themes/cosmetics/theme.json: name, label, tokens/radii/fonts.
# css_entry лиши на themes/gazu/resources/css/gazu.css.
php artisan theme:set cosmetics             # або через /admin/theme-settings
```

Глибока тема (власний CSS/blade) — додатково `vite.config.js` input + `npm run build`.
Повні кроки — [`docs/THEMES.md`](../docs/THEMES.md).

## Available themes

| Name | Label | Status |
|---|---|---|
| gazu | GAZU brutal-style (auto-parts) | active by default |
