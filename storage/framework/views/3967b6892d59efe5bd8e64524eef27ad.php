<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'price' => 184,
    'oldPrice' => null,
    'qty' => 12,
    'discount' => null,
    'productId' => null,
    'name' => '', // product name — for the 1-click modal summary
    'warehouseStocks' => null, // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID (Phase 6)
    'groupActive' => false, // персональна гуртова ціна групи активна → склад не перебиває ціну
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
    'price' => 184,
    'oldPrice' => null,
    'qty' => 12,
    'discount' => null,
    'productId' => null,
    'name' => '', // product name — for the 1-click modal summary
    'warehouseStocks' => null, // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID (Phase 6)
    'groupActive' => false, // персональна гуртова ціна групи активна → склад не перебиває ціну
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $priceFmt = number_format((float) $price, 0, '.', ' ');
    $stocks = $warehouseStocks instanceof \Illuminate\Support\Collection ? $warehouseStocks : collect();
    // Prefer geo-detected warehouse if it has stock; otherwise first in-stock.
    $defaultStock = $closestWarehouseId
        ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
        : null;
    $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
    $defaultWh = $defaultStock?->warehouse_id;
    // Ціни складів — у грн (display_price конвертує за валютою рядка), як у
    // warehouse-selector. Без цього при виборі USD-складу ціна падала на сире число.
    $defaultPrice = $defaultStock && $defaultStock->price !== null ? (float) ($defaultStock->display_price ?? $defaultStock->price) : (float) $price;
    // Build JS lookup: { warehouseId: { price, compare, qty, city, eta } }
    $stocksJs = $stocks->mapWithKeys(fn ($s) => [
        $s->warehouse_id => [
            'price'   => $s->price !== null ? (float) ($s->display_price ?? $s->price) : (float) $price,
            'compare' => $s->compare_at_price !== null ? (float) ($s->display_compare_at_price ?? $s->compare_at_price) : null,
            'qty'     => max(0, $s->quantity - $s->reserved_quantity),
            'city'    => $s->warehouse->city ?: $s->warehouse->name,
            'eta'     => $s->warehouse->delivery_eta ?: '1-3 дні',
        ],
    ])->all();
