@php
    use App\Models\DisplaySetting;
    $config = [
        'logo_type' => DisplaySetting::get('logo_type', 'text'),
        'logo_text' => DisplaySetting::get('logo_text', 'SIMPLESHOP'),
        'menu_catalog_text' => DisplaySetting::get('menu_catalog_text', __('general.catalog')),
        'menu_brands_text' => DisplaySetting::get('menu_brands_text', __('general.brands')),
        'menu_specials_text' => DisplaySetting::get('menu_specials_text', __('general.specials')),
        'menu_help_text' => DisplaySetting::get('menu_help_text', __('general.help')),
        'catalog_trigger' => DisplaySetting::get('catalog_trigger', 'click'), // click, hover, both
    ];
@endphp

<div class="bg-white relative" data-catalog-trigger="{{ $config['catalog_trigger'] }}">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center h-16 md:h-20">
            <!-- Logo section -->
            <div class="flex items-center">
                <a wire:navigate href="{{ locale_route('home') }}" class="text-2xl md:text-4xl font-black text-black tracking-tight">
                    {{ $config['logo_text'] }}
                </a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-6 desktop-menu">
                <!-- Catalog button with mega menu -->
                <button id="catalogBtn" class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors flex items-center">
                    {{ $config['menu_catalog_text'] }}
                    <svg class="w-4 h-4 ml-1 transition-transform" id="catalogArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- Other menu items -->
                <button class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">
                    {{ $config['menu_brands_text'] }}
                </button>
                
                <a wire:navigate href="{{ locale_route('specials') }}" class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">
                    {{ $config['menu_specials_text'] }}
                </a>
                
                <button class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">
                    {{ $config['menu_help_text'] }}
                </button>
            </div>
            
            <!-- Right side - search, user, cart, mobile menu -->
            <div class="flex items-center space-x-3">
                <!-- Search (desktop only) -->
                <div class="relative hidden md:block">
                    <livewire:search.search-form-component />
                </div>
                
                <!-- User menu (desktop only) -->
                <div class="hidden md:block">
                    <livewire:user.nav-component />
                </div>
                
                <!-- Cart -->
                <div class="relative">
                    <livewire:cart.cart-icon-component />
                    <livewire:cart.cart-modal-component />
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="openMobileMenu" class="lg:hidden">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mega Menu -->
    <x-header.mega-menu />
</div>