{{--
    layout_builder :: zone renderer

    Рендерить усі активні блоки зони (відсортовані). Викликається з
    LayoutBuilderServiceProvider::renderZone() через @hookAction('layout.*').

    $blocks — Collection<App\Models\LayoutBlock>
    $zone   — zone key (home.top / home.bottom / product.sidebar)
--}}
<div class="layout-builder-zone layout-builder-zone--{{ \Illuminate\Support\Str::slug($zone) }}" data-lb-zone="{{ $zone }}">
    @foreach($blocks as $block)
        @php
            $cfg = is_array($block->config) ? $block->config : [];
            $type = $block->type ?: 'html';
        @endphp

        @if($type === 'banner')
            @php
                $img = $cfg['image_url'] ?? null;
                $link = $cfg['link_url'] ?? null;
                $alt = $cfg['alt'] ?? ($block->title ?? '');
            @endphp
            @if($img)
                <div class="lb-block lb-block--banner" style="margin: 16px 0;">
                    <div class="gazu-container">
                        @if($link)
                            <a href="{{ $link }}" class="block no-underline">
                                <img src="{{ $img }}" alt="{{ $alt }}" loading="lazy"
                                     style="width:100%;height:auto;border-radius:10px;display:block;">
                            </a>
                        @else
                            <img src="{{ $img }}" alt="{{ $alt }}" loading="lazy"
                                 style="width:100%;height:auto;border-radius:10px;display:block;">
                        @endif
                    </div>
                </div>
            @endif

        @elseif($type === 'featured')
            @php
                $limit = (int) ($cfg['limit'] ?? 4);
                $limit = max(1, min($limit, 12));
                $source = $cfg['source'] ?? 'new'; // new | promo | latest
                $items = collect();
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                        $q = \App\Models\Product::query()->where('is_active', true);
                        if ($source === 'promo') {
                            $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');
                        } elseif ($source === 'new') {
                            $q->where('is_new', true);
                        }
                        $items = $q->orderByDesc('updated_at')->limit($limit)->get();
                    }
                } catch (\Throwable $e) {
                    $items = collect();
                }
            @endphp
            @if($items->isNotEmpty())
                <x-gazu.featured-row :title="$block->title ?: 'Рекомендовані товари'" :items="$items"/>
            @endif

        @else
            {{-- html / текст --}}
            @if(filled($block->content) || filled($block->title))
                <div class="lb-block lb-block--html" style="margin: 16px 0;">
                    <div class="gazu-container">
                        @if(filled($block->title))
                            <h2 class="gazu-display" style="font-size:22px;font-weight:600;margin:0 0 10px;color:var(--gazu-ink,#111);">{{ $block->title }}</h2>
                        @endif
                        @if(filled($block->content))
                            <div class="lb-html-content">{!! $block->content !!}</div>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    @endforeach
</div>
