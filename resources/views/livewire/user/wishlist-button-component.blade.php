<button wire:click="toggle"
        wire:loading.attr="disabled"
        type="button"
        title="{{ $isInWishlist ? 'Видалити зі списку бажань' : 'Додати до списку бажань' }}"
        style="background: none; border: 2px solid black; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; {{ $isInWishlist ? 'background: black; color: white;' : 'background: white; color: black;' }}"
        aria-label="{{ $isInWishlist ? 'Видалити зі списку бажань' : 'Додати до списку бажань' }}">
    <span wire:loading.remove wire:target="toggle">
        @if($isInWishlist)
            <i class="fa fa-heart"></i>
        @else
            <i class="fa-regular fa-heart"></i>
        @endif
    </span>
    <span wire:loading wire:target="toggle">
        <i class="fa fa-spinner fa-spin"></i>
    </span>
</button>
