@extends('gazu.layout')
@section('title', 'Гараж — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], ['Кабінет', route('gazu.account')], 'Гараж']"/>

    @if(session('flash_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm">
            <strong>Виправте помилки:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="gazu-grid-account mt-3">
        @include('gazu.partials.account-sidebar', ['active' => 'garage', 'user' => $user])

        <div x-data="{ openAdd: false, editing: null }">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div>
                    <h2 class="gazu-display text-3xl font-semibold m-0">Гараж</h2>
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1">{{ $cars->count() }} {{ $cars->count() === 1 ? 'авто' : 'авто' }} · збережіть авто і отримуйте підбір запчастин у 1 клік</p>
                </div>
                <button type="button" @click="openAdd = true" class="gazu-btn-primary">
                    <x-gazu.icon name="plus" size="16"/> Додати авто
                </button>
            </div>

            {{-- Cars grid --}}
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($cars as $car)
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 {{ $car->is_primary ? 'border-[var(--gazu-blue)]' : '' }}">
                        @if($car->is_primary)
                            <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">⭐ Основне</div>
                        @endif
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-14 h-14 bg-[var(--gazu-mist)] rounded-md flex items-center justify-center text-[var(--gazu-blue)]">
                                <x-gazu.icon name="car" size="28"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="gazu-display text-lg font-semibold text-[var(--gazu-ink)]">{{ $car->make }} {{ $car->model }}</div>
                                <div class="text-sm text-[var(--gazu-graphite)]">
                                    @if($car->year){{ $car->year }} рік@endif
                                    @if($car->engine) · {{ $car->engine }}@endif
                                </div>
                                @if($car->body_type)
                                    <div class="text-xs text-[var(--gazu-graphite)] mt-0.5">{{ $car->body_type }}</div>
                                @endif
                            </div>
                            <button type="button" @click='editing = {{ json_encode($car) }}'
                                    class="bg-transparent border-0 text-[var(--gazu-graphite)] cursor-pointer p-1 hover:text-[var(--gazu-ink)]">
                                <x-gazu.icon name="edit" size="16"/>
                            </button>
                        </div>

                        @if($car->vin || $car->plate || $car->color)
                            <div class="grid grid-cols-{{ count(array_filter([$car->vin, $car->plate, $car->color])) }} gap-3 text-xs mb-3">
                                @if($car->vin)
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">VIN</div>
                                        <div class="gazu-mono text-[var(--gazu-ink)]">{{ $car->vin }}</div>
                                    </div>
                                @endif
                                @if($car->plate)
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">Номер</div>
                                        <div class="gazu-mono text-[var(--gazu-ink)]">{{ $car->plate }}</div>
                                    </div>
                                @endif
                                @if($car->color)
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">Колір</div>
                                        <div class="text-[var(--gazu-ink)]">{{ $car->color }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex gap-2 flex-wrap">
                            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary flex-1 text-xs py-2 no-underline">Запчастини для авто</a>
                            @if(! $car->is_primary)
                                <form action="{{ route('gazu.garage.primary', ['car' => $car->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="Зробити основним" class="gazu-btn-outline text-xs py-2 px-3">⭐</button>
                                </form>
                            @endif
                            <form action="{{ route('gazu.garage.destroy', ['car' => $car->id]) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Видалити {{ addslashes($car->make.' '.$car->model) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Видалити" class="gazu-btn-outline text-xs py-2 px-3 hover:text-[var(--gazu-danger)]">
                                    <x-gazu.icon name="trash" size="14"/>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Add card --}}
                <button type="button" @click="openAdd = true"
                        class="bg-[var(--gazu-mist)] border-2 border-dashed border-[var(--gazu-line-2)] rounded-lg p-5 flex flex-col items-center justify-center text-center min-h-[280px] cursor-pointer hover:border-[var(--gazu-blue)] hover:bg-[var(--gazu-paper)]">
                    <div class="w-12 h-12 bg-[var(--gazu-surface)] rounded-full flex items-center justify-center text-[var(--gazu-blue)] mb-3">
                        <x-gazu.icon name="plus" size="24"/>
                    </div>
                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1.5">{{ $cars->isEmpty() ? 'Додайте перше авто' : 'Додати ще одне авто' }}</div>
                    <p class="text-xs text-[var(--gazu-graphite)] max-w-[220px]">VIN, державний номер або вручну вкажіть марку, модель та рік</p>
                </button>
            </div>

            {{-- Add modal --}}
            <div x-show="openAdd" x-cloak x-transition.opacity
                 class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
                 @click.self="openAdd = false">
                <div class="bg-[var(--gazu-surface)] rounded-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="gazu-display text-xl font-semibold m-0">Додати авто</h3>
                        <button type="button" @click="openAdd = false" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                            <x-gazu.icon name="close" size="20"/>
                        </button>
                    </div>
                    <form action="{{ route('gazu.garage.store') }}" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Марка <span class="text-[var(--gazu-danger)]">*</span></span>
                            <input type="text" name="make" value="{{ old('make') }}" required placeholder="Volkswagen"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Модель <span class="text-[var(--gazu-danger)]">*</span></span>
                            <input type="text" name="model" value="{{ old('model') }}" required placeholder="Passat B8"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Рік випуску</span>
                            <input type="number" name="year" value="{{ old('year') }}" min="1950" max="{{ date('Y') + 1 }}" placeholder="2018"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Двигун</span>
                            <input type="text" name="engine" value="{{ old('engine') }}" placeholder="2.0 TDI · CKFC"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Кузов</span>
                            <input type="text" name="body_type" value="{{ old('body_type') }}" placeholder="Універсал"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Колір</span>
                            <input type="text" name="color" value="{{ old('color') }}" placeholder="Сірий"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block col-span-2">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">VIN-код (опціонально)</span>
                            <input type="text" name="vin" value="{{ old('vin') }}" maxlength="30" placeholder="WVWZZZ3CZJE000000"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Держ. номер (опціонально)</span>
                            <input type="text" name="plate" value="{{ old('plate') }}" maxlength="20" placeholder="AA1234BB"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase">
                        </label>
                        <label class="flex items-center gap-2 col-span-2 text-sm">
                            <input type="checkbox" name="is_primary" value="1" class="w-4 h-4">
                            <span>Зробити основним авто (підставлятиметься у фільтр «Ваш автомобіль»)</span>
                        </label>
                        <div class="col-span-2 flex gap-2 mt-2">
                            <button type="submit" class="gazu-btn-primary flex-1">Додати авто</button>
                            <button type="button" @click="openAdd = false" class="gazu-btn-outline">Скасувати</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Edit modal --}}
            <div x-show="editing" x-cloak x-transition.opacity
                 class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
                 @click.self="editing = null">
                <div class="bg-[var(--gazu-surface)] rounded-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="gazu-display text-xl font-semibold m-0">Редагувати авто</h3>
                        <button type="button" @click="editing = null" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                            <x-gazu.icon name="close" size="20"/>
                        </button>
                    </div>
                    <form :action="editing ? `{{ route('gazu.garage') }}/${editing.id}` : ''" method="POST" class="grid grid-cols-2 gap-3">
                        @csrf
                        <template x-if="editing">
                            <div class="contents">
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Марка</span>
                                    <input type="text" name="make" x-model="editing.make" required class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Модель</span>
                                    <input type="text" name="model" x-model="editing.model" required class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Рік</span>
                                    <input type="number" name="year" x-model="editing.year" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Двигун</span>
                                    <input type="text" name="engine" x-model="editing.engine" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Кузов</span>
                                    <input type="text" name="body_type" x-model="editing.body_type" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Колір</span>
                                    <input type="text" name="color" x-model="editing.color" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block col-span-2"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">VIN</span>
                                    <input type="text" name="vin" x-model="editing.vin" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Номер</span>
                                    <input type="text" name="plate" x-model="editing.plate" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase"></label>
                                <div class="col-span-2 flex gap-2 mt-2">
                                    <button type="submit" class="gazu-btn-primary flex-1">Зберегти</button>
                                    <button type="button" @click="editing = null" class="gazu-btn-outline">Скасувати</button>
                                </div>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
