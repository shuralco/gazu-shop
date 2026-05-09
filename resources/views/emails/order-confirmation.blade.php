@extends('emails.layouts.base')

@section('title', __('general.email_order_confirmation_subject', ['id' => $order->id]))

@section('content')
    <!-- Heading -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="text-align: center; padding-bottom: 32px;">
                <div style="display: inline-block; background-color: #000000; color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 2px; padding: 8px 20px; text-transform: uppercase;">
                    {{ __('general.email_order_confirmed') }}
                </div>
                <h2 style="margin: 16px 0 0 0; font-size: 24px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                    {{ __('general.email_order_number', ['id' => $order->id]) }}
                </h2>
            </td>
        </tr>
    </table>

    <!-- Thank you message -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 32px; border-bottom: 2px solid #000000;">
                <p style="margin: 0; font-size: 15px; color: #333333; line-height: 1.6;">
                    {{ __('general.email_order_thank_you', ['name' => $order->first_name ?? $order->name ?? '']) }}
                </p>
            </td>
        </tr>
    </table>

    <!-- Order items -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-top: 24px; padding-bottom: 8px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    {{ __('general.your_order_items') }}
                </h3>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
        <!-- Header row -->
        <tr>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                {{ __('general.email_product') }}
            </td>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; text-align: center; width: 60px;">
                {{ __('general.quantity') }}
            </td>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; text-align: right; width: 100px;">
                {{ __('general.email_price') }}
            </td>
        </tr>
        <!-- Items -->
        @foreach($order->orderProducts as $item)
            <tr>
                <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #333333; font-weight: 600;">
                    {{ $item->title }}
                </td>
                <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #333333; text-align: center;">
                    {{ $item->quantity }}
                </td>
                <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #000000; text-align: right; font-weight: 700;">
                    {{ formatPrice($item->price * $item->quantity) }}
                </td>
            </tr>
        @endforeach
    </table>

    <!-- Totals -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 4px;">
        @if($order->discount_amount && $order->discount_amount > 0)
            <tr>
                <td style="padding: 8px 0; font-size: 14px; color: #666666;">{{ __('general.discount_label') }}</td>
                <td style="padding: 8px 0; font-size: 14px; color: #ef4444; text-align: right; font-weight: 600;">
                    -{{ formatPrice($order->discount_amount) }}
                </td>
            </tr>
        @endif
        @if($order->shipping_cost && $order->shipping_cost > 0)
            <tr>
                <td style="padding: 8px 0; font-size: 14px; color: #666666;">{{ __('general.delivery_label') }}</td>
                <td style="padding: 8px 0; font-size: 14px; color: #333333; text-align: right; font-weight: 600;">
                    {{ formatPrice($order->shipping_cost) }}
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding: 16px 0 0; border-top: 2px solid #000000; font-size: 18px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                {{ __('general.total') }}
            </td>
            <td style="padding: 16px 0 0; border-top: 2px solid #000000; font-size: 20px; font-weight: 900; color: #000000; text-align: right;">
                {{ formatPrice($order->total) }}
            </td>
        </tr>
    </table>

    <!-- Delivery & Payment -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="padding-bottom: 8px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    {{ __('general.delivery_details') }}
                </h3>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fafafa; border: 1px solid #e5e5e5;">
        <tr>
            <td style="padding: 20px;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                    @if($order->shipping_method || $order->shipping_provider)
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; width: 140px; vertical-align: top;">
                                {{ __('general.delivery_method') }}:
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 700;">
                                {{ $order->shipping_provider ?? '' }} {{ $order->shipping_method ?? '' }}
                            </td>
                        </tr>
                    @endif
                    @if($order->shipping_city)
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                {{ __('general.email_city') }}:
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                {{ $order->shipping_city }}
                            </td>
                        </tr>
                    @endif
                    @if($order->shipping_warehouse)
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                {{ __('general.delivery_address_label') }}
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                {{ $order->shipping_warehouse }}
                            </td>
                        </tr>
                    @elseif($order->shipping_address)
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                {{ __('general.delivery_address_label') }}
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                {{ $order->shipping_address }}
                            </td>
                        </tr>
                    @endif
                    @if($order->payment_method)
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                {{ __('general.payment_method_short') }}
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                {{ $order->payment_method }}
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- CTA -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="text-align: center;">
                <a href="{{ url('/cabinet/orders') }}" style="display: inline-block; background-color: #000000; color: #ffffff; font-size: 13px; font-weight: 800; letter-spacing: 2px; text-decoration: none; text-transform: uppercase; padding: 16px 40px;">
                    {{ __('general.email_view_order') }}
                </a>
            </td>
        </tr>
    </table>

    <!-- Help text -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="text-align: center; border-top: 1px solid #e5e5e5; padding-top: 24px;">
                <p style="margin: 0; font-size: 13px; color: #999999; line-height: 1.6;">
                    {{ __('general.email_order_questions') }}
                </p>
            </td>
        </tr>
    </table>
@endsection
