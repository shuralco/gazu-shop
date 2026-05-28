<?php
    $s = $gazuSettings ?? [];
    $about = $s['gazu_footer_about'] ?? sprintf(
        'Інтернет-магазин автозапчастин. %s, доставка по Україні, гарантія на кожну деталь.',
        $shopStats['products_label'] ?? 'широкий каталог'
    );
    $columns = $s['gazu_footer_columns'] ?? [
        ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов і оптика', 'Аксесуари']],
        ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
        ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
    ];

    // Map common footer labels to actual routes — kills the 'href="#"' dead-link cluster.
    // Категорії ведуть на real /{slug} (root-level catch-all → category page).
    $linkMap = [
        'Двигун'                => url('/engine'),
        'Гальмівна система'     => url('/brakes'),
        'Підвіска'              => url('/suspension'),
        'Електрика'             => url('/electrics'),
        'Трансмісія'            => url('/transmission'),
        'Мастила і рідини'      => url('/fluids'),
        'Мастила'               => url('/fluids'),
        'Кузов і оптика'        => url('/body'),
        'Кузов'                 => url('/body'),
        'Аксесуари'             => url('/accessories'),
        'Салон'                 => url('/accessories'),
        'Доставка та оплата'    => route('gazu.delivery'),
        'Гарантія та повернення'=> route('gazu.warranty'),
        'Питання та відповіді'  => route('gazu.faq'),
        'Бонусна програма'      => route('gazu.loyalty'),
        'Гуртовим клієнтам'     => route('gazu.wholesale'),
        'Про нас'               => route('gazu.about'),
        'Контакти'              => route('gazu.contacts'),
        'Вакансії'              => route('gazu.careers'),
        'Сертифікати'           => route('gazu.certificates'),
        'Публічна оферта'       => route('gazu.offer'),
    ];
    $payments = $s['gazu_footer_payments'] ?? 'Visa, Mastercard, Apple Pay, Google Pay, Нова Пошта';
    $phone = $s['gazu_phone'] ?? '0 800 75 10 24';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $social = [
        'FB' => $s['gazu_social_facebook'] ?? null,
        'IG' => $s['gazu_social_instagram'] ?? null,
        'TG' => $s['gazu_social_telegram'] ?? null,
        'YT' => $s['gazu_social_youtube'] ?? null,
    ];
?>
<footer class="bg-[var(--gazu-ink)] text-[#CDD3DC] mt-16">
    <div class="gazu-container py-14 grid gap-8 sm:gap-10 gazu-footer-grid">
        <div>
            <?php if (isset($component)) { $__componentOriginal00cc706ec7279da3d3246febbb2826f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00cc706ec7279da3d3246febbb2826f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.logo','data' => ['size' => '28','color' => '#fff']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => '28','color' => '#fff']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal00cc706ec7279da3d3246febbb2826f1)): ?>
<?php $attributes = $__attributesOriginal00cc706ec7279da3d3246febbb2826f1; ?>
<?php unset($__attributesOriginal00cc706ec7279da3d3246febbb2826f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal00cc706ec7279da3d3246febbb2826f1)): ?>
<?php $component = $__componentOriginal00cc706ec7279da3d3246febbb2826f1; ?>
<?php unset($__componentOriginal00cc706ec7279da3d3246febbb2826f1); ?>
<?php endif; ?>
            <p class="text-sm leading-relaxed mt-4 text-[#9DA5B2]"><?php echo e($about); ?></p>
            <div class="flex gap-2 mt-4">
                <?php $__currentLoopData = $social; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(! empty($url) && $url !== '#'): ?>
                        <a href="<?php echo e($url); ?>" target="_blank" rel="nofollow noopener"
                           class="w-9 h-9 rounded-lg border border-[#2A3850] flex items-center justify-center text-[11px] text-[#CDD3DC] no-underline hover:bg-[#2A3850]"><?php echo e($code); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <?php $__currentLoopData = (array) $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <div class="gazu-display text-sm font-semibold text-white mb-3.5"><?php echo e($col['title'] ?? ''); ?></div>
                <ul class="list-none p-0 m-0 flex flex-col gap-2.5">
                    <?php $__currentLoopData = (array) ($col['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $href = $linkMap[$i] ?? route('gazu.catalog'); ?>
                        <li><a wire:navigate href="<?php echo e($href); ?>" class="text-[13px] text-[#9DA5B2] no-underline hover:text-white"><?php echo e($i); ?></a></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div>
            <div class="gazu-display text-sm font-semibold text-white mb-3.5">Зворотний звʼязок</div>
            <?php if($phone): ?>
                <div class="gazu-display text-[22px] text-white mb-1"><?php echo e($phone); ?></div>
            <?php endif; ?>
            <?php if($hours): ?>
                <div class="text-xs text-[#9DA5B2] mb-4"><?php echo e($hours); ?>, безкоштовно</div>
            <?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.callback-popover','data' => ['variant' => 'button','source' => 'footer','align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.callback-popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'button','source' => 'footer','align' => 'right']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461)): ?>
<?php $attributes = $__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461; ?>
<?php unset($__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461)): ?>
<?php $component = $__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461; ?>
<?php unset($__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461); ?>
<?php endif; ?>
        </div>
    </div>
    <div class="border-t border-[#1A2740] gazu-container py-5 flex items-center gap-6 text-xs text-[#5A6573] flex-wrap">
        <span>© <?php echo e(date('Y')); ?> <?php echo e($s['gazu_brand_name'] ?? 'GAZU'); ?>. Всі права захищені.</span>
        <span class="flex-1"></span>
        <?php $__currentLoopData = array_filter(array_map('trim', explode(',', $payments))); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span><?php echo e($pay); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</footer>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/footer.blade.php ENDPATH**/ ?>