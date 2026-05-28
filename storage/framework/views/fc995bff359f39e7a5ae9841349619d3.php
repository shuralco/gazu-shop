<?php $__env->startSection('title', 'GAZU — пошук автозапчастин за артикулом'); ?>

<?php $__env->startSection('content'); ?>
    
    <section class="py-10 sm:py-14" style="background: linear-gradient(180deg, var(--gazu-mist) 0%, var(--gazu-paper) 100%);">
        <div class="gazu-container gazu-grid-hero-vin">
            <div>
                <?php
                    $s = $gazuSettings ?? [];
                    $heroSubtitle = $s['gazu_hero_subtitle'] ?? 'Запчастини для китайських авто';
                    $heroTitle1 = $s['gazu_hero_title_1'] ?? 'Підбір по авто';
                    $heroTitle2Html = $s['gazu_hero_title_2_html'] ?? 'за <span style="color:var(--gazu-blue)">марку</span> і двигун.';
                ?>
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3.5"><?php echo e($heroSubtitle); ?></div>
                <h1 class="gazu-display font-semibold text-[var(--gazu-ink)] m-0" style="font-size: clamp(28px, 5.2vw, 52px); line-height: 1.05; letter-spacing: -0.03em; overflow-wrap: anywhere; max-width: 100%;">
                    <?php echo e($heroTitle1); ?><br><?php echo $heroTitle2Html; ?>

                </h1>
                <p class="text-[15px] sm:text-[16px] text-[var(--gazu-graphite)] leading-relaxed mt-5 max-w-md">
                    <?php echo e($s['gazu_hero_description'] ?? 'BYD, Chery, Geely, Haval, Great Wall, JAC, MG, VW. У наявності 1278+ оригінальних запчастин і перевірених аналогів. Доставка 1-3 дні по Україні.'); ?>

                </p>

                
                <div class="flex flex-wrap gap-3 mt-6">
                    <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--gazu-ink)] text-white rounded-md text-[14px] font-semibold no-underline hover:bg-[var(--gazu-ink-2)] transition-colors">
                        Дивитись каталог
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="tel:0800751024" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline shadow-[inset_0_0_0_1px_var(--gazu-line)] hover:shadow-[inset_0_0_0_1px_var(--gazu-ink)] transition-shadow">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        0 800 75 10 24
                    </a>
                </div>

                
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-6 text-[12px] sm:text-[13px] text-[var(--gazu-graphite)]">
                    <span class="inline-flex gap-1.5 items-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Без передоплати</span>
                    <span class="inline-flex gap-1.5 items-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Гарантія 12+ міс.</span>
                    <span class="inline-flex gap-1.5 items-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '14','stroke' => 'var(--gazu-success)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Повернення 14 днів</span>
                </div>
            </div>

            
            <div>
                <?php if (isset($component)) { $__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.car-selector','data' => ['variant' => 'hero','initialMakes' => $heroMakes ?? []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.car-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'hero','initial-makes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($heroMakes ?? [])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0)): ?>
<?php $attributes = $__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0; ?>
<?php unset($__attributesOriginal1668e41bec6d77c0bd5b5183a6d5c5d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0)): ?>
<?php $component = $__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0; ?>
<?php unset($__componentOriginal1668e41bec6d77c0bd5b5183a6d5c5d0); ?>
<?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (isset($component)) { $__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.trust-strip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.trust-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1)): ?>
<?php $attributes = $__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1; ?>
<?php unset($__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1)): ?>
<?php $component = $__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1; ?>
<?php unset($__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal475b96e12d3e966b8e9129a84d649a77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal475b96e12d3e966b8e9129a84d649a77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.category-tiles','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.category-tiles'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal475b96e12d3e966b8e9129a84d649a77)): ?>
<?php $attributes = $__attributesOriginal475b96e12d3e966b8e9129a84d649a77; ?>
<?php unset($__attributesOriginal475b96e12d3e966b8e9129a84d649a77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal475b96e12d3e966b8e9129a84d649a77)): ?>
<?php $component = $__componentOriginal475b96e12d3e966b8e9129a84d649a77; ?>
<?php unset($__componentOriginal475b96e12d3e966b8e9129a84d649a77); ?>
<?php endif; ?>

    <?php if(isset($promoProducts) && $promoProducts->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Акції тижня','items' => $promoProducts,'viewAll' => route('gazu.catalog.promo')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Акції тижня','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($promoProducts),'viewAll' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('gazu.catalog.promo'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $attributes = $__attributesOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__attributesOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $component = $__componentOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__componentOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if(isset($newProducts) && $newProducts->isNotEmpty()): ?>
        <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Новинки','items' => $newProducts,'viewAll' => route('gazu.catalog.new')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Новинки','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($newProducts),'viewAll' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('gazu.catalog.new'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $attributes = $__attributesOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__attributesOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $component = $__componentOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__componentOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Хіти продажів','items' => $popular,'viewAll' => route('gazu.catalog.hits')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Хіти продажів','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($popular),'viewAll' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('gazu.catalog.hits'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $attributes = $__attributesOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__attributesOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $component = $__componentOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__componentOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.recently-viewed','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.recently-viewed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3)): ?>
<?php $attributes = $__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3; ?>
<?php unset($__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3)): ?>
<?php $component = $__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3; ?>
<?php unset($__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginale1be49c9ed6481a1f18dd814509ce9e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.brand-strip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.brand-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2)): ?>
<?php $attributes = $__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2; ?>
<?php unset($__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale1be49c9ed6481a1f18dd814509ce9e2)): ?>
<?php $component = $__componentOriginale1be49c9ed6481a1f18dd814509ce9e2; ?>
<?php unset($__componentOriginale1be49c9ed6481a1f18dd814509ce9e2); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal20825542a659f4430050c1f420391d16 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20825542a659f4430050c1f420391d16 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.seo-text','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.seo-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20825542a659f4430050c1f420391d16)): ?>
<?php $attributes = $__attributesOriginal20825542a659f4430050c1f420391d16; ?>
<?php unset($__attributesOriginal20825542a659f4430050c1f420391d16); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20825542a659f4430050c1f420391d16)): ?>
<?php $component = $__componentOriginal20825542a659f4430050c1f420391d16; ?>
<?php unset($__componentOriginal20825542a659f4430050c1f420391d16); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/home/v1.blade.php ENDPATH**/ ?>