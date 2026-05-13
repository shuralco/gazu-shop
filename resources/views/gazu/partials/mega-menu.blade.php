@php
    // L1 → L2 (групи) → L3 (підкатегорії з лічильниками)
    // У продакшні $megaTree приходить з GazuMenuComposer (Category::with('children.children')).
    // Якщо composer не запустився — рендеримо порожнє меню замість фейкових даних.
    $megaTree = $megaTree ?? [];
    $brands = $brands ?? [];
    $cars = $cars ?? [];
    $active = $activeMega ?? null;
    $cat = collect($megaTree)->firstWhere('id', $active) ?? collect($megaTree)->first();
    $totalCount = collect($megaTree)->sum('count');
@endphp

@if(empty($megaTree))
    {{-- No data — composer didn't run or DB empty. Hide entire mega menu. --}}
    <div></div>
@else

{{-- Popover container (positioned by parent in header.blade.php) --}}
<div class="bg-white rounded-xl overflow-hidden border border-[var(--gazu-line)] relative"
     x-data="{ activeMega: '{{ $megaTree[0]['id'] ?? 'engine' }}' }"
     style="box-shadow: 0 28px 60px -10px rgba(14,27,44,0.35), 0 8px 16px rgba(14,27,44,0.12);">

    {{-- Pointer arrow that visually connects popover to "Каталог" button --}}
    <div class="absolute -top-2 w-4 h-4 bg-white border-l border-t border-[var(--gazu-line)] rotate-45 z-10" style="left: 156px;"></div>

    {{-- Top strip --}}
    <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">Повний каталог</span>
        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)]">· {{ number_format($totalCount, 0, '.', ' ') }} товарів у {{ count($megaTree) }} категоріях</span>
        <span class="flex-1"></span>
        <button type="button" @click="megaOpen = false"
                class="w-7 h-7 border border-[var(--gazu-line)] bg-white rounded inline-flex items-center justify-center cursor-pointer text-[var(--gazu-graphite)]">
            <x-gazu.icon name="close" size="14"/>
        </button>
    </div>

    <div class="grid min-h-[540px]" style="grid-template-columns: 264px 1fr 260px;">

        {{-- L1 — root --}}
        <nav class="border-r border-[var(--gazu-line)] py-3.5 bg-[var(--gazu-paper)]">
            @foreach($megaTree as $c)
                @php $catLink = ! empty($c['slug']) ? url('/'.$c['slug']) : route('gazu.catalog'); @endphp
                <a wire:navigate href="{{ $catLink }}"
                   @mouseenter="activeMega = '{{ $c['id'] }}'"
                   :class="activeMega === '{{ $c['id'] }}' ? 'bg-white text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]'"
                   :style="activeMega === '{{ $c['id'] }}' ? 'border-left:3px solid var(--gazu-blue)' : 'border-left:3px solid transparent'"
                   class="flex items-center gap-3 py-2.5 pr-3.5 pl-5 text-sm no-underline cursor-pointer relative">
                    <x-gazu.cat-icon kind="{{ $c['icon'] ?? $c['id'] }}" size="20"/>
                    <span class="flex-1 leading-tight">{{ $c['label'] }}</span>
                    <span class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-wider">{{ number_format($c['count'], 0, '.', ' ') }}</span>
                    <x-gazu.icon name="chevron" size="12" class="-rotate-90"/>
                </a>
            @endforeach
        </nav>

        {{-- L2+L3 — groups & subcategories per category, switched via Alpine --}}
        <div class="border-r border-[var(--gazu-line)]">
            @foreach($megaTree as $c)
                <div x-show="activeMega === '{{ $c['id'] }}'" x-cloak class="px-7 pt-5 pb-6 h-full">
                    <div class="flex items-center gap-3 mb-4 pb-3.5 border-b border-[var(--gazu-line)]">
                        <x-gazu.cat-icon kind="{{ $c['icon'] ?? $c['id'] }}" size="28"/>
                        <h3 class="gazu-display text-[22px] font-bold text-[var(--gazu-ink)] m-0">{{ $c['label'] }}</h3>
                        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">{{ number_format($c['count'], 0, '.', ' ') }} товарів</span>
                        <a wire:navigate href="{{ ! empty($c['slug']) ? url('/'.$c['slug']) : route('gazu.catalog') }}" class="ml-auto text-[13px] text-[var(--gazu-blue)] no-underline inline-flex items-center gap-1">Усі →</a>
                    </div>
                    <div class="grid gap-x-6 gap-y-5" style="grid-template-columns: repeat({{ min(max(count($c['groups']), 1), 5) }}, 1fr);">
                        @foreach($c['groups'] as $g)
                            <div>
                                @if(!empty($g['title']))
                                    <div class="gazu-display text-sm font-bold text-[var(--gazu-ink)] mb-2.5">{{ $g['title'] }}</div>
                                @else
                                    <div class="mb-2.5" style="height: 21px;"></div>
                                @endif
                                <div class="flex flex-col gap-1.5">
                                    @foreach($g['items'] as $itm)
                                        @php
                                            // items: [name, count] (2-tuple, legacy) or [name, count, slug] (3-tuple, new).
                                            $itmName = $itm[0] ?? '—';
                                            $itmCount = $itm[1] ?? 0;
                                            $itmSlug = $itm[2] ?? '';
                                            $itmHref = $itmSlug ? url('/'.$itmSlug) : route('gazu.catalog');
                                        @endphp
                                        <a wire:navigate href="{{ $itmHref }}" class="flex items-baseline gap-2 text-[13px] text-[var(--gazu-graphite)] no-underline hover:text-[var(--gazu-ink)]">
                                            <span class="flex-1">{{ $itmName }}</span>
                                            <span class="gazu-mono text-[10px] text-[var(--gazu-muted)]">{{ $itmCount }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Col 3 — brands + cars + promo --}}
        <div class="px-5 pt-5 pb-6 flex flex-col gap-4.5" style="gap: 18px;">
            <div>
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-2.5">Топ бренди</div>
                <div class="grid grid-cols-3 gap-1.5">
                    @php
                        $menuBrands = is_array($brands) ? $brands : ($brands instanceof \Illuminate\Support\Enumerable ? $brands->pluck('name')->all() : []);
                    @endphp
                    @foreach(array_slice($menuBrands, 0, 9) as $b)
                        <div class="h-9 border border-[var(--gazu-line)] rounded flex items-center justify-center gazu-display text-[11px] font-semibold text-[var(--gazu-steel)] bg-white cursor-pointer">{{ $b }}</div>
                    @endforeach
                </div>
            </div>

            @if(! empty($cars))
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase font-semibold">🇨🇳 Китайські авто</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        @foreach($cars as [$brand, $model, $years])
                            <a wire:navigate href="{{ route('gazu.catalog', ['q' => $brand.' '.$model]) }}"
                               class="flex items-center gap-2 px-2 py-1.5 rounded no-underline text-[var(--gazu-ink)] text-xs hover:bg-[var(--gazu-mist)]">
                                <span class="flex-1">{{ $brand }} {{ $model }}</span>
                                <span class="gazu-mono text-[10px] text-[var(--gazu-muted)]">{{ $years }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $promoKicker = \App\Models\DisplaySetting::get('gazu_megamenu_promo_kicker', '');
                $promoTitle = \App\Models\DisplaySetting::get('gazu_megamenu_promo_title', '');
            @endphp
            @if($promoKicker || $promoTitle)
                <div class="bg-[var(--gazu-ink)] text-white rounded-lg p-4 flex flex-col gap-2 mt-auto">
                    @if($promoKicker)
                        <div class="gazu-mono text-[9px] text-[var(--gazu-blue)] tracking-widest uppercase">{{ $promoKicker }}</div>
                    @endif
                    @if($promoTitle)
                        <div class="gazu-display text-lg font-bold">{{ $promoTitle }}</div>
                    @endif
                    <a wire:navigate href="{{ route('gazu.catalog', ['promo' => 1]) }}" class="self-start px-2.5 py-1.5 bg-[var(--gazu-blue)] text-white rounded text-xs font-medium no-underline mt-0.5">До акції →</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endif
