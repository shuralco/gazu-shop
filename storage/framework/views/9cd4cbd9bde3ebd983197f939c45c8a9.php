<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['p', 'compact' => false, 'eager' => false]));

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

foreach (array_filter((['p', 'compact' => false, 'eager' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // $p — Product модель або mock-об'єкт/array з overlay-полями
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? $p->sku ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? $p->manufacturer ?? '') : ($p['brand'] ?? '');
    // Brand slug для clickable link на /catalog?brand[]=slug
    $brandSlug = is_object($p) ? ($p->brand_slug ?? null) : ($p['brand_slug'] ?? null);
    if (! $brandSlug && $brand && $brand !== 'GAZU') {
        $brandSlug = is_object($p) && ! empty($p->manufacturer) ? $p->manufacturer : \Illuminate\Support\Str::slug($brand);
    }
    // SEO-friendly URL: /brand/{slug} замість /catalog?brand[]=slug.
    // Controller робить fallback на name search якщо slug не matches → працює для legacy.
    $brandUrl = $brandSlug ? route('gazu.brand', ['slug' => $brandSlug]) : null;
    $image = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    // Реальне завантажене фото товару (пріоритет над заглушкою).
    $realImg = is_object($p) ? ($p->image ?? null) : ($p['image'] ?? null);
    if ($realImg && ! \Illuminate\Support\Str::startsWith($realImg, ['http://', 'https://'])) {
        $realImg = url('/storage/'.ltrim((string) $realImg, '/'));
    }
    $price = is_object($p) ? (float) ($p->price ?? 0) : (float) ($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    // Персональна (гуртова) ціна — для залогінених клієнтів з групою.
    $isGroupPrice = is_object($p) ? (bool) ($p->is_group_price ?? false) : false;
    $groupLabel = is_object($p) ? ($p->group_label ?? null) : null;
    $groupFromQty = is_object($p) ? ($p->group_from_qty ?? null) : null;
    $groupFromPrice = is_object($p) ? ($p->group_from_price ?? null) : null;
    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
    // $p->reviews може бути HasMany Collection (Eloquent) — захищаємось.
    $rawQty = is_object($p) ? ($p->qty ?? $p->quantity ?? 0) : ($p['qty'] ?? 0);
    $qty = is_numeric($rawQty) ? (int) $rawQty : 0;
    $stockStatus = is_object($p) ? ($p->stock_status ?? null) : ($p['stock_status'] ?? null);
    // Backorder: товар без залишку (qty<=0) можна замовити, якщо увімкнено в адмінці.
    $allowBackorder = isset($gazuSettings) ? (bool) ($gazuSettings['gazu_allow_backorder'] ?? true) : true;
    $isBackorder = $qty <= 0;
    $canOrder = $qty > 0 || $allowBackorder;
    $rawRating = is_object($p) ? ($p->rating ?? 0) : ($p['rating'] ?? 0);
    $rating = is_numeric($rawRating) ? (float) $rawRating : 0.0;
    $rawReviews = is_object($p) ? ($p->reviews_count ?? $p->reviews ?? 0) : ($p['reviews'] ?? 0);
    $reviews = is_numeric($rawReviews) ? (int) $rawReviews : 0;
    $fits = is_object($p) ? ($p->fits ?? null) : ($p['fits'] ?? null);
    $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
    $productId = is_object($p) ? ($p->id ?? null) : ($p['id'] ?? null);
?>
<div x-data="{
        cardWh: null,
        cardPrice: <?php echo e((float) $price); ?>,
        cardQty: <?php echo e((int) $qty); ?>,
     }"
     @gazu:card-warehouse.window="
        if (parseInt($event.detail.productId) === <?php echo e((int) ($productId ?? 0)); ?>) {
            cardWh    = parseInt($event.detail.warehouseId);
            cardPrice = parseFloat($event.detail.price);
            cardQty   = parseInt($event.detail.qty);
        }
     "
     class="group gazu-card-anim bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg flex flex-col font-text relative transition-all duration-200 hover:border-[var(--gazu-ink)] hover:shadow-[0_8px_24px_-12px_rgba(14,27,44,0.25)] hover:-translate-y-0.5 hover:z-40">
    <?php if($discount): ?>
        <div class="absolute top-2 left-2 px-2 py-0.5 bg-[var(--gazu-danger)] text-[var(--gazu-on-brand)] text-[11px] font-semibold rounded gazu-mono z-10">−<?php echo e($discount); ?>%</div>
    <?php endif; ?>
    <?php if($productId): ?>
        
        <button type="button"
                data-wishlist-pid="<?php echo e($productId); ?>"
                x-data="{ active: false, busy: false, burst: false }"
                x-init="if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has(<?php echo e((int) $productId); ?>)) active = true;
                        window.addEventListener('gazu:wishlist-ids-loaded', () => { if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has(<?php echo e((int) $productId); ?>)) active = true; });"
                @click.prevent="
                    if (busy) return; busy = true;
                    Promise.resolve(window.gazuWishlistToggle(<?php echo e((int) $productId); ?>)).then(inWl => {
                        active = inWl;
                        if (active) { burst = true; setTimeout(() => burst = false, 600); }
                    }).finally(() => { busy = false; });
                "
                :title="active ? 'Прибрати з обраного' : 'Додати в обране'"
                :class="active ? 'text-[var(--gazu-danger)]' : 'text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)]'"
                class="absolute top-2 right-2 w-8 h-8 rounded-md border-0 bg-[var(--gazu-surface)]/85 cursor-pointer flex items-center justify-center z-10 transition-colors">
            <svg :class="burst ? 'gazu-heart-burst' : ''" width="16" height="16" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" :fill="active ? 'currentColor' : 'none'">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
            </svg>
        </button>
    <?php endif; ?>

    
    <a wire:navigate href="<?php echo e($url); ?>" class="aspect-square block no-underline relative overflow-hidden rounded-t-lg group/img">
        <?php if($realImg): ?>
            
            <img src="<?php echo e($realImg); ?>" alt="<?php echo e($name); ?>" loading="<?php echo e($eager ? 'eager' : 'lazy'); ?>"
                 fetchpriority="<?php echo e($eager ? 'high' : 'auto'); ?>"
                 class="absolute inset-0 w-full h-full object-contain bg-[var(--gazu-surface)]">
        <?php else: ?>
            
            <?php if (isset($component)) { $__componentOriginalb3ce7faecba1472bd9053bf57696fe20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-placeholder','data' => ['name' => $name,'code' => $oem,'seed' => $productId ?? $name,'kind' => $image,'class' => 'absolute inset-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-placeholder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'code' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oem),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId ?? $name),'kind' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($image),'class' => 'absolute inset-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3ce7faecba1472bd9053bf57696fe20)): ?>
<?php $attributes = $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20; ?>
<?php unset($__attributesOriginalb3ce7faecba1472bd9053bf57696fe20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3ce7faecba1472bd9053bf57696fe20)): ?>
<?php $component = $__componentOriginalb3ce7faecba1472bd9053bf57696fe20; ?>
<?php unset($__componentOriginalb3ce7faecba1472bd9053bf57696fe20); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if($oem): ?>
            <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 gazu-mono text-[10px] text-[var(--gazu-graphite)] bg-[var(--gazu-surface)]/90 border border-[var(--gazu-line)] rounded z-[1]"><?php echo e($oem); ?></span>
        <?php endif; ?>
    </a>

    <div class="<?php echo e($compact ? 'p-2.5 sm:p-3.5' : 'p-3.5'); ?> flex flex-col gap-1.5 sm:gap-2">
        <div class="flex items-center gap-1.5 min-w-0">
            <?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.condition-badge','data' => ['value' => ''.e($condition).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($condition).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $attributes = $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $component = $__componentOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
            <?php if($brandUrl): ?>
                <a wire:navigate href="<?php echo e($brandUrl); ?>" class="gazu-mono text-[11px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-blue)] truncate no-underline transition-colors" @click.stop><?php echo e($brand); ?></a>
            <?php else: ?>
                <span class="gazu-mono text-[11px] text-[var(--gazu-graphite)] truncate"><?php echo e($brand); ?></span>
            <?php endif; ?>
        </div>

        <a wire:navigate href="<?php echo e($url); ?>" class="text-[13px] text-[var(--gazu-ink)] leading-snug font-medium no-underline line-clamp-2" style="min-height: 36px;">
            <?php echo e($name); ?>

        </a>

        <?php if($fits): ?>
            <div class="text-[11px] text-[var(--gazu-graphite)] leading-snug px-2 py-1.5 bg-[var(--gazu-mist)] rounded flex gap-1.5 items-start">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '12','stroke' => 'var(--gazu-blue)','class' => 'shrink-0 mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '12','stroke' => 'var(--gazu-blue)','class' => 'shrink-0 mt-0.5']); ?>
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
                <span class="line-clamp-2"><?php echo e($fits); ?></span>
            </div>
        <?php endif; ?>

        <?php if($rating > 0 && $reviews > 0): ?>
            <div class="flex items-center gap-1.5 mt-0.5">
                <div class="flex gap-px text-[var(--gazu-warn)] shrink-0">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'star','size' => '12','fill' => ''.e($i <= floor($rating) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','size' => '12','fill' => ''.e($i <= floor($rating) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']); ?>
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
                    <?php endfor; ?>
                </div>
                <span class="text-[11px] text-[var(--gazu-graphite)] whitespace-nowrap"><?php echo e(number_format($rating, 1)); ?> (<?php echo e($reviews); ?>)</span>
            </div>
        <?php endif; ?>

        <?php if($isBackorder && $allowBackorder): ?>
            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[var(--gazu-blue)]">
                <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-blue)]"></span>Під замовлення
            </span>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock','data' => ['qty' => ''.e($qty).'','status' => $stockStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['qty' => ''.e($qty).'','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stockStatus)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $attributes = $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $component = $__componentOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?>
        <?php endif; ?>

        <div class="flex items-end gap-2 mt-1 flex-wrap">
            <?php if($oldPrice): ?>
                <span class="text-xs text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float)$oldPrice, 0, '.', ' ')); ?> ₴</span>
            <?php endif; ?>
            <span class="gazu-display text-[19px] sm:text-[22px] font-bold text-[var(--gazu-ink)] leading-none">
                <span x-text="Math.round(cardPrice).toLocaleString('uk-UA').replace(/,/g,' ')"><?php echo e(number_format($price, 0, '.', ' ')); ?></span>
                <span class="text-sm font-medium text-[var(--gazu-graphite)]">₴</span>
            </span>
            <?php if($isGroupPrice): ?>
                <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-[var(--gazu-blue-bg,#E0EBFF)] text-[var(--gazu-blue)]"
                      title="<?php echo e($groupLabel ? 'Ціна для групи: '.$groupLabel : 'Ваша гуртова ціна'); ?>">
                    <?php echo e($groupLabel ?: 'Гуртова'); ?>

                </span>
            <?php endif; ?>
        </div>
        <?php if($groupFromQty && $groupFromPrice): ?>
            <div class="text-[11px] text-[var(--gazu-blue)] mt-0.5">
                Гуртова <?php echo e(number_format((float) $groupFromPrice, 0, '.', ' ')); ?> ₴ від <?php echo e($groupFromQty); ?> шт
            </div>
        <?php endif; ?>

        <div class="flex gap-1.5 mt-1">
            <?php if($productId && ($qty > 0 || $allowBackorder)): ?>
                <button type="button"
                        x-data="{ busy: false, added: false }"
                        @click.prevent="
                            if (busy) return;
                            busy = true;
                            fetch('<?php echo e(route('gazu.cart.add')); ?>', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                                body: new URLSearchParams(Object.assign({ product_id: '<?php echo e($productId); ?>', quantity: '1' }, cardWh ? { warehouse_id: cardWh } : {}))
                            }).then(r => r.json()).then(d => {
                                if (d.ok) {
                                    // Drawer показує результат — toast прибрано.
                                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.count, qtyTotal: d.qtyTotal, total: d.total } }));
                                    added = true;
                                    setTimeout(() => added = false, 1500);
                                } else {
                                    window.gazuToast && window.gazuToast(d.message || 'Не вдалося додати', 'error');
                                }
                            }).catch(() => window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error'))
                              .finally(() => { busy = false; });
                        "
                        :class="added ? 'bg-[var(--gazu-success)] scale-[0.97]' : (busy ? 'bg-[var(--gazu-ink)] opacity-80' : 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] active:scale-[0.97]')"
                        :disabled="busy"
                        class="flex-1 min-w-0 py-2.5 text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition-all duration-200">
                    <span x-show="!busy && !added" class="inline-flex items-center gap-1.5">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> <?php echo e($qty > 0 ? 'У кошик' : 'Замовити'); ?>

                    </span>
                    <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span x-show="added" x-cloak x-transition.duration.150ms class="inline-flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        Додано
                    </span>
                </button>
            <?php elseif($qty <= 0): ?>
                <?php if($productId): ?>
                    <?php if (isset($component)) { $__componentOriginal0f1d95fbb7c12db153d47dab51ccdd66 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0f1d95fbb7c12db153d47dab51ccdd66 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock-notify','data' => ['productId' => $productId,'variant' => 'card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock-notify'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['productId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'variant' => 'card']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0f1d95fbb7c12db153d47dab51ccdd66)): ?>
