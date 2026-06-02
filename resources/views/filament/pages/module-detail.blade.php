@php
$info = $this->getModuleInfo();
$health = $this->getHealthChecks();
$activity = $this->getRecentActivity(10);
$healthCounts = ['ok'=>0,'warning'=>0,'error'=>0];
foreach($health as $h) { $healthCounts[$h['status']] = ($healthCounts[$h['status']] ?? 0) + 1; }
$overallHealth = $healthCounts['error'] > 0 ? 'error' : ($healthCounts['warning'] > 0 ? 'warning' : 'ok');
$actionLabels = [
  'enabled' => 'Увімкнено',
  'disabled' => 'Вимкнено',
  'settings_saved' => 'Налаштування збережено',
  'install' => 'Встановлено',
  'upgrade' => 'Оновлено',
  'uninstall' => 'Видалено',
];
$inputClass = 'fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10';
@endphp

<x-filament-panels::page>
    {{-- ─── HEADER ─── --}}
    <x-filament::section>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2" style="flex:1 1 0%">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $info['name'] }}</h2>
                    @if($info['enabled'])
                        <x-filament::badge color="success" icon="heroicon-m-check-circle">Активний</x-filament::badge>
                    @else
                        <x-filament::badge color="gray" icon="heroicon-m-pause-circle">Неактивний</x-filament::badge>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-mono text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $info['key'] }}</span>
                    @if($info['version'])<span class="text-gray-300 dark:text-gray-600">·</span><span>v{{ $info['version'] }}</span>@endif
                    @if($info['author'])<span class="text-gray-300 dark:text-gray-600">·</span><span class="font-sans">{{ $info['author'] }}</span>@endif
                </div>

                @if($info['description'])
                    <p class="max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $info['description'] }}</p>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <x-filament::icon-button
                    icon="heroicon-o-arrow-path"
                    wire:click="clearModuleCache"
                    wire:loading.attr="disabled" wire:target="clearModuleCache"
                    label="Очистити кеш"
                    color="gray"
                    size="lg" />

                @if($info['enabled'])
                    <x-filament::button
                        wire:click="toggleModule"
                        wire:confirm="Вимкнути модуль «{{ $info['name'] }}»? Дані залишаються у БД."
                        wire:loading.attr="disabled" wire:target="toggleModule"
                        color="danger"
                        icon="heroicon-o-power">
                        <span wire:loading.remove wire:target="toggleModule">Вимкнути</span>
                        <span wire:loading wire:target="toggleModule">Вимикаю…</span>
                    </x-filament::button>
                @else
                    <x-filament::button
                        wire:click="toggleModule"
                        wire:loading.attr="disabled" wire:target="toggleModule"
                        color="primary"
                        icon="heroicon-o-bolt">
                        <span wire:loading.remove wire:target="toggleModule">Увімкнути</span>
                        <span wire:loading wire:target="toggleModule">Вмикаю…</span>
                    </x-filament::button>
                @endif
            </div>
        </div>

        {{-- STATS --}}
        <div class="mt-5 overflow-hidden rounded-lg bg-gray-200 dark:bg-white/10" style="display:grid;gap:1px;grid-template-columns:repeat(auto-fit,minmax(110px,1fr))">
            @php
                $stats = [
                    ['label'=>'Файлів','value'=>$info['file_count']],
                    ['label'=>'Migrations','value'=>$info['migrations_count']],
                    ['label'=>'Routes','value'=>$info['registered_routes']],
                    ['label'=>'Filament','value'=>count($info['filament_resources'])+count($info['filament_pages'])+count($info['filament_widgets'])],
                    ['label'=>'Hooks','value'=>count($info['hook_events'] ?? [])],
                ];
            @endphp
            @foreach($stats as $s)
                <div class="bg-white px-4 py-3 dark:bg-gray-900">
                    <div class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $s['label'] }}</div>
                    <div class="mt-0.5 text-xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ $s['value'] }}</div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    {{-- ─── DEPENDENCIES + SETTINGS ─── --}}
    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">

        {{-- DEPENDENCIES --}}
        <x-filament::section icon="heroicon-o-link" icon-color="gray">
            <x-slot name="heading">Залежності</x-slot>

            <div class="space-y-4 text-sm">
                <div>
                    <div class="mb-1.5 text-xs text-gray-500">Потребує</div>
                    @if(empty($info['requires']))
                        <span class="text-sm text-gray-400">— нічого —</span>
                    @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($info['requires'] as $req)
                                <a href="{{ url('/admin/modules/view?key='.$req) }}">
                                    <x-filament::badge color="warning">{{ $req }}</x-filament::badge>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <div class="mb-1.5 text-xs text-gray-500">Від нього залежать</div>
                    @if(empty($info['dependents']))
                        <span class="text-sm text-gray-400">— ніхто —</span>
                    @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($info['dependents'] as $dep => $depEnabled)
                                <a href="{{ url('/admin/modules/view?key='.$dep) }}">
                                    <x-filament::badge :color="$depEnabled ? 'success' : 'gray'">{{ $dep }}</x-filament::badge>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 text-xs dark:border-white/5">
                    <div>
                        <div class="text-gray-500">За замовчуванням</div>
                        <div class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ $info['enabled_by_default'] ? 'увімкнено' : 'вимкнено' }}</div>
                    </div>
                    @if($info['enabled_at'])
                        <div>
                            <div class="text-gray-500">Увімкнено</div>
                            <div class="mt-0.5 font-medium text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($info['enabled_at'])->diffForHumans() }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- SETTINGS --}}
        <x-filament::section icon="heroicon-o-cog-6-tooth" icon-color="gray">
            <x-slot name="heading">Налаштування</x-slot>
            @if($info['has_settings'])
                <x-slot name="headerEnd">
                    <x-filament::badge color="gray">{{ count($info['settings_schema']) }}</x-filament::badge>
                </x-slot>
            @endif

            @if(! $info['has_settings'])
                <div class="py-6 text-center">
                    <x-filament::icon icon="heroicon-o-cog-6-tooth" class="mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-gray-700" />
                    <p class="text-sm text-gray-500">Модуль не оголошує налаштувань</p>
                    <p class="mt-1 text-xs text-gray-400">Додай <code class="font-mono">settings_schema</code> у <code class="font-mono">module.json</code></p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($info['settings_schema'] as $settingKey => $schema)
                        @php
                            $type = $schema['type'] ?? 'string';
                            $hasError = ! empty($this->settingsErrors[$settingKey]);
                            $errorMsg = $this->settingsErrors[$settingKey] ?? null;
                            $label = $schema['label'] ?? $settingKey;
                            $help = $schema['help'] ?? null;
                            $required = $schema['required'] ?? false;
                            $fieldClass = $inputClass . ($hasError ? ' ring-danger-500 dark:ring-danger-500' : '');
                        @endphp
                        <div>
                            <div class="mb-1 flex items-baseline justify-between">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{ $label }}
                                    @if($required)<span class="text-danger-500" title="обов'язкове">*</span>@endif
                                </label>
                                <span class="font-mono text-[10px] uppercase tracking-wide text-gray-400">{{ $type }}</span>
                            </div>
                            @if($type === 'bool')
                                <label class="inline-flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" wire:model="settings.{{ $settingKey }}" class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 checked:ring-0 dark:bg-white/5 dark:ring-white/20" />
                                    <span class="text-xs text-gray-500">Увімкнено</span>
                                </label>
                            @elseif($type === 'int' || $type === 'float')
                                <input type="number" wire:model="settings.{{ $settingKey }}"
                                    @if(isset($schema['min']))min="{{ $schema['min'] }}"@endif
                                    @if(isset($schema['max']))max="{{ $schema['max'] }}"@endif
                                    @if($type === 'float') step="0.01" @endif
                                    placeholder="{{ $schema['default'] ?? '' }}" class="{{ $fieldClass }}" />
                            @elseif(! empty($schema['enum']))
                                <select wire:model="settings.{{ $settingKey }}" class="{{ $fieldClass }}">
                                    <option value="">— оберіть —</option>
                                    @foreach($schema['enum'] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" wire:model="settings.{{ $settingKey }}" placeholder="{{ $schema['default'] ?? '' }}" class="{{ $fieldClass }}" />
                            @endif
                            @if($hasError)
                                <p class="mt-1 flex items-center gap-1 text-xs text-danger-600 dark:text-danger-400">
                                    <x-filament::icon icon="heroicon-o-exclamation-circle" class="h-3 w-3" />
                                    {{ $errorMsg }}
                                </p>
                            @elseif($help)
                                <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
                            @endif
                        </div>
                    @endforeach
                    <div class="flex gap-2 pt-1">
                        <x-filament::button
                            wire:click="saveSettings"
                            wire:loading.attr="disabled" wire:target="saveSettings"
                            color="primary" size="sm" icon="heroicon-o-check">
                            <span wire:loading.remove wire:target="saveSettings">Зберегти</span>
                            <span wire:loading wire:target="saveSettings">Зберігаю…</span>
                        </x-filament::button>
                        <x-filament::button
                            wire:click="resetSettings"
                            wire:confirm="Скинути всі налаштування до значень з manifest?"
                            color="gray" size="sm" outlined>
                            Скинути
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- ─── HEALTH CHECKS ─── --}}
    <x-filament::section
        :icon="$overallHealth==='ok' ? 'heroicon-o-check-circle' : ($overallHealth==='warning' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle')"
        :icon-color="$overallHealth==='ok' ? 'success' : ($overallHealth==='warning' ? 'warning' : 'danger')"
    >
        <x-slot name="heading">Стан здоров'я</x-slot>
        <x-slot name="headerEnd">
            <div class="flex items-center gap-1.5">
                @if($healthCounts['ok'] > 0)<x-filament::badge color="success" size="sm">{{ $healthCounts['ok'] }}</x-filament::badge>@endif
                @if($healthCounts['warning'] > 0)<x-filament::badge color="warning" size="sm">{{ $healthCounts['warning'] }}</x-filament::badge>@endif
                @if($healthCounts['error'] > 0)<x-filament::badge color="danger" size="sm">{{ $healthCounts['error'] }}</x-filament::badge>@endif
            </div>
        </x-slot>

        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($health as $check)
                <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <span @class([
                        'h-2 w-2 shrink-0 rounded-full',
                        'bg-success-500' => $check['status']==='ok',
                        'bg-warning-500' => $check['status']==='warning',
                        'bg-danger-500' => $check['status']==='error',
                    ])></span>
                    <div class="min-w-0" style="flex:1 1 0%">
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $check['label'] }}</span>
                        @if($check['detail'])
                            <span class="ml-1.5 text-xs text-gray-500">— {{ $check['detail'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </x-filament::section>

    {{-- ─── ACTIVITY ─── --}}
    <x-filament::section icon="heroicon-o-clock" icon-color="gray">
        <x-slot name="heading">Активність</x-slot>
        @if($activity->count() > 0)
            <x-slot name="headerEnd">
                <x-filament::badge color="gray">{{ $activity->count() }}</x-filament::badge>
            </x-slot>
        @endif

        @if($activity->count() === 0)
            <div class="py-8 text-center">
                <x-filament::icon icon="heroicon-o-clock" class="mx-auto mb-1.5 h-7 w-7 text-gray-300 dark:text-gray-700" />
                <p class="text-sm text-gray-500">Поки що порожньо</p>
                <p class="mt-0.5 text-xs text-gray-400">Зміни модуля з'являться тут</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($activity as $entry)
                    <li class="flex items-center gap-3 py-2.5 text-sm first:pt-0 last:pb-0">
                        <time class="w-20 shrink-0 font-mono text-xs tabular-nums text-gray-400">{{ $entry->created_at->diffForHumans() }}</time>
                        <div class="min-w-0" style="flex:1 1 0%">
                            <span class="text-gray-900 dark:text-gray-100">{{ $actionLabels[$entry->action] ?? $entry->action }}</span>
                            @if($entry->user_id)<span class="ml-1.5 text-xs text-gray-400">user #{{ $entry->user_id }}</span>@endif
                            @if(! empty($entry->payload['from_version']) && ! empty($entry->payload['to_version']) && $entry->payload['from_version'] !== $entry->payload['to_version'])
                                <code class="ml-1.5 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-600 dark:bg-white/10 dark:text-gray-400">{{ $entry->payload['from_version'] }} → {{ $entry->payload['to_version'] }}</code>
                            @endif
                        </div>
                        <span class="hidden font-mono text-xs text-gray-400 sm:inline">{{ $entry->created_at->format('d.m H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>

    {{-- ─── MANIFEST + HOOKS + FILES ─── --}}
    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">

        <x-filament::section icon="heroicon-o-document-text" icon-color="gray">
            <x-slot name="heading">Manifest</x-slot>

            <div class="space-y-3 text-sm">
                @foreach([
                    'providers' => 'Service providers',
                    'filament_resources' => 'Resources',
                    'filament_pages' => 'Pages',
                    'filament_widgets' => 'Widgets',
                    'composer_packages' => 'Composer пакети',
                ] as $field => $label)
                    @if(! empty($info[$field]))
                        <div>
                            <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500">{{ $label }} <span class="text-gray-400">{{ count($info[$field]) }}</span></div>
                            <div class="space-y-1">
                                @foreach($info[$field] as $cls)
                                    <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $cls }}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                @if($info['views_namespace'])
                    <div>
                        <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500">Views namespace</div>
                        <code class="rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $info['views_namespace'] }}::view-name</code>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- HOOKS --}}
        @if(! empty($info['hook_events']))
            <x-filament::section icon="heroicon-o-bolt" icon-color="warning">
                <x-slot name="heading">Hook subscriptions</x-slot>
                <x-slot name="description">На які core-events модуль підписаний</x-slot>

                <ul class="space-y-1.5">
                    @foreach($info['hook_events'] as $event)
                        @php $listeners = \App\Support\Hooks::listenersFor($event); @endphp
                        <li class="flex items-baseline gap-3 text-xs">
                            <code class="rounded bg-amber-50 px-1.5 py-0.5 font-mono text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">{{ $event }}</code>
                            @php $myEntry = collect($listeners)->firstWhere('source', $info['key']); @endphp
                            @if($myEntry)
                                <span class="text-xs text-gray-500">{{ $myEntry['type'] }} · priority {{ $myEntry['priority'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs leading-relaxed text-gray-500">
                    Модуль слухає ці події в core. Якщо вимкнути модуль — listener'и зникають,
                    core працює без них (graceful degradation).
                </p>
            </x-filament::section>
        @endif

        {{-- FILES --}}
        <x-filament::section icon="heroicon-o-folder" icon-color="gray">
            <x-slot name="heading">Файли</x-slot>

            <div class="space-y-3 text-sm">
                <div>
                    <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500">Шлях</div>
                    <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ str_replace(base_path().'/', '', $info['module_path']) }}</code>
                    <span class="mt-1 block text-[11px] text-gray-500">{{ $info['folder_exists'] ? '✓ існує' : '✗ відсутня' }} · {{ $info['file_count'] }} файлів</span>
                </div>
                @if($info['migrations_count'] > 0)
                    <details class="group">
                        <summary class="flex cursor-pointer select-none items-center gap-1.5 text-[11px] uppercase tracking-wide text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                            Migrations <span class="text-gray-400">{{ $info['migrations_count'] }}</span>
                        </summary>
                        <div class="mt-2 space-y-1">
                            @foreach($info['migrations'] as $mig)
                                <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $mig }}</code>
                            @endforeach
                            <div class="pt-1">
                                <x-filament::button
                                    wire:click="runMigrations"
                                    wire:loading.attr="disabled" wire:target="runMigrations"
                                    color="gray" size="sm" outlined icon="heroicon-o-play">
                                    <span wire:loading.remove wire:target="runMigrations">Запустити migrate</span>
                                    <span wire:loading wire:target="runMigrations">Виконую…</span>
                                </x-filament::button>
                            </div>
                        </div>
                    </details>
                @endif
            </div>
        </x-filament::section>
    </div>

    {{-- ─── DEBUG ─── --}}
    <x-filament::section icon="heroicon-o-bug-ant" icon-color="gray">
        <x-slot name="heading">Debug info</x-slot>
        <x-slot name="description">Файли · routes · DB tables · hooks · env. Підвантажується при потребі.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button
                wire:click="toggleDebug"
                wire:loading.attr="disabled" wire:target="toggleDebug"
                color="gray" size="sm" outlined
                :icon="$showDebug ? 'heroicon-o-eye-slash' : 'heroicon-o-eye'">
                {{ $showDebug ? 'Сховати' : 'Показати' }}
            </x-filament::button>
        </x-slot>

        @if($showDebug)
            @php $debug = $this->getDebugInfo(); @endphp
            <div class="-mx-6 -mb-6 divide-y divide-gray-100 border-t border-gray-200 dark:divide-white/5 dark:border-white/10">

                {{-- 1. File tree --}}
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        Файлове дерево
                        <span class="ml-1 text-gray-400">{{ $debug['file_tree_total'] }}</span>
                        @if($debug['file_tree_total'] === 40)<span class="ml-1 text-[10px] text-amber-600">(перші 40)</span>@endif
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        <div class="space-y-0.5 font-mono text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">
                            @foreach($debug['file_tree'] as $f)
                                <div class="truncate">{{ $f }}</div>
                            @endforeach
                        </div>
                    </div>
                </details>

                {{-- 2. Routes --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        Routes зареєстровано
                        <span class="ml-1 text-gray-400">{{ count($debug['routes']) }}</span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        @if(empty($debug['routes']))
                            <p class="text-[11px] text-gray-500">Жодного route не зареєстровано через цей модуль.</p>
                        @else
                            <div class="space-y-1 font-mono text-[11px]">
                                @foreach($debug['routes'] as $r)
                                    <div class="flex items-center gap-2">
                                        <span class="w-16 shrink-0 text-gray-500">{{ $r['method'] }}</span>
                                        <span class="flex-1 truncate text-gray-900 dark:text-gray-100">{{ $r['uri'] }}</span>
                                        <span class="shrink-0 text-gray-400">{{ $r['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>

                {{-- 3. DB Tables --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        DB-таблиці + кількість рядків
                        <span class="ml-1 text-gray-400">{{ count($debug['table_counts']) }}</span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        @if(empty($debug['table_counts']))
                            <p class="text-[11px] text-gray-500">Таблиць з ім'ям, що містить «{{ $info['key'] }}», не знайдено.</p>
                        @else
                            <div class="space-y-0.5 font-mono text-[11px]">
                                @foreach($debug['table_counts'] as $table => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-900 dark:text-gray-100">{{ $table }}</span>
                                        <span class="tabular-nums text-gray-500">{{ $count }} rows</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>

                {{-- 4. Hook listeners --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        Hooks listeners (global)
                        <span class="ml-1 text-gray-400">{{ count($debug['hook_listeners']) }}</span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        @if(empty($debug['hook_listeners']))
                            <p class="text-[11px] text-gray-500">Жодного слухача не зареєстровано через Hooks API.</p>
                        @else
                            <div class="space-y-0.5 font-mono text-[11px]">
                                @foreach($debug['hook_listeners'] as $event => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-900 dark:text-gray-100">{{ $event }}</span>
                                        <span class="tabular-nums text-gray-500">{{ $count }} listener{{ $count === 1 ? '' : 's' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>

                {{-- 5. PHP class loaded check --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        PHP class_exists перевірка
                    </summary>
                    <div class="space-y-2 bg-gray-50/50 px-6 py-3 font-mono text-[11px] dark:bg-white/5">
                        @foreach(['providers' => 'Providers', 'resources' => 'Filament resources'] as $field => $label)
                            @if(! empty($debug['php_class_loaded_check'][$field]))
                                <div>
                                    <div class="mb-1 text-gray-500">{{ $label }}</div>
                                    @foreach($debug['php_class_loaded_check'][$field] as $check)
                                        <div class="flex items-center gap-2">
                                            <span @class([
                                                'block h-1.5 w-1.5 shrink-0 rounded-full',
                                                'bg-success-500' => $check['exists'],
                                                'bg-danger-500' => ! $check['exists'],
                                            ])></span>
                                            <span class="break-all text-gray-900 dark:text-gray-100">{{ $check['class'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                        <div class="border-t border-gray-200 pt-2 dark:border-white/10">
                            <span class="text-gray-500">Composer classmap matches:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $debug['composer_classmap_check']['matches'] ?? '?' }}</span>
                        </div>
                    </div>
                </details>

                {{-- 6. ENV vars --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        ENV
                    </summary>
                    <div class="space-y-0.5 bg-gray-50/50 px-6 py-3 font-mono text-[11px] dark:bg-white/5">
                        @foreach($debug['env_vars'] as $envKey => $envVal)
                            <div class="flex items-baseline gap-2">
                                <span class="w-44 shrink-0 text-gray-500">{{ $envKey }}</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $envVal === null ? '(unset)' : var_export($envVal, true) }}</span>
                            </div>
                        @endforeach
                    </div>
                </details>

                {{-- 7. Raw manifest --}}
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3 transition-transform group-open:rotate-90" />
                        module.json (raw)
                    </summary>
                    <pre class="overflow-x-auto bg-gray-50/50 px-6 py-3 font-mono text-[11px] leading-relaxed text-gray-700 dark:bg-white/5 dark:text-gray-400"><code>{{ json_encode($debug['manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </details>
            </div>
        @endif
    </x-filament::section>

    {{-- BACK --}}
    <div>
        <x-filament::link tag="a" href="{{ route('filament.admin.pages.modules') }}" icon="heroicon-o-arrow-left" color="gray">
            Усі модулі
        </x-filament::link>
    </div>
</x-filament-panels::page>
