<?php
    // L1 → L2 (групи) → L3 (підкатегорії з лічильниками)
    // У продакшні $megaTree приходить з GazuMenuComposer (Category::with('children.children')).
    // Якщо composer не запустився — рендеримо порожнє меню замість фейкових даних.
    $megaTree = $megaTree ?? [];
    $brands = $brands ?? [];
    $cars = $cars ?? [];
    $active = $activeMega ?? null;
    $cat = collect($megaTree)->firstWhere('id', $active) ?? collect($megaTree)->first();
    $totalCount = collect($megaTree)->sum('count');
    // Перемикач в адмінці (Мега-меню → «Показувати кількість товарів»).
    // За замовчуванням приховано, щоб не показувати «0» поки товари не розкладені.
    $showCounts = (bool) \App\Models\DisplaySetting::get('mega_menu_show_counts', false);
?>

<?php if(empty($megaTree)): ?>
    
    <div></div>
<?php else: ?>


<div class="bg-[var(--gazu-surface)] rounded-xl overflow-hidden border border-[var(--gazu-line)] relative flex flex-col max-h-full"
     x-data="{ activeMega: '<?php echo e($megaTree[0]['id'] ?? 'engine'); ?>', mobileOpen: null }"
     style="box-shadow: 0 28px 60px -10px rgba(14,27,44,0.35), 0 8px 16px rgba(14,27,44,0.12);">

    
    <div class="hidden lg:block absolute -top-2 w-4 h-4 bg-[var(--gazu-surface)] border-l border-t border-[var(--gazu-line)] rotate-45 z-10" style="left: 156px;"></div>

    
    <div class="flex items-center gap-3 px-4 sm:px-5 py-2.5 border-b border-[var(--gazu-line)] bg-[var(--gazu-paper)] shrink-0">
        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">Каталог</span>
        <?php if($showCounts): ?><span class="gazu-mono text-[11px] text-[var(--gazu-muted)] hidden sm:inline">· <?php echo e(number_format($totalCount, 0, '.', ' ')); ?> товарів у <?php echo e(count($megaTree)); ?> категоріях</span><?php endif; ?>
        <span class="flex-1"></span>
        <button type="button" @click="megaOpen = false"
                class="w-7 h-7 border border-[var(--gazu-line)] bg-[var(--gazu-surface)] rounded inline-flex items-center justify-center cursor-pointer text-[var(--gazu-graphite)]">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'close','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
        </button>
    </div>

    
    <div class="lg:hidden overflow-y-auto">
        <?php $__currentLoopData = $megaTree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $catLink = ! empty($c['slug']) ? url('/'.$c['slug']) : route('gazu.catalog'); ?>
            <div class="border-b border-[var(--gazu-line)] last:border-b-0">
                <button type="button"
                        @click="mobileOpen = (mobileOpen === '<?php echo e($c['id']); ?>' ? null : '<?php echo e($c['id']); ?>')"
                        :class="mobileOpen === '<?php echo e($c['id']); ?>' ? 'bg-[var(--gazu-paper)]' : 'bg-[var(--gazu-surface)]'"
                        class="w-full flex items-center gap-3 px-4 py-3.5 border-0 cursor-pointer text-left">
                    <?php if (isset($component)) { $__componentOriginalc6dde9adab203a51e0257bbee7e900dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.cat-icon','data' => ['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '22']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.cat-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '22']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $attributes = $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $component = $__componentOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
                    <span class="flex-1 text-[15px] font-semibold text-[var(--gazu-ink)]"><?php echo e($c['label']); ?></span>
                    <?php if($showCounts): ?><span class="gazu-mono text-[11px] text-[var(--gazu-muted)]"><?php echo e(number_format($c['count'], 0, '.', ' ')); ?></span><?php endif; ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="text-[var(--gazu-graphite)] transition-transform duration-200"
                         :class="mobileOpen === '<?php echo e($c['id']); ?>' ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="mobileOpen === '<?php echo e($c['id']); ?>'" x-cloak x-transition.opacity.duration.150ms
                     class="px-4 pb-4 bg-[var(--gazu-paper)]">
                    <a wire:navigate href="<?php echo e($catLink); ?>"
                       class="flex items-center justify-center gap-1.5 w-full py-2.5 mb-3 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-md text-[13px] font-medium no-underline">
                        Усі товари категорії →
                    </a>
                    <?php $__currentLoopData = $c['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-3.5 last:mb-0">
                            <?php if(!empty($g['title'])): ?>
                                <div class="gazu-display text-[13px] font-bold text-[var(--gazu-ink)] mb-1.5"><?php echo e($g['title']); ?></div>
                            <?php endif; ?>
                            <div class="flex flex-col">
                                <?php $__currentLoopData = $g['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $itmName = $itm[0] ?? '—';
                                        $itmCount = $itm[1] ?? 0;
                                        $itmSlug = $itm[2] ?? '';
                                        $itmHref = $itmSlug ? url('/'.$itmSlug) : route('gazu.catalog');
                                    ?>
                                    <a wire:navigate href="<?php echo e($itmHref); ?>"
                                       class="flex items-baseline gap-2 py-1.5 text-[13px] text-[var(--gazu-graphite)] no-underline">
                                        <span class="flex-1"><?php echo e($itmName); ?></span>
                                        <?php if($showCounts): ?><span class="gazu-mono text-[10px] text-[var(--gazu-muted)]"><?php echo e($itmCount); ?></span><?php endif; ?>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <div class="px-3 py-4 border-t border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
            <div class="grid grid-cols-3 gap-2">
                <?php $__currentLoopData = [
                    ['Акції', route('gazu.catalog.promo')],
                    ['Хіти', route('gazu.catalog.hits')],
                    ['Новинки', route('gazu.catalog.new')],
                    ['Бренди', route('gazu.brand')],
                    ['Блог', route('gazu.blog')],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $url]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a wire:navigate href="<?php echo e($url); ?>"
                       class="flex items-center justify-center px-2 py-2.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg text-[13px] font-medium text-[var(--gazu-ink)] no-underline text-center hover:border-[var(--gazu-ink)] transition-colors">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <?php
            $menuBrands = is_array($brands) ? $brands : ($brands instanceof \Illuminate\Support\Enumerable ? $brands->pluck('name')->all() : []);
        ?>
        <?php
            // Normalize brands: composer тепер passes array of ['name'=>, 'slug'=>] — fallback на просто names
            $brandList = collect($menuBrands)->map(function ($b) {
                if (is_array($b) && isset($b['slug'])) {
                    $bn = $b['name'] ?? '';
                    if (is_array($bn)) $bn = $bn['uk'] ?? array_values($bn)[0] ?? '';
                    return ['name' => (string) $bn, 'slug' => (string) $b['slug']];
                }
                $name = is_array($b) ? ($b['uk'] ?? array_values($b)[0] ?? '') : (string) $b;
                return ['name' => (string) $name, 'slug' => \Illuminate\Support\Str::slug((string) $name)];
            })->filter(fn ($b) => $b['name'] && $b['slug'])->all();
        ?>
        <?php if(! empty($brandList)): ?>
            <div class="px-4 py-4 border-t border-[var(--gazu-line)]">
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-2.5">Топ бренди</div>
                <div class="grid grid-cols-3 gap-1.5">
                    <?php $__currentLoopData = array_slice($brandList, 0, 9); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a wire:navigate href="<?php echo e(route('gazu.brand', ['slug' => $b['slug']])); ?>"
                           class="h-9 border border-[var(--gazu-line)] rounded flex items-center justify-center gazu-display text-[11px] font-semibold text-[var(--gazu-steel)] bg-[var(--gazu-surface)] hover:border-[var(--gazu-ink)] hover:text-[var(--gazu-ink)] no-underline transition-colors"><?php echo e($b['name']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if(! empty($cars)): ?>
            <div class="px-4 py-4 border-t border-[var(--gazu-line)]">
                <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase font-semibold mb-2">Марки</div>
                <div class="flex flex-col gap-0.5">
                    <?php $__currentLoopData = $cars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $name = is_array($c) ? ($c['name'] ?? '') : (string) $c; $slug = is_array($c) ? ($c['slug'] ?? \Illuminate\Support\Str::slug($name)) : \Illuminate\Support\Str::slug($name); $logo = is_array($c) ? ($c['logo'] ?? null) : null; ?>
                        <?php if($name && $slug): ?>
                            <a wire:navigate href="<?php echo e(route('gazu.catalog.by-make', ['make' => $slug])); ?>"
                               class="flex items-center gap-2 px-2 py-1.5 rounded no-underline text-[var(--gazu-ink)] text-xs hover:bg-[var(--gazu-mist)]">
                                <span class="w-6 h-6 rounded overflow-hidden inline-flex items-center justify-center shrink-0 <?php echo e($logo ? '' : 'bg-[var(--gazu-mist)] text-[10px] gazu-mono font-bold text-[var(--gazu-blue)]'); ?>">
                                    <?php if($logo): ?><img src="<?php echo e($logo); ?>" alt="<?php echo e($name); ?>" class="w-full h-full object-cover" loading="lazy"><?php else: ?><?php echo e(mb_substr($name, 0, 2)); ?><?php endif; ?>
                                </span>
                                <span class="flex-1"><?php echo e($name); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="hidden lg:grid min-h-[540px]" style="grid-template-columns: 264px 1fr 260px;">

        
        <nav class="border-r border-[var(--gazu-line)] py-3.5 bg-[var(--gazu-paper)]">
            <?php $__currentLoopData = $megaTree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $catLink = ! empty($c['slug']) ? url('/'.$c['slug']) : route('gazu.catalog'); ?>
                <a wire:navigate href="<?php echo e($catLink); ?>"
                   @mouseenter="activeMega = '<?php echo e($c['id']); ?>'"
                   :class="activeMega === '<?php echo e($c['id']); ?>' ? 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]'"
                   :style="activeMega === '<?php echo e($c['id']); ?>' ? 'border-left:3px solid var(--gazu-blue)' : 'border-left:3px solid transparent'"
                   class="flex items-center gap-3 py-2.5 pr-3.5 pl-5 text-sm no-underline cursor-pointer relative">
                    <?php if (isset($component)) { $__componentOriginalc6dde9adab203a51e0257bbee7e900dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.cat-icon','data' => ['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.cat-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $attributes = $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $component = $__componentOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
                    <span class="flex-1 leading-tight"><?php echo e($c['label']); ?></span>
                    <?php if($showCounts): ?><span class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-wider"><?php echo e(number_format($c['count'], 0, '.', ' ')); ?></span><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '12','class' => '-rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '12','class' => '-rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        
        <div class="border-r border-[var(--gazu-line)]">
            <?php $__currentLoopData = $megaTree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div x-show="activeMega === '<?php echo e($c['id']); ?>'" x-cloak class="px-7 pt-5 pb-6 h-full">
                    <div class="flex items-center gap-3 mb-4 pb-3.5 border-b border-[var(--gazu-line)]">
                        <?php if (isset($component)) { $__componentOriginalc6dde9adab203a51e0257bbee7e900dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.cat-icon','data' => ['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '28']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.cat-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($c['icon'] ?? $c['id']).'','size' => '28']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $attributes = $__attributesOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__attributesOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc)): ?>
<?php $component = $__componentOriginalc6dde9adab203a51e0257bbee7e900dc; ?>
<?php unset($__componentOriginalc6dde9adab203a51e0257bbee7e900dc); ?>
<?php endif; ?>
                        <h3 class="gazu-display text-[22px] font-bold text-[var(--gazu-ink)] m-0"><?php echo e($c['label']); ?></h3>
                        <?php if($showCounts): ?><span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase"><?php echo e(number_format($c['count'], 0, '.', ' ')); ?> товарів</span><?php endif; ?>
                        <a wire:navigate href="<?php echo e(! empty($c['slug']) ? url('/'.$c['slug']) : route('gazu.catalog')); ?>" class="ml-auto text-[13px] text-[var(--gazu-blue)] no-underline inline-flex items-center gap-1">Усі →</a>
                    </div>
                    <div class="grid gap-x-6 gap-y-5" style="grid-template-columns: repeat(<?php echo e(min(max(count($c['groups']), 1), 5)); ?>, 1fr);">
                        <?php $__currentLoopData = $c['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <?php if(!empty($g['title'])): ?>
                                    <div class="gazu-display text-sm font-bold text-[var(--gazu-ink)] mb-2.5"><?php echo e($g['title']); ?></div>
                                <?php else: ?>
                                    <div class="mb-2.5" style="height: 21px;"></div>
                                <?php endif; ?>
                                <div class="flex flex-col gap-1.5">
                                    <?php $__currentLoopData = $g['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            // items: [name, count] (2-tuple, legacy) or [name, count, slug] (3-tuple, new).
                                            $itmName = $itm[0] ?? '—';
                                            $itmCount = $itm[1] ?? 0;
                                            $itmSlug = $itm[2] ?? '';
                                            $itmHref = $itmSlug ? url('/'.$itmSlug) : route('gazu.catalog');
                                        ?>
                                        <a wire:navigate href="<?php echo e($itmHref); ?>" class="flex items-baseline gap-2 text-[13px] text-[var(--gazu-graphite)] no-underline hover:text-[var(--gazu-ink)]">
                                            <span class="flex-1"><?php echo e($itmName); ?></span>
                                            <?php if($showCounts): ?><span class="gazu-mono text-[10px] text-[var(--gazu-muted)]"><?php echo e($itmCount); ?></span><?php endif; ?>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="px-5 pt-5 pb-6 flex flex-col gap-4.5" style="gap: 18px;">
            <?php
                $menuBrandsDesktop = collect($brands ?? [])->map(function ($b) {
                    if (is_array($b) && isset($b['slug'])) {
                        $bn = $b['name'] ?? '';
                        if (is_array($bn)) $bn = $bn['uk'] ?? array_values($bn)[0] ?? '';
                        return ['name' => (string) $bn, 'slug' => (string) $b['slug']];
                    }
                    $name = is_array($b) ? ($b['uk'] ?? array_values($b)[0] ?? '') : (string) $b;
                    return ['name' => (string) $name, 'slug' => \Illuminate\Support\Str::slug((string) $name)];
                })->filter(fn ($b) => $b['name'] && $b['slug'])->values()->all();
            ?>
            <div>
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-2.5">Топ бренди</div>
                <div class="grid grid-cols-3 gap-1.5">
                    <?php $__currentLoopData = array_slice($menuBrandsDesktop, 0, 9); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a wire:navigate href="<?php echo e(route('gazu.brand', ['slug' => $b['slug']])); ?>"
                           class="h-9 border border-[var(--gazu-line)] rounded flex items-center justify-center gazu-display text-[11px] font-semibold text-[var(--gazu-steel)] bg-[var(--gazu-surface)] hover:border-[var(--gazu-ink)] hover:text-[var(--gazu-ink)] cursor-pointer no-underline transition-colors"><?php echo e($b['name']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <?php if(! empty($cars)): ?>
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase font-semibold">Марки</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <?php $__currentLoopData = $cars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $name = is_array($c) ? ($c['name'] ?? '') : (string) $c;
                                if (is_array($name)) { $name = $name['uk'] ?? array_values($name)[0] ?? ''; }
                                $name = (string) $name;
                                $slug = is_array($c) ? ((string) ($c['slug'] ?? '')) : '';
                                if ($slug === '' && $name !== '') $slug = \Illuminate\Support\Str::slug($name);
                                $logo = is_array($c) ? ($c['logo'] ?? null) : null;
                            ?>
                            <?php if($name && $slug): ?>
                                <a wire:navigate href="<?php echo e(route('gazu.catalog.by-make', ['make' => $slug])); ?>"
                                   class="flex items-center gap-2 px-2 py-1.5 rounded no-underline text-[var(--gazu-ink)] text-xs hover:bg-[var(--gazu-mist)]">
                                    <span class="w-6 h-6 rounded overflow-hidden inline-flex items-center justify-center shrink-0 <?php echo e($logo ? '' : 'bg-[var(--gazu-mist)] text-[10px] gazu-mono font-bold text-[var(--gazu-blue)]'); ?>">
                                        <?php if($logo): ?><img src="<?php echo e($logo); ?>" alt="<?php echo e($name); ?>" class="w-full h-full object-cover" loading="lazy"><?php else: ?><?php echo e(mb_substr($name, 0, 2)); ?><?php endif; ?>
                                    </span>
                                    <span class="flex-1"><?php echo e($name); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
                $promoKicker = \App\Models\DisplaySetting::get('gazu_megamenu_promo_kicker', '');
                $promoTitle = \App\Models\DisplaySetting::get('gazu_megamenu_promo_title', '');
            ?>
            <?php if($promoKicker || $promoTitle): ?>
                <div class="bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-lg p-4 flex flex-col gap-2 mt-auto">
                    <?php if($promoKicker): ?>
                        <div class="gazu-mono text-[9px] text-[var(--gazu-blue)] tracking-widest uppercase"><?php echo e($promoKicker); ?></div>
                    <?php endif; ?>
                    <?php if($promoTitle): ?>
                        <div class="gazu-display text-lg font-bold"><?php echo e($promoTitle); ?></div>
                    <?php endif; ?>
                    <a wire:navigate href="<?php echo e(route('gazu.catalog', ['promo' => 1])); ?>" class="self-start px-2.5 py-1.5 bg-[var(--gazu-blue)] text-[var(--gazu-on-brand)] rounded text-xs font-medium no-underline mt-0.5">До акції →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/mega-menu.blade.php ENDPATH**/ ?>