<div class="flex items-center gap-1">
    @foreach($currencies as $code => $config)
    <button wire:click="switchCurrency('{{ $code }}')"
        class="text-xs font-bold px-2 py-1 {{ $currency === $code ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }} transition-colors"
        aria-label="{{ $config['name'] }}"
        aria-pressed="{{ $currency === $code ? 'true' : 'false' }}">
        {{ $config['symbol'] }}
    </button>
    @endforeach
</div>
