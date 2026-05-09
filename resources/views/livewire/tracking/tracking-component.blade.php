<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl md:text-5xl font-black mb-6">📦 {{ __('general.tracking_title') }}</h1>

    <form wire:submit.prevent="track" class="flex gap-2 mb-8">
        <input type="text" wire:model="ttn"
               placeholder="14-значний номер ТТН"
               class="flex-1 border-2 border-black px-4 py-3 font-bold text-lg focus:outline-none focus:ring-2 focus:ring-black"
               minlength="10" maxlength="20" required>
        <button type="submit" class="btn-black px-6 py-3 font-black">
            {{ __('general.tracking_track_button') }}
        </button>
    </form>

    @if($error)
        <div class="border-2 border-red-600 bg-red-50 text-red-700 p-4 font-bold mb-6">
            ⚠️ {{ $error }}
        </div>
    @endif

    @if($statuses)
        <div class="border-4 border-black p-6 mb-6">
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <span class="bg-black text-white px-3 py-1 font-bold uppercase">
                    {{ $statuses['status_code'] ?? '?' }}
                </span>
                <h2 class="text-xl md:text-2xl font-black">{{ $statuses['status'] }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @if($statuses['sender_city'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_from') }}</div>
                    <div class="font-medium">{{ $statuses['sender_city'] }}</div>
                </div>
                @endif

                @if($statuses['recipient_city'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_to') }}</div>
                    <div class="font-medium">{{ $statuses['recipient_city'] }}{{ $statuses['recipient_warehouse'] ? ' · ' . $statuses['recipient_warehouse'] : '' }}</div>
                </div>
                @endif

                @if($statuses['estimated'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_eta') }}</div>
                    <div class="font-medium">{{ $statuses['estimated'] }}</div>
                </div>
                @endif

                @if($statuses['actual_delivery'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_actual_delivery') }}</div>
                    <div class="font-medium text-green-700">{{ $statuses['actual_delivery'] }}</div>
                </div>
                @endif

                @if($statuses['document_weight'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_weight') }}</div>
                    <div class="font-medium">{{ $statuses['document_weight'] }} кг</div>
                </div>
                @endif

                @if($statuses['document_cost'])
                <div>
                    <div class="font-bold text-gray-600">{{ __('general.tracking_cost') }}</div>
                    <div class="font-medium">{{ number_format($statuses['document_cost'], 2) }} ₴</div>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <a href="https://novaposhta.ua/tracking/?cargo_number={{ $ttn }}" target="_blank"
                   class="underline font-bold text-sm">
                    {{ __('general.tracking_view_on_np') }} →
                </a>
            </div>
        </div>

        @if($shipment && $shipment->tracking_history)
            <div class="border-2 border-black p-4">
                <h3 class="font-black text-lg mb-4">{{ __('general.tracking_history') }}</h3>
                <ul class="space-y-3">
                    @foreach(array_reverse($shipment->tracking_history) as $entry)
                        <li class="flex gap-3 items-start">
                            <span class="text-gray-400 mt-1">●</span>
                            <div>
                                <div class="font-bold">{{ $entry['status'] ?? '?' }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $entry['date'] ?? '' }}
                                    @if(! empty($entry['warehouse'])) · {{ $entry['warehouse'] }} @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>
