<?php
    // SEO: персональні meta_title/meta_description товару (SEO-таб адмінки)
    // мають пріоритет; інакше — базовий шаблон таксономії «Товари».
    $seoProductVars = [
        'name' => is_object($p) ? ($p->name ?? 'Товар') : 'Товар',
        'price' => number_format((float) (is_object($p) ? ($p->price ?? 0) : 0), 0, '.', ' '),
        'sku' => is_object($p) ? ($p->sku ?? ($p->oem ?? '')) : '',
        'brand' => is_object($p) ? ($p->brand ?? '') : '',
        'category' => (is_object($p) && isset($p->category) && is_object($p->category))
            ? (is_array($p->category->title) ? ($p->category->title['uk'] ?? '') : (string) ($p->category->title ?? ''))
            : '',
        'excerpt' => is_object($p) ? \Illuminate\Support\Str::limit(strip_tags((string) ($p->excerpt ?? '')), 100, '') : '',
    ];
    $seoMetaTitle = is_object($p) ? trim((string) ($p->meta_title ?? '')) : '';
    $seoMetaDescription = is_object($p) ? trim((string) ($p->meta_description ?? '')) : '';
?>
<?php $__env->startSection('title', $seoMetaTitle !== '' ? $seoMetaTitle : \App\Support\SeoTemplates::title('product', $seoProductVars)); ?>
<?php $__env->startSection('description', $seoMetaDescription !== '' ? $seoMetaDescription : \App\Support\SeoTemplates::description('product', $seoProductVars)); ?>
<?php $__env->startSection('og_type', 'product'); ?>

<?php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);
    // Статус наявності з довідника StockStatus (key) — перекриває qty-логіку.
    $stockStatus = is_object($p) ? ($p->stock_status ?? null) : ($p['stock_status'] ?? null);
    $stockStatusModel = $stockStatus ? \App\Models\StockStatus::byKey($stockStatus) : null;
    $availabilitySchema = $stockStatusModel?->availability
        ?? ($qty > 0 ? 'InStock' : 'OutOfStock');
    $rating = is_object($p) ? (float)($p->rating ?? 0) : (float)($p['rating'] ?? 0);
    $reviews = is_object($p) ? (int)($p->reviews ?? 0) : (int)($p['reviews'] ?? 0);
    $condition = is_object($p) ? ($p->condition ?? 'Новий') : ($p['condition'] ?? 'Новий');
    $fits = is_object($p) ? ($p->fits ?? null) : ($p['fits'] ?? null);

    // Характеристики: спершу з ТАКСОНОМІЇ (призначені фільтри товару, згруповані за
    // FilterGroup: Виробник, Положення, Тип, В'язкість…) — підтягуються авто з
    // pivot filter_products. Потім доповнюємо free-form Product->specifications.
    $specs = [];
    $monoRe = '/^\d|[\.,×]|^[A-Z]\d/'; // мономо для кодів/розмірів
    if (is_object($p) && method_exists($p, 'relationLoaded') && $p->relationLoaded('filters')) {
        $byGroup = [];
        foreach ($p->filters as $f) {
            $label = optional($f->filterGroup)->title ?: optional($f->filterGroup)->name;
            $val = $f->name ?: $f->title;
            if ($label && $val) {
                $byGroup[$label][] = (string) $val;
            }
        }
        foreach ($byGroup as $label => $vals) {
            $v = implode(', ', array_values(array_unique($vals)));
            $specs[] = [(string) $label, $v, (bool) preg_match($monoRe, $v)];
        }
    }
    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs)) {
        $have = array_map(fn ($s) => mb_strtolower($s[0]), $specs);
        foreach ($rawSpecs as $k => $v) {
            if (in_array(mb_strtolower((string) $k), $have, true)) {
                continue;
            }
            $specs[] = [(string) $k, (string) $v, (bool) preg_match($monoRe, (string) $v)];
        }
    }
    // Крос-коди (OEM + додаткові коди аналогів).
    $crossCode = is_object($p) ? ($p->cross_code ?? null) : ($p['cross_code'] ?? null);
    if ($crossCode) {
        $specs[] = ['Крос-код (OEM)', (string) $crossCode, true];
    }
    $extraCodes = is_object($p) ? ($p->extra_codes ?? null) : ($p['extra_codes'] ?? null);
    if (is_array($extraCodes) && ! empty($extraCodes)) {
        $specs[] = ['Інші коди', implode(', ', array_map('strval', $extraCodes)), true];
    }
    if (empty($specs)) {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', $condition, false],
            ['Гарантія', $gazuSettings['gazu_default_warranty'] ?? '12 місяців', false],
        ];
    }

    // Compatibility — SINGLE SOURCE OF TRUTH: pivot product_compatibility
    // (та сама data що використовує car-selector filter та apiCompatCheck).
    // Fallback на JSON column products.compatibility для legacy records.
    $compat = [];
    if (is_object($p) && method_exists($p, 'compatibleEngines')) {
        try {
            $engines = $p->compatibleEngines()
                ->with(['model.make'])
                ->limit(100)
                ->get();
            foreach ($engines as $eng) {
                $makeModel = $eng->model->make ?? null;
                $makeName  = $makeModel->name ?? '—';
                $makeLogo  = $makeModel?->logo_url;
                $modelName = $eng->model->name ?? '—';
                $years = '';
                if (! empty($eng->model->year_from) || ! empty($eng->model->year_to)) {
                    $years = (string) ($eng->model->year_from ?? '') . '–' . (string) ($eng->model->year_to ?? '');
                    $years = trim($years, '–') ?: '—';
                }
                $engineLabel = trim(($eng->label ?? '') . ' ' . ($eng->code ?? ''));
                $compat[] = [$makeName, $modelName, $years ?: '—', $engineLabel ?: '—', $makeLogo];
            }
        } catch (\Throwable $e) { /* relation might not exist on mock $p */ }
    }
    // Fallback: legacy JSON column для старих products без pivot rows.
    if (empty($compat)) {
        $rawCompat = is_object($p) ? ($p->compatibility ?? null) : ($p['compatibility'] ?? null);
        if (is_array($rawCompat) && ! empty($rawCompat)) {
            foreach ($rawCompat as $row) {
                if (is_array($row)) {
                    $compat[] = [$row['make'] ?? '—', $row['model'] ?? '—', $row['years'] ?? '—', $row['engine'] ?? '—', null];
                }
            }
        }
    }
