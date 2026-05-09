<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . $title }}</title>
        <meta name="description" content="{{ __('general.orders_meta') }}">
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

                <!-- Status Filter -->
                <div class="brutal-content-card">
                    <div class="d-flex flex-wrap gap-2">
                        <button wire:click="$set('statusFilter', '')"
                                class="{{ $statusFilter === '' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_all') }}
                        </button>
                        <button wire:click="$set('statusFilter', 'pending')"
                                class="{{ $statusFilter === 'pending' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_pending') }}
                        </button>
                        <button wire:click="$set('statusFilter', 'processing')"
                                class="{{ $statusFilter === 'processing' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_processing') }}
                        </button>
                        <button wire:click="$set('statusFilter', 'shipped')"
                                class="{{ $statusFilter === 'shipped' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_shipped') }}
                        </button>
                        <button wire:click="$set('statusFilter', 'delivered')"
                                class="{{ $statusFilter === 'delivered' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_delivered') }}
                        </button>
                        <button wire:click="$set('statusFilter', 'cancelled')"
                                class="{{ $statusFilter === 'cancelled' ? 'brutal-btn-black' : 'brutal-btn-outline' }}"
                                style="padding: 8px 16px; font-size: 14px;">
                            {{ __('general.order_status_cancelled') }}
                        </button>
                    </div>
                </div>

                <div wire:loading class="text-center py-3">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                @if($orders->count() > 0)
                    <div wire:loading.remove>
                        @foreach($orders as $order)
                        <div class="brutal-order-card" wire:key="order-{{ $order->id }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="fw-bold mb-1">{{ __('general.order_number_prefix', ['id' => $order->id]) }}</h4>
                                    <p class="text-muted mb-0">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="brutal-order-status status-{{ $order->status ?? 'new' }}">
                                        @switch($order->status)
                                            @case('processing') {{ __('general.order_status_processing') }} @break
                                            @case('shipped') {{ __('general.order_status_shipped') }} @break
                                            @case('delivered') {{ __('general.order_status_delivered') }} @break
                                            @case('cancelled') {{ __('general.order_status_cancelled') }} @break
                                            @case('pending') {{ __('general.order_status_pending') }} @break
                                            @default {{ __('general.order_status_new') }}
                                        @endswitch
                                    </span>
                                    <p class="fs-4 fw-bold mt-2 mb-0">{{ formatPrice((float) $order->total) }}</p>
                                </div>
                            </div>

                            @if($order->orderProducts && $order->orderProducts->count() > 0)
                            <div style="border-top: 2px solid #e0e0e0; padding-top: 16px;">
                                @foreach($order->orderProducts->take(3) as $item)
                                <div class="d-flex gap-3 align-items-center mb-2">
                                    <div class="brutal-product-thumb" style="width: 50px; height: 50px;">
                                        @if($item->image)
                                            <img src="{{ $item->getImageUrl() }}" alt="{{ $item->title }}"
                                                 style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        @else
                                            <i class="fa fa-box text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-bold">{{ $item->title }}</span>
                                        <span class="text-muted ms-2">x{{ $item->quantity }}</span>
                                    </div>
                                    <span class="fw-bold">{{ formatPrice((float) $item->price) }}</span>
                                </div>
                                @endforeach
                                @if($order->orderProducts->count() > 3)
                                    <p class="text-muted fw-bold mt-1 mb-0">
                                        {{ __('general.order_more_items', ['count' => $order->orderProducts->count() - 3]) }}
                                    </p>
                                @endif
                            </div>
                            @endif

                            <div class="d-flex gap-2 mt-3" style="border-top: 2px solid #e0e0e0; padding-top: 16px;">
                                <a href="{{ locale_route('orders-show', $order->id) }}" wire:navigate class="brutal-btn-outline" style="padding: 8px 16px; font-size: 14px;">
                                    {{ __('general.order_details') }}
                                </a>
                                <button wire:click="reorder({{ $order->id }})"
                                        wire:loading.attr="disabled"
                                        class="brutal-btn-outline" style="padding: 8px 16px; font-size: 14px;">
                                    <span wire:loading.remove wire:target="reorder({{ $order->id }})">{{ __('general.order_repeat') }}</span>
                                    <span wire:loading wire:target="reorder({{ $order->id }})">...</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="brutal-empty-state" wire:loading.remove>
                        <div class="brutal-empty-state-icon">&#x1F4E6;</div>
                        <div class="brutal-empty-state-text">
                            @if($statusFilter)
                                {{ __('general.orders_not_found_status') }}
                            @else
                                {{ __('general.orders_empty') }}
                            @endif
                        </div>
                        @if($statusFilter)
                            <button wire:click="$set('statusFilter', '')" class="brutal-btn-black mt-3">
                                {{ __('general.orders_show_all') }}
                            </button>
                        @else
                            <a href="{{ locale_route('home') }}" wire:navigate class="brutal-btn-black mt-3">
                                {{ __('general.go_to_shopping') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
