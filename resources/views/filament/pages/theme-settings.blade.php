<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
            <x-slot name="heading">Тема магазину</x-slot>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                Тема = набір кольорів-токенів у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/&lt;назва&gt;/theme.json</code>.
                Активна тема зберігається у БД і застосовується <strong>миттєво</strong> — вітрина переспрашивається у рантаймі
                (<code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code> <strong>не потрібен</strong>).
                Кеш вітрини скидається автоматично при перемиканні.
            </div>
        </x-filament::section>

        <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
            @foreach($this->themes as $theme)
                @php
                    $isActive = $theme['name'] === $this->activeTheme;
                    $bg     = $this->previewToken($theme['name'], 'paper') ?? '#FBFAF7';
                    $fg     = $this->previewToken($theme['name'], 'ink')   ?? '#0E1B2C';
                    $brand  = $this->previewToken($theme['name'], 'blue')  ?? ($this->previewToken($theme['name'], 'ink') ?? '#2453A6');
                    $accent = $this->previewToken($theme['name'], 'azure') ?? ($this->previewToken($theme['name'], 'warn') ?? '#3672D9');
                    $line   = $this->previewToken($theme['name'], 'line')  ?? '#E4E7EB';
                @endphp

                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            <span class="text-lg font-bold">{{ $theme['label'] }}</span>
                            @if($isActive)
                                <x-filament::badge color="success">АКТИВНА</x-filament::badge>
                            @endif
                        </span>
                    </x-slot>
                    <x-slot name="description">
                        <span class="font-mono text-xs">themes/{{ $theme['name'] }}/theme.json</span>
                    </x-slot>

                    @if($theme['description'])
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">{{ $theme['description'] }}</p>
                    @endif

                    {{-- Live preview зі справжніх токенів теми --}}
                    <div
                        class="mb-4 rounded-lg p-4"
                        style="background:{{ $bg }}; color:{{ $fg }}; border:1px solid {{ $line }}; border-radius:8px;"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <span class="inline-block h-4 w-4 rounded-full" style="background:{{ $brand }};" title="brand"></span>
                            <span class="inline-block h-4 w-4 rounded-full" style="background:{{ $accent }};" title="accent"></span>
                            <span class="font-mono text-xs">{{ $fg }} on {{ $bg }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                style="background:{{ $brand }}; color:#fff; border:0; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600;"
                                disabled
                            >Кнопка</button>
                            <span
                                style="background:{{ $accent }}; color:#fff; padding:4px 10px; border-radius:16px; font-size:11px; font-weight:600;"
                            >badge</span>
                        </div>
                    </div>

                    @if(! $isActive)
                        <x-filament::button
                            type="button"
                            wire:click="activateTheme('{{ $theme['name'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="activateTheme('{{ $theme['name'] }}')"
                            color="primary"
                            icon="heroicon-o-swatch"
                            class="w-full"
                        >
                            Активувати
                        </x-filament::button>
                    @else
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400">Поточна тема — застосована на вітрині</div>
                    @endif
                </x-filament::section>
            @endforeach
        </div>

        <x-filament::section icon="heroicon-o-plus-circle">
            <x-slot name="heading">Як додати нову тему</x-slot>

            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li>Скопіюйте теку <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/</code> у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/&lt;нова&gt;/</code></li>
                <li>У <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">theme.json</code> змініть <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">name</code>, <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">label</code> та значення кольорів у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">tokens</code> (імена ключів лишайте)</li>
                <li>Лиште <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">css_entry</code> на <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/resources/css/gazu.css</code> (спільна збірка)</li>
                <li>Поверніться сюди — нова тема зʼявиться автоматично. Натисніть «Активувати» — застосується миттєво, <strong>без</strong> <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code></li>
            </ol>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Перевизначаються лише кольори (інші ключі див. у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/theme.json</code>).
                Радіуси/шрифти/тіні — у збірці теми.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
