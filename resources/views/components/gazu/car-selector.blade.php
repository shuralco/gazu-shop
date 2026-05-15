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
    Car selector — inline brand tiles + 2 cascade dropdowns (модель / двигун).
    Brand picker is always-visible: a responsive grid of tiles you click directly.
    Once a make is picked, the model+engine triggers unlock with searchable
    flyout panels that span the full host width below all triggers.
--}}
<div x-data="gazuCarSelector({
        initialMake: @js((string) $selectedMake),
        initialModel: @js((string) $selectedModel),
        initialEngine: @js((string) $selectedEngine),
        catalogUrl: @js($catalogUrl),
        api: {
            makes:   @js($apiMakes),
            models:  @js($apiModels),
            engines: @js($apiEngines),
        },
     })"
     x-init="init()"
     @click.outside="closeAll()"
     @keydown.escape.window="closeAll()"
     class="gazu-car-selector relative w-full font-text
            {{ $isHero
                ? 'p-5 md:p-6 bg-white rounded-2xl shadow-[0_24px_60px_-30px_rgba(14,27,44,0.25)]'
                : 'p-3 sm:p-4 bg-white rounded-xl shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]' }}">

    {{-- Header: title + reset (shown only after first selection) --}}
    <div class="flex items-center justify-between mb-3 sm:mb-4">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[14px] sm:text-[16px] font-semibold text-[var(--gazu-ink)] leading-tight">Підбір по авто</div>
                <div class="text-[11px] sm:text-[12px] text-[var(--gazu-graphite)] leading-tight mt-0.5">Марка → модель → двигун</div>
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

    {{-- Brand TILES — always visible, click-to-pick.
         Hidden once a make is chosen (compact bar shows the picked brand instead). --}}
    <div x-show="!make"
         class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
        <template x-for="m in makes" :key="m.slug">
            <button type="button"
                    @click="pickMake(m)"
                    :class="[
                        m.slug === make ? 'bg-[var(--gazu-mist)]' : 'bg-white hover:bg-[var(--gazu-paper)]',
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[48px]
                           shadow-[0_1px_0_0_var(--gazu-line)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.15)]">
                <div class="w-9 h-9 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[11px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                     x-text="m.name.substring(0, 2)"></div>
                <span class="font-medium truncate" x-text="m.name"></span>
            </button>
        </template>
        <template x-if="loadingMakes">
            <div class="col-span-2 sm:col-span-4 text-center text-[12px] text-[var(--gazu-graphite)] py-4">Завантаження…</div>
        </template>
    </div>

    {{-- Picked-make pill + 2 cascade triggers (modal / engine).
         Renders only after the make is chosen. --}}
    <div x-show="make" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
        {{-- Picked make — compact pill with brand badge + change-link --}}
        <div class="px-3 py-3 rounded-lg bg-[var(--gazu-mist)] flex items-center justify-between gap-2 min-h-[58px]">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-md bg-white inline-flex items-center justify-center text-[11px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                     x-text="makeName().substring(0, 2)"></div>
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)] leading-tight">Марка</div>
                    <div class="text-[14px] font-medium text-[var(--gazu-ink)] leading-tight truncate" x-text="makeName()"></div>
                </div>
            </div>
            <button type="button"
                    @click="changeMake()"
                    class="text-[11px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0 shrink-0">
                змінити
            </button>
        </div>

        @include('components.gazu.partials.car-selector-trigger', [
            'level' => 'model',
            'label' => 'Модель',
            'placeholderLocked' => 'Оберіть модель',
        ])

        @include('components.gazu.partials.car-selector-trigger', [
            'level' => 'engine',
            'label' => 'Двигун',
            'placeholderLocked' => 'Спершу модель',
        ])
    </div>

    {{-- Shared flyout panel for model / engine — full-host-width below triggers --}}
    <template x-if="openLevel">
        <div x-show="openLevel"
             x-transition:enter="transition-all ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-[0.99] -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition-all ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-[0.99]"
             :style="panelPositionStyle()"
             class="absolute z-40 bg-white rounded-xl shadow-[0_16px_40px_-12px_rgba(14,27,44,0.30)] overflow-hidden">
            <div class="p-2 bg-[var(--gazu-paper)]">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--gazu-graphite)]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="search"
                           @keydown.arrow-down.prevent="moveHighlight(1)"
                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                           @keydown.enter.prevent="commitHighlighted()"
                           placeholder="Пошук…"
                           class="w-full pl-8 pr-3 py-2 text-[13px] bg-white rounded-md outline-none shadow-[inset_0_0_0_1px_var(--gazu-line)] focus:shadow-[inset_0_0_0_1px_var(--gazu-blue,#2563eb)]">
                </div>
            </div>
            <div class="max-h-[320px] overflow-y-auto py-1">
                <template x-for="(item, idx) in filteredItems()" :key="(openLevel === 'engine' ? item.code : item.slug)">
                    <button type="button"
                            @click="pick(item)"
                            @mouseover="highlight = idx"
                            :class="[
                                highlight === idx ? 'bg-[var(--gazu-mist)]' : '',
                                isItemSelected(item) ? 'text-[var(--gazu-blue)] font-semibold' : 'text-[var(--gazu-ink)]',
                            ]"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 text-left text-[13px] cursor-pointer border-0 bg-transparent">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg x-show="isItemSelected(item)" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[var(--gazu-blue)]"><path d="M20 6L9 17l-5-5"/></svg>
                            <span x-show="!isItemSelected(item)" class="w-[13px] shrink-0"></span>
                            <span class="truncate" x-text="itemPrimaryLabel(item)"></span>
                        </div>
                        <span x-show="itemSecondaryLabel(item)"
                              x-text="itemSecondaryLabel(item)"
                              class="text-[11px] gazu-mono text-[var(--gazu-graphite)] shrink-0"></span>
                    </button>
                </template>
                <template x-if="filteredItems().length === 0">
                    <div class="px-3 py-6 text-center text-[12px] text-[var(--gazu-graphite)]">
                        <span x-show="loading">Завантаження…</span>
                        <span x-show="!loading">Нічого не знайдено</span>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Submit CTA --}}
    <div class="mt-3 sm:mt-4">
        <button type="button"
                @click="submit()"
                :disabled="!hasAnySelection()"
                :class="hasAnySelection() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white' : 'bg-[var(--gazu-paper)] text-[var(--gazu-graphite)] cursor-not-allowed'"
                class="w-full py-3 border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span>Знайти запчастини</span>
        </button>
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
                openLevel: null,
                search: '', highlight: 0, loading: false, loadingMakes: false,
                _host: null,

                async init() {
                    this._host = this.$el;
                    this.loadingMakes = true;
                    try { const r = await fetch(opts.api.makes, { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                    finally { this.loadingMakes = false; }
                    if (this.make) await this.fetchModels();
                    if (this.model) await this.fetchEngines();
                    this.$watch('openLevel', (v) => { this.search = ''; this.highlight = 0; if (v) this.$nextTick(() => this.$refs.searchInput?.focus()); });
                },

                async fetchModels() {
                    this.loading = true; this.models = [];
                    try { const r = await fetch(opts.api.models + '?make=' + encodeURIComponent(this.make)); const d = await r.json(); this.models = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },
                async fetchEngines() {
                    this.loading = true; this.engines = [];
                    try { const r = await fetch(opts.api.engines + '?make=' + encodeURIComponent(this.make) + '&model=' + encodeURIComponent(this.model)); const d = await r.json(); this.engines = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
                },

                pickMake(m) {
                    this.make = m.slug; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.openLevel = null;
                    this.fetchModels();
                },
                changeMake() {
                    this.make = ''; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.openLevel = null;
                },

                triggerState(level) {
                    if (level === 'model') {
                        if (!this.make) return { locked: true, selected: false, label: null };
                        const m = this.models.find(x => x.slug === this.model);
                        return { locked: false, selected: !!m, label: m ? (m.name + (m.years_range ? ' ('+m.years_range+')' : '')) : null };
                    }
                    if (level === 'engine') {
                        if (!this.model) return { locked: true, selected: false, label: null };
                        const e = this.engines.find(x => x.code === this.engine);
                        return { locked: false, selected: !!e, label: e ? ((e.label || e.code) + (e.hp ? ' · '+e.hp+' к.с.' : '')) : null };
                    }
                    return { locked: true, selected: false, label: null };
                },
                toggleLevel(level) { const st = this.triggerState(level); if (st.locked) return; this.openLevel = this.openLevel === level ? null : level; },
                closeAll() { this.openLevel = null; },

                filteredItems() {
                    const list = this.openLevel === 'model' ? this.models : this.openLevel === 'engine' ? this.engines : [];
                    const q = this.search.trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '')).toLowerCase().includes(q));
                },
                isItemSelected(item) {
                    return (this.openLevel === 'model'  && item.slug === this.model)
                        || (this.openLevel === 'engine' && item.code === this.engine);
                },
                itemPrimaryLabel(item) { return this.openLevel === 'engine' ? (item.label || item.code) : (item.name + (item.years_range ? ' ('+item.years_range+')' : '')); },
                itemSecondaryLabel(item) { return this.openLevel === 'engine' && item.hp ? (item.hp + ' к.с.') : ''; },

                moveHighlight(d) { const list = this.filteredItems(); if (!list.length) return; this.highlight = (this.highlight + d + list.length) % list.length; },
                commitHighlighted() { const list = this.filteredItems(); if (list.length) this.pick(list[this.highlight]); },
                pick(item) {
                    if (this.openLevel === 'model')       { this.model = item.slug; this.engine = ''; this.engines = []; this.openLevel = null; this.fetchEngines(); }
                    else if (this.openLevel === 'engine') { this.engine = item.code; this.openLevel = null; }
                },

                panelPositionStyle() {
                    const host = this._host || this.$el;
                    const triggers = host.querySelectorAll('[data-trigger]');
                    if (! triggers.length) return '';
                    let maxBottom = 0;
                    triggers.forEach(t => { const r = t.getBoundingClientRect(); if (r.bottom > maxBottom) maxBottom = r.bottom; });
                    const hostR = host.getBoundingClientRect();
                    return `left: 0; right: 0; top: ${maxBottom - hostR.top + 6}px;`;
                },

                makeName() { const m = this.makes.find(x => x.slug === this.make); return m ? m.name : ''; },
                hasAnySelection() { return !!(this.make || this.model || this.engine); },
                reset() {
                    this.make = ''; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.openLevel = null;
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
