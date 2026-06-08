@php $data = $this->getMapData(); @endphp
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-map-pin" class="h-5 w-5 text-primary-500" />
                Географія доставок
            </span>
        </x-slot>
        <x-slot name="headerEnd">
            <div class="flex items-center gap-2">
                <x-filament::badge color="primary">{{ $data['mapped'] }} на карті</x-filament::badge>
                @if($data['unknown'] > 0)
                    <x-filament::badge color="gray" :tooltip="'Міста без координат у довіднику'">+{{ $data['unknown'] }} інші</x-filament::badge>
                @endif
            </div>
        </x-slot>

        @if($data['total'] === 0)
            <div class="py-10 text-center">
                <x-filament::icon icon="heroicon-o-map" class="mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-gray-700" />
                <p class="text-sm text-gray-500">Поки що немає замовлень із доставкою.</p>
                <p class="mt-1 text-xs text-gray-400">Локації клієнтів зʼявляться тут автоматично після перших замовлень.</p>
            </div>
        @else
            {{-- Inline x-data: Alpine обчислює обʼєкт напряму при скануванні елемента —
                 не залежить від реєстрації Alpine.data чи inline <script> (Livewire їх
                 вирізає з шаблонів віджетів). Leaflet вантажиться через @assets. --}}
            <div
                wire:ignore
                x-data="{
                    points: @js($data['points']),
                    map: null,
                    init() { this.ensureLeaflet(() => this.render()); },
                    ensureLeaflet(cb) {
                        if (window.L) { cb(); return; }
                        if (!document.getElementById('gazu-leaflet-js')) {
                            const s = document.createElement('script');
                            s.id = 'gazu-leaflet-js';
                            s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                            document.head.appendChild(s);
                        }
                        if (!document.querySelector('link[href*=&quot;leaflet@1.9.4&quot;]')) {
                            const css = document.createElement('link');
                            css.rel = 'stylesheet';
                            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                            document.head.appendChild(css);
                        }
                        const t = setInterval(() => { if (window.L) { clearInterval(t); cb(); } }, 120);
                    },
                    render() {
                        if (!window.L || this.map || !this.$refs.map) return;
                        const map = L.map(this.$refs.map, { scrollWheelZoom: false, attributionControl: false }).setView([48.45, 31.5], 5.4);
                        this.map = map;
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 12 }).addTo(map);
                        const max = Math.max(1, ...this.points.map(p => p.count));
                        this.points.forEach(p => {
                            const r = 7 + Math.round((p.count / max) * 22);
                            L.circleMarker([p.lat, p.lng], { radius: r, color: '#1E47A1', weight: 1.5, fillColor: '#2453A6', fillOpacity: 0.55 })
                                .addTo(map).bindPopup('<b>' + p.city + '</b><br>замовлень: ' + p.count);
                        });
                        if (this.points.length) {
                            map.fitBounds(L.latLngBounds(this.points.map(p => [p.lat, p.lng])).pad(0.25), { maxZoom: 7 });
                        }
                        setTimeout(() => map.invalidateSize(), 200);
                    },
                }"
                class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
            >
                <div x-ref="map" style="height: 460px; width: 100%; background:#eef2f6;"></div>
            </div>
        @endif
    </x-filament::section>

    {{-- Leaflet (CDN) — @assets інжектить у <head> при першому рендері (віджет non-lazy) --}}
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endassets
</x-filament-widgets::widget>
