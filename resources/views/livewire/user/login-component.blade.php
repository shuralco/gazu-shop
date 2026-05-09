<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.login_title') }}</title>
        <meta name="description" content="{{ __('general.meta_login') }}">
    @endsection

    <style>
        * {
            border-radius: 0 !important;
        }
        
        .brutal-container {
            max-width: 1200px;
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
        
        .brutal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .brutal-grid {
                grid-template-columns: 1fr;
            }
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
        
        .brutal-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .brutal-link {
            color: black;
            font-weight: 600;
            text-decoration: underline;
        }
        
        .brutal-link:hover {
            text-decoration: none;
        }
    </style>
        
        <!-- Main Content -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8">
        <div class="brutal-grid">
            <!-- Login Form -->
            <div class="brutal-card">
                <h2 class="brutal-title">{{ __('general.login_title') }}</h2>
                
                @if (session('success'))
                    <div class="brutal-success">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Social Login -->
                <div style="margin-bottom: 24px;">
                    <button class="brutal-btn-social">
                        <span style="font-size: 20px;">🔵</span>
                        {{ __('general.login_via_google') }}
                    </button>
                    <button class="brutal-btn-social">
                        <span style="font-size: 20px;">📘</span>
                        {{ __('general.login_via_facebook') }}
                    </button>
                    <button class="brutal-btn-social">
                        <span style="font-size: 20px;">🍎</span>
                        {{ __('general.login_via_apple') }}
                    </button>
                </div>
                
                <div class="brutal-divider">
                    <div class="brutal-divider-line"></div>
                    <span class="brutal-divider-text">{{ __('general.or') }}</span>
                    <div class="brutal-divider-line"></div>
                </div>
                
                <!-- Login Form -->
                <form wire:submit="login">
                    @csrf
                    
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
                        <label for="password" class="brutal-label">{{ __('general.password_label') }}</label>
                        <input type="password" 
                               class="brutal-input @error('password') error @enderror" 
                               id="password" 
                               placeholder="••••••••" 
                               wire:model="password">
                        @error('password')
                            <div class="brutal-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="brutal-flex">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" class="brutal-checkbox" name="remember">
                            <span style="font-weight: 600;">{{ __('general.remember_me') }}</span>
                        </label>
                        <a href="#" class="brutal-link">{{ __('general.forgot_password') }}</a>
                    </div>
                    
                    <button type="submit" class="brutal-btn-black">
                        {{ __('general.sign_in_btn') }}
                        <div wire:loading wire:target="login" style="display: inline-block; margin-left: 8px;">
                            ⏳
                        </div>
                    </button>
                    
                    <div style="margin-top: 16px;">
                        <button type="button" class="brutal-btn-outline" style="width: 100%;">
                            {{ __('general.continue_as_guest') }}
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Registration Benefits -->
            <div class="brutal-card">
                <h2 class="brutal-title">{{ __('general.register_title') }}</h2>

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

                <p style="font-weight: 600; margin-bottom: 24px; text-align: center;">
                    {{ __('general.no_account_yet') }}
                </p>

                <a href="{{ locale_route('register') }}" class="brutal-btn-black" style="text-align: center; display: block; text-decoration: none;">
                    {{ __('general.register_btn') }}
                </a>
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
</div>