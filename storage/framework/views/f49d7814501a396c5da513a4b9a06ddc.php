<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'modal-' . uniqid(),
    'title' => null,
    'size' => 'md',     // sm | md | lg | xl
    'show' => false,    // initial state; usually controlled via Alpine $store
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
    'id' => 'modal-' . uniqid(),
    'title' => null,
    'size' => 'md',     // sm | md | lg | xl
    'show' => false,    // initial state; usually controlled via Alpine $store
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
    $sizeCls = $sizes[$size] ?? $sizes['md'];
?>

<div
    id="<?php echo e($id); ?>"
    x-data="{ open: <?php echo \Illuminate\Support\Js::from($show)->toHtml() ?> }"
    x-show="open"
    @keydown.escape.window="open = false"
    @open-modal.window="if ($event.detail?.id === '<?php echo e($id); ?>') open = true"
    @close-modal.window="if ($event.detail?.id === '<?php echo e($id); ?>') open = false"
    x-cloak
    style="display: none; position: fixed; inset: 0; z-index: var(--z-modal, 7000);"
    aria-modal="true"
    role="dialog"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-black/50"
        @click="open = false"
    ></div>

    <!-- Panel -->
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="card-ui card-ui--padded card-ui--elevated w-full <?php echo e($sizeCls); ?> pointer-events-auto"
        >
            <?php if($title): ?>
                <div class="flex items-start justify-between mb-4 gap-4">
                    <h3 class="text-xl font-bold" style="color:var(--color-fg);"><?php echo e($title); ?></h3>
                    <button
                        type="button"
                        @click="open = false"
                        class="text-2xl leading-none -mt-1 -mr-1 px-2 hover:opacity-70 transition-opacity"
                        aria-label="Закрити"
                    >×</button>
                </div>
            <?php endif; ?>

            <div>
                <?php echo e($slot); ?>

            </div>

            <?php if(isset($footer)): ?>
                <div class="mt-6 flex items-center justify-end gap-2">
                    <?php echo e($footer); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/modal.blade.php ENDPATH**/ ?>