<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . $title }}</title>
        <meta name="description" content="{{ __('general.addresses') }}">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="brutal-title mb-0">{{ $title }}</h1>
                    @if(!$showForm)
                        <button wire:click="$set('showForm', true)" class="brutal-btn-black">
                            {{ __('general.add_address') }}
                        </button>
                    @endif
                </div>

                <div wire:loading class="text-center py-3">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Address Form (Modal-like inline) -->
                @if($showForm)
                <div class="brutal-content-card mb-4">
                    <h2 class="brutal-subtitle">
                        {{ $editingId ? __('general.edit_address') : __('general.new_address') }}
                    </h2>

                    <form wire:submit="save">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="brutal-label">{{ __('general.address_name_label') }}</label>
                                <input type="text" class="brutal-input @error('label') border-danger @enderror"
                                       wire:model="label" placeholder="{{ __('general.address_name_placeholder') }}">
                                @error('label')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.first_name_required') }}</label>
                                <input type="text" class="brutal-input @error('first_name') border-danger @enderror"
                                       wire:model="first_name" placeholder="{{ __('general.name_short') }}">
                                @error('first_name')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.last_name_required') }}</label>
                                <input type="text" class="brutal-input @error('last_name') border-danger @enderror"
                                       wire:model="last_name" placeholder="{{ __('general.last_name_placeholder') }}">
                                @error('last_name')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.phone_required') }}</label>
                                <input type="tel" class="brutal-input @error('phone') border-danger @enderror"
                                       wire:model="phone" placeholder="+380 XX XXX XX XX">
                                @error('phone')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="brutal-label">{{ __('general.city_required') }}</label>
                                <input type="text" class="brutal-input @error('city') border-danger @enderror"
                                       wire:model="city" placeholder="{{ __('general.city_placeholder_short') }}">
                                @error('city')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="brutal-label">{{ __('general.address_required') }}</label>
                                <input type="text" class="brutal-input @error('address') border-danger @enderror"
                                       wire:model="address" placeholder="{{ __('general.address_field_placeholder') }}">
                                @error('address')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="brutal-label">{{ __('general.postal_code_label') }}</label>
                                <input type="text" class="brutal-input @error('postal_code') border-danger @enderror"
                                       wire:model="postal_code" placeholder="01001">
                                @error('postal_code')
                                    <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="brutal-checkbox-label">
                                    <input type="checkbox" class="brutal-checkbox" wire:model="is_default">
                                    {{ __('general.make_default_address') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="brutal-btn-black" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? __('general.update_btn') : __('general.save_btn') }}
                                </span>
                                <span wire:loading wire:target="save">
                                    {{ __('general.saving_btn') }}
                                </span>
                            </button>
                            <button type="button" wire:click="resetForm" class="brutal-btn-outline">
                                {{ __('general.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Address Cards -->
                @if($addresses->count() > 0)
                    <div class="row g-3" wire:loading.remove>
                        @foreach($addresses as $addr)
                        <div class="col-12 col-md-6" wire:key="address-{{ $addr->id }}">
                            <div class="brutal-card h-100 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($addr->label)
                                            <span style="background: black; color: white; padding: 2px 12px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                {{ $addr->label }}
                                            </span>
                                        @endif
                                        @if($addr->is_default)
                                            <span style="background: #34c759; color: white; padding: 2px 12px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                {{ __('general.default_badge') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="fw-bold mb-1" style="font-size: 18px;">
                                    {{ $addr->first_name }} {{ $addr->last_name }}
                                </p>
                                <p class="mb-1">{{ $addr->address }}</p>
                                <p class="mb-1">{{ $addr->city }}{{ $addr->postal_code ? ', ' . $addr->postal_code : '' }}</p>
                                <p class="text-muted mb-3">
                                    <i class="fa fa-phone"></i> {{ $addr->phone }}
                                </p>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button wire:click="edit({{ $addr->id }})" class="brutal-btn-outline" style="padding: 8px 16px; font-size: 14px;">
                                        {{ __('general.edit_btn') }}
                                    </button>
                                    @if(!$addr->is_default)
                                        <button wire:click="setDefault({{ $addr->id }})" class="brutal-btn-outline" style="padding: 8px 16px; font-size: 14px;">
                                            {{ __('general.default_badge') }}
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $addr->id }})"
                                            wire:confirm="{{ __('general.delete_address_confirm') }}"
                                            class="brutal-btn-danger" style="padding: 8px 16px; font-size: 14px;">
                                        {{ __('general.delete_btn') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="brutal-empty-state" wire:loading.remove>
                        <div class="brutal-empty-state-icon">&#x1F4CD;</div>
                        <div class="brutal-empty-state-text">{{ __('general.no_saved_addresses') }}</div>
                        <p class="text-muted mt-2">{{ __('general.add_address_hint') }}</p>
                        <button wire:click="$set('showForm', true)" class="brutal-btn-black mt-3">
                            {{ __('general.add_first_address') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>