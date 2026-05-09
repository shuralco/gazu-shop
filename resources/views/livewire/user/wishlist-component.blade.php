<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . $title }}</title>
        <meta name="description" content="Список бажань">
    @endsection

    @include('livewire.user.partials.brutal-styles')

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                @include('livewire.user.partials.account-sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <h1 class="brutal-title">{{ $title }}</h1>

                <div wire:loading class="text-center py-3">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                @if($products->count() > 0)
                    <div class="row g-3" wire:loading.remove>
                        @foreach($products as $product)
                        <div class="col-12 col-md-6 col-xl-4" wire:key="wishlist-{{ $product->id }}">
                            <div class="brutal-card h-100 d-flex flex-column">
                                <!-- Product Image -->
                                <div style="height: 200px; background: #f5f5f5; border-bottom: 4px solid black;" class="d-flex align-items-center justify-content-center position-relative">
                                    @if($product->image)
                                        <img src="{{ $product->getImage() }}" alt="{{ $product->title }}"
                                             style="max-height: 100%; max-width: 100%; object-fit: contain;" loading="lazy">
                                    @else
                                        <i class="fa fa-image fa-3x text-muted"></i>
                                    @endif

                                    <!-- Remove button -->
                                    <button wire:click="removeFromWishlist({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            wire:confirm="{{ __('general.remove_from_wishlist') }}"
                                            class="position-absolute top-0 end-0 m-2 brutal-btn-danger"
                                            style="padding: 4px 10px; font-size: 14px;"
                                            title="Видалити зі списку бажань">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>

                                <!-- Product Info -->
                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                    <a href="{{ locale_url($product->getLocalizedSlug()) }}" wire:navigate
                                       class="fw-bold text-decoration-none text-black mb-2 d-block"
                                       style="font-size: 16px;">
                                        {{ $product->title }}
                                    </a>

                                    @if($product->brandModel)
                                        <small class="text-muted d-block mb-2">{{ $product->brandModel->name }}</small>
                                    @endif

                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <span class="fw-bold" style="font-size: 22px;">{{ formatPrice((float) $product->price) }}</span>
                                            @if($product->old_price && $product->old_price > $product->price)
                                                <span class="text-muted text-decoration-line-through" style="font-size: 14px;">
                                                    {{ formatPrice((float) $product->old_price) }}
                                                </span>
                                            @endif
                                        </div>

                                        <a href="{{ locale_url($product->getLocalizedSlug()) }}" wire:navigate
                                           class="brutal-btn-black w-100 text-center d-block">
                                            {{ __('general.buy') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="brutal-empty-state" wire:loading.remove>
                        <div class="brutal-empty-state-icon">&#x2764;&#xFE0F;</div>
                        <div class="brutal-empty-state-text">{{ __('general.wishlist_empty') }}</div>
                        <p class="text-muted mt-2">{{ __('general.wishlist_empty_text') }}</p>
                        <a href="{{ locale_route('home') }}" wire:navigate class="brutal-btn-black mt-3">
                            {{ __('general.go_to_shopping') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
