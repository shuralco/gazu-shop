@props(['items' => []])
<nav class="flex items-center gap-2 text-sm font-medium mb-4">
    <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold text-gray-600 dark:text-gray-400">{{ __('general.home') }}</a>
    @foreach($items as $item)
    <span class="text-gray-400">/</span>
    @if(isset($item['url']))
    <a wire:navigate href="{{ $item['url'] }}" class="hover:underline font-bold text-gray-600 dark:text-gray-400">{{ mb_strtoupper($item['title']) }}</a>
    @else
    <span class="font-bold text-gray-900 dark:text-white">{{ mb_strtoupper($item['title']) }}</span>
    @endif
    @endforeach
</nav>
