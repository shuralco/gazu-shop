@props(['model', 'title' => 'SEO Інформація'])

@php
    $seoMeta = null;
    $seoTitle = null;
    $seoDescription = null;
    $canonicalUrl = null;
    $structuredData = null;
    
    if ($model && $model instanceof \App\Models\SeoMeta) {
        // Model is SeoMeta itself
        $seoMeta = $model;
        $seoTitle = $seoMeta->meta_title;
        $seoDescription = $seoMeta->meta_description;
        $canonicalUrl = $seoMeta->canonical_url;
        $structuredData = $seoMeta->structured_data;
    } elseif ($model && method_exists($model, 'seoMeta')) {
        // Model has seoMeta relationship
        $seoMeta = $model->seoMeta()->where('language', 'uk')->first();
        
        if ($seoMeta) {
            $seoTitle = $seoMeta->meta_title;
            $seoDescription = $seoMeta->meta_description;
            $canonicalUrl = $seoMeta->canonical_url;
            $structuredData = $seoMeta->structured_data;
        }
        
        // Fallback to trait methods if available
        if (!$seoMeta && method_exists($model, 'getSeoTitle')) {
            $seoTitle = $model->getSeoTitle('uk');
            $seoDescription = $model->getSeoDescription('uk');
            $canonicalUrl = method_exists($model, 'getCanonicalUrl') ? $model->getCanonicalUrl() : null;
            $structuredData = method_exists($model, 'getStructuredData') ? $model->getStructuredData() : null;
        }
    }
@endphp

<div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-800">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        {{ $title }}
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- SEO Title -->
        <div>
            <label class="text-sm font-medium text-gray-600">SEO Заголовок:</label>
            <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-50 rounded border">
                {{ $seoTitle ?? 'Не встановлено' }}
            </p>
            @if($seoTitle)
                <p class="text-xs text-gray-500 mt-1">
                    Довжина: {{ mb_strlen($seoTitle) }}/60 символів
                    @if(mb_strlen($seoTitle) > 60)
                        <span class="text-red-500 font-medium">⚠️ Задовго</span>
                    @endif
                </p>
            @endif
        </div>

        <!-- SEO Description -->
        <div>
            <label class="text-sm font-medium text-gray-600">SEO Опис:</label>
            <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-50 rounded border">
                {{ $seoDescription ?? 'Не встановлено' }}
            </p>
            @if($seoDescription)
                <p class="text-xs text-gray-500 mt-1">
                    Довжина: {{ mb_strlen($seoDescription) }}/155 символів
                    @if(mb_strlen($seoDescription) > 155)
                        <span class="text-red-500 font-medium">⚠️ Задовго</span>
                    @endif
                </p>
            @endif
        </div>

        <!-- Canonical URL -->
        <div>
            <label class="text-sm font-medium text-gray-600">Canonical URL:</label>
            <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-50 rounded border break-all">
                @if($canonicalUrl)
                    <a href="{{ $canonicalUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                        {{ $canonicalUrl }}
                    </a>
                @else
                    Не встановлено
                @endif
            </p>
        </div>

        <!-- Structured Data Status -->
        <div>
            <label class="text-sm font-medium text-gray-600">Structured Data:</label>
            <div class="mt-1 p-2 bg-gray-50 rounded border">
                @if($structuredData)
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                        ✅ Налаштовано
                    </span>
                    @if(is_array($structuredData) && isset($structuredData['@type']))
                        <span class="ml-2 text-xs text-gray-600">Тип: {{ $structuredData['@type'] }}</span>
                    @endif
                @else
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                        ⚠️ Не налаштовано
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Sitemap Info -->
    @if($seoMeta)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Sitemap:</span>
                    <span class="ml-2 px-2 py-1 text-xs rounded {{ $seoMeta->sitemap_include ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $seoMeta->sitemap_include ? 'Включено' : 'Виключено' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600">Пріоритет:</span>
                    <span class="ml-2 font-medium">{{ $seoMeta->sitemap_priority ?? 'Не встановлено' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Частота:</span>
                    <span class="ml-2 font-medium">{{ $seoMeta->sitemap_changefreq ?? 'Не встановлено' }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    @if($model && auth()->user()?->is_admin)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex gap-2">
                @if($model instanceof \App\Models\Category)
                    <a href="{{ url('/admin/categories/' . $model->id . '/edit') }}" 
                       class="inline-flex items-center px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Редагувати категорію
                    </a>
                @endif

                @if($model instanceof \App\Models\Product)
                    <a href="{{ url('/admin/products/' . $model->id . '/edit') }}" 
                       class="inline-flex items-center px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded hover:bg-green-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Редагувати товар
                    </a>
                @endif

                @if($seoMeta)
                    <a href="{{ url('/admin/seo-metas/' . $seoMeta->id . '/edit') }}" 
                       class="inline-flex items-center px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded hover:bg-purple-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Редагувати SEO
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>