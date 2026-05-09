<div class="space-y-4">
    {{-- Delivery type --}}
    <div class="flex gap-2">
        <button type="button" wire:click="$set('deliveryType', 'branch')"
                class="flex-1 py-3 font-bold border-2 border-black transition-colors {{ $deliveryType === 'branch' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100' }}">
            <span class="text-lg">📮</span> Відділення
        </button>
        <button type="button" wire:click="$set('deliveryType', 'courier')"
                class="flex-1 py-3 font-bold border-2 border-black transition-colors {{ $deliveryType === 'courier' ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100' }}">
            <span class="text-lg">🚗</span> Курʼєр
        </button>
    </div>

    {{-- City search --}}
    <div class="relative" x-data="{ open: true }" @click.outside="open = false">
        <label class="block font-bold mb-1">Місто</label>
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="citySearch"
                   placeholder="Введіть назву міста або поштовий індекс…"
                   class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black"
                   autocomplete="off"
                   @focus="open = true">

            @if($cityLoading)
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                </div>
            @endif
        </div>

        @if(count($citySuggestions) > 0 && mb_strlen($citySearch) >= 2 && !$cityId)
            <div x-show="open"
                 class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-60 overflow-y-auto shadow-lg">
                @foreach($citySuggestions as $index => $city)
                    <button type="button"
                            wire:click="selectCityByIndex({{ $index }})"
                            class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white font-medium transition-colors border-b border-gray-100 last:border-b-0">
                        <span class="text-lg mr-1">🏙️</span>
                        <span class="font-bold">{{ $city['name'] }}</span>
                        @if($city['district'])
                            <span class="text-xs text-gray-500 ml-2">{{ $city['district'] }}</span>
                        @endif
                        @if($city['postcode'])
                            <span class="text-xs text-gray-400 ml-2">{{ $city['postcode'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        @if($cityId)
            <div class="mt-1 text-sm text-green-700 font-bold flex items-center gap-1">
                <span>✓</span> {{ $cityName }}
            </div>
        @endif
    </div>

    {{-- Branch (warehouse) selection --}}
    @if($cityId && $deliveryType === 'branch')
        <div class="relative" x-data="{ open: true }" @click.outside="open = false">
            <div class="flex justify-between items-center mb-1">
                <label class="block font-bold">Відділення
                <div class="flex border-2 border-black text-xs font-bold">
                    <button type="button" wire:click="setBranchTypeFilter('')"
                            class="px-2 py-1 transition-colors {{ $branchTypeFilter === '' ? 'bg-black text-white' : 'bg-white hover:bg-gray-100' }}">
                        Усі
                    </button>
                    <button type="button" wire:click="setBranchTypeFilter('ПВ')"
                            class="px-2 py-1 transition-colors border-l-2 border-black {{ $branchTypeFilter === 'ПВ' ? 'bg-black text-white' : 'bg-white hover:bg-gray-100' }}">
                        ПВ
                    </button>
                    <button type="button" wire:click="setBranchTypeFilter('ВПЗ')"
                            class="px-2 py-1 transition-colors border-l-2 border-black {{ $branchTypeFilter === 'ВПЗ' ? 'bg-black text-white' : 'bg-white hover:bg-gray-100' }}">
                        ВПЗ
                    </button>
                </div>
            </div>

            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="branchSearch"
                       placeholder="Введіть № індексу або частину адреси…"
                       class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black"
                       autocomplete="off"
                       @focus="open = true">

                @if($branchLoading)
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                    </div>
                @endif
            </div>

            @if(count($branchSuggestions) > 0 && !$branchId)
                <div x-show="open" class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-60 overflow-y-auto shadow-lg">
                    @foreach($branchSuggestions as $index => $branch)
                        <button type="button"
                                wire:click="selectBranchByIndex({{ $index }})"
                                class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white transition-colors border-b border-gray-100 last:border-b-0">
                            <span class="text-lg mr-1">📮</span>
                            <span class="font-mono font-bold">{{ $branch['postcode'] }}</span>
                            <span class="text-sm ml-2">{{ \Illuminate\Support\Str::limit($branch['address'], 80) }}</span>
                            @if(($branch['type'] ?? null) === 'ВПЗ')
                                <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">ВПЗ</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            @if($branchId)
                <div class="mt-1 text-sm text-green-700 font-bold flex items-center gap-1">
                    <span>✓</span> {{ $branchName }}
                </div>
            @endif

            @if(!$branchLoading && empty($allBranches) && $cityId)
                <div class="mt-1 text-sm text-orange-600 font-medium">
                    Для цього міста відділень не знайдено
                </div>
            @endif
        </div>
    @endif

    {{-- Courier address --}}
    @if($cityId && $deliveryType === 'courier')
        <div class="space-y-3">
            <div class="relative" x-data="{ open: true }" @click.outside="open = false">
                <label class="block font-bold mb-1">Вулиця</label>
                <input type="text" wire:model.live.debounce.300ms="street"
                       class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black"
                       placeholder="Наприклад: вул. Хрещатик"
                       autocomplete="off"
                       @focus="open = true">
                @if($streetLoading)
                    <div class="absolute right-3 top-10">
                        <div class="animate-spin w-4 h-4 border-2 border-black border-t-transparent rounded-full"></div>
                    </div>
                @endif
                @if(count($streetSuggestions) > 0 && !$streetId)
                    <div x-show="open" class="absolute z-50 w-full bg-white border-2 border-black border-t-0 max-h-60 overflow-y-auto shadow-lg">
                        @foreach($streetSuggestions as $index => $s)
                            <button type="button"
                                    wire:click="selectStreetByIndex({{ $index }})"
                                    class="block w-full text-left px-4 py-2 hover:bg-black hover:text-white transition-colors border-b border-gray-100 last:border-b-0">
                                {{ $s['name'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
                @if($streetId)
                    <div class="mt-1 text-sm text-green-700 font-bold">✓ {{ $street }}</div>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold mb-1">Будинок</label>
                    <input type="text" wire:model.live.debounce.300ms="building"
                           class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                    <label class="block font-bold mb-1">Кв.</label>
                    <input type="text" wire:model.live.debounce.300ms="apartment"
                           class="w-full border-2 border-black px-4 py-3 font-medium focus:outline-none focus:ring-2 focus:ring-black">
                </div>
            </div>
        </div>
    @endif
</div>
