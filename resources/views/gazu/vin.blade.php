@extends('gazu.layout')
@section('title', 'Підбір за VIN — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Підбір за VIN']"/>
    @php
        $s = $gazuSettings ?? [];
        $title = $s['gazu_vin_title'] ?? 'Підбір за VIN-кодом';
        $description = $s['gazu_vin_description'] ?? 'Введіть 17-значний VIN-код вашого авто. Система автоматично визначить марку, модель, рік випуску, тип двигуна — і покаже сумісні запчастини з оригінальних каталогів.';
        $steps = $s['gazu_vin_steps'] ?? [
            ['num' => '1', 'title' => 'Знайдіть VIN', 'desc' => 'У техпаспорті, на лобовому склі або у дверному отворі водія'],
            ['num' => '2', 'title' => 'Введіть код', 'desc' => 'Система перевірить його за каталогами 240+ виробників'],
            ['num' => '3', 'title' => 'Отримайте список', 'desc' => 'Тільки сумісні запчастини, без помилок підбору'],
        ];
    @endphp
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">{{ $title }}</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-7 max-w-2xl">{{ $description }}</p>

    <x-gazu.vin-block/>

    <div class="grid md:grid-cols-{{ min(count($steps), 3) }} gap-4 mt-7">
        @foreach($steps as $step)
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="gazu-display text-3xl font-bold text-[var(--gazu-blue)] mb-2">{{ $step['num'] ?? '' }}</div>
                <h3 class="gazu-display text-lg font-semibold m-0 mb-1">{{ $step['title'] ?? '' }}</h3>
                <p class="text-sm text-[var(--gazu-graphite)] m-0">{{ $step['desc'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
