<?php $__env->startSection('title', 'Гараж — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], ['Кабінет', route('gazu.account')], 'Гараж']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], ['Кабінет', route('gazu.account')], 'Гараж'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $attributes = $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $component = $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>

    <?php if(session('flash_message')): ?>
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            <?php echo e(session('flash_message')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm">
            <strong>Виправте помилки:</strong>
            <ul class="list-disc list-inside mt-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($err); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="gazu-grid-account mt-3">
        <?php echo $__env->make('gazu.partials.account-sidebar', ['active' => 'garage', 'user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div x-data="{ openAdd: false, editing: null }">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <div>
                    <h2 class="gazu-display text-3xl font-semibold m-0">Гараж</h2>
                    <p class="text-sm text-[var(--gazu-graphite)] mt-1"><?php echo e($cars->count()); ?> <?php echo e($cars->count() === 1 ? 'авто' : 'авто'); ?> · збережіть авто і отримуйте підбір запчастин у 1 клік</p>
                </div>
                <button type="button" @click="openAdd = true" class="gazu-btn-primary">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Додати авто
                </button>
            </div>

            
            <div class="grid md:grid-cols-2 gap-4">
                <?php $__currentLoopData = $cars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 <?php echo e($car->is_primary ? 'border-[var(--gazu-blue)]' : ''); ?>">
                        <?php if($car->is_primary): ?>
                            <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">⭐ Основне</div>
                        <?php endif; ?>
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-14 h-14 bg-[var(--gazu-mist)] rounded-md flex items-center justify-center text-[var(--gazu-blue)]">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'car','size' => '28']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'car','size' => '28']); ?>
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
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="gazu-display text-lg font-semibold text-[var(--gazu-ink)]"><?php echo e($car->make); ?> <?php echo e($car->model); ?></div>
                                <div class="text-sm text-[var(--gazu-graphite)]">
                                    <?php if($car->year): ?><?php echo e($car->year); ?> рік<?php endif; ?>
                                    <?php if($car->engine): ?> · <?php echo e($car->engine); ?><?php endif; ?>
                                </div>
                                <?php if($car->body_type): ?>
                                    <div class="text-xs text-[var(--gazu-graphite)] mt-0.5"><?php echo e($car->body_type); ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="button" @click='editing = <?php echo e(json_encode($car)); ?>'
                                    class="bg-transparent border-0 text-[var(--gazu-graphite)] cursor-pointer p-1 hover:text-[var(--gazu-ink)]">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'edit','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit','size' => '16']); ?>
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

                        <?php if($car->vin || $car->plate || $car->color): ?>
                            <div class="grid grid-cols-<?php echo e(count(array_filter([$car->vin, $car->plate, $car->color]))); ?> gap-3 text-xs mb-3">
                                <?php if($car->vin): ?>
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">VIN</div>
                                        <div class="gazu-mono text-[var(--gazu-ink)]"><?php echo e($car->vin); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if($car->plate): ?>
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">Номер</div>
                                        <div class="gazu-mono text-[var(--gazu-ink)]"><?php echo e($car->plate); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if($car->color): ?>
                                    <div>
                                        <div class="text-[var(--gazu-graphite)] mb-0.5">Колір</div>
                                        <div class="text-[var(--gazu-ink)]"><?php echo e($car->color); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-2 flex-wrap">
                            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary flex-1 text-xs py-2 no-underline">Запчастини для авто</a>
                            <?php if(! $car->is_primary): ?>
                                <form action="<?php echo e(route('gazu.garage.primary', ['car' => $car->id])); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" title="Зробити основним" class="gazu-btn-outline text-xs py-2 px-3">⭐</button>
                                </form>
                            <?php endif; ?>
                            <form action="<?php echo e(route('gazu.garage.destroy', ['car' => $car->id])); ?>" method="POST" class="inline"
                                  onsubmit="return confirm('Видалити <?php echo e(addslashes($car->make.' '.$car->model)); ?>?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Видалити" class="gazu-btn-outline text-xs py-2 px-3 hover:text-[var(--gazu-danger)]">
                                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'trash','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','size' => '14']); ?>
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
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <button type="button" @click="openAdd = true"
                        class="bg-[var(--gazu-mist)] border-2 border-dashed border-[var(--gazu-line-2)] rounded-lg p-5 flex flex-col items-center justify-center text-center min-h-[280px] cursor-pointer hover:border-[var(--gazu-blue)] hover:bg-[var(--gazu-paper)]">
                    <div class="w-12 h-12 bg-[var(--gazu-surface)] rounded-full flex items-center justify-center text-[var(--gazu-blue)] mb-3">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '24']); ?>
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
                    </div>
                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1.5"><?php echo e($cars->isEmpty() ? 'Додайте перше авто' : 'Додати ще одне авто'); ?></div>
                    <p class="text-xs text-[var(--gazu-graphite)] max-w-[220px]">VIN, державний номер або вручну вкажіть марку, модель та рік</p>
                </button>
            </div>

            
            <div x-show="openAdd" x-cloak x-transition.opacity
                 class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
                 @click.self="openAdd = false">
                <div class="bg-[var(--gazu-surface)] rounded-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="gazu-display text-xl font-semibold m-0">Додати авто</h3>
                        <button type="button" @click="openAdd = false" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'close','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','size' => '20']); ?>
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
                    <form action="<?php echo e(route('gazu.garage.store')); ?>" method="POST" class="grid grid-cols-2 gap-3">
                        <?php echo csrf_field(); ?>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Марка <span class="text-[var(--gazu-danger)]">*</span></span>
                            <input type="text" name="make" value="<?php echo e(old('make')); ?>" required placeholder="Volkswagen"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Модель <span class="text-[var(--gazu-danger)]">*</span></span>
                            <input type="text" name="model" value="<?php echo e(old('model')); ?>" required placeholder="Passat B8"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Рік випуску</span>
                            <input type="number" name="year" value="<?php echo e(old('year')); ?>" min="1950" max="<?php echo e(date('Y') + 1); ?>" placeholder="2018"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Двигун</span>
                            <input type="text" name="engine" value="<?php echo e(old('engine')); ?>" placeholder="2.0 TDI · CKFC"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Кузов</span>
                            <input type="text" name="body_type" value="<?php echo e(old('body_type')); ?>" placeholder="Універсал"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Колір</span>
                            <input type="text" name="color" value="<?php echo e(old('color')); ?>" placeholder="Сірий"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                        </label>
                        <label class="block col-span-2">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">VIN-код (опціонально)</span>
                            <input type="text" name="vin" value="<?php echo e(old('vin')); ?>" maxlength="30" placeholder="WVWZZZ3CZJE000000"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase">
                        </label>
                        <label class="block">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Держ. номер (опціонально)</span>
                            <input type="text" name="plate" value="<?php echo e(old('plate')); ?>" maxlength="20" placeholder="AA1234BB"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase">
                        </label>
                        <label class="flex items-center gap-2 col-span-2 text-sm">
                            <input type="checkbox" name="is_primary" value="1" class="w-4 h-4">
                            <span>Зробити основним авто (підставлятиметься у фільтр «Ваш автомобіль»)</span>
                        </label>
                        <div class="col-span-2 flex gap-2 mt-2">
                            <button type="submit" class="gazu-btn-primary flex-1">Додати авто</button>
                            <button type="button" @click="openAdd = false" class="gazu-btn-outline">Скасувати</button>
                        </div>
                    </form>
                </div>
            </div>

            
            <div x-show="editing" x-cloak x-transition.opacity
                 class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
                 @click.self="editing = null">
                <div class="bg-[var(--gazu-surface)] rounded-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="gazu-display text-xl font-semibold m-0">Редагувати авто</h3>
                        <button type="button" @click="editing = null" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'close','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','size' => '20']); ?>
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
                    <form :action="editing ? `<?php echo e(route('gazu.garage')); ?>/${editing.id}` : ''" method="POST" class="grid grid-cols-2 gap-3">
                        <?php echo csrf_field(); ?>
                        <template x-if="editing">
                            <div class="contents">
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Марка</span>
                                    <input type="text" name="make" x-model="editing.make" required class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Модель</span>
                                    <input type="text" name="model" x-model="editing.model" required class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Рік</span>
                                    <input type="number" name="year" x-model="editing.year" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Двигун</span>
                                    <input type="text" name="engine" x-model="editing.engine" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Кузов</span>
                                    <input type="text" name="body_type" x-model="editing.body_type" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Колір</span>
                                    <input type="text" name="color" x-model="editing.color" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></label>
                                <label class="block col-span-2"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">VIN</span>
                                    <input type="text" name="vin" x-model="editing.vin" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase"></label>
                                <label class="block"><span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Номер</span>
                                    <input type="text" name="plate" x-model="editing.plate" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none gazu-mono uppercase"></label>
                                <div class="col-span-2 flex gap-2 mt-2">
                                    <button type="submit" class="gazu-btn-primary flex-1">Зберегти</button>
                                    <button type="button" @click="editing = null" class="gazu-btn-outline">Скасувати</button>
                                </div>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/account/garage.blade.php ENDPATH**/ ?>