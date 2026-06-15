<?php

namespace App\Services\Gazu;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Будує дерево мега-меню для GAZU storefront. Тягне з БД (Category, Brand);
 * якщо немає даних — фолбек на статичну демо-структуру.
 */
class MegaMenuBuilder
{
    /** Mapping від slug-keyword до іконки (категорії з папки x-gazu.cat-icon). */
    private const ICON_MAP = [
        'engine'      => ['двигун', 'engine', 'motor'],
        'brakes'      => ['гальм', 'brake'],
        'suspension'  => ['підвіск', 'руль', 'suspension', 'steering'],
        'electric'    => ['електр', 'electric'],
        'body'        => ['кузов', 'оптик', 'body', 'light'],
        'interior'    => ['салон', 'interior', 'комфорт'],
        'filters'     => ['фільтр', 'filter'],
        'oils'        => ['олив', 'хімі', 'охолоджу', 'oil'],
        'tires'       => ['шин', 'диск', 'tire', 'wheel'],
        'transmission'=> ['трансміс', 'зчепленн', 'transmission'],
        'lights'      => ['освітленн', 'фар', 'лам'],
        'tools'       => ['інструмент', 'tool'],
    ];

    public function build(): array
    {
        // 1) Якщо адмін задав ручну структуру в редакторі «Мега-меню» — вона
        //    авторитетна (опційно). 2) Інакше — авто з дерева категорій.
        //    3) Інакше — статичний фолбек.
        $manual = $this->fromEditorStructure();
        if (! empty($manual)) {
            return $manual;
        }

        $tree = $this->fromDatabase();
        if (empty($tree)) {
            $tree = $this->fallback();
        }
        return $tree;
    }

