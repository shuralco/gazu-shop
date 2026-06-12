@php
    $s = $gazuSettings ?? [];
    $about = $s['gazu_footer_about'] ?? sprintf(
        'Інтернет-магазин автозапчастин. %s, доставка по Україні, гарантія на кожну деталь.',
        $shopStats['products_label'] ?? 'широкий каталог'
    );
    $columns = $s['gazu_footer_columns'] ?? [
        ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов і оптика', 'Аксесуари']],
        ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
        ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
    ];

    // Map common footer labels to actual routes — kills the 'href="#"' dead-link cluster.
    // Категорії ведуть на real /{slug} (root-level catch-all → category page).
    $linkMap = [
        'Двигун'                => url('/engine'),
        'Гальмівна система'     => url('/brakes'),
        'Підвіска'              => url('/suspension'),
        'Електрика'             => url('/electrics'),
        'Трансмісія'            => url('/transmission'),
        'Мастила і рідини'      => url('/fluids'),
        'Мастила'               => url('/fluids'),
        'Кузов і оптика'        => url('/body'),
        'Кузов'                 => url('/body'),
        'Аксесуари'             => url('/accessories'),
        'Салон'                 => url('/accessories'),
        'Доставка та оплата'    => route('gazu.delivery'),
        'Гарантія та повернення'=> route('gazu.warranty'),
        'Питання та відповіді'  => route('gazu.faq'),
        'Бонусна програма'      => route('gazu.loyalty'),
        'Гуртовим клієнтам'     => route('gazu.wholesale'),
        'Про нас'               => route('gazu.about'),
        'Контакти'              => route('gazu.contacts'),
        'Вакансії'              => route('gazu.careers'),
        'Сертифікати'           => route('gazu.certificates'),
        'Публічна оферта'       => route('gazu.offer'),
    ];
    $payments = $s['gazu_footer_payments'] ?? 'Накладений платіж, Нова Пошта, УкрПошта';
    $phone = $s['gazu_phone'] ?? '0 800 750 010';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $social = [
        'FB' => $s['gazu_social_facebook'] ?? null,
        'IG' => $s['gazu_social_instagram'] ?? null,
        'TG' => $s['gazu_social_telegram'] ?? null,
        'YT' => $s['gazu_social_youtube'] ?? null,
    ];
@endphp
<footer class="bg-[var(--gazu-ink)] text-[#CDD3DC] mt-16">
    <div class="gazu-container py-14 grid gap-8 sm:gap-10 gazu-footer-grid">
        <div>
            <x-gazu.logo size="28" color="#fff"/>
            <p class="text-sm leading-relaxed mt-4 text-[#9DA5B2]">{{ $about }}</p>
            <div class="flex gap-2 mt-4">
                @foreach($social as $code => $url)
                    @if(! empty($url) && $url !== '#')
                        <a href="{{ $url }}" target="_blank" rel="nofollow noopener"
                           class="w-9 h-9 rounded-lg border border-[#2A3850] flex items-center justify-center text-[11px] text-[#CDD3DC] no-underline hover:bg-[#2A3850]">{{ $code }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        @foreach((array) $columns as $col)
            <div>
                <div class="gazu-display text-sm font-semibold text-[var(--gazu-on-brand)] mb-3.5">{{ $col['title'] ?? '' }}</div>
                <ul class="list-none p-0 m-0 flex flex-col gap-2.5">
                    @foreach((array) ($col['items'] ?? []) as $i)
                        @php $href = $linkMap[$i] ?? route('gazu.catalog'); @endphp
                        <li><a wire:navigate href="{{ $href }}" class="text-[13px] text-[#9DA5B2] no-underline hover:text-[var(--gazu-on-brand)]">{{ $i }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <div class="gazu-display text-sm font-semibold text-[var(--gazu-on-brand)] mb-3.5">Зворотний звʼязок</div>
            @if($phone)
                <div class="gazu-display text-[22px] text-[var(--gazu-on-brand)] mb-1">{{ $phone }}</div>
            @endif
            @if($hours)
                <div class="text-xs text-[#9DA5B2] mb-4">{{ $hours }}, безкоштовно</div>
            @endif
            <x-gazu.callback-popover variant="button" source="footer" align="right"/>
        </div>
    </div>
    <div class="border-t border-[#1A2740] gazu-container py-5 flex items-center gap-6 text-xs text-[#5A6573] flex-wrap">
        <span>© {{ date('Y') }} {{ $s['gazu_brand_name'] ?? 'GAZU' }}. Всі права захищені.</span>
        <a href="https://lionex.com.ua" target="_blank" rel="nofollow noopener"
           class="inline-flex items-center gap-1.5 text-[#5A6573] no-underline hover:text-[var(--gazu-on-brand)] transition-colors">
            Розроблено
            <img src="{{ asset('lionex-logo.svg') }}" alt="LIONEX" style="height:16px;width:auto;display:inline-block;opacity:.85">
        </a>
        <span class="flex-1"></span>
        @foreach(array_filter(array_map('trim', explode(',', $payments))) as $pay)
            <span>{{ $pay }}</span>
        @endforeach
    </div>
</footer>
