<?php $__env->startSection('title', 'Оформлення замовлення — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Захисні дефолти — checkout завжди має набір способів (CheckoutController
    // їх передає; це страховка від несподіваних шляхів рендеру).
    $paymentMethods = $paymentMethods ?? [['code' => 'card', 'label' => 'Оплата картою онлайн', 'desc' => '', 'fee' => 0.0]];
    $shippingOptions = $shippingOptions ?? [['code' => 'pickup', 'label' => 'Самовивіз з магазину', 'desc' => 'Безкоштовно']];
    // Налаштування полів/кошика (модуль checkout_settings).
    $cf = \App\Support\Checkout\CheckoutConfig::fields();
    $cfCustom = \App\Support\Checkout\CheckoutConfig::customFields();
    $cfMinOrder = \App\Support\Checkout\CheckoutConfig::minOrderAmount();
    $cfFreeShip = \App\Support\Checkout\CheckoutConfig::freeShippingThreshold();
    $cfPromo = \App\Support\Checkout\CheckoutConfig::promoEnabled();
    $cfBelowMin = $cfMinOrder > 0 && (float) $cartTotal < $cfMinOrder;
?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], ['Кошик', route('gazu.cart')], 'Оформлення']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], ['Кошик', route('gazu.cart')], 'Оформлення'])]); ?>
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
    <h1 class="gazu-display text-2xl sm:text-3xl md:text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-5">Оформлення замовлення</h1>

    
    <nav aria-label="Прогрес замовлення" class="mb-7">
        <ol class="flex items-center gap-2 sm:gap-4 text-sm overflow-x-auto">
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-[var(--gazu-on-brand)] flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <a wire:navigate href="<?php echo e(route('gazu.cart')); ?>" class="text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] no-underline">Кошик</a>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-ink)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] flex items-center justify-center font-bold">2</span>
                <span class="text-[var(--gazu-ink)] font-medium">Оформлення</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-line-2)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0 opacity-60">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] flex items-center justify-center font-bold">3</span>
                <span class="text-[var(--gazu-graphite)]">Готово</span>
            </li>
        </ol>
    </nav>

    <?php if($errors->any()): ?>
        <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-4 py-3 rounded-md mb-4 text-sm">
            <strong>Виправте помилки:</strong>
            <ul class="list-disc list-inside mt-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($err); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('gazu.checkout.store')); ?>" method="POST" class="gazu-grid-cart">
        <?php echo csrf_field(); ?>
        <div class="flex flex-col gap-4">
            
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 border-[var(--gazu-ink)]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]">1</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Контактні дані</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-3 pl-11">
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="text" name="first_name" value="<?php echo e(old('first_name', auth()->user()?->name)); ?>" required
                               class="w-full px-3 py-2.5 border <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <?php if($cf['last_name']['visible']): ?>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block"><?php echo e($cf['last_name']['label']); ?><?php if($cf['last_name']['required']): ?> <span class="text-[var(--gazu-danger)]">*</span><?php endif; ?></span>
                        <input type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" <?php if($cf['last_name']['required']): ?> required <?php endif; ?> placeholder="<?php echo e($cf['last_name']['placeholder']); ?>"
                               class="w-full px-3 py-2.5 border <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <?php endif; ?>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Телефон <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="tel" name="phone" value="<?php echo e(old('phone', auth()->user()?->phone)); ?>" required placeholder="+380 67 123 45 67"
                               class="w-full px-3 py-2.5 border <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <?php if($cf['email']['visible']): ?>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block"><?php echo e($cf['email']['label']); ?><?php if($cf['email']['required']): ?> <span class="text-[var(--gazu-danger)]">*</span><?php endif; ?></span>
                        <input type="email" name="email" value="<?php echo e(old('email', auth()->user()?->email)); ?>" <?php if($cf['email']['required']): ?> required <?php endif; ?> placeholder="<?php echo e($cf['email']['placeholder']); ?>"
                               class="w-full px-3 py-2.5 border <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <?php endif; ?>

                    <?php $__currentLoopData = $cfCustom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $custom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label>
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block"><?php echo e($custom['label']); ?><?php if($custom['required']): ?> <span class="text-[var(--gazu-danger)]">*</span><?php endif; ?></span>
                        <input type="text" name="custom_<?php echo e($custom['key']); ?>" value="<?php echo e(old('custom_'.$custom['key'])); ?>" <?php if($custom['required']): ?> required <?php endif; ?> placeholder="<?php echo e($custom['placeholder']); ?>"
                               class="w-full px-3 py-2.5 border <?php $__errorArgs = ['custom_'.$custom['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none focus:border-[var(--gazu-ink)]">
                        <?php $__errorArgs = ['custom_'.$custom['key']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]">2</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Доставка</h3>
                </div>
                <?php
                    // Default shipping method = first option in the list,
                    // so the form doesn't preselect a disabled provider.
                    // $shippingOptions приходить з CheckoutController (активні
                    // провайдери доставки з БД у порядку sort_order).
                    $defaultShippingMethod = $shippingOptions[0]['code'] ?? 'pickup';
                ?>
                <div class="grid gap-2 pl-11"
                     x-data="{
                         method: <?php echo \Illuminate\Support\Js::from(old('shipping_method', $defaultShippingMethod))->toHtml() ?>,
                         city: <?php echo \Illuminate\Support\Js::from(old('shipping_city', ''))->toHtml() ?>,
                         cityRef: <?php echo \Illuminate\Support\Js::from(old('shipping_city_ref', ''))->toHtml() ?>,
                         warehouse: <?php echo \Illuminate\Support\Js::from(old('shipping_warehouse', ''))->toHtml() ?>,
                         warehouseRef: <?php echo \Illuminate\Support\Js::from(old('shipping_warehouse_ref', ''))->toHtml() ?>,
                         type: <?php echo \Illuminate\Support\Js::from(old('shipping_warehouse_type', 'branch'))->toHtml() ?>,
                             cityResults: [],
                             warehouseResults: [],
                             cityOpen: false,
                             warehouseOpen: false,
                             cityTimer: null,
                             warehouseTimer: null,
                             async fetchCities() {
                                 const r = await fetch('<?php echo e(route('gazu.api.np.cities')); ?>?q=' + encodeURIComponent(this.city), { cache: 'no-store' });
                                 const d = await r.json();
                                 this.cityResults = d.items || [];
                                 this.cityOpen = this.cityResults.length > 0;
                                 // Auto-select при точному збігу + лише 1 результат не потрібно — користувач сам обере.
                                 const exact = this.cityResults.find(c => c.name.toLowerCase() === this.city.toLowerCase());
                                 if (exact && !this.cityRef) {
                                     this.cityRef = exact.ref;
                                 }
                             },
                             onCityInput() {
                                 this.cityRef = '';
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.warehouseResults = [];
                                 clearTimeout(this.cityTimer);
                                 this.cityTimer = setTimeout(() => this.fetchCities(), 200);
                             },
                             selectCity(item) {
                                 this.city = item.name;
                                 this.cityRef = item.ref;
                                 this.cityOpen = false;
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.fetchWarehouses(true);
                             },
                             switchType(t) {
                                 this.type = t;
                                 this.warehouse = '';
                                 this.warehouseRef = '';
                                 this.warehouseResults = [];
                                 this.warehouseOpen = false;
                                 if (this.cityRef) this.fetchWarehouses(true);
                             },
                             async fetchWarehouses(autoOpen = false) {
                                 const params = new URLSearchParams({
                                     city_ref: this.cityRef || '',
                                     city: this.city || '',
                                     q: this.warehouse || '',
                                     type: this.type || 'branch',
                                 });
                                 const r = await fetch('<?php echo e(route('gazu.api.np.warehouses')); ?>?' + params, { cache: 'no-store' });
                                 const d = await r.json();
                                 this.warehouseResults = d.items || [];
                                 this.warehouseOpen = this.warehouseResults.length > 0;
                             },
                             onWarehouseInput() {
                                 this.warehouseRef = '';
                                 clearTimeout(this.warehouseTimer);
                                 // Завжди відкриваємо dropdown якщо є результати — навіть для першого символу
                                 this.warehouseTimer = setTimeout(() => this.fetchWarehouses(true), 150);
                             },
                             selectWarehouse(item) {
                                 const num = item.number ? '№' + item.number + ' · ' : '';
                                 this.warehouse = num + (item.short_address || item.name);
                                 this.warehouseRef = item.ref;
                                 this.warehouseOpen = false;
                             },
                             // Streets (NP Кур'єр)
                             street: <?php echo \Illuminate\Support\Js::from(old('shipping_street', ''))->toHtml() ?>,
                             streetRef: <?php echo \Illuminate\Support\Js::from(old('shipping_street_ref', ''))->toHtml() ?>,
                             streetResults: [],
                             streetOpen: false,
                             streetTimer: null,
                             async fetchStreets() {
                                 if (!this.cityRef || !this.street || this.street.length < 2) {
                                     this.streetResults = []; this.streetOpen = false; return;
                                 }
                                 const params = new URLSearchParams({ city_ref: this.cityRef, q: this.street });
                                 const r = await fetch('<?php echo e(route('gazu.api.np.streets')); ?>?' + params, { cache: 'no-store' });
                                 const d = await r.json();
                                 this.streetResults = d.items || [];
                                 this.streetOpen = this.streetResults.length > 0;
                             },
                             onStreetInput() {
                                 this.streetRef = '';
                                 clearTimeout(this.streetTimer);
                                 this.streetTimer = setTimeout(() => this.fetchStreets(), 250);
                             },
                             selectStreet(item) {
                                 this.street = item.name;
                                 this.streetRef = item.ref;
                                 this.streetOpen = false;
                             },
                             // Shipping cost / delivery
                             shippingCost: null,
                             shippingDays: null,
                             shippingDate: null,
                             shippingLoading: false,
                             async fetchShipping() {
                                 if (!this.cityRef || this.method !== 'novaposhta') {
                                     this.shippingCost = null; this.shippingDays = null; return;
                                 }
                                 this.shippingLoading = true;
                                 try {
                                     const params = new URLSearchParams({ city_ref: this.cityRef, type: this.type });
                                     const r = await fetch('<?php echo e(route('gazu.api.np.calculate')); ?>?' + params, { cache: 'no-store' });
                                     const d = await r.json();
                                     this.shippingCost = d.cost;
                                     this.shippingDays = d.days;
                                     this.shippingDate = d.date;
                                 } catch (e) {} finally { this.shippingLoading = false; }
                             },
                             init() {
                                 this.$watch('cityRef', (v) => { if (v) { this.fetchWarehouses(true); this.fetchShipping(); } });
                                 this.$watch('type', () => { if (this.cityRef) this.fetchShipping(); });
                                 this.$watch('method', () => { this.fetchShipping(); });
                                 // Push shipping cost у sidebar
                                 this.$watch('shippingCost', (v) => window.dispatchEvent(new CustomEvent('gazu-shipping', { detail: { cost: v, method: this.method } })));
                                 this.$watch('method', () => window.dispatchEvent(new CustomEvent('gazu-shipping', { detail: { cost: this.method === 'pickup' ? 0 : this.shippingCost, method: this.method } })));
                                 // Pick from map popup
                                 document.addEventListener('np-map-pick', (e) => {
                                     const item = this.warehouseResults.find(w => w.ref === e.detail.ref);
                                     if (item) {
                                         this.selectWarehouse(item);
                                         window.gazuToast && window.gazuToast('Обрано №' + item.number + ': ' + (item.short_address || item.name).slice(0, 40), 'success');
                                     }
                                 });
                             },
                     }">
                    
                    <?php $__currentLoopData = $shippingOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $key = $opt['code']; ?>
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer"
                               :class="method === '<?php echo e($key); ?>' ? 'border-[var(--gazu-ink)] bg-[var(--gazu-paper)]' : 'border-[var(--gazu-line)]'">
                            <input type="radio" name="shipping_method" value="<?php echo e($key); ?>" x-model="method" class="sr-only">
                            <span class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                  :class="method === '<?php echo e($key); ?>' ? 'border-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)]'">
                                <span x-show="method === '<?php echo e($key); ?>'" class="w-2 h-2 rounded-full bg-[var(--gazu-ink)]"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]"><?php echo e($opt['label']); ?></div>
                                <?php if(!empty($opt['desc'])): ?>
                                    <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e($opt['desc']); ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <div class="mt-2" x-show="method === 'novaposhta'" x-cloak>
                        <label class="block relative" @click.outside="cityOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Місто</span>
                            <input type="text" name="shipping_city" placeholder="Почніть вводити: Київ, Львів…"
                                   x-model="city" @input="onCityInput" @focus="city.length > 1 && fetchCities()"
                                   @keydown.enter.prevent="cityResults.length && selectCity(cityResults[0])"
                                   @keydown.escape="cityOpen = false"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_city_ref" :value="cityRef">
                            <div x-show="cityOpen && cityResults.length" x-cloak x-transition.opacity
                                 class="absolute z-30 left-0 right-0 mt-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 15rem; overflow-y: auto;">
                                <template x-for="item in cityResults" :key="item.ref">
                                    <button type="button" @click="selectCity(item)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]" x-text="item.name"></div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="item.area"></div>
                                    </button>
                                </template>
                            </div>
                        </label>
                    </div>

                    
                    <div x-show="method === 'novaposhta'" x-cloak class="mt-2">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                            <button type="button" @click="switchType('branch')"
                                    :class="type === 'branch' ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-[var(--gazu-ink)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'box','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'box','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Відділення
                            </button>
                            <button type="button" @click="switchType('postomat')"
                                    :class="type === 'postomat' ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-[var(--gazu-ink)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cube','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cube','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Поштомат
                            </button>
                            <button type="button" @click="switchType('np_courier')"
                                    :class="type === 'np_courier' ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-[var(--gazu-ink)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border-[var(--gazu-line)]'"
                                    class="px-3 py-2 border rounded-md text-sm font-medium flex items-center justify-center gap-2 transition">
                                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'truck','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Курʼєр
                            </button>
                        </div>
                        <input type="hidden" name="shipping_warehouse_type" :value="type">
                        <input type="hidden" name="shipping_warehouse_ref" :value="warehouseRef">

                        
                        <div x-show="type !== 'np_courier'" x-cloak x-data="{ view: 'list' }">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-[var(--gazu-graphite)] block" x-text="type === 'postomat' ? 'Поштомат' : 'Відділення / адреса'"></span>
                                <div class="flex gap-1 text-[11px]" x-show="warehouseResults.some(w => w.lat && w.lng)" x-cloak>
                                    <button type="button" @click="view = 'list'"
                                            :class="view === 'list' ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)]'"
                                            class="px-2 py-1 border border-[var(--gazu-line)] rounded">
                                        Список
                                    </button>
                                    <button type="button" @click="view = 'map'; $nextTick(() => $dispatch('np-map-render'))"
                                            :class="view === 'map' ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)]'"
                                            class="px-2 py-1 border border-[var(--gazu-line)] rounded">
                                        Мапа
                                    </button>
                                </div>
                            </div>

                            
                            <div x-show="view === 'map'" x-cloak class="border border-[var(--gazu-line)] rounded-md mb-2"
                                 style="height: 380px;"
                                 x-init="$watch('warehouseResults', () => { if (view === 'map') $dispatch('np-map-render'); })"
                                 wire:ignore>
                                <div id="gazu-np-map" style="height: 100%; width: 100%; background: #f0f0f0;"
                                     :data-warehouses="JSON.stringify(warehouseResults.filter(w => w.lat && w.lng).map(w => ({ref: w.ref, num: w.number, addr: w.short_address || w.name, lat: w.lat, lng: w.lng})))"
                                     :data-selected-ref="warehouseRef"></div>
                            </div>

                            
                            <label class="block relative" @click.outside="warehouseOpen = false" x-show="view === 'list'" x-cloak>
                                <input type="text" name="shipping_warehouse"
                                       :placeholder="type === 'postomat' ? '№ або адреса поштомата' : '№ або адреса відділення'"
                                       x-model="warehouse"
                                       @input="onWarehouseInput"
                                       @click="cityOpen = false; fetchWarehouses(true)"
                                       @focus="cityOpen = false; fetchWarehouses(true)"
                                       @keydown.escape="warehouseOpen = false"
                                       autocomplete="off"
                                       class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                <div x-show="warehouseOpen && warehouseResults.length" x-cloak x-transition.opacity
                                     class="absolute z-30 left-0 right-0 mt-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md shadow-xl"
                                     style="max-height: 18rem; overflow-y: auto;">
                                    <template x-for="item in warehouseResults" :key="item.ref">
                                        <button type="button" @click="selectWarehouse(item)"
                                                class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                            <div class="text-sm text-[var(--gazu-ink)]">
                                                <span class="gazu-mono text-[11px] text-[var(--gazu-blue)]" x-text="'#' + item.number"></span>
                                                <span x-text="item.name"></span>
                                            </div>
                                            <div class="text-xs text-[var(--gazu-graphite)]" x-text="item.short_address"></div>
                                        </button>
                                    </template>
                                </div>
                                <div x-show="!cityRef" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Спочатку оберіть місто зі списку
                                </div>
                                <div x-show="cityRef && !warehouseResults.length && !warehouseOpen && warehouse.length > 1" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Нічого не знайдено
                                </div>
                            </label>
                        </div>

                        
                        <div x-show="type === 'np_courier'" x-cloak class="space-y-3">
                            <label class="block relative" @click.outside="streetOpen = false">
                                <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Вулиця</span>
                                <input type="text" name="shipping_street"
                                       x-model="street"
                                       @input="onStreetInput"
                                       placeholder="Почніть вводити назву…"
                                       autocomplete="off"
                                       class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                <input type="hidden" name="shipping_street_ref" :value="streetRef">
                                <div x-show="streetOpen && streetResults.length" x-cloak x-transition.opacity
                                     class="absolute z-30 left-0 right-0 mt-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md shadow-xl"
                                     style="max-height: 14rem; overflow-y: auto;">
                                    <template x-for="item in streetResults" :key="item.ref">
                                        <button type="button" @click="selectStreet(item)"
                                                class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                            <span class="text-sm text-[var(--gazu-ink)]" x-text="item.name"></span>
                                        </button>
                                    </template>
                                </div>
                                <div x-show="!cityRef" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                    Спершу оберіть місто
                                </div>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Будинок</span>
                                    <input type="text" name="shipping_house" value="<?php echo e(old('shipping_house', '')); ?>"
                                           placeholder="15"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Квартира</span>
                                    <input type="text" name="shipping_apartment" value="<?php echo e(old('shipping_apartment', '')); ?>"
                                           placeholder="23"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Поверх</span>
                                    <input type="number" name="shipping_floor" value="<?php echo e(old('shipping_floor', '')); ?>"
                                           min="1" max="50" placeholder="3"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" name="shipping_has_elevator" value="1" <?php echo e(old('shipping_has_elevator') ? 'checked' : ''); ?>

                                       class="w-4 h-4 border border-[var(--gazu-line)] rounded">
                                <span class="text-[var(--gazu-ink)] font-medium">Є ліфт</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Бажана дата</span>
                                    <input type="date" name="shipping_preferred_date"
                                           min="<?php echo e(now()->addDay()->toDateString()); ?>"
                                           value="<?php echo e(old('shipping_preferred_date', '')); ?>"
                                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Бажаний час</span>
                                    <select name="shipping_preferred_time"
                                            class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none bg-[var(--gazu-surface)]">
                                        <option value="">— Будь-який —</option>
                                        <option value="09:00-14:00">9:00 — 14:00</option>
                                        <option value="14:00-18:00">14:00 — 18:00</option>
                                        <option value="18:00-22:00">18:00 — 22:00</option>
                                    </select>
                                </label>
                            </div>
                            <div class="text-xs text-[var(--gazu-muted)]">
                                Курʼєр Нової Пошти доставить замовлення на вказану адресу. Ціна — за тарифом НП.
                            </div>
                        </div>

                        
                        <div x-show="cityRef && (shippingCost !== null || shippingLoading)" x-cloak
                             class="mt-3 p-3 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded-md flex items-center justify-between text-sm">
                            <div>
                                <span class="text-[var(--gazu-graphite)]">Вартість доставки:</span>
                                <span class="font-bold text-[var(--gazu-ink)]" x-show="!shippingLoading && shippingCost !== null"
                                      x-text="shippingCost + ' ₴'"></span>
                                <span x-show="shippingLoading" class="text-[var(--gazu-muted)]">розрахунок…</span>
                            </div>
                            <div x-show="shippingDate" class="text-xs text-[var(--gazu-graphite)]">
                                <span>Прибуде:</span>
                                <span class="gazu-mono font-medium" x-text="shippingDate"></span>
                                <span x-show="shippingDays" x-text="'(~' + shippingDays + ' дн.)'"></span>
                            </div>
                        </div>
                    </div>

                    
                    <div x-show="method === 'ukrposhta'" x-cloak class="mt-2 space-y-3"
                         x-data="{
                            up: {
                                city: <?php echo \Illuminate\Support\Js::from(old('shipping_up_city', ''))->toHtml() ?>,
                                cityId: <?php echo \Illuminate\Support\Js::from(old('shipping_up_city_id', ''))->toHtml() ?>,
                                cityResults: [], cityOpen: false, cityTimer: null,
                                office: <?php echo \Illuminate\Support\Js::from(old('shipping_up_office', ''))->toHtml() ?>,
                                officeId: <?php echo \Illuminate\Support\Js::from(old('shipping_up_office_id', ''))->toHtml() ?>,
                                officeResults: [], officeOpen: false,
                                async fetchCities() {
                                    if (this.city.length < 2) { this.cityResults = []; this.cityOpen = false; return; }
                                    const r = await fetch('<?php echo e(route('gazu.api.up.cities')); ?>?q=' + encodeURIComponent(this.city), { cache: 'no-store' });
                                    const d = await r.json();
                                    this.cityResults = d.items || [];
                                    this.cityOpen = this.cityResults.length > 0;
                                },
                                onCityInput() { this.cityId = ''; this.officeResults = []; this.office = ''; this.officeId = ''; clearTimeout(this.cityTimer); this.cityTimer = setTimeout(() => this.fetchCities(), 250); },
                                selectCity(c) { this.city = c.name; this.cityId = c.id; this.cityOpen = false; this.fetchOffices(); },
                                async fetchOffices() {
                                    if (!this.cityId) return;
                                    const r = await fetch('<?php echo e(route('gazu.api.up.post-offices')); ?>?city_id=' + this.cityId, { cache: 'no-store' });
                                    const d = await r.json();
                                    this.officeResults = d.items || [];
                                    this.officeOpen = this.officeResults.length > 0;
                                },
                                selectOffice(o) { this.office = '№' + (o.postcode || '') + ' · ' + (o.address || o.name); this.officeId = o.id; this.officeOpen = false; document.querySelector('input[name=shipping_postcode]').value = o.postcode || ''; }
                            }
                         }">
                        <label class="block relative" @click.outside="up.cityOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Місто</span>
                            <input type="text" name="shipping_up_city" placeholder="Почніть вводити: Київ, Львів…"
                                   x-model="up.city" @input="up.onCityInput()" @focus="up.city.length > 1 && up.fetchCities()"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_up_city_id" :value="up.cityId">
                            <div x-show="up.cityOpen && up.cityResults.length" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 15rem; overflow-y: auto;">
                                <template x-for="c in up.cityResults" :key="c.id">
                                    <button type="button" @click="up.selectCity(c)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]" x-text="c.name"></div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="c.region"></div>
                                    </button>
                                </template>
                            </div>
                        </label>

                        <label class="block relative" @click.outside="up.officeOpen = false">
                            <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Відділення</span>
                            <input type="text" name="shipping_up_office"
                                   x-model="up.office"
                                   @click="up.cityId && up.fetchOffices()"
                                   placeholder="№ або адреса відділення УП"
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                            <input type="hidden" name="shipping_up_office_id" :value="up.officeId">
                            <div x-show="up.officeOpen && up.officeResults.length" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md shadow-xl"
                                 style="max-height: 18rem; overflow-y: auto;">
                                <template x-for="o in up.officeResults" :key="o.id">
                                    <button type="button" @click="up.selectOffice(o)"
                                            class="w-full text-left px-3 py-2 hover:bg-[var(--gazu-paper)] border-b border-[var(--gazu-line)] last:border-b-0">
                                        <div class="text-sm text-[var(--gazu-ink)]">
                                            <span class="gazu-mono text-[11px] text-[var(--gazu-blue)]" x-text="'№' + (o.postcode || '?')"></span>
                                            <span x-text="o.name || o.address"></span>
                                        </div>
                                        <div class="text-xs text-[var(--gazu-graphite)]" x-text="o.address"></div>
                                    </button>
                                </template>
                            </div>
                            <div x-show="!up.cityId" x-cloak class="text-[11px] text-[var(--gazu-muted)] mt-1">
                                Спочатку оберіть місто
                            </div>
                        </label>
                        <input type="hidden" name="shipping_postcode" :value="up.officeResults.find(o => o.id === up.officeId)?.postcode || ''">
                    </div>

                    
                    <div x-show="method === 'pickup'" x-cloak class="mt-2 p-3 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded-md text-sm">
                        <div class="flex items-start gap-2">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'store','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'store','size' => '16']); ?>
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
                                <div class="font-medium text-[var(--gazu-ink)]"><?php echo e(\App\Models\DisplaySetting::get('gazu_pickup_address', 'м. Київ, вул. Промислова, 25')); ?></div>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-1"><?php echo e(\App\Models\DisplaySetting::get('gazu_pickup_hours', 'Пн–Пт: 9:00–18:00, Сб: 10:00–15:00')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]">3</div>
                    <h3 class="gazu-display text-lg font-semibold m-0">Спосіб оплати</h3>
                </div>
                <?php $defaultPm = old('payment_method', $paymentMethods[0]['code'] ?? 'card'); ?>
                
                <div class="grid gap-2 pl-11" x-data="{ pm: <?php echo \Illuminate\Support\Js::from($defaultPm)->toHtml() ?> }">
                    <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pmOpt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $key = $pmOpt['code'];
                            $fee = (float) ($pmOpt['fee'] ?? 0);
                            $feeNote = $fee > 0
                                ? ' · доплата '.rtrim(rtrim(number_format($fee, 2, '.', ''), '0'), '.').'%'
                                : '';
                        ?>
                        <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer"
                               :class="pm === '<?php echo e($key); ?>' ? 'border-[var(--gazu-ink)] bg-[var(--gazu-paper)]' : 'border-[var(--gazu-line)]'">
                            <input type="radio" name="payment_method" value="<?php echo e($key); ?>" x-model="pm" class="sr-only">
                            <span class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                  :class="pm === '<?php echo e($key); ?>' ? 'border-[var(--gazu-ink)]' : 'border-[var(--gazu-line-2)]'">
                                <span x-show="pm === '<?php echo e($key); ?>'" class="w-2 h-2 rounded-full bg-[var(--gazu-ink)]"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]"><?php echo e($pmOpt['label']); ?></div>
                                <?php if(!empty($pmOpt['desc']) || $feeNote): ?>
                                    <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e($pmOpt['desc']); ?><?php echo e($feeNote); ?></div>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <?php if($cf['comment']['visible']): ?>
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-[var(--gazu-line)] text-[var(--gazu-graphite)]">4</div>
                    <h3 class="gazu-display text-lg font-semibold m-0"><?php echo e($cf['comment']['label']); ?></h3>
                </div>
                <div class="pl-11">
                    <textarea name="note" rows="3" <?php if($cf['comment']['required']): ?> required <?php endif; ?> placeholder="<?php echo e($cf['comment']['placeholder']); ?>"
                              class="w-full px-3 py-2.5 border <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-[var(--gazu-danger)] <?php else: ?> border-[var(--gazu-line)] <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-md outline-none"><?php echo e(old('note')); ?></textarea>
                    <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-[var(--gazu-danger)] mt-1 block"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($cfBelowMin): ?>
                <div class="p-3 rounded-md text-sm text-center bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)]">
                    Мінімальна сума замовлення — <?php echo e(number_format($cfMinOrder, 0, '.', ' ')); ?> ₴.
                    Додайте товарів ще на <?php echo e(number_format($cfMinOrder - $cartTotal, 0, '.', ' ')); ?> ₴.
                </div>
                <button type="submit" class="gazu-btn-primary py-4 text-base opacity-50 cursor-not-allowed" disabled>
                    Оформити замовлення на <?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴
                </button>
            <?php else: ?>
                <button type="submit" class="gazu-btn-primary py-4 text-base">
                    Оформити замовлення на <?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴
                </button>
            <?php endif; ?>
            <p class="text-xs text-[var(--gazu-graphite)] text-center">
                Натискаючи кнопку, ви погоджуєтесь з <a href="#" class="text-[var(--gazu-blue)]">умовами публічної оферти</a>.
            </p>
        </div>

        
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 lg:sticky lg:top-4 self-start">
            <h3 class="gazu-display text-lg font-semibold m-0 mb-4">Ваше замовлення</h3>
            <div class="flex flex-col gap-3 mb-4 max-h-[400px] overflow-y-auto">
                <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                        $price = (float) ($item['price'] ?? 0);
                        $qty = (int) ($item['quantity'] ?? 1);
                        $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                        $img = $item['image'] ?? null;
                        $hasReal = $img && ! \Illuminate\Support\Str::contains((string) $img, 'default-product');
                        $imgUrl = $hasReal ? (\Illuminate\Support\Str::startsWith($img, 'http') ? $img : asset('storage/'.ltrim((string) $img, '/storage/'))) : null;
                    ?>
                    <div class="flex gap-3 items-center group" x-data="{ removing: false }">
                        <div class="w-12 h-12 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0 overflow-hidden">
                            <?php if($imgUrl): ?>
                                <img src="<?php echo e($imgUrl); ?>" alt="" class="w-12 h-12 object-contain">
                            <?php else: ?>
                                <?php if (isset($component)) { $__componentOriginalb3ce7faecba1472bd9053bf57696fe20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-placeholder','data' => ['name' => $title,'seed' => $productId,'class' => 'w-12 h-12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-placeholder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'class' => 'w-12 h-12']); ?>
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
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] text-[var(--gazu-ink)] truncate"><?php echo e($title); ?></div>
                            <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono"><?php echo e($qty); ?> × <?php echo e(number_format($price, 0, '.', ' ')); ?> ₴</div>
                        </div>
                        <div class="gazu-display font-bold text-sm text-[var(--gazu-ink)] whitespace-nowrap"><?php echo e(number_format($price * $qty, 0, '.', ' ')); ?> ₴</div>
                        <button type="button" :disabled="removing"
                                @click.prevent="
                                    if (removing) return;
                                    removing = true;
                                    fetch('<?php echo e(route('gazu.cart.remove')); ?>', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                                        body: new URLSearchParams({ product_id: '<?php echo e($productId); ?>' })
                                    }).then(r => r.json()).then(d => {
                                        if (d.ok) {
                                            window.location.reload();
                                        } else {
                                            removing = false;
                                            window.gazuToast && window.gazuToast(d.message || 'Не вдалося видалити', 'error');
                                        }
                                    }).catch(() => {
                                        removing = false;
                                        window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error');
                                    });
                                "
                                title="Видалити з замовлення"
                                class="w-7 h-7 rounded-md text-[var(--gazu-muted)] hover:text-[var(--gazu-danger)] hover:bg-[var(--gazu-danger-bg)] cursor-pointer inline-flex items-center justify-center bg-transparent border-0 opacity-50 group-hover:opacity-100 transition-all disabled:opacity-30 disabled:cursor-wait shrink-0">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-show="!removing"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" x-show="removing" x-cloak class="animate-spin"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="h-px bg-[var(--gazu-line)] my-3"></div>
            <div x-data="{
                    base: <?php echo e((float) $cartTotal); ?>,
                    discount: <?php echo e((int) ($appliedCoupon['discount'] ?? 0)); ?>,
                    couponCode: <?php echo \Illuminate\Support\Js::from($appliedCoupon['code'] ?? '')->toHtml() ?>,
                    shippingCost: null,
                    shippingMethod: <?php echo \Illuminate\Support\Js::from($defaultShippingMethod)->toHtml() ?>,
                    promoOpen: false,
                    promoBusy: false,
                    promoInput: '',
                    fmt(n) { return Math.round(n).toLocaleString('uk-UA').replace(/,/g,' '); },
                    get total() { return Math.max(0, this.base - this.discount) + (this.shippingCost || 0); },
                    async applyPromo() {
                        if (!this.promoInput.trim() || this.promoBusy) return;
                        this.promoBusy = true;
                        try {
                            const r = await fetch('<?php echo e(route('gazu.cart.coupon.apply')); ?>', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                                body: new URLSearchParams({ code: this.promoInput.trim() })
                            });
                            const d = await r.json();
                            if (d.ok) {
                                this.discount = d.discount || 0;
                                this.couponCode = this.promoInput.trim();
                                this.promoInput = '';
                                this.promoOpen = false;
                                window.gazuToast && window.gazuToast(d.message || 'Промокод застосовано · -' + this.fmt(this.discount) + ' ₴', 'success');
                            } else {
                                window.gazuToast && window.gazuToast(d.message || 'Промокод не знайдено', 'error');
                            }
                        } catch (e) {
                            window.gazuToast && window.gazuToast('Помилка', 'error');
                        } finally { this.promoBusy = false; }
                    },
                    async removePromo() {
                        if (this.promoBusy) return;
                        this.promoBusy = true;
                        try {
                            await fetch('<?php echo e(route('gazu.cart.coupon.remove')); ?>', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                            });
                            this.discount = 0;
                            this.couponCode = '';
                            window.gazuToast && window.gazuToast('Промокод видалено', 'info');
                        } catch (e) {}
                        finally { this.promoBusy = false; }
                    },
                    get shippingLabel() {
                        if (this.shippingMethod === 'pickup') return 'Безкоштовно';
                        if (this.shippingCost === null) return 'розрахунок при отриманні';
                        return this.fmt(this.shippingCost) + ' ₴';
                    },
                    flash(refName) {
                        const el = this.$refs[refName];
                        if (!el) return;
                        el.setAttribute('data-changed', '0');
                        void el.offsetWidth;
                        el.setAttribute('data-changed', '1');
                        setTimeout(() => el.setAttribute('data-changed', '0'), 450);
                    },
                    init() {
                        this.$watch('total', () => this.flash('totalEl'));
                        this.$watch('shippingCost', () => this.flash('shipEl'));
                    }
                 }"
                 @gazu-shipping.window="shippingCost = $event.detail.cost; shippingMethod = $event.detail.method">
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Сума</span>
                    <span x-text="fmt(base) + ' ₴'"><?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴</span>
                </div>
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Доставка</span>
                    <span x-ref="shipEl"
                          :class="shippingCost !== null && shippingMethod !== 'pickup' ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]'"
                          class="gazu-count-up"
                          x-text="shippingLabel">розрахунок при отриманні</span>
                </div>

                <?php if($cfFreeShip > 0): ?>
                    <?php if((float) $cartTotal >= $cfFreeShip): ?>
                        <div class="text-xs text-[var(--gazu-success)] font-medium mb-2">✓ Безкоштовна доставка від <?php echo e(number_format($cfFreeShip, 0, '.', ' ')); ?> ₴</div>
                    <?php else: ?>
                        <div class="text-xs text-[var(--gazu-graphite)] mb-2">Додайте на <?php echo e(number_format($cfFreeShip - $cartTotal, 0, '.', ' ')); ?> ₴ — і доставка безкоштовна</div>
                    <?php endif; ?>
                <?php endif; ?>

                
                <?php if($cfPromo && module('coupons')->enabled()): ?>
                <div class="my-3 pt-3 border-t border-[var(--gazu-line)]">
                    <template x-if="couponCode && discount > 0">
                        <div class="flex justify-between items-center mb-2">
                            <div class="text-sm">
                                <span class="text-[var(--gazu-success)] font-medium">Промокод <code class="gazu-mono text-[12px]" x-text="couponCode"></code></span>
                                <button type="button" @click="removePromo()" class="ml-1.5 text-[11px] text-[var(--gazu-muted)] hover:text-[var(--gazu-danger)] cursor-pointer bg-transparent border-0 underline">прибрати</button>
                            </div>
                            <span class="text-[var(--gazu-success)] font-medium text-sm">−<span x-text="fmt(discount)"></span> ₴</span>
                        </div>
                    </template>
                    <template x-if="!couponCode || discount === 0">
                        <div>
                            <button type="button" @click="promoOpen = !promoOpen" class="w-full flex items-center justify-between text-sm text-[var(--gazu-ink)] bg-transparent border-0 cursor-pointer p-0">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    Маєте промокод?
                                </span>
                                <svg :class="promoOpen ? 'rotate-180' : ''" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="promoOpen" x-cloak x-collapse class="mt-2.5 flex gap-2">
                                <input type="text" x-model="promoInput" @keydown.enter.prevent="applyPromo()" placeholder="Введіть код"
                                       class="flex-1 px-3 py-2 border border-[var(--gazu-line)] rounded-md text-sm focus:border-[var(--gazu-ink)] outline-none gazu-mono uppercase">
                                <button type="button" @click="applyPromo()" :disabled="promoBusy || !promoInput.trim()"
                                        class="px-3.5 py-2 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[var(--gazu-ink-2)] transition-colors">
                                    <span x-show="!promoBusy">OK</span>
                                    <svg x-show="promoBusy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <?php endif; ?>

                <div class="h-px bg-[var(--gazu-line)] my-3"></div>
                <div class="flex justify-between items-baseline">
                    <span class="font-medium text-[var(--gazu-ink)]">До сплати</span>
                    <span x-ref="totalEl"
                          class="gazu-display text-2xl font-bold text-[var(--gazu-ink)] gazu-count-up"
                          x-text="fmt(total) + ' ₴'"><?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴</span>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/checkout.blade.php ENDPATH**/ ?>