    /**
     * Будує megaTree з ручної структури редактора (main_mega_menu_structure),
     * якщо вона задана. Формат редактора (columns → items) мапиться у формат
     * фронту (root-таби з групами). Порожньо → [] (фолбек на категорії).
     */
    private function fromEditorStructure(): array
    {
        try {
            $structure = \App\Models\DisplaySetting::get('main_mega_menu_structure');
            if (is_string($structure)) {
                $structure = json_decode($structure, true);
            }
            $columns = is_array($structure) ? ($structure['columns'] ?? null) : null;
            if (! is_array($columns) || empty($columns)) {
                return [];
            }

            $this->loadCounts();
            $roots = [];
            foreach ($columns as $col) {
                foreach ((array) $col as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $type = $item['type'] ?? 'category';
                    $title = $this->resolveTitle($item['title'] ?? '');
                    $slug = (string) ($item['slug'] ?? '');

                    if ($type === 'custom_link') {
                        $roots[] = [
                            'id' => $slug ?: \Illuminate\Support\Str::slug($title) ?: 'link-'.count($roots),
                            'icon' => $this->iconKey($slug, $title),
                            'slug' => $slug,
                            'label' => $title,
                            'count' => 0,
                            'image' => null,
                            'url' => $item['url'] ?? ('/'.ltrim($slug, '/')),
                            'groups' => [],
                        ];
                        continue;
                    }

                    $children = is_array($item['children'] ?? null) ? $item['children'] : [];
                    $items = [];
                    foreach ($children as $c) {
                        $cSlug = (string) ($c['slug'] ?? '');
                        $cid = $c['category_id'] ?? null;
                        $items[] = [
                            $this->resolveTitle($c['title'] ?? ''),
                            $cid ? (int) ($this->treeCounts[$cid] ?? 0) : 0,
                            $cSlug,
                        ];
                    }
                    $catId = $item['category_id'] ?? null;
                    $roots[] = [
                        'id' => $slug ?: ('cat-'.($catId ?? count($roots))),
                        'icon' => $this->iconKey($slug, $title),
                        'slug' => $slug,
                        'label' => $title,
                        'count' => $catId ? (int) ($this->treeCounts[$catId] ?? 0) : 0,
                        'image' => null,
                        'groups' => $items ? [['title' => $title, 'items' => $items]] : [],
                    ];
                }
            }

            return array_slice($roots, 0, 12);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /** Translatable title → рядок поточної локалі. */
    private function resolveTitle(mixed $t): string
    {
        if (is_array($t)) {
            return (string) ($t['uk'] ?? reset($t) ?: '');
        }
        $decoded = json_decode((string) $t, true);
        if (is_array($decoded)) {
            return (string) ($decoded['uk'] ?? reset($decoded) ?: '');
        }
        return (string) $t;
    }

    /**
     * Топ-бренди для mega-menu. Повертає array of [name, slug] щоб mega-menu
     * рендерив clickable links на /brand/{slug}.
     */
    public function brands(): array
    {
        if (! class_exists(Brand::class)) {
            return $this->fallbackBrands();
        }
        try {
            $brands = Brand::query()
                ->when(\Schema::hasColumn('brands', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->when(\Schema::hasColumn('brands', 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
                ->orderBy('id')
                ->limit(12)
                ->get(['id', 'name', 'slug']);

            if ($brands->isNotEmpty()) {
                return $brands->map(fn ($b) => [
                    'name' => (string) $b->name,
                    'slug' => (string) $b->slug,
                ])->filter(fn ($b) => $b['name'] && $b['slug'])->values()->all();
            }
            return $this->fallbackBrands();
        } catch (\Throwable) {
            return $this->fallbackBrands();
        }
    }

    private function fromDatabase(): array
    {
        if (! class_exists(Category::class)) {
            return [];
        }

        try {
            $roots = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)
                      ->orderBy('sort_order')
                      ->with(['children' => function ($qq) {
                          $qq->where('is_active', true)->orderBy('sort_order');
                      }]);
                }])
                ->orderBy('sort_order')
                ->limit(40)
                ->get();

            if ($roots->isEmpty()) return [];

            // Dedup by title — AutoPartsSeeder ran multiple times and the
            // 'slug' column got JSON-wrapped by the translatable migration,
            // so firstOrCreate(['slug' => 'auto-batteries']) never matched
            // existing rows and created N duplicates per category.
            $unique = $roots
                ->unique(fn (Category $r) => mb_strtolower(trim((string) ($r->title ?? $r->name ?? ''))))
                ->take(12)
                ->values();

            return $unique->map(fn (Category $root) => $this->mapRoot($root))->all();
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /** Cached counts: category_id => direct product count. */
    private ?array $directCounts = null;
    /** Cached counts including descendants: category_id => total product count. */
    private ?array $treeCounts = null;

    private function loadCounts(): void
    {
        if ($this->directCounts !== null) return;
        try {
            $this->directCounts = \App\Models\Product::query()
                ->when(\Schema::hasColumn('products', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->selectRaw('category_id, COUNT(*) as c')
                ->whereNotNull('category_id')
                ->groupBy('category_id')
                ->pluck('c', 'category_id')
                ->map(fn ($v) => (int) $v)
                ->all();

            $parents = Category::query()->pluck('parent_id', 'id')->all();

            $this->treeCounts = $this->directCounts;
            foreach ($this->directCounts as $catId => $count) {
                $parent = $parents[$catId] ?? null;
                while ($parent) {
                    $this->treeCounts[$parent] = ($this->treeCounts[$parent] ?? 0) + $count;
                    $parent = $parents[$parent] ?? null;
                }
            }
        } catch (\Throwable) {
            $this->directCounts = [];
            $this->treeCounts = [];
        }
    }

    private function totalCount(Category $c): int
    {
        $this->loadCounts();
        return (int) ($this->treeCounts[$c->id] ?? 0);
    }

    private function mapRoot(Category $root): array
    {
        $titleStr = (string) ($root->title ?? $root->name ?? 'Категорія');
        $slugKey = $this->slugify($root->slug ?: $titleStr);
        $count = $this->totalCount($root);

        $hasL3 = $root->children->contains(fn (Category $g) => $g->children->isNotEmpty());

        if ($root->children->isEmpty()) {
            $groups = [['title' => $titleStr, 'items' => [[$titleStr, $count]]]];
        } elseif ($hasL3) {
            $groups = $root->children->map(fn (Category $g) => [
                'title' => (string) ($g->title ?? $g->name ?? '—'),
                'items' => $g->children->isNotEmpty()
                    ? $g->children->map(fn (Category $leaf) => [
                        (string) ($leaf->title ?? $leaf->name ?? '—'),
                        $this->totalCount($leaf),
                        (string) ($leaf->slug ?? ''),
                    ])->take(10)->values()->all()
                    : [[(string) ($g->title ?? '—'), $this->totalCount($g), (string) ($g->slug ?? '')]],
            ])->take(6)->values()->all();
        } else {
            $items = $root->children->map(fn (Category $g) => [
                (string) ($g->title ?? $g->name ?? '—'),
                $this->totalCount($g),
                (string) ($g->slug ?? ''),
            ])->values()->all();

            $cols = max(2, min(4, (int) ceil(count($items) / 6)));
            $perCol = (int) ceil(count($items) / $cols);
            $groups = [];
            for ($i = 0; $i < $cols; $i++) {
                $slice = array_slice($items, $i * $perCol, $perCol);
                if (! empty($slice)) {
                    $groups[] = [
                        'title' => $i === 0 ? 'Підкатегорії' : '',
                        'items' => $slice,
                    ];
                }
            }
        }

        $slug = (string) ($root->slug ?? '');
        // Admin-uploaded category image (public disk) → root-relative URL.
        $img = $root->image ?? null;
        $imageUrl = $img
            ? (\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '/']) ? $img : asset('storage/'.ltrim($img, '/')))
            : null;
        return [
            // `id` must be unique per root — use slug, not iconKey (which can
            // collide when two roots fall through to the same fallback icon).
            'id'    => $slug ?: 'cat-'.$root->id,
            'icon'  => $this->iconKey($slugKey, $titleStr),
            'slug'  => $slug,
            'label' => $titleStr,
            'count' => $count,
            'image' => $imageUrl,
            'groups'=> $groups,
        ];
    }

    private function safeProductCount(Category $c): int
    {
        return $this->totalCount($c);
    }

    private function iconKey(string $slug, string $title): string
    {
        $haystack = mb_strtolower($slug.' '.$title);
        foreach (self::ICON_MAP as $key => $needles) {
            foreach ($needles as $n) {
                if (mb_strpos($haystack, $n) !== false) return $key;
            }
        }
        return 'engine';
    }

    private function slugify(string $s): string
    {
        return mb_strtolower($s);
    }

    private function fallback(): array
    {
        return [
            ['id' => 'engine', 'label' => 'Двигун та системи', 'count' => 8420, 'groups' => [
                ['title' => 'Двигун', 'items' => [['Поршні та кільця', 412], ['Колінвал та шатуни', 186], ['Розподілвал', 240], ['Маховик', 124], ['Прокладка ГБЦ', 318], ['Сальники', 452], ['Опори двигуна', 286], ['Ремені та ролики', 624]]],
                ['title' => 'Система охолодження', 'items' => [['Радіатори', 312], ['Помпи', 268], ['Термостати', 196], ['Вентилятори', 142], ['Патрубки', 384], ['Бачки', 78]]],
                ['title' => 'Паливна система', 'items' => [['Форсунки', 322], ['Паливний насос', 184], ['ТНВД', 96], ['Регулятори тиску', 124], ['Свічки розжарювання', 218]]],
                ['title' => 'Випуск', 'items' => [['Глушники', 186], ['Каталізатори', 142], ['Лямбда-зонди', 312], ['Прокладки випуску', 224], ['Датчик EGR', 86]]],
            ]],
            ['id' => 'brakes', 'label' => 'Гальмівна система', 'count' => 2180, 'groups' => [
                ['title' => 'Передні гальма', 'items' => [['Колодки передні', 324], ['Диски передні', 284], ['Супорти передні', 142], ['Скоби супорта', 96]]],
                ['title' => 'Задні гальма', 'items' => [['Колодки задні', 286], ['Диски задні', 218], ['Барабани', 124]]],
                ['title' => 'Гідравліка', 'items' => [['Шланги гальмівні', 184], ['Трубки', 86], ['Головний циліндр', 124]]],
                ['title' => 'ABS / ESP', 'items' => [['Датчики ABS', 312], ['Блок ABS', 48], ['Гідроблок ESP', 24]]],
            ]],
            ['id' => 'suspension', 'label' => 'Підвіска та рульове', 'count' => 4120, 'groups' => [
                ['title' => 'Амортизатори', 'items' => [['Передні', 412], ['Задні', 386], ['Опори стійок', 224]]],
                ['title' => 'Пружини', 'items' => [['Пружини передні', 218], ['Пружини задні', 196], ['Стійки стабілізатора', 312]]],
                ['title' => 'Важелі', 'items' => [['Важелі передні', 286], ['Сайлентблоки', 412], ['Шарові опори', 196]]],
                ['title' => 'Рульове', 'items' => [['Рейки', 124], ['Тяги рульові', 218], ['Наконечники', 268]]],
            ]],
            ['id' => 'electric', 'label' => 'Електрика та електроніка', 'count' => 5860, 'groups' => [
                ['title' => 'Запуск та зарядка', 'items' => [['Стартери', 412], ['Генератори', 386], ['Реле-регулятори', 142]]],
                ['title' => 'Запалювання', 'items' => [['Свічки запалювання', 624], ['Котушки', 286], ['Високовольтні дроти', 184]]],
                ['title' => 'Датчики', 'items' => [['Кисню (лямбда)', 312], ['Колінвала', 218], ['ABS', 312]]],
                ['title' => 'АКБ та проводка', 'items' => [['Акумулятори', 142], ['Клеми', 218], ['Запобіжники', 96]]],
            ]],
            ['id' => 'body', 'label' => 'Кузов та оптика', 'count' => 2940, 'groups' => [
                ['title' => 'Зовнішній кузов', 'items' => [['Бампери', 346], ['Капоти', 96], ['Крила', 142]]],
                ['title' => 'Скло', 'items' => [['Лобове', 124], ['Бічні', 186], ['Дзеркала', 218]]],
                ['title' => 'Освітлення', 'items' => [['Фари передні', 286], ['Лампи H4/H7', 412], ['LED', 196]]],
                ['title' => 'Кріплення', 'items' => [['Молдинги', 124], ['Кліпси', 286], ['Решітки', 96]]],
            ]],
            ['id' => 'interior', 'label' => 'Салон та комфорт', 'count' => 1240, 'groups' => [
                ['title' => 'Опорядження', 'items' => [['Килимки', 312], ['Чохли', 184]]],
                ['title' => 'Клімат', 'items' => [['Радіатор пічки', 84], ['Вентилятор салону', 124]]],
                ['title' => 'Кермо', 'items' => [['Перемикачі', 124], ['Замки запалювання', 84]]],
            ]],
            ['id' => 'filters', 'label' => 'Фільтри', 'count' => 980, 'groups' => [
                ['title' => 'Фільтри', 'items' => [['Масляні', 312], ['Повітряні', 286], ['Паливні', 218], ['Салону', 164]]],
            ]],
            ['id' => 'oils', 'label' => 'Олива, хімія', 'count' => 1640, 'groups' => [
                ['title' => 'Моторні оливи', 'items' => [['5W-30', 286], ['5W-40', 312], ['10W-40', 184]]],
                ['title' => 'Трансмісійні', 'items' => [['АКПП', 184], ['МКПП', 142]]],
                ['title' => 'Хімія', 'items' => [['Антифриз', 186], ['Гальмівна', 124]]],
            ]],
            ['id' => 'tires', 'label' => 'Шини та диски', 'count' => 760, 'groups' => [
                ['title' => 'Шини', 'items' => [['Літо', 218], ['Зима', 286], ['Всесезон', 124]]],
                ['title' => 'Диски', 'items' => [['Литі', 96], ['Сталеві', 142]]],
            ]],
            ['id' => 'transmission', 'label' => 'Трансмісія', 'count' => 890, 'groups' => [
                ['title' => 'Зчеплення', 'items' => [['Комплекти', 184], ['Диски', 124]]],
                ['title' => 'Привод', 'items' => [['ШРУСи', 218], ['Пильовики ШРУСа', 184]]],
            ]],
            ['id' => 'lights', 'label' => 'Освітлення', 'count' => 1420, 'groups' => [
                ['title' => 'Зовнішнє', 'items' => [['Фари передні', 286], ['Фари задні', 224]]],
                ['title' => 'Лампи', 'items' => [['H4/H7', 412], ['LED', 196], ['Ксенон', 84]]],
            ]],
            ['id' => 'tools', 'label' => 'Інструмент', 'count' => 540, 'groups' => [
                ['title' => 'Ручний', 'items' => [['Ключі', 184], ['Знімачі', 96], ['Викрутки', 124]]],
                ['title' => 'Спецінструмент', 'items' => [['OBD діагностика', 86]]],
            ]],
        ];
    }

    private function fallbackBrands(): array
    {
        $names = ['Bosch', 'SACHS', 'Lemförder', 'Febi', 'Mahle', 'NGK', 'Brembo', 'Mann', 'Continental', 'Valeo', 'Hella', 'Castrol'];
        return array_map(fn ($n) => [
            'name' => $n,
            'slug' => \Illuminate\Support\Str::slug($n),
        ], $names);
    }
}
