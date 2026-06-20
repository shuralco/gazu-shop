<?php $__env->startSection('title', __('general.email_order_confirmation_subject', ['id' => $order->id])); ?>

<?php $__env->startSection('content'); ?>
    <!-- Heading -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="text-align: center; padding-bottom: 32px;">
                <div style="display: inline-block; background-color: #000000; color: #ffffff; font-size: 13px; font-weight: 700; letter-spacing: 2px; padding: 8px 20px; text-transform: uppercase;">
                    <?php echo e(__('general.email_order_confirmed')); ?>

                </div>
                <h2 style="margin: 16px 0 0 0; font-size: 24px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo e(__('general.email_order_number', ['id' => $order->id])); ?>

                </h2>
            </td>
        </tr>
    </table>

    <!-- Thank you message -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 32px; border-bottom: 2px solid #000000;">
                <p style="margin: 0; font-size: 15px; color: #333333; line-height: 1.6;">
                    <?php echo e(__('general.email_order_thank_you', ['name' => $order->first_name ?? $order->name ?? ''])); ?>

                </p>
            </td>
        </tr>
    </table>

    <!-- Order items -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-top: 24px; padding-bottom: 8px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    <?php echo e(__('general.your_order_items')); ?>

                </h3>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
        <!-- Header row -->
        <tr>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                <?php echo e(__('general.email_product')); ?>

            </td>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; text-align: center; width: 60px;">
                <?php echo e(__('general.quantity')); ?>

            </td>
            <td style="padding: 12px 0; border-bottom: 2px solid #000000; font-size: 11px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px; text-align: right; width: 100px;">
                <?php echo e(__('general.email_price')); ?>

            </td>
        </tr>
        <!-- Items, optionally grouped by warehouse -->
        <?php
            $orderProducts = $order->orderProducts->load('warehouse');
            $byWarehouse = $orderProducts->groupBy(fn ($op) => $op->warehouse_id ?? 0);
            $isMulti = $byWarehouse->count() > 1;
        ?>
        <?php $__currentLoopData = $byWarehouse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $whId => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $wh = $whId ? $items->first()->warehouse : null; ?>
            <?php if($wh && ($isMulti || $wh->delivery_eta)): ?>
                <tr>
                    <td colspan="3" style="padding: 12px 0 6px; font-size: 12px; color: #666; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e5e5e5;">
                        📦 <?php echo e($wh->city ?: $wh->name); ?><?php if($wh->delivery_eta): ?> · <?php echo e($wh->delivery_eta); ?><?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #333333; font-weight: 600;">
                        <?php echo e($item->title); ?>

                    </td>
                    <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #333333; text-align: center;">
                        <?php echo e($item->quantity); ?>

                    </td>
                    <td style="padding: 14px 0; border-bottom: 1px solid #e5e5e5; font-size: 14px; color: #000000; text-align: right; font-weight: 700;">
                        <?php echo e(formatPrice($item->price * $item->quantity)); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>

    <!-- Totals -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 4px;">
        <?php if($order->discount_amount && $order->discount_amount > 0): ?>
            <tr>
                <td style="padding: 8px 0; font-size: 14px; color: #666666;"><?php echo e(__('general.discount_label')); ?></td>
                <td style="padding: 8px 0; font-size: 14px; color: #ef4444; text-align: right; font-weight: 600;">
                    -<?php echo e(formatPrice($order->discount_amount)); ?>

                </td>
            </tr>
        <?php endif; ?>
        <?php if($order->shipping_cost && $order->shipping_cost > 0): ?>
            <tr>
                <td style="padding: 8px 0; font-size: 14px; color: #666666;"><?php echo e(__('general.delivery_label')); ?></td>
                <td style="padding: 8px 0; font-size: 14px; color: #333333; text-align: right; font-weight: 600;">
                    <?php echo e(formatPrice($order->shipping_cost)); ?>

                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td style="padding: 16px 0 0; border-top: 2px solid #000000; font-size: 18px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                <?php echo e(__('general.total')); ?>

            </td>
            <td style="padding: 16px 0 0; border-top: 2px solid #000000; font-size: 20px; font-weight: 900; color: #000000; text-align: right;">
                <?php echo e(formatPrice($order->total)); ?>

            </td>
        </tr>
    </table>

    <!-- Delivery & Payment -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="padding-bottom: 8px;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    <?php echo e(__('general.delivery_details')); ?>

                </h3>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fafafa; border: 1px solid #e5e5e5;">
        <tr>
            <td style="padding: 20px;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                    <?php if($order->shipping_method || $order->shipping_provider): ?>
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; width: 140px; vertical-align: top;">
                                <?php echo e(__('general.delivery_method')); ?>:
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 700;">
                                <?php echo e($order->shipping_provider ?? ''); ?> <?php echo e($order->shipping_method ?? ''); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if($order->shipping_city): ?>
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                <?php echo e(__('general.email_city')); ?>:
                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                <?php echo e($order->shipping_city); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if($order->shipping_warehouse): ?>
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                <?php echo e(__('general.delivery_address_label')); ?>

                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                <?php echo e($order->shipping_warehouse); ?>

                            </td>
                        </tr>
                    <?php elseif($order->shipping_address): ?>
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                <?php echo e(__('general.delivery_address_label')); ?>

                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                <?php echo e($order->shipping_address); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if($order->payment_method): ?>
                        <tr>
                            <td style="padding: 4px 0; font-size: 13px; color: #666666; vertical-align: top;">
                                <?php echo e(__('general.payment_method_short')); ?>

                            </td>
                            <td style="padding: 4px 0; font-size: 13px; color: #000000; font-weight: 600;">
                                <?php echo e($order->payment_method); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>

    <!-- CTA -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="text-align: center;">
                <a href="<?php echo e(url('/cabinet/orders')); ?>" style="display: inline-block; background-color: #000000; color: #ffffff; font-size: 13px; font-weight: 800; letter-spacing: 2px; text-decoration: none; text-transform: uppercase; padding: 16px 40px;">
                    <?php echo e(__('general.email_view_order')); ?>

                </a>
            </td>
        </tr>
    </table>

    <!-- Help text -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="text-align: center; border-top: 1px solid #e5e5e5; padding-top: 24px;">
                <p style="margin: 0; font-size: 13px; color: #999999; line-height: 1.6;">
                    <?php echo e(__('general.email_order_questions')); ?>

                </p>
            </td>
        </tr>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/emails/order-confirmation.blade.php ENDPATH**/ ?>