<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.register_title') }}</title>
        <meta name="description" content="{{ __('general.meta_register') }}">
    @endsection

    <!-- Main Content -->
    <main style="padding-top: 120px;">
    <style>
        @media (min-width: 768px) {
            main {
                padding-top: 95px !important;
            }
        }
        * {
            border-radius: 0 !important;
        }
        
        .brutal-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 16px;
        }
        
        .brutal-header {
            background: white;
            border-bottom: 4px solid black;
            padding: 20px 0;
            margin-bottom: 40px;
        }
        
        .brutal-logo {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -1px;
            color: black;
            text-decoration: none;
        }
        
        .brutal-btn-outline {
            background: white;
            color: black;
            border: 2px solid black;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .brutal-btn-outline:hover {
            background: black;
            color: white;
            text-decoration: none;
        }
        
        .brutal-card {
            border: 4px solid black;
            background: white;
            padding: 32px;
        }
        
        .brutal-title {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 32px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .brutal-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid black;
            font-size: 16px;
            font-weight: 500;
            background: white;
            transition: all 0.2s ease;
        }
        
        .brutal-input:focus {
            outline: none;
            background: #f9f9f9;
            box-shadow: 4px 4px 0 black;
        }
        
        .brutal-input.error {
            border-color: red;
            background: #fff5f5;
        }
        
        .brutal-label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .brutal-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid black;
            position: relative;
            cursor: pointer;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .brutal-checkbox:checked {
            background: black;
        }
        
        .brutal-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: 700;
            font-size: 14px;
        }
        
        .brutal-btn-black {
            background: black;
            color: white;
            border: 2px solid black;
            padding: 16px 32px;
            font-weight: 700;
            font-size: 18px;
            text-transform: uppercase;
            transition: all 0.2s ease;
            cursor: pointer;
            width: 100%;
        }
        
        .brutal-btn-black:hover {
            background: white;
            color: black;
        }
        
        .brutal-benefits {
            background: black;
            color: white;
            padding: 24px;
            margin-bottom: 32px;
        }
        
        .brutal-benefits h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 16px;
            text-transform: uppercase;
        }
        
        .brutal-benefits ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .brutal-benefits li {
            padding: 8px 0;
        }
        
        .brutal-error {
            color: red;
            font-size: 14px;
            font-weight: 600;
            margin-top: 4px;
        }
        
        .brutal-success {
            background: #00ff00;
            border: 2px solid black;
            padding: 16px;
            font-weight: 700;
            margin-bottom: 24px;
            text-transform: uppercase;
        }
        
        .brutal-form-group {
            margin-bottom: 24px;
        }
        
        .brutal-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        @media (max-width: 640px) {
            .brutal-grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        .brutal-link {
            color: black;
            font-weight: 600;
            text-decoration: underline;
        }
        
        .brutal-link:hover {
            text-decoration: none;
        }
        
        .brutal-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
        }
        
        .brutal-divider-line {
            flex: 1;
            height: 2px;
            background: black;
        }
        
        .brutal-divider-text {
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .brutal-btn-social {
            border: 2px solid black;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: white;
            margin-bottom: 12px;
        }
        
        .brutal-btn-social:hover {
            background: black;
            color: white;
        }
    </style>
        
        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ __('general.register_title') }}</span>
            </nav>
        </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8">
        <div class="max-w-2xl mx-auto">
        <div class="brutal-card">
            <h2 class="brutal-title">{{ __('general.register_title') }}</h2>
            
            @if (session('success'))
                <div class="brutal-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <!-- Benefits -->
            <div class="brutal-benefits">
                <h3>{{ __('general.registration_benefits') }}</h3>
                <ul>
                    <li>✓ {{ __('general.benefit_fast_checkout') }}</li>
                    <li>✓ {{ __('general.benefit_order_history') }}</li>
                    <li>✓ {{ __('general.benefit_personal_discounts') }}</li>
                    <li>✓ {{ __('general.benefit_loyalty_program') }}</li>
                    <li>✓ {{ __('general.benefit_early_sales') }}</li>
                </ul>
            </div>
            
            <!-- Social Registration -->
            <div style="margin-bottom: 24px;">
                <button class="brutal-btn-social">
                    <span style="font-size: 20px;">🔵</span>
                    {{ __('general.register_via_google') }}
                </button>
                <button class="brutal-btn-social">
                    <span style="font-size: 20px;">📘</span>
                    {{ __('general.register_via_facebook') }}
                </button>
                <button class="brutal-btn-social">
                    <span style="font-size: 20px;">🍎</span>
                    {{ __('general.register_via_apple') }}
                </button>
            </div>
            
            <div class="brutal-divider">
                <div class="brutal-divider-line"></div>
                <span class="brutal-divider-text">{{ __('general.or') }}</span>
                <div class="brutal-divider-line"></div>
            </div>
            
            <!-- Registration Form -->
            <form wire:submit="save">
                @csrf
                
                <div class="brutal-grid-2">
                    <div class="brutal-form-group">
                        <label for="first_name" class="brutal-label">{{ __('general.first_name_form') }}</label>
                        <input type="text"
                               class="brutal-input @error('first_name') error @enderror"
                               id="first_name"
                               placeholder="{{ __('general.first_name_form_placeholder') }}"
                               wire:model="first_name">
                        @error('first_name')
                            <div class="brutal-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="brutal-form-group">
                        <label for="last_name" class="brutal-label">{{ __('general.last_name_form') }}</label>
                        <input type="text"
                               class="brutal-input @error('last_name') error @enderror"
                               id="last_name"
                               placeholder="{{ __('general.last_name_form_placeholder') }}"
                               wire:model="last_name">
                        @error('last_name')
                            <div class="brutal-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="brutal-form-group">
                    <label for="name" class="brutal-label">{{ __('general.full_name_label') }}</label>
                    <input type="text"
                           class="brutal-input @error('name') error @enderror"
                           id="name"
                           placeholder="{{ __('general.full_name_placeholder') }}"
                           wire:model="name">
                    @error('name')
                        <div class="brutal-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="brutal-form-group">
                    <label for="email" class="brutal-label">Email *</label>
                    <input type="email" 
                           class="brutal-input @error('email') error @enderror" 
                           id="email" 
                           placeholder="your@email.com" 
                           wire:model="email">
                    @error('email')
                        <div class="brutal-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="brutal-form-group">
                    <label for="phone" class="brutal-label">{{ __('general.phone_form') }}</label>
                    <input type="tel" 
                           class="brutal-input @error('phone') error @enderror" 
                           id="phone" 
                           placeholder="+380" 
                           wire:model="phone">
                    @error('phone')
                        <div class="brutal-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="brutal-form-group">
                    <label for="password" class="brutal-label">{{ __('general.password_form') }}</label>
                    <input type="password"
                           class="brutal-input @error('password') error @enderror"
                           id="password"
                           placeholder="{{ __('general.password_placeholder') }}"
                           wire:model="password">
                    @error('password')
                        <div class="brutal-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="brutal-form-group">
                    <label for="password_confirmation" class="brutal-label">{{ __('general.password_confirm_label') }}</label>
                    <input type="password"
                           class="brutal-input @error('password_confirmation') error @enderror"
                           id="password_confirmation"
                           placeholder="{{ __('general.password_confirm_placeholder') }}"
                           wire:model="password_confirmation">
                    @error('password_confirmation')
                        <div class="brutal-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="brutal-form-group">
                    <label style="display: flex; align-items: flex-start; cursor: pointer;">
                        <input type="checkbox" class="brutal-checkbox" required style="margin-top: 2px;">
                        <span style="font-size: 14px; line-height: 1.5;">
                            {{ __('general.agree_terms') }} <a href="#" class="brutal-link">{{ __('general.terms_link') }}</a>
                            {{ __('general.and') }} <a href="#" class="brutal-link">{{ __('general.privacy_link') }}</a>
                        </span>
                    </label>
                </div>
                
                <div class="brutal-form-group">
                    <label style="display: flex; align-items: flex-start; cursor: pointer;">
                        <input type="checkbox" class="brutal-checkbox" style="margin-top: 2px;">
                        <span style="font-size: 14px; line-height: 1.5;">
                            {{ __('general.subscribe_newsletter') }}
                        </span>
                    </label>
                </div>
                
                <button type="submit" class="brutal-btn-black">
                    {{ __('general.register_btn') }}
                    <div wire:loading wire:target="save" style="display: inline-block; margin-left: 8px;">
                        ⏳
                    </div>
                </button>
                
                <div style="margin-top: 24px; text-align: center;">
                    <p style="font-weight: 600;">{{ __('general.already_have_account') }}</p>
                    <a href="{{ locale_route('login') }}" class="brutal-link">{{ __('general.sign_in_link') }}</a>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="background: black; color: white; padding: 48px 0; margin-top: 80px;">
        <div class="brutal-container">
            <div style="text-align: center;">
                <h3 style="font-size: 24px; font-weight: 800; margin-bottom: 16px;">{{ __('general.need_help') }}</h3>
                <p style="margin-bottom: 24px;">{{ __('general.support_247') }}</p>
                <div style="display: flex; justify-content: center; gap: 48px; flex-wrap: wrap;">
                    <div>
                        <p style="font-weight: 700; margin-bottom: 8px;">📞 {{ __('general.support_phone') }}</p>
                        <p style="font-size: 20px;">{{ shopPhone() }}</p>
                    </div>
                    <div>
                        <p style="font-weight: 700; margin-bottom: 8px;">📧 {{ __('general.support_email') }}</p>
                        <p style="font-size: 20px;">{{ shopEmail() }}</p>
                    </div>
                    <div>
                        <p style="font-weight: 700; margin-bottom: 8px;">💬 {{ __('general.support_live_chat') }}</p>
                        <p style="font-size: 20px;">{{ __('general.support_click_here') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
</div>