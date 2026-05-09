<div data-route="checkout">
    @section('metatags')
        <title>{{ shopName() . ' :: ' . ($title ?? 'Page Title') }}</title>
        <meta name="description" content="{{ $desc ?? '' }}">
    @endsection

    <!-- Scroll Progress -->
    <div class="scroll-progress" id="scrollProgress"></div>
    
    <!-- Main Content -->
    <div class="pt-4 md:pt-6">
        
        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-1 md:mb-2">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ __('general.checkout') }}</span>
            </nav>
        </div>
        
        <!-- Page Title -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 pb-8">
            <h1 class="text-4xl md:text-6xl font-black text-black mb-2">{{ __('general.checkout') }}</h1>
        </div>
        
        <!-- Content -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($cart = \App\Helpers\Cart\Cart::getCart())
            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <form wire:submit.prevent="saveOrder">
                        @csrf
                        
                        <!-- Validation Errors -->
                        @if($errors->any())
                            <div class="border-4 border-red-600 bg-red-50 p-4 mb-6">
                                <p class="font-bold text-red-600 mb-2">{{ __('general.checkout_error') }}</p>
                                <ul class="list-disc list-inside text-red-600">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <!-- Contact Information -->
                        <div class="border-4 border-black p-6 mb-6 bg-white">
                            <h2 class="text-2xl font-black mb-6">{{ __('general.contact_info') }}</h2>
                            
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block font-bold mb-2">{{ __('general.first_name_label') }}</label>
                                    <input type="text" class="input-field @error('first_name') error @enderror"
                                           placeholder="{{ __('general.first_name_placeholder') }}" wire:model="first_name">
                                    @error('first_name')
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-bold mb-2">{{ __('general.last_name_label') }}</label>
                                    <input type="text" class="input-field @error('last_name') error @enderror"
                                           placeholder="{{ __('general.last_name_placeholder') }}" wire:model="last_name">
                                    @error('last_name')
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold mb-2">{{ __('general.phone_label') }}</label>
                                    <input type="tel" class="input-field @error('phone') error @enderror" 
                                           placeholder="+38 (0XX) XXX-XX-XX" 
                                           wire:model="phone"
                                           id="phoneInput">
                                    @error('phone')
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-bold mb-2">{{ __('general.email_label') }}</label>
                                    <input type="email" class="input-field @error('email') error @enderror"
                                           placeholder="your@email.com" wire:model="email">
                                    @error('email')
                                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Юридична особа --}}
                            <div class="mt-4 pt-4 border-t-2 border-gray-200">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model.live="isCompany" class="w-5 h-5 border-2 border-black">
                                    <span class="font-bold">Я представник компанії (юр. особа)</span>
                                </label>

                                @if($isCompany)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label class="block font-bold mb-2">Назва ТОВ/ФОП</label>
                                            <input type="text" class="input-field" wire:model="companyName" placeholder="ТОВ «Назва»">
                                        </div>
                                        <div>
                                            <label class="block font-bold mb-2">ЄДРПОУ</label>
                                            <input type="text" class="input-field" wire:model="edrpou" placeholder="12345678" maxlength="10">
                                        </div>
                                        <div>
                                            <label class="block font-bold mb-2">Контактна особа</label>
                                            <input type="text" class="input-field" wire:model="contactPerson" placeholder="ПІБ контактної особи">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Saved Addresses --}}
                        @auth
                        @if(count($savedAddresses) > 0)
                        <div class="border-4 border-black p-6 mb-6 bg-white">
                            <h3 class="font-black text-sm uppercase mb-3">{{ __('general.saved_addresses') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($savedAddresses as $addr)
                                <button type="button" wire:click="selectAddress({{ $addr['id'] }})"
                                    class="text-left p-3 border-2 transition-colors {{ $selectedAddressId === $addr['id'] ? 'border-black bg-gray-100' : 'border-gray-300 hover:border-black' }}">
                                    <div class="font-bold text-sm">{{ $addr['label'] }}</div>
                                    <div class="text-xs">{{ $addr['first_name'] }} {{ $addr['last_name'] }}</div>
                                    <div class="text-xs text-gray-600">{{ $addr['city'] }}</div>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endauth

                        <!-- Delivery Method -->
                        <div class="border-4 border-black p-6 mb-6 bg-white">
                            <h2 class="text-2xl font-black mb-6">{{ __('general.delivery_method') }}</h2>
                            
                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                @foreach($availableProviders as $provider => $providerName)
                                    @php
                                        $icon = match($provider) {
                                            'novaposhta' => '📦',
                                            'ukrposhta' => '📮',
                                            'rozetka' => '🛒',
                                            'pickup' => '🏪',
                                            default => '📦'
                                        };
                                        $description = match($provider) {
                                            'novaposhta' => __('general.shipping_desc_np') . ' • ' . __('general.nova_poshta_price'),
                                            'ukrposhta' => __('general.shipping_desc_ukr') . ' • ' . __('general.ukrposhta_price'),
                                            'rozetka' => __('general.shipping_desc_delivery'),
                                            'pickup' => __('general.shipping_desc_free'),
                                            default => __('general.shipping_desc_delivery')
                                        };
                                        $price = $shippingCalculated && $shippingProvider === $provider ? formatPrice($shippingCost) : (match($provider) {
                                            'novaposhta' => '65 ₴',
                                            'ukrposhta' => '45 ₴',
                                            'rozetka' => '50 ₴',
                                            'pickup' => '0 ₴',
                                            default => '50 ₴'
                                        });
                                    @endphp
                                    <label class="radio-option @if($shippingProvider === $provider) active @endif flex items-center justify-between">
                                        <input type="radio" name="shippingProvider" value="{{ $provider }}" 
                                               wire:model.live="shippingProvider" @if($shippingProvider === $provider) checked @endif>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <span class="text-2xl">{{ $icon }}</span>
                                                <div>
                                                    <p class="font-bold">{{ $providerName }}</p>
                                                    <p class="text-sm">{{ $description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="font-bold">{{ $price }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('shippingProvider')
                                <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                            @enderror

                            {{-- For Nova Poshta: show unified selector with built-in delivery type tabs --}}
                            @if($shippingProvider === 'novaposhta')
                                <div class="mb-6">
                                    <livewire:shipping.nova-poshta-selector :key="'np-selector'" />
                                </div>
                            @endif

                            {{-- For other providers: show method selection --}}
                            @if($shippingProvider && $shippingProvider !== 'novaposhta' && count($availableMethods) > 0)
                                <div class="grid md:grid-cols-2 gap-4 mb-6">
                                    @foreach($availableMethods as $method => $methodName)
                                        <label class="radio-option @if($shippingMethod === $method) active @endif block">
                                            <input type="radio" name="shippingMethod" value="{{ $method }}"
                                                   wire:model.live="shippingMethod" wire:change="handleShippingMethodChange" @if($shippingMethod === $method) checked @endif>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xl">{{ $method === 'warehouse' ? '🏢' : ($method === 'courier' ? '🚚' : ($method === 'pickup_point' ? '🏪' : '📮')) }}</span>
                                                <div>
                                                    <p class="font-bold">{{ $methodName }}</p>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shippingMethod')
                                    <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                                @enderror
                            @endif

                            <!-- Delivery Address Fields -->
                            <div class="space-y-4">

                                {{-- УкрПошта (відділення / курʼєр) --}}
                                @if($shippingProvider === 'ukrposhta')
                                    <livewire:shipping.ukr-poshta-selector :key="'up-selector'" />
                                @endif

                                {{-- Rozetka Delivery - Пункт видачі --}}
                                @if($shippingProvider === 'rozetka' && $shippingMethod === 'pickup_point')
                                    <div>
                                        <label class="block font-bold mb-2">{{ __('general.city_label') }}</label>
                                        <div class="relative">
                                            <input type="text" class="input-field @error('rozetkaCity') error @enderror" 
                                                   placeholder="{{ __('general.city_placeholder') }}" 
                                                   wire:model.live="rozetkaCity" 
                                                   wire:input.debounce.100ms="searchRozetkaCities">
                                            
                                            @if($rozetkaCitiesLoading)
                                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                                    <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                                                </div>
                                            @endif
                                            
                                            @if(count($rozetkaCities) > 0 && !$rozetkaCitiesLoading)
                                                <div class="dropdown-cities">
                                                    @foreach($rozetkaCities as $index => $city)
                                                        <button type="button" class="dropdown-item" 
                                                                wire:click="selectRozetkaCityByIndex({{ $index }})">
                                                            <span class="text-lg">🏙️</span>
                                                            <div>
                                                                <p class="font-medium">{{ $city['name'] }}</p>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error('rozetkaCity')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if($rozetkaCityId)
                                        <div>
                                            <label class="block font-bold mb-2">{{ __('general.pickup_point_label') }}</label>
                                            <div class="relative">
                                                <input type="text" class="input-field @error('rozetkaPickupPoint') error @enderror" 
                                                       placeholder="{{ __('general.pickup_point_placeholder') }}" 
                                                       value="{{ $rozetkaPickupPoint }}" readonly 
                                                       wire:click="loadRozetkaPickupPoints">
                                                
                                                @if($rozetkaPickupPointsLoading)
                                                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                                        <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                                                    </div>
                                                @endif
                                                
                                                @if(count($rozetkaPickupPoints) > 0 && !$rozetkaPickupPointsLoading)
                                                    <div class="dropdown-cities">
                                                        @foreach($rozetkaPickupPoints as $index => $point)
                                                            <button type="button" class="dropdown-item" 
                                                                    wire:click="selectRozetkaPickupPointByIndex({{ $index }})">
                                                                <span class="text-lg">🏢</span>
                                                                <div>
                                                                    <p class="font-medium">{{ $point['name'] }}</p>
                                                                    <p class="text-sm text-gray-600">{{ $point['address'] }}</p>
                                                                </div>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @error('rozetkaPickupPoint')
                                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                @endif

                                {{-- Rozetka Delivery - Кур'єр --}}
                                @if($shippingProvider === 'rozetka' && $shippingMethod === 'courier')
                                    <div>
                                        <label class="block font-bold mb-2">{{ __('general.city_label') }}</label>
                                        <div class="relative">
                                            <input type="text" class="input-field @error('rozetkaCourierCity') error @enderror" 
                                                   placeholder="{{ __('general.city_placeholder') }}" 
                                                   wire:model.live="rozetkaCourierCity" 
                                                   wire:input.debounce.100ms="searchRozetkaCourierCities">
                                            
                                            @if(count($rozetkaCourierCities) > 0)
                                                <div class="dropdown-cities">
                                                    @foreach($rozetkaCourierCities as $index => $city)
                                                        <button type="button" class="dropdown-item" 
                                                                wire:click="selectRozetkaCourierCityByIndex({{ $index }})">
                                                            <span class="text-lg">🏙️</span>
                                                            <div>
                                                                <p class="font-medium">{{ $city['name'] }}</p>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-2">
                                            <label class="block font-bold mb-2">{{ __('general.street_label') }}</label>
                                            <input type="text" class="input-field" placeholder="{{ __('general.street_placeholder') }}" wire:model="rozetkaCourierStreet">
                                        </div>
                                        <div>
                                            <label class="block font-bold mb-2">{{ __('general.building_label') }}</label>
                                            <input type="text" class="input-field" placeholder="15" wire:model="rozetkaCourierBuilding">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block font-bold mb-2">{{ __('general.apartment_label') }}</label>
                                        <input type="text" class="input-field" placeholder="23" wire:model="rozetkaCourierApartment">
                                    </div>
                                @endif

                                {{-- Самовивіз --}}
                                @if($shippingProvider === 'pickup' && $shippingMethod === 'shop')
                                    <div class="p-4 bg-gray-100 border-2 border-black">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">🏪</span>
                                            <div>
                                                <p class="font-bold">{{ __('general.shop_address_label') }}</p>
                                                <p class="text-sm">{{ __('general.shop_address_value') }}</p>
                                                <p class="text-sm">{{ __('general.shop_schedule') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- Автоматичний розрахунок доставки (приховано) --}}
                                
                                {{-- Відображення вартості доставки --}}
                                @if($shippingCalculated && $shippingCost > 0)
                                    <div class="p-4 bg-green-50 border-2 border-green-500">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">✅</span>
                                            <div>
                                                <p class="font-bold">{{ __('general.shipping_cost_label', ['price' => formatPrice($shippingCost)]) }}</p>
                                                <p class="text-sm text-gray-600">{{ $availableProviders[$shippingProvider] ?? '' }} - {{ $availableMethods[$shippingMethod] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="border-4 border-black p-6 mb-6 bg-white">
                            <h2 class="text-2xl font-black mb-6">{{ __('general.payment_method') }}</h2>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($availablePaymentMethods as $code => $method)
                                    <label class="radio-option @if($paymentMethod === $code) active @endif">
                                        <input type="radio" name="payment" value="{{ $code }}" wire:model="paymentMethod">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">
                                                @if($code === 'cash') 💵
                                                @elseif($code === 'privat24') 💳
                                                @elseif($code === 'monobank') 🏦
                                                @elseif($code === 'liqpay') 💳
                                                @else 💰
                                                @endif
                                            </span>
                                            <div>
                                                <p class="font-bold">{{ $method['name'] }}</p>
                                                <p class="text-sm">{{ $method['description'] }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('paymentMethod')
                                <div class="text-red-600 text-sm mt-4">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Comment -->
                        <div class="border-4 border-black p-6 bg-white">
                            <h2 class="text-2xl font-black mb-6">{{ __('general.order_comment') }}</h2>
                            <textarea class="input-field" rows="4" placeholder="{{ __('general.order_comment_placeholder') }}" wire:model="note"></textarea>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="border-4 border-black bg-white sticky top-8">
                        <div class="p-6">
                            <h2 class="text-2xl font-black mb-6">{{ __('general.your_order') }}</h2>
                            
                            <!-- Order Items -->
                            <div class="mb-6">
                                @foreach($cart as $product_id => $product)
                                    <div class="order-item" wire:key="{{ $product_id }}">
                                        <div class="w-20 h-20 bg-gray-100 border-2 border-black flex items-center justify-center flex-shrink-0">
                                            @if($product['image'])
                                                <img src="{{ asset($product['image']) }}" alt="{{ $product['title'] }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-3xl">📦</span>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold">{{ $product['title'] }}</p>
                                            <p class="text-sm text-gray-600">{{ number_format($product['price'], 0, ',', ' ') }} ₴</p>
                                            <!-- Quantity Controls -->
                                            <div class="flex items-center gap-1 mt-2">
                                                <button type="button" class="quantity-btn-sm" wire:click="decreaseQuantity({{ $product_id }})" wire:loading.attr="disabled">−</button>
                                                <input type="number" class="quantity-input-sm" value="{{ $product['quantity'] }}" readonly>
                                                <button type="button" class="quantity-btn-sm" wire:click="increaseQuantity({{ $product_id }})" wire:loading.attr="disabled">+</button>
                                                <button type="button" class="delete-btn-sm ml-2" wire:click="removeFromCart({{ $product_id }})" wire:loading.attr="disabled">×</button>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-black text-lg">{{ number_format($product['price'] * $product['quantity'], 0, ',', ' ') }} ₴</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Promo Code -->
                            @livewire('cart.coupon-component')
                            
                            {{-- Loyalty Points Redemption --}}
                            @auth
                            @if($availablePoints > 0)
                            <div class="border-2 border-black p-4 mt-3">
                                <h3 class="font-black text-xs uppercase mb-2">{{ __('general.bonus_points') }}</h3>
                                <p class="text-xs mb-2">{{ __('general.points_available', ['count' => $availablePoints]) }}</p>
                                <div class="flex items-center gap-2">
                                    <input type="number" wire:model.live.debounce.500ms="redeemPoints" min="0" max="{{ $availablePoints }}"
                                        class="border-2 border-black px-2 py-1 w-24 text-sm font-bold text-center">
                                    <span class="text-xs">= <strong>{{ number_format($loyaltyDiscount, 2) }}</strong> {{ __('general.currency_short') }}</span>
                                </div>
                            </div>
                            @endif
                            @endauth

                            <!-- Order Totals -->
                            <div class="border-t-2 border-black pt-4 space-y-3 mt-6">
                                <div class="flex justify-between">
                                    <span>{{ __('general.items_count', ['count' => count($cart)]) }}</span>
                                    <span class="font-bold">{{ formatPrice($cartTotal) }}</span>
                                </div>
                                @if($this->isFreeShipping())
                                    <div class="flex justify-between text-green-700">
                                        <span class="font-bold">🎁 {{ __('general.delivery_label') }}</span>
                                        <span class="font-black">БЕЗКОШТОВНО</span>
                                    </div>
                                @elseif($shippingCost > 0)
                                    <div class="flex justify-between">
                                        <span>{{ __('general.delivery_label') }}</span>
                                        <span class="font-bold">{{ formatPrice($shippingCost) }}</span>
                                    </div>
                                    @php($threshold = $this->getFreeShippingThreshold())
                                    @if($threshold > 0 && $cartTotal < $threshold)
                                        <div class="text-xs text-gray-600 -mt-2">
                                            Додайте товарів на {{ formatPrice($threshold - $cartTotal) }} для безкоштовної доставки
                                        </div>
                                    @endif
                                @endif
                                @if(count($appliedCoupon) && $discountAmount > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>{{ __('general.discount_label') }}</span>
                                        <span class="font-bold">-{{ formatPrice($discountAmount) }}</span>
                                    </div>
                                @endif
                                @if($loyaltyDiscount > 0)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ __('general.points_discount') }}</span>
                                        <span class="text-green-600 font-bold">-{{ number_format($loyaltyDiscount, 2) }} {{ __('general.currency_short') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-2xl font-black border-t-2 border-black pt-3">
                                    <span>{{ __('general.grand_total') }}</span>
                                    <span>{{ formatPrice($totalWithShipping) }}</span>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <x-ui.button
                                size="lg"
                                class="w-full mt-6"
                                wire:click="saveOrder"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                            >
                                <span wire:loading.remove wire:target="saveOrder">{{ __('general.place_order') }}</span>
                                <span wire:loading wire:target="saveOrder">{{ __('general.processing') }}</span>
                            </x-ui.button>
                            
                            <!-- Terms -->
                            <p class="text-xs text-gray-600 mt-4 text-center">
                                {{ __('general.terms_agreement') }}
                                <a href="#" class="underline">{{ __('general.personal_data_terms') }}</a>
                            </p>
                            
                            <!-- Security Badge -->
                            <div class="mt-6 p-4 bg-gray-100 border-2 border-black">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🔒</span>
                                    <div>
                                        <p class="font-bold">{{ __('general.secure_payment') }}</p>
                                        <p class="text-sm">{{ __('general.ssl_encryption') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-16">
                <div class="border-4 border-black p-8 bg-white max-w-md mx-auto">
                    <div class="text-6xl mb-4">🛒</div>
                    <h2 class="text-2xl font-black mb-4">{{ __('general.cart_empty') }}</h2>
                    <p class="mb-6">{{ __('general.cart_add_items_checkout') }}</p>
                    <a href="{{ locale_route('home') }}" class="btn-black">{{ __('general.go_to_shopping') }}</a>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* General Styles */
* { 
    border-radius: 0 !important; 
    font-family: 'Inter', sans-serif; 
}

body {
    scrollbar-width: thin;
    scrollbar-color: black transparent;
}

::-webkit-scrollbar { width: 12px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: black; border: 2px solid white; }

/* Scroll Progress */
.scroll-progress {
    position: fixed;
    right: 0;
    top: 0;
    width: 4px;
    height: 0%;
    background: black;
    z-index: 1050;
    transition: height 0.1s ease;
}

/* Button Styles */
.btn-black {
    background: black;
    color: white;
    border: 2px solid black;
    padding: 16px 32px;
    font-weight: 700;
    font-size: 18px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-black:hover {
    background: white;
    color: black;
}

.btn-outline {
    background: white;
    color: black;
    border: 2px solid black;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-outline:hover {
    background: black;
    color: white;
}

/* Input Styles */
.input-field {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid black;
    font-size: 16px;
    font-weight: 500;
    background: white;
    transition: all 0.2s ease;
}

.input-field:focus {
    outline: none;
    background: #f9f9f9;
    box-shadow: 4px 4px 0 black;
}

.input-field.error {
    border-color: red;
    background: #fff5f5;
}

/* Radio Options */
.radio-option {
    border: 2px solid black;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.radio-option:hover {
    background: #f9f9f9;
}

.radio-option.active {
    background: black;
    color: white;
}

.radio-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

/* Steps */
.step {
    position: relative;
    padding: 8px 16px;
    font-weight: 600;
}

.step.active {
    background: black;
    color: white;
}

.step.completed {
    background: #e0e0e0;
}

.step-arrow {
    position: absolute;
    right: -20px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 20px solid black;
    border-top: 20px solid transparent;
    border-bottom: 20px solid transparent;
}

.step.completed .step-arrow {
    border-left-color: #e0e0e0;
}

/* Order Items */
.order-item {
    display: flex;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #e0e0e0;
}

.order-item:last-child {
    border-bottom: none;
}

/* Quantity Controls for Sidebar */
.quantity-btn-sm {
    background: white;
    color: black;
    border: 2px solid black;
    width: 24px;
    height: 24px;
    font-weight: 900;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quantity-btn-sm:hover {
    background: black;
    color: white;
}

.quantity-input-sm {
    width: 40px;
    height: 24px;
    border: 2px solid black;
    border-left: none;
    border-right: none;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    background: white;
    color: black;
}

.delete-btn-sm {
    background: white;
    color: black;
    border: 2px solid black;
    width: 24px;
    height: 24px;
    font-weight: 900;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.delete-btn-sm:hover {
    background: black;
    color: white;
}

/* Dropdown Cities */
.dropdown-cities {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid black;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1050;
    margin-top: 2px;
    box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.2);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    transition: background-color 0.2s ease;
    background: white;
    border: none;
    width: 100%;
    text-align: left;
    font-family: inherit;
}

.dropdown-item:hover {
    background: #f9f9f9;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:focus {
    outline: none;
    background: #f0f0f0;
}

/* Mobile Layout */
@media (max-width: 768px) {
    .grid.md\\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .step-arrow {
        display: none;
    }
    
    .step {
        border-right: 2px solid black;
    }
    
    .step:last-child {
        border-right: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Phone mask for Ukrainian numbers
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phoneInput');
    
    if (phoneInput) {
        // Format phone number as user types
        phoneInput.addEventListener('input', function(e) {
            // Prevent infinite loop by checking if we're already processing
            if (e.target.dataset.formatting) {
                return;
            }
            
            e.target.dataset.formatting = 'true';
            
            let value = e.target.value.replace(/\D/g, '');

            // Strip leading 380 / 38 / 0 — keep just the 9-digit subscriber number
            if (value.startsWith('380')) {
                value = value.substring(3);
            } else if (value.startsWith('38')) {
                value = value.substring(2);
            }
            if (value.startsWith('0')) {
                value = value.substring(1);
            }

            // Limit subscriber number to 9 digits, then prepend 380
            value = '380' + value.substring(0, 9);
            
            // Format the number
            let formatted = '';
            if (value.length > 0) {
                formatted = '+' + value.substring(0, 2);
            }
            if (value.length > 2) {
                formatted += ' (' + value.substring(2, 5);
            }
            if (value.length > 5) {
                formatted += ') ' + value.substring(5, 8);
            }
            if (value.length > 8) {
                formatted += '-' + value.substring(8, 10);
            }
            if (value.length > 10) {
                formatted += '-' + value.substring(10, 12);
            }
            
            // Only update if the value actually changed
            if (e.target.value !== formatted) {
                e.target.value = formatted;
            }
            
            // Clear the formatting flag
            delete e.target.dataset.formatting;
        });
        
        // Set initial value if exists
        if (phoneInput.value && !phoneInput.value.startsWith('+')) {
            phoneInput.value = '+38 ' + phoneInput.value;
        }
    }
});

// Scroll Progress
window.addEventListener('scroll', () => {
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPosition = window.scrollY;
    const progress = (scrollPosition / scrollHeight) * 100;
    document.getElementById('scrollProgress').style.height = progress + '%';
});

// Radio Options
function initializeRadioOptions() {
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        // Remove existing event listeners to prevent duplicates
        radio.removeEventListener('change', handleRadioChange);
        radio.addEventListener('change', handleRadioChange);
    });
}

function handleRadioChange() {
    // Update active state for radio group
    const name = this.name;
    document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
        r.closest('.radio-option')?.classList.remove('active');
    });
    this.closest('.radio-option')?.classList.add('active');
}

// Initialize radio options
initializeRadioOptions();

// Form Validation Visual Feedback
document.querySelectorAll('.input-field').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.hasAttribute('required') && !this.value) {
            this.classList.add('error');
        } else {
            this.classList.remove('error');
        }
    });
});

// Livewire hooks for closing dropdowns after selection
document.addEventListener('livewire:updated', function() {
    // Re-initialize radio options after Livewire updates
    initializeRadioOptions();
    
    // Re-apply radio option states after Livewire updates
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        const radioOption = radio.closest('.radio-option');
        if (radioOption) {
            if (radio.checked) {
                radioOption.classList.add('active');
            } else {
                radioOption.classList.remove('active');
            }
        }
    });
});


// Close dropdown on click outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-cities') && !e.target.closest('input')) {
        document.querySelectorAll('.dropdown-cities').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});

// Auto-focus and dropdown management
// Universal event listener for city selection (works with both Livewire v2 and v3)
function handleCitySelected() {
    setTimeout(() => {
        document.querySelectorAll('.dropdown-cities').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }, 100);
}

// Initialize city selection handler for Livewire 3 (current version)
document.addEventListener('livewire:initialized', function() {
    if (window.Livewire) {
        window.addEventListener('citySelected', handleCitySelected);
    }
});

// Fallback for Livewire 2 (if needed)
if (typeof Livewire !== 'undefined' && Livewire.on) {
    document.addEventListener('livewire:load', function() {
        Livewire.on('citySelected', handleCitySelected);
    });
}
</script>
@endpush