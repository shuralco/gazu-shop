@php
    $groups = \App\Support\DashboardMetrics::arrangedGroups();
@endphp

<x-filament-panels::page class="fi-dashboard-page">
    <style>
        .gz-bar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.25rem;flex-wrap:wrap}
        .gz-bar-hint{font-size:.8rem;color:rgb(113 113 122);display:flex;align-items:center;gap:.4rem}
        .dark .gz-bar-hint{color:rgb(161 161 170)}
        .gz-reset{font-size:.78rem;font-weight:600;color:rgb(82 82 91);background:rgb(244 244 245);border:1px solid rgb(228 228 231);border-radius:.5rem;padding:.3rem .7rem;cursor:pointer;transition:.15s;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem}
        .gz-reset:hover{background:rgb(228 228 231)}
        .dark .gz-reset{color:rgb(212 212 216);background:rgb(39 39 42);border-color:rgb(63 63 70)}
        .dark .gz-reset:hover{background:rgb(63 63 70)}

        .gz-group-title{margin:1.1rem 0 .15rem;font-size:.95rem;font-weight:700;color:rgb(39 39 42);letter-spacing:-.01em}
        .dark .gz-group-title{color:#e4e4e7}
        .gz-group-title:first-of-type{margin-top:.25rem}

        .gz-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(215px,1fr));gap:.75rem}
        .gz-card{position:relative;display:flex;flex-direction:column;gap:.35rem;padding:.95rem 1rem;background:#fff;border:1px solid rgb(228 228 231);border-radius:.85rem;box-shadow:0 1px 2px rgba(0,0,0,.04);user-select:none;transition:box-shadow .15s,border-color .15s;overflow:hidden}
        .gz-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.08);border-color:rgb(212 212 216)}
        .dark .gz-card{background:rgb(24 24 27);border-color:rgb(39 39 42);box-shadow:none}
        .dark .gz-card:hover{border-color:rgb(63 63 70)}
        .gz-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gz-accent,#71717a)}

        .gz-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
        .gz-ico{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:.6rem;background:color-mix(in srgb,var(--gz-accent,#71717a) 14%,transparent);color:var(--gz-accent,#71717a);flex-shrink:0}
        .gz-ico svg{width:20px;height:20px}

        .gz-val{font-size:1.65rem;line-height:1.1;font-weight:700;color:rgb(24 24 27);letter-spacing:-.02em}
        .dark .gz-val{color:#fafafa}
        .gz-label{font-size:.82rem;font-weight:600;color:rgb(63 63 70)}
        .dark .gz-label{color:rgb(212 212 216)}
        .gz-sub{font-size:.74rem;color:rgb(113 113 122)}
        .dark .gz-sub{color:rgb(161 161 170)}
        .gz-spark{margin-top:.15rem}
        .gz-spark svg{display:block;width:100%;height:26px;overflow:visible}

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
            <x-filament::icon icon="heroicon-m-squares-2x2" style="width:1rem;height:1rem"/>
            Видимість, порядок і групи карток — у налаштуваннях дашборду
        </div>
        @if(\Illuminate\Support\Facades\Route::has('filament.admin.pages.dashboard-settings'))
            <a href="{{ route('filament.admin.pages.dashboard-settings') }}" class="gz-reset" wire:navigate>
                <x-filament::icon icon="heroicon-o-cog-6-tooth" style="width:.95rem;height:.95rem"/>
                Налаштувати дашборд
            </a>
        @endif
    </div>

    @forelse ($groups as $group)
        <div class="gz-group-title">{{ $group['label'] }}</div>
        <div class="gz-grid">
            @foreach ($group['cards'] as $m)
                <div class="gz-card gz-c-{{ $m['color'] ?? 'gray' }}">
                    <div class="gz-top">
                        <div class="gz-ico">
                            <x-filament::icon :icon="$m['icon'] ?? 'heroicon-o-chart-bar'"/>
                        </div>
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

        {{-- «Останні замовлення» — одразу під групою «Продажі» --}}
        @if($group['key'] === 'sales')
            <div style="margin-top:1rem">
                @livewire(\App\Filament\Widgets\LatestOrders::class)
            </div>
        @endif
    @empty
        <div class="gz-bar-hint" style="margin-top:1rem">Усі картки сховані — увімкніть у «Налаштувати дашборд».</div>
    @endforelse

    {{-- Графіки + таблиці (звичайні Filament-віджети) під сіткою метрик --}}
    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="$this->getWidgetData()"
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
