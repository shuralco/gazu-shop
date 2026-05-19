@props([
    'priceRange' => ['min' => 0, 'max' => 10000, 'currentMin' => 0, 'currentMax' => 10000],
    'availableCategories' => null,
    'availableBrands' => collect(),
    'selectedBrands' => [],
    'availableConditions' => null,
    'selectedConditions' => [],
    'inStockOnly' => false,
    'searchQuery' => '',
    'category' => null,
])
@php
    $brands = collect($availableBrands);
    $selected = collect($selectedBrands);
    $rangeFromUrl = ['min','max','sort'];
    $hasFilters = !empty(request('brand')) || request()->filled('min') || request()->filled('max') || request('stock') === 'in';
@endphp

<form method="GET" action="{{ url()->current() }}" class="font-text text-sm" x-data="{
        priceMin: '{{ request()->filled('min') ? (int) request('min') : '' }}',
        priceMax: '{{ request()->filled('max') ? (int) request('max') : '' }}'
    }">
    {{-- Зберігаємо з URL: ?cat, ?q, ?sort при subimt --}}
    @foreach (['cat', 'q', 'sort'] as $kept)
        @if (request()->filled($kept))
            <input type="hidden" name="{{ $kept }}" value="{{ request($kept) }}">
        @endif
    @endforeach

    {{-- Ваш автомобіль — модуль gazu_garage --}}
    @if(module('gazu_garage')->enabled())
        @php
            $primaryCar = auth()->check() ? auth()->user()->primaryCar : null;
            $rows = [];
            if ($primaryCar) {
                $rows = [
                    ['Марка', $primaryCar->make, true],
                    ['Модель', $primaryCar->model, true],
                    ['Рік', $primaryCar->year ?: '—', (bool) $primaryCar->year],
                    ['Двигун', $primaryCar->engine ?: '—', (bool) $primaryCar->engine],
                    ['Кузов', $primaryCar->body_type ?: '—', (bool) $primaryCar->body_type],
                ];
            }
        @endphp
        <div class="bg-[var(--gazu-mist)] p-4 rounded-lg mb-5">
            <div class="text-xs gazu-mono text-[var(--gazu-graphite)] tracking-widest uppercase mb-2.5">Ваш автомобіль</div>
            @if($primaryCar)
                <div class="flex flex-col gap-2">
                    @foreach($rows as [$k, $v, $filled])
                        <div class="flex items-center gap-2.5 px-2.5 py-2 bg-white rounded {{ $filled ? 'border border-[var(--gazu-line)]' : 'border border-[var(--gazu-line-2)] opacity-60' }}">
                            <span class="text-[11px] text-[var(--gazu-graphite)] w-14">{{ $k }}</span>
                            <span class="flex-1 text-[13px] {{ $filled ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-muted)]' }}">{{ $v }}</span>
                            @if($filled)<x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/>@endif
                        </div>
                    @endforeach
                </div>
                <a wire:navigate href="{{ route('gazu.garage') }}" class="block w-full mt-2.5 py-2 bg-transparent border border-dashed border-[var(--gazu-line-2)] rounded text-xs text-[var(--gazu-graphite)] cursor-pointer text-center no-underline">Змінити авто</a>
            @else
                <p class="text-xs text-[var(--gazu-graphite)] mb-2">@auth Додайте авто у Гараж — фільтр буде підставляти його автоматично @else Увійдіть, щоб зберегти своє авто @endauth</p>
                <a wire:navigate href="{{ auth()->check() ? route('gazu.garage') : route('gazu.auth') }}"
                   class="block w-full py-2 bg-[var(--gazu-ink)] text-white rounded text-xs text-center no-underline hover:bg-[var(--gazu-ink-2)]">
                    @auth + Додати авто @else Увійти @endauth
                </a>
            @endif
        </div>
    @endif

    {{-- Ціна --}}
    <details class="border-b border-[var(--gazu-line)] py-3.5" open>
        <summary class="flex justify-between items-center cursor-pointer list-none">
            <span class="text-sm font-medium text-[var(--gazu-ink)]">Ціна, ₴</span>
            <x-gazu.icon name="chevron" size="16" stroke="var(--gazu-graphite)"/>
        </summary>
        <div class="mt-3">
            <div class="flex gap-2">
                <input type="number" name="min" x-model="priceMin"
                       min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}"
                       placeholder="від {{ (int) $priceRange['min'] }}"
                       class="flex-1 py-2 px-2.5 text-[13px] gazu-mono border border-[var(--gazu-line)] rounded bg-white outline-none placeholder:text-[var(--gazu-muted)]">
                <input type="number" name="max" x-model="priceMax"
                       min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}"
                       placeholder="до {{ (int) $priceRange['max'] }}"
                       class="flex-1 py-2 px-2.5 text-[13px] gazu-mono border border-[var(--gazu-line)] rounded bg-white outline-none placeholder:text-[var(--gazu-muted)]">
            </div>
            <div class="text-[11px] text-[var(--gazu-muted)] mt-2">
                Від <span class="gazu-mono">{{ number_format($priceRange['min'], 0, '.', ' ') }} ₴</span>
                до <span class="gazu-mono">{{ number_format($priceRange['max'], 0, '.', ' ') }} ₴</span>
            </div>
        </div>
    </details>

    {{-- Категорія — drill-down. Показуємо тільки якщо є availableCategories.
         Filter persistence: при переході на іншу категорію → preserve існуючі
         filters (brand, min, max, condition, stock, sort) у URL. --}}
    @php
        $catList = collect($availableCategories ?? []);
        // Збираємо filter query string що треба зберегти.
        $preserveParams = collect(['brand', 'min', 'max', 'condition', 'stock', 'sort', 'q'])
            ->mapWithKeys(fn ($k) => [$k => request($k)])
            ->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])
            ->all();
        $preserveQuery = ! empty($preserveParams) ? '?'.http_build_query($preserveParams) : '';
    @endphp
    @if($catList->isNotEmpty())
        @php $catLimit = 8; $catHidden = max(0, $catList->count() - $catLimit); @endphp
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">
                    {{ $category ? 'Підкатегорії' : 'Категорія' }}
                </span>
                <x-gazu.icon name="chevron" size="16" stroke="var(--gazu-graphite)"/>
            </summary>
            <div class="mt-3" x-data="{ showAllCats: false }">
                @if($category)
                    {{-- 'Усі' link — повертає на корінь catalog без category, з збереженням filters --}}
                    <a wire:navigate href="{{ route('gazu.catalog').$preserveQuery }}"
                       class="flex items-center gap-2 py-1.5 text-[13px] text-[var(--gazu-blue)] no-underline hover:text-[var(--gazu-ink)]">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Усі категорії
                    </a>
                @endif
                @foreach($catList as $i => $cat)
                    @php
                        $rawSlug = $cat->getRawOriginal('slug');
                        if (is_string($rawSlug) && str_starts_with($rawSlug, '{')) {
                            $rawSlug = json_decode($rawSlug, true)['uk'] ?? null;
                        }
                        $catSlug = (string) ($rawSlug ?: '');
                        $catTitle = is_array($cat->title) ? ($cat->title['uk'] ?? '—') : ($cat->title ?? '—');
                        $catCount = $cat->products_count ?? 0;
                        $hidden = $i >= $catLimit;
                    @endphp
                    <a wire:navigate href="{{ url('/'.$catSlug).$preserveQuery }}"
                       class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)] no-underline"
                       @if($hidden) x-show="showAllCats" x-cloak @endif>
                        <span class="flex-1 truncate">{{ $catTitle }}</span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono">{{ $catCount }}</span>
                    </a>
                @endforeach
                @if($catHidden > 0)
                    <button type="button"
                            @click.prevent="showAllCats = !showAllCats"
                            class="mt-2 text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline inline-flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0">
                        <span x-show="!showAllCats">Показати ще {{ $catHidden }}</span>
                        <span x-show="showAllCats" x-cloak>Згорнути</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="showAllCats ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                @endif

                {{-- Footer actions: переключити головну категорію АБО переглянути всі товари --}}
                <div class="mt-3 pt-3 border-t border-[var(--gazu-line)] flex flex-col gap-1.5">
                    @if($category)
                        {{-- Drill-down state — пропонуємо вийти на інші головні --}}
                        <a wire:navigate href="{{ route('gazu.catalog') }}"
                           class="flex items-center justify-between gap-2 px-2.5 py-1.5 bg-[var(--gazu-mist)] hover:bg-[var(--gazu-paper)] rounded text-[12px] text-[var(--gazu-ink)] no-underline transition-colors">
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                Обрати головну категорію
                            </span>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    @endif
                    <a wire:navigate href="{{ route('gazu.catalog') }}"
                       class="flex items-center justify-between gap-2 px-2.5 py-1.5 hover:bg-[var(--gazu-mist)] rounded text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline transition-colors">
                        <span class="inline-flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            Переглянути всі товари
                        </span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </details>
    @endif

    {{-- Виробник — truncate до 8, з тонкою 'Показати ще' для розкриття решти --}}
    @if($brands->isNotEmpty())
        @php
            $brandLimit = 8;
            $brandHidden = max(0, $brands->count() - $brandLimit);
        @endphp
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">Виробник</span>
                <x-gazu.icon name="chevron" size="16" stroke="var(--gazu-graphite)"/>
            </summary>
            <div class="mt-3" x-data="{ showAll: false }">
                @foreach($brands as $i => $row)
                    @php
                        $value = is_object($row) ? $row->manufacturer : ($row['manufacturer'] ?? '');
                        $label = is_object($row) ? ($row->label ?? $row->manufacturer) : ($row['label'] ?? $row['manufacturer'] ?? '');
                        $count = is_object($row) ? $row->count : ($row['count'] ?? 0);
                        $checked = $selected->contains($value);
                        $hidden = $i >= $brandLimit && ! $checked;
                    @endphp
                    <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]"
                           @if($hidden) x-show="showAll" x-cloak @endif>
                        <input type="checkbox" name="brand[]" value="{{ $value }}"
                               class="sr-only" {{ $checked ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <span class="w-4 h-4 border-[1.5px] {{ $checked ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white' }} rounded inline-flex items-center justify-center shrink-0">
                            @if($checked)<x-gazu.icon name="check" size="11" stroke="#fff" strokeWidth="2.5"/>@endif
                        </span>
                        <span class="flex-1">{{ $label }}</span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono">{{ $count }}</span>
                    </label>
                @endforeach
                @if($brandHidden > 0)
                    <button type="button"
                            @click.prevent="showAll = !showAll"
                            class="mt-2 text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline inline-flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0">
                        <span x-show="!showAll">Показати ще {{ $brandHidden }}</span>
                        <span x-show="showAll" x-cloak>Згорнути</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="showAll ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                @endif
            </div>
        </details>
    @endif

    {{-- Стан --}}
    @php
        $conditions = collect($availableConditions ?? collect());
        $selectedConds = collect($selectedConditions ?? []);
        $condLabels = ['new' => 'Новий', 'used' => 'Б/у', 'refurbished' => 'Відновлений'];
    @endphp
    @if($conditions->isNotEmpty())
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">Стан</span>
                <x-gazu.icon name="chevron" size="16" stroke="var(--gazu-graphite)"/>
            </summary>
            <div class="mt-3">
                @foreach($conditions as $row)
                    @php
                        $val = is_object($row) ? $row->condition : ($row['condition'] ?? '');
                        $count = is_object($row) ? $row->count : ($row['count'] ?? 0);
                        $checked = $selectedConds->contains($val);
                        $label = $condLabels[$val] ?? ucfirst($val);
                    @endphp
                    <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]">
                        <input type="checkbox" name="condition[]" value="{{ $val }}"
                               class="sr-only" {{ $checked ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <span class="w-4 h-4 border-[1.5px] {{ $checked ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white' }} rounded inline-flex items-center justify-center shrink-0">
                            @if($checked)<x-gazu.icon name="check" size="11" stroke="#fff" strokeWidth="2.5"/>@endif
                        </span>
                        <span class="flex-1">{{ $label }}</span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono">{{ $count }}</span>
                    </label>
                @endforeach
            </div>
        </details>
    @endif

    {{-- Наявність --}}
    <details class="border-b border-[var(--gazu-line)] py-3.5" {{ $inStockOnly ? 'open' : '' }}>
        <summary class="flex justify-between items-center cursor-pointer list-none">
            <span class="text-sm font-medium text-[var(--gazu-ink)]">Наявність</span>
            <x-gazu.icon name="chevron" size="16" stroke="var(--gazu-graphite)"/>
        </summary>
        <div class="mt-3">
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)]">
                <input type="checkbox" name="stock" value="in" class="sr-only"
                       {{ $inStockOnly ? 'checked' : '' }} onchange="this.form.submit()">
                <span class="w-4 h-4 border-[1.5px] {{ $inStockOnly ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white' }} rounded inline-flex items-center justify-center">
                    @if($inStockOnly)<x-gazu.icon name="check" size="11" stroke="#fff" strokeWidth="2.5"/>@endif
                </span>
                <span class="flex-1">Тільки в наявності</span>
            </label>
        </div>
    </details>

    <button type="submit" class="w-full mt-4 py-3 bg-[var(--gazu-ink)] text-white border-0 rounded text-[13px] font-medium cursor-pointer hover:bg-[var(--gazu-ink-2)]">
        Застосувати фільтри
    </button>
    @if($hasFilters || request()->filled('q'))
        <a wire:navigate href="{{ $category ? url()->current().'?cat='.($category->slug ?? $category->id) : url()->current() }}"
           class="block w-full mt-1.5 py-2 bg-transparent text-center text-[var(--gazu-graphite)] text-xs no-underline">
            Скинути всі фільтри
        </a>
    @endif
</form>
