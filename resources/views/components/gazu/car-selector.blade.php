@props([
    'variant' => 'catalog', // 'catalog' | 'hero'
    'selectedMake' => null,
    'selectedModel' => null,
    'selectedEngine' => null,
])
@php
    $apiMakes   = route('gazu.api.cars.makes');
    $apiModels  = route('gazu.api.cars.models');
    $apiEngines = route('gazu.api.cars.engines');
    $catalogUrl = route('gazu.catalog');
    $isHero = $variant === 'hero';
@endphp
{{--
    Cascade selector — all three levels (марка → модель → двигун) як inline tiles.
    Selected levels collapse to compact "chip" pill row at the top with «змінити» link.
    The currently-active level renders a responsive grid; on long lists a search-input
    appears above and the grid scrolls within max-height. Mobile-first sizing.
--}}
<div x-data="gazuCarSelector({
        initialMake: @js((string) $selectedMake),
        initialModel: @js((string) $selectedModel),
        initialEngine: @js((string) $selectedEngine),
        catalogUrl: @js($catalogUrl),
        api: { makes: @js($apiMakes), models: @js($apiModels), engines: @js($apiEngines) },
     })"
     x-init="init()"
     class="gazu-car-selector relative w-full font-text
            {{ $isHero
                ? 'p-4 sm:p-5 md:p-6 bg-white rounded-2xl shadow-[0_24px_60px_-30px_rgba(14,27,44,0.25)]'
                : 'p-3 sm:p-4 bg-white rounded-xl shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]' }}">

    {{-- Header: title + reset --}}
    <div class="flex items-center justify-between mb-3 sm:mb-4">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[14px] sm:text-[16px] font-semibold text-[var(--gazu-ink)] leading-tight">Підбір по авто</div>
                <div class="text-[11px] sm:text-[12px] text-[var(--gazu-graphite)] leading-tight mt-0.5" x-text="currentStepHint()"></div>
            </div>
        </div>
        <button type="button"
                @click="reset()"
                x-show="hasAnySelection()" x-cloak
                class="text-[12px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0 inline-flex items-center gap-1 shrink-0">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Скинути
        </button>
    </div>

    {{-- Picked chips — horizontal row of selected levels with «змінити». --}}
    <div x-show="hasAnySelection()" x-cloak class="flex flex-wrap items-center gap-1.5 mb-3">
        <template x-for="chip in pickedChips()" :key="chip.level">
            <div class="inline-flex items-center gap-1.5 pl-1.5 pr-2 py-1 rounded-full bg-[var(--gazu-mist)] text-[12px] text-[var(--gazu-ink)] max-w-full">
                <div class="w-5 h-5 rounded-full bg-white inline-flex items-center justify-center text-[9px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="chip.badge"></div>
                <span class="font-medium truncate" x-text="chip.label"></span>
                <button type="button"
                        @click="changeLevel(chip.level)"
                        class="text-[10px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0 ml-1 shrink-0">змінити</button>
            </div>
        </template>
    </div>

    {{-- Active level: tile grid for current step --}}
    <div x-show="activeLevel()" class="mb-3 sm:mb-4">
        {{-- Step label --}}
        <div class="flex items-center justify-between mb-2">
            <div class="text-[10px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)]" x-text="stepLabel()"></div>
            {{-- Search appears only when list has many items --}}
            <div x-show="currentList().length > 6" class="relative max-w-[200px]">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--gazu-graphite)]" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" x-model="search" placeholder="Пошук…"
                       class="pl-7 pr-2 py-1.5 text-[12px] bg-white rounded-md outline-none shadow-[inset_0_0_0_1px_var(--gazu-line)] focus:shadow-[inset_0_0_0_1px_var(--gazu-blue,#2563eb)] w-full">
            </div>
        </div>

        {{-- Loading state --}}
        <div x-show="loading" x-cloak class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            <template x-for="i in 4" :key="i">
                <div class="rounded-lg bg-[var(--gazu-paper)] animate-pulse min-h-[52px]"></div>
            </template>
        </div>

        {{-- Tile grid: 2/3/4 cols based on viewport --}}
        <div x-show="!loading && filteredItems().length > 0"
             :class="expanded ? 'max-h-none' : 'max-h-[260px] sm:max-h-[280px]'"
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 overflow-y-auto transition-[max-height]">
            <template x-for="(item, idx) in filteredItems()" :key="itemKey(item)">
                <button type="button"
                        @click="pick(item)"
                        :class="[
                            isItemSelected(item) ? 'bg-[var(--gazu-mist)] shadow-[inset_0_0_0_2px_var(--gazu-blue,#2563eb)]' : 'bg-white shadow-[0_1px_0_0_var(--gazu-line)] hover:bg-[var(--gazu-paper)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.18)]',
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[52px] sm:min-h-[56px]">
                    {{-- Badge: 2-letter brand acronym for makes, otherwise the item code --}}
                    <div class="w-9 h-9 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[10px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                         x-text="itemBadge(item)"></div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium truncate leading-tight" x-text="itemPrimary(item)"></div>
                        <div x-show="itemSecondary(item)" class="text-[10px] text-[var(--gazu-graphite)] truncate leading-tight mt-0.5" x-text="itemSecondary(item)"></div>
                    </div>
                </button>
            </template>
        </div>

        {{-- "Show all" toggle for very long lists (above 12 items, ~3 rows on desktop) --}}
        <div x-show="!loading && filteredItems().length > 12" x-cloak class="mt-2 text-center">
            <button type="button" @click="expanded = !expanded"
                    class="text-[12px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0">
                <span x-show="!expanded">Показати всі (<span x-text="filteredItems().length"></span>)</span>
                <span x-show="expanded" x-cloak>Згорнути</span>
            </button>
        </div>

        {{-- Empty search results --}}
        <div x-show="!loading && filteredItems().length === 0 && search"
             class="text-center py-4 text-[12px] text-[var(--gazu-graphite)]">
            Нічого не знайдено за «<span x-text="search"></span>»
        </div>
    </div>

    {{-- Submit CTA — shown only once we have at least one selection --}}
    <button type="button"
            @click="submit()"
            :disabled="!hasAnySelection()"
            :class="hasAnySelection() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white' : 'bg-[var(--gazu-paper)] text-[var(--gazu-graphite)] cursor-not-allowed'"
            class="w-full py-3 border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <span>Знайти запчастини</span>
    </button>
