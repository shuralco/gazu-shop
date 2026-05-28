<?php
    $activeNav = $activeNav ?? 'catalog';
    $cartCount = $cartCount ?? 0;
    $megaOpen = $megaOpen ?? false;
?>
<header class="bg-white border-b border-[var(--gazu-line)] relative font-text"
        x-data="{ megaOpen: false }"
        @keydown.escape.window="megaOpen = false">
    <?php echo $__env->make('gazu.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="gazu-container py-3 sm:py-4 flex flex-wrap items-center gap-x-3 sm:gap-x-4 gap-y-3 lg:flex-nowrap lg:gap-5">
        <a wire:navigate href="<?php echo e(route('gazu.home')); ?>" class="no-underline shrink-0 inline-flex items-center">
            <?php if (isset($component)) { $__componentOriginal00cc706ec7279da3d3246febbb2826f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal00cc706ec7279da3d3246febbb2826f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.logo','data' => ['size' => '26']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => '26']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal00cc706ec7279da3d3246febbb2826f1)): ?>
<?php $attributes = $__attributesOriginal00cc706ec7279da3d3246febbb2826f1; ?>
<?php unset($__attributesOriginal00cc706ec7279da3d3246febbb2826f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal00cc706ec7279da3d3246febbb2826f1)): ?>
<?php $component = $__componentOriginal00cc706ec7279da3d3246febbb2826f1; ?>
<?php unset($__componentOriginal00cc706ec7279da3d3246febbb2826f1); ?>
<?php endif; ?>
        </a>

        
        <button type="button"
                @click="megaOpen = !megaOpen"
                :aria-label="megaOpen ? 'Закрити каталог' : 'Відкрити каталог'"
                :class="megaOpen ? 'bg-[var(--gazu-blue)]' : 'bg-[var(--gazu-ink)]'"
                class="inline-flex items-center justify-center gap-2 w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 text-white border-0 rounded-lg text-sm font-medium shrink-0 cursor-pointer transition-colors hover:opacity-90">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'menu','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'menu','size' => '18']); ?>
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
            <span class="hidden sm:inline">Каталог</span>
        </button>

        
        <div class="order-last w-full lg:order-none lg:w-auto lg:flex-1 min-w-0 relative"
             x-data="{
                q: <?php echo \Illuminate\Support\Js::from(request('q', ''))->toHtml() ?>,
                items: [],
                total: 0,
                open: false,
                loading: false,
                timer: null,
                voiceSupported: false,
                listening: false,
                _rec: null,
                async fetch() {
                    if (this.q.length < 2) { this.items = []; this.open = false; return; }
                    this.loading = true;
                    try {
                        const r = await window.fetch('<?php echo e(route('gazu.search.suggest')); ?>?q=' + encodeURIComponent(this.q));
                        const d = await r.json();
                        this.items = d.items || [];
                        this.total = d.total || 0;
                        this.open = true;
                    } catch(e) { this.items = []; this.open = false; }
                    finally { this.loading = false; }
                },
                onInput() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => this.fetch(), 250);
                },
                initVoice() {
                    this.voiceSupported = 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window;
                },
                voice() {
                    if (!this.voiceSupported) return;
                    if (this.listening) { this._rec?.stop(); return; }
                    const R = window.SpeechRecognition || window.webkitSpeechRecognition;
                    const r = new R();
                    r.lang = 'uk-UA';
                    r.interimResults = true;
                    r.continuous = false;
                    r.onstart  = () => { this.listening = true; };
                    r.onend    = () => { this.listening = false; this._rec = null; };
                    r.onerror  = () => { this.listening = false; };
                    r.onresult = (e) => {
                        const t = Array.from(e.results).map(x => x[0].transcript).join('').trim();
                        this.q = t;
                        if (e.results[e.results.length - 1].isFinal) {
                            this.onInput();
                        }
                    };
                    this._rec = r;
                    try { r.start(); } catch(e) { this.listening = false; }
                }
             }"
             x-init="initVoice()"
             @click.outside="open = false">
            <form action="<?php echo e(route('gazu.search')); ?>" method="GET" class="flex items-stretch border-[1.5px] border-[var(--gazu-ink)] rounded-lg overflow-hidden bg-white">
                <input name="q" placeholder="Назва категорії, бренд або деталь — напр. оливний фільтр, Bosch, амортизатор"
                       x-model="q" @input="onInput" @focus="if (items.length) open = true"
                       class="flex-1 min-w-0 border-0 outline-none px-3.5 py-2.5 text-sm text-[var(--gazu-ink)]"
                       autocomplete="off">
                
                <button type="button" @click="voice()" x-show="voiceSupported" x-cloak
                        :aria-pressed="listening"
                        :title="listening ? 'Зупинити запис' : 'Голосовий пошук'"
                        :class="listening ? 'text-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)]' : 'text-[var(--gazu-graphite)] bg-white hover:bg-[var(--gazu-paper)]'"
                        class="border-0 border-l border-[var(--gazu-line)] px-3 cursor-pointer inline-flex items-center justify-center shrink-0 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         :class="listening ? 'animate-pulse' : ''"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 1 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 1 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                </button>
                <button type="submit" class="border-0 bg-[var(--gazu-ink)] text-white px-4 cursor-pointer inline-flex items-center gap-1.5 text-sm shrink-0">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'search','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','size' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> <span class="hidden sm:inline">Знайти</span>
                </button>
            </form>

            
            <div x-show="open && (items.length || loading)" x-cloak x-transition.opacity
                 class="absolute top-full left-0 right-0 mt-2 bg-white border border-[var(--gazu-line)] rounded-lg shadow-2xl z-50 overflow-hidden max-h-[80vh] overflow-y-auto">
                <template x-if="loading && !items.length">
                    <div class="p-4 text-center text-sm text-[var(--gazu-graphite)]">Шукаю…</div>
                </template>
                <template x-for="item in items" :key="item.id">
                    <a wire:navigate :href="item.url" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[var(--gazu-paper)] no-underline border-b border-[var(--gazu-line)] last:border-b-0">
                        <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded shrink-0 flex items-center justify-center text-[10px] gazu-mono text-[var(--gazu-muted)]" x-text="item.image_kind"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-[var(--gazu-ink)] truncate" x-text="item.title"></div>
                            <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono truncate">
                                <span x-text="item.manufacturer"></span><span x-show="item.manufacturer && item.sku"> · </span><span x-text="item.sku"></span>
                            </div>
                        </div>
                        <div class="gazu-display font-bold text-sm text-[var(--gazu-ink)] whitespace-nowrap">
                            <span x-text="item.price_formatted"></span> ₴
                        </div>
                    </a>
                </template>
                <template x-if="total > items.length">
                    <a wire:navigate :href="`<?php echo e(route('gazu.search')); ?>?q=${encodeURIComponent(q)}`"
                       class="block px-3 py-2.5 text-center bg-[var(--gazu-paper)] text-sm text-[var(--gazu-blue)] no-underline hover:bg-[var(--gazu-mist)]">
                        Усі <span x-text="total"></span> результатів →
                    </a>
                </template>
            </div>
        </div>

        
        <?php
            $phone = $gazuSettings['gazu_phone'] ?? '0 800 75 10 24';
            $phoneSubtitle = $gazuSettings['gazu_phone_subtitle'] ?? 'безкоштовно по Україні';
        ?>
        <?php if($phone): ?>
            <div class="hidden lg:flex flex-col items-start gap-1 shrink-0">
                <a href="tel:<?php echo e(preg_replace('/\s+/', '', $phone)); ?>" class="no-underline">
                    <div class="text-[15px] font-bold text-[var(--gazu-ink)] gazu-display whitespace-nowrap leading-none"><?php echo e($phone); ?></div>
                </a>
                <?php if (isset($component)) { $__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.callback-popover','data' => ['variant' => 'link','source' => 'header','align' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.callback-popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'link','source' => 'header','align' => 'left']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461)): ?>
<?php $attributes = $__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461; ?>
<?php unset($__attributesOriginalf880cdd2a92bda13a7bd65fa5f8c7461); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461)): ?>
<?php $component = $__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461; ?>
<?php unset($__componentOriginalf880cdd2a92bda13a7bd65fa5f8c7461); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>

        
        <div class="flex items-center gap-1 shrink-0 ml-auto lg:ml-0">
            <?php $wlc = auth()->check() ? \DB::table('wishlists')->where('user_id', auth()->id())->count() : 0; ?>
            <a wire:navigate href="<?php echo e(route('gazu.wishlist')); ?>" title="Обране" aria-label="Список обраних товарів"
               x-data="{ count: <?php echo e((int) $wlc); ?> }"
               x-on:gazu:wishlist-changed.window="count = $event.detail.count"
               x-init="$nextTick(() => { if (window.GAZU_WISHLIST_IDS) count = window.GAZU_WISHLIST_IDS.size; })"
               class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'heart','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'heart','size' => '20']); ?>
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
                <span x-show="count > 0" x-cloak class="absolute -top-1 -right-1 bg-[var(--gazu-danger)] text-white rounded-full min-w-[18px] h-[18px] text-[11px] font-semibold flex items-center justify-center px-1" x-text="count"></span>
            </a>
            <a wire:navigate href="<?php echo e(auth()->check() ? route('gazu.account') : route('gazu.auth')); ?>"
               title="<?php echo e(auth()->check() ? auth()->user()->name : 'Вхід / Реєстрація'); ?>"
               class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'user','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','size' => '20']); ?>
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
                <?php if(auth()->guard()->check()): ?>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-[var(--gazu-success)] rounded-full border-2 border-white"></span>
                <?php endif; ?>
            </a>
            <a wire:navigate href="<?php echo e(route('gazu.cart')); ?>"
               data-gazu-cart-icon
               x-data="{ count: <?php echo e((int) $cartCount); ?> }"
               x-on:cart-updated.window="count = $event.detail.count"
               class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-[var(--gazu-ink)] text-white border border-[var(--gazu-ink)] rounded-lg cursor-pointer relative">
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
                <span x-show="count > 0" x-cloak
                      class="absolute -top-1 -right-1 bg-[var(--gazu-blue)] text-white rounded-full min-w-[18px] h-[18px] text-[11px] font-semibold flex items-center justify-center px-1"
                      x-text="count"><?php echo e($cartCount); ?></span>
            </a>
        </div>
    </div>

    
    <div class="hidden lg:block border-t border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
        <?php
            // Sub-nav: admin-editable (gazu_subnav) із fallback на дефолтні маршрути.
            $subnavSetting = $gazuSettings['gazu_subnav'] ?? null;
            if (is_array($subnavSetting) && ! empty($subnavSetting)) {
                $subnav = collect($subnavSetting)
                    ->map(fn ($i) => [
                        'k'     => $i['key'] ?? \Illuminate\Support\Str::slug($i['label'] ?? ''),
                        'label' => $i['label'] ?? '',
                        'url'   => $i['url'] ?? '#',
                    ])
                    ->filter(fn ($i) => $i['label'] !== '')
                    ->all();
            } else {
                $subnav = [
                    ['k' => 'promo',  'label' => 'Акції',   'url' => route('gazu.catalog.promo')],
                    ['k' => 'hits',   'label' => 'Хіти',    'url' => route('gazu.catalog.hits')],
                    ['k' => 'new',    'label' => 'Новинки', 'url' => route('gazu.catalog.new')],
                    ['k' => 'brands', 'label' => 'Бренди',  'url' => route('gazu.brand')],
                    ['k' => 'blog',   'label' => 'Блог',    'url' => route('gazu.blog')],
                ];
            }
        ?>
        <div class="gazu-container px-6 flex items-center gap-0.5 text-[13px] whitespace-nowrap overflow-x-auto">
            <?php $__currentLoopData = $subnav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a wire:navigate href="<?php echo e($item['url']); ?>"
                   class="px-3.5 py-3.5 no-underline <?php echo e($activeNav === $item['k'] ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]'); ?>"
                   style="border-bottom: 2px solid <?php echo e($activeNav === $item['k'] ? 'var(--gazu-blue)' : 'transparent'); ?>;"><?php echo e($item['label']); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <span class="flex-1"></span>
        </div>
    </div>

    
    <template x-teleport="body">
        <div x-show="megaOpen"
             x-transition.opacity.duration.150ms
             style="display: none;">
            
            <div class="fixed inset-0 bg-black/45 z-[55] cursor-pointer"
                 @click="megaOpen = false"></div>
            
            <div class="fixed z-[56] left-2 right-2 top-2 bottom-2
                        lg:left-1/2 lg:right-auto lg:top-[105px] lg:bottom-auto
                        lg:-translate-x-1/2 lg:w-[min(1280px,calc(100vw-48px))]"
                 @click.outside="megaOpen = false">
                <?php echo $__env->make('gazu.partials.mega-menu', ['activeMega' => 'engine'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </template>
</header>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/header.blade.php ENDPATH**/ ?>