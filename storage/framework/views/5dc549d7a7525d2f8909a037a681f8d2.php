<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => null]));

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

foreach (array_filter((['items' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $items = $items ?? ($gazuSettings['gazu_trust_items'] ?? null);
    if (empty($items) || !is_array($items)) {
        $items = [
            ['icon' => 'truck',  'title' => 'Доставка по Україні', 'desc' => '1–3 дні · Нова Пошта · Укрпошта', 'accent' => 'blue'],
            ['icon' => 'shield', 'title' => 'Гарантія на запчастини', 'desc' => 'Від 6 до 24 місяців', 'accent' => 'green'],
            ['icon' => 'return', 'title' => 'Повернення', 'desc' => '14 днів без пояснення причин', 'accent' => 'warn'],
            ['icon' => 'headset', 'title' => 'Допомога з підбором', 'desc' => 'Менеджер передзвонить за 5 хв', 'accent' => 'purple'],
        ];
    }

    // Inline SVG icons — preimageously кращі за gazu.icon component для цього компоненту
    $svgFor = function (string $key): string {
        return match ($key) {
            'truck' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v10H3z"/><path d="M14 10h4l3 3v4h-7"/><circle cx="6.5" cy="17.5" r="2"/><circle cx="17.5" cy="17.5" r="2"/><path d="M3 11h11" opacity="0.4"/></svg>',
            'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v7c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V5l-8-3Z"/><path d="m9 12 2.2 2.2L15 10.5"/></svg>',
            'return' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><polyline points="3 4 3 9 8 9"/></svg>',
            'wrench' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a5 5 0 0 0-1 5.7l-8.4 8.4a1.5 1.5 0 1 0 2.1 2.1l8.4-8.4a5 5 0 1 0 5-5l-3.1 3.1-2.7-.7-.7-2.7 3.1-3.1a5 5 0 0 0-2.7 0Z"/></svg>',
            'headset' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M4 14h3v6H4a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1Z"/><path d="M20 14h-3v6h3a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1Z"/><path d="M17 20a4 4 0 0 1-4 4h-1"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };

    $accentClass = function (string $a): array {
        return match ($a) {
            'blue'   => ['from' => 'from-blue-500', 'to' => 'to-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'glow' => 'rgba(37,99,235,0.25)'],
            'green'  => ['from' => 'from-emerald-500', 'to' => 'to-teal-600', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'glow' => 'rgba(5,150,105,0.25)'],
            'warn'   => ['from' => 'from-amber-500', 'to' => 'to-orange-600', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'glow' => 'rgba(217,119,6,0.25)'],
            'purple' => ['from' => 'from-violet-500', 'to' => 'to-purple-600', 'bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'glow' => 'rgba(124,58,237,0.25)'],
            default  => ['from' => 'from-slate-500', 'to' => 'to-slate-700', 'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'glow' => 'rgba(71,85,105,0.25)'],
        };
    };
?>
<?php if(! empty($items)): ?>
<section class="bg-gradient-to-b from-[var(--gazu-paper)] to-white py-8 sm:py-10">
    <div class="gazu-container">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $accent = $accentClass($it['accent'] ?? 'blue');
                    $icon = $it['icon'] ?? 'shield';
                ?>
                <div class="group relative bg-white border border-[var(--gazu-line)] rounded-xl p-4 sm:p-5 hover:border-transparent transition-all duration-300 overflow-hidden"
                     style="--glow: <?php echo e($accent['glow']); ?>;"
                     onmouseover="this.style.boxShadow='0 12px 28px -8px '+getComputedStyle(this).getPropertyValue('--glow')"
                     onmouseout="this.style.boxShadow='none'">
                    
                    <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-gradient-to-br <?php echo e($accent['from']); ?> <?php echo e($accent['to']); ?> opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>

                    
                    <div class="relative inline-flex w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br <?php echo e($accent['from']); ?> <?php echo e($accent['to']); ?> items-center justify-center text-white mb-3 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-[0_4px_12px_-4px_rgba(0,0,0,0.15)]">
                        <div class="w-5 h-5 sm:w-6 sm:h-6"><?php echo $svgFor($icon); ?></div>
                    </div>

                    <div class="text-[14px] sm:text-[15px] font-semibold text-[var(--gazu-ink)] leading-snug mb-1"><?php echo e($it['title'] ?? ''); ?></div>
                    <div class="text-[11px] sm:text-[12px] text-[var(--gazu-graphite)] leading-snug"><?php echo e($it['desc'] ?? ''); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/trust-strip.blade.php ENDPATH**/ ?>