?>

<?php
    // Schema.org Product — enriched для rich snippets у Google.
    // Включає: mpn, image, itemCondition, priceValidUntil, hasMerchantReturnPolicy,
    // shippingDetails. Це дає Google показувати ціну/наявність/рейтинг прямо у SERP.
    $conditionMap = [
        'new'         => 'https://schema.org/NewCondition',
        'Новий'       => 'https://schema.org/NewCondition',
        'used'        => 'https://schema.org/UsedCondition',
        'Б/у'         => 'https://schema.org/UsedCondition',
        'refurbished' => 'https://schema.org/RefurbishedCondition',
        'Відновлений' => 'https://schema.org/RefurbishedCondition',
    ];
    $productImageUrl = is_object($p) ? ($p->image ?? null) : null;
    if ($productImageUrl && ! \Illuminate\Support\Str::startsWith($productImageUrl, ['http://','https://'])) {
        $productImageUrl = url('/storage/'.$productImageUrl);
    }
    // Fallback на part-image webp pool (same algorithm як у product card).
    if (! $productImageUrl) {
        $kindForJsonLd = is_object($p) ? ($p->image_kind ?? 'filter') : 'filter';
        $poolDir = public_path("img/parts/{$kindForJsonLd}");
        $poolFiles = is_dir($poolDir) ? glob($poolDir.'/*.webp') : [];
        sort($poolFiles);
        if (! empty($poolFiles)) {
            $seedForLd = is_object($p) ? (int) ($p->id ?? 0) : 0;
            $productImageUrl = url("/img/parts/{$kindForJsonLd}/".basename($poolFiles[abs($seedForLd) % count($poolFiles)]));
        }
    }

    $jsonldProduct = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'sku' => (string) $oem,
        'mpn' => (string) $oem,
        'description' => $fits ?: $name,
        'image' => $productImageUrl ?: url('/og-default.png'),
        'url' => url()->current(),
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => 'UAH',
            'availability' => 'https://schema.org/'.$availabilitySchema,
            'itemCondition' => $conditionMap[$condition ?? 'new'] ?? 'https://schema.org/NewCondition',
            'url' => url()->current(),
            'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            'seller' => [
                '@type' => 'Organization',
                'name' => 'GAZU',
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'UA',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 14,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn',
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'UA'],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    'businessDays' => ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['https://schema.org/Monday','https://schema.org/Tuesday','https://schema.org/Wednesday','https://schema.org/Thursday','https://schema.org/Friday','https://schema.org/Saturday']],
                    'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                    'transitTime'  => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 3, 'unitCode' => 'DAY'],
                ],
            ],
        ],
    ];
    if (! empty($brand)) {
        $jsonldProduct['brand'] = ['@type' => 'Brand', 'name' => $brand];
    }
    // SEO мікророзмітка з aggregateRating — тільки якщо reviews модуль УВімкнено
    if (module('reviews')->enabled() && $rating > 0 && $reviews > 0) {
        $jsonldProduct['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $rating,
            'reviewCount' => $reviews,
            'bestRating' => '5',
            'worstRating' => '1',
        ];
    }
?>

