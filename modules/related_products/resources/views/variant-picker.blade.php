{{-- Variants picker — Розетка-стиль "виберіть розмір/тип/обʼєм".
     Показує related-products згруповані по тій характеристиці, що відрізняється
     від поточного товару. Клік на pill → AJAX swap без перезавантаження.

     Очікує: $p (Product instance). Виноситься з gazu/product/v1.blade.php
     при увімкненні модуля related_products. --}}
@php
    $maxVariants = (int) (module('related_products')->setting('max_variants_displayed') ?? 50);
    $currentSpecs = is_array($p->specifications) ? $p->specifications : (json_decode((string) $p->specifications, true) ?: []);
    $variants = $p->relatedProducts()
        ->where('related_products.type', 'related')
        ->limit($maxVariants)
        ->get(['products.id', 'products.title', 'products.slug', 'products.price', 'products.specifications', 'products.image']);

    $variantGroups = [];
    foreach ($variants as $v) {
        $vs = is_array($v->specifications) ? $v->specifications : (json_decode((string) $v->specifications, true) ?: []);
        if (! is_array($vs)) continue;
        foreach ($currentSpecs as $k => $curVal) {
            if (! isset($vs[$k])) continue;
            if ((string) $vs[$k] !== (string) $curVal) {
                $variantGroups[$k][] = [
                    'id' => $v->id,
                    'value' => (string) $vs[$k],
                    'slug' => is_array($v->slug) ? ($v->slug['uk'] ?? '') : (string) $v->slug,
                    'price' => $v->price,
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
@endphp
@if(! empty($variantGroups))
    <section class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 sm:p-5 mt-4 mb-4"
             x-data="{
                activeId: {{ (int) $p->id }},
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
        @foreach($variantGroups as $specKey => $options)
            <div class="flex flex-wrap items-baseline gap-2 mb-3 last:mb-0">
                <span class="text-sm text-[var(--gazu-graphite)] mr-2">{{ $specKey }}:</span>
                @php $currentValue = (string) ($currentSpecs[$specKey] ?? ''); @endphp
                @if($currentValue !== '')
                    <button type="button"
                            @click="switchTo({{ (int) $p->id }}, '{{ is_array($p->slug) ? ($p->slug['uk'] ?? '') : $p->slug }}')"
                            :class="activeId === {{ (int) $p->id }} ? 'bg-[var(--gazu-ink)] text-white ring-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)]'"
                            class="inline-flex items-center px-2.5 py-1 text-sm font-medium rounded-md ring-1 transition-colors">
                        {{ $currentValue }}
                    </button>
                @endif
                @foreach($options as $opt)
                    <button type="button"
                            @click="switchTo({{ (int) $opt['id'] }}, '{{ $opt['slug'] }}')"
                            :disabled="switching"
                            :class="activeId === {{ (int) $opt['id'] }} ? 'bg-[var(--gazu-ink)] text-white ring-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] ring-[var(--gazu-line)] hover:ring-[var(--gazu-ink)] hover:bg-[var(--gazu-paper)]'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-sm rounded-md ring-1 transition-colors disabled:opacity-50 disabled:cursor-wait">
                        <span>{{ $opt['value'] }}</span>
                        <span class="text-xs opacity-70">{{ number_format($opt['price'], 0, '.', ' ') }} ₴</span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </section>

    {{-- AJAX swap script — слухає gazu:variant-switched і оновлює
         ключові DOM-поля. Цей же handler використовується і для опцій
         (options block). --}}
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
@endif
