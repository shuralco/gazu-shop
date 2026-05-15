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
@endphp
{{--
    Car selector widget: марка → модель → двигун + кнопка «Знайти».
    On submit, redirects to /catalog?make=...&model=...&engine=... so the
    filter is shareable. Dropdowns are populated client-side via fetch
    to keep initial HTML small.
--}}
<div x-data="{
        makes: [],
        models: [],
        engines: [],
        make: @js((string) $selectedMake),
        model: @js((string) $selectedModel),
        engine: @js((string) $selectedEngine),
        loadingMakes: false,
        loadingModels: false,
        loadingEngines: false,

        async init() {
            this.loadingMakes = true;
            try { const r = await fetch('{{ $apiMakes }}', { headers: { Accept: 'application/json' } }); const d = await r.json(); this.makes = d.items || []; }
            catch (e) {} finally { this.loadingMakes = false; }
            if (this.make) await this.loadModels();
            if (this.model) await this.loadEngines();
        },
        async loadModels() {
            this.loadingModels = true; this.models = [];
            try { const r = await fetch('{{ $apiModels }}?make=' + encodeURIComponent(this.make)); const d = await r.json(); this.models = d.items || []; }
            catch (e) {} finally { this.loadingModels = false; }
        },
        async loadEngines() {
            this.loadingEngines = true; this.engines = [];
            try { const r = await fetch('{{ $apiEngines }}?make=' + encodeURIComponent(this.make) + '&model=' + encodeURIComponent(this.model)); const d = await r.json(); this.engines = d.items || []; }
            catch (e) {} finally { this.loadingEngines = false; }
        },
        onMakeChange() {
            this.model = ''; this.engine = ''; this.models = []; this.engines = [];
            if (this.make) this.loadModels();
        },
        onModelChange() {
            this.engine = ''; this.engines = [];
            if (this.model) this.loadEngines();
        },
        hasAnySelection() { return !!(this.make || this.model || this.engine); },
        reset() {
            this.make = ''; this.model = ''; this.engine = '';
            this.models = []; this.engines = [];
            if (window.location.search) window.location.assign('{{ $catalogUrl }}');
        },
        submit() {
            if (!this.hasAnySelection()) return;
            const u = new URL('{{ $catalogUrl }}', window.location.origin);
            if (this.make)   u.searchParams.set('make',   this.make);
            if (this.model)  u.searchParams.set('model',  this.model);
            if (this.engine) u.searchParams.set('engine', this.engine);
            window.location.assign(u.toString());
        },
     }"
     x-init="init()"
     class="gazu-car-selector w-full {{ $variant === 'hero' ? 'p-5 md:p-6 bg-white rounded-xl border border-[var(--gazu-line)] shadow-[0_8px_24px_-12px_rgba(14,27,44,0.20)]' : 'p-3 bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-lg' }} font-text">

    @if($variant === 'hero')
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div class="text-[15px] sm:text-[17px] font-semibold text-[var(--gazu-ink)]">Підбір запчастин по авто</div>
            <button type="button"
                @click="reset()"
                x-show="hasAnySelection()" x-cloak
                class="text-[12px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0">
                Скинути
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
        <label class="block">
            <span class="text-[11px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)] mb-1 block">Марка</span>
            <select x-model="make"
                    @change="onMakeChange()"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] text-[var(--gazu-ink)] focus:border-[var(--gazu-ink)] outline-none cursor-pointer">
                <option value="">Оберіть марку</option>
                <template x-for="m in makes" :key="m.slug">
                    <option :value="m.slug" :selected="m.slug === make" x-text="m.name"></option>
                </template>
            </select>
        </label>

        <label class="block">
            <span class="text-[11px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)] mb-1 block">Модель</span>
            <select x-model="model"
                    @change="onModelChange()"
                    :disabled="!make || loadingModels"
                    :class="(!make || loadingModels) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] text-[var(--gazu-ink)] focus:border-[var(--gazu-ink)] outline-none">
                <option value="" x-text="!make ? 'Спершу марку' : (loadingModels ? 'Завантаження…' : 'Оберіть модель')"></option>
                <template x-for="m in models" :key="m.slug">
                    <option :value="m.slug" :selected="m.slug === model" x-text="m.name + (m.years_range ? ' (' + m.years_range + ')' : '')"></option>
                </template>
            </select>
        </label>

        <label class="block">
            <span class="text-[11px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)] mb-1 block">Двигун</span>
            <select x-model="engine"
                    :disabled="!model || loadingEngines"
                    :class="(!model || loadingEngines) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] text-[var(--gazu-ink)] focus:border-[var(--gazu-ink)] outline-none">
                <option value="" x-text="!model ? 'Спершу модель' : (loadingEngines ? 'Завантаження…' : 'Оберіть двигун')"></option>
                <template x-for="e in engines" :key="e.code">
                    <option :value="e.code" :selected="e.code === engine" x-text="(e.label || e.code) + (e.hp ? ' · ' + e.hp + ' к.с.' : '')"></option>
                </template>
            </select>
        </label>
    </div>

    <div class="mt-3 sm:mt-4 flex gap-2 sm:gap-3">
        <button type="button"
                @click="submit()"
                :disabled="!hasAnySelection()"
                :class="hasAnySelection() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]' : 'bg-[var(--gazu-line-2)] cursor-not-allowed'"
                class="flex-1 py-2.5 sm:py-3 text-white border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span>Знайти запчастини</span>
        </button>
        @if($variant !== 'hero')
            <button type="button"
                    @click="reset()"
                    x-show="hasAnySelection()" x-cloak
                    class="px-4 py-2.5 bg-white border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] rounded-md text-[13px] cursor-pointer transition-colors">
                Скинути
            </button>
        @endif
    </div>
</div>
