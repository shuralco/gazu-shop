@php
    $metrics = \App\Support\DashboardMetrics::all();
@endphp

<x-filament-panels::page class="fi-dashboard-page">
    <style>
        .gz-bar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.25rem;flex-wrap:wrap}
        .gz-bar-hint{font-size:.8rem;color:rgb(113 113 122);display:flex;align-items:center;gap:.4rem}
        .dark .gz-bar-hint{color:rgb(161 161 170)}
        .gz-reset{font-size:.78rem;font-weight:600;color:rgb(82 82 91);background:rgb(244 244 245);border:1px solid rgb(228 228 231);border-radius:.5rem;padding:.3rem .7rem;cursor:pointer;transition:.15s}
        .gz-reset:hover{background:rgb(228 228 231)}
        .dark .gz-reset{color:rgb(212 212 216);background:rgb(39 39 42);border-color:rgb(63 63 70)}
        .dark .gz-reset:hover{background:rgb(63 63 70)}

        .gz-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(215px,1fr));gap:.75rem}
        .gz-card{position:relative;display:flex;flex-direction:column;gap:.35rem;padding:.95rem 1rem;background:#fff;border:1px solid rgb(228 228 231);border-radius:.85rem;box-shadow:0 1px 2px rgba(0,0,0,.04);cursor:grab;user-select:none;transition:box-shadow .15s,transform .12s,border-color .15s;overflow:hidden}
        .gz-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.08);border-color:rgb(212 212 216)}
        .gz-card:active{cursor:grabbing}
        .dark .gz-card{background:rgb(24 24 27);border-color:rgb(39 39 42);box-shadow:none}
        .dark .gz-card:hover{border-color:rgb(63 63 70)}
        .gz-card.gz-dragging{opacity:.35;transform:scale(.97)}
        .gz-card.gz-over{border-color:rgb(var(--primary-500,59 130 246));box-shadow:0 0 0 2px rgba(var(--primary-500,59 130 246),.35)}
        .gz-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gz-accent,#71717a)}

        .gz-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
        .gz-ico{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:.6rem;background:color-mix(in srgb,var(--gz-accent,#71717a) 14%,transparent);color:var(--gz-accent,#71717a);flex-shrink:0}
        .gz-ico svg{width:20px;height:20px}
        .gz-grip{color:rgb(212 212 216);opacity:0;transition:.15s}
        .gz-card:hover .gz-grip{opacity:1}
        .dark .gz-grip{color:rgb(82 82 91)}
        .gz-grip svg{width:18px;height:18px}

        .gz-val{font-size:1.65rem;line-height:1.1;font-weight:700;color:rgb(24 24 27);letter-spacing:-.02em}
        .dark .gz-val{color:#fafafa}
        .gz-label{font-size:.82rem;font-weight:600;color:rgb(63 63 70)}
        .dark .gz-label{color:rgb(212 212 216)}
        .gz-sub{font-size:.74rem;color:rgb(113 113 122)}
        .dark .gz-sub{color:rgb(161 161 170)}
        .gz-spark{margin-top:.15rem}
        .gz-spark svg{display:block;width:100%;height:26px;overflow:visible}

        /* accent palette (працює і в light, і в dark, незалежно від Tailwind) */
        .gz-c-success{--gz-accent:#16a34a}
        .gz-c-warning{--gz-accent:#d97706}
        .gz-c-danger{--gz-accent:#dc2626}
        .gz-c-info{--gz-accent:#2563eb}
        .gz-c-primary{--gz-accent:rgb(var(--primary-600,37 99 235))}
        .gz-c-gray{--gz-accent:#71717a}

        @media (max-width:640px){.gz-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}.gz-val{font-size:1.4rem}}
    </style>

    <div class="gz-bar">
        <div class="gz-bar-hint">
            <x-filament::icon icon="heroicon-m-arrows-pointing-out" style="width:1rem;height:1rem"/>
            Перетягуйте картки, щоб змінити порядок — він збережеться у цьому браузері
        </div>
        <button type="button" class="gz-reset" onclick="window.gzResetDashboard&&window.gzResetDashboard()">
            ↺ Скинути порядок
        </button>
    </div>

    <div class="gz-grid" id="gz-dashboard-grid">
        @foreach ($metrics as $i => $m)
            <div class="gz-card gz-c-{{ $m['color'] ?? 'gray' }}" draggable="true" data-gz-id="{{ $m['id'] }}" data-gz-default="{{ $i }}">
                <div class="gz-top">
                    <div class="gz-ico">
                        <x-filament::icon :icon="$m['icon'] ?? 'heroicon-o-chart-bar'"/>
                    </div>
                    <span class="gz-grip" title="Перетягнути">
                        <x-filament::icon icon="heroicon-o-bars-3"/>
                    </span>
                </div>
                <div class="gz-val">{{ $m['value'] }}</div>
                <div class="gz-label">{{ $m['label'] }}</div>
                @if (!empty($m['sub']))
                    <div class="gz-sub">{{ $m['sub'] }}</div>
                @endif
                @if (!empty($m['spark']) && is_array($m['spark']))
                    @php
                        $vals = array_map('floatval', $m['spark']);
                        $max = max($vals) ?: 1; $min = min($vals);
                        $range = ($max - $min) ?: 1; $n = count($vals); $w = 100; $h = 26;
                        $pts = [];
                        foreach ($vals as $k => $v) {
                            $x = $n > 1 ? round($k / ($n - 1) * $w, 2) : 0;
                            $y = round($h - (($v - $min) / $range) * $h, 2);
                            $pts[] = "$x,$y";
                        }
                        $line = implode(' ', $pts);
                    @endphp
                    <div class="gz-spark">
                        <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none">
                            <polyline points="{{ $line }}" fill="none" stroke="var(--gz-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                        </svg>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Графіки + таблиці (звичайні Filament-віджети) під сіткою метрик --}}
    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="$this->getWidgetData()"
        :widgets="$this->getVisibleWidgets()"
    />

    <script>
        (function () {
            const KEY = 'gazu_dashboard_order_v1';

            function init() {
                const grid = document.getElementById('gz-dashboard-grid');
                if (!grid || grid.dataset.gzInit === '1') return;
                grid.dataset.gzInit = '1';

                // 1) відновити збережений порядок
                try {
                    const saved = JSON.parse(localStorage.getItem(KEY) || '[]');
                    if (Array.isArray(saved) && saved.length) {
                        const byId = {};
                        grid.querySelectorAll('.gz-card').forEach(c => byId[c.dataset.gzId] = c);
                        saved.forEach(id => { if (byId[id]) grid.appendChild(byId[id]); });
                        // нові картки (яких не було у збереженому порядку) — лишаються в кінці у дефолтному порядку
                        Object.values(byId).forEach(c => { if (!saved.includes(c.dataset.gzId)) grid.appendChild(c); });
                    }
                } catch (e) {}

                function save() {
                    const order = [...grid.querySelectorAll('.gz-card')].map(c => c.dataset.gzId);
                    try { localStorage.setItem(KEY, JSON.stringify(order)); } catch (e) {}
                }

                let dragged = null;
                grid.addEventListener('dragstart', e => {
                    const card = e.target.closest('.gz-card');
                    if (!card) return;
                    dragged = card;
                    card.classList.add('gz-dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    try { e.dataTransfer.setData('text/plain', card.dataset.gzId); } catch (_) {}
                });
                grid.addEventListener('dragend', e => {
                    if (dragged) dragged.classList.remove('gz-dragging');
                    grid.querySelectorAll('.gz-over').forEach(c => c.classList.remove('gz-over'));
                    dragged = null;
                    save();
                });
                grid.addEventListener('dragover', e => {
                    e.preventDefault();
                    const target = e.target.closest('.gz-card');
                    if (!target || target === dragged || !dragged) return;
                    grid.querySelectorAll('.gz-over').forEach(c => { if (c !== target) c.classList.remove('gz-over'); });
                    target.classList.add('gz-over');
                    const r = target.getBoundingClientRect();
                    // вставляємо до/після залежно від положення курсора (по обох осях для сітки)
                    const after = (e.clientY - r.top) > r.height / 2 || (Math.abs(e.clientY - (r.top + r.height/2)) < r.height/2 && (e.clientX - r.left) > r.width / 2);
                    grid.insertBefore(dragged, after ? target.nextSibling : target);
                });
                grid.addEventListener('drop', e => {
                    e.preventDefault();
                    grid.querySelectorAll('.gz-over').forEach(c => c.classList.remove('gz-over'));
                    save();
                });
            }

            window.gzResetDashboard = function () {
                try { localStorage.removeItem('gazu_dashboard_order_v1'); } catch (e) {}
                const grid = document.getElementById('gz-dashboard-grid');
                if (!grid) return;
                [...grid.querySelectorAll('.gz-card')]
                    .sort((a, b) => (+a.dataset.gzDefault) - (+b.dataset.gzDefault))
                    .forEach(c => grid.appendChild(c));
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
</x-filament-panels::page>
