<div class="flex items-center gap-1">
    <button
        wire:click="switchLocale('uk')"
        class="text-xs font-bold px-2 py-1 rounded transition-colors {{ $locale === 'uk' ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}"
        aria-label="Українська"
    >UA</button>
    <button
        wire:click="switchLocale('en')"
        class="text-xs font-bold px-2 py-1 rounded transition-colors {{ $locale === 'en' ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}"
        aria-label="English"
    >EN</button>
</div>
