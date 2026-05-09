<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @section('metatags')
        <x-seo-meta
            :model="$seoModel ?? null"
            :title="$seoTitle ?? $title ?? null"
            :description="$seoDescription ?? null"
            :keywords="$seoKeywords ?? null"
            :canonical="$seoCanonical ?? null"
            :robots="$seoRobots ?? null"
            :language="$seoLanguage ?? app()->getLocale()"
            :pageType="$seoPageType ?? 'website'"
        />
    @show

    {{-- Hreflang tags for multi-language SEO --}}
    @foreach(config('app.available_locales', ['uk', 'en']) as $hrefLocale)
    <link rel="alternate" hreflang="{{ $hrefLocale }}" href="{{ url(switch_locale_url($hrefLocale)) }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url(switch_locale_url(config('app.locale'))) }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    {!! \App\Helpers\ViteHelper::renderAssets() !!}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    @livewireScripts
    <!-- Bootstrap and other libraries that depend on jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" defer></script>
    <!-- Main scripts without defer to ensure proper execution order -->
    <script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>
    <script src="{{ asset('assets/js/brutal.js') }}?v={{ filemtime(public_path('assets/js/brutal.js')) }}"></script>
    <script src="{{ asset('assets/js/header.js') }}?v={{ filemtime(public_path('assets/js/header.js')) }}"></script>
    <script src="{{ asset('assets/js/np-map.js') }}?v={{ filemtime(public_path('assets/js/np-map.js')) }}" defer></script>

    <!-- Mega Menu Admin Assets -->
    <link rel="stylesheet" href="{{ asset('assets/css/mega-menu-admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" defer></script>
    <script src="{{ asset('assets/js/mega-menu-admin.js') }}?v={{ filemtime(public_path('assets/js/mega-menu-admin.js')) }}" defer></script>
    
    @livewireStyles
    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }
        @keyframes nprogress-pulse {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>

    {{-- Layout component styles loaded via resources/css/components.css (imported in app.css) --}}
</head>

<body class="{{ \App\Models\DisplaySetting::get('show_skeleton_loaders', false) ? 'skeletons-enabled' : 'skeletons-disabled' }}">

    <!-- Livewire Navigate Loading Bar -->
    <div x-data="{ loading: false }"
         x-init="
            document.addEventListener('livewire:navigate', () => { loading = true });
            document.addEventListener('livewire:navigated', () => { loading = false });
         "
         x-show="loading"
         x-cloak
         class="fixed top-0 left-0 right-0 z-[99999] h-1 bg-gray-200 overflow-hidden">
        <div class="h-full w-1/2 bg-black" style="animation: nprogress-pulse 1s ease-in-out infinite;"></div>
    </div>

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <!-- Mobile Menu Panel -->
    <div class="mobile-menu-panel" id="mobileMenuPanel">
        <div class="p-4 border-b-4 border-black">
            <div class="flex justify-between items-center">
                <span class="text-2xl font-black text-black">{{ __('general.menu') }}</span>
                <button id="closeMobileMenu" class="text-black">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="overflow-y-auto h-full">
            <!-- Mobile Categories -->
            <div class="p-4 border-b-2 border-gray-200">
                <div class="mb-3">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.categories') }}</span>
                </div>
                @php
                    $mobileCategories = \App\Models\Category::whereNull('parent_id')->with('children')->get();
                @endphp
                @foreach($mobileCategories as $category)
                    <div class="mb-2">
                        @if($category->children->count() > 0)
                            <button class="w-full text-left py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4 flex justify-between items-center mobile-category-toggle"
                                    data-target="mobile-sub-{{ $category->id }}">
                                {{ strtoupper($category->title) }}
                                <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="mobile-sub-{{ $category->id }}" class="mobile-submenu hidden pl-6 pt-2 pb-3 bg-gray-50 -mx-4 px-4">
                                @foreach($category->children as $child)
                                    <a wire:navigate href="{{ locale_url($child->slug) }}" class="block py-2 text-base font-medium text-gray-700 hover:text-black hover:font-bold">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        @else
                            <a wire:navigate href="{{ locale_url($category->getLocalizedSlug()) }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ strtoupper($category->title) }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Mobile Search -->
            <div class="p-4 border-b-2 border-gray-200">
                <div class="mb-2">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.search') }}</span>
                </div>
                <div class="relative">
                    <livewire:search.search-form-component />
                </div>
            </div>
            
            <!-- Mobile Special Links -->
            <div class="p-4 border-b-2 border-gray-200">
                <div class="mb-2">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.special_offers') }}</span>
                </div>
                <a wire:navigate href="{{ locale_route('specials') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.sales') }}</a>
                <a wire:navigate href="{{ locale_route('hits') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.hits') }}</a>
                <a wire:navigate href="{{ locale_route('new') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.new_products') }}</a>
            </div>
            
            <!-- Mobile User Menu -->
            <div class="p-4 border-b-2 border-gray-200">
                <div class="mb-2">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.account') }}</span>
                </div>
                @guest
                    <a wire:navigate href="{{ locale_route('login') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.login') }}</a>
                    <a wire:navigate href="{{ locale_route('register') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.register') }}</a>
                @endguest
                @auth
                    <a wire:navigate href="{{ locale_route('account') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.personal_cabinet') }}</a>
                    <a href="{{ locale_route('logout') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.logout') }}</a>
                    @if(auth()->user()->is_admin)
                        <a href="/admin" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.admin_panel') }}</a>
                    @endif
                @endauth
            </div>
            
            <!-- Mobile Info Pages -->
            <div class="p-4 border-b-2 border-gray-200">
                <div class="mb-3">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.information') }}</span>
                </div>
                <a wire:navigate href="{{ locale_route('brands') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.brands') }}</a>
                <a wire:navigate href="{{ locale_route('specials') }}" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.specials') }}</a>
                <a href="#" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.help') }}</a>
                <a href="#" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.about') }}</a>
                <a href="#" class="block py-3 text-lg font-bold text-black hover:bg-gray-100 -mx-4 px-4">{{ __('general.contacts') }}</a>
            </div>
            
            <!-- Mobile Contact -->
            <div class="p-4">
                <div class="mb-3">
                    <span class="text-sm font-bold text-gray-600 uppercase tracking-wide">{{ __('general.contacts') }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-black text-white flex items-center justify-center text-sm">📞</div>
                        <a href="tel:{{ shopPhone() }}" class="font-bold text-black text-lg">{{ shopPhone() }}</a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-black text-white flex items-center justify-center text-sm">📧</div>
                        <a href="mailto:{{ shopEmail() }}" class="font-medium text-black">{{ shopEmail() }}</a>
                    </div>
                    <div class="text-sm text-gray-600 mt-4">
                        <strong>{{ __('general.work_schedule') }}:</strong><br>
                        {{ __('general.working_hours') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed w-full bg-white" style="z-index: 10000 !important;">
        <!-- Dynamic Top Bar -->
        <x-header.top-bar />
        
        <!-- Dynamic Main Header -->
        <x-header.main-header />
        
        <!-- Dynamic Horizontal Menu -->
        <x-header.horizontal-menu />
    </nav>

    <script>
    (function() {
        function setMainPadding() {
            var nav = document.querySelector('nav.fixed');
            var main = document.getElementById('mainContent');
            if (nav && main) {
                main.style.paddingTop = nav.offsetHeight + 'px';
            }
        }
        setMainPadding();
        window.addEventListener('resize', setMainPadding);
        document.addEventListener('livewire:navigated', setMainPadding);
    })();
    </script>

    <main class="main" id="mainContent">
        @if(isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
        <livewire:cookie-consent-component />
        <livewire:product.comparison-bar-component />
        <livewire:promo-popup-component />
    </main>

    <!-- Footer -->
    <footer class="bg-white py-12 md:py-24 border-t-4 border-black">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
            <div class="grid md:grid-cols-4 gap-8 md:gap-16">
                <div>
                    <h3 class="text-2xl md:text-3xl font-black text-black mb-4 md:mb-8">LITESHOP</h3>
                    <p class="text-base md:text-lg text-black font-medium leading-relaxed">
                        {{ __('general.ecommerce_tagline') }}
                    </p>
                    <div class="flex gap-4 mt-6">
                        <a href="#" class="w-10 h-10 border-2 border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 border-2 border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 border-2 border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-black text-black text-lg md:text-xl mb-4 md:mb-8">{{ __('general.categories') }}</h4>
                    <ul class="space-y-2 md:space-y-4 text-base md:text-lg font-medium">
                        @php
                            $mainCategories = \App\Models\Category::whereNull('parent_id')->limit(6)->get();
                        @endphp
                        @foreach($mainCategories as $category)
                            <li><a wire:navigate href="{{ locale_url($category->getLocalizedSlug()) }}" class="text-black hover:underline">{{ $category->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="font-black text-black text-lg md:text-xl mb-4 md:mb-8">{{ __('general.information') }}</h4>
                    <ul class="space-y-2 md:space-y-4 text-base md:text-lg font-medium">
                        <li><a wire:navigate href="{{ locale_route('home') }}" class="text-black hover:underline">{{ __('general.home') }}</a></li>
                        <li><a wire:navigate href="{{ locale_route('returns') }}" class="text-black hover:underline">{{ __('general.returns') }}</a></li>
                        <li><a wire:navigate href="{{ locale_route('offer') }}" class="text-black hover:underline">{{ __('general.public_offer') }}</a></li>
                        <li><a wire:navigate href="{{ locale_route('privacy') }}" class="text-black hover:underline">{{ __('general.privacy_policy') }}</a></li>
                        <li><a wire:navigate href="{{ locale_route('terms') }}" class="text-black hover:underline">{{ __('general.terms') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-black text-black text-lg md:text-xl mb-4 md:mb-8">{{ __('general.contacts') }}</h4>
                    <ul class="space-y-2 md:space-y-4 text-base md:text-lg font-medium text-black">
                        <li class="flex items-center gap-2">
                            <span>📞</span>
                            <a href="tel:{{ shopPhone() }}">{{ shopPhone() }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>📧</span>
                            <a href="mailto:{{ shopEmail() }}">{{ shopEmail() }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>📍</span>
                            <span>{{ shopSetting('shop_address', 'Київ, вул. Хрещатик, 1') }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>🕐</span>
                            <span>{{ __('general.work_schedule_short') }}</span>
                        </li>
                    </ul>
                    <div class="mt-6">
                        <p class="text-sm font-bold mb-2">{{ __('general.hotline') }}</p>
                        <p class="text-2xl font-black">{{ shopPhone() }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ __('general.free_in_ukraine') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="border-t-4 border-black mt-12 pt-8">
                <div class="flex flex-wrap items-center justify-center gap-6 mb-8">
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">VISA</span>
                    </div>
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">MasterCard</span>
                    </div>
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">Apple Pay</span>
                    </div>
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">Google Pay</span>
                    </div>
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">Приват24</span>
                    </div>
                    <div class="border-2 border-black px-4 py-2">
                        <span class="font-bold">monobank</span>
                    </div>
                </div>
                
                <p class="text-center text-black font-bold text-base md:text-lg">
                    © {{ date('Y') }} LITESHOP. {{ __('general.all_rights') }}. {{ __('general.brutal_minimalism') }}.
                </p>
            </div>
        </div>
    </footer>
</div>

<button id="top">
    <i class="fa-solid fa-angles-up"></i>
</button>

<script>
// Emergency Cart Modal Fix - Direct Implementation
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up cart functions');
    
    // Define cart functions globally
    window.openCartModal = function() {
        console.log('Opening cart modal...');
        
        // Immediately dispatch event to suppress notifications FIRST
        if (window.Livewire) {
            console.log('Dispatching cart-modal-opened event...');
            Livewire.dispatch('cart-modal-opened');
            
            // Small delay to ensure Livewire processes the event
            setTimeout(() => {
                const modal = document.getElementById('cartModal');
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    console.log('Cart modal opened, notifications suppressed');
                } else {
                    console.error('Cart modal element not found!');
                }
            }, 50);
        } else {
            console.error('Livewire not available!');
            // Fallback without Livewire
            const modal = document.getElementById('cartModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        
        // Clear any toastr notifications if present (legacy support)
        if (typeof toastr !== 'undefined') {
            toastr.clear();
        }
    };
    
    window.closeCartModal = function() {
        console.log('Closing cart modal...');
        
        // Dispatch Livewire event to restore notifications
        Livewire.dispatch('cart-modal-closed');
        
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            console.log('Cart modal closed, notifications restored');
        }
    };
    
    // Handle session expiration on mobile devices
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            if (fail && fail.status === 419) {
                // Session expired - reload page automatically
                window.location.reload();
            }
        });
        
    // Notification listener for cart and other Livewire events
        Livewire.on('notify', (data) => {
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message || data[0]?.message || 'Успішно');
            }
        });

        Livewire.on('show-notification', (data) => {
            if (typeof toastr !== 'undefined') {
                const type = data.type || data[0]?.type || 'info';
                const message = data.message || data[0]?.message || '';
                const title = data.title || data[0]?.title || '';
                if (toastr[type]) {
                    toastr[type](message, title);
                } else {
                    toastr.info(message, title);
                }
            }
        });

    // Optimistic cart updates for better UX
        Livewire.on('cart-updated', () => {
            // Immediately update cart count optimistically
            const cartButtons = document.querySelectorAll('[wire\\:loading][wire\\:target*="add2Cart"]');
            cartButtons.forEach(btn => {
                // Remove loading state immediately after dispatch
                setTimeout(() => {
                    btn.classList.remove('opacity-50');
                    btn.removeAttribute('disabled');
                }, 100);
            });
        });
    });

    // Re-initialize functions after Livewire navigation
    document.addEventListener('livewire:navigated', () => {
        console.log('Livewire navigation detected, re-initializing...');
        
        // Re-initialize header functionality
        if (window.headerManager) {
            window.headerManager.reinitialize();
        }
        
        // Re-bind any dynamic elements
        if (typeof initFilterModal === 'function') {
            initFilterModal();
        }
        
        // Ensure cart functions remain available
        if (typeof openCartModal === 'function') {
            window.openCartModal = openCartModal;
        }
        if (typeof closeCartModal === 'function') {
            window.closeCartModal = closeCartModal;
        }
    });
    
    // Navigation functions
    window.goToCart = function() {
        window.location.href = '{{ locale_route("checkout") }}';
        window.closeCartModal();
    };

    window.goToCheckout = function() {
        window.location.href = '{{ locale_route("checkout") }}';
        window.closeCartModal();
    };
    
    // Debug cart button
    const cartButtons = document.querySelectorAll('[onclick*="openCartModal"]');
    console.log('Found cart buttons:', cartButtons.length);
    cartButtons.forEach((btn, index) => {
        console.log(`Cart button ${index}:`, btn);
    });
    
    // Check if modal exists
    const modal = document.getElementById('cartModal');
    console.log('Cart modal element:', modal);
    
    // Emergency click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cart-btn-brutal')) {
            e.preventDefault();
            console.log('Emergency cart button handler triggered');
            window.openCartModal();
        }
    });
    
    // Initialize HeaderManager if not already done
    if (typeof window.headerManager === 'undefined') {
        console.log('Initializing HeaderManager...');
        window.headerManager = new HeaderManager();
    }
});
</script>

@stack('scripts')

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
</script>

</body>

</html>
