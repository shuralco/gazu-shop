<?php
    $__active = \App\Support\Locales::switchable();
    $__cur = app()->getLocale();
?>
<?php if($__active): ?>
    <div class="inline-flex items-center gap-1.5" aria-label="Мова сайту">
        <?php $__currentLoopData = \App\Support\Locales::labels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $__code => $__label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($__flag = \App\Support\Locales::FLAGS[$__code] ?? ''); ?>
            <?php if($__code === $__cur): ?>
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-white font-semibold"
                      style="background:rgba(255,255,255,.14);font-size:12px;" title="<?php echo e($__label); ?>">
                    <?php echo e($__flag); ?> <?php echo e(mb_strtoupper($__code)); ?>

                </span>
            <?php else: ?>
                <a href="<?php echo e(url('/locale/'.$__code)); ?>"
                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded no-underline hover:text-white"
                   style="color:#CDD3DC;font-size:12px;" title="Перейти на <?php echo e($__label); ?>">
                    <?php echo e($__flag); ?> <?php echo e(mb_strtoupper($__code)); ?>

                </a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/modules/multilang/resources/views/switcher.blade.php ENDPATH**/ ?>