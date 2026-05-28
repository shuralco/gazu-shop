# Module Creation Guide — Step-by-Step

> Створи робочий модуль за **15 хвилин**. Покроковий tutorial з прикладом.

## Що ми створимо

Модуль `product_compare` — кнопка "Порівняти" на сторінці товару + сторінка `/compare` зі списком вибраних товарів.

Використає:
- `@hookAction('product.page.variants', $p)` для рендеру кнопки
- Власна Eloquent модель `ProductComparison`
- Власний Filament Resource для admin
- Routes для `/compare` сторінки
- Migration для збереження порівнянь

---

## Шаг 1: Створити структуру

```bash
mkdir -p modules/product_compare/{src/{Models,Http/Controllers,Filament/Resources},resources/views,routes,database/migrations}
```

Структура:
```
modules/product_compare/
├── module.json
├── src/
│   ├── ProductCompareServiceProvider.php
│   ├── Models/
│   │   └── ProductComparison.php
│   ├── Http/Controllers/
│   │   └── CompareController.php
│   └── Filament/Resources/
│       └── ProductComparisonResource.php
├── resources/views/
│   ├── button.blade.php
│   └── index.blade.php
├── routes/web.php
└── database/migrations/
    └── 2026_05_28_create_product_comparisons_table.php
```

---

## Шаг 2: Створити manifest

```bash
cat > modules/product_compare/module.json <<'EOF'
{
    "name": "product_compare",
    "label": "Порівняння товарів",
    "description": "Кнопка \"Порівняти\" на картці товару + сторінка /compare зі списком вибраних товарів.",
    "version": "1.0.0",
    "author": "Lionex",
    "engine": ">=2.0",
    "php": ">=8.2",

    "requires_modules": [],

    "providers": [
        "Modules\\ProductCompare\\ProductCompareServiceProvider"
    ],

    "filament_resources": [
        "App\\Filament\\Resources\\ProductComparisonResource"
    ],

    "migrations_path": "database/migrations",
    "views_path": "resources/views",
    "views_namespace": "product_compare",
    "routes": "routes/web.php",

    "settings_schema": {
        "max_items_per_user": {
            "type": "integer",
            "default": 4,
            "min": 2,
            "max": 10,
            "label": "Максимум товарів в порівнянні"
        }
    },

    "enabled_by_default": false
}
EOF
```

---

## Шаг 3: ServiceProvider з hook listener

```php
// modules/product_compare/src/ProductCompareServiceProvider.php
<?php

namespace Modules\ProductCompare;

use App\Support\Hooks;
use Illuminate\Support\ServiceProvider;

class ProductCompareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Якщо потрібні CLI commands:
        // if ($this->app->runningInConsole()) {
        //     $this->commands([SomeCommand::class]);
        // }
    }

    public function boot(): void
    {
        // На сторінці товару — додати кнопку "Порівняти" через hook.
        // Core blade `gazu/product/v1.blade.php` має `@hookAction('product.page.variants', $p)` —
        // ми рендеримо HTML і його буде вставлено у вказану зону.
        Hooks::on('product.page.variants', function ($product) {
            return view('product_compare::button', compact('product'))->render();
        }, priority: 20, source: 'product_compare');
    }
}
```

---

## Шаг 4: Модель

```php
// modules/product_compare/src/Models/ProductComparison.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComparison extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function for(?int $userId, ?string $sessionId): array
    {
        return self::query()
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) $q->where('user_id', $userId);
                else $q->where('session_id', $sessionId);
            })
            ->with('product')
            ->pluck('product')
            ->all();
    }
}
```

**Важливо:** namespace `App\Models` (не `Modules\ProductCompare\Models`). Composer classmap у `modules/` робить це працюючим.

---

## Шаг 5: Migration

```php
// modules/product_compare/database/migrations/2026_05_28_create_product_comparisons_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['user_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_comparisons');
    }
};
```

---

## Шаг 6: View — кнопка на картці товару

```blade
{{-- modules/product_compare/resources/views/button.blade.php --}}
<button type="button"
        x-data="{ added: false }"
        @click="
            fetch('/api/compare/add', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.GAZU_CSRF,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ product_id: '{{ $product->id }}' })
            }).then(r => r.json()).then(d => {
                if (d.ok) added = true;
            });
        "
        :class="added ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded-md transition-colors mt-2">
    <x-gazu.icon name="scale" size="14"/>
    <span x-text="added ? 'У порівнянні' : 'Порівняти'"></span>
</button>
```

```blade
{{-- modules/product_compare/resources/views/index.blade.php --}}
@extends('gazu.layout')

@section('title', 'Порівняння товарів — GAZU')

@section('content')
<div class="gazu-container">
    <h1 class="gazu-display text-3xl font-semibold mb-6">Порівняння товарів</h1>

    @if($items->isEmpty())
        <p class="text-gray-500">Поки що нічого не вибрано. Додайте товари через кнопку "Порівняти" на сторінках товарів.</p>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($items as $p)
                <x-gazu.product-card :p="$p"/>
            @endforeach
        </div>
    @endif
</div>
@endsection
```

---

## Шаг 7: Controller + Routes

