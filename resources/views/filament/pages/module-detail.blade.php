@php
$info = $this->getModuleInfo();
$health = $this->getHealthChecks();
$activity = $this->getRecentActivity(10);
$healthCounts = ['ok'=>0,'warning'=>0,'error'=>0];
foreach($health as $h) { $healthCounts[$h['status']] = ($healthCounts[$h['status']] ?? 0) + 1; }
$actionLabels = [
  'enabled' => '✓ Увімкнено',
  'disabled' => '⏸ Вимкнено',
  'settings_saved' => '⚙️ Налаштування збережено',
  'install' => '📦 Встановлено',
  'upgrade' => '⬆️ Оновлено',
  'uninstall' => '🗑 Видалено',
  'boot' => 'boot',
  'disable' => 'disable',
];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- HEADER: hero with name, version, status, primary actions --}}
        <div class="rounded-xl border-2 {{ $info['enabled'] ? 'border-success-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-900 overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $info['name'] }}</h1>
                            @if($info['version'])
                                <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full font-mono">v{{ $info['version'] }}</span>
                            @endif
                            @if($info['enabled'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold bg-success-100 dark:bg-success-900 text-success-800 dark:text-success-200 rounded-full">
                                    <span class="w-2 h-2 bg-success-500 rounded-full animate-pulse"></span>
                                    Увімкнено
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">Вимкнено</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 mb-3">
                            <code class="font-mono">{{ $info['key'] }}</code>
                            @if($info['author'])
                                <span>·</span>
                                <span>{{ $info['author'] }}</span>
                            @endif
                            @if($info['engine_requirement'])
                                <span>·</span>
                                <span>engine {{ $info['engine_requirement'] }}</span>
                            @endif
                        </div>
                        @if($info['description'])
                            <p class="text-gray-700 dark:text-gray-300 max-w-3xl leading-relaxed">{{ $info['description'] }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 shrink-0 w-full sm:w-auto">
                        @if($info['enabled'])
                            <button type="button"
                                wire:click="toggleModule"
                                wire:confirm="Точно вимкнути «{{ $info['name'] }}»?"
                                wire:loading.attr="disabled"
                                wire:target="toggleModule"
                                class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-gray-800 border-2 border-danger-500 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-wait">
                                <x-filament::icon icon="heroicon-o-power" class="w-4 h-4" wire:loading.remove wire:target="toggleModule" />
                                <svg wire:loading wire:target="toggleModule" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                                <span wire:loading.remove wire:target="toggleModule">Вимкнути</span>
                                <span wire:loading wire:target="toggleModule">Вимикаю…</span>
                            </button>
                        @else
                            <button type="button" wire:click="toggleModule"
                                wire:loading.attr="disabled"
                                wire:target="toggleModule"
                                class="w-full sm:w-auto px-4 py-2 bg-success-600 hover:bg-success-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-wait">
                                <x-filament::icon icon="heroicon-o-bolt" class="w-4 h-4" wire:loading.remove wire:target="toggleModule" />
                                <svg wire:loading wire:target="toggleModule" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                                <span wire:loading.remove wire:target="toggleModule">Увімкнути</span>
                                <span wire:loading wire:target="toggleModule">Вмикаю…</span>
                            </button>
                        @endif
                        <button type="button" wire:click="clearModuleCache"
                            wire:loading.attr="disabled"
                            wire:target="clearModuleCache"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-wait">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4" wire:loading.class="animate-spin" wire:target="clearModuleCache" />
                            Очистити кеш
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @php
                $stats = [
                    ['label' => 'Файлів', 'value' => $info['file_count'], 'icon' => 'heroicon-o-document'],
                    ['label' => 'Migrations', 'value' => $info['migrations_count'], 'icon' => 'heroicon-o-circle-stack'],
                    ['label' => 'Routes', 'value' => $info['registered_routes'], 'icon' => 'heroicon-o-arrows-right-left'],
                    ['label' => 'Filament', 'value' => count($info['filament_resources']) + count($info['filament_pages']) + count($info['filament_widgets']), 'icon' => 'heroicon-o-rectangle-group'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider">{{ $stat['label'] }}</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</div>
                        </div>
                        <x-filament::icon :icon="$stat['icon']" class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- DEPENDENCIES --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="flex items-center gap-2 text-base font-bold mb-3 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-link" class="w-5 h-5 text-primary-500" />
                    Залежності
                </h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500 block mb-1.5">Цей модуль потребує:</span>
                        @if(empty($info['requires']))
                            <span class="text-gray-400 italic text-sm">— нічого не вимагає —</span>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($info['requires'] as $req)
                                    <a href="{{ url('/admin/modules/view?key='.$req) }}"
                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 rounded font-mono text-xs hover:bg-blue-200 dark:hover:bg-blue-900 transition-colors">
                                        {{ $req }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500 block mb-1.5">Від нього залежать:</span>
                        @if(empty($info['dependents']))
                            <span class="text-gray-400 italic text-sm">— ніхто не залежить —</span>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($info['dependents'] as $dep => $depEnabled)
                                    <a href="{{ url('/admin/modules/view?key='.$dep) }}"
                                        class="px-2 py-1 rounded font-mono text-xs flex items-center gap-1.5 transition-colors {{ $depEnabled ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 hover:bg-amber-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                                        @if($depEnabled)
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                        @endif
                                        {{ $dep }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-gray-500 block">Default state:</span>
                            <span class="font-medium {{ $info['enabled_by_default'] ? 'text-success-600' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $info['enabled_by_default'] ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                        @if($info['enabled_at'])
                            <div>
                                <span class="text-gray-500 block">Увімкнено:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($info['enabled_at'])->diffForHumans() }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- SETTINGS FORM --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="flex items-center gap-2 text-base font-bold mb-3 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-5 h-5 text-primary-500" />
                    Налаштування
                    @if($info['has_settings'])
                        <span class="text-xs font-normal text-gray-500">({{ count($info['settings_schema']) }})</span>
                    @endif
                </h2>

                @if(! $info['has_settings'])
                    {{-- Empty state: no settings declared --}}
                    <div class="text-center py-8">
                        <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">Цей модуль не оголошує налаштувань.</p>
                        <p class="text-xs text-gray-400 mt-1">Додай <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">settings_schema</code> у <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">module.json</code> щоб з'явилася форма тут.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($info['settings_schema'] as $settingKey => $schema)
                            @php
                                $type = $schema['type'] ?? 'string';
                                $hasError = ! empty($this->settingsErrors[$settingKey]);
                                $errorMsg = $this->settingsErrors[$settingKey] ?? null;
                                $label = $schema['label'] ?? $settingKey;
                                $help = $schema['help'] ?? null;
                                $required = $schema['required'] ?? false;
                            @endphp
                            <label class="block">
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $label }}
                                        @if($required)<span class="text-danger-500" title="Обов'язкове">*</span>@endif
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $type }}</span>
                                </div>
                                @if($type === 'bool')
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="settings.{{ $settingKey }}" class="rounded text-primary-600" />
                                        <span class="text-sm text-gray-500">{{ isset($schema['default']) ? "default: ".($schema['default'] ? 'true' : 'false') : '' }}</span>
                                    </label>
                                @elseif($type === 'int' || $type === 'float')
                                    <input type="number" wire:model="settings.{{ $settingKey }}"
                                        @if(isset($schema['min']))min="{{ $schema['min'] }}"@endif
                                        @if(isset($schema['max']))max="{{ $schema['max'] }}"@endif
                                        @if($type === 'float') step="0.01" @endif
                                        placeholder="default: {{ $schema['default'] ?? '' }}"
                                        class="w-full px-3 py-1.5 text-sm rounded border {{ $hasError ? 'border-danger-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:outline-none" />
                                @else
                                    @if(! empty($schema['enum']))
                                        <select wire:model="settings.{{ $settingKey }}"
                                            class="w-full px-3 py-1.5 text-sm rounded border {{ $hasError ? 'border-danger-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                            <option value="">— оберіть —</option>
                                            @foreach($schema['enum'] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" wire:model="settings.{{ $settingKey }}"
                                            placeholder="default: {{ $schema['default'] ?? '' }}"
                                            class="w-full px-3 py-1.5 text-sm rounded border {{ $hasError ? 'border-danger-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:outline-none" />
                                    @endif
                                @endif
                                @if($hasError)
                                    <span class="block text-xs text-danger-600 mt-1">{{ $errorMsg }}</span>
                                @elseif($help)
                                    <span class="block text-xs text-gray-500 mt-1">{{ $help }}</span>
                                @endif
                            </label>
                        @endforeach

                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <button type="button" wire:click="saveSettings"
                                wire:loading.attr="disabled" wire:target="saveSettings"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-wait flex items-center justify-center gap-2">
                                <svg wire:loading wire:target="saveSettings" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                                <span wire:loading.remove wire:target="saveSettings">Зберегти</span>
                                <span wire:loading wire:target="saveSettings">Зберігаю…</span>
                            </button>
                            <button type="button" wire:click="resetSettings"
                                wire:confirm="Скинути всі налаштування до значень з manifest?"
                                wire:loading.attr="disabled" wire:target="resetSettings"
                                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-wait">
                                Скинути
                            </button>
                        </div>
                    </div>
                @endif
            </section>

            {{-- MANIFEST PREVIEW --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="flex items-center gap-2 text-base font-bold mb-3 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-500" />
                    Manifest
                </h2>

                <div class="space-y-2 text-sm">
                    @foreach([
                        'providers' => 'Service Providers',
                        'filament_resources' => 'Filament Resources',
                        'filament_pages' => 'Filament Pages',
                        'filament_widgets' => 'Filament Widgets',
                        'composer_packages' => 'Composer packages',
                    ] as $field => $label)
                        @if(! empty($info[$field]))
                            <div>
                                <span class="text-xs uppercase tracking-wider text-gray-500 block mb-1">{{ $label }} ({{ count($info[$field]) }})</span>
                                <div class="space-y-1">
                                    @foreach($info[$field] as $cls)
                                        <code class="block text-xs px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded font-mono break-all">{{ $cls }}</code>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($info['views_namespace'])
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gray-500 block mb-1">Views namespace</span>
                            <code class="text-xs px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded font-mono">{{ $info['views_namespace'] }}::view-name</code>
                        </div>
                    @endif
                </div>
            </section>

            {{-- FILES & MIGRATIONS --}}
            <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="flex items-center gap-2 text-base font-bold mb-3 text-gray-900 dark:text-white">
                    <x-filament::icon icon="heroicon-o-folder-open" class="w-5 h-5 text-primary-500" />
                    Файли модуля
                </h2>

                <div class="text-sm space-y-2">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-500 block mb-1">Шлях</span>
                        <code class="text-xs px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded font-mono break-all block">{{ str_replace(base_path().'/', '', $info['module_path']) }}</code>
                        <span class="text-xs text-gray-400 mt-1 block">
                            {{ $info['folder_exists'] ? '✓ існує' : '✗ ВІДСУТНЯ' }} · {{ $info['file_count'] }} файлів
                        </span>
                    </div>

                    @if($info['migrations_count'] > 0)
                        <details class="pt-2">
                            <summary class="cursor-pointer text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 select-none">
                                Migrations ({{ $info['migrations_count'] }})
                            </summary>
                            <div class="mt-2 space-y-1">
                                @foreach($info['migrations'] as $mig)
                                    <code class="block text-xs px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded font-mono break-all">{{ $mig }}</code>
                                @endforeach
                                <button type="button" wire:click="runMigrations"
                                    class="mt-2 w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-xs transition-colors">
                                    Запустити migrate для цього модуля
                                </button>
                            </div>
                        </details>
                    @endif
                </div>
            </section>

        </div>

        {{-- HEALTH CHECKS --}}
        <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
                    @if($healthCounts['error'] > 0)
                        <span class="w-2.5 h-2.5 bg-danger-500 rounded-full"></span>
                    @elseif($healthCounts['warning'] > 0)
                        <span class="w-2.5 h-2.5 bg-warning-500 rounded-full"></span>
                    @else
                        <span class="w-2.5 h-2.5 bg-success-500 rounded-full"></span>
                    @endif
                    Health Checks
                </h2>
                <div class="flex gap-2 text-xs">
                    @if($healthCounts['ok'] > 0)<span class="px-2 py-0.5 bg-success-100 dark:bg-success-900 text-success-800 dark:text-success-200 rounded-full">{{ $healthCounts['ok'] }} OK</span>@endif
                    @if($healthCounts['warning'] > 0)<span class="px-2 py-0.5 bg-warning-100 dark:bg-warning-900 text-warning-800 dark:text-warning-200 rounded-full">{{ $healthCounts['warning'] }} warning</span>@endif
                    @if($healthCounts['error'] > 0)<span class="px-2 py-0.5 bg-danger-100 dark:bg-danger-900 text-danger-800 dark:text-danger-200 rounded-full">{{ $healthCounts['error'] }} error</span>@endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($health as $check)
                    <div class="flex items-start gap-3 p-3 rounded-lg
                        @if($check['status']==='ok') bg-success-50 dark:bg-success-900/20
                        @elseif($check['status']==='warning') bg-warning-50 dark:bg-warning-900/20
                        @else bg-danger-50 dark:bg-danger-900/20
                        @endif">
                        <div class="shrink-0 mt-0.5">
                            @if($check['status']==='ok')
                                <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-success-600" />
                            @elseif($check['status']==='warning')
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-warning-600" />
                            @else
                                <x-filament::icon icon="heroicon-o-x-circle" class="w-5 h-5 text-danger-600" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $check['label'] }}</div>
                            @if($check['detail'])
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ $check['detail'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ACTIVITY LOG --}}
        <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="flex items-center gap-2 text-base font-bold mb-3 text-gray-900 dark:text-white">
                <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-primary-500" />
                Активність
                @if($activity->count() > 0)
                    <span class="text-sm font-normal text-gray-500">({{ $activity->count() }})</span>
                @endif
            </h2>

            @if($activity->count() === 0)
                {{-- Empty state --}}
                <div class="text-center py-8">
                    <x-filament::icon icon="heroicon-o-clock" class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">Поки що записів немає.</p>
                    <p class="text-xs text-gray-400 mt-1">Коли модуль буде увімкнено / вимкнено / змінено налаштування — дія з'явиться тут.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($activity as $entry)
                        <div class="flex items-start gap-3 p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
                            <div class="shrink-0 text-gray-400 text-xs font-mono mt-0.5 w-20">
                                {{ $entry->created_at->diffForHumans() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $actionLabels[$entry->action] ?? $entry->action }}</span>
                                @if($entry->user_id)
                                    <span class="text-xs text-gray-500 ml-2">user #{{ $entry->user_id }}</span>
                                @endif
                                @if(! empty($entry->payload['from_version']) && ! empty($entry->payload['to_version']) && $entry->payload['from_version'] !== $entry->payload['to_version'])
                                    <code class="ml-2 text-xs px-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">{{ $entry->payload['from_version'] }} → {{ $entry->payload['to_version'] }}</code>
                                @endif
                            </div>
                            <div class="shrink-0 text-xs text-gray-400 hidden sm:block">
                                {{ $entry->created_at->format('d.m H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- RAW MANIFEST (collapsible) --}}
        <details class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <summary class="cursor-pointer p-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center gap-2 select-none">
                <x-filament::icon icon="heroicon-o-code-bracket" class="w-4 h-4" />
                Raw <code class="font-mono">module.json</code>
            </summary>
            <pre class="p-4 text-xs bg-gray-50 dark:bg-gray-950 overflow-x-auto rounded-b-xl"><code>{{ json_encode($info['raw_manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
        </details>

        {{-- BACK LINK --}}
        <a href="{{ route('filament.admin.pages.modules') }}" class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:underline">
            <x-filament::icon icon="heroicon-o-arrow-left" class="w-4 h-4" />
            Назад до списку модулів
        </a>
    </div>
</x-filament-panels::page>
