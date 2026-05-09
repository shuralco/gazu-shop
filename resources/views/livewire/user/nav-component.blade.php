<div>
    @guest
        <a wire:navigate href="{{ locale_route('login') }}" class="text-black text-sm font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">{{ __('general.login') }}</a>
    @endguest

    @auth
        <div class="relative">
            <button id="accountDropdown" class="text-black text-sm font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors flex items-center">
                {{ __('general.account') }}
                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div id="accountMenu" class="absolute right-0 mt-2 w-48 bg-white border-2 border-black z-50 opacity-0 visibility-hidden transform translateY(-10px) transition-all duration-300 pointer-events-none" style="display: none;">
                <a wire:navigate href="{{ locale_route('account') }}" class="block px-4 py-2 text-black font-medium hover:bg-black hover:text-white">{{ __('general.account') }}</a>
                <a href="{{ locale_route('logout') }}" class="block px-4 py-2 text-black font-medium hover:bg-black hover:text-white">{{ __('general.logout') }}</a>
                @if(auth()->user()->is_admin)
                    <a href="/admin" class="block px-4 py-2 text-black font-medium hover:bg-black hover:text-white">Адміністрування</a>
                @endif
            </div>
        </div>
    @endauth
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountDropdown = document.getElementById('accountDropdown');
    const accountMenu = document.getElementById('accountMenu');
    
    if (accountDropdown && accountMenu) {
        accountDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const isVisible = accountMenu.style.display === 'block';
            
            if (isVisible) {
                accountMenu.style.display = 'none';
            } else {
                accountMenu.style.display = 'block';
                accountMenu.style.opacity = '1';
                accountMenu.style.visibility = 'visible';
                accountMenu.style.transform = 'translateY(0)';
                accountMenu.style.pointerEvents = 'all';
            }
        });
        
        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!accountDropdown.contains(e.target) && !accountMenu.contains(e.target)) {
                accountMenu.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
