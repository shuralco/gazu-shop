@php
    $s = $gazuSettings ?? [];
    $enabled = $s['gazu_seo_enabled'] ?? true;
    $title = trim((string) ($s['gazu_seo_title'] ?? ''));
    $html = trim((string) ($s['gazu_seo_html'] ?? ''));
@endphp
@if($enabled && ($title || $html))
<section class="gazu-container py-12 sm:py-14">
    <div class="max-w-4xl">
        @if($title)
            <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2.5">Про магазин</div>
            <h2 class="gazu-display text-[24px] sm:text-[32px] font-semibold text-[var(--gazu-ink)] leading-tight m-0 mb-5">{{ $title }}</h2>
        @endif
        @if($html)
            <div class="gazu-seo-content gazu-prose text-[var(--gazu-graphite)]">
                {!! $html !!}
            </div>
        @endif
    </div>
</section>
@endif
