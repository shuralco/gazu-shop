@extends('gazu.layout')
@section('title', 'Оформлення замовлення — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], ['Кошик', route('gazu.cart')], 'Оформлення']"/>
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-5">Оформлення замовлення</h1>

    {{-- Multi-step progress indicator (UA shop convention) --}}
    <nav aria-label="Прогрес замовлення" class="mb-7">
        <ol class="flex items-center gap-2 sm:gap-4 text-sm overflow-x-auto">
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-white flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <a wire:navigate href="{{ route('gazu.cart') }}" class="text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] no-underline">Кошик</a>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-ink)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-ink)] text-white flex items-center justify-center font-bold">2</span>
                <span class="text-[var(--gazu-ink)] font-medium">Оформлення</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-line-2)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0 opacity-60">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] flex items-center justify-center font-bold">3</span>
                <span class="text-[var(--gazu-graphite)]">Готово</span>
            </li>
        </ol>
    </nav>

    @if($errors->any())
        <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm">
            <strong>Виправте помилки:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gazu.checkout.store') }}" method="POST" class="gazu-grid-cart">
        @csrf
        <div class="flex flex-col gap-4">
            {{-- 1. Контактні дані --}}
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5 border-[var(--gazu-ink)]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-white">1</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Контактні дані</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-3 pl-11">
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="text" name="first_name" value="{{ old('first_name', auth()->user()?->name) }}" required
                               class="w-full px-3 py-2.5 border @error('first_name') border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] @else border-[var(--gazu-line)] @enderror rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        @error('first_name')<span class="text-xs text-[var(--gazu-danger)] mt-1 block">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Прізвище</span>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               class="w-full px-3 py-2.5 border @error('last_name') border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] @else border-[var(--gazu-line)] @enderror rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        @error('last_name')<span class="text-xs text-[var(--gazu-danger)] mt-1 block">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Телефон <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" required placeholder="+380 67 123 45 67"
                               class="w-full px-3 py-2.5 border @error('phone') border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] @else border-[var(--gazu-line)] @enderror rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                        @error('phone')<span class="text-xs text-[var(--gazu-danger)] mt-1 block">{{ $message }}</span>@enderror
                    </label>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                               class="w-full px-3 py-2.5 border @error('email') border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] @else border-[var(--gazu-line)] @enderror rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        @error('email')<span class="text-xs text-[var(--gazu-danger)] mt-1 block">{{ $message }}</span>@enderror
                    </label>
                </div>
            </div>

            {{-- 2. Доставка --}}
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-white">2</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Доставка</h3>
                </div>
                <div class="grid gap-2 pl-11"
                     x-data="{
                         method: @js(old('shipping_method', 'novaposhta')),
                         city: @js(old('shipping_city', '')),
                         cityRef: @js(old('shipping_city_ref', '')),
                         warehouse: @js(old('shipping_warehouse', '')),
                         warehouseRef: @js(old('shipping_warehouse_ref', '')),
                         type: @js(old('shipping_warehouse_type', 'branch')),
                             cityResults: [],
                             warehouseResults: [],
                             cityOpen: false,
                             warehouseOpen: false,
                             cityTimer: null,
                             warehouseTimer: null,
                             async fetchCities() {
                                 const r = await fetch('{{ route('gazu.api.np.cities') }}?q=' + encodeURIComponent(this.city), { cache: 'no-store' });
                                 const d = await r.json();
                                 this.cityResults = d.items || [];
                                 this.cityOpen = this.cityResults.length > 0;
                                 // Auto-select при точному збігу + лише 1 результат не потрібно — користувач сам обере.
                                 const exact = this.cityResults.find(c => c.name.toLowerCase() === this.city.toLowerCase());
                                 if (exact && !this.cityRef) {
                                     this.cityRef = exact.ref;
                                 }
                             },
                             onCityInput() {
                                 this.cityRef = '';
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.warehouseResults = [];
                                 clearTimeout(this.cityTimer);
                                 this.cityTimer = setTimeout(() => this.fetchCities(), 200);
                             },
                             selectCity(item) {
                                 this.city = item.name;
                                 this.cityRef = item.ref;
                                 this.cityOpen = false;
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.fetchWarehouses(true);
                             },
                             switchType(t) {
                                 this.type = t;
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.warehouseResults = [];
                                 this.warehouseOpen = false;
                                 if (this.cityRef) this.fetchWarehouses(true);
                             },
                             async fetchWarehouses(autoOpen = false) {
                                 const params = new URLSearchParams({
                                     city_ref: this.cityRef || '',
                                     city: this.city || '',
                                     q: this.warehouse || '',
                                     type: this.type || 'branch',
                                 });
                                 const r = await fetch('{{ route('gazu.api.np.warehouses') }}?' + params, { cache: 'no-store' });
                                 const d = await r.json();
                                 this.warehouseResults = d.items || [];
                                 this.warehouseOpen = this.warehouseResults.length > 0;
                             },
                             onWarehouseInput() {
                                 this.warehouseRef = '';
                                 clearTimeout(this.warehouseTimer);
                                 // Завжди відкриваємо dropdown якщо є результати — навіть для першого символу
                                 this.warehouseTimer = setTimeout(() => this.fetchWarehouses(true), 150);
                             },
                             selectWarehouse(item) {
                                 const num = item.number ? '№' + item.number + ' · ' : '';
                                 this.warehouse = num + (item.short_address || item.name);
                                 this.warehouseRef = item.ref;
                                 this.warehouseOpen = false;
                             },
                             // Streets (NP Кур'єр)
                             street: @js(old('shipping_street', '')),
                             streetRef: @js(old('shipping_street_ref', '')),
                             streetResults: [],
                             streetOpen: false,
                             streetTimer: null,
                             async fetchStreets() {
                                 if (!this.cityRef || !this.street || this.street.length < 2) {
                                     this.streetResults = []; this.streetOpen = false; return;
                                 }
                                 const params = new URLSearchParams({ city_ref: this.cityRef, q: this.street });
                                 const r = await fetch('{{ route('gazu.api.np.streets') }}?' + params, { cache: 'no-store' });
                                 const d = await r.json();
                                 this.streetResults = d.items || [];
                                 this.streetOpen = this.streetResults.length > 0;
                             },
                             onStreetInput() {
                                 this.streetRef = '';
                                 clearTimeout(this.streetTimer);
                                 this.streetTimer = setTimeout(() => this.fetchStreets(), 250);
                             },
                             selectStreet(item) {
                                 this.street = item.name;
                                 this.streetRef = item.ref;
                                 this.streetOpen = false;
                             },
                             // Shipping cost / delivery
                             shippingCost: null,
                             shippingDays: null,
                             shippingDate: null,
                             shippingLoading: false,
                             async fetchShipping() {
                                 if (!this.cityRef || this.method !== 'novaposhta') {
                                     this.shippingCost = null; this.shippingDays = null; return;
                                 }
                                 this.shippingLoading = true;
                                 try {
                                     const params = new URLSearchParams({ city_ref: this.cityRef, type: this.type });
                                     const r = await fetch('{{ route('gazu.api.np.calculate') }}?' + params, { cache: 'no-store' });
                                     const d = await r.json();
                                     this.shippingCost = d.cost;
                                     this.shippingDays = d.days;
                                     this.shippingDate = d.date;
                                 } catch (e) {} finally { this.shippingLoading = false; }
                             },
                             init() {
                                 this.$watch('cityRef', (v) => { if (v) { this.fetchWarehouses(true); this.fetchShipping(); } });
                                 this.$watch('type', () => { if (this.cityRef) this.fetchShipping(); });
                                 this.$watch('method', () => { this.fetchShipping(); });
                                 // Push shipping cost у sidebar
                                 this.$watch('shippingCost', (v) => window.dispatchEvent(new CustomEvent('gazu-shipping', { detail: { cost: v, method: this.method } })));
                                 this.$watch('method', () => window.dispatchEvent(new CustomEvent('gazu-shipping', { detail: { cost: this.method === 'pickup' ? 0 : this.shippingCost, method: this.method } })));
                                 // Pick from map popup
                                 document.addEventListener('np-map-pick', (e) => {
                                     const item = this.warehouseResults.find(w => w.ref === e.detail.ref);
                                     if (item) {
                                         this.selectWarehouse(item);
                                         window.gazuToast && window.gazuToast('Обрано №' + item.number + ': ' + (item.short_address || item.name).slice(0, 40), 'success');
                                     }
                                 });
                             },
                     }">
                    @foreach([
                        ['novaposhta', 'Нова Пошта', 'Відділення / Поштомат / Курʼєр НП — 1-3 дні'],
                        ['ukrposhta', 'УкрПошта', 'Відділення / адреса · 3-5 днів, дешевше'],
                        ['pickup', 'Самовивіз з магазину', 'Безкоштовно'],
                    ] as [$key, $label, $desc])
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer"
                               :class="method === '{{ $key }}' ? 'border-[var(--gazu-ink)] bg-[var(--gazu-paper)]' : 'border-[var(--gazu-line)]'">
                            <input type="radio" name="shipping_method" value="{{ $key }}" x-model="method" class="sr-only">
                            <span class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                  :class="method === '{{ $key }}' ? 'border-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)]'">
                                <span x-show="method === '{{ $key }}'" class="w-2 h-2 rounded-full bg-[var(--gazu-ink)]"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]">{{ $label }}</div>
                                <div class="text-xs text-[var(--gazu-graphite)]">{{ $desc }}</div>
                            </div>
                        </label>
                    @endforeach

                    {{-- Спільне поле "Місто" — для НП (УП має свій autocomplete нижче) --}}
                    <div class="mt-2" x-show="method === 'novaposhta'" x-cloak>
                        <label class="block relative" @click.outside="cityOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Місто</span>
                            <input type="text" name="shipping_city" placeholder="Почніть вводити: Київ, Львів…"
                                   x-model="city" @input="onCityInput" @focus="city.length > 1 && fetchCities()"
                                   @keydown.enter.prevent="cityResults.length && selectCity(cityResults[0])"
                                   @keydown.escape="cityOpen = false"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_city_ref" :value="cityRef">
                            <div x-show="cityOpen && cityResults.length" x-cloak x-transition.opacity
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 15rem; overflow-y: auto;">
                                <template x-for="item in cityResults" :key="item.ref">
                                    <button type="button" @click="selectCity(item)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]" x-text="item.name"></div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="item.area"></div>
                                    </button>
                                </template>
                            </div>
                        </label>
                    </div>

                    {{-- Нова Пошта: відділення / поштомат / курʼєр НП --}}
                    <div x-show="method === 'novaposhta'" x-cloak class="mt-2">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                            <button type="button" @click="switchType('branch')"
                                    :class="type === 'branch' ? 'bg-[var(--gazu-ink)] text-white border-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <x-gazu.icon name="box" size="14"/> Відділення
                            </button>
                            <button type="button" @click="switchType('postomat')"
                                    :class="type === 'postomat' ? 'bg-[var(--gazu-ink)] text-white border-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <x-gazu.icon name="cube" size="14"/> Поштомат
                            </button>
                            <button type="button" @click="switchType('np_courier')"
                                    :class="type === 'np_courier' ? 'bg-[var(--gazu-ink)] text-white border-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <x-gazu.icon name="truck" size="14"/> Курʼєр
                            </button>
                        </div>
                        <input type="hidden" name="shipping_warehouse_type" :value="type">
                        <input type="hidden" name="shipping_warehouse_ref" :value="warehouseRef">

                        {{-- Відділення / Поштомат — autocomplete + мапа --}}
                        <div x-show="type !== 'np_courier'" x-cloak x-data="{ view: 'list' }">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-[var(--gazu-graphite)] block" x-text="type === 'postomat' ? 'Поштомат' : 'Відділення / адреса'"></span>
                                <div class="flex gap-1 text-[11px]" x-show="warehouseResults.some(w => w.lat && w.lng)" x-cloak>
                                    <button type="button" @click="view = 'list'"
                                            :class="view === 'list' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-ink)]'"
                                            class="px-2 py-1 border border-[var(--gazu-line)] rounded">
                                        Список
                                    </button>
                                    <button type="button" @click="view = 'map'; $nextTick(() => $dispatch('np-map-render'))"
                                            :class="view === 'map' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-ink)]'"
                                            class="px-2 py-1 border border-[var(--gazu-line)] rounded">
                                        Мапа
                                    </button>
                                </div>
                            </div>

                            {{-- MAP VIEW --}}
                            <div x-show="view === 'map'" x-cloak class="border border-[var(--gazu-line)] rounded-md mb-2"
                                 style="height: 380px;"
                                 x-init="$watch('warehouseResults', () => { if (view === 'map') $dispatch('np-map-render'); })"
                                 wire:ignore>
                                <div id="gazu-np-map" style="height: 100%; width: 100%; background: #f0f0f0;"
                                     :data-warehouses="JSON.stringify(warehouseResults.filter(w => w.lat && w.lng).map(w => ({ref: w.ref, num: w.number, addr: w.short_address || w.name, lat: w.lat, lng: w.lng})))"
                                     :data-selected-ref="warehouseRef"></div>
                            </div>

                            {{-- LIST VIEW --}}
                            <label class="block relative" @click.outside="warehouseOpen = false" x-show="view === 'list'" x-cloak>
                                <input type="text" name="shipping_warehouse"
                                       :placeholder="type === 'postomat' ? '№ або адреса поштомата' : '№ або адреса відділення'"
                                       x-model="warehouse"
                                       @input="onWarehouseInput"
                                       @click="cityOpen = false; fetchWarehouses(true)"
                                       @focus="cityOpen = false; fetchWarehouses(true)"
                                       @keydown.escape="warehouseOpen = false"
                                       autocomplete="off"
                                       class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                <div x-show="warehouseOpen && warehouseResults.length" x-cloak x-transition.opacity
                                     class="absolute z-30 left-0 right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-md shadow-xl"
                                     style="max-height: 18rem; overflow-y: auto;">
                                    <template x-for="item in warehouseResults" :key="item.ref">
                                        <button type="button" @click="selectWarehouse(item)"
                                                class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                            <div class="text-sm text-[var(--gazu-ink)]">
                                                <span class="gazu-mono text-[11px] text-[var(--gazu-blue)]" x-text="'#' + item.number"></span>
                                                <span x-text="item.name"></span>
                                            </div>
                                            <div class="text-xs text-[var(--gazu-graphite)]" x-text="item.short_address"></div>
                                        </button>
                                    </template>
                                </div>
                                <div x-show="!cityRef" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Спочатку оберіть місто зі списку
                                </div>
                                <div x-show="cityRef && !warehouseResults.length && !warehouseOpen && warehouse.length > 1" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Нічого не знайдено
                                </div>
                            </label>
                        </div>

                        {{-- Курʼєр Нової Пошти — поля адреси (як у brutal NovaPoshtaSelector) --}}
                        <div x-show="type === 'np_courier'" x-cloak class="space-y-3">
                            <label class="block relative" @click.outside="streetOpen = false">
                                <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Вулиця</span>
                                <input type="text" name="shipping_street"
                                       x-model="street"
                                       @input="onStreetInput"
                                       placeholder="Почніть вводити назву…"
                                       autocomplete="off"
                                       class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                <input type="hidden" name="shipping_street_ref" :value="streetRef">
                                <div x-show="streetOpen && streetResults.length" x-cloak x-transition.opacity
                                     class="absolute z-30 left-0 right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-md shadow-xl"
                                     style="max-height: 14rem; overflow-y: auto;">
                                    <template x-for="item in streetResults" :key="item.ref">
                                        <button type="button" @click="selectStreet(item)"
                                                class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                            <span class="text-sm text-[var(--gazu-ink)]" x-text="item.name"></span>
                                        </button>
                                    </template>
                                </div>
                                <div x-show="!cityRef" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Спершу оберіть місто
                                </div>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Будинок</span>
                                    <input type="text" name="shipping_house" value="{{ old('shipping_house', '') }}"
                                           placeholder="15"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Квартира</span>
                                    <input type="text" name="shipping_apartment" value="{{ old('shipping_apartment', '') }}"
                                           placeholder="23"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Поверх</span>
                                    <input type="number" name="shipping_floor" value="{{ old('shipping_floor', '') }}"
                                           min="1" max="50" placeholder="3"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" name="shipping_has_elevator" value="1" {{ old('shipping_has_elevator') ? 'checked' : '' }}
                                       class="w-4 h-4 border border-[var(--gazu-line)] rounded">
                                <span class="text-[var(--gazu-ink)] font-medium">Є ліфт</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Бажана дата</span>
                                    <input type="date" name="shipping_preferred_date"
                                           min="{{ now()->addDay()->toDateString() }}"
                                           value="{{ old('shipping_preferred_date', '') }}"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Бажаний час</span>
                                    <select name="shipping_preferred_time"
                                            class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none bg-white">
                                        <option value="">— Будь-який —</option>
                                        <option value="09:00-14:00">9:00 — 14:00</option>
                                        <option value="14:00-18:00">14:00 — 18:00</option>
                                        <option value="18:00-22:00">18:00 — 22:00</option>
                                    </select>
                                </label>
                            </div>
                            <div class="text-xs text-[var(--gazu-muted)]">
                                Курʼєр Нової Пошти доставить замовлення на вказану адресу. Ціна — за тарифом НП.
                            </div>
                        </div>

                        {{-- Розрахунок вартості + орієнтовна дата --}}
                        <div x-show="cityRef && (shippingCost !== null || shippingLoading)" x-cloak
                             class="mt-3 p-3 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded-md flex items-center justify-between text-sm">
                            <div>
                                <span class="text-[var(--gazu-graphite)]">Вартість доставки:</span>
                                <span class="font-bold text-[var(--gazu-ink)]" x-show="!shippingLoading && shippingCost !== null"
                                      x-text="shippingCost + ' ₴'"></span>
                                <span x-show="shippingLoading" class="text-[var(--gazu-muted)]">розрахунок…</span>
                            </div>
                            <div x-show="shippingDate" class="text-xs text-[var(--gazu-graphite)]">
                                <span>Прибуде:</span>
                                <span class="gazu-mono font-medium" x-text="shippingDate"></span>
                                <span x-show="shippingDays" x-text="'(~' + shippingDays + ' дн.)'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- УкрПошта: city autocomplete + post office + адреса --}}
                    <div x-show="method === 'ukrposhta'" x-cloak class="mt-2 space-y-3"
                         x-data="{
                            up: {
                                city: @js(old('shipping_up_city', '')),
                                cityId: @js(old('shipping_up_city_id', '')),
                                cityResults: [], cityOpen: false, cityTimer: null,
                                office: @js(old('shipping_up_office', '')),
                                officeId: @js(old('shipping_up_office_id', '')),
                                officeResults: [], officeOpen: false,
                                async fetchCities() {
                                    if (this.city.length < 2) { this.cityResults = []; this.cityOpen = false; return; }
                                    const r = await fetch('{{ route('gazu.api.up.cities') }}?q=' + encodeURIComponent(this.city), { cache: 'no-store' });
                                    const d = await r.json();
                                    this.cityResults = d.items || [];
                                    this.cityOpen = this.cityResults.length > 0;
                                },
                                onCityInput() { this.cityId = ''; this.officeResults = []; this.office = ''; this.officeId = ''; clearTimeout(this.cityTimer); this.cityTimer = setTimeout(() => this.fetchCities(), 250); },
                                selectCity(c) { this.city = c.name; this.cityId = c.id; this.cityOpen = false; this.fetchOffices(); },
                                async fetchOffices() {
                                    if (!this.cityId) return;
                                    const r = await fetch('{{ route('gazu.api.up.post-offices') }}?city_id=' + this.cityId, { cache: 'no-store' });
                                    const d = await r.json();
                                    this.officeResults = d.items || [];
                                    this.officeOpen = this.officeResults.length > 0;
                                },
                                selectOffice(o) { this.office = '№' + (o.postcode || '') + ' · ' + (o.address || o.name); this.officeId = o.id; this.officeOpen = false; document.querySelector('input[name=shipping_postcode]').value = o.postcode || ''; }
                            }
                         }">
                        <label class="block relative" @click.outside="up.cityOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Місто</span>
                            <input type="text" name="shipping_up_city" placeholder="Почніть вводити: Київ, Львів…"
                                   x-model="up.city" @input="up.onCityInput()" @focus="up.city.length > 1 && up.fetchCities()"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_up_city_id" :value="up.cityId">
                            <div x-show="up.cityOpen && up.cityResults.length" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 15rem; overflow-y: auto;">
                                <template x-for="c in up.cityResults" :key="c.id">
                                    <button type="button" @click="up.selectCity(c)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]" x-text="c.name"></div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="c.region"></div>
                                    </button>
                                </template>
                            </div>
                        </label>

                        <label class="block relative" @click.outside="up.officeOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Відділення</span>
                            <input type="text" name="shipping_up_office"
                                   x-model="up.office"
                                   @click="up.cityId && up.fetchOffices()"
                                   placeholder="№ або адреса відділення УП"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_up_office_id" :value="up.officeId">
                            <div x-show="up.officeOpen && up.officeResults.length" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 18rem; overflow-y: auto;">
                                <template x-for="o in up.officeResults" :key="o.id">
                                    <button type="button" @click="up.selectOffice(o)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]">
                                            <span class="gazu-mono text-[11px] text-[var(--gazu-blue)]" x-text="'№' + (o.postcode || '?')"></span>
                                            <span x-text="o.name || o.address"></span>
                                        </div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="o.address"></div>
                                    </button>
                                </template>
                            </div>
                            <div x-show="!up.cityId" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                Спочатку оберіть місто
                            </div>
                        </label>
                        <input type="hidden" name="shipping_postcode" :value="up.officeResults.find(o => o.id === up.officeId)?.postcode || ''">
                    </div>

                    {{-- Самовивіз --}}
                    <div x-show="method === 'pickup'" x-cloak class="mt-2 p-3 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded-md text-sm">
                        <div class="flex items-start gap-2">
                            <x-gazu.icon name="store" size="16"/>
                            <div>
                                <div class="font-medium text-[var(--gazu-ink)]">{{ \App\Models\DisplaySetting::get('gazu_pickup_address', 'м. Київ, вул. Промислова, 25') }}</div>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-1">{{ \App\Models\DisplaySetting::get('gazu_pickup_hours', 'Пн–Пт: 9:00–18:00, Сб: 10:00–15:00') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Оплата --}}
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-white">3</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Спосіб оплати</h3>
                </div>
                <div class="grid gap-2 pl-11" x-data="{ pm: '{{ old('payment_method', 'card') }}' }">
                    @foreach([
                        ['card', 'Оплата картою онлайн', 'Visa, Mastercard через WayForPay'],
                        ['applepay', 'Apple Pay / Google Pay', 'Швидка оплата'],
                        ['cod', 'Накладений платіж', 'При отриманні · доплата 1.5%'],
                        ['invoice', 'Рахунок для гуртових клієнтів', 'Безготівковий розрахунок'],
                    ] as [$key, $label, $desc])
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer"
                               :class="pm === '{{ $key }}' ? 'border-[var(--gazu-ink)] bg-[var(--gazu-paper)]' : 'border-[var(--gazu-line)]'">
                            <input type="radio" name="payment_method" value="{{ $key }}" x-model="pm" class="sr-only">
                            <span class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                  :class="pm === '{{ $key }}' ? 'border-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)]'">
                                <span x-show="pm === '{{ $key }}'" class="w-2 h-2 rounded-full bg-[var(--gazu-ink)]"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]">{{ $label }}</div>
                                <div class="text-xs text-[var(--gazu-graphite)]">{{ $desc }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 4. Коментар --}}
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-line)] text-[var(--gazu-graphite)]">4</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Коментар (необовʼязково)</h3>
                </div>
                <div class="pl-11">
                    <textarea name="note" rows="3" placeholder="Уточнення щодо доставки, монтажу тощо…"
                              class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">{{ old('note') }}</textarea>
                </div>
            </div>

            <button type="submit" class="gazu-btn-primary py-4 text-base">
                Оформити замовлення на {{ number_format($cartTotal, 0, '.', ' ') }} ₴
            </button>
            <p class="text-xs text-[var(--gazu-graphite)] text-center">
                Натискаючи кнопку, ви погоджуєтесь з <a href="#" class="text-[var(--gazu-blue)]">умовами публічної оферти</a>.
            </p>
        </div>

        {{-- Order summary --}}
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5 self-start">
            <h3 class="gazu-display text-lg font-semibold m-0 mb-4">Ваше замовлення</h3>
            <div class="flex flex-col gap-3 mb-4 max-h-[400px] overflow-y-auto">
                @foreach($cart as $key => $item)
                    @php
                        $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['quantity'] ?? 1);
                        $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                        $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                        $kind = $kinds[$productId % count($kinds)];
                    @endphp
                    <div class="flex gap-3 items-center">
                        <div class="w-12 h-12 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0">
                            <x-gazu.part-image kind="{{ $kind }}" size="42"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] text-[var(--gazu-ink)] truncate">{{ $title }}</div>
                            <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono">{{ $qty }} × {{ number_format($price, 0, '.', ' ') }} ₴</div>
                        </div>
                        <div class="gazu-display font-bold text-sm text-[var(--gazu-ink)] whitespace-nowrap">{{ number_format($price * $qty, 0, '.', ' ') }} ₴</div>
                    </div>
                @endforeach
            </div>
            <div class="h-px bg-[var(--gazu-line)] my-3"></div>
            <div x-data="{
                    base: {{ (float) $cartTotal }},
                    shippingCost: null,
                    shippingMethod: 'novaposhta',
                    fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g,' '); },
                    get total() { return this.base + (this.shippingCost || 0); },
                    get shippingLabel() {
                        if (this.shippingMethod === 'pickup') return 'Безкоштовно';
                        if (this.shippingCost === null) return 'розрахунок при отриманні';
                        return this.fmt(this.shippingCost) + ' ₴';
                    },
                    flash(refName) {
                        const el = this.$refs[refName];
                        if (!el) return;
                        el.setAttribute('data-changed', '0');
                        void el.offsetWidth;
                        el.setAttribute('data-changed', '1');
                        setTimeout(() => el.setAttribute('data-changed', '0'), 450);
                    },
                    init() {
                        this.$watch('total', () => this.flash('totalEl'));
                        this.$watch('shippingCost', () => this.flash('shipEl'));
                    }
                 }"
                 @gazu-shipping.window="shippingCost = $event.detail.cost; shippingMethod = $event.detail.method">
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Сума</span>
                    <span x-text="fmt(base) + ' ₴'">{{ number_format($cartTotal, 0, '.', ' ') }} ₴</span>
                </div>
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Доставка</span>
                    <span x-ref="shipEl"
                          :class="shippingCost !== null && shippingMethod !== 'pickup' ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]'"
                          class="gazu-count-up"
                          x-text="shippingLabel">розрахунок при отриманні</span>
                </div>
                <div class="h-px bg-[var(--gazu-line)] my-3"></div>
                <div class="flex justify-between items-baseline">
                    <span class="font-medium text-[var(--gazu-ink)]">До сплати</span>
                    <span x-ref="totalEl"
                          class="gazu-display text-2xl font-bold text-[var(--gazu-ink)] gazu-count-up"
                          x-text="fmt(total) + ' ₴'">{{ number_format($cartTotal, 0, '.', ' ') }} ₴</span>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
