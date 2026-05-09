@php
    $s = $gazuSettings ?? [];
    $about = $s['gazu_footer_about'] ?? 'Інтернет-магазин автозапчастин. Понад 50 000 артикулів, доставка по Україні, гарантія на кожну деталь.';
    $columns = $s['gazu_footer_columns'] ?? [
        ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов', 'Салон']],
        ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
        ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
    ];
    $payments = $s['gazu_footer_payments'] ?? 'Visa, Mastercard, Apple Pay, Google Pay, Нова Пошта';
    $phone = $s['gazu_phone'] ?? '0 800 75 10 24';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $social = [
        'FB' => $s['gazu_social_facebook'] ?? null,
        'IG' => $s['gazu_social_instagram'] ?? null,
        'TG' => $s['gazu_social_telegram'] ?? null,
        'YT' => $s['gazu_social_youtube'] ?? null,
    ];
@endphp
<footer class="bg-[var(--gazu-ink)] text-[#CDD3DC] mt-16">
    <div class="gazu-container py-14 grid gap-10" style="grid-template-columns: 1.4fr 1fr 1fr 1fr 1.2fr;">
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
                <div class="gazu-display text-sm font-semibold text-white mb-3.5">{{ $col['title'] ?? '' }}</div>
                <ul class="list-none p-0 m-0 flex flex-col gap-2.5">
                    @foreach((array) ($col['items'] ?? []) as $i)
                        <li><a href="#" class="text-[13px] text-[#9DA5B2] no-underline hover:text-white">{{ $i }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <div class="gazu-display text-sm font-semibold text-white mb-3.5">Зворотний звʼязок</div>
            @if($phone)
                <div class="gazu-display text-[22px] text-white mb-1">{{ $phone }}</div>
            @endif
            @if($hours)
                <div class="text-xs text-[#9DA5B2] mb-4">{{ $hours }}, безкоштовно</div>
            @endif
            <button type="button" class="w-full py-3 bg-[var(--gazu-blue)] text-white rounded-lg text-sm font-medium cursor-pointer hover:bg-[var(--gazu-blue-600)]">
                Замовити дзвінок
            </button>
        </div>
    </div>
    <div class="border-t border-[#1A2740] gazu-container py-5 flex items-center gap-6 text-xs text-[#5A6573] flex-wrap">
        <span>© {{ date('Y') }} GAZU. Всі права захищені.</span>
        <span class="flex-1"></span>
        @foreach(array_filter(array_map('trim', explode(',', $payments))) as $pay)
            <span>{{ $pay }}</span>
        @endforeach
    </div>
</footer>
