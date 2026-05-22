@props([
    'variant' => 'catalog',
    'selectedMake' => null,
    'selectedModel' => null,
    'selectedEngine' => null,
    'initialMakes' => [],  // SSR-prefetched brand list — уникає pop-in після Alpine fetch
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
        initialMakes: @js($initialMakes),
        catalogUrl: @js($catalogUrl),
        autoSubmit: true,
        api: { makes: @js($apiMakes), models: @js($apiModels), engines: @js($apiEngines) },
     })"
     x-init="init()"
     :class="!activeLevel() && !_redirecting && !{{ $isHero ? 'true' : 'false' }} ? 'gazu-done-bar' : ''"
     class="gazu-car-selector relative w-full font-text
            {{ $isHero
                ? 'p-5 sm:p-6 bg-white rounded-2xl shadow-[0_20px_50px_-30px_rgba(14,27,44,0.22)]'
                : 'px-3 py-2.5 bg-white rounded-xl shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]' }}">

    {{-- DONE state (catalog only): single-row filter-bar з іконкою + чипами + reset.
         Hero завжди показує повний layout. --}}
    @if(! $isHero)
    <div x-show="!activeLevel() && !_redirecting" x-cloak class="flex items-center gap-2 flex-wrap">
        <div class="inline-flex items-center gap-1.5 shrink-0">
            <svg class="text-[var(--gazu-blue)]" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span class="text-[12px] sm:text-[13px] font-semibold text-[var(--gazu-ink)]">Авто:</span>
        </div>
        <div class="flex flex-wrap items-center gap-1.5 flex-1 min-w-0">
            <template x-for="chip in pickedChips()" :key="chip.level">
                <button type="button" @click="changeLevel(chip.level)"
                        class="inline-flex items-center gap-1.5 pl-1 pr-2.5 py-1 rounded-full bg-[var(--gazu-mist)] text-[12px] text-[var(--gazu-ink)] hover:bg-[var(--gazu-line)] cursor-pointer border-0 transition-colors">
                    <span class="w-5 h-5 rounded-full bg-white inline-flex items-center justify-center text-[8px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="chip.badge"></span>
                    <span class="font-medium truncate" x-text="chip.label"></span>
                </button>
            </template>
        </div>
        <button type="button" @click="reset()" class="text-[11px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0 inline-flex items-center gap-1 shrink-0">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Скинути
        </button>
    </div>
    @endif

    {{-- ACTIVE state header (під час вибору): title + step + reset. --}}
    <div x-show="activeLevel() || _redirecting || {{ $isHero ? 'true' : 'false' }}" x-cloak class="flex items-center justify-between gap-2 mb-2">
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9h6l2 4v5a2 2 0 0 1-2 2h-1"/><path d="M14 17H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h9z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[13px] sm:text-[14px] font-semibold text-[var(--gazu-ink)] leading-tight">Підбір по авто</div>
                <div x-show="activeLevel() || _redirecting" class="text-[10px] sm:text-[11px] text-[var(--gazu-graphite)] leading-tight" x-text="stepLabel()"></div>
            </div>
        </div>
        <button type="button" @click="reset()" x-show="hasAnySelection()" x-cloak
                class="text-[11px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0 inline-flex items-center gap-1 shrink-0">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Скинути
        </button>
    </div>

    {{-- Active chip row — тільки під час активного вибору (показує вже обрані рівні) --}}
    <div x-show="(activeLevel() || _redirecting) && hasAnySelection()" x-cloak class="flex flex-wrap items-center gap-1 mb-2">
        <template x-for="chip in pickedChips()" :key="chip.level">
            <button type="button" @click="changeLevel(chip.level)"
                    class="inline-flex items-center gap-1 pl-1 pr-2 py-0.5 rounded-full bg-[var(--gazu-mist)] text-[11px] text-[var(--gazu-ink)] hover:bg-[var(--gazu-line)] cursor-pointer border-0 transition-colors">
                <span class="w-4 h-4 rounded-full bg-white inline-flex items-center justify-center text-[8px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="chip.badge"></span>
                <span class="font-medium truncate" x-text="chip.label"></span>
            </button>
        </template>
    </div>

    {{-- Active step tile grid. Стабільний min-height резервує місце під ~3 ряди
         плиток, щоб блок не «скакав» при переході між кроками (8 марок → 2 моделі
         → 3 двигуни) та під час fetch. --}}
    <div x-show="activeLevel()" class="flex flex-col gazu-selector-step">
        {{-- Loading skeleton --}}
        <div x-show="loading" x-cloak class="grid grid-cols-2 gap-2 flex-1">
            <template x-for="i in 4" :key="i">
                <div class="rounded-lg bg-[var(--gazu-paper)] animate-pulse min-h-[56px]"></div>
            </template>
        </div>

        {{-- Tile grid: hero uses 2-cols (tight column), catalog uses 3-4 cols (wide) --}}
        <div x-show="!loading && filteredItems().length > 0"
             :class="(filteredItems().length > 12 && !expanded) ? 'max-h-[280px] overflow-y-auto' : 'overflow-visible'"
             class="gazu-tile-grid grid grid-cols-2 {{ $isHero ? 'sm:grid-cols-2' : 'sm:grid-cols-3 md:grid-cols-4' }} gap-2 content-start transition-[max-height] flex-1"
             style="scrollbar-gutter: stable;">
            <template x-for="(item, idx) in filteredItems()" :key="activeLevel() + ':' + itemKey(item)">
                <button type="button"
                        @click="pick(item)"
                        :style="'--gazu-tile-delay: ' + (idx * 18) + 'ms'"
                        :class="isItemSelected(item) ? 'bg-[var(--gazu-mist)] shadow-[inset_0_0_0_2px_var(--gazu-blue,#2563eb)]' : 'bg-white shadow-[0_1px_0_0_var(--gazu-line)] hover:bg-[var(--gazu-paper)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.18)]'"
                        class="gazu-tile-in flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[56px]">
                    <div class="w-9 h-9 rounded-md inline-flex items-center justify-center shrink-0 overflow-hidden"
                         :class="(item.logo && activeLevel() === 'make') ? '' : 'bg-[var(--gazu-mist)]'">
                        <template x-if="item.logo && activeLevel() === 'make'">
                            <img :src="item.logo" :alt="item.name" class="w-full h-full object-cover" loading="lazy">
                        </template>
                        <template x-if="!(item.logo && activeLevel() === 'make')">
                            <span class="text-[10px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase" x-text="itemBadge(item)"></span>
                        </template>
                    </div>
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
                makes: Array.isArray(opts.initialMakes) ? opts.initialMakes : [],
                models: [], engines: [],
                make: opts.initialMake || '',
                model: opts.initialModel || '',
                engine: opts.initialEngine || '',
                search: '', expanded: false, loading: false,
                _redirecting: false,
                _opts: opts,

                async init() {
                    // Якщо SSR вже передав makes — не fetch'ити (миттєвий рендер).
                    if (this.makes.length === 0) {
                        this.loading = true;
                        try { const r = await fetch(opts.api.makes, { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                        finally { this.loading = false; }
                    }
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
                    // Pretty URL: /zapchastyny/{make}/{model}/{engine}
                    const segs = ['zapchastyny', this.make, this.model, this.engine].map(encodeURIComponent);
                    window.location.assign(window.location.origin + '/' + segs.join('/'));
                },
            }));
        };
        register();
    })();
</script>
@endonce
