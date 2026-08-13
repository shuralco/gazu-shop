@php
    /**
     * Блок «Чому обирають GAZU» — переваги магазину.
     *
     * Раніше цей текст жив усередині SEO-блоку в самому низу головної, тобто
     * його бачив лише той, хто докрутив до кінця. Тепер це окрема секція одразу
     * під категоріями, а керується так само з адмінки:
     * Налаштування → GAZU візуальні блоки → вкладка «Головна».
     */
    $s = $gazuSettings ?? [];
    $enabled = $s['gazu_why_enabled'] ?? true;
    $title = trim((string) ($s['gazu_why_title'] ?? 'Чому обирають GAZU'));
    $html = trim((string) ($s['gazu_why_html'] ?? ''));
@endphp
@if($enabled && $html !== '')
<section class="gazu-container pt-6 pb-2">
    @if($title !== '')
        <h2 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0 mb-4">{{ $title }}</h2>
    @endif
    {{-- Контент — HTML з візуального редактора. Списки з тексту перетворюємо
         на плитки: власник пише звичайний список, а виглядає як секція. --}}
    <div class="gazu-advantages grid gap-3 sm:gap-3.5 md:grid-cols-2 lg:grid-cols-4">
        {!! $html !!}
    </div>
</section>
<style>
    /* Плитки будуються з того, що ввели в редакторі: кожен <li> — окрема картка.
       Так власник керує текстом списком, не думаючи про верстку. */
    .gazu-advantages ul { display: contents; margin: 0; padding: 0; list-style: none; }
    .gazu-advantages li {
        background: var(--gazu-surface);
        border: 1px solid var(--gazu-line);
        border-radius: 10px;
        padding: 16px 18px;
        font-size: 14px;
        line-height: 1.55;
        color: var(--gazu-graphite);
    }
    .gazu-advantages li strong { display: block; color: var(--gazu-ink); margin-bottom: 4px; font-weight: 600; }
    /* Абзаци поза списком — на всю ширину, як вступ до блоку. */
    .gazu-advantages > p { grid-column: 1 / -1; margin: 0 0 4px; color: var(--gazu-graphite); font-size: 14px; }
</style>
@endif
