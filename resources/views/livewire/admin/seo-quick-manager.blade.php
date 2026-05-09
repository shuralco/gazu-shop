<div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold flex items-center text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            🎯 SEO Швидкий менеджер
        </h3>
        
        <div class="flex items-center gap-2">
            <select wire:model.live="language" class="text-xs border border-gray-300 rounded px-2 py-1">
                <option value="uk">🇺🇦 UK</option>
                <option value="en">🇬🇧 EN</option>
            </select>
            
            @if(!$editMode)
                <button wire:click="toggleEditMode" class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">
                    ✏️ Редагувати
                </button>
            @endif
        </div>
    </div>

    @if(session('seo-generated'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
            ✅ {{ session('seo-generated') }}
        </div>
    @endif

    @if(session('seo-saved'))
        <div class="mb-4 p-3 bg-blue-100 border border-blue-400 text-blue-700 rounded">
            💾 {{ session('seo-saved') }}
        </div>
    @endif

    @if($editMode)
        <form wire:submit="saveSeo" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SEO Заголовок</label>
                <input type="text" wire:model="seoTitle" 
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                       maxlength="60">
                <p class="text-xs text-gray-500 mt-1">
                    {{ mb_strlen($seoTitle ?? '') }}/60 символів
                    @if(mb_strlen($seoTitle ?? '') > 60)
                        <span class="text-red-500 font-medium">⚠️ Задовго</span>
                    @endif
                </p>
                @error('seoTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SEO Опис</label>
                <textarea wire:model="seoDescription" 
                          rows="3"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                          maxlength="155"></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    {{ mb_strlen($seoDescription ?? '') }}/155 символів
                    @if(mb_strlen($seoDescription ?? '') > 155)
                        <span class="text-red-500 font-medium">⚠️ Задовго</span>
                    @endif
                </p>
                @error('seoDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                <input type="url" wire:model="canonicalUrl" 
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sitemap включити</label>
                    <select wire:model="sitemapInclude" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="1">✅ Так</option>
                        <option value="0">❌ Ні</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Пріоритет</label>
                    <select wire:model="sitemapPriority" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="1.0">1.0 - Найвищий</option>
                        <option value="0.9">0.9 - Дуже високий</option>
                        <option value="0.8">0.8 - Високий</option>
                        <option value="0.7">0.7 - Середній</option>
                        <option value="0.6">0.6 - Низький</option>
                        <option value="0.5">0.5 - Дуже низький</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    💾 Зберегти
                </button>
                <button type="button" wire:click="toggleEditMode" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                    ❌ Скасувати
                </button>
            </div>
        </form>
    @else
        <div class="space-y-3">
            <!-- SEO Title -->
            <div>
                <label class="text-sm font-medium text-gray-600">SEO Заголовок:</label>
                <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-50 rounded border">
                    {{ $seoTitle ?? 'Не встановлено' }}
                </p>
                @if($seoTitle && mb_strlen($seoTitle) > 60)
                    <p class="text-xs text-red-500 mt-1">⚠️ Заголовок задовгий ({{ mb_strlen($seoTitle) }}/60)</p>
                @endif
            </div>

            <!-- SEO Description -->
            <div>
                <label class="text-sm font-medium text-gray-600">SEO Опис:</label>
                <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-50 rounded border">
                    {{ $seoDescription ?? 'Не встановлено' }}
                </p>
                @if($seoDescription && mb_strlen($seoDescription) > 155)
                    <p class="text-xs text-red-500 mt-1">⚠️ Опис задовгий ({{ mb_strlen($seoDescription) }}/155)</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="flex gap-2 pt-2">
                <button wire:click="generateSeo" class="px-3 py-1 text-xs bg-purple-500 text-white rounded hover:bg-purple-600">
                    🎯 Згенерувати SEO
                </button>
                
                @if($model && $model instanceof \App\Models\Category)
                    <a href="{{ url('/admin/categories/' . $model->id . '/edit') }}" 
                       class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">
                        ✏️ Редагувати категорію
                    </a>
                @endif

                @if($model && $model instanceof \App\Models\Product)
                    <a href="{{ url('/admin/products/' . $model->id . '/edit') }}" 
                       class="px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">
                        ✏️ Редагувати товар
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>