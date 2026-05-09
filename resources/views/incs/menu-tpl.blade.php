<li @if (isset($item['children'])) class="nav-item dropend" @endif wire:key="{{ $item['id'] }}">
    @if (isset($item['children']))
        <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown"
           data-bs-auto-close="outside">{{ $item['title'] }}</a>
        <ul class="dropdown-menu dropdown-menu-end">
            {!! \App\Helpers\Category\Category::getHtml($item['children']) !!}
        </ul>
    @else
        <a class="dropdown-item" wire:current="active" wire:navigate
           href="{{ locale_url($item['slug']) }}">{{ $item['title'] }}</a>
    @endif
</li>
