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
    Custom searchable combobox-style cascade. Replaces native <select> with a
    button-anchored panel that supports:
      • live text search (filters list as you type)
      • keyboard nav (↑/↓/Enter/Esc)
      • selected-state pill (green dot) so users see progress
      • smooth scale+fade transition for the panel
    Each level (марка / модель / двигун) unlocks the next, with a visible
    "lock" icon and helper text on disabled levels.
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
     class="gazu-car-selector w-full font-text relative
            {{ $isHero
                ? 'p-5 md:p-7 bg-white rounded-2xl border border-[var(--gazu-line)] shadow-[0_24px_60px_-30px_rgba(14,27,44,0.30)]'
                : 'p-3 sm:p-4 bg-white rounded-xl border border-[var(--gazu-line)]' }}">

    {{-- Header: title + reset (hero) / hide on catalog (which has reset on the right) --}}
    <div class="flex items-center justify-between mb-3 sm:mb-4">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12.42V16h2"/><circle cx="6.5" cy="16.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/></svg>
            </div>
            <div>
                <div class="text-[14px] sm:text-[16px] font-semibold text-[var(--gazu-ink)] leading-tight">Підбір запчастин по авто</div>
                <div class="text-[11px] sm:text-[12px] text-[var(--gazu-graphite)] leading-tight mt-0.5">Виберіть марку, модель і двигун</div>
            </div>
        </div>
        <button type="button"
                @click="reset()"
                x-show="hasAnySelection()" x-cloak
                class="text-[12px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0 inline-flex items-center gap-1">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Скинути
        </button>
    </div>

    {{-- 3 trigger buttons in a row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
        {{-- Make trigger --}}
        @include('components.gazu.partials.car-selector-trigger', [
            'level' => 'make',
            'label' => 'Марка',
            'placeholderLocked' => 'Оберіть марку',
            'unlocked' => true,
        ])

        {{-- Model trigger --}}
        @include('components.gazu.partials.car-selector-trigger', [
            'level' => 'model',
            'label' => 'Модель',
            'placeholderLocked' => 'Спершу марку',
            'unlocked' => false, // x-bind handles the dynamic state
        ])

        {{-- Engine trigger --}}
        @include('components.gazu.partials.car-selector-trigger', [
            'level' => 'engine',
            'label' => 'Двигун',
            'placeholderLocked' => 'Спершу модель',
            'unlocked' => false,
        ])
    </div>

    {{-- Single shared panel — positioned absolutely below the triggers via the level it belongs to. --}}
    <template x-if="openLevel">
        <div x-show="openLevel"
             x-transition:enter="transition-all ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition-all ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             :style="panelPositionStyle()"
             class="absolute z-40 bg-white border border-[var(--gazu-line)] rounded-xl shadow-[0_16px_40px_-12px_rgba(14,27,44,0.35)] overflow-hidden">
            {{-- Search box --}}
            <div class="p-2 border-b border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--gazu-graphite)]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="search"
                           @keydown.arrow-down.prevent="moveHighlight(1)"
                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                           @keydown.enter.prevent="commitHighlighted()"
                           placeholder="Пошук…"
                           class="w-full pl-8 pr-3 py-2 text-[13px] bg-white border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </div>
            </div>

            {{-- Brand-grid view (makes only): responsive 2/3/4 cols depending on host width --}}
            <template x-if="openLevel === 'make' && !search">
                <div class="p-2.5 max-h-[320px] overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        <template x-for="(item, idx) in filteredItems()" :key="item.slug">
                            <button type="button"
                                    @click="pick(item)"
                                    @mouseover="highlight = idx"
                                    :class="[
                                        highlight === idx ? 'border-[var(--gazu-blue,#2563eb)] bg-[var(--gazu-mist)]' : 'border-[var(--gazu-line)] hover:border-[var(--gazu-line-2)] hover:bg-[var(--gazu-paper)]',
                                        item.slug === make ? 'border-[var(--gazu-blue,#2563eb)] bg-[var(--gazu-mist)]' : '',
                                    ]"
                                    class="flex items-center gap-2.5 px-3 py-2.5 bg-white border rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[46px]">
                                <div class="w-8 h-8 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[10px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                                     x-text="item.name.substring(0, 2)"></div>
                                <span class="font-medium truncate" x-text="item.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- List view (model/engine, or makes when searching) --}}
            <template x-if="openLevel !== 'make' || search">
                <div class="max-h-[280px] overflow-y-auto py-1">
                    <template x-for="(item, idx) in filteredItems()" :key="(openLevel === 'engine' ? item.code : item.slug)">
                        <button type="button"
                                @click="pick(item)"
                                @mouseover="highlight = idx"
                                :class="[
                                    highlight === idx ? 'bg-[var(--gazu-mist)]' : '',
                                    isItemSelected(item) ? 'text-[var(--gazu-blue)] font-semibold' : 'text-[var(--gazu-ink)]',
                                ]"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2 text-left text-[13px] cursor-pointer border-0 bg-transparent">
                            <div class="flex items-center gap-2 min-w-0">
                                {{-- selection check --}}
                                <svg x-show="isItemSelected(item)" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[var(--gazu-blue)]"><path d="M20 6L9 17l-5-5"/></svg>
                                <span x-show="!isItemSelected(item)" class="w-[13px] shrink-0"></span>
                                <span class="truncate" x-text="itemPrimaryLabel(item)"></span>
                            </div>
                            <span x-show="itemSecondaryLabel(item)"
                                  x-text="itemSecondaryLabel(item)"
                                  class="text-[11px] gazu-mono text-[var(--gazu-graphite)] shrink-0"></span>
                        </button>
                    </template>
                    {{-- Empty state --}}
                    <template x-if="filteredItems().length === 0">
                        <div class="px-3 py-6 text-center text-[12px] text-[var(--gazu-graphite)]">
                            <span x-show="loading">Завантаження…</span>
                            <span x-show="!loading">Нічого не знайдено</span>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>

    {{-- Submit CTA --}}
    <div class="mt-3 sm:mt-4">
        <button type="button"
                @click="submit()"
                :disabled="!hasAnySelection()"
                :class="hasAnySelection() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]' : 'bg-[var(--gazu-line-2)] cursor-not-allowed text-[var(--gazu-graphite)]'"
                class="w-full py-3 text-white border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
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
                openLevel: null,        // 'make' | 'model' | 'engine' | null
                search: '',
                highlight: 0,
                loading: false,
                _opts: opts,

                async init() {
                    // Capture host root for cross-template DOM queries — $el shifts when
                    // methods are called from inside <template x-if> blocks.
                    this._host = this.$el;
                    this.loading = true;
                    try { const r = await fetch(opts.api.makes, { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
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

                // Trigger state per level: { selected, label, locked, badge }
                triggerState(level) {
                    if (level === 'make') {
                        const m = this.makes.find(x => x.slug === this.make);
                        return { locked: false, selected: !!m, label: m ? m.name : null };
                    }
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

                toggleLevel(level) {
                    const st = this.triggerState(level);
                    if (st.locked) return;
                    this.openLevel = this.openLevel === level ? null : level;
                },
                closeAll() { this.openLevel = null; },

                // Items shown in the panel for the currently-open level, filtered by search.
                filteredItems() {
                    const list = this.openLevel === 'make' ? this.makes
                               : this.openLevel === 'model' ? this.models
                               : this.openLevel === 'engine' ? this.engines : [];
                    const q = this.search.trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => {
                        const hay = (item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '') + ' ' + (item.slug || '');
                        return hay.toLowerCase().includes(q);
                    });
                },
                isItemSelected(item) {
                    return (this.openLevel === 'make'   && item.slug === this.make)
                        || (this.openLevel === 'model'  && item.slug === this.model)
                        || (this.openLevel === 'engine' && item.code === this.engine);
                },
                itemPrimaryLabel(item) {
                    if (this.openLevel === 'engine') return item.label || item.code;
                    return item.name + (item.years_range ? ' ('+item.years_range+')' : '');
                },
                itemSecondaryLabel(item) {
                    if (this.openLevel === 'engine') return item.hp ? (item.hp + ' к.с.') : '';
                    return '';
                },
                moveHighlight(d) {
                    const list = this.filteredItems();
                    if (!list.length) return;
                    this.highlight = (this.highlight + d + list.length) % list.length;
                    this.$nextTick(() => {
                        const opts = this.$el.querySelectorAll('[class*="bg-[var(--gazu-mist)]"]');
                    });
                },
                commitHighlighted() {
                    const list = this.filteredItems();
                    if (list.length) this.pick(list[this.highlight]);
                },
                pick(item) {
                    if (this.openLevel === 'make') {
                        this.make = item.slug; this.model = ''; this.engine = ''; this.models = []; this.engines = [];
                        this.openLevel = null;
                        this.fetchModels();
                    } else if (this.openLevel === 'model') {
                        this.model = item.slug; this.engine = ''; this.engines = [];
                        this.openLevel = null;
                        this.fetchEngines();
                    } else if (this.openLevel === 'engine') {
                        this.engine = item.code; this.openLevel = null;
                    }
                },

                panelPositionStyle() {
                    // Panel spans the full host width and drops below the row of all triggers
                    // (not just the clicked column) — feels like a single dropdown surface.
                    const host = this._host || this.$el;
                    const triggers = host.querySelectorAll('[data-trigger]');
                    if (! triggers.length) return '';
                    let maxBottom = 0;
                    triggers.forEach(t => { const r = t.getBoundingClientRect(); if (r.bottom > maxBottom) maxBottom = r.bottom; });
                    const hostR = host.getBoundingClientRect();
                    return `left: 0; right: 0; top: ${maxBottom - hostR.top + 6}px;`;
                },

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
