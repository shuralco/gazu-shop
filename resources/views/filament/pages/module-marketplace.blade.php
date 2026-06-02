<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $categories = $this->getCategories();
    @endphp

    {{-- ─── HEADER + STATS ─── --}}
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Каталог-вітрина усіх модулів магазину. Встанови з .zip, увімкни/вимкни одним кліком,
                    подивись деталі або експортуй модуль як архів.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-filament::badge color="success" icon="heroicon-m-check-circle">
                    {{ $stats['enabled'] }} активних
                </x-filament::badge>
                <x-filament::badge color="gray">
                    {{ $stats['total'] }} всього
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>

    {{-- ─── INSTALL FROM ZIP ─── --}}
    <x-filament::section
        icon="heroicon-o-arrow-up-tray"
        icon-color="primary"
        collapsible
        collapsed
    >
        <x-slot name="heading">Встановити модуль з .zip</x-slot>
        <x-slot name="description">
            Очікується ZIP з module.json у корені (або в одній обгортковій папці). Ліміт 10&nbsp;MB.
        </x-slot>

        <div class="space-y-4">
            <input
                type="file"
                wire:model="installZip"
                accept=".zip"
                class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
            />

            @error('installZip')
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror

            @if ($installZip)
                <x-filament::badge color="success" icon="heroicon-m-document-arrow-up">
                    Файл готовий до встановлення
                </x-filament::badge>
            @endif

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input
                    type="checkbox"
                    wire:model="installForce"
                    class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 checked:ring-0 dark:bg-white/5 dark:ring-white/20"
                />
                Перезаписати, якщо модуль уже встановлено
            </label>

            <div class="flex flex-wrap items-center gap-2">
                @if ($installZip && ! $installPreview)
                    <x-filament::button
                        wire:click="previewInstall"
                        wire:loading.attr="disabled"
                        wire:target="previewInstall"
                        color="gray"
                        outlined
                        icon="heroicon-o-eye"
                    >
                        <span wire:loading.remove wire:target="previewInstall">Preview</span>
                        <span wire:loading wire:target="previewInstall">Аналізую…</span>
                    </x-filament::button>
                @endif

                <x-filament::button
                    wire:click="installFromZip"
                    wire:loading.attr="disabled"
                    wire:target="installFromZip,installZip"
                    color="primary"
                    icon="heroicon-o-arrow-down-tray"
                >
                    <span wire:loading.remove wire:target="installFromZip">Встановити</span>
                    <span wire:loading wire:target="installFromZip">Встановлюю…</span>
                </x-filament::button>

                @if ($installZip)
                    <x-filament::link
                        tag="button"
                        wire:click="$set('installZip', null); $set('installPreview', null)"
                        color="gray"
                    >
                        Скасувати
                    </x-filament::link>
                @endif
            </div>

            {{-- Preview-результат --}}
            @if ($installPreview)
                <div class="rounded-lg border border-primary-200 bg-primary-50/50 p-4 text-sm dark:border-primary-900 dark:bg-primary-900/10">
                    <div class="flex flex-wrap items-center gap-2">
                        <strong class="text-gray-900 dark:text-white">
                            {{ $installPreview['label'] ?? $installPreview['module_name'] ?? '—' }}
                        </strong>
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

    {{-- ─── CATALOG BY CATEGORY ─── --}}
    @foreach ($categories as $catKey => $category)
        <x-filament::section :icon="$category['icon']" icon-color="gray">
            <x-slot name="heading">{{ $category['label'] }}</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="gray">{{ count($category['modules']) }}</x-filament::badge>
            </x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
                @foreach ($category['modules'] as $module)
                    <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-base font-semibold leading-tight text-gray-900 dark:text-white">
                                    {{ $module['name'] }}
                                </h3>
                                @if ($module['enabled'])
                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">Увімкнено</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" icon="heroicon-m-pause-circle">Вимкнено</x-filament::badge>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if ($module['version'])
                                    <x-filament::badge color="info" size="sm">v{{ $module['version'] }}</x-filament::badge>
                                @endif
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $module['key'] }}</span>
                                @if (! $module['in_modules_dir'])
                                    <x-filament::badge color="warning" size="sm">config-only</x-filament::badge>
                                @endif
                            </div>

                            @if ($module['description'])
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ $module['description'] }}
                                </p>
                            @endif

                            @if (! empty($module['requires']))
                                <div class="mt-2 flex flex-wrap items-center gap-1">
                                    <span class="text-xs text-gray-400">Потребує:</span>
                                    @foreach ($module['requires'] as $req)
                                        <x-filament::badge color="warning" size="sm">{{ $req }}</x-filament::badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- ─── ACTIONS ─── --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-white/5">
                            @if ($module['enabled'])
                                <x-filament::button
                                    wire:click="toggleModule('{{ $module['key'] }}', false)"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleModule"
                                    color="danger"
                                    size="sm"
                                    outlined
                                    icon="heroicon-m-pause"
                                >
                                    Вимкнути
                                </x-filament::button>
                            @else
                                <x-filament::button
                                    wire:click="toggleModule('{{ $module['key'] }}', true)"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleModule"
                                    color="success"
                                    size="sm"
                                    icon="heroicon-m-play"
                                >
                                    Увімкнути
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                tag="a"
                                href="{{ url('/admin/modules/view?key=' . $module['key']) }}"
                                color="gray"
                                size="sm"
                                outlined
                                icon="heroicon-m-information-circle"
                            >
                                Деталі
                            </x-filament::button>

                            @if ($module['in_modules_dir'])
                                <x-filament::button
                                    wire:click="exportModule('{{ $module['key'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="exportModule"
                                    color="gray"
                                    size="sm"
                                    outlined
                                    icon="heroicon-m-arrow-down-tray"
                                >
                                    Експорт
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach

    @if (empty($categories))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Модулів не знайдено.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
