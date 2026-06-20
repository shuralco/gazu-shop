
<?php
    $rpModule = module('related_products');
    $pickerEnabled = (bool) ($rpModule->setting('picker_enabled') ?? true);
    $maxVariants = (int) ($rpModule->setting('max_variants_displayed') ?? 50);

    // Конфіг характеристик зі сторінки «Зв'язки товарів» (HPM-стиль):
    // spec_key => [label, display(button|dropdown|image|image_button), show].
    // Порожній конфіг = легасі-поведінка (всі відмінні характеристики, pills).
    $pickerCfg = [];
    foreach ((array) ($rpModule->setting('picker_characteristics') ?? []) as $row) {
        if (! empty($row['spec_key'])) {
            $pickerCfg[(string) $row['spec_key']] = [
                'label' => trim((string) ($row['label'] ?? '')) ?: (string) $row['spec_key'],
                'display' => in_array($row['display'] ?? 'button', ['button', 'dropdown', 'image', 'image_button'], true) ? $row['display'] : 'button',
                'show' => array_key_exists('show', $row) ? (bool) $row['show'] : true,
            ];
        }
    }

    $currentSpecs = is_array($p->specifications) ? $p->specifications : (json_decode((string) $p->specifications, true) ?: []);
    $variantGroups = [];

    if ($pickerEnabled) {
        $variants = $p->relatedProducts()
            ->where('related_products.type', 'related')
            ->limit($maxVariants)
            ->get(['products.id', 'products.title', 'products.slug', 'products.price', 'products.specifications', 'products.image']);

        $imgKind = is_object($p) ? ($p->image_kind ?? 'filter') : 'filter';
        foreach ($variants as $v) {
            $vs = is_array($v->specifications) ? $v->specifications : (json_decode((string) $v->specifications, true) ?: []);
            if (! is_array($vs)) continue;
            foreach ($currentSpecs as $k => $curVal) {
                if (! isset($vs[$k])) continue;
                if ($pickerCfg && ! isset($pickerCfg[$k])) continue; // характеристика не вибрана в налаштуваннях
                if ((string) $vs[$k] !== (string) $curVal) {
                    $vTitle = is_array($v->title) ? ($v->title['uk'] ?? '') : (string) $v->title;
                    $variantGroups[$k][] = [
                        'id' => $v->id,
                        'value' => (string) $vs[$k],
                        'slug' => is_array($v->slug) ? ($v->slug['uk'] ?? '') : (string) $v->slug,
                        'price' => $v->price,
                        'image' => \App\Support\PartImage::resolve(explicit: $v->image, kind: $imgKind, seed: $v->id, title: $vTitle),
                    ];
                    break;
                }
            }
        }
        foreach ($variantGroups as $k => $list) {
            $seen = [];
            $variantGroups[$k] = array_values(array_filter($list, function ($v) use (&$seen) {
                if (isset($seen[$v['value']])) return false;
                $seen[$v['value']] = true;
                return true;
            }));
        }

        // Прибираємо приховані та сортуємо за порядком конфігу.
        if ($pickerCfg) {
            $variantGroups = array_filter($variantGroups, fn ($_, $k) => $pickerCfg[$k]['show'] ?? true, ARRAY_FILTER_USE_BOTH);
            $order = array_flip(array_keys($pickerCfg));
            uksort($variantGroups, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));
        }
    }
