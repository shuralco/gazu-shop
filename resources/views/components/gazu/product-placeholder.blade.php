@props(['label' => null])
{{-- Нейтральна заглушка фото товару (без оманливих демо-фото по типу).
     Показується, коли в товара немає реального завантаженого зображення. --}}
<div {{ $attributes->merge(['class' => 'w-full h-full flex flex-col items-center justify-center gap-2 bg-[var(--gazu-paper)] text-[var(--gazu-line-2)] select-none']) }}>
    <svg width="40%" height="40%" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="max-w-[80px] max-h-[80px]" aria-hidden="true">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <path d="M21 15l-5-5L5 21"/>
    </svg>
    <span class="text-[11px] text-[var(--gazu-graphite)] tracking-wide">Фото готується</span>
</div>