```php
// modules/product_compare/src/Http/Controllers/CompareController.php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductComparison;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $items = ProductComparison::for(
            $request->user()?->id,
            $request->session()->getId()
        );
        return view('product_compare::index', compact('items'));
    }

    public function add(Request $request)
    {
        $productId = $request->integer('product_id');
        $product = Product::findOrFail($productId);

        $max = (int) module('product_compare')->setting('max_items_per_user', 4);
        $current = ProductComparison::query()
            ->where(function ($q) use ($request) {
                if ($request->user()) $q->where('user_id', $request->user()->id);
                else $q->where('session_id', $request->session()->getId());
            })->count();

        if ($current >= $max) {
            return response()->json(['ok' => false, 'message' => "Максимум {$max} товарів"]);
        }

        ProductComparison::firstOrCreate([
            'user_id' => $request->user()?->id,
            'session_id' => $request->user() ? null : $request->session()->getId(),
            'product_id' => $productId,
        ]);

        return response()->json(['ok' => true, 'count' => $current + 1]);
    }

    public function remove(Request $request, int $productId)
    {
        ProductComparison::query()
            ->where(function ($q) use ($request) {
                if ($request->user()) $q->where('user_id', $request->user()->id);
                else $q->where('session_id', $request->session()->getId());
            })
            ->where('product_id', $productId)
            ->delete();

        return back();
    }
}
```

```php
// modules/product_compare/routes/web.php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/compare', [\App\Http\Controllers\CompareController::class, 'index'])->name('compare.index');
    Route::post('/api/compare/add', [\App\Http\Controllers\CompareController::class, 'add'])->name('compare.add');
    Route::delete('/compare/{product}', [\App\Http\Controllers\CompareController::class, 'remove'])->name('compare.remove');
});
```

---

## Шаг 8: Filament Resource (admin CRUD)

```php
// modules/product_compare/src/Filament/Resources/ProductComparisonResource.php
<?php

namespace App\Filament\Resources;

use App\Models\ProductComparison;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductComparisonResource extends Resource
{
    protected static ?string $model = ProductComparison::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Порівняння';
    protected static ?string $navigationGroup = 'Аналітика';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Користувач')->placeholder('гість')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->label('Товар')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

---

## Шаг 9: Composer dump + clear caches + enable

```bash
composer dump-autoload
php artisan view:clear
php artisan filament:cache-components

# Перевір що видно в admin
php artisan module:list | grep product_compare

# Activate
php artisan module:enable product_compare
```

Очікуваний output:
```
✓ Enabled: product_compare
  Migrations: ran
  Lifecycle: install() called
  Autoload: refreshed
```

Або через UI: `/admin/modules` → шукати «Порівняння товарів» → «Увімкнути».

---

## Шаг 10: Перевірка

1. **Сторінка товару** — має бути кнопка «Порівняти» (натисніть її)
2. **/compare** — має показати додані товари
3. **/admin/product-comparisons** — admin побачить usage stats
4. **/admin/modules/view?key=product_compare** — має показати `Hooks: 1` (`product.page.variants`)

---

## Шаг 11: (Опціонально) Lifecycle handler

Якщо потрібна спецлогіка при install/upgrade/disable:

```php
// modules/product_compare/src/ProductCompareLifecycle.php
<?php

namespace Modules\ProductCompare;

use App\Support\Modules\ModuleLifecycle;

class ProductCompareLifecycle implements ModuleLifecycle
{
    public function install(): void
    {
        // First-time setup: seed example або налаштування
    }

    public function upgrade(string $from, string $to): void
    {
        if (version_compare($from, '2.0.0', '<')) {
            // Між v1.x та v2.0 щось зробити
        }
    }

    public function disable(): void
    {
        // Cleanup кешу — дані залишаються
        \Cache::tags(['product_compare'])->flush();
    }

    public function uninstall(): void
    {
        // Final cleanup — налаштування, scheduled jobs
    }

    public function boot(): void
    {
        // Викликається при кожному boot (коли module enabled)
    }
}
```

І в manifest:
```json
{
    "lifecycle": "Modules\\ProductCompare\\ProductCompareLifecycle"
}
```

---

## Шаг 12: Export для distribution

```bash
php artisan module:export product_compare --out=/tmp/product_compare-v1.0.0.zip
```

Передайте `.zip` колезі — він через `/admin/modules` → «Встановити з .zip» отримає той самий модуль.

---

## Checklist готовності

- [ ] `module.json` валідний (`jq .` парсить)
- [ ] ServiceProvider зареєстрований у manifest
- [ ] `boot()` реєструє hooks (якщо потрібні)
- [ ] Migrations створюють tables у `modules/X/database/migrations/`
- [ ] Routes у `routes/web.php` через `Route::middleware('web')->group(...)`
- [ ] Views у `resources/views/` доступні як `view('mod_name::filename')`
- [ ] Models у `App\Models\` namespace (не Modules\...)
- [ ] Filament Resources в manifest `filament_resources` + у `src/Filament/Resources/`
- [ ] composer dump-autoload виконано
- [ ] `module:list` показує модуль
- [ ] `/admin/modules` показує картку
- [ ] Enable → міграція проходить → tables створено
- [ ] Health check (9 пунктів) — всі ✓
- [ ] `module:export` створює архів → `module:install --force` повертає той самий

---

## Питання — куди далі?

- [MODULES.md](MODULES.md) — overall reference
- [MODULE-HOOKS.md](MODULE-HOOKS.md) — повний реєстр event-points
- [MODULE-INSTALLER.md](MODULE-INSTALLER.md) — install/export workflow
- [MODULE-AUDIT.md](MODULE-AUDIT.md) — що ще треба перенести з core

Коли застрягнеш — `php artisan module:list`, `/admin/modules/view?key=X` health-check.
