<div class="brutal-sidebar">
    <div class="brutal-sidebar-header">
        <div class="d-flex align-items-center gap-3">
            <div class="brutal-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <h5 class="fw-bold mb-0">{{ auth()->user()->name }}</h5>
                <small class="text-muted">{{ auth()->user()->email }}</small>
            </div>
        </div>
    </div>

    <nav>
        <a href="{{ locale_route('account') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('account') ? 'active' : '' }}">
            <span>&#x1F464;</span> ОСОБИСТI ДАНI
        </a>
        <a href="{{ locale_route('orders') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('orders') ? 'active' : '' }}">
            <span>&#x1F4E6;</span> МОЇ ЗАМОВЛЕННЯ
        </a>
        <a href="{{ locale_route('wishlist') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('wishlist') ? 'active' : '' }}">
            <span>&#x2764;&#xFE0F;</span> СПИСОК БАЖАНЬ
            @if(($wCount = auth()->user()->wishlistItems()->count()) > 0)
                <span class="ms-auto brutal-badge">{{ $wCount }}</span>
            @endif
        </a>
        <a href="{{ locale_route('addresses') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('addresses') ? 'active' : '' }}">
            <span>&#x1F4CD;</span> АДРЕСНА КНИГА
            @if(($aCount = auth()->user()->addresses()->count()) > 0)
                <span class="ms-auto brutal-badge">{{ $aCount }}</span>
            @endif
        </a>
        <a href="{{ locale_route('loyalty') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('loyalty') ? 'active' : '' }}">
            <span>&#x1F381;</span> БОНУСИ
            @if(auth()->user()->loyalty_points > 0)
                <span class="ms-auto brutal-badge">{{ auth()->user()->loyalty_points }}</span>
            @endif
        </a>
        <a href="{{ locale_route('settings') }}" wire:navigate
           class="brutal-sidebar-item {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span>&#x2699;&#xFE0F;</span> НАЛАШТУВАННЯ
        </a>
        <a href="{{ locale_route('logout') }}"
           class="brutal-sidebar-item">
            <span>&#x1F6AA;</span> ВИХІД
        </a>
    </nav>
</div>
