<?php
    // Pretty-URL contexts (/novynky, /khity, /akcii) have правильні title
    // замість generic 'Каталог'. Перевіряємо query string що ставить роут.
    $carSeo = $carSeo ?? null;
    $contextTitle = null;
    if (request('new') == 1) { $contextTitle = 'Новинки'; }
    elseif (request('hits') == 1) { $contextTitle = 'Хіти продажу'; }
    elseif (request('promo') == 1) { $contextTitle = 'Акції та знижки'; }
    elseif ($carSeo && ! empty($carSeo['contextTitle'])) { $contextTitle = $carSeo['contextTitle']; }
    $title = $category->title ?? ($searchQuery ? 'Пошук: '.$searchQuery : ($contextTitle ?? 'Каталог'));
    $crumbs = [['Головна', route('gazu.home')]];
    if ($category) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        // Ancestor chain: показуємо повний drill-down до поточної категорії.
        foreach (($ancestors ?? collect()) as $anc) {
            $crumbs[] = [(string) ($anc->title ?? '—'), url('/'.$anc->slug)];
        }
        $crumbs[] = (string) ($category->title ?? 'Категорія');
    } elseif ($contextTitle) {
        $crumbs[] = ['Каталог', route('gazu.catalog')];
        $crumbs[] = $contextTitle;
    } else {
        $crumbs[] = 'Каталог';
    }
?>

<?php
    // SEO-шаблони таксономій: пер-категорійний meta_title має пріоритет,
    // далі carSeo (підбір по авто), далі базові шаблони (category/search/car).
    $catalogCount = plural_uk_count($totalCount ?? 0, 'товар', 'товари', 'товарів');
    $catalogSeoTitle = null;
    if ($carSeo && ! empty($carSeo['metaTitle'])) {
        $catalogSeoTitle = $carSeo['metaTitle'];
    } elseif ($carSeo && ! empty($carSeo['contextTitle'])) {
        $catalogSeoTitle = \App\Support\SeoTemplates::title('car', ['car' => $carSeo['contextTitle'], 'count' => $catalogCount]);
    } elseif (! empty($searchQuery)) {
        $catalogSeoTitle = \App\Support\SeoTemplates::title('search', ['query' => $searchQuery, 'count' => $catalogCount]);
    } elseif ($category) {
        $catalogSeoTitle = ! empty($category->meta_title)
            ? (is_array($category->meta_title) ? ($category->meta_title['uk'] ?? '') : (string) $category->meta_title)
            : '';
        if ($catalogSeoTitle === '') {
            $catalogSeoTitle = \App\Support\SeoTemplates::title('category', ['name' => $title, 'count' => $catalogCount]);
        }
    }
?>
<?php $__env->startSection('title', $catalogSeoTitle ?: $title . ' — GAZU'); ?>



<?php $__env->startSection('jsonld'); ?>
    <?php
        $itemList = [];
        $pos = 1;
        foreach ($crumbs as $crumb) {
            if (is_array($crumb)) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => (string) $crumb[0],
                    'item' => (string) ($crumb[1] ?? url()->current()),
                ];
            } elseif (is_string($crumb)) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $crumb,
                    'item' => url()->current(),
                ];
            }
        }
        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemList,
        ];
    ?>
    <script type="application/ld+json"><?php echo json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php $__env->stopSection(); ?>
<?php
    $catalogSeoDescription = null;
    if ($carSeo && ! empty($carSeo['metaDescription'])) {
        $catalogSeoDescription = $carSeo['metaDescription'];
    } elseif ($carSeo && ! empty($carSeo['contextTitle'])) {
        $catalogSeoDescription = \App\Support\SeoTemplates::description('car', ['car' => $carSeo['contextTitle'], 'count' => $catalogCount]);
    } elseif (! empty($searchQuery)) {
        $catalogSeoDescription = \App\Support\SeoTemplates::description('search', ['query' => $searchQuery, 'count' => $catalogCount]);
    } elseif ($category) {
        $catalogSeoDescription = $category->meta_description
            ? (is_array($category->meta_description) ? ($category->meta_description['uk'] ?? '') : (string) $category->meta_description)
            : '';
        if ($catalogSeoDescription === '') {
            $catalogSeoDescription = \App\Support\SeoTemplates::description('category', ['name' => $title, 'count' => $catalogCount]);
        }
    }
?>
<?php $__env->startSection('description', $catalogSeoDescription ?: 'Каталог автозапчастин · '.$catalogCount.' · доставка по Україні'); ?>

