# Themes — Developer Guide

Themes are the **visual layer** of SimpleShop. They control CSS tokens (colors,
fonts, spacing) and can override any storefront blade view. The same modules
work under any theme — themes only touch presentation, not business logic.

This doc supersedes the older draft (which described the pre-modular tokens
approach). For the 30-second overview, see [ARCHITECTURE.md](ARCHITECTURE.md).

## Anatomy of a theme

```
themes/gazu/
├── theme.json                  # ① manifest
├── README.md
└── resources/
    ├── css/
    │   ├── gazu.css            # ② Tailwind 4 entry — Vite input
    │   └── tokens.css          # design tokens (--color-*, --font-*, ...)
    ├── js/                     # optional theme-scoped JS
    └── views/                  # ③ blade overrides
        └── gazu/               # mirrors core resources/views/gazu/
            ├── layout.blade.php
            ├── home/
            ├── catalog/
            └── ...
```

## ① The manifest (`theme.json`)

```json
{
  "name": "gazu",
  "label": "GAZU brutal-style (auto-parts)",
  "description": "Темний акцент + жирні display fonts. Оптимізовано для автозапчастин.",
  "version": "1.0.0",
  "author": "Lionex",
  "engine": ">=2.0",
  "parent": null,

  "vite_inputs": [
    "themes/gazu/resources/css/gazu.css"
  ],
  "views_path": "resources/views",
  "css_entry": "themes/gazu/resources/css/gazu.css",

  "tokens": {
    "primary": "#0a0f1a",
    "accent": "#ffd200",
    "ink": "#0a0f1a",
    "graphite": "#3a3f4a",
    "paper": "#f7f7f5"
  }
}
```

### Field reference

| Field | Required | What it does |
|---|---|---|
| `name` | yes | Unique identifier; matches folder name |
| `label` | yes | Human label for admin UI |
| `description` | yes | One-line summary |
| `version` | yes | Semver |
| `engine` | recommended | Required engine version |
| `parent` | optional | Name of theme to inherit from (NYI — currently no inheritance) |
| `vite_inputs` | yes | Paths added to `vite.config.js` input array (must be there too) |
| `views_path` | optional | Relative to theme root; default `resources/views` |
| `css_entry` | yes | The CSS file the storefront layout loads via `@vite()` |
| `tokens` | optional | Static token values (used for runtime injection, future) |

## ② CSS — Tailwind 4 entry

Each theme has its OWN Tailwind build, completely independent of `resources/css/app.css`
(which is the admin/Filament side). This means:

- Theme CSS never accidentally bleeds into admin.
- Multiple themes can ship with different Tailwind configs.
- You can use `@source` to control which views Tailwind scans.

Example `themes/gazu/resources/css/gazu.css`:

```css
@import 'tailwindcss';
@import './tokens.css';

@source '../../../../resources/views/gazu/**/*.blade.php';
@source '../../../../resources/views/components/gazu/**/*.blade.php';
@source '../views/**/*.blade.php';

@theme {
  --color-ink:      #0a0f1a;
  --color-graphite: #3a3f4a;
  --color-paper:    #f7f7f5;
  --color-accent:   #ffd200;

  --font-display: 'Archivo Black', sans-serif;
  --font-body:    'Inter Tight', sans-serif;
  --font-mono:    'JetBrains Mono', monospace;

  --radius-sm:   2px;
  --radius-card: 14px;
}

/* Theme-specific component styles */
.gazu-btn-primary { /* ... */ }
```

The `@source` directives tell Tailwind 4 where to scan for class usage.
Paths are relative to the CSS file location.

## ③ Blade view overrides

Theme views go under `themes/{name}/resources/views/`. The engine **prepends**
this directory to Laravel's view finder at boot, so:

```php
view('gazu.layout')
//   ↓
// 1. themes/gazu/resources/views/gazu/layout.blade.php  ← if exists, win
// 2. resources/views/gazu/layout.blade.php              ← fallback
```

You can override **any** view by mirroring its path. To customize the homepage
hero in your theme without touching core:

```bash
mkdir -p themes/mytheme/resources/views/gazu/home
cp resources/views/gazu/home/hero.blade.php themes/mytheme/resources/views/gazu/home/
# edit your copy
```

The original stays as the default; your theme's copy wins when this theme is active.

## Adding a theme to Vite

Theme CSS files must be listed in `vite.config.js` input array so Laravel
Vite can build them:

```javascript
laravel({
    input: [
        'resources/css/app.css',
        'themes/gazu/resources/css/gazu.css',
        'themes/cosmetics/resources/css/cosmetics.css',  // ← add new theme here
        'resources/js/app.js',
    ],
    refresh: true,
}),
```

Then `npm run build` — produces compiled CSS in `public/build/assets/` for each
input. The layout decides which one to load via `@vite()`:

```blade
{{-- resources/views/gazu/layout.blade.php --}}
@vite([\App\Support\ThemeManager::cssEntry() ?: 'themes/gazu/resources/css/gazu.css'])
```

`ThemeManager::cssEntry()` reads from the active theme's `theme.json` so the
correct CSS file loads based on which theme is active.

## Switching themes

### Via Filament UI