?>
<?php if(! empty($variantGroups)): ?>
    <section class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 sm:p-5 mt-4 mb-4"
             x-data="{
                activeId: <?php echo e((int) $p->id); ?>,
                switching: false,
                async switchTo(id, slug) {
                    if (this.switching || id === this.activeId) return;
                    this.switching = true;
                    try {
                        const res = await fetch(`/api/products/${id}/snapshot`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('http '+res.status);
                        const data = await res.json();
                        this.activeId = id;
                        window.dispatchEvent(new CustomEvent('gazu:variant-switched', { detail: data }));
                        if (slug) {
                            history.replaceState({ ...history.state, productId: id }, '', '/'+slug);
                        }
                    } catch (e) {
                        console.warn('[variants] fetch failed', e);
                        if (slug) window.location.href = '/'+slug;
                    } finally {
                        this.switching = false;
                    }
                }
             }">
        <?php $__currentLoopData = $variantGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specKey => $options): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $label = $pickerCfg[$specKey]['label'] ?? $specKey;
                $display = $pickerCfg[$specKey]['display'] ?? 'button';
                $currentValue = (string) ($currentSpecs[$specKey] ?? '');
                $curSlug = is_array($p->slug) ? ($p->slug['uk'] ?? '') : $p->slug;
            ?>
            <div class="flex flex-wrap items-center gap-2 mb-3 last:mb-0">
                <span class="text-sm text-[var(--gazu-graphite)] mr-2"><?php echo e($label); ?>:</span>

                <?php if($display === 'dropdown'): ?>
                    
                    <select class="text-sm border border-[var(--gazu-line)] rounded-md px-2.5 py-1.5 bg-white text-[var(--gazu-ink)] cursor-pointer focus:outline-none focus:border-[var(--gazu-ink)]"
                            :disabled="switching"
                            @change="const [id, slug] = $event.target.value.split('|'); switchTo(parseInt(id), slug)">
                        <?php if($currentValue !== ''): ?>
                            <option value="<?php echo e((int) $p->id); ?>|<?php echo e($curSlug); ?>" selected><?php echo e($currentValue); ?></option>
                        <?php endif; ?>
                        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e((int) $opt['id']); ?>|<?php echo e($opt['slug']); ?>"><?php echo e($opt['value']); ?> — <?php echo e(number_format($opt['price'], 0, '.', ' ')); ?> ₴</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                <?php elseif($display === 'image' || $display === 'image_button'): ?>
                    
                    <?php if($currentValue !== ''): ?>
                        <button type="button"
                                @click="switchTo(<?php echo e((int) $p->id); ?>, '<?php echo e($curSlug); ?>')"
                                :class="activeId === <?php echo e((int) $p->id); ?> ? 'ring-2 ring-[var(--gazu-ink)]' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)]'"
                                class="flex flex-col items-center gap-1 p-1.5 rounded-md bg-white transition-all"
                                title="<?php echo e($currentValue); ?>">
                            <img src="<?php echo e(\App\Support\PartImage::resolve(explicit: $p->image ?? null, kind: $imgKind, seed: (int) $p->id, title: (string) ($p->name ?? ''))); ?>" alt="<?php echo e($currentValue); ?>" class="w-12 h-12 object-contain rounded" loading="lazy">
                            <?php if($display === 'image_button'): ?><span class="text-xs font-medium text-[var(--gazu-ink)] max-w-[72px] truncate"><?php echo e($currentValue); ?></span><?php endif; ?>
                        </button>
                    <?php endif; ?>
                    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                @click="switchTo(<?php echo e((int) $opt['id']); ?>, '<?php echo e($opt['slug']); ?>')"
                                :disabled="switching"
                                :class="activeId === <?php echo e((int) $opt['id']); ?> ? 'ring-2 ring-[var(--gazu-ink)]' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)]'"
                                class="flex flex-col items-center gap-1 p-1.5 rounded-md bg-white transition-all disabled:opacity-50 disabled:cursor-wait"
                                title="<?php echo e($opt['value']); ?> — <?php echo e(number_format($opt['price'], 0, '.', ' ')); ?> ₴">
                            <img src="<?php echo e($opt['image']); ?>" alt="<?php echo e($opt['value']); ?>" class="w-12 h-12 object-contain rounded" loading="lazy">
                            <?php if($display === 'image_button'): ?><span class="text-xs text-[var(--gazu-ink)] max-w-[72px] truncate"><?php echo e($opt['value']); ?></span><?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php else: ?>
                    
                    <?php if($currentValue !== ''): ?>
                        <button type="button"
                                @click="switchTo(<?php echo e((int) $p->id); ?>, '<?php echo e($curSlug); ?>')"
                                :class="activeId === <?php echo e((int) $p->id); ?> ? 'bg-[var(--gazu-ink)] text-white ring-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)]'"
                                class="inline-flex items-center px-2.5 py-1 text-sm font-medium rounded-md ring-1 transition-colors">
                            <?php echo e($currentValue); ?>

                        </button>
                    <?php endif; ?>
                    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                @click="switchTo(<?php echo e((int) $opt['id']); ?>, '<?php echo e($opt['slug']); ?>')"
                                :disabled="switching"
                                :class="activeId === <?php echo e((int) $opt['id']); ?> ? 'bg-[var(--gazu-ink)] text-white ring-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] hover:bg-[var(--gazu-paper)]'"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-sm rounded-md ring-1 transition-colors disabled:opacity-50 disabled:cursor-wait">
                            <span><?php echo e($opt['value']); ?></span>
                            <span class="text-xs opacity-70"><?php echo e(number_format($opt['price'], 0, '.', ' ')); ?> ₴</span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    
    <script>
    (function () {
        if (window.__gazuVariantSwapBound) return;
        window.__gazuVariantSwapBound = true;
        window.addEventListener('gazu:variant-switched', (e) => {
            const d = e.detail || {};
            document.querySelectorAll('[data-gazu-product-title]').forEach(el => el.textContent = d.title);
            const h1 = document.querySelector('h1');
            if (h1) h1.textContent = d.title;
            const priceFmt = (v) => new Intl.NumberFormat('uk-UA', { maximumFractionDigits: 0 }).format(v) + ' ₴';
            document.querySelectorAll('[data-gazu-product-price]').forEach(el => el.textContent = priceFmt(d.price));
            document.querySelectorAll('[data-gazu-product-image]').forEach(el => {
                if (el.tagName === 'IMG') {
                    el.style.opacity = '0';
                    el.style.display = 'block';
                    el.src = d.image;
                } else {
                    el.style.backgroundImage = `url(${d.image})`;
                }
            });
            document.querySelectorAll('[data-gazu-product-id]').forEach(el => {
                el.dataset.gazuProductId = String(d.id);
                if (el.tagName === 'INPUT') el.value = String(d.id);
                if (el.dataset.productId !== undefined) el.dataset.productId = String(d.id);
                if (el.dataset.wishlistPid !== undefined) el.dataset.wishlistPid = String(d.id);
            });
            document.title = d.title + ' — GAZU';
            document.querySelectorAll('[data-gazu-product-sku]').forEach(el => el.textContent = d.sku || '');
            document.querySelectorAll('[data-gazu-product-stock]').forEach(el => {
                el.textContent = d.in_stock ? `${d.qty} в наявності` : 'Немає в наявності';
                el.classList.toggle('text-[var(--gazu-success)]', d.in_stock);
                el.classList.toggle('text-[var(--gazu-danger)]', !d.in_stock);
            });
        });
    })();
    </script>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/modules/related_products/resources/views/variant-picker.blade.php ENDPATH**/ ?>