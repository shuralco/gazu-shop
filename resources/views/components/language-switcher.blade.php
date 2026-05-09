<div class="flex items-center gap-0.5">
    @foreach(config('app.available_locales', ['uk', 'en']) as $loc)
    <a wire:navigate href="{{ switch_locale_url($loc) }}"
       class="text-xs font-bold px-1.5 py-0.5 rounded transition-colors {{ app()->getLocale() === $loc ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}"
       aria-label="{{ $loc === 'uk' ? 'Українська' : 'English' }}">
        {{ strtoupper($loc) }}
    </a>
    @endforeach
</div>
