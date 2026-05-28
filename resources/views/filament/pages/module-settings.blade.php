<x-filament-panels::page>
<div class="-mt-2 space-y-6 max-w-6xl">

  {{-- ─── HEADER ─── --}}
  <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 pb-5 border-b border-gray-200 dark:border-gray-800">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white mb-1">Модулі</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl leading-relaxed">
        Опційні фічі магазину — вмикай/вимикай миттєво без redeploy. Стан зберігається у БД, дані модулів залишаються при disable.
      </p>
    </div>
    <div class="flex items-center gap-3 text-[11px] uppercase tracking-wide text-gray-500 font-mono">
      <span class="inline-flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
        {{ \App\Support\ModuleManager::all()->filter(fn($m)=>$m->enabled())->count() }} активних
      </span>
      <span class="text-gray-300 dark:text-gray-700">·</span>
      <span>{{ \App\Support\ModuleManager::all()->count() }} всього</span>
    </div>
  </header>

  {{-- ─── INSTALL FROM ZIP ─── --}}
  <section x-data="{ open: false }" class="border border-dashed border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-900/60 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors cursor-pointer">
      <div class="flex items-center gap-2.5">
        <x-filament::icon icon="heroicon-o-arrow-up-tray" class="w-4 h-4 text-gray-500" />
        <span class="text-[13px] font-semibold text-gray-700 dark:text-gray-300">Встановити модуль з .zip</span>
      </div>
      <x-filament::icon icon="heroicon-o-chevron-down" class="w-4 h-4 text-gray-400 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
    </button>
    <div x-show="open" x-cloak x-transition.opacity class="p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
      <div class="space-y-3 max-w-2xl">
        <div>
          <input type="file"
                 wire:model="installZip"
                 accept=".zip"
                 class="block w-full text-sm text-gray-700 dark:text-gray-300
                        file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0
                        file:text-[12px] file:font-medium
                        file:bg-gray-900 dark:file:bg-white file:text-white dark:file:text-gray-900
                        hover:file:bg-gray-800 dark:hover:file:bg-gray-100 cursor-pointer"/>
          <p class="mt-1.5 text-[11px] text-gray-500">
            Очікується ZIP з <code class="font-mono">module.json</code> у корені (або у єдиній обгортковій папці). Ліміт: 10&nbsp;MB.
          </p>
        </div>

        @if($installZip)
          <div class="flex items-center gap-2 text-[12px] text-gray-600 dark:text-gray-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-md px-3 py-2">
            <x-filament::icon icon="heroicon-o-document-arrow-up" class="w-4 h-4 text-emerald-600" />
            Файл готовий до встановлення
          </div>
        @endif

        <label class="flex items-center gap-2 text-[12px] text-gray-600 dark:text-gray-400 cursor-pointer">
          <input type="checkbox" wire:model="installForce"
                 class="rounded border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-gray-900 dark:focus:ring-white"/>
          Перезаписати, якщо модуль вже встановлено
        </label>

        <div class="flex items-center gap-2 pt-1">
          <button type="button" wire:click="installFromZip"
                  wire:loading.attr="disabled" wire:target="installFromZip,installZip"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-medium rounded bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 transition-colors disabled:opacity-50 disabled:cursor-wait">
            <span wire:loading.remove wire:target="installFromZip">Встановити</span>
            <span wire:loading wire:target="installFromZip">Встановлюю…</span>
          </button>
          @if($installZip)
            <button type="button" wire:click="$set('installZip', null)"
                    class="text-[12px] text-gray-500 hover:text-gray-900 dark:hover:text-gray-100">
              Скасувати
            </button>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- ─── GROUPS ─── --}}
  @foreach($this->getGroupedModules() as $groupKey => $group)
    <section>
      <div class="flex items-center justify-between mb-3 px-1">
        <h2 class="flex items-center gap-2 text-[13px] font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
          <x-filament::icon :icon="$group['icon']" class="w-4 h-4 text-gray-400" />
          {{ $group['label'] }}
        </h2>
        <span class="text-[11px] text-gray-400 font-mono">{{ count($group['modules']) }}</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($group['modules'] as $m)
          <article class="group relative bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 hover:shadow-sm transition-all flex flex-col"
                   x-data="{ showDelete: false, deleteMode: 'soft', showDisable: false, rollbackMigrations: false }">

            {{-- Status dot (top-left absolute) --}}
            <div class="absolute top-3 right-3">
              @if($m['enabled'])
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full block" title="Активний"></span>
              @else
                <span class="w-1.5 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full block" title="Неактивний"></span>
              @endif
            </div>

            <div class="p-4 flex-1 flex flex-col">
              {{-- Header --}}
              <div class="mb-2 pr-5">
                <h3 class="text-[15px] font-semibold text-gray-900 dark:text-white leading-tight mb-0.5">{{ $m['name'] }}</h3>
                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 font-mono">
                  <span>{{ $m['key'] }}</span>
                  @if($m['version'])
                    <span class="text-gray-300 dark:text-gray-700">·</span>
                    <span>v{{ $m['version'] }}</span>
                  @endif
                </div>
              </div>

              {{-- Description --}}
              @if($m['description'])
                <p class="text-[13px] text-gray-600 dark:text-gray-400 leading-relaxed mb-3 line-clamp-3">{{ $m['description'] }}</p>
              @endif

              {{-- Deps inline --}}
              @if(! empty($m['requires']) || ! empty($m['dependents']))
                <div class="text-[11px] mb-3 space-y-1">
                  @if(! empty($m['requires']))
                    <div class="flex items-baseline gap-1.5 flex-wrap">
                      <span class="text-gray-400">потребує:</span>
                      @foreach($m['requires'] as $req)
                        <code class="font-mono text-gray-600 dark:text-gray-400">{{ $req }}</code>
                      @endforeach
                    </div>
                  @endif
                  @if(! empty($m['dependents']))
                    <div class="flex items-baseline gap-1.5 flex-wrap">
                      <span class="text-amber-600 dark:text-amber-500">потрібен для:</span>
                      @foreach($m['dependents'] as $dep)
                        <code class="font-mono text-gray-600 dark:text-gray-400">{{ $dep }}</code>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endif

              {{-- Actions --}}
              <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-2">
                <a href="{{ url('/admin/modules/view?key='.$m['key']) }}"
                   class="text-[12px] text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 transition-colors inline-flex items-center gap-1 group/details">
                  Деталі
                  <x-filament::icon icon="heroicon-o-arrow-up-right" class="w-3 h-3 group-hover/details:translate-x-0.5 group-hover/details:-translate-y-0.5 transition-transform" />
                </a>

                @if($m['in_modules_dir'] ?? false)
                  <button type="button"
                          wire:click="exportModule('{{ $m['key'] }}')"
                          wire:loading.attr="disabled" wire:target="exportModule('{{ $m['key'] }}')"
                          title="Завантажити модуль як ZIP"
                          class="text-[12px] text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 transition-colors inline-flex items-center gap-1 disabled:opacity-40 disabled:cursor-wait">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-3 h-3" />
                    .zip
                  </button>

                  @if(! $m['enabled'])
                    <button type="button"
                            @click="showDelete = true; deleteMode = 'soft'"
                            title="Видалити модуль"
                            class="text-[12px] text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors inline-flex items-center gap-1">
                      <x-filament::icon icon="heroicon-o-trash" class="w-3 h-3" />
                    </button>
                  @endif
                @endif

                <div class="ml-auto">
                  @if($m['enabled'])
                    <button type="button"
                      @click="showDisable = true; rollbackMigrations = false"
                      class="px-2.5 py-1 text-[12px] font-medium rounded text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-200 dark:ring-gray-800 hover:ring-gray-300 dark:hover:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all">
                      Вимкнути
                    </button>
                  @else
                    <button type="button"
                      wire:click="toggleModule('{{ $m['key'] }}', true)"
                      wire:loading.attr="disabled" wire:target="toggleModule('{{ $m['key'] }}', true)"
                      class="px-2.5 py-1 text-[12px] font-medium rounded bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 transition-colors">
                      Увімкнути
                    </button>
                  @endif
                </div>
              </div>
            </div>

            {{-- Disable-confirm модал --}}
            <div x-show="showDisable" x-cloak
                 @keydown.escape.window="showDisable = false"
                 class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
                 @click.self="showDisable = false">
              <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-xl max-w-md w-full p-5" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Вимкнути «{{ $m['name'] }}»?</h3>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-4">
                  <code class="font-mono">{{ $m['key'] }}</code>
                  @if(! empty($m['dependents']))
                    <br><span class="text-amber-600 dark:text-amber-500">Залежать: {{ implode(', ', $m['dependents']) }} — будуть вимкнені каскадно.</span>
                  @endif
                </p>

                <label class="flex items-start gap-2.5 p-3 rounded-md border border-gray-200 dark:border-gray-800 hover:border-amber-300 dark:hover:border-amber-700 cursor-pointer transition-colors mb-3"
                       :class="rollbackMigrations ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700' : ''">
                  <input type="checkbox" x-model="rollbackMigrations" class="mt-0.5"/>
                  <div class="flex-1">
                    <div class="text-[13px] font-medium text-gray-900 dark:text-gray-100">Скинути міграції (drop tables)</div>
                    <div class="text-[11px] text-gray-500">Видалить дані модуля з БД. Без цього — дані лишаться, reinstall їх відновить.</div>
                  </div>
                </label>

                <div class="flex items-center justify-end gap-2">
                  <button type="button" @click="showDisable = false"
                          class="px-3 py-1.5 text-[12px] font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">
                    Скасувати
                  </button>
                  <button type="button"
                          @click="$wire.call('toggleModule', '{{ $m['key'] }}', false, true, rollbackMigrations); showDisable = false"
                          :class="rollbackMigrations ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100'"
                          class="px-3 py-1.5 text-[12px] font-medium rounded transition-colors">
                    <span x-text="rollbackMigrations ? 'Вимкнути + скинути дані' : 'Вимкнути'"></span>
                  </button>
                </div>
              </div>
            </div>

            {{-- Confirm-видалення modal --}}
            <div x-show="showDelete" x-cloak
                 @keydown.escape.window="showDelete = false"
                 class="fixed inset-0 z-[80] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
                 @click.self="showDelete = false">
              <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-xl max-w-md w-full p-5" @click.stop>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Видалити «{{ $m['name'] }}»?</h3>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-4">
                  Оберіть тип видалення. <code class="font-mono">{{ $m['key'] }}</code>
                </p>

                <div class="space-y-2.5 mb-4">
                  <label class="flex items-start gap-2.5 p-3 rounded-md border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 cursor-pointer transition-colors"
                         :class="deleteMode === 'soft' ? 'bg-gray-50 dark:bg-gray-800/60 border-gray-300 dark:border-gray-700' : ''">
                    <input type="radio" x-model="deleteMode" value="soft" class="mt-0.5"/>
                    <div class="flex-1">
                      <div class="text-[13px] font-medium text-gray-900 dark:text-gray-100">Лише файли</div>
                      <div class="text-[11px] text-gray-500">Видалити папку <code class="font-mono">modules/{{ $m['key'] }}/</code>. Дані в БД залишаться — reinstall відновить доступ.</div>
                    </div>
                  </label>
                  <label class="flex items-start gap-2.5 p-3 rounded-md border border-gray-200 dark:border-gray-800 hover:border-red-300 dark:hover:border-red-800 cursor-pointer transition-colors"
                         :class="deleteMode === 'hard' ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700' : ''">
                    <input type="radio" x-model="deleteMode" value="hard" class="mt-0.5"/>
                    <div class="flex-1">
                      <div class="text-[13px] font-medium text-red-700 dark:text-red-400">Файли + дані</div>
                      <div class="text-[11px] text-gray-500">Rollback migrations (drop tables) + видалення з БД. <strong class="text-red-600">Необоротна дія.</strong></div>
                    </div>
                  </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                  <button type="button" @click="showDelete = false"
                          class="px-3 py-1.5 text-[12px] font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">
                    Скасувати
                  </button>
                  <button type="button"
                          @click="$wire.call('uninstallModule', '{{ $m['key'] }}', deleteMode); showDelete = false"
                          :class="deleteMode === 'hard' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100'"
                          class="px-3 py-1.5 text-[12px] font-medium rounded transition-colors">
                    <span x-text="deleteMode === 'hard' ? 'Видалити повністю' : 'Видалити папку'"></span>
                  </button>
                </div>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  @endforeach

  {{-- ─── FOOTER TIP ─── --}}
  <footer class="text-[12px] text-gray-500 dark:text-gray-400 leading-relaxed pt-4 border-t border-gray-200 dark:border-gray-800">
    Для швидкого набору під певний тип магазину запустіть preset:
    <code class="px-1 py-0.5 mx-0.5 bg-gray-100 dark:bg-gray-800 rounded text-[11px] font-mono">php artisan preset:apply auto-parts</code>,
    <code class="px-1 py-0.5 mx-0.5 bg-gray-100 dark:bg-gray-800 rounded text-[11px] font-mono">cosmetics</code> або
    <code class="px-1 py-0.5 mx-0.5 bg-gray-100 dark:bg-gray-800 rounded text-[11px] font-mono">general-shop</code>.
  </footer>
</div>
</x-filament-panels::page>
