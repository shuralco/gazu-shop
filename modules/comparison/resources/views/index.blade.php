@extends('gazu.layout')
@section('title', 'Порівняння товарів — GAZU')
@section('description', 'Порівняйте характеристики обраних товарів поруч у зручній таблиці.')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Порівняння']"/>
    <h1 class="gazu-display text-2xl sm:text-3xl md:text-4xl font-semibold m-0 mb-2">Порівняння товарів</h1>

    @if(session('flash_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    @if(empty($products) || (is_object($products) && $products->isEmpty()))
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Список порівняння порожній</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Натискайте «Порівняти» на картках товарів, щоб додати їх сюди.</p>
            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">До каталогу</a>
        </div>
    @else
        <p class="text-sm text-[var(--gazu-graphite)] mb-5">{{ plural_uk_count($products->count(), 'товар', 'товари', 'товарів') }} у порівнянні</p>

        <div class="overflow-x-auto border border-[var(--gazu-line)] rounded-lg bg-white">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-[var(--gazu-line)]">
                        <th class="text-left p-3 align-bottom font-medium text-[var(--gazu-graphite)] w-40">Характеристика</th>
                        @foreach($products as $product)
                            <th class="p-3 align-bottom text-left min-w-[180px]">
                                <a wire:navigate href="{{ route('gazu.product.show', $product->slug ?? $product->id) }}"
                                   class="gazu-display font-semibold no-underline text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]">
                                    {{ $product->title }}
                                </a>
                                <form method="POST" action="{{ route('gazu.comparison.remove') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="text-xs text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger,#c0392b)] underline">
                                        Прибрати
                                    </button>
                                </form>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributes as $row)
                        <tr class="border-b border-[var(--gazu-line)] last:border-0">
                            <td class="p-3 font-medium text-[var(--gazu-graphite)]">{{ $row['name'] }}</td>
                            @foreach($products as $product)
                                <td class="p-3">{{ $row['values'][$product->id] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('gazu.comparison.clear') }}" class="mt-5">
            @csrf
            <button type="submit" class="text-sm text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] underline">
                Очистити порівняння
            </button>
        </form>
    @endif
</div>
@endsection
