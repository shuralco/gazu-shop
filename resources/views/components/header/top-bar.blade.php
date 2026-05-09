@php
    use App\Models\DisplaySetting;
    $config = [
        'show' => DisplaySetting::get('show_top_bar', true),
        'phone' => DisplaySetting::get('header_phone', shopPhone()),
        'email' => DisplaySetting::get('header_email', shopEmail()),
        'working_hours' => DisplaySetting::get('header_working_hours', __('general.working_hours')),
        'promo_text' => DisplaySetting::get('header_promo_text', __('general.free_delivery')),
        'show_social' => DisplaySetting::get('show_social_links', true),
        'facebook_url' => DisplaySetting::get('facebook_url', '#'),
        'instagram_url' => DisplaySetting::get('instagram_url', '#'),
    ];
@endphp

@if($config['show'])
<div class="bg-gray-100 border-b-2 border-black">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        <div class="flex justify-between items-center h-12 text-sm">
            <!-- Left side - contacts -->
            <div class="flex items-center space-x-6">
                <!-- Phone -->
                <div class="flex items-center space-x-2">
                    <span>📞</span>
                    <a href="tel:{{ $config['phone'] }}" class="font-medium text-black hover:underline">{{ $config['phone'] }}</a>
                </div>
                
                <!-- Email (desktop only) -->
                <div class="hidden md:flex items-center space-x-2">
                    <span>📧</span>
                    <a href="mailto:{{ $config['email'] }}" class="font-medium text-black hover:underline">{{ $config['email'] }}</a>
                </div>
                
                <!-- Working hours (large screens only) -->
                <div class="hidden lg:block">
                    <span class="text-xs font-bold">{{ $config['working_hours'] }}</span>
                </div>
            </div>
            
            <!-- Right side - social and promo -->
            <div class="flex items-center space-x-4">
                <!-- Social links -->
                @if($config['show_social'])
                <div class="hidden md:flex space-x-2">
                    @if($config['facebook_url'] && $config['facebook_url'] !== '#')
                    <a href="{{ $config['facebook_url'] }}" target="_blank" rel="noopener" class="w-6 h-6 border border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors text-xs">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    @endif
                    
                    @if($config['instagram_url'] && $config['instagram_url'] !== '#')
                    <a href="{{ $config['instagram_url'] }}" target="_blank" rel="noopener" class="w-6 h-6 border border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors text-xs">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    @endif
                </div>
                @endif
                
                <!-- Language & Currency switchers -->
                <div class="hidden md:flex items-center gap-1">
                    <x-language-switcher />
                    <span class="text-gray-300">|</span>
                    <livewire:currency-switcher-component />
                </div>

                <!-- Promo text -->
                <div class="text-xs font-bold hidden lg:block">{{ $config['promo_text'] }}</div>
            </div>
        </div>
    </div>
</div>
@endif