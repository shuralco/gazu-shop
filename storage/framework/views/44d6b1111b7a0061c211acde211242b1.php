<?php $__env->startSection('title', 'Каталог · Rich list — GAZU'); ?>

<?php $__env->startSection('content'); ?>
    <div class="gazu-container">
        <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [
            ['Головна', route('gazu.home')],
            ['Каталог', route('gazu.catalog')],
            'Двигун',
            'Фільтри',
            'Масляні фільтри',
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['Головна', route('gazu.home')],
            ['Каталог', route('gazu.catalog')],
            'Двигун',
            'Фільтри',
            'Масляні фільтри',
        ])]); ?>
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

        <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0">Масляні фільтри</h1>
        <div class="text-sm text-[var(--gazu-graphite)] mb-4.5 mt-1">Розширений вигляд зі специфікаціями та сумісністю.</div>
        <?php echo $__env->make('gazu.partials.active-filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="gazu-grid-sidebar">
            <?php if (isset($component)) { $__componentOriginal939926802e1c3fbb39005b130947314c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal939926802e1c3fbb39005b130947314c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.filter-panel','data' => ['priceRange' => $priceRange,'availableBrands' => $availableBrands,'selectedBrands' => $selectedBrands,'availableConditions' => $availableConditions ?? null,'selectedConditions' => $selectedConditions ?? [],'inStockOnly' => $inStockOnly,'searchQuery' => $searchQuery,'category' => $category]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.filter-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['priceRange' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($priceRange),'availableBrands' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableBrands),'selectedBrands' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedBrands),'availableConditions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableConditions ?? null),'selectedConditions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedConditions ?? []),'inStockOnly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inStockOnly),'searchQuery' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($searchQuery),'category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category)]); ?>
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
            <div class="min-w-0">
                <?php echo $__env->make('gazu.partials.sort-bar', ['count' => $totalCount, 'view' => 'list', 'currentSort' => $currentSort], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="flex flex-col gap-3 mt-4">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                            $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
                            $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
                            $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
                            $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
                            $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
                            $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
                            $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
                            $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
                            $rating = is_object($p) ? (float)($p->rating ?? 0) : (float)($p['rating'] ?? 0);
                            $reviews = is_object($p) ? (int)($p->reviews ?? 0) : (int)($p['reviews'] ?? 0);
                            $fits = is_object($p) ? ($p->fits ?? '') : ($p['fits'] ?? '');
                            $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
                            $warranty = $gazuSettings['gazu_default_warranty'] ?? '12 місяців';
                            $analogsArr = is_object($p) ? ($p->analogs ?? null) : ($p['analogs'] ?? null);
                            $analogsCount = is_array($analogsArr) ? count($analogsArr) : 0;
                        ?>
                        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 gazu-grid-list font-text">
                            <a wire:navigate href="<?php echo e($url); ?>" class="bg-[var(--gazu-paper)] rounded-md flex items-center justify-center" style="aspect-ratio:1;">
                                <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '140']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '140']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                            </a>
                            <div class="flex flex-col gap-2 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.condition-badge','data' => ['value' => ''.e($condition).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($condition).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $attributes = $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $component = $__componentOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
                                    <span class="gazu-display font-semibold text-sm text-[var(--gazu-ink)]"><?php echo e($brand); ?></span>
                                    <span class="flex-1"></span>
                                    <div class="flex items-center gap-1 whitespace-nowrap">
                                        <div class="flex gap-px text-[var(--gazu-warn)]">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'star','size' => '12','fill' => ''.e($i <= floor($rating) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','size' => '12','fill' => ''.e($i <= floor($rating) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-[11px] text-[var(--gazu-graphite)]"><?php echo e(number_format($rating, 1)); ?> (<?php echo e($reviews); ?>)</span>
                                    </div>
                                </div>
                                <a wire:navigate href="<?php echo e($url); ?>" class="gazu-display text-[17px] font-semibold text-[var(--gazu-ink)] no-underline"><?php echo e($name); ?></a>
                                <div class="flex gap-3.5 text-xs text-[var(--gazu-graphite)] flex-wrap">
                                    <span class="whitespace-nowrap"><span class="text-[var(--gazu-muted)]">Артикул:</span> <span class="gazu-mono text-[var(--gazu-ink)]"><?php echo e($oem); ?></span></span>
                                    <span class="whitespace-nowrap"><span class="text-[var(--gazu-muted)]">Гарантія:</span> <?php echo e($warranty); ?></span>
                                </div>
                                <?php if($fits): ?>
                                    <div class="text-xs text-[var(--gazu-graphite)] px-2.5 py-2 bg-[var(--gazu-mist)] rounded flex gap-2">
                                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '14','stroke' => 'var(--gazu-blue)','class' => 'shrink-0 mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '14','stroke' => 'var(--gazu-blue)','class' => 'shrink-0 mt-0.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                                        <span><span class="text-[var(--gazu-ink)] font-medium">Сумісність:</span> <?php echo e($fits); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex gap-2.5 mt-1">
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Технічні характеристики</button>
                                    <span class="text-[var(--gazu-line-2)]">·</span>
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Аналоги<?php echo e($analogsCount ? ' ('.$analogsCount.')' : ''); ?></button>
                                    <span class="text-[var(--gazu-line-2)]">·</span>
                                    <button type="button" class="bg-transparent border-0 p-0 text-[var(--gazu-blue)] text-xs cursor-pointer">Інструкція</button>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2.5 justify-between border-l border-[var(--gazu-line)] pl-5">
                                <div>
                                    <?php if($oldPrice): ?><div class="text-xs text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float)$oldPrice, 0, '.', ' ')); ?> ₴</div><?php endif; ?>
                                    <div class="gazu-display text-[28px] font-bold text-[var(--gazu-ink)] leading-none"><?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</div>
                                    <div class="mt-2"><?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock','data' => ['qty' => ''.e($qty).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['qty' => ''.e($qty).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $attributes = $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $component = $__componentOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?></div>
                                    <div class="text-xs text-[var(--gazu-graphite)] mt-1 inline-flex gap-1 items-center">
                                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'truck','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Доставка завтра
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" class="flex-1 py-3 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5">
                                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> У кошик
                                    </button>
                                    <button type="button" class="w-10 bg-[var(--gazu-surface)] text-[var(--gazu-graphite)] border border-[var(--gazu-line)] rounded-md cursor-pointer flex items-center justify-center">
                                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'heart','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if (isset($component)) { $__componentOriginal876be2cf017156a88aa3c73cbba82096 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal876be2cf017156a88aa3c73cbba82096 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.pagination','data' => ['paginator' => $paginator ?? null,'current' => 1,'total' => 12]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($paginator ?? null),'current' => 1,'total' => 12]); ?>
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
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/catalog/v3.blade.php ENDPATH**/ ?>