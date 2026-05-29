@php
    $__active = \App\Support\Locales::switchable();
    $__cur = app()->getLocale();
@endphp
@if ($__active)
    <div class="inline-flex items-center gap-1.5" aria-label="Мова сайту">
        @foreach (\App\Support\Locales::labels() as $__code => $__label)
            @php($__flag = \App\Support\Locales::FLAGS[$__code] ?? '')
            @if ($__code === $__cur)
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-white font-semibold"
                      style="background:rgba(255,255,255,.14);font-size:12px;" title="{{ $__label }}">
                    {{ $__flag }} {{ mb_strtoupper($__code) }}
                </span>
            @else
                <a href="{{ url('/locale/'.$__code) }}"
                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded no-underline hover:text-white"
                   style="color:#CDD3DC;font-size:12px;" title="Перейти на {{ $__label }}">
                    {{ $__flag }} {{ mb_strtoupper($__code) }}
                </a>
            @endif
        @endforeach
    </div>
@endif
