<x-filament-panels::page>
    {{-- Tab Navigation --}}
    <x-filament::tabs class="mb-6">
        @foreach ([
            'products' => ['Генератор товарів', 'heroicon-o-cube'],
            'enrichment' => ['Збагачення товарів', 'heroicon-o-paint-brush'],
            'api_settings' => ['Налаштування API', 'heroicon-o-cog-6-tooth'],
            'history' => ['Історія', 'heroicon-o-clock'],
        ] as $tab => [$label, $icon])
            <x-filament::tabs.item
                wire:click="$set('activeTab', '{{ $tab }}')"
                :active="$activeTab === $tab"
                :icon="$icon"
            >
                {{ $label }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB 1: PRODUCT GENERATOR                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'products')
        <div class="space-y-6">
            {{-- Generator Form --}}
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Параметри генерації</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Категорія *</label>
                        <select wire:model="genCategoryId"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Оберіть категорію</option>
                            @foreach ($this->categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Count --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість товарів</label>
                        <input type="number" wire:model="genCount" min="1" max="50"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    {{-- Language --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Мова</label>
                        <select wire:model="genLanguage"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="both">Обидві (UK + EN)</option>
                            <option value="uk">Тільки українська</option>
                            <option value="en">Тільки англійська</option>
                        </select>
                    </div>

                    {{-- Price From --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ціна від (грн)</label>
                        <input type="number" wire:model="genPriceFrom" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    {{-- Price To --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ціна до (грн)</label>
                        <input type="number" wire:model="genPriceTo" min="0"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    {{-- Style --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стиль</label>
                        <select wire:model="genStyle"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="professional">Професійний</option>
                            <option value="casual">Розмовний</option>
                            <option value="technical">Технічний</option>
                        </select>
                    </div>
                </div>

                {{-- Additional Instructions --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Додаткові інструкції</label>
                    <textarea wire:model="genInstructions" rows="3" placeholder="Наприклад: генерувати тільки бюджетні товари, фокус на ігровій тематиці..."
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <x-filament::button wire:click="handleGeneratePrompt" color="primary" icon="heroicon-o-document-text">
                        Згенерувати промт
                    </x-filament::button>

                    @if ($this->isApiConfigured)
                        <x-filament::button
                            wire:click="handleGenerateViaApi"
                            wire:loading.attr="disabled"
                            wire:target="handleGenerateViaApi"
                            color="info"
                            icon="heroicon-o-sparkles">
                            Згенерувати через API
                        </x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Generated Prompt --}}
            @if ($generatedPrompt)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Згенерований промт</h3>
                        <x-filament::button
                            x-data
                            x-on:click="navigator.clipboard.writeText(document.getElementById('prompt-text').value); const l=$el.querySelector('.fi-btn-label'); if(l){const o=l.textContent; l.textContent='Скопійовано!'; setTimeout(() => l.textContent=o, 2000);}"
                            size="sm"
                            color="primary"
                            icon="heroicon-o-clipboard-document">
                            Копіювати
                        </x-filament::button>
                    </div>
                    <textarea id="prompt-text" readonly rows="12"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-xs font-mono shadow-sm">{{ $generatedPrompt }}</textarea>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Скопіюйте цей промт та вставте в ChatGPT, Claude, Gemini або інший AI. Потім вставте JSON відповідь нижче.
                    </p>
                </div>
            @endif

            {{-- JSON Input (manual paste) --}}
            @if ($generatedPrompt || $generatedJson)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">JSON відповідь від AI</h3>
                    <textarea wire:model="generatedJson" rows="10" placeholder='Вставте JSON відповідь від AI сюди...'
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-xs font-mono shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    <div class="flex items-center gap-3 mt-3">
                        <x-filament::button wire:click="handleParseJson" color="info" icon="heroicon-o-code-bracket">
                            Розпарсити JSON
                        </x-filament::button>
                    </div>
                </div>
            @endif

            {{-- Preview --}}
            @if ($showPreview && !empty($previewProducts))
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Попередній перегляд ({{ count($previewProducts) }} товарів)
                        </h3>
                        <x-filament::button
                            wire:click="handleImportProducts"
                            wire:loading.attr="disabled"
                            wire:target="handleImportProducts"
                            wire:confirm="Імпортувати {{ count($previewProducts) }} товарів у базу даних?"
                            color="success"
                            icon="heroicon-o-arrow-down-tray">
                            Імпортувати все
                        </x-filament::button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">#</th>
                                    <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Назва (UK)</th>
                                    <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">SKU</th>
                                    <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Бренд</th>
                                    <th class="text-right py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Ціна</th>
                                    <th class="text-right py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Стара ціна</th>
                                    <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Хіт</th>
                                    <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Новинка</th>
                                    <th class="text-right py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Дії</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($previewProducts as $index => $product)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50" wire:key="preview-{{ $index }}">
                                        <td class="py-3 px-3 text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-3 px-3 font-medium text-gray-900 dark:text-white max-w-xs truncate">
                                            {{ $product['title_uk'] ?? '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                            {{ $product['sku'] ?? '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-gray-500 dark:text-gray-400">
                                            {{ $product['brand'] ?? '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-medium text-gray-900 dark:text-white">
                                            {{ number_format($product['price'] ?? 0, 0, ',', ' ') }} <span class="text-xs text-gray-400">грн</span>
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-400 line-through">
                                            @if (!empty($product['old_price']))
                                                {{ number_format($product['old_price'], 0, ',', ' ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if (!empty($product['is_hit']))
                                                <x-filament::badge color="warning">Хіт</x-filament::badge>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if (!empty($product['is_new']))
                                                <x-filament::badge color="success">New</x-filament::badge>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <x-filament::icon-button
                                                icon="heroicon-o-trash"
                                                wire:click="removePreviewProduct({{ $index }})"
                                                label="Видалити"
                                                color="danger"
                                                size="sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB 2: ENRICHMENT                                              --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'enrichment')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Збагачення існуючих товарів</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Product Select --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Товари *</label>
                        <select wire:model="enrichProductIds" multiple size="8"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @foreach ($this->productsForEnrichment as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Ctrl+Click для множинного вибору. Обрано: {{ count($enrichProductIds) }}</p>
                    </div>

                    {{-- Options --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Що генерувати</label>
                            <select wire:model.live="enrichType"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="all">Все (опис + SEO + теги)</option>
                                <option value="description">Тільки опис</option>
                                <option value="seo">Тільки SEO мета</option>
                                <option value="tags">Тільки пошукові теги</option>
                                <option value="translate">Переклад</option>
                            </select>
                        </div>

                        @if ($enrichType === 'translate')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Цільова мова</label>
                                <select wire:model="enrichTargetLocale"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="en">Англійська</option>
                                    <option value="uk">Українська</option>
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <x-filament::button wire:click="handleEnrichPrompt" color="primary" icon="heroicon-o-document-text">
                        Згенерувати промт
                    </x-filament::button>

                    @if ($this->isApiConfigured)
                        <x-filament::button
                            wire:click="handleEnrichViaApi"
                            wire:loading.attr="disabled"
                            wire:target="handleEnrichViaApi"
                            wire:confirm="Застосувати збагачення через API до {{ count($enrichProductIds) }} товарів?"
                            color="info"
                            icon="heroicon-o-sparkles">
                            Застосувати через API
                        </x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Generated Enrichment Prompt --}}
            @if ($enrichPrompt)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Промт для збагачення</h3>
                        <x-filament::button
                            x-data
                            x-on:click="navigator.clipboard.writeText(document.getElementById('enrich-prompt-text').value); const l=$el.querySelector('.fi-btn-label'); if(l){const o=l.textContent; l.textContent='Скопійовано!'; setTimeout(() => l.textContent=o, 2000);}"
                            size="sm"
                            color="primary"
                            icon="heroicon-o-clipboard-document">
                            Копіювати
                        </x-filament::button>
                    </div>
                    <textarea id="enrich-prompt-text" readonly rows="12"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-xs font-mono shadow-sm">{{ $enrichPrompt }}</textarea>
                </div>
            @endif

            {{-- Enrichment JSON Input --}}
            @if ($enrichPrompt)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">JSON відповідь (для одного товару)</h3>
                    <textarea wire:model="enrichJson" rows="8" placeholder='Вставте JSON відповідь від AI...'
                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-xs font-mono shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    <div class="flex items-center gap-3 mt-3">
                        <x-filament::button wire:click="handleApplyEnrichJson" color="success" icon="heroicon-o-check">
                            Застосувати до товару
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB 3: API SETTINGS                                            --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'api_settings')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Налаштування AI провайдера</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Provider --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Провайдер</label>
                        <select wire:model.live="apiProvider"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="none">Не використовувати (тільки промти)</option>
                            <option value="openai">OpenAI</option>
                            <option value="anthropic">Anthropic (Claude)</option>
                        </select>
                    </div>

                    {{-- API Key --}}
                    @if ($apiProvider !== 'none')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                            <input type="password" wire:model="apiKey"
                                   placeholder="{{ $this->isApiConfigured ? '********** (збережено)' : 'sk-...' }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-gray-400 mt-1">Залиште порожнім, щоб зберегти поточний ключ</p>
                        </div>
                    @endif

                    {{-- Model (OpenAI) --}}
                    @if ($apiProvider === 'openai')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Модель</label>
                            <select wire:model="apiModelOpenai"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="gpt-4o">GPT-4o (рекомендовано)</option>
                                <option value="gpt-4o-mini">GPT-4o Mini (швидше, дешевше)</option>
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo (найдешевше)</option>
                            </select>
                        </div>
                    @endif

                    {{-- Model (Anthropic) --}}
                    @if ($apiProvider === 'anthropic')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Модель</label>
                            <select wire:model="apiModelAnthropic"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="claude-sonnet-4-20250514">Claude Sonnet 4 (рекомендовано)</option>
                                <option value="claude-haiku-4-5-20251001">Claude Haiku 4.5 (швидше, дешевше)</option>
                            </select>
                        </div>
                    @endif

                    {{-- Temperature --}}
                    @if ($apiProvider !== 'none')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Temperature: {{ $apiTemperature }}
                            </label>
                            <input type="range" wire:model.live="apiTemperature" min="0" max="1" step="0.1"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-primary-600">
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0.0 (точний)</span>
                                <span>1.0 (креативний)</span>
                            </div>
                        </div>

                        {{-- Max Tokens --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Макс. токенів</label>
                            <input type="number" wire:model="apiMaxTokens" min="1000" max="16000" step="500"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-gray-400 mt-1">1000-16000. Більше токенів = довші відповіді, але дорожче.</p>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3 mt-6">
                    <x-filament::button wire:click="handleSaveApiSettings" color="success" icon="heroicon-o-check">
                        Зберегти налаштування
                    </x-filament::button>

                    @if ($apiProvider !== 'none')
                        <x-filament::button
                            wire:click="handleTestConnection"
                            wire:loading.attr="disabled"
                            wire:target="handleTestConnection"
                            color="info"
                            icon="heroicon-o-signal">
                            Тестувати підключення
                        </x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Info Box --}}
            <div class="rounded-xl bg-blue-50 dark:bg-blue-500/10 p-6 ring-1 ring-blue-200 dark:ring-blue-500/20">
                <div class="flex gap-3">
                    <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <p class="font-medium mb-2">Як це працює:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Без API</strong> - генеруються промти, які можна копіювати та вставляти в ChatGPT, Claude чи інший AI через веб-інтерфейс</li>
                            <li><strong>З API</strong> - генерація відбувається автоматично через API обраного провайдера</li>
                            <li>OpenAI: отримайте ключ на <a href="https://platform.openai.com/api-keys" target="_blank" class="underline">platform.openai.com</a></li>
                            <li>Anthropic: отримайте ключ на <a href="https://console.anthropic.com/" target="_blank" class="underline">console.anthropic.com</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TAB 4: HISTORY                                                 --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'history')
        <div class="space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Історія AI генерацій</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Дата</th>
                                <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Тип</th>
                                <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Провайдер</th>
                                <th class="text-left py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Модель</th>
                                <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Токени</th>
                                <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Створено</th>
                                <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Оновлено</th>
                                <th class="text-center py-3 px-3 font-medium text-gray-500 dark:text-gray-400">Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($this->historyLogs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50" wire:key="log-{{ $log->id }}">
                                    <td class="py-3 px-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $log->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <x-filament::badge color="info">
                                            {{ $log->type_label }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-3 px-3 text-gray-500 dark:text-gray-400">
                                        {{ $log->provider ?? 'manual' }}
                                    </td>
                                    <td class="py-3 px-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                        {{ $log->model ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center text-gray-500 dark:text-gray-400">
                                        {{ $log->tokens_used > 0 ? number_format($log->tokens_used) : '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($log->products_created > 0)
                                            <x-filament::badge color="success">
                                                +{{ $log->products_created }}
                                            </x-filament::badge>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($log->products_updated > 0)
                                            <x-filament::badge color="warning">
                                                {{ $log->products_updated }}
                                            </x-filament::badge>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <x-filament::badge :color="match ($log->status) {
                                            'success' => 'success',
                                            'error' => 'danger',
                                            'pending' => 'warning',
                                            default => 'gray',
                                        }">
                                            {{ $log->status }}
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-sparkles class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                        <p class="font-medium">Поки що немає генерацій</p>
                                        <p class="text-sm mt-1">Перейдіть на вкладку "Генератор товарів" щоб почати</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
