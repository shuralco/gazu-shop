(function () {
    'use strict';

    var LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    var LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    var CLUSTER_JS = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
    var CLUSTER_CSS = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css';

    var loadingPromise = null;
    function loadLeaflet() {
        if (loadingPromise) return loadingPromise;
        loadingPromise = new Promise(function (resolve) {
            ['link[href*="leaflet@"]', 'link[href*="MarkerCluster"]'].forEach(function (sel) {
                if (document.querySelector(sel)) return;
            });
            var l1 = document.createElement('link'); l1.rel = 'stylesheet'; l1.href = LEAFLET_CSS; document.head.appendChild(l1);
            var l2 = document.createElement('link'); l2.rel = 'stylesheet'; l2.href = CLUSTER_CSS; document.head.appendChild(l2);
            var s1 = document.createElement('script'); s1.src = LEAFLET_JS;
            s1.onload = function () {
                var s2 = document.createElement('script'); s2.src = CLUSTER_JS;
                s2.onload = function () { resolve(); };
                document.head.appendChild(s2);
            };
            document.head.appendChild(s1);
        });
        return loadingPromise;
    }

    var state = { map: null, cluster: null };

    // Делегований клік на кнопку «Обрати» в popup — на document, бо Leaflet popup може рендеритись поза map element
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.gazu-np-pick');
        if (!btn) return;
        e.preventDefault();
        var ref = btn.getAttribute('data-ref');
        document.dispatchEvent(new CustomEvent('np-map-pick', { detail: { ref: ref } }));
        // Закриваємо popup
        if (state.map) state.map.closePopup();
    });

    function buildPopup(w, isSelected) {
        var s = 'font-family:system-ui,sans-serif;line-height:1.4';
        var html = '<div style="min-width:220px;'+s+'">';
        if (isSelected) {
            html += '<div style="background:#10b981;color:#fff;padding:3px 10px;font-size:11px;font-weight:700;letter-spacing:0.5px;display:inline-block;margin-bottom:6px;text-transform:uppercase">✓ ОБРАНО</div><br>';
        }
        html += '<div style="font-size:15px;font-weight:700;color:#000">№' + (w.num || '?') + '</div>';
        html += '<div style="font-size:13px;color:#333;margin-top:2px">' + (w.addr || '') + '</div>';
        if (isSelected) {
            html += '<div style="font-size:11px;color:#10b981;font-weight:700;margin-top:6px">Це поточний вибір</div>';
        } else {
            html += '<button type="button" data-ref="' + w.ref + '" class="gazu-np-pick" '
                  + 'style="margin-top:10px;padding:8px 16px;background:#000;color:#fff;border:0;'
                  + 'font-weight:700;letter-spacing:0.5px;font-size:12px;text-transform:uppercase;'
                  + 'cursor:pointer;width:100%">ОБРАТИ</button>';
        }
        html += '</div>';
        return html;
    }

    function render(el) {
        if (!window.L || !window.L.markerClusterGroup) return;
        var data;
        try { data = JSON.parse(el.getAttribute('data-warehouses') || '[]'); } catch (e) { data = []; }
        var selectedRef = el.getAttribute('data-selected-ref') || '';
        if (!data.length) return;

        if (!state.map) {
            state.map = L.map(el, { preferCanvas: true, maxZoom: 19 });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(state.map);
            state.cluster = L.markerClusterGroup({ maxClusterRadius: 40 });
            state.map.addLayer(state.cluster);
        }

        state.cluster.clearLayers();
        var selectedLatLng = null;
        var selectedMarker = null;
        data.forEach(function (w) {
            var isSelected = w.ref === selectedRef;
            var icon = isSelected
                ? L.divIcon({
                    className: 'np-marker-selected',
                    html: '<div style="width:32px;height:42px;filter:drop-shadow(0 4px 6px rgba(0,0,0,.4))">'
                        + '<svg viewBox="0 0 38 50" width="32" height="42" xmlns="http://www.w3.org/2000/svg">'
                        + '<path d="M19 0C8.5 0 0 8.5 0 19c0 13.5 19 31 19 31s19-17.5 19-31C38 8.5 29.5 0 19 0z" fill="#10b981"/>'
                        + '<circle cx="19" cy="19" r="11" fill="#fff"/>'
                        + '<path d="M13.5 19l4 4 7-8" stroke="#10b981" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>'
                        + '</svg></div>',
                    iconSize: [32, 42], iconAnchor: [16, 42], popupAnchor: [0, -36]
                })
                : null;
            var m = icon ? L.marker([w.lat, w.lng], { icon: icon, zIndexOffset: 1000 }) : L.marker([w.lat, w.lng]);
            m._gazuWh = w;
            m.bindPopup(buildPopup(w, isSelected));
            state.cluster.addLayer(m);
            if (isSelected) { selectedMarker = m; selectedLatLng = [w.lat, w.lng]; }
        });

        setTimeout(function () { state.map.invalidateSize(true); }, 50);

        if (selectedLatLng) {
            state.map.setView(selectedLatLng, 15, { animate: false });
            setTimeout(function () { selectedMarker && selectedMarker.openPopup(); }, 100);
        } else if (data.length > 1) {
            try {
                var bounds = L.latLngBounds(data.map(function (w) { return [w.lat, w.lng]; }));
                state.map.fitBounds(bounds.pad(0.05), { maxZoom: 14, animate: false });
            } catch (e) {}
        } else {
            state.map.setView([data[0].lat, data[0].lng], 14);
        }
    }

    document.addEventListener('np-map-render', function () {
        var el = document.getElementById('gazu-np-map');
        if (!el) return;
        loadLeaflet().then(function () { render(el); });
    });

    // ResizeObserver — оновлювати мапу коли блок стає видимим
    var ro = new ResizeObserver(function () {
        if (state.map) {
            try { state.map.invalidateSize(true); } catch (e) {}
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('gazu-np-map');
        if (el) ro.observe(el);
    });
})();
