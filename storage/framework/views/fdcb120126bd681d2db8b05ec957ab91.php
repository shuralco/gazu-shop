
<?php
    $custom = \App\Models\DisplaySetting::get('gazu_logo');
    if ($custom) {
        $custom = \Illuminate\Support\Str::startsWith($custom, ['http://', 'https://'])
            ? $custom
            : (\Illuminate\Support\Str::startsWith($custom, '/')
                ? url($custom)
                : asset('storage/'.ltrim($custom, '/')));
    }
    $brand = \App\Models\DisplaySetting::get('gazu_brand_name') ?: 'GAZU';
?>
<?php if($custom): ?>
    <img src="<?php echo e($custom); ?>" alt="<?php echo e($brand); ?>"
         style="height:32px;width:auto;display:block;object-fit:contain;">
<?php else: ?>
    <span style="display:inline-flex;align-items:center;gap:8px;line-height:1;">
        <svg width="30" height="30" viewBox="0 0 100 100" fill="none" style="display:block;flex-shrink:0;">
            <polygon points="50,6 90,28 90,72 50,94 10,72 10,28"
                     fill="#2453A6" stroke="#2453A6" stroke-width="6" stroke-linejoin="round"/>
            <text x="50" y="64" text-anchor="middle"
                  font-family="'Archivo Black','Space Grotesk',sans-serif" font-weight="900"
                  font-size="36" fill="#ffffff" letter-spacing="-2">GZ</text>
        </svg>
        <span style="font-weight:900;font-size:20px;letter-spacing:-0.04em;text-transform:uppercase;color:currentColor;"><?php echo e(\Illuminate\Support\Str::upper($brand)); ?></span>
    </span>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/partials/brand-logo.blade.php ENDPATH**/ ?>