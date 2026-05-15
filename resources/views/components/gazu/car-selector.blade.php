@props([
    'variant' => 'catalog',
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
    Compact cascade selector. Auto-submits to /catalog?make=&model=&engine=
    as soon as the engine is picked — no extra «Знайти запчастини» button.
--}}
<div x-data="gazuCarSelector({
        initialMake: @js((string) $selectedMake),
        initialModel: @js((string) $selectedModel),
        initialEngine: @js((string) $selectedEngine),
        catalogUrl: @js($catalogUrl),
        autoSubmit: true,
        api: { makes: @js($apiMakes), models: @js($apiModels), engines: @js($apiEngines) },
     })"
     x-init="init()"
     class="gazu-car-selector relative w-full font-text
            {{ $isHero
                ? 'p-5 sm:p-6 bg-white rounded-2xl shadow-[0_20px_50px_-30px_rgba(14,27,44,0.22)]'
                : 'p-3 bg-white rounded-xl shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]' }}">

    {{-- Header — мінімалістичний. Subtitle ховаємо коли selector в done-режимі
         (зайвий контекст коли всі чипи й так видно). --}}
    <div class="flex items-center justify-between mb-2 gap-2">
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[13px] sm:text-[14px] font-semibold text-[var(--gazu-ink)] leading-tight">Підбір по авто</div>
                <div x-show="activeLevel() || _redirecting"
                     class="text-[10px] sm:text-[11px] text-[var(--gazu-graphite)] leading-tight" x-text="stepLabel()"></div>
            </div>
        </div>
        <button type="button"
                @click="reset()"
                x-show="hasAnySelection()" x-cloak
                class="text-[11px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0 inline-flex items-center gap-1 shrink-0">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Скинути
        </button>
    </div>

    {{-- Chip row — reserves space to prevent jump --}}
    <div class="flex flex-wrap items-center gap-1 mb-2 min-h-[24px]">
        <template x-for="chip in pickedChips()" :key="chip.level">
            <div class="inline-flex items-center gap-1 pl-1 pr-1.5 py-0.5 rounded-full bg-[var(--gazu-mist)] text-[11px] text-[var(--gazu-ink)] max-w-full">
                <div class="w-4 h-4 rounded-full bg-white inline-flex items-center justify-center text-[8px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="chip.badge"></div>
                <span class="font-medium truncate" x-text="chip.label"></span>
                <button type="button"
                        @click="changeLevel(chip.level)"
                        class="text-[9px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0 shrink-0">×</button>
            </div>
        </template>
    </div>

    {{-- Active step tile grid — no search input (брендів мало, не потрібен).
         Adaptive grid: 2 cols mobile, 2 cols hero (вузький), 3-4 cols catalog (широкий). --}}
    <div x-show="activeLevel()" class="min-h-[244px] sm:min-h-[252px] flex flex-col">
        {{-- Loading skeleton --}}
        <div x-show="loading" x-cloak class="grid grid-cols-2 gap-2 flex-1">
            <template x-for="i in 4" :key="i">
                <div class="rounded-lg bg-[var(--gazu-paper)] animate-pulse min-h-[56px]"></div>
            </template>
        </div>

        {{-- Tile grid: hero uses 2-cols (tight column), catalog uses 3-4 cols (wide) --}}
        <div x-show="!loading && filteredItems().length > 0"
             :class="expanded ? 'max-h-none' : 'max-h-[280px]'"
             class="gazu-tile-grid grid grid-cols-2 {{ $isHero ? 'sm:grid-cols-2' : 'sm:grid-cols-3 md:grid-cols-4' }} gap-2 overflow-y-auto content-start transition-[max-height] flex-1">
            <template x-for="(item, idx) in filteredItems()" :key="activeLevel() + ':' + itemKey(item)">
                <button type="button"
                        @click="pick(item)"
                        :style="'--gazu-tile-delay: ' + (idx * 18) + 'ms'"
                        :class="isItemSelected(item) ? 'bg-[var(--gazu-mist)] shadow-[inset_0_0_0_2px_var(--gazu-blue,#2563eb)]' : 'bg-white shadow-[0_1px_0_0_var(--gazu-line)] hover:bg-[var(--gazu-paper)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.18)]'"
                        class="gazu-tile-in flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[56px]">
                    <div class="w-9 h-9 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[10px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="itemBadge(item)"></div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium truncate leading-tight" x-text="itemPrimary(item)"></div>
                        <div x-show="itemSecondary(item)" class="text-[10px] text-[var(--gazu-graphite)] truncate leading-tight mt-0.5" x-text="itemSecondary(item)"></div>
                    </div>
                </button>
            </template>
        </div>

        <div x-show="!loading && filteredItems().length > 12" x-cloak class="mt-2 text-center">
            <button type="button" @click="expanded = !expanded" class="text-[11px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0">
                <span x-show="!expanded">Показати всі (<span x-text="filteredItems().length"></span>)</span>
                <span x-show="expanded" x-cloak>Згорнути</span>
            </button>
        </div>
    </div>

    {{-- Done state — селектор колапсує до тонкого filter-bar'а (тільки чипи + reset вгорі).
         Жодного додаткового візуального простору не займає. --}}

    {{-- Transition spinner — тільки під час pick→redirect (350ms вікно). --}}
    <div x-show="_redirecting" x-cloak
         class="min-h-[120px] flex items-center justify-center gap-2 px-3">
        <svg class="animate-spin shrink-0 text-[var(--gazu-blue)]" width="18" height="18" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <div class="text-[13px] font-medium text-[var(--gazu-ink)]">Шукаємо запчастини…</div>
    </div>
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
                _redirecting: false, // only true between engine-pick and URL navigation
                _opts: opts,

                async init() {
                    this.loading = true;
                    try { const r = await fetch(opts.api.makes, { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                    if (this.make) await this.fetchModels();
                    if (this.model) await this.fetchEngines();
                },

                async fetchModels() {
                    this.loading = true; this.models = []; this.search = ''; this.expanded = false;
                    try { const r = await fetch(opts.api.models + '?make=' + encodeURIComponent(this.make)); const d = await r.json(); this.models = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },
                async fetchEngines() {
                    this.loading = true; this.engines = []; this.search = ''; this.expanded = false;
                    try { const r = await fetch(opts.api.engines + '?make=' + encodeURIComponent(this.make) + '&model=' + encodeURIComponent(this.model)); const d = await r.json(); this.engines = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },

                activeLevel() {
                    if (! this.make) return 'make';
                    if (! this.model) return 'model';
                    if (! this.engine) return 'engine';
                    return null;
                },
                stepLabel() {
                    const l = this.activeLevel();
                    return l === 'make' ? 'Крок 1 · оберіть марку'
                         : l === 'model' ? 'Крок 2 · оберіть модель'
                         : l === 'engine' ? 'Крок 3 · оберіть двигун' : 'Готово';
                },
                currentList() {
                    const l = this.activeLevel();
                    return l === 'make' ? this.makes : l === 'model' ? this.models : l === 'engine' ? this.engines : [];
                },
                filteredItems() {
                    const list = this.currentList();
                    const q = (this.search || '').trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '') + ' ' + (item.slug || '')).toLowerCase().includes(q));
                },
                itemKey(item) { return this.activeLevel() === 'engine' ? item.code : item.slug; },
                isItemSelected(item) {
                    const l = this.activeLevel();
                    return (l === 'make' && item.slug === this.make) || (l === 'model' && item.slug === this.model) || (l === 'engine' && item.code === this.engine);
                },
                itemBadge(item) {
                    const l = this.activeLevel();
                    if (l === 'engine') return (item.code || '').substring(0, 4).toUpperCase();
                    return (item.name || '').substring(0, 2);
                },
                itemPrimary(item) {
                    const l = this.activeLevel();
                    return l === 'engine' ? (item.label || item.code) : item.name;
                },
                itemSecondary(item) {
                    const l = this.activeLevel();
                    if (l === 'engine') return item.hp ? (item.hp + ' к.с.' + (item.years_range ? ' · '+item.years_range : '')) : (item.years_range || '');
                    if (l === 'model')  return item.years_range || '';
                    return '';
                },
                pick(item) {
                    const l = this.activeLevel();
                    if (l === 'make') {
                        this.make = item.slug; this.model = ''; this.engine = ''; this.models = []; this.engines = [];
                        this.fetchModels();
                    } else if (l === 'model') {
                        this.model = item.slug; this.engine = ''; this.engines = [];
                        this.fetchEngines();
                    } else if (l === 'engine') {
                        this.engine = item.code;
                        // Auto-submit — small delay so the loading state renders briefly.
                        if (opts.autoSubmit) {
                            this._redirecting = true;
                            setTimeout(() => this.submit(), 350);
                        }
                    }
                    this.search = ''; this.expanded = false;
                },

                pickedChips() {
                    const chips = [];
                    if (this.make)   { const m = this.makes.find(x => x.slug === this.make);   chips.push({ level: 'make', label: m?.name || this.make, badge: (m?.name || this.make).substring(0, 2) }); }
                    if (this.model)  { const m = this.models.find(x => x.slug === this.model); chips.push({ level: 'model', label: m?.name || this.model, badge: (m?.name || this.model).substring(0, 2) }); }
                    if (this.engine) { const e = this.engines.find(x => x.code === this.engine); chips.push({ level: 'engine', label: e ? (e.label || e.code) : this.engine, badge: (this.engine).substring(0, 4).toUpperCase() }); }
                    return chips;
                },
                changeLevel(level) {
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
                    if (!(this.make && this.model && this.engine)) return;
                    const u = new URL(opts.catalogUrl, window.location.origin);
                    u.searchParams.set('make',   this.make);
                    u.searchParams.set('model',  this.model);
                    u.searchParams.set('engine', this.engine);
                    window.location.assign(u.toString());
                },
            }));
        };
        register();
    })();
</script>
@endonce
