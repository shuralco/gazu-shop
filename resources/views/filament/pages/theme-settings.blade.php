<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section icon="heroicon-o-information-circle" icon-color="info">
            <x-slot name="heading">Тема магазину</x-slot>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                Тема магазину = пакет CSS-токенів з <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">resources/css/tokens/</code>.
                Зміна теми перепише <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">@import</code> у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">app.css</code> — після цього виконайте <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code> (production) або запустіть <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run dev</code> (live reload).
                <br><br>
                Усі компоненти <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">&lt;x-ui.*&gt;</code> на storefront автоматично адаптуються — кнопки, картки, badges, inputs, sections.
            </div>
        </x-filament::section>

        <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
            @foreach($this->availableThemes as $theme)
                @php
                    $isActive = $theme === $this->activeTheme;
                    $colorFg = $this->previewToken($theme, 'color-fg') ?? '#000';
                    $colorBg = $this->previewToken($theme, 'color-bg') ?? '#fff';
                    $colorBrand = $this->previewToken($theme, 'color-brand') ?? '#000';
                    $colorAccent = $this->previewToken($theme, 'color-accent') ?? '#000';
                    $radiusCard = $this->previewToken($theme, 'radius-card') ?? '0px';
                @endphp

                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            <span class="text-lg font-bold capitalize">{{ str_replace('-', ' ', $theme) }}</span>
                            @if($isActive)
                                <x-filament::badge color="success">АКТИВНА</x-filament::badge>
                            @endif
                        </span>
                    </x-slot>
                    <x-slot name="description">
                        <span class="font-mono text-xs">tokens/{{ $theme }}.css</span>
                    </x-slot>

                    {{-- Live preview --}}
                    <div
                        class="mb-4 rounded-lg border p-4"
                        style="background:{{ $colorBg }}; color:{{ $colorFg }}; border-color:{{ $colorFg }}; border-radius:{{ $radiusCard }};"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <span class="inline-block h-4 w-4 rounded-full" style="background:{{ $colorBrand }};" title="brand"></span>
                            <span class="inline-block h-4 w-4 rounded-full" style="background:{{ $colorAccent }};" title="accent"></span>
                            <span class="font-mono text-xs">{{ $colorFg }} on {{ $colorBg }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                style="background:{{ $colorBrand }}; color:{{ $colorBg }}; border:1px solid {{ $colorBrand }}; padding:6px 12px; border-radius:{{ $radiusCard }}; font-size:12px; font-weight:600;"
                                disabled
                            >Кнопка</button>
                            <span
                                style="background:{{ $colorAccent }}; color:{{ $colorBg }}; padding:4px 10px; border-radius:{{ $radiusCard }}; font-size:11px; font-weight:600;"
                            >badge</span>
                        </div>
                    </div>

                    @if(! $isActive)
                        <x-filament::button
                            type="button"
                            wire:click="activateTheme('{{ $theme }}')"
                            color="primary"
                            icon="heroicon-o-swatch"
                            class="w-full"
                        >
                            Активувати
                        </x-filament::button>
                    @else
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400">Поточна тема</div>
                    @endif
                </x-filament::section>
            @endforeach
        </div>

        <x-filament::section icon="heroicon-o-plus-circle">
            <x-slot name="heading">Як створити нову тему</x-slot>

            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li>Скопіюйте <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">resources/css/tokens/brutal.css</code> у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">my-theme.css</code></li>
                <li>Відредагуйте значення CSS-змінних (зберігаючи усі імена)</li>
                <li>Поверніться сюди й активуйте «my theme»</li>
                <li>Виконайте <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code></li>
            </ol>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Повний контракт CSS-змінних:
                <x-filament::link href="/docs/THEMES.md" target="_blank">docs/THEMES.md</x-filament::link> ·
                UI components:
                <x-filament::link href="/docs/UI-COMPONENTS.md" target="_blank">docs/UI-COMPONENTS.md</x-filament::link>
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
