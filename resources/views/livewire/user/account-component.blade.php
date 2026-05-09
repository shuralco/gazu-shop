<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.personal_cabinet') }}</title>
        <meta name="description" content="{{ __('general.personal_cabinet') }}">
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

                <!-- Statistics -->
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">{{ __('general.statistics') }}</h2>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="brutal-stat-box">
                                <div class="brutal-stat-number">{{ $ordersCount }}</div>
                                <div class="brutal-stat-label">{{ __('general.orders_count') }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="brutal-stat-box">
                                <div class="brutal-stat-number">{{ formatPrice((float) $totalSpent) }}</div>
                                <div class="brutal-stat-label">{{ __('general.spent_total') }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="brutal-stat-box">
                                <div class="brutal-stat-number">{{ $user->loyalty_points }}</div>
                                <div class="brutal-stat-label">{{ __('general.bonuses_label') }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="brutal-stat-box">
                                <div class="brutal-stat-number">{{ $tier?->display_name ?? 'Bronze' }}</div>
                                <div class="brutal-stat-label">{{ __('general.level_label') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Tier Progress -->
                @if($nextTier)
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">{{ __('general.loyalty_progress') }}</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">{{ $tier?->display_name ?? 'Bronze' }}</span>
                        <span class="fw-bold">{{ $nextTier->display_name }}</span>
                    </div>
                    <div class="brutal-progress-bar mb-2">
                        <div class="brutal-progress-fill" style="width: {{ $tierProgress }}%"></div>
                    </div>
                    <p class="text-muted mb-0">
                        {{ __('general.to_next_level', ['percent' => number_format($tierProgress, 1)]) }}
                    </p>
                    <a href="{{ locale_route('loyalty') }}" wire:navigate class="brutal-btn-outline mt-3">
                        {{ __('general.more_about_bonuses') }}
                    </a>
                </div>
                @endif

                <!-- Personal Info -->
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">{{ __('general.basic_info') }}</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="brutal-label">{{ __('general.name_short') }}</label>
                            <input type="text" class="brutal-input" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="brutal-label">{{ __('general.email_short') }}</label>
                            <input type="email" class="brutal-input" value="{{ $user->email }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="brutal-label">{{ __('general.phone_account') }}</label>
                            <input type="tel" class="brutal-input" value="{{ $user->phone ?? '' }}" readonly placeholder="{{ __('general.not_specified') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="brutal-label">{{ __('general.birthdate_label') }}</label>
                            <input type="text" class="brutal-input" value="{{ $user->birthdate?->format('d.m.Y') ?? '' }}" readonly placeholder="{{ __('general.not_specified') }}">
                        </div>
                    </div>
                    <a href="{{ locale_route('settings') }}" wire:navigate class="brutal-btn-black">
                        {{ __('general.edit_profile') }}
                    </a>
                </div>

                <!-- Recent Orders -->
                @if($recentOrders->count() > 0)
                <div class="brutal-content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="brutal-subtitle mb-0">{{ __('general.recent_orders') }}</h2>
                        <a href="{{ locale_route('orders') }}" wire:navigate class="brutal-btn-outline">{{ __('general.all_orders') }}</a>
                    </div>

                    @foreach($recentOrders as $order)
                    <div class="brutal-order-card">
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
                                        @default {{ __('general.order_status_pending') }}
                                    @endswitch
                                </span>
                                <p class="fs-4 fw-bold mt-2 mb-0">{{ formatPrice((float) $order->total) }}</p>
                            </div>
                        </div>

                        @if($order->orderProducts && $order->orderProducts->count() > 0)
                        <div class="d-flex gap-3 align-items-center">
                            <div class="brutal-product-thumb">
                                <i class="fa fa-box fa-2x text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-bold mb-1">
                                    {{ __('general.items_of_count', ['count' => $order->orderProducts->count()]) }}
                                </p>
                                <p class="text-muted mb-0">
                                    {{ $order->orderProducts->first()->title ?? __('general.description') }}
                                    @if($order->orderProducts->count() > 1)
                                        {{ __('general.and_more_items', ['count' => $order->orderProducts->count() - 1]) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ locale_route('orders-show', $order->id) }}" wire:navigate class="brutal-btn-outline">{{ __('general.order_details') }}</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="brutal-content-card">
                    <div class="brutal-empty-state">
                        <div class="brutal-empty-state-icon">&#x1F4E6;</div>
                        <div class="brutal-empty-state-text">{{ __('general.no_orders_yet') }}</div>
                        <a href="{{ locale_route('home') }}" wire:navigate class="brutal-btn-black mt-4">
                            {{ __('general.go_to_shopping') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>