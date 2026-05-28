<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'priceRange' => ['min' => 0, 'max' => 10000, 'currentMin' => 0, 'currentMax' => 10000],
    'availableCategories' => null,
    'availableBrands' => collect(),
    'selectedBrands' => [],
    'availableConditions' => null,
    'selectedConditions' => [],
    'inStockOnly' => false,
    'searchQuery' => '',
    'category' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'priceRange' => ['min' => 0, 'max' => 10000, 'currentMin' => 0, 'currentMax' => 10000],
    'availableCategories' => null,
    'availableBrands' => collect(),
    'selectedBrands' => [],
    'availableConditions' => null,
    'selectedConditions' => [],
    'inStockOnly' => false,
    'searchQuery' => '',
    'category' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $brands = collect($availableBrands);
    $selected = collect($selectedBrands);
    $rangeFromUrl = ['min','max','sort'];
    $hasFilters = !empty(request('brand')) || request()->filled('min') || request()->filled('max') || request('stock') === 'in';
?>

<form method="GET" action="<?php echo e(url()->current()); ?>" class="font-text text-sm" x-data="{
        priceMin: '<?php echo e(request()->filled('min') ? (int) request('min') : ''); ?>',
        priceMax: '<?php echo e(request()->filled('max') ? (int) request('max') : ''); ?>'
    }">
    
    <?php $__currentLoopData = ['cat', 'q', 'sort']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(request()->filled($kept)): ?>
            <input type="hidden" name="<?php echo e($kept); ?>" value="<?php echo e(request($kept)); ?>">
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php if(module('gazu_garage')->enabled()): ?>
        <?php
            $primaryCar = auth()->check() ? auth()->user()->primaryCar : null;
            $rows = [];
            if ($primaryCar) {
                $rows = [
                    ['Марка', $primaryCar->make, true],
                    ['Модель', $primaryCar->model, true],
                    ['Рік', $primaryCar->year ?: '—', (bool) $primaryCar->year],
                    ['Двигун', $primaryCar->engine ?: '—', (bool) $primaryCar->engine],
                    ['Кузов', $primaryCar->body_type ?: '—', (bool) $primaryCar->body_type],
                ];
            }
        ?>
        <div class="bg-[var(--gazu-mist)] p-4 rounded-lg mb-5">
            <div class="text-xs gazu-mono text-[var(--gazu-graphite)] tracking-widest uppercase mb-2.5">Ваш автомобіль</div>
            <?php if($primaryCar): ?>
                <div class="flex flex-col gap-2">
                    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $v, $filled]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2.5 px-2.5 py-2 bg-white rounded <?php echo e($filled ? 'border border-[var(--gazu-line)]' : 'border border-[var(--gazu-line-2)] opacity-60'); ?>">
                            <span class="text-[11px] text-[var(--gazu-graphite)] w-14"><?php echo e($k); ?></span>
                            <span class="flex-1 text-[13px] <?php echo e($filled ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-muted)]'); ?>"><?php echo e($v); ?></span>
                            <?php if($filled): ?><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
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
<?php endif; ?><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <a wire:navigate href="<?php echo e(route('gazu.garage')); ?>" class="block w-full mt-2.5 py-2 bg-transparent border border-dashed border-[var(--gazu-line-2)] rounded text-xs text-[var(--gazu-graphite)] cursor-pointer text-center no-underline">Змінити авто</a>
            <?php else: ?>
                <p class="text-xs text-[var(--gazu-graphite)] mb-2"><?php if(auth()->guard()->check()): ?> Додайте авто у Гараж — фільтр буде підставляти його автоматично <?php else: ?> Увійдіть, щоб зберегти своє авто <?php endif; ?></p>
                <a wire:navigate href="<?php echo e(auth()->check() ? route('gazu.garage') : route('gazu.auth')); ?>"
                   class="block w-full py-2 bg-[var(--gazu-ink)] text-white rounded text-xs text-center no-underline hover:bg-[var(--gazu-ink-2)]">
                    <?php if(auth()->guard()->check()): ?> + Додати авто <?php else: ?> Увійти <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <details class="border-b border-[var(--gazu-line)] py-3.5" open>
        <summary class="flex justify-between items-center cursor-pointer list-none">
            <span class="text-sm font-medium text-[var(--gazu-ink)]">Ціна, ₴</span>
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']); ?>
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
        </summary>
        <div class="mt-3">
            <div class="flex gap-2">
                <input type="number" name="min" x-model="priceMin"
                       min="<?php echo e($priceRange['min']); ?>" max="<?php echo e($priceRange['max']); ?>"
                       placeholder="від <?php echo e((int) $priceRange['min']); ?>"
                       class="flex-1 py-2 px-2.5 text-[13px] gazu-mono border border-[var(--gazu-line)] rounded bg-white outline-none placeholder:text-[var(--gazu-muted)]">
                <input type="number" name="max" x-model="priceMax"
                       min="<?php echo e($priceRange['min']); ?>" max="<?php echo e($priceRange['max']); ?>"
                       placeholder="до <?php echo e((int) $priceRange['max']); ?>"
                       class="flex-1 py-2 px-2.5 text-[13px] gazu-mono border border-[var(--gazu-line)] rounded bg-white outline-none placeholder:text-[var(--gazu-muted)]">
            </div>
            <div class="text-[11px] text-[var(--gazu-muted)] mt-2">
                Від <span class="gazu-mono"><?php echo e(number_format($priceRange['min'], 0, '.', ' ')); ?> ₴</span>
                до <span class="gazu-mono"><?php echo e(number_format($priceRange['max'], 0, '.', ' ')); ?> ₴</span>
            </div>
        </div>
    </details>

    
    <?php
        $catList = collect($availableCategories ?? []);
        // Збираємо filter query string що треба зберегти при переході між
        // категоріями. Pretty-URL routes (/zapchastyny/{make}/...) ставлять
        // car params через $request->query->set (path → query). Беремо також
        // $selectedMake/Model/Engine якщо доступні з catalog/v1 controller.
        $carParams = array_filter([
            'make'   => $selectedMake   ?? request('make'),
            'model'  => $selectedModel  ?? request('model'),
            'engine' => $selectedEngine ?? request('engine'),
        ], fn ($v) => $v !== null && $v !== '');

        $otherParams = collect(['brand', 'min', 'max', 'condition', 'stock', 'sort', 'q'])
            ->mapWithKeys(fn ($k) => [$k => request($k)])
            ->filter(fn ($v) => $v !== null && $v !== '' && $v !== [])
            ->all();

        $preserveParams = array_merge($carParams, $otherParams);
        $preserveQuery = ! empty($preserveParams) ? '?'.http_build_query($preserveParams) : '';
    ?>
    <?php if($catList->isNotEmpty()): ?>
        <?php $catLimit = 8; $catHidden = max(0, $catList->count() - $catLimit); ?>
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">
                    <?php echo e($category ? 'Підкатегорії' : 'Категорія'); ?>

                </span>
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']); ?>
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
            </summary>
            <div class="mt-3" x-data="{ showAllCats: false }">
                <?php if($category): ?>
                    
                    <a wire:navigate href="<?php echo e(route('gazu.catalog').$preserveQuery); ?>"
                       class="flex items-center gap-2 py-1.5 text-[13px] text-[var(--gazu-blue)] no-underline hover:text-[var(--gazu-ink)]">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Усі категорії
                    </a>
                <?php endif; ?>
                <?php $__currentLoopData = $catList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $rawSlug = $cat->getRawOriginal('slug');
                        if (is_string($rawSlug) && str_starts_with($rawSlug, '{')) {
                            $rawSlug = json_decode($rawSlug, true)['uk'] ?? null;
                        }
                        $catSlug = (string) ($rawSlug ?: '');
                        $catTitle = is_array($cat->title) ? ($cat->title['uk'] ?? '—') : ($cat->title ?? '—');
                        $catCount = $cat->products_count ?? 0;
                        $hidden = $i >= $catLimit;
                    ?>
                    <a wire:navigate href="<?php echo e(url('/'.$catSlug).$preserveQuery); ?>"
                       class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)] no-underline"
                       <?php if($hidden): ?> x-show="showAllCats" x-cloak <?php endif; ?>>
                        <span class="flex-1 truncate"><?php echo e($catTitle); ?></span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono"><?php echo e($catCount); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($catHidden > 0): ?>
                    <button type="button"
                            @click.prevent="showAllCats = !showAllCats"
                            class="mt-2 text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline inline-flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0">
                        <span x-show="!showAllCats">Показати ще <?php echo e($catHidden); ?></span>
                        <span x-show="showAllCats" x-cloak>Згорнути</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="showAllCats ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                <?php endif; ?>

                
                <div class="mt-3 pt-3 border-t border-[var(--gazu-line)] flex flex-col gap-1.5">
                    <?php if($category): ?>
                        
                        <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>"
                           class="flex items-center justify-between gap-2 px-2.5 py-1.5 bg-[var(--gazu-mist)] hover:bg-[var(--gazu-paper)] rounded text-[12px] text-[var(--gazu-ink)] no-underline transition-colors">
                            <span class="inline-flex items-center gap-1.5">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                Обрати головну категорію
                            </span>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    <?php endif; ?>
                    <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>"
                       class="flex items-center justify-between gap-2 px-2.5 py-1.5 hover:bg-[var(--gazu-mist)] rounded text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline transition-colors">
                        <span class="inline-flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            Переглянути всі товари
                        </span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </details>
    <?php endif; ?>

    
    <?php if($brands->isNotEmpty()): ?>
        <?php
            $brandLimit = 8;
            $brandHidden = max(0, $brands->count() - $brandLimit);
        ?>
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">Виробник</span>
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']); ?>
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
            </summary>
            <div class="mt-3" x-data="{ showAll: false }">
                <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $value = is_object($row) ? $row->manufacturer : ($row['manufacturer'] ?? '');
                        $label = is_object($row) ? ($row->label ?? $row->manufacturer) : ($row['label'] ?? $row['manufacturer'] ?? '');
                        $count = is_object($row) ? $row->count : ($row['count'] ?? 0);
                        $checked = $selected->contains($value);
                        $hidden = $i >= $brandLimit && ! $checked;
                    ?>
                    <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]"
                           <?php if($hidden): ?> x-show="showAll" x-cloak <?php endif; ?>>
                        <input type="checkbox" name="brand[]" value="<?php echo e($value); ?>"
                               class="sr-only" <?php echo e($checked ? 'checked' : ''); ?>

                               onchange="this.form.submit()">
                        <span class="w-4 h-4 border-[1.5px] <?php echo e($checked ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white'); ?> rounded inline-flex items-center justify-center shrink-0">
                            <?php if($checked): ?><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?><?php endif; ?>
                        </span>
                        <span class="flex-1"><?php echo e($label); ?></span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono"><?php echo e($count); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($brandHidden > 0): ?>
                    <button type="button"
                            @click.prevent="showAll = !showAll"
                            class="mt-2 text-[12px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] no-underline inline-flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0">
                        <span x-show="!showAll">Показати ще <?php echo e($brandHidden); ?></span>
                        <span x-show="showAll" x-cloak>Згорнути</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="showAll ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                <?php endif; ?>
            </div>
        </details>
    <?php endif; ?>

    
    <?php
        $conditions = collect($availableConditions ?? collect());
        $selectedConds = collect($selectedConditions ?? []);
        $condLabels = ['new' => 'Новий', 'used' => 'Б/у', 'refurbished' => 'Відновлений'];
    ?>
    <?php if($conditions->isNotEmpty()): ?>
        <details class="border-b border-[var(--gazu-line)] py-3.5" open>
            <summary class="flex justify-between items-center cursor-pointer list-none">
                <span class="text-sm font-medium text-[var(--gazu-ink)]">Стан</span>
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']); ?>
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
            </summary>
            <div class="mt-3">
                <?php $__currentLoopData = $conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $val = is_object($row) ? $row->condition : ($row['condition'] ?? '');
                        $count = is_object($row) ? $row->count : ($row['count'] ?? 0);
                        $checked = $selectedConds->contains($val);
                        $label = $condLabels[$val] ?? ucfirst($val);
                    ?>
                    <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]">
                        <input type="checkbox" name="condition[]" value="<?php echo e($val); ?>"
                               class="sr-only" <?php echo e($checked ? 'checked' : ''); ?>

                               onchange="this.form.submit()">
                        <span class="w-4 h-4 border-[1.5px] <?php echo e($checked ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white'); ?> rounded inline-flex items-center justify-center shrink-0">
                            <?php if($checked): ?><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?><?php endif; ?>
                        </span>
                        <span class="flex-1"><?php echo e($label); ?></span>
                        <span class="text-xs text-[var(--gazu-muted)] gazu-mono"><?php echo e($count); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </details>
    <?php endif; ?>

    
    <details class="border-b border-[var(--gazu-line)] py-3.5" <?php echo e($inStockOnly ? 'open' : ''); ?>>
        <summary class="flex justify-between items-center cursor-pointer list-none">
            <span class="text-sm font-medium text-[var(--gazu-ink)]">Наявність</span>
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '16','stroke' => 'var(--gazu-graphite)']); ?>
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
        </summary>
        <div class="mt-3">
            <label class="flex items-center gap-2.5 py-1.5 cursor-pointer text-[13px] text-[var(--gazu-ink)]">
                <input type="checkbox" name="stock" value="in" class="sr-only"
                       <?php echo e($inStockOnly ? 'checked' : ''); ?> onchange="this.form.submit()">
                <span class="w-4 h-4 border-[1.5px] <?php echo e($inStockOnly ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)] bg-white'); ?> rounded inline-flex items-center justify-center">
                    <?php if($inStockOnly): ?><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '11','stroke' => '#fff','strokeWidth' => '2.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?><?php endif; ?>
                </span>
                <span class="flex-1">Тільки в наявності</span>
            </label>
        </div>
    </details>

    <button type="submit" class="w-full mt-4 py-3 bg-[var(--gazu-ink)] text-white border-0 rounded text-[13px] font-medium cursor-pointer hover:bg-[var(--gazu-ink-2)]">
        Застосувати фільтри
    </button>
    <?php if($hasFilters || request()->filled('q')): ?>
        <a wire:navigate href="<?php echo e($category ? url()->current().'?cat='.($category->slug ?? $category->id) : url()->current()); ?>"
           class="block w-full mt-1.5 py-2 bg-transparent text-center text-[var(--gazu-graphite)] text-xs no-underline">
            Скинути всі фільтри
        </a>
    <?php endif; ?>
</form>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/filter-panel.blade.php ENDPATH**/ ?>