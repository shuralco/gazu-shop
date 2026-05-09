<div>

    @section('metatags')

        <title>{{ shopName() . ' :: ' . ($title ?? 'Page Title') }}</title>
        <meta name="description" content="{{ $desc ?? '' }}">

    @endsection

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="breadcrumbs">
                    <ul>
                        <li><a wire:navigate href="{{ locale_route('home') }}">Home</a></li>
                        <li><a wire:navigate href="{{ locale_route('account') }}">Account</a></li>
                        <li><span>Change account</span></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="container">


        <div class="row">

            <div class="col-lg-4 mb-3">
                <div class="cart-summary p-3 sidebar">
                    <h5 class="section-title"><span>Links</span></h5>
                    @include('incs.account-links')
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                <div class="cart-content p-3 h-100 bg-white">
                    <h5 class="section-title"><span>Change account</span></h5>

                    <form wire:submit="save">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label required">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" placeholder="Name" wire:model="name">
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" placeholder="Email" wire:model="email">
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Аватар</label>
                            @if(auth()->user()->avatar)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                         class="rounded-circle" 
                                         style="width: 80px; height: 80px; object-fit: cover;" 
                                         alt="Поточний аватар">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                   id="avatar" wire:model="avatar" accept="image/*">
                            @error('avatar')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                            <div wire:loading wire:target="avatar" class="text-muted small">
                                Завантаження...
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Поточний пароль</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" placeholder="Введіть поточний пароль для зміни" 
                                   wire:model="current_password">
                            @error('current_password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Новий пароль</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                                   placeholder="Залишіть порожнім, якщо не хочете змінювати" wire:model="password">
                            @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-warning">
                                Save
                                <div wire:loading wire:target="save">
                                    <div class="spinner-grow spinner-grow-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>


