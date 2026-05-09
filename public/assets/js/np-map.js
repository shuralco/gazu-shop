/**
 * Nova Poshta map renderer.
 *
 * Driven by data-* attributes on a hidden <div id="np-map-data-{cmpId}">
 * inside the NovaPoshtaSelector blade view. Re-runs on every Livewire
 * morph so the map survives re-renders and reflects current state.
 *
 * Public globals:
 *   window.__npMaps[elId] = { map, tileLayer, cluster }
 *   window.__npLeafletReady (boolean)
 */
(function () {
    'use strict';

    const LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    const CLUSTER_CSS = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css';
    const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    const CLUSTER_JS = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';

    function loadLeaflet() {
        if (window.__npLeafletLoading) return;
        window.__npLeafletLoading = true;

        function appendLink(href) {
            const l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = href;
            document.head.appendChild(l);
        }
        appendLink(LEAFLET_CSS);
        appendLink(CLUSTER_CSS);

        const s1 = document.createElement('script');
        s1.src = LEAFLET_JS;
        document.head.appendChild(s1);
        s1.onload = function () {
            const s2 = document.createElement('script');
            s2.src = CLUSTER_JS;
            document.head.appendChild(s2);
            s2.onload = function () {
                window.__npLeafletReady = true;
                document.dispatchEvent(new Event('np-leaflet-ready'));
                scanAndInit();
            };
        };
    }

    function readData(dataEl) {
        try {
            return {
                warehouses: JSON.parse(dataEl.getAttribute('data-warehouses') || '[]'),
                selectedRef: dataEl.getAttribute('data-selected-ref') || '',
                cmpId: dataEl.getAttribute('data-cmp-id') || '',
            };
        } catch (e) {
            return null;
        }
    }

    function selectedIcon() {
        return L.divIcon({
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
    }

    function buildPopup(w, isSelected, cmpId) {
        return ''
            + '<div style="min-width:200px">'
            + (isSelected
                ? '<div style="background:#10b981;color:#fff;padding:2px 8px;font-size:11px;font-weight:bold;display:inline-block;margin-bottom:4px">✓ ОБРАНО</div><br>'
                : '')
            + '<strong>№' + w.number + '</strong><br>'
            + '<span>' + (w.address || '') + '</span><br>'
            + (w.phone ? '<small>📞 ' + w.phone + '</small><br>' : '')
            + (w.max_weight ? '<small>Макс. ' + w.max_weight + 'кг</small> ' : '')
            + (w.pos_terminal ? '<small>💳 POS</small><br>' : '<br>')
            + (isSelected
                ? '<small style="color:#10b981;font-weight:bold">Це поточний вибір</small>'
                : '<button onclick="window.Livewire.find(\'' + cmpId + '\').call(\'selectWarehouseByRef\', \'' + w.ref + '\'); this.closest(\'.leaflet-popup\').querySelector(\'.leaflet-popup-close-button\')?.click();" style="margin-top:8px;padding:6px 12px;background:#000;color:#fff;border:0;font-weight:bold;cursor:pointer">ОБРАТИ</button>')
            + '</div>';
    }

    function renderMarkers(state, cmpId) {
        const dataEl = document.getElementById('np-map-data-' + cmpId);
        if (!dataEl) return;
        const data = readData(dataEl);
        if (!data) return;
        const { warehouses, selectedRef } = data;

        state.cluster.clearLayers();
        let selectedMarker = null;
        let selectedLatLng = null;

        warehouses.forEach(function (w) {
            const isSelected = w.ref === selectedRef;
            const m = isSelected
                ? L.marker([w.lat, w.lng], { icon: selectedIcon(), zIndexOffset: 1000 })
                : L.marker([w.lat, w.lng]);
            m.bindPopup(buildPopup(w, isSelected, cmpId));
            state.cluster.addLayer(m);
            if (isSelected) {
                selectedMarker = m;
                selectedLatLng = [w.lat, w.lng];
            }
        });

        if (selectedLatLng) {
            state.map.setView(selectedLatLng, 16, { animate: true });
            setTimeout(function () { if (selectedMarker) selectedMarker.openPopup(); }, 200);
        } else if (warehouses.length > 1) {
            try {
                const bounds = L.latLngBounds(warehouses.map(function (w) { return [w.lat, w.lng]; }));
                if (bounds && bounds.isValid()) {
                    state.map.fitBounds(bounds.pad(0.05), { maxZoom: 13, animate: false });
                }
            } catch (e) {}
        } else if (warehouses.length === 1) {
            state.map.setView([warehouses[0].lat, warehouses[0].lng], 14);
        }
    }

    function initMap(dataEl) {
        const data = readData(dataEl);
        if (!data || !data.warehouses.length) return;
        if (typeof L === 'undefined' || typeof L.markerClusterGroup === 'undefined') {
            return setTimeout(function () { initMap(dataEl); }, 300);
        }

        const cmpId = data.cmpId;
        const elId = 'np-map-' + cmpId;
        const el = document.getElementById(elId);
        if (!el) return;

        // Wait until container is actually visible (x-show toggles display)
        // before mounting Leaflet — Leaflet won't load tiles into a 0x0 box.
        if (el.offsetWidth === 0 || el.offsetHeight === 0) {
            return setTimeout(function () { initMap(dataEl); }, 200);
        }

        window.__npMaps = window.__npMaps || {};

        if (window.__npMaps[elId]) {
            const existing = window.__npMaps[elId];
            // If Leaflet's internal container was wiped (Livewire morph etc.)
            // or if the element has no leaflet children — drop and rebuild.
            const hasLeaflet = el.classList.contains('leaflet-container') && el.children.length > 0;
            if (hasLeaflet) {
                try {
                    renderMarkers(existing, cmpId);
                    setTimeout(function () { existing.map.invalidateSize(true); }, 50);
                    return;
                } catch (e) {}
            }
            try { existing.map.remove(); } catch (e) {}
            delete window.__npMaps[elId];
            el.innerHTML = '';
            el.className = '';
        }

        const map = L.map(elId, { preferCanvas: true, maxZoom: 19 });
        const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        const cluster = typeof L.markerClusterGroup === 'function'
            ? L.markerClusterGroup({ maxClusterRadius: 40 })
            : L.layerGroup();
        map.addLayer(cluster);

        const state = { map, tileLayer, cluster };
        window.__npMaps[elId] = state;
        renderMarkers(state, cmpId);

        function refit() {
            if (el.offsetWidth === 0 || el.offsetHeight === 0) return;
            map.invalidateSize(true);
            tileLayer.redraw();
            const fresh = readData(dataEl);
            if (fresh && fresh.warehouses.length > 1) {
                const bounds = L.latLngBounds(fresh.warehouses.map(function (w) { return [w.lat, w.lng]; }));
                if (bounds && bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.05), { maxZoom: 13, animate: false });
                }
            }
        }

        document.addEventListener('np-map-show', function () {
            [50, 200, 500, 1000].forEach(function (d) { setTimeout(refit, d); });
        });

        if (typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(function () { refit(); });
            ro.observe(el);
        }
    }

    function scanAndInit() {
        document.querySelectorAll('[id^="np-map-data-"]').forEach(function (d) { initMap(d); });
    }

    function refreshAll() {
        document.querySelectorAll('[id^="np-map-data-"]').forEach(function (d) {
            const cmpId = d.getAttribute('data-cmp-id');
            const mapEl = document.getElementById('np-map-' + cmpId);
            if (!mapEl) return;
            const elId = 'np-map-' + cmpId;
            window.__npMaps = window.__npMaps || {};
            if (window.__npMaps[elId]) {
                renderMarkers(window.__npMaps[elId], cmpId);
                setTimeout(function () {
                    try { window.__npMaps[elId].map.invalidateSize(true); } catch (e) {}
                }, 100);
            } else {
                initMap(d);
            }
        });
    }

    function boot() {
        loadLeaflet();
        if (window.__npLeafletReady) scanAndInit();

        // Hook into Livewire morph.updated to refresh after every component update.
        function registerHook() {
            if (window.__npMapHookRegistered) return;
            if (!window.Livewire || !window.Livewire.hook) {
                return setTimeout(registerHook, 200);
            }
            window.__npMapHookRegistered = true;
            window.Livewire.hook('morph.updated', refreshAll);
            window.Livewire.hook('commit', function (payload) {
                if (payload.respond) payload.respond(refreshAll);
            });
        }
        registerHook();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
