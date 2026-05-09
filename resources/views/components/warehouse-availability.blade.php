@props(['product'])

@php
    use App\Models\MerchantWarehouse;

    $rows = MerchantWarehouse::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->with([
            'inventory' => fn ($q) => $q->where('product_id', $product->id),
        ])
        ->get()
        ->map(function (MerchantWarehouse $w) {
            $inv = $w->inventory->first();
            $available = $inv ? max(0, $inv->quantity - $inv->reserved_quantity) : 0;

            return [
                'code' => $w->code,
                'name' => $w->name,
                'city' => $w->city,
                'pickup' => $w->pickup_supported,
                'available' => $available,
                'low' => $available > 0 && $available <= 5,
            ];
        });

    $hasAny = $rows->contains(fn ($r) => $r['available'] > 0);
@endphp

@if($rows->isNotEmpty())
    <div class="mt-4 border-2 border-black p-4 bg-white">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-black text-base uppercase tracking-wide">
                Наявність на складах
            </h3>
            @if($hasAny)
                <span class="text-xs font-bold text-green-700 uppercase">є в наявності</span>
            @else
                <span class="text-xs font-bold text-red-700 uppercase">немає в наявності</span>
            @endif
        </div>

        <ul class="space-y-2">
            @foreach($rows as $row)
                <li class="flex items-center justify-between gap-3 py-1 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center gap-2 min-w-0">
                        @if($row['available'] > 0)
                            <span class="inline-block w-2.5 h-2.5 bg-green-500 shrink-0" aria-hidden="true"></span>
                        @else
                            <span class="inline-block w-2.5 h-2.5 bg-red-500 shrink-0" aria-hidden="true"></span>
                        @endif
                        <div class="min-w-0">
                            <div class="font-bold text-sm truncate">
                                {{ $row['name'] }}
                                @if($row['city'])
                                    <span class="font-normal text-gray-500">— {{ $row['city'] }}</span>
                                @endif
                            </div>
                            @if($row['pickup'])
                                <div class="text-xs text-gray-500 mt-0.5">самовивіз доступний</div>
                            @endif
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        @if($row['available'] === 0)
                            <span class="text-xs font-bold text-red-700 uppercase">немає</span>
                        @elseif($row['low'])
                            <span class="text-xs font-bold text-orange-700 uppercase">залишилось {{ $row['available'] }} шт.</span>
                        @else
                            <span class="text-xs font-bold text-green-700 uppercase">в наявності</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