<?php $__env->startSection('jsonld'); ?>
<script type="application/ld+json"><?php echo json_encode($jsonldProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php $__env->stopSection(); ?>


<?php if(! empty($productImageUrl)): ?>
    <?php $__env->startSection('og_image'); ?><?php echo e($productImageUrl); ?><?php $__env->stopSection(); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
    <div class="gazu-container">
        <?php echo $__env->make('gazu.partials.product-breadcrumbs', compact('p', 'brand', 'oem', 'name'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php
            // Brand link + article are passed down to the central column's
            // <x-gazu.warehouse-selector> — no longer rendered in this header.
            $brandHeaderSlug = null;
            if (is_object($p) && method_exists($p, 'relationLoaded') && $p->relationLoaded('brand') && ($b = $p->getRelation('brand'))) {
                $brandHeaderSlug = $b->slug ?: \Illuminate\Support\Str::slug((string) $b->getRawOriginal('name'));
            }
            if (! $brandHeaderSlug && is_object($p) && ($p->manufacturer ?? null)) {
                $brandHeaderSlug = \Illuminate\Support\Str::slug((string) $p->manufacturer);
            }
            // SEO-friendly: /brand/{slug} (brand profile) замість /catalog filter.
            $brandUrl = $brandHeaderSlug ? route('gazu.brand', ['slug' => $brandHeaderSlug]) : null;
            $oemReal = $oem ?: (is_object($p) ? ($p->sku ?? '') : '');
            $soldCount = is_object($p) ? (int) ($p->sold_count ?? 0) : 0;
            // Etap 51: $productId був визначений нижче (line 480) — підняли наверх
            // для heart button у gallery section (line ~130). Інакше Undefined variable.
            $productId = is_object($p) ? ($p->id ?? null) : null;
        ?>
        
        <div class="gazu-grid-product-main mt-1">
            
            <?php
                $gallerySeed = is_object($p) ? (int) ($p->id ?? 0) : 0;
                $variants = [
                    $gallerySeed,
                    $gallerySeed + 1001,
                    $gallerySeed + 2002,
                    $gallerySeed + 3003,
                ];
            ?>
            <div class="flex flex-col gap-3" x-data="{ idx: 0, zoom: false }" @keydown.escape.window="zoom = false">
                <div class="aspect-square bg-[var(--gazu-surface)] rounded-lg relative overflow-hidden cursor-zoom-in group/main"
                     @click="zoom = true" title="Натисніть щоб збільшити">
                    <div class="absolute inset-0 gazu-grid-pattern"></div>
                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $seed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="absolute inset-0 transition-opacity duration-200"
                             :class="idx === <?php echo e($i); ?> ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','seed' => $seed,'fit' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seed),'fit' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <img data-gazu-product-image
                         alt=""
                         style="display:none; opacity:0; transition: opacity .2s ease;"
                         class="absolute inset-0 w-full h-full object-contain bg-[var(--gazu-surface)] z-[3]"
                         onload="this.style.opacity='1';"
                         onerror="this.style.display='none';"/>
                    <div class="absolute top-3.5 left-3.5 px-2.5 py-1.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] gazu-mono text-[11px] text-[var(--gazu-ink)] tracking-wider rounded z-[1]">
                        <span x-text="idx + 1">1</span> / <?php echo e(count($variants)); ?>

                    </div>
                    
                    <div class="absolute bottom-3.5 left-3.5 w-9 h-9 rounded-lg bg-[var(--gazu-surface)]/90 backdrop-blur border border-[var(--gazu-line)] inline-flex items-center justify-center text-[var(--gazu-ink)] opacity-0 group-hover/main:opacity-100 transition-opacity z-[1] pointer-events-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                    <?php if($productId): ?>
                        
                        <button type="button"
                                data-wishlist-pid="<?php echo e($productId); ?>"
                                x-data="{ active: false, busy: false }"
                                x-init="if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has(<?php echo e((int) $productId); ?>)) active = true;
                                        window.addEventListener('gazu:wishlist-ids-loaded', () => { if (window.GAZU_WISHLIST_IDS && window.GAZU_WISHLIST_IDS.has(<?php echo e((int) $productId); ?>)) active = true; });"
                                @click.prevent.stop="
                                    if (busy) return; busy = true;
                                    Promise.resolve(window.gazuWishlistToggle(<?php echo e((int) $productId); ?>)).then(inWl => { active = inWl; }).finally(() => busy = false);"
                                :title="active ? 'Прибрати з обраного' : 'Додати в обране'"
                                :class="active ? 'text-[var(--gazu-danger)] border-[var(--gazu-danger)]' : 'text-[var(--gazu-graphite)] border-[var(--gazu-line)] hover:text-[var(--gazu-danger)]'"
                                class="absolute top-3.5 right-3.5 w-9 h-9 border bg-[var(--gazu-surface)] rounded-lg cursor-pointer inline-flex items-center justify-center transition-colors z-[2]">
                            <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" :fill="active ? 'currentColor' : 'none'">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
                            </svg>
                        </button>
                    <?php endif; ?>
                </div>

                
                <div x-show="zoom" x-cloak x-transition.opacity
                     class="fixed inset-0 z-[90] flex items-center justify-center p-4 sm:p-8"
                     style="background: rgba(14,27,44,0.92);"
                     @click.self="zoom = false">
                    <button type="button" @click="zoom = false" aria-label="Закрити"
                            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                    <button type="button"
                            @click.stop="idx = (idx - 1 + <?php echo e(count($variants)); ?>) % <?php echo e(count($variants)); ?>"
                            aria-label="Попереднє"
                            class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button type="button"
                            @click.stop="idx = (idx + 1) % <?php echo e(count($variants)); ?>"
                            aria-label="Наступне"
                            class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border-0 cursor-pointer inline-flex items-center justify-center transition-colors z-[1]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <div class="relative w-full max-w-[90vw] max-h-[85vh] aspect-square bg-[var(--gazu-surface)] rounded-2xl overflow-hidden flex items-center justify-center" @click.stop>
                        <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $seed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="absolute inset-0 flex items-center justify-center p-8 transition-opacity"
                                 :class="idx === <?php echo e($i); ?> ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                                <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','seed' => $seed,'fit' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seed),'fit' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 px-3 py-1.5 bg-black/70 text-white gazu-mono text-[12px] rounded">
                            <span x-text="idx + 1">1</span> / <?php echo e(count($variants)); ?>

                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-2">
                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $seed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                @click="idx = <?php echo e($i); ?>" @mouseover="idx = <?php echo e($i); ?>"
                                :class="idx === <?php echo e($i); ?> ? 'ring-2 ring-[var(--gazu-blue)] ring-offset-1' : 'opacity-80 hover:opacity-100'"
                                class="aspect-square bg-[var(--gazu-paper)] rounded-md overflow-hidden cursor-pointer transition-all">
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','seed' => $seed,'fit' => true,'class' => 'w-full h-full object-cover']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($seed),'fit' => true,'class' => 'w-full h-full object-cover']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div>
                <h1 data-gazu-product-title class="gazu-display text-[28px] sm:text-[32px] font-semibold text-[var(--gazu-ink)] m-0 leading-tight"><?php echo e($name); ?></h1>
                <?php
                    // Reviews/rating показуються тільки якщо модуль reviews УВімкнено.
                    // soldCount — це окрема метрика (не reviews-модуль), не гейтиться.
                    $showReviews = module('reviews')->enabled();
                ?>
                <?php if(($showReviews && ($rating > 0 || $reviews > 0)) || $soldCount > 0): ?>
                    <div class="flex items-center gap-1 whitespace-nowrap mt-2">
                        <?php if($showReviews && $rating > 0): ?>
                            <div class="flex gap-px text-[var(--gazu-warn)]">
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
                        <?php endif; ?>
                        <span class="text-xs text-[var(--gazu-graphite)]">
                            <?php if($showReviews && $rating > 0): ?><?php echo e(number_format($rating, 1)); ?><?php endif; ?>
                            <?php if($showReviews && $reviews > 0): ?> · <?php echo e($reviews); ?> <?php echo e(\plural_uk_count($reviews, 'відгук', 'відгуки', 'відгуків')); ?><?php endif; ?>
                            <?php if($soldCount > 0): ?> · <?php echo e($soldCount); ?> продано <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                
                <div class="gazu-grid-product-rhs mt-4">
                    
                    <div>
                        <?php if($stockStatusModel): ?>
                            <div class="mb-2.5">
                                <?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock','data' => ['status' => $stockStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stockStatus)]); ?>
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
                            </div>
                        <?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginale6917196fe944ed89b394fcef90e97f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale6917196fe944ed89b394fcef90e97f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.warehouse-selector','data' => ['warehouseStocks' => $warehouseStocks ?? collect(),'closestWarehouseId' => $closestWarehouseId ?? null,'price' => $price,'brand' => $brand,'brandUrl' => $brandUrl,'article' => $oemReal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.warehouse-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['warehouseStocks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($warehouseStocks ?? collect()),'closestWarehouseId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($closestWarehouseId ?? null),'price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($price),'brand' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brand),'brandUrl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brandUrl),'article' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oemReal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale6917196fe944ed89b394fcef90e97f3)): ?>
<?php $attributes = $__attributesOriginale6917196fe944ed89b394fcef90e97f3; ?>
<?php unset($__attributesOriginale6917196fe944ed89b394fcef90e97f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale6917196fe944ed89b394fcef90e97f3)): ?>
<?php $component = $__componentOriginale6917196fe944ed89b394fcef90e97f3; ?>
<?php unset($__componentOriginale6917196fe944ed89b394fcef90e97f3); ?>
<?php endif; ?>
                    </div>

                    
                    <div class="lg:sticky lg:top-4 lg:self-start" id="buy-panel-anchor">
                        <?php
                            $isGroupPrice = is_object($p) ? (bool) ($p->is_group_price ?? false) : false;
                            $groupLabel = is_object($p) ? ($p->group_label ?? null) : null;
                            $groupFromQty = is_object($p) ? ($p->group_from_qty ?? null) : null;
                            $groupFromPrice = is_object($p) ? ($p->group_from_price ?? null) : null;
                        ?>
                        <?php if($isGroupPrice || ($groupFromQty && $groupFromPrice)): ?>
                            <div class="mb-2 flex items-center gap-2 text-[13px]">
                                <?php if($isGroupPrice): ?>
                                    <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-[var(--gazu-blue-bg,#E0EBFF)] text-[var(--gazu-blue)]">
                                        <?php echo e($groupLabel ?: 'Гуртова ціна'); ?>

                                    </span>
                                    <span class="text-[var(--gazu-graphite)]">ваша персональна ціна</span>
                                <?php else: ?>
                                    <span class="text-[var(--gazu-blue)]">Гуртова <?php echo e(number_format((float) $groupFromPrice, 0, '.', ' ')); ?> ₴ від <?php echo e($groupFromQty); ?> шт</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginala3e840b12d118989ee8c832a7cb2ee4b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.buy-panel','data' => ['price' => $price,'oldPrice' => $oldPrice,'qty' => $qty,'discount' => $discount,'productId' => is_object($p) ? ($p->id ?? null) : null,'name' => $name,'warehouseStocks' => $warehouseStocks ?? collect(),'closestWarehouseId' => $closestWarehouseId ?? null,'groupActive' => $isGroupPrice]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.buy-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['price' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($price),'oldPrice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldPrice),'qty' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($qty),'discount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($discount),'productId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(is_object($p) ? ($p->id ?? null) : null),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'warehouseStocks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($warehouseStocks ?? collect()),'closestWarehouseId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($closestWarehouseId ?? null),'groupActive' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isGroupPrice)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b)): ?>
