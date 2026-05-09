@extends('components.layouts.app')

@section('title', '404 - ' . __('general.page_not_found'))

@section('content')
@php
    $errorTitle = $errorTitle ?? __('general.page_not_found');
    $errorSubtitle = $errorSubtitle ?? __('general.page_not_found_text');
    $errorPhone = $errorPhone ?? shopPhone();
    $errorEmail = $errorEmail ?? shopEmail();
    $categories = $categories ?? \App\Models\Category::whereNull('parent_id')->where('is_active', true)->withCount('products')->take(4)->get();
    $recommendedProducts = $recommendedProducts ?? \App\Models\Product::where('is_hit', true)->where('is_active', true)->take(4)->get();
@endphp
<div>
    <style>
        .error-illustration {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }
        
        .error-illustration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 8px solid black;
            background: white;
        }
        
        .error-illustration::after {
            content: '×';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            font-weight: 900;
            line-height: 1;
        }
        
        .floating-square {
            position: absolute;
            background: black;
            animation: float 3s ease-in-out infinite;
        }
        
        .floating-square:nth-child(1) {
            width: 20px;
            height: 20px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-square:nth-child(2) {
            width: 16px;
            height: 16px;
            top: 30%;
            right: 15%;
            animation-delay: 1s;
        }
        
        .floating-square:nth-child(3) {
            width: 24px;
            height: 24px;
            bottom: 25%;
            left: 20%;
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .product-image {
            background: linear-gradient(135deg, #f0f0f0 25%, transparent 25%), 
                        linear-gradient(225deg, #f0f0f0 25%, transparent 25%), 
                        linear-gradient(45deg, #f0f0f0 25%, transparent 25%), 
                        linear-gradient(315deg, #f0f0f0 25%, white 25%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
    </style>

    <!-- Breadcrumbs -->
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-4 pt-32 md:pt-40">
        <nav class="flex items-center gap-2 text-sm font-medium">
            <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
            <span class="text-black font-black">/</span>
            <span class="font-black text-black uppercase">{{ __('general.error_404') }}</span>
        </nav>
    </div>

    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8">
        <!-- Error Section -->
        <div class="text-center mb-16">
            <div class="relative mb-12">
                <div class="error-illustration mx-auto mb-8">
                    <div class="floating-square"></div>
                    <div class="floating-square"></div>
                    <div class="floating-square"></div>
                </div>
            </div>
            
            <h1 class="text-8xl lg:text-9xl font-black mb-4">404</h1>
            <h2 class="text-2xl lg:text-4xl font-black mb-6">{{ $errorTitle }}</h2>
            <div class="text-lg mb-8 max-w-2xl mx-auto font-medium">
                {{ $errorSubtitle }}
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="{{ locale_route('home') }}" class="btn-black text-lg px-8 py-4">{{ __('general.back_to_home') }}</a>
                <a href="{{ locale_route('home') }}#categories" class="btn-white text-lg px-8 py-4">{{ __('general.go_to_catalog') }}</a>
            </div>
        </div>

        <!-- Search Section -->
        <section class="mb-16">
            <div class="max-w-2xl mx-auto text-center">
                <h3 class="text-2xl font-black mb-6">{{ __('general.try_search') }}</h3>
                <livewire:search.search-component />
            </div>
        </section>

        <!-- Popular Categories -->
        <section class="mb-16">
            <h3 class="text-2xl lg:text-4xl font-black mb-8 text-center">{{ __('general.popular_categories') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($categories->take(4) as $category)
                    <a wire:navigate href="{{ locale_url($category->getLocalizedSlug()) }}" class="border-2 border-black p-6 text-center hover:bg-black hover:text-white transition-colors">
                        <div class="text-3xl font-black mb-3">{{ $category->icon ?? '📦' }}</div>
                        <div class="text-lg font-black">{{ strtoupper($category->title) }}</div>
                        <div class="text-sm font-medium">{{ $category->products_count ?? 0 }} {{ __('general.products_count_label') }}</div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Recommended Products -->
        <section class="mb-16">
            <h3 class="text-2xl lg:text-4xl font-black mb-8 text-center">{{ __('general.you_may_like') }}</h3>
            <div class="product-grid">
                @foreach($recommendedProducts->take(4) as $product)
                    <div>
                        @include('incs.brutal-product-card')
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Possible Reasons -->
        <section class="mb-16">
            <div class="max-w-4xl mx-auto">
                <h3 class="text-2xl font-black mb-6 text-center">{{ __('general.possible_error_reasons') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border-2 border-black p-6">
                        <div class="text-lg font-black mb-3">🔗 {{ __('general.error_wrong_link') }}</div>
                        <div class="font-medium">{{ __('general.error_wrong_link_desc') }}</div>
                    </div>
                    <div class="border-2 border-black p-6">
                        <div class="text-lg font-black mb-3">📦 {{ __('general.error_product_removed') }}</div>
                        <div class="font-medium">{{ __('general.error_product_removed_desc') }}</div>
                    </div>
                    <div class="border-2 border-black p-6">
                        <div class="text-lg font-black mb-3">🔧 {{ __('general.error_maintenance') }}</div>
                        <div class="font-medium">{{ __('general.error_maintenance_desc') }}</div>
                    </div>
                    <div class="border-2 border-black p-6">
                        <div class="text-lg font-black mb-3">📱 {{ __('general.error_typo') }}</div>
                        <div class="font-medium">{{ __('general.error_typo_desc') }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Support Contact -->
        <section class="text-center border-4 border-black p-12">
            <h3 class="text-2xl lg:text-4xl font-black mb-6">{{ __('general.need_help') }}</h3>
            <div class="text-lg mb-8 max-w-2xl mx-auto font-medium">{{ __('general.support_description') }}</div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="text-center">
                    <div class="text-3xl font-black mb-3">📞</div>
                    <div class="text-lg font-black mb-2">{{ __('general.support_phone_label') }}</div>
                    <div class="font-medium">{{ $errorPhone }}</div>
                    <div class="text-sm font-medium">{{ __('general.support_phone_note') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black mb-3">💬</div>
                    <div class="text-lg font-black mb-2">{{ __('general.support_live_chat_label') }}</div>
                    <div class="font-medium">simpleshop.ua</div>
                    <div class="text-sm font-medium">{{ __('general.support_live_chat_note') }}</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black mb-3">✉️</div>
                    <div class="text-lg font-black mb-2">{{ __('general.support_email_label') }}</div>
                    <div class="font-medium">{{ $errorEmail }}</div>
                    <div class="text-sm font-medium">{{ __('general.support_email_note') }}</div>
                </div>
            </div>
            
            <button class="btn-black text-lg px-8 py-4">{{ __('general.contact_support') }}</button>
        </section>
    </div>
</div>
@endsection