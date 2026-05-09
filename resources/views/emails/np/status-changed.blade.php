<x-mail::message>
# {{ __("emails.np.title.{$template}") }}

{{ __('emails.np.greeting') }}{{ $order->first_name ? ', ' . $order->first_name : '' }}!

{{ __("emails.np.body.{$template}") }}

<x-mail::panel>
**{{ __('emails.np.order_label') }}:** #{{ $order->id }}<br>
**{{ __('emails.np.np_status_label') }}:** {{ $shipment->np_status ?? __('emails.np.awaiting') }}<br>
@if($shipment->ttn)
**{{ __('emails.np.ttn_label') }}:** {{ $shipment->ttn }}<br>
@endif
@if($shipment->estimated_delivery_date)
**{{ __('emails.np.eta_label') }}:** {{ $shipment->estimated_delivery_date }}<br>
@endif
@if($shipment->recipient_warehouse_name)
**{{ __('emails.np.warehouse_label') }}:** {{ $shipment->recipient_warehouse_name }}<br>
@endif
</x-mail::panel>

@if($trackingUrl)
<x-mail::button :url="$trackingUrl">
{{ __('emails.np.track_button') }}
</x-mail::button>
@endif

{{ __('emails.np.questions') }} [{{ \App\Models\DisplaySetting::get('header_email', 'admin@simpleshop.com') }}](mailto:{{ \App\Models\DisplaySetting::get('header_email', 'admin@simpleshop.com') }}).

{{ __('emails.np.regards') }},<br>
{{ \App\Models\DisplaySetting::get('site_name', 'SimpleShop') }}
</x-mail::message>
