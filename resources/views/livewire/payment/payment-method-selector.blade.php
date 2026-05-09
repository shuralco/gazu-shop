<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        {{ __('general.select_payment_for_order', ['id' => $order->id]) }}
                    </h4>
                </div>

                <div class="card-body">
                    @if($error)
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ $error }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div>
                                <h5 class="mb-0">{{ __('general.amount_to_pay') }}</h5>
                                <small class="text-muted">{{ __('general.including_delivery') }}</small>
                            </div>
                            <div class="text-end">
                                <h4 class="text-primary mb-0">{{ formatPrice($order->total) }}</h4>
                            </div>
                        </div>
                    </div>

                    @if($order->hasSuccessfulPayment())
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ __('general.order_already_paid') }}
                        </div>
                        <a href="{{ locale_route('orders') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('general.back_to_orders') }}
                        </a>
                    @else
                        <div class="payment-methods">
                            <h5 class="mb-3">{{ __('general.available_payment_methods') }}</h5>

                            <div class="row g-3">
                                @foreach($availableGateways as $key => $gateway)
                                    <div class="col-md-6">
                                        <div class="payment-method-card border rounded p-3 cursor-pointer {{ $selectedGateway === $key ? 'border-primary bg-light' : '' }}"
                                             wire:click="selectGateway('{{ $key }}')">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="payment_gateway"
                                                       id="gateway_{{ $key }}"
                                                       value="{{ $key }}"
                                                       wire:model="selectedGateway">
                                                <label class="form-check-label w-100" for="gateway_{{ $key }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <strong>{{ $gateway['name'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $gateway['description'] }}</small>
                                                        </div>
                                                        @if(isset($gateway['icon']))
                                                            <div class="ms-3">
                                                                @if(str_starts_with($gateway['icon'], '/'))
                                                                    <img src="{{ $gateway['icon'] }}" alt="{{ $gateway['name'] }}" style="height: 30px;">
                                                                @else
                                                                    <i class="fas fa-{{ $gateway['icon'] }} fa-2x text-primary"></i>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if(isset($gateway['fee']) && $gateway['fee'] > 0)
                                                        <div class="mt-2">
                                                            <span class="badge bg-warning text-dark">
                                                                {{ __('general.fee_label', ['amount' => formatPrice($gateway['fee'])]) }}
                                                            </span>
                                                        </div>
                                                    @endif

                                                    @if(isset($gateway['processing_time']))
                                                        <div class="mt-1">
                                                            <small class="text-info">
                                                                <i class="fas fa-clock me-1"></i>
                                                                {{ $gateway['processing_time'] }}
                                                            </small>
                                                        </div>
                                                    @endif

                                                    @if(isset($gateway['features']) && count($gateway['features']) > 0)
                                                        <div class="mt-2">
                                                            @foreach($gateway['features'] as $feature)
                                                                <span class="badge bg-secondary me-1">
                                                                    @switch($feature)
                                                                        @case('refund')
                                                                            <i class="fas fa-undo me-1"></i> {{ __('general.feature_refund') }}
                                                                            @break
                                                                        @case('partial_refund')
                                                                            <i class="fas fa-percentage me-1"></i> {{ __('general.feature_partial_refund') }}
                                                                            @break
                                                                        @case('recurring')
                                                                            <i class="fas fa-sync me-1"></i> {{ __('general.feature_recurring') }}
                                                                            @break
                                                                        @default
                                                                            {{ $feature }}
                                                                    @endswitch
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            @if($selectedGateway)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('general.you_selected', ['name' => $availableGateways[$selectedGateway]['name']]) }}
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ locale_route('orders') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        {{ __('general.back_to_orders_short') }}
                                    </a>

                                    <button wire:click="processPayment"
                                            wire:loading.attr="disabled"
                                            class="btn btn-primary btn-lg">
                                        <span wire:loading.remove>
                                            <i class="fas fa-lock me-2"></i>
                                            {{ __('general.pay_amount', ['amount' => formatPrice($order->total)]) }}
                                        </span>
                                        <span wire:loading>
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            {{ __('general.processing_payment') }}
                                        </span>
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ __('general.please_select_payment') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if(!$order->hasSuccessfulPayment())
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-shield-alt me-2 text-success"></i>
                            {{ __('general.payment_security_title') }}
                        </h6>
                        <p class="card-text small text-muted mb-0">
                            {{ __('general.payment_security_text') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .payment-method-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .payment-method-card:hover {
        border-color: #007bff !important;
        box-shadow: 0 0 10px rgba(0,123,255,0.2);
    }

    .payment-method-card.border-primary {
        box-shadow: 0 0 15px rgba(0,123,255,0.3);
    }
</style>
@endpush

@push('scripts')
<script>
    window.addEventListener('submit-payment-form', event => {
        console.log('Payment form event received:', event.detail);

        const formData = event.detail.formData;
        const formAction = event.detail.formAction;

        if (!formData || !formAction) {
            console.error('Missing form data or action');
            return;
        }

        console.log('Creating form with action:', formAction);
        console.log('Form data:', formData);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = formAction;

        for (const [key, value] of Object.entries(formData)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        console.log('Submitting payment form...');

        document.body.appendChild(form);
        form.submit();
    });
</script>
@endpush