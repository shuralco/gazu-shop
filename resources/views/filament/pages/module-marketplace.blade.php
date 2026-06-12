<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $categories = $this->getCategories();
        $store = $this->getStoreCatalog();
        $storeConfigured = $this->isStoreConfigured();
    @endphp

    {{-- ─── HEADER + STATS ─── --}}
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Єдина вітрина модулів і інтеграцій магазину, згрупована по категоріях.
                    Вмикай/вимикай одним кліком, відкривай налаштування, встановлюй із .zip
                    або обирай нові розширення в магазині нижче.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-filament::badge color="success" icon="heroicon-m-check-circle">
                    {{ $stats['enabled'] }} активних
                </x-filament::badge>
                <x-filament::badge color="gray">
                    {{ $stats['modules'] }} модулів · {{ $stats['integrations'] }} інтеграцій
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>

    {{-- ─── INSTALL FROM ZIP ─── --}}
    <x-filament::section icon="heroicon-o-arrow-up-tray" icon-color="primary" collapsible collapsed>
        <x-slot name="heading">Встановити модуль з .zip</x-slot>
        <x-slot name="description">
            Очікується ZIP з module.json у корені (або в одній обгортковій папці). Ліміт 10&nbsp;MB.
        </x-slot>

        <div class="space-y-4">
            <input type="file" wire:model="installZip" accept=".zip"
                class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />

            @error('installZip')
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror

            @if ($installZip)
                <x-filament::badge color="success" icon="heroicon-m-document-arrow-up">Файл готовий до встановлення</x-filament::badge>
            @endif

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" wire:model="installForce"
                    class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 checked:ring-0 dark:bg-white/5 dark:ring-white/20" />
                Перезаписати, якщо модуль уже встановлено
            </label>

            <div class="flex flex-wrap items-center gap-2">
                @if ($installZip && ! $installPreview)
                    <x-filament::button wire:click="previewInstall" wire:loading.attr="disabled" wire:target="previewInstall" color="gray" outlined icon="heroicon-o-eye">
                        <span wire:loading.remove wire:target="previewInstall">Preview</span>
                        <span wire:loading wire:target="previewInstall">Аналізую…</span>
                    </x-filament::button>
                @endif

                <x-filament::button wire:click="installFromZip" wire:loading.attr="disabled" wire:target="installFromZip,installZip" color="primary" icon="heroicon-o-arrow-down-tray">
                    <span wire:loading.remove wire:target="installFromZip">Встановити</span>
                    <span wire:loading wire:target="installFromZip">Встановлюю…</span>
                </x-filament::button>

                @if ($installZip)
                    <x-filament::link tag="button" wire:click="$set('installZip', null); $set('installPreview', null)" color="gray">Скасувати</x-filament::link>
                @endif
            </div>

            @if ($installPreview)
                <div class="rounded-lg border border-primary-200 bg-primary-50/50 p-4 text-sm dark:border-primary-900 dark:bg-primary-900/10">
                    <div class="flex flex-wrap items-center gap-2">
                        <strong class="text-gray-900 dark:text-white">{{ $installPreview['label'] ?? $installPreview['module_name'] ?? '—' }}</strong>
                        @if (! empty($installPreview['version']))
                            <x-filament::badge color="info">v{{ $installPreview['version'] }}</x-filament::badge>
                        @endif
                    </div>
                    @if (! empty($installPreview['description']))
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $installPreview['description'] }}</p>
                    @endif
                    @if (! empty($installPreview['will_create_tables']))
                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            <span class="text-gray-500">Створить таблиці:</span>
                            @foreach ($installPreview['will_create_tables'] as $t)
                                <x-filament::badge color="gray">{{ $t }}</x-filament::badge>
                            @endforeach
                        </div>
                    @endif
                    @if (! empty($installPreview['requires_modules']))
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="text-gray-500">Потребує модулі:</span>
                            @foreach ($installPreview['requires_modules'] as $r)
                                <x-filament::badge color="warning">{{ $r }}</x-filament::badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- ─── CATALOG BY CATEGORY (modules + integrations) ─── --}}
    @foreach ($categories as $catKey => $category)
        <x-filament::section :icon="$category['icon']" icon-color="gray">
            <x-slot name="heading">{{ $category['label'] }}</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="gray">{{ count($category['items']) }}</x-filament::badge>
            </x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
                @foreach ($category['items'] as $item)
                    <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="flex items-center gap-2 text-base font-semibold leading-tight text-gray-900 dark:text-white">
                                    @if (! empty($item['icon_emoji']))
                                        <span class="text-lg leading-none">{{ $item['icon_emoji'] }}</span>
                                    @endif
                                    <span>{{ $item['name'] }}</span>
                                </h3>
                                @if ($item['enabled'])
                                    @if (($item['status_level'] ?? null) === 'warning')
                                        <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">Налаштувати</x-filament::badge>
                                    @else
                                        <x-filament::badge color="success" icon="heroicon-m-check-circle">Увімкнено</x-filament::badge>
                                    @endif
                                @else
                                    <x-filament::badge color="gray" icon="heroicon-m-pause-circle">Вимкнено</x-filament::badge>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if ($item['type'] === 'integration')
                                    <x-filament::badge color="primary" size="sm">Інтеграція</x-filament::badge>
                                @else
                                    <x-filament::badge color="info" size="sm">Модуль</x-filament::badge>
                                @endif
                                @if (! empty($item['version']))
                                    <x-filament::badge color="gray" size="sm">v{{ $item['version'] }}</x-filament::badge>
                                @endif
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $item['key'] }}</span>
                                @if ($item['type'] === 'module' && empty($item['in_modules_dir']))
                                    <x-filament::badge color="warning" size="sm">config-only</x-filament::badge>
                                @endif
                            </div>

                            @if (! empty($item['description']))
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $item['description'] }}</p>
                            @endif

                            @if (! empty($item['requires']))
                                <div class="mt-2 flex flex-wrap items-center gap-1">
                                    <span class="text-xs text-gray-400">Потребує:</span>
                                    @foreach ($item['requires'] as $req)
                                        <x-filament::badge color="warning" size="sm">{{ $req }}</x-filament::badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- ─── ACTIONS ─── --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-white/5">
                            @if ($item['type'] === 'integration')
                                @if ($item['enabled'])
                                    <x-filament::button wire:click="toggleIntegration('{{ $item['key'] }}')" wire:loading.attr="disabled" wire:target="toggleIntegration" color="danger" size="sm" outlined icon="heroicon-m-pause">Вимкнути</x-filament::button>
                                @else
                                    <x-filament::button wire:click="toggleIntegration('{{ $item['key'] }}')" wire:loading.attr="disabled" wire:target="toggleIntegration" color="success" size="sm" icon="heroicon-m-play">Увімкнути</x-filament::button>
                                @endif
                                @if (! empty($item['config_url']))
                                    <x-filament::button tag="a" href="{{ $item['config_url'] }}" color="gray" size="sm" outlined icon="heroicon-m-cog-6-tooth">Налаштування</x-filament::button>
                                @endif
                            @else
                                @if ($item['enabled'])
                                    <x-filament::button wire:click="toggleModule('{{ $item['key'] }}', false)" wire:loading.attr="disabled" wire:target="toggleModule" color="danger" size="sm" outlined icon="heroicon-m-pause">Вимкнути</x-filament::button>
                                @else
                                    <x-filament::button wire:click="toggleModule('{{ $item['key'] }}', true)" wire:loading.attr="disabled" wire:target="toggleModule" color="success" size="sm" icon="heroicon-m-play">Увімкнути</x-filament::button>
                                @endif
                                <x-filament::button tag="a" href="{{ $item['config_url'] }}" color="gray" size="sm" outlined icon="heroicon-m-information-circle">Деталі</x-filament::button>
                                @if (! empty($item['in_modules_dir']))
                                    <x-filament::button wire:click="exportModule('{{ $item['key'] }}')" wire:loading.attr="disabled" wire:target="exportModule" color="gray" size="sm" outlined icon="heroicon-m-arrow-down-tray">Експорт</x-filament::button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach

    {{-- ─── STORE (license server, stub) ─── --}}
    <x-filament::section icon="heroicon-o-shopping-bag" icon-color="warning">
        <x-slot name="heading">Магазин розширень</x-slot>
        <x-slot name="description">Додаткові модулі та інтеграції з ліцензійного сервера Lionex.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::badge :color="$storeConfigured ? 'success' : 'gray'">
                {{ $storeConfigured ? 'Ліцензію підключено' : 'Демо-каталог' }}
            </x-filament::badge>
        </x-slot>

        @unless ($storeConfigured)
            <div class="mb-4 rounded-lg border border-warning-200 bg-warning-50/50 p-3 text-sm text-warning-800 dark:border-warning-900 dark:bg-warning-900/10 dark:text-warning-300">
                Підключіть ліцензійний ключ Lionex (env <code>MARKETPLACE_LICENSE_KEY</code>) — тоді тут зʼявиться повний каталог і стане доступною купівля в один клік.
            </div>
        @endunless

        @if (empty($store))
            <p class="text-sm text-gray-500 dark:text-gray-400">Каталог поки порожній.</p>
        @else
            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
                @foreach ($store as $ext)
                    <div class="flex flex-col justify-between rounded-xl border border-dashed border-gray-300 bg-gray-50/50 p-4 transition hover:shadow-md dark:border-white/15 dark:bg-white/5">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="flex items-center gap-2 text-base font-semibold leading-tight text-gray-900 dark:text-white">
                                    @if (! empty($ext['icon']))
                                        <span class="text-lg leading-none">{{ $ext['icon'] }}</span>
                                    @endif
                                    <span>{{ $ext['name'] }}</span>
                                </h3>
                                @if (($ext['status'] ?? '') === 'soon')
                                    <x-filament::badge color="gray">Незабаром</x-filament::badge>
                                @else
                                    <x-filament::badge color="success">Доступно</x-filament::badge>
                                @endif
                            </div>
                            @if (! empty($ext['price']))
                                <div class="mt-1.5"><x-filament::badge color="warning" size="sm">{{ $ext['price'] }}</x-filament::badge></div>
                            @endif
                            @if (! empty($ext['description']))
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $ext['description'] }}</p>
                            @endif
                        </div>
                        <div class="mt-4 border-t border-gray-100 pt-3 dark:border-white/5">
                            <x-filament::button wire:click="purchaseModule('{{ $ext['key'] }}')" wire:loading.attr="disabled" wire:target="purchaseModule" color="warning" size="sm" icon="heroicon-m-shopping-cart">
                                Придбати
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    @if (empty($categories))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Розширень не знайдено.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
