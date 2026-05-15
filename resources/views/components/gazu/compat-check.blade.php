@props([
    'productId' => null,
])
@php
    $apiMakes   = route('gazu.api.cars.makes');
    $apiModels  = route('gazu.api.cars.models');
    $apiEngines = route('gazu.api.cars.engines');
    $apiCheck   = route('gazu.api.compat.check');
@endphp
{{--
    4D: Compat-check with the same searchable-combobox UX as the catalog selector.
    Calls /api/compat/check which reads product_compatibility pivot.
--}}
<section class="my-6 sm:my-8">
    <div x-data="gazuCompatCheck({
            productId: @js((int) $productId),
            api: {
                makes:   @js($apiMakes),
                models:  @js($apiModels),
                engines: @js($apiEngines),
                check:   @js($apiCheck),
            },
         })"
         x-init="init()"
         @click.outside="closeAll()"
         @keydown.escape.window="closeAll()"
         class="relative bg-white rounded-2xl p-5 sm:p-6 font-text shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <div>
                <div class="text-[15px] sm:text-[17px] font-semibold text-[var(--gazu-ink)]">Перевірити сумісність з авто</div>
                <div class="text-[12px] text-[var(--gazu-graphite)]">Виберіть марку, модель і двигун — ми перевіримо чи підходить</div>
            </div>
        </div>

        {{-- Brand tiles — same UX as catalog/hero --}}
        <div x-show="!make"
             class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
            <template x-for="m in makes" :key="m.slug">
                <button type="button"
                        @click="pickMake(m)"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] bg-white min-h-[48px]
                               shadow-[0_1px_0_0_var(--gazu-line)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.15)] hover:bg-[var(--gazu-paper)]">
                    <div class="w-9 h-9 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[11px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                         x-text="m.name.substring(0, 2)"></div>
                    <span class="font-medium truncate" x-text="m.name"></span>
                </button>
            </template>
        </div>

        <div x-show="make" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
            <div class="px-3 py-3 rounded-lg bg-[var(--gazu-mist)] flex items-center justify-between gap-2 min-h-[58px]">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-md bg-white inline-flex items-center justify-center text-[11px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0"
                         x-text="makeName().substring(0, 2)"></div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)] leading-tight">Марка</div>
                        <div class="text-[14px] font-medium text-[var(--gazu-ink)] leading-tight truncate" x-text="makeName()"></div>
                    </div>
                </div>
                <button type="button" @click="changeMake()" class="text-[11px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0 shrink-0">змінити</button>
            </div>
            @include('components.gazu.partials.compat-trigger', ['level' => 'model', 'label' => 'Модель', 'placeholderLocked' => 'Оберіть модель'])
            @include('components.gazu.partials.compat-trigger', ['level' => 'engine', 'label' => 'Двигун', 'placeholderLocked' => 'Спершу модель'])
        </div>

        {{-- Shared panel --}}
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

        <div class="mt-4 flex gap-3">
            <button type="button"
                    @click="check()"
                    :disabled="!canCheck()"
                    :class="canCheck() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]' : 'bg-[var(--gazu-line-2)] cursor-not-allowed text-[var(--gazu-graphite)]'"
                    class="px-6 py-2.5 text-white border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
                <span x-show="!checking">Перевірити</span>
                <span x-show="checking" x-cloak>Перевірка…</span>
            </button>
            <button type="button"
                    @click="reset()"
                    x-show="hasAnySelection()" x-cloak
                    class="px-4 py-2.5 bg-white border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] rounded-md text-[13px] cursor-pointer transition-colors">
                Скинути
            </button>
        </div>

        <div x-show="result" x-cloak x-transition.opacity.duration.200ms class="mt-4">
            <div :class="result?.fits ? 'bg-[var(--gazu-success-bg,#e7f7ed)] border-[var(--gazu-success,#1f9d55)] text-[var(--gazu-success,#0d6a3a)]' : 'bg-[var(--gazu-danger-bg,#fdeaea)] border-[var(--gazu-danger,#c5364e)] text-[var(--gazu-danger,#a02638)]'"
                 class="border rounded-md px-4 py-3 flex items-start gap-3">
                <svg x-show="result?.fits" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="M20 6L9 17l-5-5"/></svg>
                <svg x-show="!result?.fits" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-[14px]">
                        <span x-show="result?.fits">Підходить ✓</span>
                        <span x-show="!result?.fits">Не підходить</span>
                    </div>
                    <div class="text-[13px] opacity-90 mt-0.5" x-text="result?.label"></div>
                    <div x-show="!result?.fits" class="text-[12px] opacity-75 mt-1">
                        Якщо ви впевнені — зв`яжіться з менеджером, ми уточнимо вручну.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@once
<script>
    (function() {
        if (typeof window.__gazuCompatCheckRegistered !== 'undefined') return;
        window.__gazuCompatCheckRegistered = true;
        const register = () => {
            if (! window.Alpine) { document.addEventListener('alpine:init', register, { once: true }); return; }
            window.Alpine.data('gazuCompatCheck', (opts) => ({
                makes: [], models: [], engines: [],
                make: '', model: '', engine: '',
                openLevel: null, search: '', highlight: 0, loading: false,
                checking: false, result: null,
                _opts: opts,

                async init() {
                    this._host = this.$el;
                    try { const r = await fetch(opts.api.makes); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
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

                triggerState(level) {
                    if (level === 'model')  { if (!this.make) return { locked: true, selected: false, label: null }; const m = this.models.find(x => x.slug === this.model); return { locked: false, selected: !!m, label: m ? m.name : null }; }
                    if (level === 'engine') { if (!this.model) return { locked: true, selected: false, label: null }; const e = this.engines.find(x => x.code === this.engine); return { locked: false, selected: !!e, label: e ? (e.label || e.code) : null }; }
                    return { locked: true, selected: false, label: null };
                },
                pickMake(m) {
                    this.make = m.slug; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.openLevel = null;
                    this.result = null; this.fetchModels();
                },
                changeMake() {
                    this.make = ''; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.openLevel = null; this.result = null;
                },
                makeName() { const m = this.makes.find(x => x.slug === this.make); return m ? m.name : ''; },
                toggleLevel(level) { const st = this.triggerState(level); if (st.locked) return; this.openLevel = this.openLevel === level ? null : level; },
                closeAll() { this.openLevel = null; },

                filteredItems() {
                    const list = this.openLevel === 'model' ? this.models : this.openLevel === 'engine' ? this.engines : [];
                    const q = this.search.trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '')).toLowerCase().includes(q));
                },
                isItemSelected(item) {
                    return (this.openLevel === 'model' && item.slug === this.model)
                        || (this.openLevel === 'engine' && item.code === this.engine);
                },
                itemPrimaryLabel(item) { return this.openLevel === 'engine' ? (item.label || item.code) : (item.name + (item.years_range ? ' ('+item.years_range+')' : '')); },
                itemSecondaryLabel(item) { return this.openLevel === 'engine' && item.hp ? (item.hp + ' к.с.') : ''; },

                moveHighlight(d) { const list = this.filteredItems(); if (!list.length) return; this.highlight = (this.highlight + d + list.length) % list.length; },
                commitHighlighted() { const list = this.filteredItems(); if (list.length) this.pick(list[this.highlight]); },
                pick(item) {
                    if (this.openLevel === 'model')       { this.model = item.slug; this.engine = ''; this.engines = []; this.openLevel = null; this.result = null; this.fetchEngines(); }
                    else if (this.openLevel === 'engine') { this.engine = item.code; this.openLevel = null; this.result = null; }
                },
                panelPositionStyle() {
                    const host = this._host || this.$el;
                    const triggers = host.querySelectorAll('[data-trigger]');
                    if (!triggers.length) return '';
                    let maxBottom = 0;
                    triggers.forEach(t => { const r = t.getBoundingClientRect(); if (r.bottom > maxBottom) maxBottom = r.bottom; });
                    const hostR = host.getBoundingClientRect();
                    return `left: 0; right: 0; top: ${maxBottom - hostR.top + 6}px;`;
                },

                hasAnySelection() { return !!(this.make || this.model || this.engine); },
                canCheck() { return this.make && this.model && this.engine && !this.checking; },
                async check() {
                    if (!this.canCheck()) return;
                    this.checking = true; this.result = null;
                    try {
                        const u = new URL(opts.api.check, window.location.origin);
                        u.searchParams.set('product_id', String(opts.productId));
                        u.searchParams.set('make',  this.make);
                        u.searchParams.set('model', this.model);
                        u.searchParams.set('engine', this.engine);
                        const r = await fetch(u.toString()); const d = await r.json();
                        if (! d.ok) { this.result = { fits: false, label: d.message || 'Помилка перевірки' }; return; }
                        this.result = { fits: !!d.fits, label: d.engine?.label || '' };
                    } catch (e) { this.result = { fits: false, label: 'Помилка зв`язку' }; }
                    finally { this.checking = false; }
                },
                reset() { this.make = ''; this.model = ''; this.engine = ''; this.models = []; this.engines = []; this.openLevel = null; this.result = null; },
            }));
        };
        register();
    })();
</script>
@endonce