<?php $attributes = $__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b; ?>
<?php unset($__attributesOriginala3e840b12d118989ee8c832a7cb2ee4b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala3e840b12d118989ee8c832a7cb2ee4b)): ?>
<?php $component = $__componentOriginala3e840b12d118989ee8c832a7cb2ee4b; ?>
<?php unset($__componentOriginala3e840b12d118989ee8c832a7cb2ee4b); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(is_object($p) && $p instanceof \App\Models\Product): ?>
            <?php if (isset($component)) { $__componentOriginal602dfc8921674b2021a18e7fca701504 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal602dfc8921674b2021a18e7fca701504 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.compat-check','data' => ['productId' => $p->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.compat-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal602dfc8921674b2021a18e7fca701504)): ?>
<?php $attributes = $__attributesOriginal602dfc8921674b2021a18e7fca701504; ?>
<?php unset($__attributesOriginal602dfc8921674b2021a18e7fca701504); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal602dfc8921674b2021a18e7fca701504)): ?>
<?php $component = $__componentOriginal602dfc8921674b2021a18e7fca701504; ?>
<?php unset($__componentOriginal602dfc8921674b2021a18e7fca701504); ?>
<?php endif; ?>
        <?php endif; ?>

        
        <?php echo \App\Support\Hooks::render('product.page.variants', $p); ?>

        
        <?php if(is_object($p) && $p instanceof \App\Models\Product): ?>
            <?php
                $productOptions = $p->options()->where('is_active', true)->orderBy('sort_order')->with(['values' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])->get();
            ?>
            <?php if($productOptions->isNotEmpty()): ?>
                <section class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 sm:p-5 mt-4 mb-4"
                         x-data="{
                            picks: {},
                            busy: false,
                            async sync() {
                                const ids = Object.values(this.picks).filter(Boolean);
                                if (ids.length === 0) return;
                                this.busy = true;
                                try {
                                    const url = new URL('/api/products/<?php echo e((int) $p->id); ?>/variant-by-options', window.location.origin);
                                    ids.forEach(id => url.searchParams.append('option_value_ids[]', id));
                                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) throw new Error('http '+res.status);
                                    const data = await res.json();
                                    window.dispatchEvent(new CustomEvent('gazu:variant-switched', { detail: data }));
                                } catch (e) { console.warn('[options] fetch failed', e); }
                                finally { this.busy = false; }
                            }
                         }">
                    <?php $__currentLoopData = $productOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-4 last:mb-0">
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-sm font-semibold text-[var(--gazu-ink)]"><?php echo e($opt->name); ?>:</span>
                                <span class="text-sm text-[var(--gazu-graphite)]" x-text="picks[<?php echo e($opt->id); ?>] ? '<?php echo e($opt->id); ?>' : ''" x-cloak></span>
                            </div>

                            <?php if($opt->type === 'color'): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $opt->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button"
                                                title="<?php echo e($v->value); ?>"
                                                @click="picks[<?php echo e($opt->id); ?>] = <?php echo e($v->id); ?>; sync();"
                                                :disabled="busy"
                                                :class="picks[<?php echo e($opt->id); ?>] === <?php echo e($v->id); ?> ? 'ring-2 ring-[var(--gazu-ink)] ring-offset-2' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-graphite)]'"
                                                style="background-color: <?php echo e($v->color_hex ?: '#ddd'); ?>"
                                                class="w-9 h-9 rounded-full transition-all disabled:opacity-50 disabled:cursor-wait">
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php elseif($opt->type === 'image'): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $opt->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button"
                                                title="<?php echo e($v->value); ?>"
                                                @click="picks[<?php echo e($opt->id); ?>] = <?php echo e($v->id); ?>; sync();"
                                                :disabled="busy"
                                                :class="picks[<?php echo e($opt->id); ?>] === <?php echo e($v->id); ?> ? 'ring-2 ring-[var(--gazu-ink)] ring-offset-1' : 'ring-1 ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] opacity-90'"
                                                class="w-16 h-16 rounded-md overflow-hidden bg-[var(--gazu-paper)] transition-all disabled:opacity-50 disabled:cursor-wait">
                                            <?php if($v->image): ?>
                                                <img src="<?php echo e(\Illuminate\Support\Str::startsWith($v->image, ['http','/']) ? $v->image : '/storage/'.$v->image); ?>" alt="<?php echo e($v->value); ?>" class="w-full h-full object-cover"/>
                                            <?php else: ?>
                                                <span class="block w-full h-full flex items-center justify-center text-xs"><?php echo e($v->value); ?></span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php elseif($opt->type === 'select'): ?>
                                <select @change="picks[<?php echo e($opt->id); ?>] = parseInt($event.target.value); sync();"
                                        :disabled="busy"
                                        class="w-full max-w-xs px-3 py-2 text-sm rounded-md border border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-ink)] focus:outline-none focus:border-[var(--gazu-ink)] disabled:opacity-50">
                                    <option value="">— Оберіть <?php echo e(mb_strtolower($opt->name)); ?> —</option>
                                    <?php $__currentLoopData = $opt->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->value); ?><?php if($v->price_modifier != 0): ?> (<?php echo e($v->price_modifier > 0 ? '+' : ''); ?><?php echo e((int) $v->price_modifier); ?> ₴)<?php endif; ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $opt->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button"
                                                @click="picks[<?php echo e($opt->id); ?>] = <?php echo e($v->id); ?>; sync();"
                                                :disabled="busy"
                                                :class="picks[<?php echo e($opt->id); ?>] === <?php echo e($v->id); ?> ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] ring-[var(--gazu-ink)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] hover:bg-[var(--gazu-paper)]'"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md ring-1 transition-colors disabled:opacity-50 disabled:cursor-wait">
                                            <span><?php echo e($v->value); ?></span>
                                            <?php if($v->price_modifier != 0): ?>
                                                <span class="text-xs opacity-70"><?php echo e($v->price_modifier > 0 ? '+' : ''); ?><?php echo e((int) $v->price_modifier); ?> ₴</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <?php
                    $analogList = ($analogs ?? null) instanceof \Illuminate\Support\Collection
                        ? $analogs : collect();
                    $tabCounts = [
                        'spec'     => count($specs),
                        'compat'   => count($compat),
                        'analogs'  => $analogList->count(),
                        'reviews'  => $reviews,
                        'delivery' => null,
                    ];
                    $tabDefs = [
                        'spec'     => 'Характеристики',
                        'compat'   => 'Сумісність',
                        'analogs'  => 'Аналоги',
                        'reviews'  => 'Відгуки',
                        'delivery' => 'Доставка та оплата',
                    ];
                    $deliveryText = $gazuSettings['gazu_product_delivery_text']
                        ?? 'Нова Пошта по Україні · Доставка наступного дня для замовлень до 16:00 · Безкоштовно від 1500 ₴.';
                    $paymentText = $gazuSettings['gazu_product_payment_text']
                        ?? 'Visa / Mastercard, Apple Pay, Google Pay, готівка при отриманні (накладений платіж), безпечна оплата через LiqPay.';
                ?>
                <div class="mt-2" x-data="{ tab: 'spec' }">
                    
                    
                    <div class="md:hidden sticky top-2 z-30 mt-3"
                         x-data="{
                            canL: false, canR: false,
                            upd() {
                                const e = this.$refs.strip;
                                this.canL = e.scrollLeft > 4;
                                this.canR = e.scrollLeft + e.clientWidth < e.scrollWidth - 4;
                            },
                            nudge(d) { this.$refs.strip.scrollBy({ left: d * 150, behavior: 'smooth' }); }
                         }"
                         x-init="$nextTick(() => upd())"
                         @resize.window.debounce.150ms="upd()">
                        <div class="flex items-stretch bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl overflow-hidden shadow-[0_6px_20px_-6px_rgba(14,27,44,0.22)]">
                            <button type="button" @click="nudge(-1)" x-show="canL" x-cloak x-transition.opacity
                                    aria-label="Прокрутити вкладки вліво"
                                    class="w-9 shrink-0 bg-[var(--gazu-surface)] border-r border-[var(--gazu-line)] text-[var(--gazu-ink)] inline-flex items-center justify-center cursor-pointer active:scale-90 transition-transform">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <div x-ref="strip" @scroll.passive="upd()" role="tablist" aria-label="Інформація про товар"
                                 class="flex gap-1 gazu-scroll-x flex-1 px-1">
                                <?php $__currentLoopData = $tabDefs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button" role="tab"
                                            :aria-selected="tab === '<?php echo e($k); ?>'"
                                            @click="tab = '<?php echo e($k); ?>'; $el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })"
                                            :class="tab === '<?php echo e($k); ?>'
                                                ? 'text-[var(--gazu-ink)] font-semibold border-b-2 border-[var(--gazu-ink)]'
                                                : 'text-[var(--gazu-graphite)] border-b-2 border-transparent'"
                                            class="px-3.5 py-3 -mb-px bg-transparent cursor-pointer inline-flex items-center gap-1.5 text-[13px] shrink-0 whitespace-nowrap transition-colors">
                                        <?php echo e($l); ?>

                                        <?php if($tabCounts[$k] !== null && $tabCounts[$k] > 0): ?>
                                            <span class="text-[10px] text-[var(--gazu-muted)] gazu-mono"><?php echo e($tabCounts[$k]); ?></span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <button type="button" @click="nudge(1)" x-show="canR" x-cloak x-transition.opacity
                                    aria-label="Прокрутити вкладки вправо"
                                    class="w-9 shrink-0 bg-[var(--gazu-surface)] border-l border-[var(--gazu-line)] text-[var(--gazu-ink)] inline-flex items-center justify-center cursor-pointer active:scale-90 transition-transform">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>

                    
                    <div role="tablist" aria-label="Інформація про товар"
                         class="border-b border-[var(--gazu-line)] hidden md:flex gap-1 font-text mt-3 gazu-scroll-x whitespace-nowrap">
                        <?php $__currentLoopData = $tabDefs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" role="tab"
                                    :aria-selected="tab === '<?php echo e($k); ?>'"
                                    :tabindex="tab === '<?php echo e($k); ?>' ? 0 : -1"
                                    @click="tab = '<?php echo e($k); ?>'"
                                    :class="tab === '<?php echo e($k); ?>'
                                        ? 'text-[var(--gazu-ink)] font-semibold border-b-2 border-[var(--gazu-ink)]'
                                        : 'text-[var(--gazu-graphite)] border-b-2 border-transparent hover:text-[var(--gazu-ink)]'"
                                    class="px-4.5 py-3.5 -mb-px bg-transparent cursor-pointer inline-flex items-center gap-1.5 text-sm transition-colors">
                                <?php echo e($l); ?>

                                <?php if($tabCounts[$k] !== null && $tabCounts[$k] > 0): ?>
                                    <span class="text-[11px] text-[var(--gazu-muted)] gazu-mono"><?php echo e($tabCounts[$k]); ?></span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div role="tabpanel" x-show="tab === 'spec'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Характеристики</div>
                        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden">
                            <?php
                                // Clickable spec rows → catalog filter:
                                //   "Виробник" → brand slug (lower-cased name with hyphens)
                                //   "Категорія" → cat slug (if Product has a category relation)
                                $brandSlug = null;
                                if (is_object($p) && method_exists($p, 'relationLoaded') && $p->relationLoaded('brand') && ($b = $p->getRelation('brand'))) {
                                    $brandSlug = $b->slug ?: \Illuminate\Support\Str::slug((string) $b->getRawOriginal('name'));
                                }
                                if (! $brandSlug && is_object($p) && ($p->manufacturer ?? null)) {
                                    $brandSlug = \Illuminate\Support\Str::slug((string) $p->manufacturer);
                                }
                                $catSlug = null;
                                if (is_object($p) && method_exists($p, 'relationLoaded') && $p->relationLoaded('category') && ($cat = $p->getRelation('category'))) {
                                    $raw = $cat->getRawOriginal('slug');
                                    if (is_string($raw) && str_starts_with($raw, '{')) {
                                        $decoded = json_decode($raw, true);
                                        $catSlug = $decoded['uk'] ?? $decoded['en'] ?? null;
                                    } else {
                                        $catSlug = $raw ?: ($cat->slug ?? null);
                                    }
                                }
                            ?>
                            <?php $__currentLoopData = $specs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $v, $mono]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $href = null;
                                    if ($k === 'Виробник' && $brandSlug && $v !== '—') {
                                        $href = route('gazu.brand', ['slug' => $brandSlug]);
                                    } elseif ($k === 'Категорія' && $catSlug) {
                                        $href = url('/'.$catSlug);
                                    }
                                ?>
                                <div class="grid grid-cols-2 px-4 py-2.5 text-[13px] <?php if(!$loop->last): ?> border-b border-[var(--gazu-line)] <?php endif; ?>">
                                    <span class="text-[var(--gazu-graphite)]"><?php echo e($k); ?></span>
                                    <?php if($href): ?>
                                        <a wire:navigate href="<?php echo e($href); ?>" class="text-[var(--gazu-blue)] <?php echo e($mono ? 'gazu-mono font-medium' : ''); ?> no-underline hover:underline inline-flex items-center gap-1">
                                            <?php echo e($v); ?>

                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[var(--gazu-ink)] <?php echo e($mono ? 'gazu-mono font-medium' : ''); ?>"><?php echo e($v); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div role="tabpanel" x-show="tab === 'compat'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Сумісність з автомобілями</div>
                        <?php if(! empty($compat)): ?>
                            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden overflow-x-auto">
                                <table class="w-full text-left font-text text-[13px]">
                                    <thead class="bg-[var(--gazu-bone)] gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider uppercase">
                                        <tr>
                                            <th class="px-3.5 py-3 font-medium">Марка</th>
                                            <th class="px-3.5 py-3 font-medium">Модель</th>
                                            <th class="px-3.5 py-3 font-medium">Роки</th>
                                            <th class="px-3.5 py-3 font-medium">Двигун</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $compat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-t border-[var(--gazu-line)]">
                                                <td class="px-3.5 py-3 gazu-display font-semibold text-[var(--gazu-ink)]">
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="w-6 h-6 rounded overflow-hidden inline-flex items-center justify-center shrink-0 <?php echo e(($r[4] ?? null) ? '' : 'bg-[var(--gazu-mist)] text-[9px] gazu-mono text-[var(--gazu-blue)]'); ?>">
                                                            <?php if($r[4] ?? null): ?><img src="<?php echo e($r[4]); ?>" alt="<?php echo e($r[0]); ?>" class="w-full h-full object-cover" loading="lazy"><?php else: ?><?php echo e(mb_substr($r[0], 0, 2)); ?><?php endif; ?>
                                                        </span>
                                                        <span><?php echo e($r[0]); ?></span>
                                                    </span>
                                                </td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-ink)]"><?php echo e($r[1]); ?></td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs"><?php echo e($r[2]); ?></td>
                                                <td class="px-3.5 py-3 text-[var(--gazu-graphite)] gazu-mono text-xs"><?php echo e($r[3]); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-6 text-center">
                                <p class="text-[13px] text-[var(--gazu-graphite)]">Список сумісних авто для цієї деталі поки не заповнено. Зв'яжіться з менеджером для уточнення.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div role="tabpanel" x-show="tab === 'analogs'" x-cloak class="mt-6">
                        <div class="gazu-display text-lg font-semibold mb-3">Аналоги</div>
                        <?php if($analogList->isNotEmpty()): ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php $__currentLoopData = $analogList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $r]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r)]); ?>
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
                        <?php else: ?>
                            <p class="text-[13px] text-[var(--gazu-graphite)]">Поки що немає підібраних аналогів для цього товару.</p>
                        <?php endif; ?>
                    </div>

                    
                    <div role="tabpanel" x-show="tab === 'reviews'" x-cloak class="mt-6">
                        <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
                            <div class="gazu-display text-lg font-semibold">Відгуки покупців</div>
                            <?php if(auth()->guard()->check()): ?>
                                <a href="#review-form"
                                   @click.prevent="document.getElementById('review-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                                   class="text-[13px] font-medium text-[var(--gazu-ink)] border border-[var(--gazu-ink)] rounded-md px-3 py-1.5 hover:bg-[var(--gazu-mist)] transition-colors no-underline inline-block">
                                    Залишити відгук
                                </a>
                            <?php else: ?>
                                <a wire:navigate href="<?php echo e(route('gazu.auth')); ?>"
                                   class="text-[13px] font-medium text-[var(--gazu-ink)] border border-[var(--gazu-ink)] rounded-md px-3 py-1.5 hover:bg-[var(--gazu-mist)] transition-colors no-underline inline-block">
                                    Увійти, щоб залишити відгук
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if(is_object($p) && method_exists($p, 'approvedReviews') && ($reviewList = $p->approvedReviews()->latest()->take(3)->get())->isNotEmpty()): ?>
                            <div class="flex flex-col gap-3">
                                <?php $__currentLoopData = $reviewList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <article class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4">
                                        <header class="flex items-center justify-between gap-3 mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-[var(--gazu-ink)] text-[14px]"><?php echo e($rev->author_name ?? $rev->user?->name ?? 'Анонім'); ?></span>
                                                <span class="text-[11px] text-[var(--gazu-muted)] gazu-mono"><?php echo e(optional($rev->created_at)->format('d.m.Y')); ?></span>
                                            </div>
                                            <div class="flex gap-px text-[var(--gazu-warn)]">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'star','size' => '12','fill' => ''.e($i <= (int) ($rev->rating ?? 0) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'star','size' => '12','fill' => ''.e($i <= (int) ($rev->rating ?? 0) ? 'var(--gazu-warn)' : 'none').'','stroke' => 'var(--gazu-warn)']); ?>
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
                                        </header>
                                        <?php if(!empty($rev->title)): ?>
                                            <div class="text-[14px] font-semibold text-[var(--gazu-ink)] mb-1"><?php echo e($rev->title); ?></div>
                                        <?php endif; ?>
                                        <p class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed m-0"><?php echo e($rev->body ?? $rev->comment ?? ''); ?></p>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-[13px] text-[var(--gazu-graphite)]">Будьте першим, хто залишить відгук на цей товар.</p>
                        <?php endif; ?>
                    </div>

                    
                    <div role="tabpanel" x-show="tab === 'delivery'" x-cloak class="mt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 flex gap-3 items-start">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'truck','size' => '22','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','size' => '22','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']); ?>
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
                                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1">Доставка</div>
                                    <div class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed"><?php echo e($deliveryText); ?></div>
                                </div>
                            </div>
                            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 flex gap-3 items-start">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'shield','size' => '22','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield','size' => '22','stroke' => 'var(--gazu-blue)','class' => 'shrink-0']); ?>
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
                                    <div class="gazu-display font-semibold text-[var(--gazu-ink)] mb-1">Оплата</div>
                                    <div class="text-[13px] text-[var(--gazu-graphite)] leading-relaxed"><?php echo e($paymentText); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Часто купують разом','items' => $related,'bare' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Часто купують разом','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($related),'bare' => true]); ?>
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
    </div>

    
    <?php
        $productId = is_object($p) ? ($p->id ?? null) : null;
        $stocks = ($warehouseStocks ?? collect());
        $defaultStock = $closestWarehouseId
            ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
            : null;
        $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
        $defaultWh = $defaultStock?->warehouse_id;
        $defaultPrice = $defaultStock && $defaultStock->price !== null ? (float) $defaultStock->price : (float) $price;
    ?>
    <?php if($productId): ?>
        <div x-data="{
                show: false,
                init() {
                    const anchor = document.getElementById('buy-panel-anchor');
                    if (!anchor || !('IntersectionObserver' in window)) return;
                    const io = new IntersectionObserver(
                        ([entry]) => { this.show = !entry.isIntersecting; },
                        { rootMargin: '-80px 0px 0px 0px', threshold: 0 }
                    );
                    io.observe(anchor);
                }
             }"
             x-show="show" x-cloak x-transition.opacity.duration.200ms
             class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-[var(--gazu-surface)] border-t border-[var(--gazu-line)] shadow-[0_-4px_12px_-2px_rgba(0,0,0,0.08)] px-4 py-3"
             role="region" aria-label="Швидкий кошик">
            <form action="<?php echo e(route('gazu.cart.add')); ?>" method="POST" class="flex items-center gap-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo e($productId); ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="warehouse_id" value="<?php echo e($defaultWh); ?>">
                <div class="flex-1 min-w-0">
                    <div class="text-[11px] text-[var(--gazu-graphite)] truncate"><?php echo e(is_object($p) ? ($p->name ?? '') : ''); ?></div>
                    <div class="gazu-display font-bold text-[var(--gazu-ink)] gazu-mono">
                        <?php echo e(number_format($defaultPrice, 0, '.', ' ')); ?> ₴
                    </div>
                </div>
                <button type="submit"
                    class="h-12 px-5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-lg text-[14px] font-semibold cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-ink-2)] whitespace-nowrap"
                    aria-label="Додати в кошик за <?php echo e(number_format($defaultPrice, 0, '.', ' ')); ?> грн">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '18']); ?>
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
                    <span>У кошик</span>
                </button>
            </form>
        </div>
    <?php endif; ?>

    
    <?php $currentPid = is_object($p) ? (int) ($p->id ?? 0) : 0; ?>
    <?php if($currentPid): ?>
        <script>
            // Trigger через wire:navigate (livewire:navigated) + initial DOMContentLoaded
            (function () {
                var t = function () { if (window.gazuTrackProduct) window.gazuTrackProduct(<?php echo e($currentPid); ?>); };
                document.addEventListener('DOMContentLoaded', t, { once: true });
                document.addEventListener('livewire:navigated', t);
                t(); // immediate if script late
            })();
        </script>
        <?php if (isset($component)) { $__componentOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25ec19f9c4b9686e9e5f651a70853bf3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.recently-viewed','data' => ['excludeId' => $currentPid]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.recently-viewed'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['exclude-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentPid)]); ?>
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
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/product/v1.blade.php ENDPATH**/ ?>