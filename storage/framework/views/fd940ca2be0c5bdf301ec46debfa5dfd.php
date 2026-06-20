<?php if (isset($component)) { $__componentOriginalaa758e6a82983efcbf593f765e026bd9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa758e6a82983efcbf593f765e026bd9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => $__env->getContainer()->make(Illuminate\View\Factory::class)->make('mail::message'),'data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mail::message'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
# <?php echo e(__("emails.np.title.{$template}")); ?>


<?php echo e(__('emails.np.greeting')); ?><?php echo e($order->first_name ? ', ' . $order->first_name : ''); ?>!

<?php echo e(__("emails.np.body.{$template}")); ?>


<?php if (isset($component)) { $__componentOriginal91214b38020aa1d764d4a21e693f703c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91214b38020aa1d764d4a21e693f703c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => $__env->getContainer()->make(Illuminate\View\Factory::class)->make('mail::panel'),'data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mail::panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
**<?php echo e(__('emails.np.order_label')); ?>:** #<?php echo e($order->id); ?><br>
**<?php echo e(__('emails.np.np_status_label')); ?>:** <?php echo e($shipment->np_status ?? __('emails.np.awaiting')); ?><br>
<?php if($shipment->ttn): ?>
**<?php echo e(__('emails.np.ttn_label')); ?>:** <?php echo e($shipment->ttn); ?><br>
<?php endif; ?>
<?php if($shipment->estimated_delivery_date): ?>
**<?php echo e(__('emails.np.eta_label')); ?>:** <?php echo e($shipment->estimated_delivery_date); ?><br>
<?php endif; ?>
<?php if($shipment->recipient_warehouse_name): ?>
**<?php echo e(__('emails.np.warehouse_label')); ?>:** <?php echo e($shipment->recipient_warehouse_name); ?><br>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91214b38020aa1d764d4a21e693f703c)): ?>
<?php $attributes = $__attributesOriginal91214b38020aa1d764d4a21e693f703c; ?>
<?php unset($__attributesOriginal91214b38020aa1d764d4a21e693f703c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91214b38020aa1d764d4a21e693f703c)): ?>
<?php $component = $__componentOriginal91214b38020aa1d764d4a21e693f703c; ?>
<?php unset($__componentOriginal91214b38020aa1d764d4a21e693f703c); ?>
<?php endif; ?>

<?php if($trackingUrl): ?>
<?php if (isset($component)) { $__componentOriginal15a5e11357468b3880ae1300c3be6c4f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15a5e11357468b3880ae1300c3be6c4f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => $__env->getContainer()->make(Illuminate\View\Factory::class)->make('mail::button'),'data' => ['url' => $trackingUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mail::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($trackingUrl)]); ?>
<?php echo e(__('emails.np.track_button')); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15a5e11357468b3880ae1300c3be6c4f)): ?>
<?php $attributes = $__attributesOriginal15a5e11357468b3880ae1300c3be6c4f; ?>
<?php unset($__attributesOriginal15a5e11357468b3880ae1300c3be6c4f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15a5e11357468b3880ae1300c3be6c4f)): ?>
<?php $component = $__componentOriginal15a5e11357468b3880ae1300c3be6c4f; ?>
<?php unset($__componentOriginal15a5e11357468b3880ae1300c3be6c4f); ?>
<?php endif; ?>
<?php endif; ?>

<?php echo e(__('emails.np.questions')); ?> [<?php echo e(\App\Models\DisplaySetting::get('header_email', 'admin@simpleshop.com')); ?>](mailto:<?php echo e(\App\Models\DisplaySetting::get('header_email', 'admin@simpleshop.com')); ?>).

<?php echo e(__('emails.np.regards')); ?>,<br>
<?php echo e(\App\Models\DisplaySetting::get('site_name', 'SimpleShop')); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa758e6a82983efcbf593f765e026bd9)): ?>
<?php $attributes = $__attributesOriginalaa758e6a82983efcbf593f765e026bd9; ?>
<?php unset($__attributesOriginalaa758e6a82983efcbf593f765e026bd9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa758e6a82983efcbf593f765e026bd9)): ?>
<?php $component = $__componentOriginalaa758e6a82983efcbf593f765e026bd9; ?>
<?php unset($__componentOriginalaa758e6a82983efcbf593f765e026bd9); ?>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/emails/np/status-changed.blade.php ENDPATH**/ ?>