<?php $__env->startSection('content'); ?>
    <div class="gazu-container">
        <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => $crumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($crumbs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $attributes = $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $component = $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>

        <div class="flex items-end justify-between mb-5 flex-wrap gap-2">
            <div>
                <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0"><?php echo e($title); ?></h1>
                <?php if($category && ($category->description ?? false)): ?>
                    <div class="text-sm text-[var(--gazu-graphite)] mt-1.5 max-w-xl gazu-prose"><?php echo $category->description; ?></div>
                <?php elseif($carSeo && ! empty($carSeo['description'])): ?>
                    <div class="text-sm text-[var(--gazu-graphite)] mt-1.5 max-w-xl gazu-prose"><?php echo $carSeo['description']; ?></div>
                <?php elseif($searchQuery): ?>
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1.5">Знайдено <?php echo e(plural_uk_count($totalCount, 'товар', 'товари', 'товарів')); ?></p>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($category || ! empty($selectedMake ?? '') || ! empty($selectedModel ?? '') || ! empty($selectedEngine ?? '')): ?>
            <div class="mb-4">
                <?php if (isset($component)) { $__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.car-selector','data' => ['variant' => 'catalog','selectedMake' => $selectedMake ?? '','selectedModel' => $selectedModel ?? '','selectedEngine' => $selectedEngine ?? '','categoryUrl' => $category ? request()->url() : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.car-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'catalog','selected-make' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedMake ?? ''),'selected-model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedModel ?? ''),'selected-engine' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedEngine ?? ''),'category-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category ? request()->url() : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0)): ?>
<?php $attributes = $__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0; ?>
<?php unset($__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0)): ?>
<?php $component = $__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0; ?>
<?php unset($__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>

        
        <?php if(! empty($subcategories) && $subcategories->isNotEmpty()): ?>
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 mb-5">
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-3">Підкатегорії</div>
                <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a wire:navigate href="<?php echo e(url('/'.$sub->slug)); ?>"
                           class="flex items-center justify-between gap-2 px-3 py-2.5 bg-[var(--gazu-paper)] hover:bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-md no-underline text-[var(--gazu-ink)] transition-colors">
                            <span class="text-[13px] font-medium truncate"><?php echo e($sub->title); ?></span>
                            <span class="gazu-mono text-[10px] text-[var(--gazu-muted)] whitespace-nowrap"><?php echo e($sub->products_count ?? 0); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('gazu.partials.active-filters', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="gazu-grid-sidebar mt-3" x-data="{ filtersOpen: false }"
             @keydown.escape.window="filtersOpen = false"
             :class="filtersOpen ? 'gazu-filters-active' : ''">
            
            <div x-show="filtersOpen" x-cloak x-transition.opacity
                 class="lg:hidden fixed inset-0 z-[69]" style="background: rgba(14,27,44,0.5);"
                 @click="filtersOpen = false"></div>

            
            <div class="gazu-filter-panel" :data-open="filtersOpen ? '1' : '0'">
                <div class="lg:hidden flex items-center justify-between mb-3 pb-3 border-b border-[var(--gazu-line)]">
                    <span class="gazu-display text-lg font-semibold text-[var(--gazu-ink)]">Фільтри</span>
                    <button type="button" @click="filtersOpen = false"
                            class="w-8 h-8 rounded-md hover:bg-[var(--gazu-mist)] flex items-center justify-center text-[var(--gazu-graphite)] cursor-pointer" aria-label="Закрити">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <?php if (isset($component)) { $__componentOriginal939926802e1c3fbb39005b130947314c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal939926802e1c3fbb39005b130947314c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.filter-panel','data' => ['priceRange' => $priceRange,'availableCategories' => $availableCategories ?? collect(),'availableBrands' => $availableBrands,'selectedBrands' => $selectedBrands,'availableConditions' => $availableConditions ?? null,'selectedConditions' => $selectedConditions ?? [],'inStockOnly' => $inStockOnly,'searchQuery' => $searchQuery,'category' => $category]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.filter-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['priceRange' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priceRange),'availableCategories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableCategories ?? collect()),'availableBrands' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableBrands),'selectedBrands' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedBrands),'availableConditions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableConditions ?? null),'selectedConditions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedConditions ?? []),'inStockOnly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inStockOnly),'searchQuery' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($searchQuery),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal939926802e1c3fbb39005b130947314c)): ?>
