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
    4D compat-check — compact cascade tiles + auto-check on engine pick.
--}}
<section class="my-5 sm:my-6">
    <div x-data="gazuCompatCheck({
            productId: @js((int) $productId),
            api: { makes: @js($apiMakes), models: @js($apiModels), engines: @js($apiEngines), check: @js($apiCheck) },
         })"
         x-init="init()"
         class="relative bg-[var(--gazu-surface)] rounded-2xl p-3 sm:p-4 font-text shadow-[0_2px_10px_-4px_rgba(14,27,44,0.08)]">

        <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <div class="min-w-0">
                <div class="text-[13px] sm:text-[14px] font-semibold text-[var(--gazu-ink)] leading-tight">Перевірити сумісність</div>
                <div class="text-[11px] text-[var(--gazu-graphite)] leading-tight" x-text="stepLabel()"></div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1 mb-2 min-h-[24px]">
            <template x-for="chip in pickedChips()" :key="chip.level">
                <div class="inline-flex items-center gap-1 pl-1 pr-1.5 py-0.5 rounded-full bg-[var(--gazu-mist)] text-[11px] text-[var(--gazu-ink)] max-w-full">
                    <div class="w-4 h-4 rounded-full bg-[var(--gazu-surface)] inline-flex items-center justify-center text-[8px] gazu-mono font-bold text-[var(--gazu-blue)] uppercase shrink-0" x-text="chip.badge"></div>
                    <span class="font-medium truncate" x-text="chip.label"></span>
                    <button type="button" @click="changeLevel(chip.level)" class="text-[9px] text-[var(--gazu-blue)] hover:underline bg-transparent border-0 cursor-pointer p-0 shrink-0">×</button>
                </div>
            </template>
        </div>

        <div x-show="activeLevel()" class="min-h-[200px] sm:min-h-[180px] flex flex-col">
            <div x-show="loading" x-cloak class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 flex-1">
                <template x-for="i in 4" :key="i"><div class="rounded-lg bg-[var(--gazu-paper)] animate-pulse min-h-[52px]"></div></template>
            </div>

            <div x-show="!loading && filteredItems().length > 0"
                 :class="(filteredItems().length > 12 && !expanded) ? 'max-h-[260px] overflow-y-auto' : 'overflow-visible'"
                 class="gazu-tile-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 content-start transition-[max-height] flex-1"
                 style="scrollbar-gutter: stable;">
                <template x-for="(item, idx) in filteredItems()" :key="activeLevel() + ':' + itemKey(item)">
                    <button type="button"
                            @click="pick(item)"
                            :style="'--gazu-tile-delay: ' + (idx * 18) + 'ms'"
                            :class="isItemSelected(item) ? 'bg-[var(--gazu-mist)] shadow-[inset_0_0_0_2px_var(--gazu-blue,#2563eb)]' : 'bg-[var(--gazu-surface)] shadow-[0_1px_0_0_var(--gazu-line)] hover:bg-[var(--gazu-paper)] hover:shadow-[0_2px_8px_-3px_rgba(14,27,44,0.18)]'"
                            class="gazu-tile-in flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left cursor-pointer transition-all text-[13px] text-[var(--gazu-ink)] min-h-[52px]">
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

        <div x-show="!activeLevel() && checking" x-cloak
             class="min-h-[208px] sm:min-h-[180px] flex flex-col items-center justify-center text-center">
            <div class="w-10 h-10 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] mb-2">
                <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="text-[13px] font-semibold text-[var(--gazu-ink)]">Перевіряємо…</div>
        </div>

        {{-- Result banner --}}
        <div x-show="!activeLevel() && !checking && result" x-cloak
             class="min-h-[160px] flex items-center">
            <div :class="result?.fits ? 'bg-[var(--gazu-success-bg,#e7f7ed)] text-[var(--gazu-success,#0d6a3a)] shadow-[inset_0_0_0_1px_var(--gazu-success,#1f9d55)]' : 'bg-[var(--gazu-danger-bg,#fdeaea)] text-[var(--gazu-danger,#a02638)] shadow-[inset_0_0_0_1px_var(--gazu-danger,#c5364e)]'"
                 class="rounded-md px-4 py-3 flex items-start gap-3 w-full">
                <svg x-show="result?.fits" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="M20 6L9 17l-5-5"/></svg>
                <svg x-show="!result?.fits" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-[14px]">
                        <span x-show="result?.fits">Підходить ✓</span>
                        <span x-show="!result?.fits">Не підходить</span>
                    </div>
                    <div class="text-[13px] opacity-90 mt-0.5" x-text="result?.label"></div>
                    <div x-show="!result?.fits" class="text-[12px] opacity-75 mt-1">Якщо ви впевнені — зв`яжіться з менеджером.</div>
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
                search: '', expanded: false, loading: false,
                checking: false, result: null,
                _opts: opts,

                async init() {
                    this.loading = true;
                    try { const r = await fetch(opts.api.makes); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
                    finally { this.loading = false; }
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
                         : l === 'engine' ? 'Крок 3 · оберіть двигун' : (this.checking ? 'Перевірка…' : (this.result ? 'Результат' : 'Готово'));
                },
                currentList() {
                    const l = this.activeLevel();
                    return l === 'make' ? this.makes : l === 'model' ? this.models : l === 'engine' ? this.engines : [];
                },
                filteredItems() {
                    const list = this.currentList();
                    const q = (this.search || '').trim().toLowerCase();
                    if (!q) return list;
                    return list.filter(item => ((item.name || '') + ' ' + (item.label || '') + ' ' + (item.code || '')).toLowerCase().includes(q));
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
                    if (l === 'engine') return item.hp ? (item.hp + ' к.с.') : (item.years_range || '');
                    if (l === 'model')  return item.years_range || '';
                    return '';
                },
                pick(item) {
                    const l = this.activeLevel();
                    if (l === 'make')   { this.make = item.slug; this.model = ''; this.engine = ''; this.models = []; this.engines = []; this.result = null; this.fetchModels(); }
                    else if (l === 'model')  { this.model = item.slug; this.engine = ''; this.engines = []; this.result = null; this.fetchEngines(); }
                    else if (l === 'engine') { this.engine = item.code; setTimeout(() => this.check(), 200); }
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
                    this.result = null; this.search = ''; this.expanded = false;
                },

                hasAnySelection() { return !!(this.make || this.model || this.engine); },
                async check() {
                    if (!(this.make && this.model && this.engine)) return;
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
                reset() {
                    this.make = ''; this.model = ''; this.engine = '';
                    this.models = []; this.engines = []; this.result = null; this.search = ''; this.expanded = false;
                },
            }));
        };
        register();
    })();
</script>
@endonce
