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
          <article class="group relative bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 hover:shadow-sm transition-all flex flex-col">

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

                <div class="ml-auto">
                  @if($m['enabled'])
                    <button type="button"
                      wire:click="toggleModule('{{ $m['key'] }}', false)"
                      wire:confirm="Вимкнути «{{ $m['name'] }}»?"
                      wire:loading.attr="disabled" wire:target="toggleModule('{{ $m['key'] }}', false)"
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
