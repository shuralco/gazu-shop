<div class="space-y-4">
    {{-- Delivery Type Selection --}}
    <div class="flex gap-2">
        <button type="button" wire:click="$set('deliveryType', 'warehouse')"
                class="flex-1 py-3 font-bold border-2 border-black transition-colors {{ $deliveryType === 'warehouse' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100' }}">
            <span class="text-lg">📦</span> {{ __('general.np_warehouse') }}
        </button>
        <button type="button" wire:click="$set('deliveryType', 'postomat')"
                class="flex-1 py-3 font-bold border-2 border-black transition-colors {{ $deliveryType === 'postomat' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100' }}">
            <span class="text-lg">🏧</span> {{ __('general.np_postomat') }}
        </button>
        <button type="button" wire:click="$set('deliveryType', 'courier')"
                class="flex-1 py-3 font-bold border-2 border-black transition-colors {{ $deliveryType === 'courier' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100' }}">
            <span class="text-lg">🚗</span> {{ __('general.np_courier') }}
        </button>
    </div>

    {{-- City Search --}}
    <div class="relative" x-data="{ open: true }" @click.outside="open = false">
        <label class="block font-bold mb-1">{{ __('general.city_label') }}</label>
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="citySearch"
                   placeholder="{{ __('general.np_city_placeholder') }}"
                   class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black"
                   autocomplete="off"
                   @focus="open = true">

            @if($cityLoading)
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                </div>
            @endif
        </div>

        @if(count($citySuggestions) > 0 && mb_strlen($citySearch) >= 2 && !$cityRef)
            <div x-show="open" class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-48 overflow-y-auto shadow-lg">
                @foreach($citySuggestions as $index => $city)
                    <button type="button"
                            wire:click="selectCityByIndex({{ $index }})"
                            class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white font-medium transition-colors">
                        <span class="text-lg mr-1">🏙️</span>
                        {{ $city['Description'] }}{{ $city['AreaDescription'] ? ', ' . $city['AreaDescription'] . ' обл.' : '' }}
                    </button>
                @endforeach
            </div>
        @endif

        @if($cityRef)
            <div class="mt-1 text-sm text-green-700 font-bold flex items-center gap-1">
                <span>✓</span> {{ $cityName }}
            </div>
        @endif

        @error('citySearch')
            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Warehouse/Postomat Selection --}}
    @if($cityRef && in_array($deliveryType, ['warehouse', 'postomat']))
        <div class="relative" x-data="{ open: true, view: 'list' }" @click.outside="open = false">
            <div class="flex justify-between items-center mb-1">
                <label class="block font-bold">
                    {{ $deliveryType === 'postomat' ? __('general.postomat_label') : __('general.np_warehouse_label') }}
                </label>
                @php
                    $hasCoords = collect($allWarehouses)->contains(fn ($w) => !empty($w['lat']) && !empty($w['lng']));
                @endphp
                @if($hasCoords)
                    <div class="flex border-2 border-black text-sm font-bold">
                        <button type="button" @click="view = 'list'"
                                :class="view === 'list' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                                class="px-3 py-1 transition-colors">
                            📋 СПИСОК
                        </button>
                        <button type="button"
                                @click="view = 'map';
                                    [50, 200, 500, 1000].forEach(d => setTimeout(() => document.dispatchEvent(new Event('np-map-show')), d))"
                                :class="view === 'map' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'"
                                class="px-3 py-1 transition-colors border-l-2 border-black">
                            🗺️ МАПА
                        </button>
                    </div>
                @endif
            </div>

            {{-- Map view (wire:ignore on BOTH wrapper and inner so Leaflet's
                 injected tiles + clusters survive every Livewire morph) --}}
            <div x-show="view === 'map'" x-cloak class="border-2 border-black mb-2"
                 wire:ignore.self wire:key="np-map-wrap-{{ $this->getId() }}">
                <div wire:ignore
                     id="np-map-{{ $this->getId() }}"
                     style="height: 380px; width: 100%; background: #f0f0f0;"></div>
            </div>

            <div x-show="view === 'list'" class="relative">
                <input type="text" wire:model.live.debounce.300ms="warehouseSearch"
                       placeholder="{{ __('general.np_warehouse_search_placeholder') }}"
                       class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black"
                       autocomplete="off"
                       @focus="open = true">

                @if($warehouseLoading)
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                    </div>
                @endif
            </div>

            @if(count($warehouseSuggestions) > 0 && !$warehouseRef)
                <div x-show="open" class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-60 overflow-y-auto shadow-lg">
                    @foreach($warehouseSuggestions as $index => $warehouse)
                        <button type="button"
                                wire:click="selectWarehouseByIndex({{ $index }})"
                                class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white transition-colors border-b border-gray-100 last:border-b-0">
                            <span class="text-lg mr-1">{{ $deliveryType === 'postomat' ? '📮' : '🏢' }}</span>
                            <span class="font-bold">№{{ $warehouse['number'] }}</span>
                            <span class="text-sm ml-1">{{ $warehouse['address'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            @if($warehouseRef)
                <div class="mt-1 text-sm text-green-700 font-bold flex items-center gap-1">
                    <span>✓</span> {{ $warehouseName }}
                </div>
            @endif

            @if(!$warehouseLoading && empty($allWarehouses) && $cityRef)
                <div class="mt-1 text-sm text-orange-600 font-medium">
                    {{ __('general.np_no_warehouses') }}
                </div>
            @endif

            @error('warehouseSearch')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror

            {{-- Map data carrier — picked up by /assets/js/np-map.js which
                 also registers a Livewire hook to refresh on every morph. --}}
            <div id="np-map-data-{{ $this->getId() }}"
                 data-warehouses="{{ json_encode(collect($allWarehouses)->filter(fn ($w) => !empty($w['lat']) && !empty($w['lng']))->values(), JSON_UNESCAPED_UNICODE) }}"
                 data-selected-ref="{{ $warehouseRef }}"
                 data-cmp-id="{{ $this->getId() }}"
                 style="display:none"></div>

            @if(false)
                <script>
                (() => {
                    // Lazy-load Leaflet CSS/JS once
                    if (!window.__npLeafletLoading) {
                        window.__npLeafletLoading = true;
                        const css1 = document.createElement('link');
                        css1.rel = 'stylesheet';
                        css1.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(css1);

                        const css2 = document.createElement('link');
                        css2.rel = 'stylesheet';
                        css2.href = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css';
                        document.head.appendChild(css2);

                        const s1 = document.createElement('script');
                        s1.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        document.head.appendChild(s1);
                        s1.onload = () => {
                            const s2 = document.createElement('script');
                            s2.src = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
                            document.head.appendChild(s2);
                            s2.onload = () => { window.__npLeafletReady = true; document.dispatchEvent(new Event('np-leaflet-ready')); };
                        };
                    }

                    const elId = 'np-map-{{ $this->getId() }}';
                    const dataElId = 'np-map-data-{{ $this->getId() }}';

                    const readData = () => {
                        const dataEl = document.getElementById(dataElId);
                        if (!dataEl) return null;
                        try {
                            return {
                                warehouses: JSON.parse(dataEl.getAttribute('data-warehouses') || '[]'),
                                selectedRef: dataEl.getAttribute('data-selected-ref') || '',
                                cmpId: dataEl.getAttribute('data-cmp-id') || '',
                            };
                        } catch (e) {
                            return null;
                        }
                    };

                    const data0 = readData();
                    if (!data0 || !data0.warehouses.length) return;

                    const cmpId = data0.cmpId;

                    window.__npMaps = window.__npMaps || {};

                    const selectedIcon = () => L.divIcon({
                        className: 'np-marker-selected',
                        html: '<div style="position:relative;width:38px;height:50px;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.4))">'
                            + '<svg viewBox="0 0 38 50" width="38" height="50" xmlns="http://www.w3.org/2000/svg">'
                            + '<path d="M19 0C8.5 0 0 8.5 0 19c0 13.5 19 31 19 31s19-17.5 19-31C38 8.5 29.5 0 19 0z" fill="#10b981"/>'
                            + '<circle cx="19" cy="19" r="11" fill="#fff"/>'
                            + '<path d="M13.5 19l4 4 7-8" stroke="#10b981" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>'
                            + '</svg></div>',
                        iconSize: [38, 50],
                        iconAnchor: [19, 50],
                        popupAnchor: [0, -42],
                    });

                    const buildPopup = (w, isSelected) => `
                        <div style="min-width:200px">
                            ${isSelected ? '<div style="background:#10b981;color:#fff;padding:2px 8px;font-size:11px;font-weight:bold;display:inline-block;margin-bottom:4px">✓ ОБРАНО</div><br>' : ''}
                            <strong>№${w.number}</strong><br>
                            <span>${w.address || ''}</span><br>
                            ${w.phone ? `<small>📞 ${w.phone}</small><br>` : ''}
                            ${w.max_weight ? `<small>Макс. ${w.max_weight}кг</small> ` : ''}
                            ${w.pos_terminal ? '<small>💳 POS</small><br>' : '<br>'}
                            ${isSelected
                                ? '<small style="color:#10b981;font-weight:bold">Це поточний вибір</small>'
                                : `<button onclick="window.Livewire.find('${cmpId}').call('selectWarehouseByRef', '${w.ref}'); this.closest('.leaflet-popup').querySelector('.leaflet-popup-close-button')?.click();"
                                    style="margin-top:8px;padding:6px 12px;background:#000;color:#fff;border:0;font-weight:bold;cursor:pointer">
                                    ОБРАТИ
                                </button>`}
                        </div>`;

                    const renderMarkers = (state) => {
                        const fresh = readData();
                        if (!fresh) return;
                        const warehouses = fresh.warehouses;
                        const selectedRef = fresh.selectedRef;
                        state.cluster.clearLayers();
                        let selectedMarker = null;
                        let selectedLatLng = null;
                        warehouses.forEach(w => {
                            const isSelected = w.ref === selectedRef;
                            const m = isSelected
                                ? L.marker([w.lat, w.lng], { icon: selectedIcon(), zIndexOffset: 1000 })
                                : L.marker([w.lat, w.lng]);
                            m.bindPopup(buildPopup(w, isSelected));
                            state.cluster.addLayer(m);
                            if (isSelected) {
                                selectedMarker = m;
                                selectedLatLng = [w.lat, w.lng];
                            }
                        });

                        if (selectedLatLng) {
                            state.map.setView(selectedLatLng, 16, { animate: true });
                            setTimeout(() => selectedMarker && selectedMarker.openPopup(), 200);
                        } else if (warehouses.length > 1) {
                            try {
                                const bounds = L.latLngBounds(warehouses.map(w => [w.lat, w.lng]));
                                if (bounds && bounds.isValid()) {
                                    state.map.fitBounds(bounds.pad(0.05), { maxZoom: 13, animate: false });
                                }
                            } catch (e) {}
                        } else {
                            state.map.setView([warehouses[0].lat, warehouses[0].lng], 14);
                        }
                    };

                    const initMap = () => {
                        if (typeof L === 'undefined' || typeof L.markerClusterGroup === 'undefined') {
                            return setTimeout(initMap, 300);
                        }
                        const el = document.getElementById(elId);
                        if (!el) return;

                        // Already mounted — only refresh markers + invalidate size.
                        if (window.__npMaps[elId] && window.__npMaps[elId].map._loaded !== false) {
                            const state = window.__npMaps[elId];
                            renderMarkers(state);
                            setTimeout(() => state.map.invalidateSize(true), 50);
                            return;
                        }

                        const map = L.map(elId, { preferCanvas: true, maxZoom: 19 });
                        const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap',
                            maxZoom: 19,
                        }).addTo(map);

                        const cluster = (typeof L.markerClusterGroup === 'function')
                            ? L.markerClusterGroup({ maxClusterRadius: 40 }) : L.layerGroup();
                        map.addLayer(cluster);

                        const state = { map, tileLayer, cluster };
                        window.__npMaps[elId] = state;
                        renderMarkers(state);

                        const refit = () => {
                            if (el.offsetWidth === 0 || el.offsetHeight === 0) return;
                            map.invalidateSize(true);
                            tileLayer.redraw();
                            const fresh = readData();
                            if (fresh && fresh.warehouses.length > 1) {
                                const bounds = L.latLngBounds(fresh.warehouses.map(w => [w.lat, w.lng]));
                                if (bounds && bounds.isValid()) {
                                    map.fitBounds(bounds.pad(0.05), { maxZoom: 13, animate: false });
                                }
                            }
                        };

                        document.addEventListener('np-map-show', () => {
                            [50, 200, 500, 1000].forEach(d => setTimeout(refit, d));
                        });

                        const ro = new ResizeObserver(() => refit());
                        ro.observe(el);
                    };

                    initMap();

                    // Re-init on every Livewire update — the data attributes on
                    // the carrier div hold the latest warehouses + selectedRef.
                    if (!window.__npMapHookRegistered && window.Livewire?.hook) {
                        window.__npMapHookRegistered = true;
                        window.Livewire.hook('morph.updated', () => {
                            for (const id of Object.keys(window.__npMaps || {})) {
                                const el = document.getElementById(id);
                                if (!el) {
                                    delete window.__npMaps[id];
                                    continue;
                                }
                                const state = window.__npMaps[id];
                                renderMarkers(state);
                                setTimeout(() => state.map.invalidateSize(true), 100);
                            }
                            // Initialize maps that didn't exist yet
                            document.querySelectorAll('[id^=np-map-data-]').forEach(d => {
                                const cmpId = d.getAttribute('data-cmp-id');
                                const mapEl = document.getElementById('np-map-' + cmpId);
                                if (mapEl && !window.__npMaps[mapEl.id]) initMap();
                            });
                        });
                    }
                })();
                </script>
                @endscript
            @endif
        </div>
    @endif

    {{-- Courier Address --}}
    @if($cityRef && $deliveryType === 'courier')
        <div class="space-y-4">
            <div class="relative" x-data="{ open: true }" @click.outside="open = false">
                <label class="block font-bold mb-1">{{ __('general.street_label') }}</label>
                <input type="text" wire:model.live.debounce.300ms="streetSearch"
                       placeholder="{{ __('general.street_placeholder') }}"
                       autocomplete="off"
                       @focus="open = true"
                       class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                @if($streetLoading)
                    <div class="absolute right-3 top-10">
                        <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                    </div>
                @endif
                @if(count($streetSuggestions) > 0 && !$streetRef)
                    <div x-show="open" class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-60 overflow-y-auto shadow-lg">
                        @foreach($streetSuggestions as $index => $street)
                            <button type="button"
                                    wire:click="selectStreetByIndex({{ $index }})"
                                    class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white transition-colors border-b border-gray-100 last:border-b-0">
                                {{ $street['name'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
                @if($streetRef)
                    <div class="mt-1 text-sm text-green-700 font-bold">✓ {{ $streetSearch }}</div>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold mb-1">{{ __('general.building_label') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="building"
                           placeholder="15"
                           class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                    <label class="block font-bold mb-1">{{ __('general.apartment_label') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="apartment"
                           placeholder="23"
                           class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                    <label class="block font-bold mb-1">Поверх</label>
                    <input type="number" wire:model.live="floor"
                           placeholder="3"
                           min="1" max="50"
                           class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="hasElevator" class="w-5 h-5 border-2 border-black">
                <span class="font-bold">Є ліфт</span>
            </label>
        </div>
    @endif

    {{-- Preferred delivery time --}}
    @if($cityRef)
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">Бажана дата</label>
                <input type="date" wire:model.live="preferredDate"
                       min="{{ now()->addDay()->toDateString() }}"
                       class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
            </div>
            <div>
                <label class="block font-bold mb-1">Бажаний час</label>
                <select wire:model.live="preferredTime"
                        class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                    <option value="">— Будь-який —</option>
                    <option value="09:00-14:00">9:00 - 14:00</option>
                    <option value="14:00-18:00">14:00 - 18:00</option>
                    <option value="18:00-22:00">18:00 - 22:00</option>
                </select>
            </div>
        </div>
    @endif

    {{-- No city selected hint --}}
    @if(!$cityRef && !$citySearch)
        <div class="text-sm text-gray-500 font-medium">
            {{ __('general.np_select_city_first') }}
        </div>
    @endif

    {{-- Shipping Cost & Delivery Estimate --}}
    @if($shippingCost !== null)
        <div class="bg-gray-100 border-2 border-black p-4">
            <div class="flex justify-between font-bold">
                <span>{{ __('general.np_shipping_cost') }}:</span>
                <span>{{ number_format($shippingCost, 0) }} ₴</span>
            </div>
            @if($estimatedDelivery)
                <div class="flex justify-between text-sm mt-1 text-gray-600">
                    <span>{{ __('general.np_estimated_delivery') }}:</span>
                    <span>{{ $estimatedDelivery }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