<?php $attributes = $__attributesOriginal0f1d95fbb7c12db153d47dab51ccdd66; ?>
<?php unset($__attributesOriginal0f1d95fbb7c12db153d47dab51ccdd66); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0f1d95fbb7c12db153d47dab51ccdd66)): ?>
<?php $component = $__componentOriginal0f1d95fbb7c12db153d47dab51ccdd66; ?>
<?php unset($__componentOriginal0f1d95fbb7c12db153d47dab51ccdd66); ?>
<?php endif; ?>
                <?php else: ?>
                    <button type="button" disabled class="flex-1 min-w-0 py-2.5 bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] border-0 rounded-md text-[13px] font-medium cursor-not-allowed inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                        Під замовлення
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <a wire:navigate href="<?php echo e($url); ?>" class="flex-1 min-w-0 py-2.5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:bg-[var(--gazu-ink-2)] no-underline">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Деталі
                </a>
            <?php endif; ?>
            <?php if($productId && ($qty > 0 || $allowBackorder)): ?>
                <button type="button"
                        title="Купити в 1 клік"
                        x-data
                        @click.prevent="$dispatch('gazu:one-click', { productId: '<?php echo e($productId); ?>', productName: <?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>, productPrice: <?php echo e((float) $price); ?> })"
                        class="w-8 sm:w-9 shrink-0 border border-[var(--gazu-line)] rounded-md bg-[var(--gazu-surface)] text-[var(--gazu-ink)] hover:border-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                </button>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(is_object($p) && $p instanceof \App\Models\Product): ?>
        <?php if (isset($component)) { $__componentOriginal1b2fae1296e7bf3865968f0b8e554b1a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b2fae1296e7bf3865968f0b8e554b1a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card-stocks','data' => ['p' => $p,'basePrice' => (float) $price,'groupActive' => $isGroupPrice]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card-stocks'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'base-price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((float) $price),'group-active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isGroupPrice)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b2fae1296e7bf3865968f0b8e554b1a)): ?>
<?php $attributes = $__attributesOriginal1b2fae1296e7bf3865968f0b8e554b1a; ?>
<?php unset($__attributesOriginal1b2fae1296e7bf3865968f0b8e554b1a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b2fae1296e7bf3865968f0b8e554b1a)): ?>
<?php $component = $__componentOriginal1b2fae1296e7bf3865968f0b8e554b1a; ?>
<?php unset($__componentOriginal1b2fae1296e7bf3865968f0b8e554b1a); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/product-card.blade.php ENDPATH**/ ?>