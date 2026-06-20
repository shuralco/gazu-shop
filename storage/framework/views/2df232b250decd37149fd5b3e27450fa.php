<?php $__env->startSection('title', __('general.email_welcome_subject', ['shop' => shopName()])); ?>

<?php $__env->startSection('content'); ?>
    <!-- Heading -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="text-align: center; padding-bottom: 32px;">
                <div style="font-size: 48px; line-height: 1; margin-bottom: 16px;">&#9733;</div>
                <h2 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo e(__('general.email_welcome_heading')); ?>

                </h2>
                <p style="margin: 0; font-size: 16px; color: #666666;">
                    <?php echo e(__('general.email_welcome_subheading', ['name' => $user->name ?? ''])); ?>

                </p>
            </td>
        </tr>
    </table>

    <!-- Welcome message -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 32px; border-bottom: 2px solid #000000;">
                <p style="margin: 0; font-size: 15px; color: #333333; line-height: 1.7;">
                    <?php echo e(__('general.email_welcome_text', ['shop' => shopName()])); ?>

                </p>
            </td>
        </tr>
    </table>

    <!-- Benefits -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td>
                <h3 style="margin: 0 0 20px 0; font-size: 14px; font-weight: 900; color: #000000; text-transform: uppercase; letter-spacing: 2px;">
                    <?php echo e(__('general.email_welcome_benefits_title')); ?>

                </h3>
            </td>
        </tr>
    </table>

    <?php
        $benefits = [
            ['icon' => '&#9889;', 'title' => __('general.benefit_fast_checkout'), 'desc' => __('general.email_benefit_fast_desc')],
            ['icon' => '&#9733;', 'title' => __('general.benefit_loyalty_program'), 'desc' => __('general.email_benefit_loyalty_desc')],
            ['icon' => '&#9881;', 'title' => __('general.benefit_personal_discounts'), 'desc' => __('general.email_benefit_discounts_desc')],
            ['icon' => '&#9998;', 'title' => __('general.benefit_order_history'), 'desc' => __('general.email_benefit_history_desc')],
        ];
    ?>

    <?php $__currentLoopData = $benefits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px;">
            <tr>
                <td style="width: 48px; vertical-align: top; padding-top: 4px;">
                    <div style="width: 40px; height: 40px; background-color: #000000; color: #ffffff; text-align: center; line-height: 40px; font-size: 18px;">
                        <?php echo $benefit['icon']; ?>

                    </div>
                </td>
                <td style="padding-left: 16px; vertical-align: top;">
                    <p style="margin: 0 0 2px 0; font-size: 14px; font-weight: 800; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
                        <?php echo e($benefit['title']); ?>

                    </p>
                    <p style="margin: 0; font-size: 13px; color: #666666; line-height: 1.5;">
                        <?php echo e($benefit['desc']); ?>

                    </p>
                </td>
            </tr>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <!-- Promo code -->
    <?php if($promoCode): ?>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
            <tr>
                <td style="background-color: #000000; padding: 28px; text-align: center;">
                    <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #999999; text-transform: uppercase; letter-spacing: 2px;">
                        <?php echo e(__('general.email_promo_label')); ?>

                    </p>
                    <p style="margin: 0 0 8px 0; font-size: 28px; font-weight: 900; color: #ffffff; letter-spacing: 4px; font-family: 'Courier New', Courier, monospace;">
                        <?php echo e($promoCode); ?>

                    </p>
                    <p style="margin: 0; font-size: 13px; color: #999999;">
                        <?php echo e(__('general.email_promo_hint')); ?>

                    </p>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- CTA -->
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 32px;">
        <tr>
            <td style="text-align: center;">
                <a href="<?php echo e(url('/cabinet')); ?>" style="display: inline-block; background-color: #000000; color: #ffffff; font-size: 13px; font-weight: 800; letter-spacing: 2px; text-decoration: none; text-transform: uppercase; padding: 16px 40px;">
                    <?php echo e(__('general.email_go_to_account')); ?>

                </a>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; padding-top: 16px;">
                <a href="<?php echo e(url('/')); ?>" style="font-size: 13px; color: #000000; font-weight: 700; text-decoration: underline; text-transform: uppercase; letter-spacing: 1px;">
                    <?php echo e(__('general.email_start_shopping')); ?>

                </a>
            </td>
        </tr>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/emails/welcome.blade.php ENDPATH**/ ?>