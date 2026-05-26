<x-filament-panels::page>
    <div class="space-y-8">

        {{-- Hero info banner --}}
        <div class="rounded-xl bg-gradient-to-br from-primary-50 to-blue-50 dark:from-gray-800 dark:to-gray-900 p-5 border border-primary-200 dark:border-gray-700">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
                    <x-filament::icon icon="heroicon-o-puzzle-piece" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Модульна система</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        Будь-який модуль можна <strong>увімкнути або вимкнути миттєво</strong> — стан зберігається у БД-таблиці <code class="px-1.5 py-0.5 bg-white/70 dark:bg-gray-700 rounded text-xs">modules</code>, redeploy не потрібен.
                        Вимкнення приховує модуль із sidebar, скасовує routes і views, але <strong>дані залишаються</strong> — повторне ввімкнення повертає функціонал миттєво.
                    </p>
                    <div class="flex flex-wrap gap-2 mt-3 text-xs">
                        <span class="px-2 py-1 bg-white dark:bg-gray-700 rounded font-mono">php artisan module:list</span>
                        <span class="px-2 py-1 bg-white dark:bg-gray-700 rounded font-mono">php artisan preset:apply auto-parts</span>
                        <a href="https://github.com/shuralco/gazu-shop/blob/main/docs/MODULES.md" target="_blank" class="px-2 py-1 bg-white dark:bg-gray-700 rounded text-primary-600 hover:underline">📖 docs/MODULES.md</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Module groups --}}
        @foreach($this->getGroupedModules() as $groupKey => $group)
            <section>
                <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white mb-3">
                    <x-filament::icon :icon="$group['icon']" class="w-5 h-5 text-gray-500" />
                    {{ $group['label'] }}
                    <span class="text-sm font-normal text-gray-500">({{ count($group['modules']) }})</span>
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($group['modules'] as $m)
                        <div class="rounded-xl border-2 {{ $m['enabled'] ? 'border-success-500 bg-success-50/30 dark:bg-success-900/10' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900' }} p-5 transition-all hover:shadow-lg flex flex-col gap-3">

                            {{-- Header: name + status + version --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">{{ $m['name'] }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <code class="text-xs text-gray-500 font-mono">{{ $m['key'] }}</code>
                                        @if($m['version'])
                                            <span class="text-xs text-gray-400">v{{ $m['version'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status badge --}}
                                <div class="shrink-0">
                                    @if($m['enabled'])
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-success-100 dark:bg-success-900 text-success-800 dark:text-success-200 rounded-full">
                                            <span class="w-1.5 h-1.5 bg-success-500 rounded-full animate-pulse"></span>
                                            УВІМК
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">
                                            ВИМК
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Description --}}
                            @if($m['description'])
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $m['description'] }}</p>
                            @endif

                            {{-- Dependencies info --}}
                            @if(! empty($m['requires']) || ! empty($m['dependents']))
                                <div class="space-y-1.5 text-xs">
                                    @if(! empty($m['requires']))
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="text-gray-500">Потребує:</span>
                                            @foreach($m['requires'] as $req)
                                                <span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 rounded font-mono">{{ $req }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(! empty($m['dependents']))
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="text-amber-600 dark:text-amber-500">⚠️ Від нього залежать:</span>
                                            @foreach($m['dependents'] as $dep)
                                                <span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 rounded font-mono">{{ $dep }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Settings form (if schema defined) --}}
                            @if($m['has_settings'] && $m['enabled'])
                                <details class="text-sm">
                                    <summary class="cursor-pointer text-primary-600 dark:text-primary-400 font-medium hover:underline select-none">
                                        ⚙️ Налаштування ({{ count($m['settings_schema']) }})
                                    </summary>
                                    <div class="mt-3 space-y-3 pl-3 border-l-2 border-primary-200 dark:border-primary-700">
                                        @foreach($m['settings_schema'] as $settingKey => $schema)
                                            <label class="block">
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 block mb-1">
                                                    {{ $settingKey }}
                                                    <span class="text-gray-400 font-normal">
                                                        ({{ $schema['type'] ?? 'string' }})
                                                    </span>
                                                </span>
                                                @if(($schema['type'] ?? 'string') === 'bool')
                                                    <label class="flex items-center gap-2">
                                                        <input
                                                            type="checkbox"
                                                            wire:model="settings.{{ $m['key'] }}.{{ $settingKey }}"
                                                            class="rounded text-primary-600"
                                                        />
                                                        <span class="text-sm text-gray-500">Увімкнено</span>
                                                    </label>
                                                @elseif(($schema['type'] ?? 'string') === 'int')
                                                    <input
                                                        type="number"
                                                        wire:model="settings.{{ $m['key'] }}.{{ $settingKey }}"
                                                        placeholder="{{ $schema['default'] ?? '' }}"
                                                        class="w-full px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                                    />
                                                @else
                                                    <input
                                                        type="text"
                                                        wire:model="settings.{{ $m['key'] }}.{{ $settingKey }}"
                                                        placeholder="{{ $schema['default'] ?? '' }}"
                                                        class="w-full px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                                    />
                                                @endif
                                            </label>
                                        @endforeach
                                        <button
                                            type="button"
                                            wire:click="saveModuleSettings('{{ $m['key'] }}')"
                                            class="w-full px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded font-medium text-sm transition-colors"
                                        >
                                            Зберегти налаштування
                                        </button>
                                    </div>
                                </details>
                            @endif

                            {{-- Actions: toggle + details --}}
                            <div class="mt-auto pt-2 flex gap-2">
                                <a href="{{ url('/admin/modules/view?key='.$m['key']) }}"
                                    class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg font-medium text-sm transition-colors flex items-center gap-1.5"
                                    title="Деталі модуля">
                                    <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-4 h-4" />
                                    Деталі
                                </a>
                                @if($m['enabled'])
                                    <button
                                        type="button"
                                        wire:click="toggleModule('{{ $m['key'] }}', false)"
                                        wire:confirm="Точно вимкнути «{{ $m['name'] }}»?"
                                        class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 border-2 border-danger-500 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2"
                                    >
                                        <x-filament::icon icon="heroicon-o-power" class="w-4 h-4" />
                                        Вимкнути
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="toggleModule('{{ $m['key'] }}', true)"
                                        class="flex-1 px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2"
                                    >
                                        <x-filament::icon icon="heroicon-o-bolt" class="w-4 h-4" />
                                        Увімкнути
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Footer info --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
            <strong class="text-gray-800 dark:text-gray-200">Tip:</strong>
            Для швидкого набору модулів під певний тип магазину використовуй preset:
            <code class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded font-mono">php artisan preset:apply auto-parts</code>,
            <code class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded font-mono">cosmetics</code>,
            <code class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded font-mono">general-shop</code>.
        </div>
    </div>
</x-filament-panels::page>
