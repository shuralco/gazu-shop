<?php $__env->startSection('title', __('general.email_status_update_subject', ['id' => $order->id])); ?>

<?php $__env->startSection('content'); ?>
    <!-- Heading -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="text-align: center; padding-bottom: 32px;">
                <h2 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo e(__('general.email_status_update_heading')); ?>

                </h2>
                <p style="margin: 0; font-size: 15px; color: #666666;">
                    <?php echo e(__('general.email_order_number', ['id' => $order->id])); ?>

                </p>
            </td>
        </tr>
    </table>

    <!-- Status badge -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="text-align: center; padding-bottom: 32px;">
                <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                    <tr>
                        <td style="padding: 20px 40px; border: 3px solid <?php echo e($__env->getShared('statusColor', $getStatusColor ?? '#000000')); ?>; text-align: center;">
                            <?php
                                $statusColor = method_exists($__env, 'getShared') ? '#000' : '#000';
                                if (isset($newStatus)) {
                                    $statusColor = match ($newStatus) {
                                        'pending', 'new' => '#f59e0b',
                                        'processing' => '#3b82f6',
                                        'shipped' => '#8b5cf6',
                                        'delivered' => '#10b981',
                                        'cancelled' => '#ef4444',
                                        default => '#6b7280',
                                    };
                                    $statusLabel = match ($newStatus) {
                                        'pending' => __('general.order_status_pending'),
                                        'new' => __('general.order_status_new'),
                                        'processing' => __('general.order_status_processing'),
                                        'shipped' => __('general.order_status_shipped'),
                                        'delivered' => __('general.order_status_delivered'),
                                        'cancelled' => __('general.order_status_cancelled'),
                                        default => $newStatus,
                                    };
                                    $statusMessage = match ($newStatus) {
                                        'pending', 'new' => __('general.email_status_msg_pending'),
                                        'processing' => __('general.email_status_msg_processing'),
                                        'shipped' => __('general.email_status_msg_shipped'),
                                        'delivered' => __('general.email_status_msg_delivered'),
                                        'cancelled' => __('general.email_status_msg_cancelled'),
                                        default => __('general.email_status_msg_default'),
                                    };
                                }
                            ?>
                            <div style="width: 16px; height: 16px; background-color: <?php echo e($statusColor); ?>; border-radius: 50%; margin: 0 auto 12px auto;"></div>
                            <div style="font-size: 20px; font-weight: 900; color: <?php echo e($statusColor); ?>; text-transform: uppercase; letter-spacing: 2px;">
                                <?php echo e($statusLabel ?? ''); ?>

                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Status message -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 24px; background-color: #fafafa; border-left: 4px solid <?php echo e($statusColor); ?>; margin-bottom: 24px;">
                <p style="margin: 0; font-size: 15px; color: #333333; line-height: 1.6;">
                    <?php echo e($statusMessage ?? ''); ?>

                </p>
            </td>
        </tr>
    </table>

    <!-- Tracking number -->
    <?php if($trackingNumber): ?>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 24px;">
            <tr>
                <td style="padding: 20px; background-color: #000000; text-align: center;">
                    <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #999999; text-transform: uppercase; letter-spacing: 2px;">
                        <?php echo e(__('general.tracking_number')); ?>

                    </p>
                    <p style="margin: 0; font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: 3px; font-family: 'Courier New', Courier, monospace;">
                        <?php echo e($trackingNumber); ?>

                    </p>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- Order summary -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="border-top: 2px solid #000000; padding-top: 16px;">
                <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    <?php echo e(__('general.order_details_title')); ?>

                </h3>
            </td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #666666;"><?php echo e(__('general.buyer_label')); ?></td>
            <td style="padding: 6px 0; font-size: 13px; color: #000000; font-weight: 600; text-align: right;">
                <?php echo e($order->first_name ?? ''); ?> <?php echo e($order->last_name ?? ''); ?>

            </td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 13px; color: #666666;"><?php echo e(__('general.total_label')); ?></td>
            <td style="padding: 6px 0; font-size: 15px; color: #000000; font-weight: 900; text-align: right;">
                <?php echo e(formatPrice($order->total)); ?>

            </td>
        </tr>
        <?php if($order->shipping_city): ?>
            <tr>
                <td style="padding: 6px 0; font-size: 13px; color: #666666;"><?php echo e(__('general.delivery_address_label')); ?></td>
                <td style="padding: 6px 0; font-size: 13px; color: #000000; font-weight: 600; text-align: right;">
                    <?php echo e($order->shipping_city); ?><?php echo e($order->shipping_warehouse ? ', ' . $order->shipping_warehouse : ''); ?>

                </td>
            </tr>
        <?php endif; ?>
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

<?php echo $__env->make('emails.layouts.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/emails/order-status-update.blade.php ENDPATH**/ ?>