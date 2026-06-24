<?php $__env->startSection('title', ($p->name ?? 'Товар') . ' · sticky-buy — GAZU'); ?>

<?php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);

    // Характеристики з ТАКСОНОМІЇ — призначені фільтри товару, згруповані за
    // FilterGroup (Виробник, Положення, Тип, В'язкість…). Підтягуються авто з
    // pivot filter_products. Доповнюються free-form specifications (екстра-поля).
    $specs = [];
    $monoRe = '/^\d|[\.,×]|^[A-Z]\d/';
    if (is_object($p) && method_exists($p, 'relationLoaded') && $p->relationLoaded('filters')) {
        $byGroup = [];
        foreach ($p->filters as $f) {
            $label = optional($f->filterGroup)->title ?: optional($f->filterGroup)->name;
            $val = $f->name ?: $f->title;
            if ($label && $val) {
                $byGroup[$label][] = (string) $val;
            }
        }
        foreach ($byGroup as $label => $vals) {
            $v = implode(', ', array_values(array_unique($vals)));
            $specs[] = [(string) $label, $v, (bool) preg_match($monoRe, $v)];
        }
    }
    // free-form specifications — додаємо лише назви, яких ще немає з таксономії.
    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs)) {
        $have = array_map(fn ($s) => mb_strtolower($s[0]), $specs);
        foreach ($rawSpecs as $k => $v) {
            if (in_array(mb_strtolower((string) $k), $have, true)) {
                continue;
            }
            $specs[] = [(string) $k, (string) $v, (bool) preg_match($monoRe, (string) $v)];
        }
    }
    // Fallback коли товар ще без характеристик.
    if (empty($specs)) {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', 'Новий', false],
            ['Гарантія', '12 місяців', false],
        ];
    }
    $half = (int) ceil(count($specs) / 2);
?>

