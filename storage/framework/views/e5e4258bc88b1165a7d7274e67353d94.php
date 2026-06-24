<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Ви нещодавно дивились', 'limit' => 8, 'excludeId' => null]));

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

foreach (array_filter((['title' => 'Ви нещодавно дивились', 'limit' => 8, 'excludeId' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section x-data="gazuRecentlyViewed(<?php echo e((int) ($excludeId ?? 0)); ?>, <?php echo e((int) $limit); ?>)"
         x-init="load()"
         x-show="products.length > 0" x-cloak
         class="gazu-container py-10">
    <div class="flex items-end justify-between mb-5">
        <div>
            <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">Історія</div>
            <h2 class="gazu-display text-[22px] sm:text-[28px] font-semibold text-[var(--gazu-ink)] m-0 leading-tight"><?php echo e($title); ?></h2>
        </div>
        <button type="button" @click="clear()"
                class="text-[12px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer inline-flex items-center gap-1.5">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            Очистити історію
        </button>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
        <template x-for="p in products" :key="p.id">
            <a :href="p.url" wire:navigate class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-3 no-underline text-[var(--gazu-ink)] hover:border-[var(--gazu-ink)] hover:shadow-[0_8px_24px_-12px_rgba(14,27,44,0.18)] transition-all flex flex-col gap-2">
                <div class="aspect-square bg-[var(--gazu-paper)] rounded-md overflow-hidden flex items-center justify-center">
                    <template x-if="p.image">
                        <img :src="p.image" :alt="p.name" loading="lazy" decoding="async" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!p.image">
                        <div class="text-[var(--gazu-line-2)] text-xs">GAZU</div>
                    </template>
                </div>
                <div class="text-[12px] text-[var(--gazu-graphite)]" x-text="p.brand"></div>
                <div class="text-[13px] font-medium leading-tight line-clamp-2" x-text="p.name"></div>
                <div class="gazu-display text-[16px] font-bold text-[var(--gazu-ink)]" x-text="p.price + ' ₴'"></div>
            </a>
        </template>
    </div>
</section>

<?php if (! $__env->hasRenderedOnce('99c60fc2-a91a-4f07-8ed0-48be29354356')): $__env->markAsRenderedOnce('99c60fc2-a91a-4f07-8ed0-48be29354356'); ?>
<script>
    (function () {
        if (typeof window.__gazuRecentRegistered !== 'undefined') return;
        window.__gazuRecentRegistered = true;
        const COOKIE = 'gazu_recent';
        const ENDPOINT = <?php echo json_encode(route('gazu.api.products.by-ids'), 15, 512) ?>;

        // Cookie helpers
        function getRecent() {
            const m = document.cookie.match(/(?:^|; )gazu_recent=([^;]+)/);
            if (!m) return [];
            try { return m[1].split(',').map(Number).filter(Boolean); } catch (e) { return []; }
        }
        function setRecent(ids) {
            ids = [...new Set(ids)].slice(0, 24);
            const v = ids.join(',');
            document.cookie = COOKIE + '=' + v + '; path=/; max-age=' + (60*60*24*30) + '; samesite=lax';
        }
        // Public API: window.gazuTrackProduct(productId)
        window.gazuTrackProduct = function (pid) {
            pid = parseInt(pid);
            if (!pid) return;
            const cur = getRecent();
            const next = [pid, ...cur.filter(x => x !== pid)];
            setRecent(next);
        };

        const register = () => {
            if (!window.Alpine) { document.addEventListener('alpine:init', register, {once: true}); return; }
            window.Alpine.data('gazuRecentlyViewed', (excludeId, limit) => ({
                products: [],
                excludeId: excludeId, limit: limit,
                async load() {
                    const ids = getRecent().filter(id => id !== this.excludeId).slice(0, this.limit);
                    if (ids.length === 0) return;
                    try {
                        const r = await fetch(ENDPOINT + '?ids=' + ids.join(','));
                        const d = await r.json();
                        this.products = d.items || [];
                    } catch (e) {}
                },
                clear() {
                    document.cookie = COOKIE + '=; path=/; max-age=0';
                    this.products = [];
                },
            }));
        };
        register();
    })();
</script>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/recently-viewed.blade.php ENDPATH**/ ?>