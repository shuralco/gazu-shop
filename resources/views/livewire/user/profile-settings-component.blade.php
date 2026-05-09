<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . $title }}</title>
        <meta name="description" content="{{ __('general.settings') }}">
    @endsection

    @include('livewire.user.partials.brutal-styles')

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                @include('livewire.user.partials.account-sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <h1 class="brutal-title">{{ $title }}</h1>

                <form wire:submit="save">
                    <!-- Personal Data -->
                    <div class="brutal-content-card">
                        <h2 class="brutal-subtitle">{{ __('general.personal_info') }}</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.first_name_required') }}</label>
                                <input type="text" class="brutal-input @error('name') border-danger @enderror"
                                       wire:model="name" placeholder="{{ __('general.first_name_placeholder') }}">
                                @error('name')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.email_short') }} *</label>
                                <input type="email" class="brutal-input @error('email') border-danger @enderror"
                                       wire:model="email" placeholder="email@example.com">
                                @error('email')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.phone_account') }}</label>
                                <input type="tel" class="brutal-input @error('phone') border-danger @enderror"
                                       wire:model="phone" placeholder="+380 XX XXX XX XX">
                                @error('phone')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.birthdate_label') }}</label>
                                <input type="date" class="brutal-input @error('birthdate') border-danger @enderror"
                                       wire:model="birthdate">
                                @error('birthdate')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Avatar -->
                    <div class="brutal-content-card">
                        <h2 class="brutal-subtitle">{{ __('general.avatar_label') }}</h2>
                        <div class="d-flex align-items-center gap-4">
                            <div style="width: 80px; height: 80px; border: 4px solid black; background: black; color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; flex-shrink: 0; overflow: hidden;">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                @elseif(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ __('general.avatar_label') }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" class="brutal-input @error('avatar') border-danger @enderror"
                                       wire:model="avatar" accept="image/*">
                                @error('avatar')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                                <div wire:loading wire:target="avatar" class="text-muted fw-bold mt-1">
                                    {{ __('general.uploading') }}
                                </div>
                                <small class="text-muted d-block mt-1">JPG, PNG (max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="brutal-content-card">
                        <h2 class="brutal-subtitle">{{ __('general.notifications_label') }}</h2>
                        <label class="brutal-checkbox-label">
                            <input type="checkbox" class="brutal-checkbox" wire:model="email_orders">
                            {{ __('general.order_notifications') }}
                        </label>
                        <label class="brutal-checkbox-label">
                            <input type="checkbox" class="brutal-checkbox" wire:model="email_promotions">
                            {{ __('general.promotions_offers') }}
                        </label>
                    </div>

                    <!-- Change Password -->
                    <div class="brutal-content-card">
                        <h2 class="brutal-subtitle">{{ __('general.change_password') }}</h2>
                        <p class="text-muted mb-3">{{ __('general.password_leave_empty') }}</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.current_password') }}</label>
                                <input type="password" class="brutal-input @error('current_password') border-danger @enderror"
                                       wire:model="current_password" placeholder="{{ __('general.current_password_placeholder') }}">
                                @error('current_password')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.new_password_label') }}</label>
                                <input type="password" class="brutal-input @error('new_password') border-danger @enderror"
                                       wire:model="new_password" placeholder="{{ __('general.min_8_chars') }}">
                                @error('new_password')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <button type="submit" class="brutal-btn-black w-100" wire:loading.attr="disabled" style="padding: 20px;">
                        <span wire:loading.remove wire:target="save">{{ __('general.save_changes') }}</span>
                        <span wire:loading wire:target="save">{{ __('general.saving_btn') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>