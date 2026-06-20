<?php $__env->startSection('title', ($p->name ?? 'Товар') . ' · інженерний — GAZU'); ?>

<?php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);

    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs) && ! empty($rawSpecs)) {
        $specs = [];
        foreach ($rawSpecs as $k => $v) {
            $isMono = preg_match('/^\d|[\.,×]|^[A-Z]\d/', (string) $v);
            $specs[] = [(string) $k, (string) $v, (bool) $isMono];
        }
    } else {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', $condition ?? 'Новий', false],
            ['Гарантія', '12 місяців', false],
        ];
    }

    // No demo fallback — empty arrays mean compat/analogs sections hide.
    $compat = [];
    $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
    if (is_array($rawCompat)) {
        foreach ($rawCompat as $row) {
            if (is_array($row)) {
                $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—'];
            }
        }
    }

    $analogs = [];
    $rawAnalogs = is_object($p) ? ($p->analogs ?? null) : ($p['analogs'] ?? null);
    if (is_array($rawAnalogs)) {
        foreach ($rawAnalogs as $row) {
            if (is_array($row)) {
                $analogs[] = [
                    $row['brand'] ?? '—',
                    $row['oem'] ?? '—',
                    (float) ($row['price'] ?? 0),
                    (int) ($row['qty'] ?? 0),
                    (float) ($row['rating'] ?? 0),
                ];
            }
        }
    }
?>

<?php $__env->startSection('content'); ?>
    <div class="gazu-container">
        <?php echo $__env->make('gazu.partials.product-breadcrumbs', compact('p', 'brand', 'oem', 'name'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="gazu-grid-buy-left">
            <div>
                <div class="aspect-[4/3] bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '320']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '320']); ?>
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
                </div>
                <div class="grid grid-cols-5 gap-2 mt-2">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer" style="border: 1.5px solid <?php echo e($i === 1 ? 'var(--gazu-ink)' : 'var(--gazu-line)'); ?>;">
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '50']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '50']); ?>
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
                    <?php endfor; ?>
                </div>

                <div class="mt-4.5 p-4 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg">
                    <div class="gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-widest uppercase mb-2.5">Розміри (мм)</div>
                    <svg width="100%" height="120" viewBox="0 0 400 120">
                        <rect x="120" y="30" width="160" height="60" fill="var(--gazu-bone)" stroke="var(--gazu-ink)" stroke-width="1.5"/>
                        <line x1="120" y1="20" x2="280" y2="20" stroke="var(--gazu-graphite)"/>
                        <line x1="120" y1="15" x2="120" y2="25" stroke="var(--gazu-graphite)"/>
                        <line x1="280" y1="15" x2="280" y2="25" stroke="var(--gazu-graphite)"/>
                        <text x="200" y="13" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="var(--gazu-ink)">76,2 мм</text>
                        <line x1="290" y1="30" x2="290" y2="90" stroke="var(--gazu-graphite)"/>
                        <line x1="285" y1="30" x2="295" y2="30" stroke="var(--gazu-graphite)"/>
                        <line x1="285" y1="90" x2="295" y2="90" stroke="var(--gazu-graphite)"/>
                        <text x="305" y="64" font-size="11" fill="var(--gazu-ink)">79 мм</text>
                        <circle cx="200" cy="60" r="20" fill="#fff" stroke="var(--gazu-blue)" stroke-width="1.5" stroke-dasharray="3 3"/>
                        <text x="200" y="64" text-anchor="middle" font-size="10" fill="var(--gazu-blue)">M20×1.5</text>
                    </svg>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-2.5 mb-2.5">
                    <?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.condition-badge','data' => ['value' => 'Новий']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'Новий']); ?>
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
                    <span class="gazu-mono text-[11px] px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded">Артикул</span>
                    <span class="gazu-display font-semibold text-[var(--gazu-ink)] text-sm"><?php echo e($brand); ?></span>
                </div>
                <h1 class="gazu-display text-[32px] font-semibold text-[var(--gazu-ink)] m-0 mb-1.5 leading-tight"><?php echo e($name); ?></h1>
                <?php if($oem): ?>
                    <div class="text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-4.5">Артикул <?php echo e($oem); ?></div>
                <?php endif; ?>

                <?php if (isset($component)) { $__componentOriginala3e840b12d118989ee8c832a7cb2ee4b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.buy-panel','data' => ['price' => $price,'oldPrice' => $oldPrice,'qty' => $qty,'discount' => $discount,'productId' => is_object($p) ? ($p->id ?? null) : null,'name' => $name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.buy-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($price),'oldPrice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldPrice),'qty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($qty),'discount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($discount),'productId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(is_object($p) ? ($p->id ?? null) : null),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b)): ?>
