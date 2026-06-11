@extends('gazu.layout')
@section('title', 'Контакти — GAZU')

@section('content')
@php
    $s = $gazuSettings ?? [];
    $phone = $s['gazu_phone'] ?? '0 800 75 10 24';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $tg = $s['gazu_contacts_telegram'] ?? null;
    $viber = $s['gazu_contacts_viber'] ?? null;
    $email = $s['gazu_contacts_email'] ?? 'info@gazu.uno';
    $offices = $s['gazu_contacts_offices'] ?? [];
@endphp
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Контакти']"/>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-7">Контакти</h1>

    <div class="gazu-grid-contacts">
        <div>
            <div class="bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-lg p-6 mb-4">
                <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-2">Гаряча лінія</div>
                <div class="gazu-display text-3xl font-bold mb-1">{{ $phone }}</div>
                <div class="text-sm text-[#9DA5B2]">Безкоштовно по Україні · {{ $hours }}</div>
            </div>

            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 mb-4">
                <h3 class="gazu-display text-base font-semibold m-0 mb-3">Месенджери</h3>
                @foreach([
                    ['phone', 'Telegram', $tg],
                    ['phone', 'Viber', $viber],
                    ['mail', 'Email', $email],
                ] as [$ic, $name, $val])
                    @if(! empty($val))
                        <div class="flex items-center gap-3 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0">
                            <span class="text-[var(--gazu-blue)]"><x-gazu.icon name="{{ $ic }}" size="20"/></span>
                            <div class="flex-1">
                                <div class="text-xs text-[var(--gazu-graphite)]">{{ $name }}</div>
                                <div class="text-sm text-[var(--gazu-ink)] font-medium gazu-mono">{{ $val }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if(! empty($offices))
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                    <h3 class="gazu-display text-base font-semibold m-0 mb-3">{{ count($offices) }} відділень в Україні</h3>
                    @foreach($offices as $off)
                        <div class="flex items-start gap-3 py-2 border-b border-[var(--gazu-line)] last:border-b-0">
                            <x-gazu.icon name="location" size="14" stroke="var(--gazu-blue)" class="mt-0.5"/>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]">{{ $off['city'] ?? '' }}</div>
                                <div class="text-xs text-[var(--gazu-graphite)]">{{ $off['addr'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @php
                // Карта з адмінки: gazu_contacts_map може бути або повним <iframe>
                // embed-кодом Google Maps, або просто URL для src.
                $mapRaw = trim((string) ($s['gazu_contacts_map'] ?? ''));
                $mapIsIframe = \Illuminate\Support\Str::contains($mapRaw, '<iframe');
                $mapIsUrl = $mapRaw !== '' && \Illuminate\Support\Str::startsWith($mapRaw, 'http');
            @endphp
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden mb-5" style="height: 420px;">
                @if($mapIsIframe)
                    <div class="w-full h-full gazu-map-embed">{!! $mapRaw !!}</div>
                @elseif($mapIsUrl)
                    <iframe src="{{ $mapRaw }}" class="w-full h-full" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Карта"></iframe>
                @else
                    <div class="w-full h-full bg-[var(--gazu-mist)] gazu-grid-pattern flex items-center justify-center">
                        <div class="text-center text-[var(--gazu-graphite)]">
                            <x-gazu.icon name="location" size="40" stroke="var(--gazu-blue)"/>
                            <div class="text-sm mt-2">Карта {{ count($offices) }} відділень</div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-6">
                <h3 class="gazu-display text-xl font-semibold m-0 mb-4">Напишіть нам</h3>
                <form class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя</span>
                        <input type="text" name="name" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                        <input type="email" name="email" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block col-span-2">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Тема</span>
                        <input type="text" name="subject" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block col-span-2">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Повідомлення</span>
                        <textarea rows="4" name="message" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></textarea>
                    </label>
                    <button type="submit" class="gazu-btn-primary col-span-2">Надіслати</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
