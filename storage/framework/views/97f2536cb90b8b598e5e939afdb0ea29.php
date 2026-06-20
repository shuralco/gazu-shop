<?php $__env->startSection('title', '404 — Сторінку не знайдено · GAZU'); ?>
<?php $__env->startSection('description', 'Шукана сторінка не існує. Скористайтеся пошуком або перейдіть до каталогу запчастин для китайських авто.'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $s = $gazuSettings ?? [];
    $title = $s['gazu_404_title'] ?? 'Сторінку не знайдено';
    $desc  = $s['gazu_404_desc']  ?? 'Можливо, посилання застаріле або сторінку перенесли. Спробуйте знайти потрібне нижче.';
    // Популярні категорії — top 8 з cache (withCount генерує products_count з relation).
    $popular404 = \Cache::remember('home:popular404', 3600, function () {
        return \App\Models\Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(8)
            ->get(['id', 'slug', 'title']);
    });
?>
<section class="gazu-container py-12 sm:py-20">
    <div class="max-w-2xl mx-auto text-center">
        <?php $badge404 = trim((string) ($s['gazu_404_badge'] ?? '')); ?>
        <div class="relative inline-block">
            <div class="gazu-display font-bold text-[var(--gazu-ink)] m-0 leading-none tracking-tight" style="font-size: clamp(80px, 16vw, 160px); letter-spacing: -0.05em;">404</div>
            <?php if($badge404 !== ''): ?>
                <div class="absolute -top-2 -right-6 sm:-right-10 rotate-12">
                    <span class="inline-block px-3 py-1 bg-[var(--gazu-danger)] text-[var(--gazu-on-brand)] rounded-md gazu-mono text-[11px] uppercase tracking-widest font-bold shadow-[0_8px_16px_-8px_rgba(178,59,59,0.4)]"><?php echo e($badge404); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="gazu-display text-[24px] sm:text-[32px] font-semibold text-[var(--gazu-ink)] mt-5 mb-3 leading-tight"><?php echo e($title); ?></h1>
        <p class="text-[15px] text-[var(--gazu-graphite)] mb-7 max-w-md mx-auto leading-relaxed"><?php echo e($desc); ?></p>

        
        <form method="GET" action="<?php echo e(route('gazu.search')); ?>" class="max-w-md mx-auto mb-10">
            <div class="relative">
                <input type="text" name="q" autofocus
                       placeholder="Артикул, бренд або назва запчастини"
                       class="w-full pl-12 pr-4 py-3.5 text-[14px] bg-[var(--gazu-surface)] border-2 border-[var(--gazu-line)] focus:border-[var(--gazu-ink)] rounded-lg outline-none transition-colors">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--gazu-graphite)]" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
        </form>

        <div class="flex gap-2 justify-center flex-wrap mb-12">
            <a wire:navigate href="<?php echo e(route('gazu.home')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-md text-[14px] font-semibold no-underline hover:bg-[var(--gazu-ink-2)] transition-colors">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                На головну
            </a>
            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[var(--gazu-surface)] text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] transition-colors">
                Каталог
            </a>
            <a wire:navigate href="<?php echo e(route('gazu.contacts')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[var(--gazu-surface)] text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] transition-colors">
                Контакти
            </a>
        </div>

        <?php if($popular404 && $popular404->isNotEmpty()): ?>
            <div class="text-left">
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3 text-center">Популярні категорії</div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    <?php $__currentLoopData = $popular404; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a wire:navigate href="<?php echo e(url('/'.$cat->slug)); ?>"
                           class="px-3 py-2.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] rounded-md text-[13px] text-[var(--gazu-ink)] no-underline text-center transition-colors truncate">
                            <?php echo e(is_array($cat->title) ? ($cat->title['uk'] ?? '—') : $cat->title); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/404.blade.php ENDPATH**/ ?>