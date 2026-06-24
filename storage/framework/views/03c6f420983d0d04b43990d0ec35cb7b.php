<?php $__env->startSection('title', 'Кошик — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Кошик']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Кошик'])]); ?>
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
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-2">Кошик</h1>
    <?php
        $itemsCount = array_sum(array_column($cart, 'quantity'));
        $positionsCount = count($cart);
    ?>
    <div class="text-sm text-[var(--gazu-graphite)] mb-6">
        <?php echo e(plural_uk_count($positionsCount, 'позиція', 'позиції', 'позицій')); ?>

        <?php if($itemsCount !== $positionsCount): ?>
            · <?php echo e($itemsCount); ?> шт.
        <?php endif; ?>
    </div>

    <?php if(session('cart_message')): ?>
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            <?php echo e(session('cart_message')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div role="alert" class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm flex items-start gap-2">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'close','size' => '16','stroke' => 'var(--gazu-danger)','class' => 'shrink-0 mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','size' => '16','stroke' => 'var(--gazu-danger)','class' => 'shrink-0 mt-0.5']); ?>
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
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($msg); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="gazu-grid-cart"
         x-data="{
            total: <?php echo e((float) $cartTotal); ?>,
            count: <?php echo e((int) $positionsCount); ?>,
            qtyTotal: <?php echo e((int) $itemsCount); ?>,
            fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g,' '); },
            flash(refName) {
                const el = this.$refs[refName];
                if (!el) return;
                el.setAttribute('data-changed', '0');
                void el.offsetWidth;
                el.setAttribute('data-changed', '1');
                setTimeout(() => el.setAttribute('data-changed', '0'), 450);
            },
            init() {
                window.addEventListener('cart-updated', (e) => {
                    if (e.detail.total !== undefined) this.total = e.detail.total;
                    if (e.detail.count !== undefined) this.count = e.detail.count;
                    if (e.detail.qtyTotal !== undefined) this.qtyTotal = e.detail.qtyTotal;
                });
                this.$watch('total', () => { this.flash('grandEl'); this.flash('subEl'); });
            }
         }">
        <?php
            // Eager-load all warehouses referenced by the cart in ONE query
            // instead of MerchantWarehouse::find() per cart line (N+1).
            $cartWarehouseIds = collect($cart)->pluck('warehouse_id')->filter()->unique()->all();
            $cartWarehouses = $cartWarehouseIds
                ? \App\Models\MerchantWarehouse::query()->whereIn('id', $cartWarehouseIds)->get()->keyBy('id')
                : collect();
        ?>
        <div>
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                        $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                        $slug = is_array($item['slug'] ?? null) ? ($item['slug']['uk'] ?? null) : ($item['slug'] ?? null);
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['quantity'] ?? 1);
                        $img = $item['image'] ?? null;
                        $warehouseId = (int) ($item['warehouse_id'] ?? 0);
                        $warehouse = $warehouseId ? ($cartWarehouses[$warehouseId] ?? null) : null;
                        $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                        $kind = $kinds[$productId % count($kinds)];
                    ?>
                    <?php
                        $hasRealImg = ! empty($img) && (str_starts_with($img, 'http') || str_starts_with($img, '/storage/') || file_exists(public_path($img)));
                        $imgUrl = $hasRealImg ? (str_starts_with($img, 'http') ? $img : asset('storage/'.ltrim($img, '/storage/'))) : null;
                    ?>
                    <div class="gazu-grid-cart-row <?php echo e($loop->index ? 'border-t border-[var(--gazu-line)]' : ''); ?>"
                         x-data="{
                            qty: <?php echo e($qty); ?>,
                            price: <?php echo e($price); ?>,
                            busy: false,
                            removing: false,
                            get lineTotal() { return this.price * this.qty; },
                            async setQty(newQty) {
                                if (this.busy || newQty < 1) return;
                                const prev = this.qty;
                                this.qty = newQty;
                                this.busy = true;
                                try {
                                    const r = await fetch('<?php echo e(route('gazu.cart.update')); ?>', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                                        body: new URLSearchParams({ product_id: '<?php echo e($productId); ?>', quantity: String(newQty) })
                                    });
                                    const d = await r.json();
                                    if (d.ok) {
                                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                                    } else { this.qty = prev; }
                                } catch (e) {
                                    this.qty = prev;
                                    window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error');
                                } finally { this.busy = false; }
                            },
                            async remove() {
                                if (this.busy) return;
                                this.busy = true; this.removing = true;
                                try {
                                    const r = await fetch('<?php echo e(route('gazu.cart.remove')); ?>', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                                        body: new URLSearchParams({ product_id: '<?php echo e($productId); ?>' })
                                    });
                                    const d = await r.json();
                                    if (d.ok) {
                                        $el.style.transition = 'opacity .25s, max-height .25s, padding .25s';
                                        $el.style.maxHeight = $el.offsetHeight + 'px';
                                        requestAnimationFrame(() => { $el.style.maxHeight = '0'; $el.style.opacity = '0'; $el.style.padding = '0'; });
                                        setTimeout(() => $el.remove(), 250);
                                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                                        window.gazuToast && window.gazuToast('Видалено з кошика', 'info');
                                    }
                                } catch (e) {
                                    this.removing = false;
                                    window.gazuToast && window.gazuToast('Помилка', 'error');
                                } finally { this.busy = false; }
                            }
                         }">
                        <div class="bg-[var(--gazu-paper)] rounded-md aspect-square flex items-center justify-center overflow-hidden">
                            <?php if($imgUrl): ?>
                                <img src="<?php echo e($imgUrl); ?>" alt="" class="w-20 h-20 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling?.style.removeProperty('display');">
                                <?php if (isset($component)) { $__componentOriginalb3ce7faecba1472bd9053bf57696fe20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-placeholder','data' => ['name' => $title,'seed' => $productId,'class' => 'w-20 h-20','style' => 'display:none']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-placeholder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'class' => 'w-20 h-20','style' => 'display:none']); ?>
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
                            <?php else: ?>
                                <?php if (isset($component)) { $__componentOriginalb3ce7faecba1472bd9053bf57696fe20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-placeholder','data' => ['name' => $title,'seed' => $productId,'class' => 'w-20 h-20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-placeholder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'class' => 'w-20 h-20']); ?>
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
                        </div>
                        <div class="min-w-0">
                            <?php if($slug): ?>
                                <a wire:navigate href="<?php echo e(route('gazu.product.show', ['slug' => $slug])); ?>" class="text-[var(--gazu-ink)] no-underline font-medium leading-snug"><?php echo e($title); ?></a>
                            <?php else: ?>
                                <span class="text-[var(--gazu-ink)] font-medium leading-snug"><?php echo e($title); ?></span>
                            <?php endif; ?>
                            <?php if($warehouse): ?>
                                <div class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] text-[var(--gazu-graphite)]">
                                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '12','stroke' => 'var(--gazu-blue)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '12','stroke' => 'var(--gazu-blue)']); ?>
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
                                    <span><?php echo e($warehouse->city ?: $warehouse->name); ?></span>
                                    <?php if($warehouse->delivery_eta): ?>
                                        <span class="text-[var(--gazu-muted)]">·</span>
                                        <span><?php echo e($warehouse->delivery_eta); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center border border-[var(--gazu-line)] rounded-md" role="group" aria-label="Кількість">
                            <button type="button" @click="setQty(qty - 1)" :disabled="busy || qty <= 1"
                                    aria-label="Зменшити"
                                    class="w-11 h-11 md:w-9 md:h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'minus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','size' => '14']); ?>
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
                            <span class="w-10 text-center py-2 text-sm gazu-mono font-medium" :class="busy && 'opacity-50'" x-text="qty" aria-live="polite"><?php echo e($qty); ?></span>
                            <button type="button" @click="setQty(qty + 1)" :disabled="busy"
                                    aria-label="Збільшити"
                                    class="w-11 h-11 md:w-9 md:h-9 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer flex items-center justify-center disabled:opacity-40">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '14']); ?>
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
                        
                        <div class="text-right md:flex md:items-baseline md:justify-end md:gap-2.5">
                            <div class="text-[11px] text-[var(--gazu-graphite)] md:order-1 whitespace-nowrap">
                                <span x-text="fmt(price)"><?php echo e(number_format($price, 0, '.', ' ')); ?></span> ₴ × <span x-text="qty"><?php echo e($qty); ?></span> шт.
                            </div>
                            <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)] gazu-count-up md:order-2 whitespace-nowrap" x-text="fmt(lineTotal) + ' ₴'"><?php echo e(number_format($price * $qty, 0, '.', ' ')); ?> ₴</div>
                        </div>
                        <button type="button" @click="remove()" :disabled="busy"
                                aria-label="Видалити з кошика"
                                class="w-11 h-11 md:w-9 md:h-9 bg-transparent text-[var(--gazu-graphite)] border-0 cursor-pointer flex items-center justify-center hover:text-[var(--gazu-danger)] disabled:opacity-40">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'trash','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','size' => '18']); ?>
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
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">← Продовжити покупки</a>
                <span class="flex-1"></span>
                <form action="<?php echo e(route('gazu.cart.clear')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-[13px] text-[var(--gazu-danger)] bg-transparent border-0 cursor-pointer">Очистити кошик</button>
                </form>
            </div>
        </div>

        <?php
            $shipping = app(\App\Services\Cart\ShippingCalculator::class)->breakdown($cart);
        ?>
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 self-start">
            <h3 class="gazu-display text-xl font-semibold m-0 mb-4">Підсумок</h3>
            <div class="flex justify-between mb-2 text-sm">
                <span class="text-[var(--gazu-graphite)]" x-text="count + ' ' + (count === 1 ? 'позиція' : (count >= 2 && count <= 4 ? 'позиції' : 'позицій'))"><?php echo e(plural_uk_count($positionsCount, 'позиція', 'позиції', 'позицій')); ?></span>
                <span x-ref="subEl" class="text-[var(--gazu-ink)] gazu-count-up" x-text="fmt(total) + ' ₴'"><?php echo e(number_format($shipping['subtotal'], 0, '.', ' ')); ?> ₴</span>
            </div>

            
            <?php if(count($shipping['groups']) > 0): ?>
                <div class="my-3 pt-3 border-t border-[var(--gazu-line)]">
                    <div class="text-[11px] uppercase tracking-wide text-[var(--gazu-graphite)] mb-2 font-bold">Доставка</div>
                    <?php $__currentLoopData = $shipping['groups']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $w = $g['warehouse'];
                            $whName = $w?->city ?: ($w?->name ?: 'Склад');
                        ?>
                        <div class="flex justify-between items-baseline mb-1.5 text-xs">
                            <span class="text-[var(--gazu-graphite)] inline-flex items-center gap-1">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '11','stroke' => 'var(--gazu-blue)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '11','stroke' => 'var(--gazu-blue)']); ?>
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
                                <span><?php echo e($whName); ?></span>
                            </span>
                            <span class="gazu-mono">
                                <?php if($g['free']): ?>
                                    <span class="text-[var(--gazu-success)]">безкоштовно</span>
                                <?php else: ?>
                                    <?php echo e(number_format($g['shipping'], 0, '.', ' ')); ?> ₴
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if(! $g['free'] && $w?->free_shipping_threshold): ?>
                            <?php $remaining = (float) $w->free_shipping_threshold - $g['subtotal']; ?>
                            <?php if($remaining > 0): ?>
                                <div class="text-[10px] text-[var(--gazu-muted)] mb-1.5 ml-4">
                                    + <?php echo e(number_format($remaining, 0, '.', ' ')); ?> ₴ до безкоштовної
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between text-sm pt-2 mt-1 border-t border-[var(--gazu-line)]">
                        <span class="text-[var(--gazu-graphite)]">Разом доставка</span>
                        <span class="font-medium gazu-mono">
                            <?php if($shipping['shipping_total'] == 0): ?>
                                <span class="text-[var(--gazu-success)]">безкоштовно</span>
                            <?php else: ?>
                                <?php echo e(number_format($shipping['shipping_total'], 0, '.', ' ')); ?> ₴
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php $freeShipThreshold = 1000; ?>
            <div class="my-3 pt-3 border-t border-[var(--gazu-line)]"
                 x-data="{ threshold: <?php echo e($freeShipThreshold); ?>, get pct(){ return Math.min(100, Math.round(total / this.threshold * 100)) }, get remaining(){ return Math.max(0, this.threshold - total) } }">
                <template x-if="remaining > 0">
                    <div>
                        <div class="text-[11px] text-[var(--gazu-graphite)] mb-1.5">
                            До безкоштовної доставки ще <span class="font-semibold text-[var(--gazu-ink)] gazu-mono" x-text="fmt(remaining) + ' ₴'"></span>
                        </div>
                        <div class="w-full h-2 bg-[var(--gazu-mist)] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[var(--gazu-blue)] to-[var(--gazu-success)] transition-all duration-500 ease-out" :style="`width: ${pct}%`"></div>
                        </div>
                    </div>
                </template>
                <template x-if="remaining === 0">
                    <div class="text-[11px] text-[var(--gazu-success)] font-medium inline-flex items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                        Безкоштовна доставка
                    </div>
                </template>
            </div>

            
            <?php if(module('coupons')->enabled()): ?>
            <div class="my-3 pt-3 border-t border-[var(--gazu-line)]"
                 x-data="{
                    open: false, code: '', busy: false, applied: null,
                    apply() {
                        if (!this.code.trim() || this.busy) return;
                        this.busy = true;
                        fetch('<?php echo e(route('gazu.cart.coupon.apply')); ?>', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                            body: new URLSearchParams({ code: this.code.trim() })
                        }).then(r => r.json()).then(d => {
                            if (d.ok) {
                                this.applied = { code: this.code, discount: d.discount };
                                window.gazuToast && window.gazuToast(d.message || 'Промокод застосовано', 'success');
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: d }));
                            } else {
                                window.gazuToast && window.gazuToast(d.message || 'Промокод не знайдено', 'error');
                            }
                        }).catch(() => window.gazuToast && window.gazuToast('Помилка', 'error'))
                          .finally(() => { this.busy = false; });
                    }
                 }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-sm text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0" :aria-expanded="open">
                    <span class="inline-flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        Промокод
                    </span>
                    <svg :class="open ? 'rotate-180' : ''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open" x-cloak x-collapse class="mt-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="code" @keydown.enter.prevent="apply()" placeholder="Введіть код" class="flex-1 px-3 py-2 border border-[var(--gazu-line)] rounded-md text-sm focus:border-[var(--gazu-ink)] outline-none">
                        <button type="button" @click="apply()" :disabled="busy || !code.trim()" class="px-4 py-2 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[var(--gazu-ink-2)] transition-colors">
                            <span x-show="!busy">Застосувати</span>
                            <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <template x-if="applied">
                        <div class="mt-2 text-[12px] text-[var(--gazu-success)] inline-flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            <span x-text="'Знижка −' + fmt(applied.discount) + ' ₴'"></span>
                        </div>
                    </template>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex justify-between items-baseline mb-4 pt-3 border-t border-[var(--gazu-line)]">
                <span class="text-[var(--gazu-ink)] font-medium">До сплати</span>
                <span x-ref="grandEl" x-text="fmt(total + (<?php echo e((int) $shipping['shipping_total']); ?>)) + ' ₴'" class="gazu-display text-2xl font-bold text-[var(--gazu-ink)] gazu-count-up"><?php echo e(number_format($shipping['grand_total'], 0, '.', ' ')); ?> ₴</span>
            </div>
            <?php
                $ccFreeShip = \App\Support\Checkout\CheckoutConfig::freeShippingThreshold();
                $ccMinOrder = \App\Support\Checkout\CheckoutConfig::minOrderAmount();
                $ccTotal = (float) $cartTotal;
                $ccBelowMin = $ccMinOrder > 0 && $ccTotal < $ccMinOrder;
            ?>

            <?php if($ccFreeShip > 0): ?>
                <?php if($ccTotal >= $ccFreeShip): ?>
                    <div class="flex items-center gap-2 mb-3 text-sm text-[var(--gazu-success)] font-medium">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'truck','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Безкоштовна доставка застосована
                    </div>
                <?php else: ?>
                    <?php $ccPct = max(0, min(100, (int) round($ccTotal / $ccFreeShip * 100))); ?>
                    <div class="mb-3">
                        <div class="text-xs text-[var(--gazu-graphite)] mb-1.5">
                            Додайте на <span class="font-medium text-[var(--gazu-ink)]"><?php echo e(number_format($ccFreeShip - $ccTotal, 0, '.', ' ')); ?> ₴</span> — і доставка безкоштовна
                        </div>
                        <div class="h-2 rounded-full bg-[var(--gazu-line)] overflow-hidden">
                            <div class="h-full rounded-full bg-[var(--gazu-success)] transition-all" style="width: <?php echo e($ccPct); ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if($ccBelowMin): ?>
                <div class="p-3 rounded-md text-sm text-center mb-3 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)]">
                    Мінімальна сума замовлення — <?php echo e(number_format($ccMinOrder, 0, '.', ' ')); ?> ₴.
                    Додайте товарів ще на <?php echo e(number_format($ccMinOrder - $ccTotal, 0, '.', ' ')); ?> ₴.
                </div>
                <span class="gazu-btn-primary w-full no-underline opacity-50 cursor-not-allowed flex items-center justify-center" aria-disabled="true">Оформити замовлення →</span>
            <?php else: ?>
                <a wire:navigate href="<?php echo e(route('gazu.checkout')); ?>" class="gazu-btn-primary w-full no-underline">Оформити замовлення →</a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(! empty($recommended ?? []) && count($recommended) > 0): ?>
        <section class="mt-10">
            <h2 class="gazu-display text-2xl font-semibold mb-4">Часто купують разом</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 gazu-stagger">
                <?php $__currentLoopData = $recommended; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $attributes = $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $component = $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/cart/index.blade.php ENDPATH**/ ?>