<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['qty' => 0, 'status' => null]));

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

foreach (array_filter((['qty' => 0, 'status' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // Явний статус наявності з довідника StockStatus (key). Якщо заданий —
    // він перекриває стару логіку від кількості. Інакше — fallback на qty.
    $st = $status ? \App\Models\StockStatus::byKey($status) : null;
    // НАЯВНІСТЬ ЗІ СКЛАДУ: якщо залишку немає (qty<=0), але статус каже «в
    // наявності» (InStock / без availability) — це суперечність (склад порожній,
    // кнопка все одно «Під замовлення»). Ігноруємо такий статус → впаде у
    // qty-логіку нижче і покаже «Під замовлення». Адмін не мусить вручну
    // синхронити stock_status зі складськими залишками.
    if ($qty <= 0 && $st && in_array($st->availability, ['InStock', null], true)) {
        $st = null;
    }
    $colorVar = [
        'success' => '--gazu-success',
        'warning' => '--gazu-warn',
        'danger'  => '--gazu-danger',
        'info'    => '--gazu-primary',
        'primary' => '--gazu-primary',
        'gray'    => '--gazu-muted',
    ][$st->color ?? 'gray'] ?? '--gazu-muted';
?>
<?php if($st): ?>
    <span class="text-xs inline-flex items-center gap-1 whitespace-nowrap" style="color:var(<?php echo e($colorVar); ?>)">
        <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:var(<?php echo e($colorVar); ?>)"></span> <?php echo e($st->label); ?>

    </span>
<?php elseif($qty > 5): ?>
    <span class="text-xs text-[var(--gazu-success)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-success)] shrink-0"></span> В наявності
    </span>
<?php elseif($qty > 0): ?>
    <span class="text-xs text-[var(--gazu-warn)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-warn)] shrink-0"></span> Залишилось <?php echo e($qty); ?>

    </span>
<?php else: ?>
    <span class="text-xs text-[var(--gazu-muted)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-muted)] shrink-0"></span> Під замовлення
    </span>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/stock.blade.php ENDPATH**/ ?>