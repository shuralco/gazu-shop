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
@endphp

<x-filament-panels::page>
<div class="-mt-2 space-y-6 max-w-5xl">

  {{-- ─── HEADER ─── --}}
  <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-3 mb-1.5 flex-wrap">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $info['name'] }}</h1>
        @if($info['enabled'])
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide rounded text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 ring-1 ring-inset ring-emerald-600/20">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
            Активний
          </span>
        @else
          <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide rounded text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800/60 ring-1 ring-inset ring-gray-500/20">
            Неактивний
          </span>
        @endif
      </div>
      <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2.5 font-mono">
        <span>{{ $info['key'] }}</span>
        @if($info['version'])<span class="text-gray-300 dark:text-gray-600">·</span><span>v{{ $info['version'] }}</span>@endif
        @if($info['author'])<span class="text-gray-300 dark:text-gray-600">·</span><span class="font-sans">{{ $info['author'] }}</span>@endif
      </div>
      @if($info['description'])
        <p class="text-[15px] text-gray-600 dark:text-gray-300 max-w-2xl leading-relaxed">{{ $info['description'] }}</p>
      @endif
    </div>

    <div class="flex gap-2 shrink-0">
      <button type="button" wire:click="clearModuleCache"
        wire:loading.attr="disabled" wire:target="clearModuleCache"
        title="Очистити кеш"
        class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-40">
        <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4" wire:loading.class="animate-spin" wire:target="clearModuleCache" />
      </button>
      @if($info['enabled'])
        <button type="button"
          wire:click="toggleModule"
          wire:confirm="Вимкнути модуль «{{ $info['name'] }}»? Дані залишаються у БД."
          wire:loading.attr="disabled" wire:target="toggleModule"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-40">
          <svg wire:loading wire:target="toggleModule" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
          <span wire:loading.remove wire:target="toggleModule">Вимкнути</span>
          <span wire:loading wire:target="toggleModule">Вимикаю…</span>
        </button>
      @else
        <button type="button" wire:click="toggleModule"
          wire:loading.attr="disabled" wire:target="toggleModule"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 transition-colors disabled:opacity-40">
          <svg wire:loading wire:target="toggleModule" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
          <span wire:loading.remove wire:target="toggleModule">Увімкнути</span>
          <span wire:loading wire:target="toggleModule">Вмикаю…</span>
        </button>
      @endif
    </div>
  </header>

  {{-- ─── STATS GRID (no boxes, clean inline) ─── --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
    @php
      $stats = [
        ['label'=>'Файлів','value'=>$info['file_count']],
        ['label'=>'Migrations','value'=>$info['migrations_count']],
        ['label'=>'Routes','value'=>$info['registered_routes']],
        ['label'=>'Filament','value'=>count($info['filament_resources'])+count($info['filament_pages'])+count($info['filament_widgets'])],
      ];
    @endphp
    @foreach($stats as $s)
      <div class="bg-white dark:bg-gray-900 px-4 py-3">
        <div class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">{{ $s['label'] }}</div>
        <div class="text-xl font-semibold text-gray-900 dark:text-white mt-0.5 tabular-nums">{{ $s['value'] }}</div>
      </div>
    @endforeach
  </div>

  {{-- ─── TWO COLUMN: dependencies + settings ─── --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- DEPENDENCIES --}}
    <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
      <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Залежності</h2>
      </div>
      <div class="p-5 space-y-4 text-sm">
        <div>
          <div class="text-xs text-gray-500 mb-1.5">Потребує</div>
          @if(empty($info['requires']))
            <span class="text-gray-400 text-sm">— нічого —</span>
          @else
            <div class="flex flex-wrap gap-1.5">
              @foreach($info['requires'] as $req)
                <a href="{{ url('/admin/modules/view?key='.$req) }}" class="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">{{ $req }}</a>
              @endforeach
            </div>
          @endif
        </div>
        <div>
          <div class="text-xs text-gray-500 mb-1.5">Від нього залежать</div>
          @if(empty($info['dependents']))
            <span class="text-gray-400 text-sm">— ніхто —</span>
          @else
            <div class="flex flex-wrap gap-1.5">
              @foreach($info['dependents'] as $dep => $depEnabled)
                <a href="{{ url('/admin/modules/view?key='.$dep) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                  @if($depEnabled)<span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>@endif
                  {{ $dep }}
                </a>
              @endforeach
            </div>
          @endif
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
          <div>
            <div class="text-gray-500">Default</div>
            <div class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $info['enabled_by_default'] ? 'on' : 'off' }}</div>
          </div>
          @if($info['enabled_at'])
            <div>
              <div class="text-gray-500">Увімкнено</div>
              <div class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ \Carbon\Carbon::parse($info['enabled_at'])->diffForHumans() }}</div>
            </div>
          @endif
        </div>
      </div>
    </section>

    {{-- SETTINGS --}}
    <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
      <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h2 class="text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Налаштування</h2>
        @if($info['has_settings'])
          <span class="text-xs text-gray-400">{{ count($info['settings_schema']) }}</span>
        @endif
      </div>
      <div class="p-5">
        @if(! $info['has_settings'])
          <div class="text-center py-6">
            <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-8 h-8 text-gray-300 dark:text-gray-700 mx-auto mb-2" />
            <p class="text-sm text-gray-500">Модуль не оголошує налаштувань</p>
            <p class="text-[11px] text-gray-400 mt-1">Додай <code class="font-mono">settings_schema</code> у <code class="font-mono">module.json</code></p>
          </div>
        @else
          <div class="space-y-3.5">
            @foreach($info['settings_schema'] as $settingKey => $schema)
              @php
                $type = $schema['type'] ?? 'string';
                $hasError = ! empty($this->settingsErrors[$settingKey]);
                $errorMsg = $this->settingsErrors[$settingKey] ?? null;
                $label = $schema['label'] ?? $settingKey;
                $help = $schema['help'] ?? null;
                $required = $schema['required'] ?? false;
                $inputClass = "w-full px-2.5 py-1.5 text-sm rounded-md bg-white dark:bg-gray-950/50 text-gray-900 dark:text-gray-100 placeholder-gray-400 ring-1 ring-inset focus:ring-2 focus:ring-primary-500 focus:outline-none transition-shadow ".($hasError ? 'ring-rose-400' : 'ring-gray-300 dark:ring-gray-700');
              @endphp
              <div>
                <div class="flex items-baseline justify-between mb-1">
                  <label class="text-[13px] font-medium text-gray-800 dark:text-gray-200">
                    {{ $label }}
                    @if($required)<span class="text-rose-500" title="обов'язкове">*</span>@endif
                  </label>
                  <span class="text-[10px] uppercase tracking-wide text-gray-400 font-mono">{{ $type }}</span>
                </div>
                @if($type === 'bool')
                  <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="settings.{{ $settingKey }}" class="rounded text-primary-600 focus:ring-primary-500" />
                    <span class="text-xs text-gray-500">Увімкнено</span>
                  </label>
                @elseif($type === 'int' || $type === 'float')
                  <input type="number" wire:model="settings.{{ $settingKey }}"
                    @if(isset($schema['min']))min="{{ $schema['min'] }}"@endif
                    @if(isset($schema['max']))max="{{ $schema['max'] }}"@endif
                    @if($type === 'float') step="0.01" @endif
                    placeholder="{{ $schema['default'] ?? '' }}" class="{{ $inputClass }}" />
                @elseif(! empty($schema['enum']))
                  <select wire:model="settings.{{ $settingKey }}" class="{{ $inputClass }}">
                    <option value="">— оберіть —</option>
                    @foreach($schema['enum'] as $opt)
                      <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                  </select>
                @else
                  <input type="text" wire:model="settings.{{ $settingKey }}" placeholder="{{ $schema['default'] ?? '' }}" class="{{ $inputClass }}" />
                @endif
                @if($hasError)
                  <p class="text-[11px] text-rose-600 mt-1 flex items-center gap-1">
                    <x-filament::icon icon="heroicon-o-exclamation-circle" class="w-3 h-3" />
                    {{ $errorMsg }}
                  </p>
                @elseif($help)
                  <p class="text-[11px] text-gray-500 mt-1">{{ $help }}</p>
                @endif
              </div>
            @endforeach
            <div class="flex gap-2 pt-2">
              <button type="button" wire:click="saveSettings"
                wire:loading.attr="disabled" wire:target="saveSettings"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 transition-colors disabled:opacity-40">
                <svg wire:loading wire:target="saveSettings" class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                <span wire:loading.remove wire:target="saveSettings">Зберегти</span>
                <span wire:loading wire:target="saveSettings">Зберігаю…</span>
              </button>
              <button type="button" wire:click="resetSettings"
                wire:confirm="Скинути всі налаштування до значень з manifest?"
                class="px-3 py-1.5 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Скинути
              </button>
            </div>
          </div>
        @endif
      </div>
    </section>
  </div>

  {{-- ─── HEALTH CHECKS ─── --}}
  <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
      <h2 class="flex items-center gap-2 text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
        @if($overallHealth==='ok')
          <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
        @elseif($overallHealth==='warning')
          <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
        @else
          <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
        @endif
        Health
      </h2>
      <div class="flex items-center gap-1.5 text-[11px] font-mono text-gray-500">
        @if($healthCounts['ok'] > 0)<span class="text-emerald-600 dark:text-emerald-400">{{ $healthCounts['ok'] }}✓</span>@endif
        @if($healthCounts['warning'] > 0)<span class="text-amber-600 dark:text-amber-400">{{ $healthCounts['warning'] }}!</span>@endif
        @if($healthCounts['error'] > 0)<span class="text-rose-600 dark:text-rose-400">{{ $healthCounts['error'] }}✕</span>@endif
      </div>
    </div>
    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
      @foreach($health as $check)
        <li class="flex items-center gap-3 px-5 py-2.5">
          <div class="shrink-0">
            @if($check['status']==='ok')
              <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
            @elseif($check['status']==='warning')
              <div class="w-1.5 h-1.5 bg-amber-500 rounded-full"></div>
            @else
              <div class="w-1.5 h-1.5 bg-rose-500 rounded-full"></div>
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <span class="text-[13px] text-gray-900 dark:text-gray-100">{{ $check['label'] }}</span>
            @if($check['detail'])
              <span class="text-[12px] text-gray-500 ml-1.5">— {{ $check['detail'] }}</span>
            @endif
          </div>
        </li>
      @endforeach
    </ul>
  </section>

  {{-- ─── ACTIVITY ─── --}}
  <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
    <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
      <h2 class="text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Активність</h2>
      @if($activity->count() > 0)<span class="text-xs text-gray-400">{{ $activity->count() }}</span>@endif
    </div>
    @if($activity->count() === 0)
      <div class="px-5 py-8 text-center">
        <x-filament::icon icon="heroicon-o-clock" class="w-7 h-7 text-gray-300 dark:text-gray-700 mx-auto mb-1.5" />
        <p class="text-sm text-gray-500">Поки що порожньо</p>
        <p class="text-[11px] text-gray-400 mt-0.5">Зміни модуля з'являться тут</p>
      </div>
    @else
      <ul class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($activity as $entry)
          <li class="px-5 py-2.5 flex items-center gap-3 text-sm">
            <time class="shrink-0 text-[11px] text-gray-400 font-mono w-20 tabular-nums">{{ $entry->created_at->diffForHumans() }}</time>
            <div class="flex-1 min-w-0">
              <span class="text-gray-900 dark:text-gray-100">{{ $actionLabels[$entry->action] ?? $entry->action }}</span>
              @if($entry->user_id)<span class="text-[11px] text-gray-400 ml-1.5">user #{{ $entry->user_id }}</span>@endif
              @if(! empty($entry->payload['from_version']) && ! empty($entry->payload['to_version']) && $entry->payload['from_version'] !== $entry->payload['to_version'])
                <code class="ml-1.5 text-[11px] px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded font-mono">{{ $entry->payload['from_version'] }} → {{ $entry->payload['to_version'] }}</code>
              @endif
            </div>
            <span class="hidden sm:inline text-[11px] text-gray-400 font-mono">{{ $entry->created_at->format('d.m H:i') }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </section>

  {{-- ─── MANIFEST + FILES (two-column compact) ─── --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
      <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Manifest</h2>
      </div>
      <div class="p-5 space-y-3 text-sm">
        @foreach([
          'providers' => 'Service providers',
          'filament_resources' => 'Resources',
          'filament_pages' => 'Pages',
          'filament_widgets' => 'Widgets',
          'composer_packages' => 'Composer пакети',
        ] as $field => $label)
          @if(! empty($info[$field]))
            <div>
              <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-1.5">{{ $label }} <span class="text-gray-400">{{ count($info[$field]) }}</span></div>
              <div class="space-y-1">
                @foreach($info[$field] as $cls)
                  <code class="block text-[11px] px-2 py-1 bg-gray-50 dark:bg-gray-950/50 text-gray-700 dark:text-gray-300 rounded font-mono break-all">{{ $cls }}</code>
                @endforeach
              </div>
            </div>
          @endif
        @endforeach
        @if($info['views_namespace'])
          <div>
            <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-1.5">Views namespace</div>
            <code class="text-[11px] px-2 py-1 bg-gray-50 dark:bg-gray-950/50 rounded font-mono text-gray-700 dark:text-gray-300">{{ $info['views_namespace'] }}::view-name</code>
          </div>
        @endif
      </div>
    </section>

    <section class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
      <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Файли</h2>
      </div>
      <div class="p-5 text-sm space-y-3">
        <div>
          <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-1.5">Шлях</div>
          <code class="block text-[11px] px-2 py-1 bg-gray-50 dark:bg-gray-950/50 text-gray-700 dark:text-gray-300 rounded font-mono break-all">{{ str_replace(base_path().'/', '', $info['module_path']) }}</code>
          <span class="text-[11px] text-gray-500 mt-1 block">{{ $info['folder_exists'] ? '✓ існує' : '✗ відсутня' }} · {{ $info['file_count'] }} файлів</span>
        </div>
        @if($info['migrations_count'] > 0)
          <details>
            <summary class="cursor-pointer text-[11px] uppercase tracking-wide text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 select-none flex items-center gap-1.5">
              <x-filament::icon icon="heroicon-o-chevron-right" class="w-3 h-3 group-open:rotate-90 transition-transform" />
              Migrations <span class="text-gray-400">{{ $info['migrations_count'] }}</span>
            </summary>
            <div class="mt-2 space-y-1">
              @foreach($info['migrations'] as $mig)
                <code class="block text-[11px] px-2 py-1 bg-gray-50 dark:bg-gray-950/50 text-gray-700 dark:text-gray-300 rounded font-mono break-all">{{ $mig }}</code>
              @endforeach
              <button type="button" wire:click="runMigrations"
                wire:loading.attr="disabled" wire:target="runMigrations"
                class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg wire:loading wire:target="runMigrations" class="animate-spin w-3 h-3" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path></svg>
                Запустити migrate
              </button>
            </div>
          </details>
        @endif
      </div>
    </section>
  </div>

  {{-- ─── RAW MANIFEST ─── --}}
  <details class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 group">
    <summary class="cursor-pointer px-5 py-3 text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors flex items-center gap-2 select-none rounded-lg">
      <x-filament::icon icon="heroicon-o-chevron-right" class="w-3.5 h-3.5 group-open:rotate-90 transition-transform" />
      module.json
    </summary>
    <pre class="px-5 pb-4 text-[11px] text-gray-700 dark:text-gray-400 overflow-x-auto font-mono leading-relaxed"><code>{{ json_encode($info['raw_manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
  </details>

  {{-- BACK --}}
  <a href="{{ route('filament.admin.pages.modules') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
    <x-filament::icon icon="heroicon-o-arrow-left" class="w-3.5 h-3.5" />
    Усі модулі
  </a>
</div>
</x-filament-panels::page>