`/admin/theme-settings` — dropdown of installed themes. Persists via
`DisplaySetting::set('active_theme', $name)`. Cache invalidates immediately.

### Via CLI

```bash
php artisan theme:set gazu        # switch active theme
npm run build                     # rebuild Vite if you also changed CSS
```

### Via ENV

`.env`:

```ini
THEME=gazu
```

ENV is fallback only — DB toggle (via UI) wins.

### Programmatically

```php
\App\Support\ThemeManager::setActive('cosmetics');
```

## Creating a new theme

Currently no `make:theme` artisan (planned). Manual steps:

```bash
mkdir -p themes/cosmetics/resources/{css,views,js}
cp themes/gazu/theme.json themes/cosmetics/theme.json
# edit cosmetics/theme.json — name, label, css_entry, tokens
cp themes/gazu/resources/css/gazu.css themes/cosmetics/resources/css/cosmetics.css
# tweak tokens / styles
```

Add the new CSS input to `vite.config.js`:

```diff
  input: [
      'resources/css/app.css',
      'themes/gazu/resources/css/gazu.css',
+     'themes/cosmetics/resources/css/cosmetics.css',
      'resources/js/app.js',
  ],
```

Update `themes/cosmetics/theme.json`:

```json
{
  "name": "cosmetics",
  "label": "Cosmetics / Beauty Shop",
  "css_entry": "themes/cosmetics/resources/css/cosmetics.css",
  "tokens": {
    "primary": "#ff6b9d",
    "accent": "#fbf7f4"
  }
}
```

Then:

```bash
npm run build
php artisan theme:set cosmetics
```

Visit `/` — should now render with cosmetics CSS. View overrides are optional;
absent ones fall back to defaults.

## View finder order

```
view('gazu.layout')
     ↓
1. themes/{active}/resources/views/gazu/layout.blade.php   ← active theme
2. resources/views/gazu/layout.blade.php                   ← core default
```

For module views (`view('loyalty::tier')`), namespaces are checked separately:

```
view('loyalty::tier')
     ↓
1. registered namespace 'loyalty' →
   modules/loyalty/resources/views/tier.blade.php
```

Themes can override namespaced views by registering the same namespace from
a deeper location, but this is **not currently wired up** in `ThemeDiscovery`.
(See "Limitations" below.)

## Tokens (CSS variables)

The current pattern is to define tokens via Tailwind 4's `@theme` directive
in the theme's CSS entry. These become available globally as CSS variables:

```css
/* themes/gazu/resources/css/gazu.css */
@theme {
  --color-ink: #0a0f1a;
}
```

Then in any view:

```css
.my-thing { color: var(--color-ink); }
```

Or use Tailwind utilities — Tailwind 4 auto-generates `text-ink`, `bg-ink`,
`border-ink` etc. from `--color-ink`.

The `tokens` block in `theme.json` is **descriptive only** right now (read by
`ThemeManager::tokens()`). It's not auto-injected into CSS. The actual values
live in the CSS file. (Future Phase 3+: auto-inject from JSON into a generated
`:root {}` block.)

## Best practices

- **Tokens, not hex.** Reference everything via `var(--color-*)` in custom
  CSS so themes can override cleanly.
- **Tailwind utilities over custom CSS.** Stay in Tailwind 4 land as much as
  possible — the `@theme` block makes utility generation token-aware.
- **Override sparingly.** A theme that copies 80 blade files is hard to
  maintain. Prefer overriding just `layout.blade.php`, `home/*`, `partials/*`.
- **One CSS per theme.** Don't try to share CSS files between themes; if you
  need shared base, use Tailwind's `@layer base` and `@theme` defaults in core
  `app.css`.

## Limitations / NYI

- **No view-overlay for blade components** (`<x-gazu.breadcrumbs>`). These are
  shared `resources/views/components/gazu/` files. To customize per-theme,
  you'd need `Blade::anonymousComponentPath()` registration per theme — not
  done yet.
- **No parent/child theme inheritance** despite the `parent` field in manifest.
- **Tokens block is descriptive only** — not auto-injected as `:root {}`.
- **No `make:theme` artisan** — copy gazu manually for now.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| 500: "Unable to locate file in Vite manifest" | `npm run build` not run after theme/css change | `npm run build` |
| Theme dropdown empty in `/admin/theme-settings` | `themes/*/theme.json` missing | Create manifest |
| View renders core version, not theme version | Cached view OR theme path wrong | `php artisan view:clear` + check `themes/{n}/resources/views/...` |
| `theme:set` succeeds but no visual change | Browser CSS cached, OR theme has no overrides yet | Hard refresh, OR add overrides |
| `npm run build` fails on missing `@source` path | Wrong relative depth | Count: theme CSS is at depth 3 in `themes/{n}/resources/css/` |

## Reference

- `app/Support/ThemeManager.php` — active theme resolution
- `app/Support/ThemeDiscovery.php` — view-finder prepend
- `app/Console/Commands/ThemeSetCommand.php` — `theme:set` CLI
- `app/Filament/Pages/ThemeSettings.php` — admin dropdown
- `config/themes.php` — default theme fallback
- `vite.config.js` — list theme CSS inputs here
- `themes/gazu/theme.json` — reference manifest
