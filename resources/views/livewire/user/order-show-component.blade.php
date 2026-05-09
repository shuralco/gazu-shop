<div>

    @section('metatags')

        <title>{{ shopName() . ' :: ' . ($title ?? 'Page Title') }}</title>
        <meta name="description" content="{{ $desc ?? '' }}">

    @endsection

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="breadcrumbs">
                    <ul>
                        <li><a wire:navigate href="{{ locale_route('home') }}">Home</a></li>
                        <li><a wire:navigate href="{{ locale_route('account') }}">Account</a></li>
                        <li><a wire:navigate href="{{ locale_route('orders') }}">Orders</a></li>
                        <li><span>Order</span></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="container position-relative">

        @isset($timeline)
            @if(count($timeline) > 0)
                <div class="border-2 border-black p-4 md:p-6 my-4 md:my-6 max-w-3xl mx-auto bg-white">
                    <h3 class="font-black text-lg md:text-xl mb-4 uppercase">{{ __('general.order_timeline') }}</h3>
                    <ul class="space-y-3">
                        @foreach($timeline as $event)
                            <li class="flex items-start gap-3 {{ $event['done'] ? 'opacity-100' : 'opacity-50' }}">
                                <span class="text-xl md:text-2xl shrink-0">{{ $event['icon'] }}</span>
                                <div class="flex-1">
                                    <div class="font-bold text-sm md:text-base">{{ $event['title'] }}</div>
                                    @if(! empty($event['datetime']))
                                        <div class="text-xs text-gray-500">
                                            @if($event['datetime'] instanceof \Carbon\Carbon || $event['datetime'] instanceof \Illuminate\Support\Carbon)
                                                {{ $event['datetime']->format('d.m.Y H:i') }}
                                            @else
                                                {{ $event['datetime'] }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if($event['done'])
                                    <span class="text-green-600 font-bold">✓</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endisset

        <div class="update-loading" wire:loading>
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="row">

            <div class="col-lg-4 mb-3">
                <div class="cart-summary p-3 sidebar">
                    <h5 class="section-title"><span>Links</span></h5>
                    @include('incs.account-links')
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="cart-content p-3 h-100 bg-white">
                    <h5 class="section-title"><span>Order #{{ $order->id }}</span></h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->orderProducts as $product)
                                <tr wire:key="{{ $product->id }}">
                                    <td><img src="{{ asset($product->image) }}" alt="{{ $product->title }}" style="width: 60px; height: 60px; object-fit: cover;"></td>
                                    <td>
                                        @if($product->product)
                                            <a href="{{ locale_url($product->product->getLocalizedSlug()) }}" wire:navigate>{{ $product->title }}</a>
                                        @else
                                            {{ $product->title }}
                                        @endif
                                    </td>
                                    <td>{{ formatPrice($product->price) }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>{{ formatPrice($product->price * $product->quantity) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th colspan="5" class="text-end">Total: {{ formatPrice($order->total) }}</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($order->note)
                        <p><strong>Note:</strong> {{ $order->note }}</p>
                    @endif

                </div>
            </div>

        </div>
    </div>

</div>



