<?php if(empty($help)): ?>
    <div class="text-sm text-gray-500">У цього шаблону немає документованих змінних.</div>
<?php else: ?>
    <div class="space-y-2">
        <?php $__currentLoopData = $help; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-start gap-3 text-sm">
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono shrink-0">&#123;&#123; <?php echo e($item['key'] ?? ''); ?> &#125;&#125;</code>
                <span class="text-gray-600 dark:text-gray-400"><?php echo e($item['desc'] ?? ''); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/email-template-variables.blade.php ENDPATH**/ ?>