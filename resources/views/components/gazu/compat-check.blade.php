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
    4D: Compat-check — каскад марка → модель → двигун + кнопка «Перевірити».
    Reference: chery911.com.ua/products/aftermarket-yj026280-30376.html.
    Backend rule: pivot table product_compatibility (product_id ↔ engine_id).
--}}
<section class="my-6 sm:my-8">
    <div x-data="{
            makes: [], models: [], engines: [],
            make: '', model: '', engine: '',
            loadingModels: false, loadingEngines: false,
            checking: false,
            result: null, // null | {fits:bool, label:string}

            async init() {
                try { const r = await fetch('{{ $apiMakes }}'); const d = await r.json(); this.makes = d.items || []; } catch (e) {}
            },
            async loadModels() {
                this.loadingModels = true; this.models = []; this.engines = []; this.model = ''; this.engine = ''; this.result = null;
                if (! this.make) { this.loadingModels = false; return; }
                try { const r = await fetch('{{ $apiModels }}?make=' + encodeURIComponent(this.make)); const d = await r.json(); this.models = d.items || []; } catch (e) {}
                finally { this.loadingModels = false; }
            },
            async loadEngines() {
                this.loadingEngines = true; this.engines = []; this.engine = ''; this.result = null;
                if (! this.model) { this.loadingEngines = false; return; }
                try { const r = await fetch('{{ $apiEngines }}?make=' + encodeURIComponent(this.make) + '&model=' + encodeURIComponent(this.model)); const d = await r.json(); this.engines = d.items || []; } catch (e) {}
                finally { this.loadingEngines = false; }
            },
            canCheck() { return this.make && this.model && this.engine && !this.checking; },
            async check() {
                if (! this.canCheck()) return;
                this.checking = true; this.result = null;
                try {
                    const u = new URL('{{ $apiCheck }}', window.location.origin);
                    u.searchParams.set('product_id', '{{ (int) $productId }}');
                    u.searchParams.set('make',  this.make);
                    u.searchParams.set('model', this.model);
                    u.searchParams.set('engine', this.engine);
                    const r = await fetch(u.toString());
                    const d = await r.json();
                    if (! d.ok) { this.result = { fits: false, label: d.message || 'Помилка перевірки' }; return; }
                    const label = d.engine?.label || '';
                    this.result = { fits: !!d.fits, label };
                } catch (e) { this.result = { fits: false, label: 'Помилка зв`язку' }; }
                finally { this.checking = false; }
            },
         }"
         x-init="init()"
         class="bg-white border border-[var(--gazu-line)] rounded-xl p-5 sm:p-6 font-text">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-9 h-9 rounded-full bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <div>
                <div class="text-[15px] sm:text-[17px] font-semibold text-[var(--gazu-ink)]">Перевірити сумісність з авто</div>
                <div class="text-[12px] text-[var(--gazu-graphite)]">Оберіть марку, модель і двигун — ми перевіримо чи підходить</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
            <select x-model="make"
                    @change="loadModels()"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] cursor-pointer focus:border-[var(--gazu-ink)] outline-none">
                <option value="">Марка</option>
                <template x-for="m in makes" :key="m.slug">
                    <option :value="m.slug" x-text="m.name"></option>
                </template>
            </select>

            <select x-model="model"
                    @change="loadEngines()"
                    :disabled="!make || loadingModels"
                    :class="(!make || loadingModels) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] focus:border-[var(--gazu-ink)] outline-none">
                <option value="" x-text="!make ? 'Спершу марку' : (loadingModels ? 'Завантаження…' : 'Модель')"></option>
                <template x-for="m in models" :key="m.slug">
                    <option :value="m.slug" x-text="m.name"></option>
                </template>
            </select>

            <select x-model="engine"
                    :disabled="!model || loadingEngines"
                    :class="(!model || loadingEngines) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    class="w-full bg-white border border-[var(--gazu-line)] rounded-md py-2.5 px-3 text-[14px] focus:border-[var(--gazu-ink)] outline-none">
                <option value="" x-text="!model ? 'Спершу модель' : (loadingEngines ? 'Завантаження…' : 'Двигун')"></option>
                <template x-for="e in engines" :key="e.code">
                    <option :value="e.code" x-text="(e.label || e.code) + (e.hp ? ' · ' + e.hp + ' к.с.' : '')"></option>
                </template>
            </select>
        </div>

        <div class="mt-3 sm:mt-4 flex gap-3">
            <button type="button"
                    @click="check()"
                    :disabled="!canCheck()"
                    :class="canCheck() ? 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]' : 'bg-[var(--gazu-line-2)] cursor-not-allowed'"
                    class="px-5 py-2.5 text-white border-0 rounded-md text-[14px] font-semibold transition-colors">
                <span x-show="!checking">Перевірити</span>
                <span x-show="checking" x-cloak>Перевірка…</span>
            </button>
        </div>

        {{-- Result banner --}}
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
                        Якщо ви впевнені — зв'яжіться з менеджером, ми уточнимо вручну.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
