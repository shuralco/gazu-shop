<?php $__env->startSection('title', 'Каталог · B2B — GAZU'); ?>

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

        <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0">Масляні фільтри</h1>
        <div class="text-[13px] text-[var(--gazu-graphite)] mb-4.5 mt-1"><?php echo e($products->count() * 24); ?> артикулів · режим B2B-таблиці</div>
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
                <div class="mt-4 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left" style="border-collapse: collapse;">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr class="border-b border-[var(--gazu-line)]">
                                <th class="py-2.5 px-2 font-medium"></th>
                                <th class="py-2.5 px-2 font-medium">Назва · Артикул</th>
                                <th class="py-2.5 px-2 font-medium">Бренд</th>
                                <th class="py-2.5 px-2 font-medium">Стан</th>
                                <th class="py-2.5 px-2 font-medium">Сумісність</th>
                                <th class="py-2.5 px-2 font-medium">Наявн.</th>
                                <th class="py-2.5 px-2 font-medium text-right">Ціна</th>
                                <th class="py-2.5 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="text-[13px]">
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
                                    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
                                    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                                    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
                                    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
                                    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
                                    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
                                    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
                                    $fits = is_object($p) ? ($p->fits ?? '') : ($p['fits'] ?? '');
                                ?>
                                <tr class="border-b border-[var(--gazu-line)]">
                                    <td class="py-2.5 px-2" style="width: 56px;">
                                        <div class="w-11 h-11 bg-[var(--gazu-paper)] rounded flex items-center justify-center">
                                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '38']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '38']); ?>
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
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <div class="text-[var(--gazu-ink)] font-medium mb-0.5"><?php echo e($name); ?></div>
                                        <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono"><?php echo e($oem); ?></div>
                                    </td>
                                    <td class="py-2.5 px-2 gazu-display font-semibold text-[var(--gazu-ink)] text-xs"><?php echo e($brand); ?></td>
                                    <td class="py-2.5 px-2"><?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
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
<?php endif; ?></td>
                                    <td class="py-2.5 px-2 text-[var(--gazu-graphite)] text-xs" style="max-width: 160px;"><?php echo e($fits); ?></td>
                                    <td class="py-2.5 px-2 whitespace-nowrap"><?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
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
<?php endif; ?></td>
                                    <td class="py-2.5 px-2 text-right whitespace-nowrap">
                                        <div class="gazu-display font-bold text-[15px] text-[var(--gazu-ink)]"><?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</div>
                                        <?php if($oldPrice): ?><div class="text-[11px] text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float)$oldPrice, 0, '.', ' ')); ?> ₴</div><?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-2" style="width: 92px;">
                                        <button type="button" class="px-3 py-2 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded text-xs font-medium cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
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
<?php endif; ?> Купити
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/catalog/v2.blade.php ENDPATH**/ ?>