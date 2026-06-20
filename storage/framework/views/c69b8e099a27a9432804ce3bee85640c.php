<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Технічне обслуговування — GAZU</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
            font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
            background:#0f172a;color:#e2e8f0;padding:24px;text-align:center}
        .card{max-width:520px}
        .logo{font-weight:800;font-size:28px;letter-spacing:-.02em;color:#fff;margin-bottom:24px}
        .logo span{color:#3b82f6}
        .icon{width:64px;height:64px;margin:0 auto 20px;color:#3b82f6}
        h1{font-size:24px;margin:0 0 12px;color:#fff}
        p{font-size:16px;line-height:1.6;color:#94a3b8;margin:0 0 8px}
        .muted{font-size:13px;color:#64748b;margin-top:24px}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">GA<span>ZU</span></div>
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
        </svg>
        <h1>Технічне обслуговування</h1>
        <p><?php echo e($message ?? 'Сайт тимчасово недоступний. Зайдіть трохи пізніше.'); ?></p>
        <p class="muted">Дякуємо за розуміння.</p>
    </div>
</body>
</html>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/maintenance.blade.php ENDPATH**/ ?>