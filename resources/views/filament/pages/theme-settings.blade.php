<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
            <div class="flex gap-3">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-600 shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900 dark:text-blue-100">
                    Тема магазину = пакет CSS-токенів з <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">resources/css/tokens/</code>.
                    Зміна теми перепише <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">@import</code> у <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">app.css</code> — після цього виконайте <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">npm run build</code> (production) або запустіть <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">npm run dev</code> (live reload).
                    <br><br>
                    Усі компоненти <code class="px-1 bg-blue-100 dark:bg-blue-800 rounded">&lt;x-ui.*&gt;</code> на storefront автоматично адаптуються — кнопки, картки, badges, inputs, sections.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($this->availableThemes as $theme)
                @php
                    $isActive = $theme === $this->activeTheme;
                    $colorFg = $this->previewToken($theme, 'color-fg') ?? '#000';
                    $colorBg = $this->previewToken($theme, 'color-bg') ?? '#fff';
                    $colorBrand = $this->previewToken($theme, 'color-brand') ?? '#000';
                    $colorAccent = $this->previewToken($theme, 'color-accent') ?? '#000';
                    $radiusCard = $this->previewToken($theme, 'radius-card') ?? '0px';
                @endphp

                <div class="border-2 {{ $isActive ? 'border-success-500' : 'border-gray-200 dark:border-gray-700' }} rounded-lg p-5 bg-white dark:bg-gray-900 transition-all hover:shadow-md">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold capitalize">{{ str_replace('-', ' ', $theme) }}</h3>
                                @if($isActive)
                                    <span class="px-2 py-0.5 text-xs font-bold bg-success-500 text-white rounded">АКТИВНА</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1 font-mono">tokens/{{ $theme }}.css</p>
                        </div>
                    </div>

                    <!-- Live preview -->
                    <div
                        class="rounded-lg p-4 mb-4 border"
                        style="background:{{ $colorBg }}; color:{{ $colorFg }}; border-color:{{ $colorFg }}; border-radius:{{ $radiusCard }};"
                    >
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-block w-4 h-4 rounded-full" style="background:{{ $colorBrand }};" title="brand"></span>
                            <span class="inline-block w-4 h-4 rounded-full" style="background:{{ $colorAccent }};" title="accent"></span>
                            <span class="text-xs font-mono">{{ $colorFg }} on {{ $colorBg }}</span>
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
                        <button
                            type="button"
                            wire:click="activateTheme('{{ $theme }}')"
                            class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded font-medium transition-colors"
                        >
                            Активувати
                        </button>
                    @else
                        <div class="text-center text-sm text-gray-500">Поточна тема</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="font-bold mb-2">Як створити нову тему</h4>
            <ol class="text-sm space-y-1 list-decimal list-inside">
                <li>Скопіюйте <code class="px-1 bg-white dark:bg-gray-800 rounded">resources/css/tokens/brutal.css</code> у <code class="px-1 bg-white dark:bg-gray-800 rounded">my-theme.css</code></li>
                <li>Відредагуйте значення CSS-змінних (зберігаючи усі імена)</li>
                <li>Поверніться сюди й активуйте «my theme»</li>
                <li>Виконайте <code class="px-1 bg-white dark:bg-gray-800 rounded">npm run build</code></li>
            </ol>
            <p class="text-xs text-gray-500 mt-2">
                Повний контракт CSS-змінних: <a href="/docs/THEMES.md" class="text-primary-600 underline" target="_blank">docs/THEMES.md</a> ·
                UI components: <a href="/docs/UI-COMPONENTS.md" class="text-primary-600 underline" target="_blank">docs/UI-COMPONENTS.md</a>
            </p>
        </div>
    </div>
</x-filament-panels::page>
