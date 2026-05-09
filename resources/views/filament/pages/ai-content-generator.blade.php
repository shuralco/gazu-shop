<x-filament-panels::page>
    {{-- Tab Navigation --}}
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
        @foreach ([
            'products' => ['Генератор товарів', 'heroicon-o-cube'],
            'enrichment' => ['Збагачення товарів', 'heroicon-o-paint-brush'],
            'api_settings' => ['Налаштування API', 'heroicon-o-cog-6-tooth'],
            'history' => ['Історія', 'heroicon-o-clock'],
        ] as $tab => [$label, $icon])
            <button
                wire:click="$set('activeTab', '{{ $tab }}')"
                @class([
                    'inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors duration-150',
                    'bg-primary-600 text-white shadow-sm' => $activeTab === $tab,
                    'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' => $activeTab !== $tab,
                ])
            >
                <x-dynamic-component :component="$icon" class="w-5 h-5" />
                {{ $label }}
            </button>
        @endforeach
    </div>

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
                    <button wire:click="handleGeneratePrompt"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                        Згенерувати промт
                    </button>

                    @if ($this->isApiConfigured)
                        <button wire:click="handleGenerateViaApi"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="handleGenerateViaApi">
                                <x-heroicon-o-sparkles class="w-5 h-5" />
                            </span>
                            <span wire:loading wire:target="handleGenerateViaApi">
                                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                            </span>
                            Згенерувати через API
                        </button>
                    @endif
                </div>
            </div>

            {{-- Generated Prompt --}}
            @if ($generatedPrompt)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Згенерований промт</h3>
                        <button onclick="navigator.clipboard.writeText(document.getElementById('prompt-text').value); this.textContent='Скопійовано!'; setTimeout(() => this.textContent='Копіювати', 2000)"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-500/20 transition-colors">
                            <x-heroicon-o-clipboard-document class="w-4 h-4" />
                            Копіювати
                        </button>
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
                        <button wire:click="handleParseJson"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <x-heroicon-o-code-bracket class="w-5 h-5" />
                            Розпарсити JSON
                        </button>
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
                        <button wire:click="handleImportProducts"
                                wire:loading.attr="disabled"
                                wire:confirm="Імпортувати {{ count($previewProducts) }} товарів у базу даних?"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="handleImportProducts">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                            </span>
                            <span wire:loading wire:target="handleImportProducts">
                                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                            </span>
                            Імпортувати все
                        </button>
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
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-500/10 text-orange-800 dark:text-orange-300">Хіт</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if (!empty($product['is_new']))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-500/10 text-green-800 dark:text-green-300">New</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <button wire:click="removePreviewProduct({{ $index }})"
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                                    title="Видалити">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
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
                    <button wire:click="handleEnrichPrompt"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                        Згенерувати промт
                    </button>

                    @if ($this->isApiConfigured)
                        <button wire:click="handleEnrichViaApi"
                                wire:loading.attr="disabled"
                                wire:confirm="Застосувати збагачення через API до {{ count($enrichProductIds) }} товарів?"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="handleEnrichViaApi">
                                <x-heroicon-o-sparkles class="w-5 h-5" />
                            </span>
                            <span wire:loading wire:target="handleEnrichViaApi">
                                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                            </span>
                            Застосувати через API
                        </button>
                    @endif
                </div>
            </div>

            {{-- Generated Enrichment Prompt --}}
            @if ($enrichPrompt)
                <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Промт для збагачення</h3>
                        <button onclick="navigator.clipboard.writeText(document.getElementById('enrich-prompt-text').value); this.textContent='Скопійовано!'; setTimeout(() => this.textContent='Копіювати', 2000)"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-500/20 transition-colors">
                            <x-heroicon-o-clipboard-document class="w-4 h-4" />
                            Копіювати
                        </button>
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
                        <button wire:click="handleApplyEnrichJson"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700 transition-colors">
                            <x-heroicon-o-check class="w-5 h-5" />
                            Застосувати до товару
                        </button>
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
                    <button wire:click="handleSaveApiSettings"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700 transition-colors">
                        <x-heroicon-o-check class="w-5 h-5" />
                        Зберегти налаштування
                    </button>

                    @if ($apiProvider !== 'none')
                        <button wire:click="handleTestConnection"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="handleTestConnection">
                                <x-heroicon-o-signal class="w-5 h-5" />
                            </span>
                            <span wire:loading wire:target="handleTestConnection">
                                <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                            </span>
                            Тестувати підключення
                        </button>
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-500/10 text-blue-800 dark:text-blue-300">
                                            {{ $log->type_label }}
                                        </span>
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
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-500/10 text-green-800 dark:text-green-300">
                                                +{{ $log->products_created }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($log->products_updated > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-500/10 text-amber-800 dark:text-amber-300">
                                                {{ $log->products_updated }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span @class([
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 dark:bg-green-500/10 text-green-800 dark:text-green-300' => $log->status === 'success',
                                            'bg-red-100 dark:bg-red-500/10 text-red-800 dark:text-red-300' => $log->status === 'error',
                                            'bg-yellow-100 dark:bg-yellow-500/10 text-yellow-800 dark:text-yellow-300' => $log->status === 'pending',
                                        ])>
                                            {{ $log->status }}
                                        </span>
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