<?php $attributes = $__attributesOriginal939926802e1c3fbb39005b130947314c; ?>
<?php unset($__attributesOriginal939926802e1c3fbb39005b130947314c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal939926802e1c3fbb39005b130947314c)): ?>
<?php $component = $__componentOriginal939926802e1c3fbb39005b130947314c; ?>
<?php unset($__componentOriginal939926802e1c3fbb39005b130947314c); ?>
<?php endif; ?>
            </div>
            <div class="min-w-0">
                <?php
                    $currentView = request('view') === 'list' ? 'list' : 'grid';
                    $activeFilterCount = (is_array(request('brand')) ? count(request('brand')) : 0)
                        + (is_array(request('condition')) ? count(request('condition')) : 0)
                        + (request()->filled('min') || request()->filled('max') ? 1 : 0)
                        + (request('stock') === 'in' ? 1 : 0);
                ?>
                
                <button type="button" @click="filtersOpen = true"
                        class="lg:hidden w-full mb-3 px-4 py-2.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg flex items-center justify-center gap-2 text-[13px] font-medium text-[var(--gazu-ink)] cursor-pointer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
                    Фільтри
                    <?php if($activeFilterCount > 0): ?>
                        <span class="ml-1 px-1.5 py-0.5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] text-[11px] rounded-full gazu-mono leading-none"><?php echo e($activeFilterCount); ?></span>
                    <?php endif; ?>
                </button>
                <?php echo $__env->make('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => $currentView, 'currentSort' => $currentSort], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if($products->isEmpty()): ?>
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center mt-4">
                        <div class="gazu-display text-2xl font-semibold mb-2">Нічого не знайдено</div>
                        <p class="text-sm text-[var(--gazu-graphite)] mb-4">Спробуйте змінити фільтри або скинути всі.</p>
                        <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Скинути фільтри</a>
                    </div>
                <?php elseif($currentView === 'list'): ?>
                    <div class="flex flex-col gap-2 mt-4">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginal7b7ab515f3241c7183eb5a9333012766 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b7ab515f3241c7183eb5a9333012766 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-row','data' => ['p' => $p]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b7ab515f3241c7183eb5a9333012766)): ?>
<?php $attributes = $__attributesOriginal7b7ab515f3241c7183eb5a9333012766; ?>
<?php unset($__attributesOriginal7b7ab515f3241c7183eb5a9333012766); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b7ab515f3241c7183eb5a9333012766)): ?>
<?php $component = $__componentOriginal7b7ab515f3241c7183eb5a9333012766; ?>
<?php unset($__componentOriginal7b7ab515f3241c7183eb5a9333012766); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal876be2cf017156a88aa3c73cbba82096 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal876be2cf017156a88aa3c73cbba82096 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.pagination','data' => ['paginator' => $paginator]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paginator)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal876be2cf017156a88aa3c73cbba82096)): ?>
<?php $attributes = $__attributesOriginal876be2cf017156a88aa3c73cbba82096; ?>
<?php unset($__attributesOriginal876be2cf017156a88aa3c73cbba82096); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal876be2cf017156a88aa3c73cbba82096)): ?>
<?php $component = $__componentOriginal876be2cf017156a88aa3c73cbba82096; ?>
<?php unset($__componentOriginal876be2cf017156a88aa3c73cbba82096); ?>
<?php endif; ?>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 mt-4 gazu-stagger">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p,'compact' => true,'eager' => $loop->index < 4]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'compact' => true,'eager' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loop->index < 4)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $attributes = $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $component = $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal876be2cf017156a88aa3c73cbba82096 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal876be2cf017156a88aa3c73cbba82096 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.pagination','data' => ['paginator' => $paginator]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paginator)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal876be2cf017156a88aa3c73cbba82096)): ?>
<?php $attributes = $__attributesOriginal876be2cf017156a88aa3c73cbba82096; ?>
<?php unset($__attributesOriginal876be2cf017156a88aa3c73cbba82096); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal876be2cf017156a88aa3c73cbba82096)): ?>
<?php $component = $__componentOriginal876be2cf017156a88aa3c73cbba82096; ?>
<?php unset($__componentOriginal876be2cf017156a88aa3c73cbba82096); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.recently-viewed','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.recently-viewed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3)): ?>
<?php $attributes = $__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3; ?>
<?php unset($__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3)): ?>
<?php $component = $__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3; ?>
<?php unset($__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/catalog/v1.blade.php ENDPATH**/ ?>