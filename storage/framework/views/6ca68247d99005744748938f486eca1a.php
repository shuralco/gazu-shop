<?php
    $s = $gazuSettings ?? [];
    $stats = $shopStats ?? [];
    $enabled = $s['gazu_seo_enabled'] ?? true;
    $title = trim((string) ($s['gazu_seo_title'] ?? ''));
    $html = trim((string) ($s['gazu_seo_html'] ?? ''));
    $productsLabel = $stats['products_label'] ?? '1 278+ артикулів';

    // 4 part-image collage для правого боку hero (case study look)
    $collageKinds = [
        ['kind' => 'filter', 'seed' => 1],
        ['kind' => 'brake-disc', 'seed' => 2],
        ['kind' => 'oil', 'seed' => 3],
        ['kind' => 'spark', 'seed' => 4],
    ];

    $usps = [
        ['num' => $productsLabel,           'label' => 'оригіналів і аналогів', 'icon' => 'parts'],
        ['num' => '12+',                    'label' => 'місяців гарантії',      'icon' => 'shield'],
        ['num' => '1-3',                    'label' => 'дні доставки по UA',    'icon' => 'truck'],
        ['num' => '14',                     'label' => 'днів на повернення',    'icon' => 'return'],
    ];
?>
<?php if($enabled && ($title || $html)): ?>
<section class="bg-gradient-to-b from-[var(--gazu-paper)] to-white pt-16 sm:pt-20 pb-12">
    <div class="gazu-container">

        
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center mb-14 lg:mb-20">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[var(--gazu-mist)] rounded-full mb-5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-blue)]"></span>
                    <span class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase">Про магазин</span>
                </div>
                <?php if($title): ?>
                    <h2 class="gazu-display text-[28px] sm:text-[40px] lg:text-[44px] font-semibold text-[var(--gazu-ink)] leading-[1.1] tracking-[-0.02em] m-0 mb-5">
                        <?php echo e($title); ?>

                    </h2>
                <?php endif; ?>
                <p class="text-[15px] sm:text-[17px] text-[var(--gazu-graphite)] leading-relaxed max-w-xl mb-7">
                    Спеціалізуємось на запчастинах для китайських автомобілів. Працюємо тільки з перевіреними постачальниками, кожна позиція проходить контроль якості перед відправкою.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white rounded-md text-[14px] font-semibold no-underline transition-colors">
                        Дивитись каталог
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="tel:0800751024" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    0 800 75 10 24
                    </a>
                </div>
            </div>

            
            <div class="relative aspect-square max-w-md mx-auto lg:mx-0 lg:ml-auto w-full">
                
                <div class="absolute -top-4 -left-4 w-24 h-24 rounded-2xl bg-[var(--gazu-blue)] opacity-10"></div>
                <div class="absolute -bottom-4 -right-4 w-32 h-32 rounded-2xl bg-[var(--gazu-warn)] opacity-10"></div>

                <div class="relative grid grid-cols-2 gap-3 sm:gap-4">
                    <?php $__currentLoopData = $collageKinds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-[0_8px_24px_-12px_rgba(14,27,44,0.15)] <?php echo e($i === 0 ? 'translate-y-4' : ($i === 1 ? '' : ($i === 2 ? '' : 'translate-y-4'))); ?> bg-white">
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($c['kind']).'','seed' => $c['seed'],'fit' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($c['kind']).'','seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['seed']),'fit' => true]); ?>
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
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-14 lg:mb-16">
            <?php $__currentLoopData = $usps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="relative bg-white border border-[var(--gazu-line)] rounded-xl p-5 sm:p-6 hover:border-[var(--gazu-ink)] transition-colors group">
                    <div class="gazu-display text-[28px] sm:text-[36px] font-bold text-[var(--gazu-ink)] leading-none mb-1.5 tracking-tight"><?php echo e($usp['num']); ?></div>
                    <div class="text-[12px] sm:text-[13px] text-[var(--gazu-graphite)] leading-snug"><?php echo e($usp['label']); ?></div>
                    <div class="absolute top-5 right-5 w-7 h-7 rounded-md bg-[var(--gazu-mist)] inline-flex items-center justify-center text-[var(--gazu-blue)] group-hover:bg-[var(--gazu-ink)] group-hover:text-white transition-colors">
                        <?php if($usp['icon'] === 'parts'): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/></svg>
                        <?php elseif($usp['icon'] === 'shield'): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php elseif($usp['icon'] === 'truck'): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <?php else: ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if($html): ?>
            <div class="gazu-seo-content gazu-prose-2col text-[var(--gazu-graphite)] max-w-none">
                <?php echo $html; ?>

            </div>
        <?php endif; ?>

        
        <div class="mt-14 lg:mt-20 relative overflow-hidden rounded-2xl bg-[var(--gazu-ink)] text-white p-8 sm:p-10 lg:p-14">
            <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 14px 14px;"></div>
            <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-[var(--gazu-blue)] opacity-20 blur-3xl"></div>
            <div class="relative grid lg:grid-cols-2 gap-6 items-center">
                <div>
                    <h3 class="gazu-display text-[22px] sm:text-[28px] font-semibold leading-tight m-0 mb-2">Не знайшли потрібну деталь?</h3>
                    <p class="text-[14px] sm:text-[15px] text-white/75 leading-relaxed m-0 max-w-md">
                        Зателефонуйте — підберемо за артикулом OEM або за маркою/моделлю/двигуном. Безкоштовна консультація.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="tel:0800751024" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline hover:bg-[var(--gazu-paper)] transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        0 800 75 10 24
                    </a>
                    <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="inline-flex items-center gap-2 px-5 py-3 bg-transparent text-white rounded-md text-[14px] font-semibold no-underline border border-white/40 hover:bg-white/10 transition-colors">
                        Дивитись каталог
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/seo-text.blade.php ENDPATH**/ ?>