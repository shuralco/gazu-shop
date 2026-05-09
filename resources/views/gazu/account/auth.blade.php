@extends('gazu.layout')
@section('title', 'Вхід · реєстрація — GAZU')

@section('content')
<div class="gazu-container py-12">
    @if(session('flash_message'))
        <div class="max-w-4xl mx-auto bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-3 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-7 max-w-4xl mx-auto">
        {{-- Sign in --}}
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-1">Вхід</h2>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Якщо у вас вже є акаунт</p>

            @error('email')
                <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-3 py-2 rounded mb-4 text-xs">{{ $message }}</div>
            @enderror

            <form action="{{ route('gazu.auth.login') }}" method="POST" class="flex flex-col gap-3">
                @csrf
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Пароль</span>
                    <input type="password" name="password" required minlength="4"
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-2 text-xs text-[var(--gazu-graphite)] cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4"> Запамʼятати мене
                    </label>
                    <a href="#" class="text-xs text-[var(--gazu-blue)]">Забули пароль?</a>
                </div>
                <button type="submit" class="gazu-btn-primary mt-2">Увійти</button>
            </form>
        </div>

        {{-- Sign up --}}
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-1">Реєстрація</h2>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Створіть акаунт за 30 секунд</p>

            @if($errors->hasAny(['name', 'email_register', 'phone_register', 'password']))
                <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-3 py-2 rounded mb-4 text-xs">
                    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('gazu.auth.register') }}" method="POST" class="flex flex-col gap-3">
                @csrf
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя</span>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Телефон (необовʼязково)</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+380 67 123 45 67"
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Пароль</span>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                    </label>
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Повторіть</span>
                        <input type="password" name="password_confirmation" required minlength="6"
                               class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                    </label>
                </div>

                @php
                    $bonuses = $gazuSettings['gazu_auth_bonuses'] ?? [
                        'Бонусна програма — кешбек 3% на замовлення',
                        'Збережені адреси та швидке оформлення',
                        'Історія замовлень + сервіс-нагадування',
                    ];
                @endphp
                <ul class="text-xs text-[var(--gazu-graphite)] flex flex-col gap-1 mt-1">
                    @foreach((array) $bonuses as $bonus)
                        <li class="flex gap-2"><span class="text-[var(--gazu-success)]"><x-gazu.icon name="check" size="12"/></span> {{ $bonus }}</li>
                    @endforeach
                </ul>

                <button type="submit" class="gazu-btn-blue mt-2">Створити акаунт →</button>
            </form>
        </div>
    </div>
</div>
@endsection