</div>

@once
<script>
    (function() {
        if (typeof window.__gazuCarSelectorRegistered !== 'undefined') return;
        window.__gazuCarSelectorRegistered = true;
        const register = () => {
            if (! window.Alpine) { document.addEventListener('alpine:init', register, { once: true }); return; }
            window.Alpine.data('gazuCarSelector', (opts) => ({
                makes: [], models: [], engines: [],
                make: opts.initialMake || '',
                model: opts.initialModel || '',
                engine: opts.initialEngine || '',
                search: '', expanded: false, loading: false,
                _opts: opts,

                async init() {
                    this.loading = true;
                    try { const r = await fetch(opts.api.makes, { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                    if (this.make) await this.fetchModels();
                    if (this.model) await this.fetchEngines();
                },

                async fetchModels() {
                    this.loading = true; this.models = []; this.expanded = false; this.search = '';
                    try { const r = await fetch(opts.api.models + '?make=' + encodeURIComponent(this.make)); const d = await r.json(); this.models = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },
                async fetchEngines() {
                    this.loading = true; this.engines = []; this.expanded = false; this.search = '';
                    try { const r = await fetch(opts.api.engines + '?make=' + encodeURIComponent(this.make) + '&model=' + encodeURIComponent(this.model)); const d = await r.json(); this.engines = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },

                // Which level should render its tile grid right now.
                activeLevel() {
                    if (! this.make) return 'make';
                    if (! this.model) return 'model';
                    if (! this.engine) return 'engine';
                    return null; // all picked
                },
                stepLabel() {
                    const l = this.activeLevel();
                    return l === 'make' ? 'Крок 1 · Оберіть марку'
                         : l === 'model' ? 'Крок 2 · Оберіть модель'
                         : l === 'engine' ? 'Крок 3 · Оберіть двигун' : '';
                },
                currentStepHint() {
                    const l = this.activeLevel();
                    if (l === null) return 'Готово — натисніть «Знайти запчастини»';
                    return l === 'make' ? 'Почніть з марки авто'
                         : l === 'model' ? 'Тепер модель' : 'І двигун';
                },
                currentList() {
                    const l = this.activeLevel();
                    return l === 'make' ? this.makes : l === 'model' ? this.models : l === 'engine' ? this.engines : [];
                },
                filteredItems() {
                    const list = this.currentList();
                    const q = (this.search || '').trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => (
                        (item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '') + ' ' + (item.slug || '')
                    ).toLowerCase().includes(q));
                },
                itemKey(item) {
                    return this.activeLevel() === 'engine' ? item.code : item.slug;
                },
                isItemSelected(item) {
                    const l = this.activeLevel();
                    return (l === 'make' && item.slug === this.make)
                        || (l === 'model' && item.slug === this.model)
                        || (l === 'engine' && item.code === this.engine);
                },
                itemBadge(item) {
                    const l = this.activeLevel();
                    if (l === 'make')   return (item.name || '').substring(0, 2);
                    if (l === 'model')  return (item.name || '').substring(0, 2);
                    if (l === 'engine') return (item.code || '').substring(0, 4).toUpperCase();
                    return '';
                },
                itemPrimary(item) {
                    const l = this.activeLevel();
                    if (l === 'engine') return item.label || item.code;
                    return item.name;
                },
                itemSecondary(item) {
                    const l = this.activeLevel();
                    if (l === 'engine') return item.hp ? (item.hp + ' к.с.' + (item.years_range ? ' · ' + item.years_range : '')) : (item.years_range || '');
                    if (l === 'model')  return item.years_range || '';
                    return '';
                },
                pick(item) {
                    const l = this.activeLevel();
                    if (l === 'make') {
                        this.make = item.slug; this.model = ''; this.engine = '';
                        this.models = []; this.engines = [];
                        this.fetchModels();
                    } else if (l === 'model') {
                        this.model = item.slug; this.engine = ''; this.engines = [];
                        this.fetchEngines();
                    } else if (l === 'engine') {
                        this.engine = item.code;
                    }
                    this.search = ''; this.expanded = false;
                },

                // Picked-chip metadata for the breadcrumb-pill row at the top.
                pickedChips() {
                    const chips = [];
                    if (this.make) {
                        const m = this.makes.find(x => x.slug === this.make);
                        chips.push({ level: 'make', label: m?.name || this.make, badge: (m?.name || this.make).substring(0, 2) });
                    }
                    if (this.model) {
                        const m = this.models.find(x => x.slug === this.model);
                        chips.push({ level: 'model', label: m?.name || this.model, badge: (m?.name || this.model).substring(0, 2) });
                    }
                    if (this.engine) {
                        const e = this.engines.find(x => x.code === this.engine);
                        chips.push({ level: 'engine', label: e ? (e.label || e.code) : this.engine, badge: (this.engine).substring(0, 4).toUpperCase() });
                    }
                    return chips;
                },
                changeLevel(level) {
                    // Clear this level + all deeper levels so the user re-picks the cascade.
                    if (level === 'make')   { this.make = ''; this.model = ''; this.engine = ''; this.models = []; this.engines = []; }
                    if (level === 'model')  { this.model = ''; this.engine = ''; this.engines = []; }
                    if (level === 'engine') { this.engine = ''; }
                    this.search = ''; this.expanded = false;
                },

                hasAnySelection() { return !!(this.make || this.model || this.engine); },
                reset() {
                    this.make = ''; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.search = ''; this.expanded = false;
                    if (window.location.search) window.location.assign(opts.catalogUrl);
                },
                submit() {
                    if (!this.hasAnySelection()) return;
                    const u = new URL(opts.catalogUrl, window.location.origin);
                    if (this.make)   u.searchParams.set('make',   this.make);
                    if (this.model)  u.searchParams.set('model',  this.model);
                    if (this.engine) u.searchParams.set('engine', this.engine);
                    window.location.assign(u.toString());
                },
            }));
        };
        register();
    })();
</script>
@endonce