<?php $__env->startSection('content'); ?>
    <div style="max-width: 1100px; margin-inline: auto; padding-inline: 24px; padding-bottom: 80px;">
        <?php echo $__env->make('gazu.partials.product-breadcrumbs', ['p' => $p, 'brand' => $brand, 'oem' => $oem, 'name' => $name, 'skipHome' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid lg:grid-cols-2 gap-10">
            <div class="grid grid-cols-[60px_1fr] gap-3">
                <div class="flex flex-col gap-2">
                    <?php for($i = 0; $i < 4; $i++): ?>
                        <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer" style="border: 1.5px solid <?php echo e($i === 0 ? 'var(--gazu-ink)' : 'var(--gazu-line)'); ?>;">
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '42']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '42']); ?>
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
                <div class="aspect-square bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '400']); ?>
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
            </div>

            <div>
                <div class="flex items-center gap-2.5 mb-2">
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
                    <span class="gazu-display font-semibold text-[var(--gazu-ink)]"><?php echo e($brand); ?></span>
                </div>
                <h1 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0 mb-1.5 leading-tight"><?php echo e($name); ?></h1>
                <?php $soldV3 = is_object($p) ? (int) ($p->sold_count ?? 0) : 0; ?>
                <?php if($oem || $soldV3 > 0): ?>
                    <div class="text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-3.5">
                        <?php if($oem): ?>Артикул <?php echo e($oem); ?><?php endif; ?>
                        <?php if($oem && $soldV3 > 0): ?> · <?php endif; ?>
                        <?php if($soldV3 > 0): ?><?php echo e($soldV3); ?> продано<?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="flex items-baseline gap-3 mb-3.5">
                    <span class="gazu-display font-bold text-[var(--gazu-ink)]" style="font-size: 36px; letter-spacing: -0.03em;"><?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</span>
                    <?php if($oldPrice): ?>
                        <span class="text-sm text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float)$oldPrice, 0, '.', ' ')); ?> ₴</span>
                        <?php if($discount): ?>
                            <span class="text-[11px] gazu-mono px-1.5 py-0.5 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] rounded">−<?php echo e($discount); ?>%</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
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
<?php endif; ?>
                <?php if(module('gazu_garage')->enabled()): ?>
                    <?php $primaryCar = auth()->check() ? auth()->user()->primaryCar : null; ?>
                    <?php if($primaryCar): ?>
                        <div class="mt-4.5 p-3.5 bg-[var(--gazu-success-bg)] rounded-lg flex gap-2.5">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '18','stroke' => 'var(--gazu-success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '18','stroke' => 'var(--gazu-success)']); ?>
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
                            <div class="text-[13px] text-[var(--gazu-ink)]">
                                Підходить для <span class="font-semibold"><?php echo e($primaryCar->display_name); ?><?php if($primaryCar->engine): ?>, <?php echo e($primaryCar->engine); ?><?php endif; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="mt-4.5 gazu-display text-base font-semibold mb-2.5">Опис</div>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0">
                    Накручуваний масляний фільтр з фільтруючим елементом з целюлозного волокна.
                    Перепускний клапан запобігає відсутності змащування при холодному пуску. Зворотний клапан утримує масло у фільтрі після зупинки двигуна.
                </p>
                <div class="mt-5 grid grid-cols-2 gap-2 text-xs">
                    <?php $__currentLoopData = [
                        ['truck', 'Доставка завтра'],
                        ['shield', 'Гарантія 12 міс'],
                        ['return', 'Повернення 14 днів'],
                        ['box', 'Оригінальна упаковка'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic, $t]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex gap-2 items-center px-2.5 py-2 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md text-[var(--gazu-graphite)]">
                            <span class="text-[var(--gazu-blue)]"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => ''.e($ic).'','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($ic).'','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></span><?php echo e($t); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <?php echo $__env->make('gazu.partials.product-tabs', ['active' => 'spec'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="mt-5 grid lg:grid-cols-2 gap-6">
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg px-4">
                <?php $__currentLoopData = array_slice($specs, 0, $half); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $v, $mono]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                        <span class="text-[var(--gazu-graphite)]"><?php echo e($k); ?></span>
                        <span class="text-[var(--gazu-ink)] <?php echo e($mono ? 'gazu-mono font-medium' : ''); ?>"><?php echo e($v); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg px-4">
                <?php $__currentLoopData = array_slice($specs, $half); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $v, $mono]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                        <span class="text-[var(--gazu-graphite)]"><?php echo e($k); ?></span>
                        <span class="text-[var(--gazu-ink)] <?php echo e($mono ? 'gazu-mono font-medium' : ''); ?>"><?php echo e($v); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <div class="fixed bottom-0 left-0 right-0 bg-[var(--gazu-surface)] border-t border-[var(--gazu-line)] px-6 py-3.5 flex items-center gap-4 z-10" style="box-shadow: 0 -4px 16px rgba(14,27,44,0.06);">
        <div class="w-11 h-11 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center shrink-0">
            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '36']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '36']); ?>
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
        <div class="min-w-0 flex-1 hidden md:block">
            <div class="text-[13px] font-medium text-[var(--gazu-ink)] truncate"><?php echo e($name); ?></div>
            <div class="text-[11px] text-[var(--gazu-graphite)]"><?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
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
        </div>
        <div class="gazu-display font-bold text-[var(--gazu-ink)]" style="font-size: 24px; letter-spacing: -0.02em;"><?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</div>
        <div class="flex items-center border border-[var(--gazu-line)] rounded-md">
            <button type="button" class="w-9 h-10 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'minus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></button>
            <input value="1" class="w-11 text-center border-0 py-2.5 text-sm gazu-mono font-medium outline-none">
            <button type="button" class="w-9 h-10 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></button>
        </div>
        <button type="button" class="px-5 py-3 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md font-medium text-sm cursor-pointer inline-flex items-center gap-2">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> <span class="hidden sm:inline">У кошик · <?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</span>
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/product/v3.blade.php ENDPATH**/ ?>