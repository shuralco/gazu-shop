# Themes — visual layer

SimpleShop engine has a **theme system** parallel to modules. Themes control
the storefront's visual identity (CSS, layout, page templates). Modules
control business features. Both are independent: any module can be active
under any theme.

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
  "views_namespace": "gazu",
  "views_path": "resources/views",
  "css_entry": "themes/gazu/resources/css/gazu.css",

  "tokens": {
    "primary": "#0a0f1a",
    "accent": "#ffd200"
  }
}
```

## Lifecycle

1. **Active theme** resolved at boot from:
   - `DisplaySetting::get('active_theme')` — set via UI / preset
   - `THEME` env var
   - `config('themes.default')` fallback
2. **ThemeDiscovery::bootActiveTheme()**:
   - Prepends `themes/{active}/resources/views/` to view finder
     → blade `view('gazu.layout')` first checks theme, then core
   - Loads `theme.json` tokens for runtime CSS-var injection (optional)
3. **CSS** — must be listed in `vite.config.js` input array
   (one config per theme; Vite builds all themes, blade chooses which one to load via `@vite()`).

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

```bash
php artisan make:theme cosmetics
# creates themes/cosmetics/{theme.json, resources/css/, resources/views/}
# Edit theme.json, copy/modify CSS, add views as needed
npm run build  # rebuilds Vite assets for all themes
```

## Available themes

| Name | Label | Status |
|---|---|---|
| gazu | GAZU brutal-style (auto-parts) | active by default |