?>
<div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] p-6 font-text"
     x-data="{
        q: 1,
        warehouseId: <?php echo e($defaultWh ? (int) $defaultWh : 'null'); ?>,
        stocks: <?php echo e(\Illuminate\Support\Js::from($stocksJs)); ?>,
        // AJAX variant-switching state — overrides take priority over per-warehouse stock.
        currentProductId: <?php echo e((int) $productId); ?>,
        overridePrice: null,
        overrideQty: null,
        groupActive: <?php echo e($groupActive ? 'true' : 'false'); ?>,
        get price() {
            if (this.overridePrice !== null) return this.overridePrice;
            // Гуртова ціна групи головніша за ціну складу — вибір складу не змінює ціну.
            if (this.groupActive) return <?php echo e((float) $price); ?>;
            return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].price : <?php echo e((float) $defaultPrice); ?>;
        },
        get compareAt() { return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].compare : null; },
        get available() {
            if (this.overrideQty !== null) return this.overrideQty;
            return this.warehouseId && this.stocks[this.warehouseId] ? this.stocks[this.warehouseId].qty : <?php echo e((int) $qty); ?>;
        },
        fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g, ' '); },
        adding: false,
        async addToCart() {
            if (this.adding || this.available <= 0) return;
            this.adding = true;
            try {
                const r = await fetch('<?php echo e(route('gazu.cart.add')); ?>', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                    body: new URLSearchParams({
                        product_id: String(this.currentProductId),
                        quantity: this.q,
                        warehouse_id: this.overrideQty !== null ? '' : (this.warehouseId || ''),
                    })
                });
                const d = await r.json();
                if (d.ok) {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.count, qtyTotal: d.qtyTotal, total: d.total } }));
                } else {
                    window.gazuToast && window.gazuToast(d.message || 'Не вдалося додати', 'error');
                }
            } catch(e) {
                window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error');
            } finally { this.adding = false; }
        }
     }"
     @warehouse-selected.window="warehouseId = $event.detail.id; overridePrice = null; overrideQty = null;"
     @gazu:variant-switched.window="
        currentProductId = $event.detail.id;
        overridePrice = $event.detail.price;
        overrideQty = $event.detail.qty;
        warehouseId = null;
        q = 1;
     ">
    
    <div class="flex items-end justify-between gap-3 mb-4">
        <div class="min-w-0">
            <span class="gazu-display font-bold text-[var(--gazu-ink)] leading-none gazu-mono" style="font-size: 36px; font-variant-numeric: tabular-nums; display: inline-flex; align-items: baseline; gap: .2em;">
                <span data-gazu-product-price x-text="fmt(price * q)" style="display:inline-block;text-align:left"><?php echo e($priceFmt); ?></span><span class="text-xl font-medium text-[var(--gazu-graphite)]">₴</span>
            </span>
            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1">
                <template x-if="compareAt && compareAt > price">
                    <span class="text-sm text-[var(--gazu-muted)] line-through" x-text="fmt(compareAt) + ' ₴'"></span>
                </template>
                <?php if($oldPrice && !$stocks->isNotEmpty()): ?>
                    <span class="text-sm text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float)$oldPrice, 0, '.', ' ')); ?> ₴</span>
                    <?php if($discount): ?>
                        <span class="text-[11px] gazu-mono px-1.5 py-0.5 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] rounded">−<?php echo e($discount); ?>%</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <span class="text-[11px] text-[var(--gazu-graphite)] gazu-mono" style="font-variant-numeric: tabular-nums;" x-show="q > 1" x-cloak>
                    <span x-text="fmt(price)"></span> ₴ × <span x-text="q"></span> шт.
                </span>
            </div>
        </div>
        
        <div class="shrink-0">
            <div class="text-[10px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)] mb-1.5 text-center">Кількість</div>
            <div class="flex items-center bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                <button type="button" @click="q = Math.max(1, q-1)"
                    aria-label="Зменшити кількість"
                    class="w-10 h-11 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center hover:bg-[var(--gazu-line-2)] active:bg-[var(--gazu-line)] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="q <= 1">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'minus','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','size' => '16']); ?>
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
                <input x-model.number="q" type="number" min="1" :max="available || 99"
                    aria-label="Кількість"
                    class="w-12 h-11 text-center border-0 bg-[var(--gazu-surface)] text-base gazu-mono font-semibold text-[var(--gazu-ink)] outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                <button type="button" @click="q = Math.min((available || 99), q+1)"
                    aria-label="Збільшити кількість"
                    class="w-10 h-11 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center hover:bg-[var(--gazu-line-2)] active:bg-[var(--gazu-line)] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="available > 0 && q >= available">
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
<?php endif; ?>
                </button>
            </div>
        </div>
    </div>

    <div class="h-px bg-[var(--gazu-line)] mb-4"></div>

    <form action="<?php echo e(route('gazu.cart.add')); ?>" method="POST" @submit.prevent="addToCart">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" :value="currentProductId" data-gazu-product-id="<?php echo e($productId); ?>">
        <input type="hidden" name="quantity" :value="q">
        <input type="hidden" name="warehouse_id" :value="warehouseId">

        <?php
            $oneClickEnabled = ($gazuSettings['gazu_oneclick_enabled'] ?? true);
            $oneClickLabel = $gazuSettings['gazu_oneclick_label'] ?? 'Купити в один клік';
            $oneClickMessage = $gazuSettings['gazu_oneclick_message'] ?? 'Менеджер передзвонить за 5 хвилин для уточнення доставки';
        ?>

        
        <?php if($productId): ?>
            <div class="grid grid-cols-1 gap-2.5">
                <button type="submit" :disabled="available <= 0"
                    :class="available <= 0 ? 'bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] cursor-not-allowed' : 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] hover:bg-[var(--gazu-ink-2)] cursor-pointer'"
                    class="w-full h-14 border-0 rounded-lg text-[15px] font-semibold inline-flex items-center justify-center gap-2.5 transition-colors">
                    <template x-if="available > 0">
                        <span class="inline-flex items-center gap-2.5">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '20']); ?>
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
                            <span>Додати в кошик</span>
                            <span class="opacity-70">·</span>
                            <span class="gazu-mono"><span x-text="fmt(price * q)"><?php echo e($priceFmt); ?></span> ₴</span>
                        </span>
                    </template>
                    <template x-if="available <= 0">
                        <span class="inline-flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'clock','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','size' => '18']); ?>
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
                            Під замовлення
                        </span>
                    </template>
                </button>

                <?php if($oneClickEnabled): ?>
                    <button type="button" @click.prevent="$dispatch('gazu:one-click', { productId: '<?php echo e($productId); ?>', productName: <?php echo \Illuminate\Support\Js::from($name ?? '')->toHtml() ?>, productPrice: price * q })"
                            class="w-full h-12 bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border-[1.5px] border-[var(--gazu-ink)] rounded-lg text-[14px] font-medium cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-mist)] transition-colors">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'phone','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','size' => '16']); ?>
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
                        <?php echo e($oneClickLabel); ?>

                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>

    

    <?php
        // Trust-бейджі з налаштувань (gazu_product_trust) із fallback на дефолт.
        // Токен {date} у заголовку → завтрашня дата.
        $trustBadges = $gazuSettings['gazu_product_trust'] ?? [
            ['icon' => 'truck',  'title' => 'Доставка завтра, {date}', 'subtitle' => 'Замовте сьогодні до 16:00 · Нова Пошта'],
            ['icon' => 'shield', 'title' => 'Гарантія 12 місяців',     'subtitle' => 'Повернення коштів при дефекті'],
            ['icon' => 'return', 'title' => '14 днів на повернення',   'subtitle' => 'Без пояснення причин'],
        ];
        $tomorrow = now()->addDay()->format('d.m');
    ?>
    <?php if(! empty($trustBadges)): ?>
    <div class="mt-4 p-3.5 bg-[var(--gazu-mist)] rounded-lg flex flex-col gap-2.5">
        <?php $__currentLoopData = $trustBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $bTitle = str_replace('{date}', $tomorrow, (string) ($badge['title'] ?? ''));
                $bSub = $badge['subtitle'] ?? '';
                $bIcon = $badge['icon'] ?? 'shield';
            ?>
            <div class="flex gap-2.5 items-start">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => $bIcon,'size' => '18','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bIcon),'size' => '18','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']); ?>
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
                <div>
                    <div class="text-[13px] font-medium text-[var(--gazu-ink)]"><?php echo e($bTitle); ?></div>
                    <?php if($bSub): ?><div class="text-[11px] text-[var(--gazu-graphite)]"><?php echo e($bSub); ?></div><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="mt-4 p-3 border border-dashed border-[var(--gazu-line-2)] rounded-lg flex gap-2.5 items-center">
        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chat','size' => '20','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chat','size' => '20','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']); ?>
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
        <div class="text-xs text-[var(--gazu-graphite)] leading-relaxed">
            <?php $gazuPhoneTel = preg_replace('/\D+/', '', ($gazuSettings ?? [])['gazu_phone'] ?? '0 800 750 010'); ?>
            Не впевнені в підборі? <a href="tel:<?php echo e($gazuPhoneTel); ?>" class="text-[var(--gazu-blue)] font-medium hover:underline no-underline">Запитайте менеджера</a> — відповість за 5 хв.
        </div>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/buy-panel.blade.php ENDPATH**/ ?>