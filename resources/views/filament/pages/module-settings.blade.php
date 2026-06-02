<x-filament-panels::page>
    @php
        $groups = $this->getGroupedModules();
        $allModules = \App\Support\ModuleManager::all();
        $enabledCount = $allModules->filter(fn ($m) => $m->enabled())->count();
        $totalCount = $allModules->count();
    @endphp

    {{-- ─── INTRO + STATS ─── --}}
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl leading-relaxed">
                Опційні фічі магазину — вмикай/вимикай миттєво без redeploy. Стан зберігається у БД,
                дані модулів залишаються при вимкненні. Встанови з .zip, переглянь деталі або експортуй як архів.
            </p>
            <div class="flex items-center gap-2 shrink-0">
                <x-filament::badge color="success" icon="heroicon-m-check-circle">
                    {{ $enabledCount }} активних
                </x-filament::badge>
                <x-filament::badge color="gray">
                    {{ $totalCount }} всього
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

        <div class="space-y-4 max-w-2xl">
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
    @foreach ($groups as $groupKey => $group)
        <x-filament::section :icon="$group['icon']" icon-color="gray">
            <x-slot name="heading">{{ $group['label'] }}</x-slot>
            <x-slot name="headerEnd">
                <x-filament::badge color="gray">{{ count($group['modules']) }}</x-filament::badge>
            </x-slot>

            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
                @foreach ($group['modules'] as $m)
                    <div
                        x-data="{ showDelete: false, deleteMode: 'soft', showDisable: false, rollbackMigrations: false }"
                        class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-white/5"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-base font-semibold leading-tight text-gray-900 dark:text-white">
                                    {{ $m['name'] }}
                                </h3>
                                @if ($m['enabled'])
                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">Увімкнено</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" icon="heroicon-m-pause-circle">Вимкнено</x-filament::badge>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                @if ($m['version'])
                                    <x-filament::badge color="info" size="sm">v{{ $m['version'] }}</x-filament::badge>
                                @endif
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $m['key'] }}</span>
                                @if (! ($m['in_modules_dir'] ?? false))
                                    <x-filament::badge color="warning" size="sm">config-only</x-filament::badge>
                                @endif
                            </div>

                            @if ($m['description'])
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ $m['description'] }}
                                </p>
                            @endif

                            @if (! empty($m['requires']) || ! empty($m['dependents']))
                                <div class="mt-2 space-y-1 text-xs">
                                    @if (! empty($m['requires']))
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span class="text-gray-400">Потребує:</span>
                                            @foreach ($m['requires'] as $req)
                                                <x-filament::badge color="warning" size="sm">{{ $req }}</x-filament::badge>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! empty($m['dependents']))
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span class="text-gray-400">Потрібен для:</span>
                                            @foreach ($m['dependents'] as $dep)
                                                <x-filament::badge color="gray" size="sm">{{ $dep }}</x-filament::badge>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- ─── ACTIONS ─── --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 dark:border-white/5">
                            @if ($m['enabled'])
                                <x-filament::button
                                    x-on:click="showDisable = true; rollbackMigrations = false"
                                    color="danger"
                                    size="sm"
                                    outlined
                                    icon="heroicon-m-pause"
                                >
                                    Вимкнути
                                </x-filament::button>
                            @else
                                <x-filament::button
                                    wire:click="toggleModule('{{ $m['key'] }}', true)"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleModule('{{ $m['key'] }}', true)"
                                    color="success"
                                    size="sm"
                                    icon="heroicon-m-play"
                                >
                                    Увімкнути
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                tag="a"
                                href="{{ url('/admin/modules/view?key=' . $m['key']) }}"
                                color="gray"
                                size="sm"
                                outlined
                                icon="heroicon-m-information-circle"
                            >
                                Деталі
                            </x-filament::button>

                            @if ($m['in_modules_dir'] ?? false)
                                <x-filament::button
                                    wire:click="exportModule('{{ $m['key'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="exportModule('{{ $m['key'] }}')"
                                    color="gray"
                                    size="sm"
                                    outlined
                                    icon="heroicon-m-arrow-down-tray"
                                >
                                    Експорт
                                </x-filament::button>

                                @if (! $m['enabled'])
                                    <x-filament::icon-button
                                        icon="heroicon-m-trash"
                                        x-on:click="showDelete = true; deleteMode = 'soft'"
                                        label="Видалити модуль"
                                        color="danger"
                                        size="sm"
                                    />
                                @endif
                            @endif
                        </div>

                        {{-- Disable-confirm модал --}}
                        <div x-show="showDisable" x-cloak
                             x-on:keydown.escape.window="showDisable = false"
                             class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm"
                             x-on:click.self="showDisable = false">
                            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-900" x-on:click.stop>
                                <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Вимкнути «{{ $m['name'] }}»?</h3>
                                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-mono">{{ $m['key'] }}</span>
                                    @if (! empty($m['dependents']))
                                        <br><span class="text-amber-600 dark:text-amber-500">Залежать: {{ implode(', ', $m['dependents']) }} — будуть вимкнені каскадно.</span>
                                    @endif
                                </p>

                                <label class="mb-4 flex cursor-pointer items-start gap-2.5 rounded-lg border border-gray-200 p-3 transition-colors hover:border-amber-300 dark:border-white/10 dark:hover:border-amber-700"
                                       x-bind:class="rollbackMigrations ? 'bg-amber-50 border-amber-300 dark:bg-amber-900/20 dark:border-amber-700' : ''">
                                    <input type="checkbox" x-model="rollbackMigrations" class="mt-0.5 fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20" />
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Скинути міграції (drop tables)</div>
                                        <div class="text-xs text-gray-500">Видалить дані модуля з БД. Без цього — дані лишаться, reinstall їх відновить.</div>
                                    </div>
                                </label>

                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button x-on:click="showDisable = false" color="gray" size="sm" outlined>
                                        Скасувати
                                    </x-filament::button>
                                    <x-filament::button
                                        x-on:click="$wire.call('toggleModule', '{{ $m['key'] }}', false, true, rollbackMigrations); showDisable = false"
                                        color="danger"
                                        size="sm"
                                    >
                                        <span x-text="rollbackMigrations ? 'Вимкнути + скинути дані' : 'Вимкнути'"></span>
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>

                        {{-- Confirm-видалення modal --}}
                        <div x-show="showDelete" x-cloak
                             x-on:keydown.escape.window="showDelete = false"
                             class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm"
                             x-on:click.self="showDelete = false">
                            <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-900" x-on:click.stop>
                                <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Видалити «{{ $m['name'] }}»?</h3>
                                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    Оберіть тип видалення. <span class="font-mono">{{ $m['key'] }}</span>
                                </p>

                                <div class="mb-4 space-y-2.5">
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-gray-200 p-3 transition-colors hover:border-gray-300 dark:border-white/10 dark:hover:border-white/20"
                                           x-bind:class="deleteMode === 'soft' ? 'bg-gray-50 border-gray-300 dark:bg-white/5 dark:border-white/20' : ''">
                                        <input type="radio" x-model="deleteMode" value="soft" class="mt-0.5" />
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Лише файли</div>
                                            <div class="text-xs text-gray-500">Видалити папку <span class="font-mono">modules/{{ $m['key'] }}/</span>. Дані в БД залишаться — reinstall відновить доступ.</div>
                                        </div>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-gray-200 p-3 transition-colors hover:border-danger-300 dark:border-white/10 dark:hover:border-danger-800"
                                           x-bind:class="deleteMode === 'hard' ? 'bg-danger-50 border-danger-300 dark:bg-danger-900/20 dark:border-danger-700' : ''">
                                        <input type="radio" x-model="deleteMode" value="hard" class="mt-0.5" />
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-danger-700 dark:text-danger-400">Файли + дані</div>
                                            <div class="text-xs text-gray-500">Rollback migrations (drop tables) + видалення з БД. <strong class="text-danger-600">Необоротна дія.</strong></div>
                                        </div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button x-on:click="showDelete = false" color="gray" size="sm" outlined>
                                        Скасувати
                                    </x-filament::button>
                                    <x-filament::button
                                        x-on:click="$wire.call('uninstallModule', '{{ $m['key'] }}', deleteMode); showDelete = false"
                                        x-bind:color="deleteMode === 'hard' ? 'danger' : 'primary'"
                                        color="danger"
                                        size="sm"
                                    >
                                        <span x-text="deleteMode === 'hard' ? 'Видалити повністю' : 'Видалити папку'"></span>
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach

    @if (empty($groups))
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Модулів не знайдено.</p>
        </x-filament::section>
    @endif

    {{-- ─── FOOTER TIP ─── --}}
    <x-filament::section>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
            Для швидкого набору під певний тип магазину запустіть preset:
            <code class="mx-0.5 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">php artisan preset:apply auto-parts</code>,
            <code class="mx-0.5 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">cosmetics</code> або
            <code class="mx-0.5 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">general-shop</code>.
        </p>
    </x-filament::section>
</x-filament-panels::page>
