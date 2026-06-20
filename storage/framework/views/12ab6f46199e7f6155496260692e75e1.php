<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-information-circle','iconColor' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-information-circle','icon-color' => 'info']); ?>
             <?php $__env->slot('heading', null, []); ?> Тема магазину <?php $__env->endSlot(); ?>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                Тема = набір кольорів-токенів у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/&lt;назва&gt;/theme.json</code>.
                Активна тема зберігається у БД і застосовується <strong>миттєво</strong> — вітрина переспрашивається у рантаймі
                (<code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code> <strong>не потрібен</strong>).
                Кеш вітрини скидається автоматично при перемиканні.
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

        <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(320px,1fr))">
            <?php $__currentLoopData = $this->themes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = $theme['name'] === $this->activeTheme;
                    $bg     = $this->previewToken($theme['name'], 'paper') ?? '#FBFAF7';
                    $fg     = $this->previewToken($theme['name'], 'ink')   ?? '#0E1B2C';
                    $brand  = $this->previewToken($theme['name'], 'blue')  ?? ($this->previewToken($theme['name'], 'ink') ?? '#2453A6');
                    $accent = $this->previewToken($theme['name'], 'azure') ?? ($this->previewToken($theme['name'], 'warn') ?? '#3672D9');
                    $line   = $this->previewToken($theme['name'], 'line')  ?? '#E4E7EB';
                ?>

                <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                     <?php $__env->slot('heading', null, []); ?> 
                        <span class="flex items-center gap-2">
                            <span class="text-lg font-bold"><?php echo e($theme['label']); ?></span>
                            <?php if($isActive): ?>
                                <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'success']); ?>АКТИВНА <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </span>
                     <?php $__env->endSlot(); ?>
                     <?php $__env->slot('description', null, []); ?> 
                        <span class="font-mono text-xs">themes/<?php echo e($theme['name']); ?>/theme.json</span>
                     <?php $__env->endSlot(); ?>

                    <?php if($theme['description']): ?>
                        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400"><?php echo e($theme['description']); ?></p>
                    <?php endif; ?>

                    
                    <div
                        class="mb-4 rounded-lg p-4"
                        style="background:<?php echo e($bg); ?>; color:<?php echo e($fg); ?>; border:1px solid <?php echo e($line); ?>; border-radius:8px;"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <span class="inline-block h-4 w-4 rounded-full" style="background:<?php echo e($brand); ?>;" title="brand"></span>
                            <span class="inline-block h-4 w-4 rounded-full" style="background:<?php echo e($accent); ?>;" title="accent"></span>
                            <span class="font-mono text-xs"><?php echo e($fg); ?> on <?php echo e($bg); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                style="background:<?php echo e($brand); ?>; color:#fff; border:0; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600;"
                                disabled
                            >Кнопка</button>
                            <span
                                style="background:<?php echo e($accent); ?>; color:#fff; padding:4px 10px; border-radius:16px; font-size:11px; font-weight:600;"
                            >badge</span>
                        </div>
                    </div>

                    <?php if(! $isActive): ?>
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','wire:click' => 'activateTheme(\''.e($theme['name']).'\')','wire:loading.attr' => 'disabled','wire:target' => 'activateTheme(\''.e($theme['name']).'\')','color' => 'primary','icon' => 'heroicon-o-swatch','class' => 'w-full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'activateTheme(\''.e($theme['name']).'\')','wire:loading.attr' => 'disabled','wire:target' => 'activateTheme(\''.e($theme['name']).'\')','color' => 'primary','icon' => 'heroicon-o-swatch','class' => 'w-full']); ?>
                            Активувати
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400">Поточна тема — застосована на вітрині</div>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-plus-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-plus-circle']); ?>
             <?php $__env->slot('heading', null, []); ?> Як додати нову тему <?php $__env->endSlot(); ?>

            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li>Скопіюйте теку <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/</code> у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/&lt;нова&gt;/</code></li>
                <li>У <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">theme.json</code> змініть <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">name</code>, <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">label</code> та значення кольорів у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">tokens</code> (імена ключів лишайте)</li>
                <li>Лиште <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">css_entry</code> на <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/resources/css/gazu.css</code> (спільна збірка)</li>
                <li>Поверніться сюди — нова тема зʼявиться автоматично. Натисніть «Активувати» — застосується миттєво, <strong>без</strong> <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">npm run build</code></li>
            </ol>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Перевизначаються лише кольори (інші ключі див. у <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">themes/gazu/theme.json</code>).
                Радіуси/шрифти/тіні — у збірці теми.
            </p>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/theme-settings.blade.php ENDPATH**/ ?>