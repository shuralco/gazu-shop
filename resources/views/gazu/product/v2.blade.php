@extends('gazu.layout')

@section('title', ($p->name ?? 'Товар') . ' · інженерний — GAZU')

@php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $pId = is_object($p) ? ($p->id ?? 0) : ($p['id'] ?? 0);
    $pRealImg = is_object($p) ? ($p->image ?? null) : ($p['image'] ?? null);
    if ($pRealImg && ! \Illuminate\Support\Str::startsWith($pRealImg, ['http://','https://'])) { $pRealImg = url('/storage/'.ltrim((string)$pRealImg,'/')); }
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);

    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs) && ! empty($rawSpecs)) {
        $specs = [];
        foreach ($rawSpecs as $k => $v) {
            $isMono = preg_match('/^\d|[\.,×]|^[A-Z]\d/', (string) $v);
            $specs[] = [(string) $k, (string) $v, (bool) $isMono];
        }
    } else {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', $condition ?? 'Новий', false],
            ['Гарантія', '12 місяців', false],
        ];
    }

    // No demo fallback — empty arrays mean compat/analogs sections hide.
    $compat = [];
    $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
    if (is_array($rawCompat)) {
        foreach ($rawCompat as $row) {
            if (is_array($row)) {
                $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—'];
            }
        }
    }

    $analogs = [];
    $rawAnalogs = is_object($p) ? ($p->analogs ?? null) : ($p['analogs'] ?? null);
    if (is_array($rawAnalogs)) {
        foreach ($rawAnalogs as $row) {
            if (is_array($row)) {
                $analogs[] = [
                    $row['brand'] ?? '—',
                    $row['oem'] ?? '—',
                    (float) ($row['price'] ?? 0),
                    (int) ($row['qty'] ?? 0),
                    (float) ($row['rating'] ?? 0),
                ];
            }
        }
    }
@endphp

