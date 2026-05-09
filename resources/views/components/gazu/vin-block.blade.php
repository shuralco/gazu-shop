@php
    $s = $gazuSettings ?? [];
    $label = $s['gazu_vin_label'] ?? 'VIN-декодер';
    $title = $s['gazu_vin_title'] ?? 'Точний підбір за VIN-кодом авто.';
    $description = $s['gazu_vin_description'] ?? 'Введіть 17-значний код кузова — система визначить марку, модель, рік, двигун і покаже сумісні запчастини з оригінальних каталогів.';
    $vin = $s['gazu_vin_demo_code'] ?? 'WVWZZZ3CZJE';
    $vin = str_pad(substr($vin, 0, 17), 17);
    $make = $s['gazu_vin_demo_make'] ?? 'Volkswagen';
    $model = $s['gazu_vin_demo_model'] ?? 'Passat B8';
    $year = $s['gazu_vin_demo_year'] ?? '2018';
    $engine = $s['gazu_vin_demo_engine'] ?? '2.0 TDI · CKFC';
@endphp
<section class="gazu-container py-10">
    <div class="bg-[var(--gazu-ink)] rounded-2xl p-12 text-white grid lg:grid-cols-2 gap-10 items-center relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-80 h-80 rounded-full" style="background: rgba(36,83,166,0.18);"></div>
        <div class="absolute right-20 -bottom-16 w-52 h-52 rounded-full" style="background: rgba(54,114,217,0.12);"></div>
        <div class="relative">
            <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-3">{{ $label }}</div>
            <h2 class="gazu-display text-4xl lg:text-5xl font-semibold leading-tight m-0">
                {!! nl2br(e($title)) !!}
            </h2>
            <p class="text-[15px] text-[#9DA5B2] leading-relaxed mt-4 max-w-md">{{ $description }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 text-[var(--gazu-ink)] relative">
            <div class="gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-widest uppercase mb-2">VIN-код</div>
            <div class="flex gap-0 mb-3">
                @php $filledLen = strlen(rtrim($vin)); @endphp
                @for($i = 0; $i < 17; $i++)
                    @php $ch = trim(substr($vin, $i, 1)); $isFilled = $i < $filledLen; @endphp
                    <div class="flex-1 py-3 text-center gazu-mono text-base font-semibold {{ $isFilled ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-muted)] bg-[var(--gazu-paper)]' }} {{ $i < 16 ? 'border-r border-[var(--gazu-line)]' : '' }}">
                        {{ $ch !== '' ? $ch : '·' }}
                    </div>
                @endfor
            </div>
            <div class="grid grid-cols-2 gap-2.5 p-3.5 bg-[var(--gazu-mist)] rounded-lg mb-3.5">
                <div><div class="text-[11px] text-[var(--gazu-graphite)]">Марка</div><div class="font-semibold">{{ $make }}</div></div>
                <div><div class="text-[11px] text-[var(--gazu-graphite)]">Модель</div><div class="font-semibold">{{ $model }}</div></div>
                <div><div class="text-[11px] text-[var(--gazu-graphite)]">Рік</div><div class="font-semibold">{{ $year }}</div></div>
                <div><div class="text-[11px] text-[var(--gazu-graphite)]">Двигун</div><div class="font-semibold gazu-mono">{{ $engine }}</div></div>
            </div>
            <a href="{{ route('gazu.vin') }}" class="w-full py-3.5 bg-[var(--gazu-ink)] text-white border-0 rounded-lg text-sm font-medium cursor-pointer inline-flex items-center justify-center gap-2 no-underline">
                Показати запчастини для цього авто <x-gazu.icon name="arrow-r" size="16"/>
            </a>
        </div>
    </div>
</section>