<?php $attributes = $__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b; ?>
<?php unset($__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala3e840b12d118989ee8c832a7cb2ee4b)): ?>
<?php $component = $__componentOriginala3e840b12d118989ee8c832a7cb2ee4b; ?>
<?php unset($__componentOriginala3e840b12d118989ee8c832a7cb2ee4b); ?>
<?php endif; ?>

                <div class="mt-7 gazu-display text-lg font-semibold mb-3">Повні характеристики</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg px-4">
                    <?php $__currentLoopData = $specs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $v, $mono]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                            <span class="text-[var(--gazu-graphite)]"><?php echo e($k); ?></span>
                            <span class="text-[var(--gazu-ink)] <?php echo e($mono ? 'gazu-mono font-medium' : ''); ?>"><?php echo e($v); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="mt-10 grid lg:grid-cols-2 gap-6">
            <div>
                <div class="gazu-display text-[22px] font-semibold mb-3.5">Сумісність</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr>
                                <th class="px-3.5 py-3 font-medium">Марка</th>
                                <th class="px-3.5 py-3 font-medium">Модель</th>
                                <th class="px-3.5 py-3 font-medium">Роки</th>
                                <th class="px-3.5 py-3 font-medium">Двигун</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $compat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-t border-[var(--gazu-line)]">
                                    <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]"><?php echo e($r[0]); ?></td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-ink)]"><?php echo e($r[1]); ?></td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs"><?php echo e($r[2]); ?></td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs"><?php echo e($r[3]); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="gazu-display text-[22px] font-semibold mb-3.5">Аналоги та замінники</div>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                            <tr>
                                <th class="px-3.5 py-3 font-medium">Виробник</th>
                                <th class="px-3.5 py-3 font-medium">Артикул</th>
                                <th class="px-3.5 py-3 font-medium">Рейтинг</th>
                                <th class="px-3.5 py-3 font-medium">Наявність</th>
                                <th class="px-3.5 py-3 font-medium text-right">Ціна</th>
                                <th class="px-3.5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $analogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$brnd, $oemA, $priceA, $qtyA, $rate]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-t border-[var(--gazu-line)]">
                                    <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]"><?php echo e($brnd); ?></td>
                                    <td class="px-3.5 py-3 text-[var(--gazu-ink)] gazu-mono text-xs"><?php echo e($oemA); ?></td>
                                    <td class="px-3.5 py-3">
                                        <div class="flex gap-1.5 items-center">
                                            <div class="flex gap-px text-[var(--gazu-warn)]">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'star','size' => '11','fill' => ''.e($i <= floor($rate) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','size' => '11','fill' => ''.e($i <= floor($rate) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']); ?>
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
                                            <span class="text-[11px] text-[var(--gazu-graphite)]"><?php echo e($rate); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3"><?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock','data' => ['qty' => ''.e($qtyA).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['qty' => ''.e($qtyA).'']); ?>
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
                                    <td class="px-3.5 py-3 text-right gazu-display font-bold text-[var(--gazu-ink)] text-[15px]"><?php echo e($priceA); ?> ₴</td>
                                    <td class="px-3.5 py-3 text-right">
                                        <button type="button" class="px-3 py-1.5 bg-[var(--gazu-paper)] text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded text-xs cursor-pointer">У кошик</button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/product/v2.blade.php ENDPATH**/ ?>