@section('content')
    <div class="gazu-container">
        @include('gazu.partials.product-breadcrumbs', compact('p', 'brand', 'oem', 'name'))

        <div class="gazu-grid-buy-left">
            <div>
                <div class="aspect-[4/3] bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center overflow-hidden">
                        @if($pRealImg)<img src="{{ $pRealImg }}" alt="{{ $name }}" class="w-full h-full object-contain"/>@else<x-gazu.product-placeholder :name="$name" :code="$oem" :seed="$pId" class="w-full h-full"/>@endif
                    </div>
                </div>
                <div class="grid grid-cols-5 gap-2 mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer overflow-hidden" style="border: 1.5px solid {{ $i === 1 ? 'var(--gazu-ink)' : 'var(--gazu-line)' }};">
                            @if($pRealImg)<img src="{{ $pRealImg }}" alt="" class="w-full h-full object-contain"/>@else<x-gazu.product-placeholder :name="$name" :code="$oem" :seed="$pId" class="w-full h-full"/>@endif
                        </div>
                    @endfor
                </div>

                <div class="mt-4.5 p-4 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg">
                    <div class="gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-widest uppercase mb-2.5">Розміри (мм)</div>
                    <svg width="100%" height="120" viewBox="0 0 400 120">
                        <rect x="120" y="30" width="160" height="60" fill="var(--gazu-bone)" stroke="var(--gazu-ink)" stroke-width="1.5"/>
                        <line x1="120" y1="20" x2="280" y2="20" stroke="var(--gazu-graphite)"/>
                        <line x1="120" y1="15" x2="120" y2="25" stroke="var(--gazu-graphite)"/>
                        <line x1="280" y1="15" x2="280" y2="25" stroke="var(--gazu-graphite)"/>
                        <text x="200" y="13" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="var(--gazu-ink)">76,2 мм</text>
                        <line x1="290" y1="30" x2="290" y2="90" stroke="var(--gazu-graphite)"/>
                        <line x1="285" y1="30" x2="295" y2="30" stroke="var(--gazu-graphite)"/>
                        <line x1="285" y1="90" x2="295" y2="90" stroke="var(--gazu-graphite)"/>
                        <text x="305" y="64" font-size="11" fill="var(--gazu-ink)">79 мм</text>
                        <circle cx="200" cy="60" r="20" fill="#fff" stroke="var(--gazu-blue)" stroke-width="1.5" stroke-dasharray="3 3"/>
                        <text x="200" y="64" text-anchor="middle" font-size="10" fill="var(--gazu-blue)">M20×1.5</text>
                    </svg>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-2.5 mb-2.5">
                    <x-gazu.condition-badge value="Новий"/>
                    <span class="gazu-mono text-[11px] px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded">Артикул</span>
                    <span class="gazu-display font-semibold text-[var(--gazu-ink)] text-sm">{{ $brand }}</span>
                </div>
                <h1 class="gazu-display text-[32px] font-semibold text-[var(--gazu-ink)] m-0 mb-1.5 leading-tight">{{ $name }}</h1>
                @if($oem)
                    <div class="text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-4.5">Артикул {{ $oem }}</div>
                @endif

                <x-gazu.buy-panel :price="$price" :oldPrice="$oldPrice" :qty="$qty" :discount="$discount" :productId="is_object($p) ? ($p->id ?? null) : null" :name="$name"/>

                <div class="mt-7 gazu-display text-lg font-semibold mb-3">Повні характеристики</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg px-4">
                    @foreach($specs as [$k, $v, $mono])
                        <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                            <span class="text-[var(--gazu-graphite)]">{{ $k }}</span>
                            <span class="text-[var(--gazu-ink)] {{ $mono ? 'gazu-mono font-medium' : '' }}">{{ $v }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-10 grid lg:grid-cols-2 gap-6">
            <div>
                <div class="gazu-display text-[22px] font-semibold mb-3.5">Сумісність</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr>
                                <th class="px-3.5 py-3 font-medium">Марка</th>
                                <th class="px-3.5 py-3 font-medium">Модель</th>
                                <th class="px-3.5 py-3 font-medium">Роки</th>
                                <th class="px-3.5 py-3 font-medium">Двигун</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compat as $r)
                                <tr class="border-t border-[var(--gazu-line)]">
                                    <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]">{{ $r[0] }}</td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-ink)]">{{ $r[1] }}</td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs">{{ $r[2] }}</td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs">{{ $r[3] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="gazu-display text-[22px] font-semibold mb-3.5">Аналоги та замінники</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr>
                                <th class="px-3.5 py-3 font-medium">Виробник</th>
                                <th class="px-3.5 py-3 font-medium">Артикул</th>
                                <th class="px-3.5 py-3 font-medium">Рейтинг</th>
                                <th class="px-3.5 py-3 font-medium">Наявність</th>
                                <th class="px-3.5 py-3 font-medium text-right">Ціна</th>
                                <th class="px-3.5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analogs as [$brnd, $oemA, $priceA, $qtyA, $rate])
                                <tr class="border-t border-[var(--gazu-line)]">
                                    <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]">{{ $brnd }}</td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-ink)] gazu-mono text-xs">{{ $oemA }}</td>
                                    <td class="px-3.5 py-3">
                                        <div class="flex gap-1.5 items-center">
                                            <div class="flex gap-px text-[var(--gazu-warn)]">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <x-gazu.icon name="star" size="11" fill="{{ $i <= floor($rate) ? 'var(--gazu-warn)' : 'none' }}" stroke="var(--gazu-warn)"/>
                                                @endfor
                                            </div>
                                            <span class="text-[11px] text-[var(--gazu-graphite)]">{{ $rate }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3"><x-gazu.stock qty="{{ $qtyA }}"/></td>
                                    <td class="px-3.5 py-3 text-right gazu-display font-bold text-[var(--gazu-ink)] text-[15px]">{{ $priceA }} ₴</td>
                                    <td class="px-3.5 py-3 text-right">
                                        <button type="button" class="px-3 py-1.5 bg-[var(--gazu-paper)] text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded text-xs cursor-pointer">У кошик</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
