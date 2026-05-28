<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8"/>
    <title>Safe mode applied — SimpleShop</title>
    <style>
        body { font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; max-width: 640px; margin: 60px auto; padding: 0 20px; color: #0e1b2c; }
        h1 { font-size: 22px; margin-bottom: 8px; }
        p { color: #5a6c80; }
        ul { list-style: none; padding: 12px 16px; margin: 16px 0; background: #f7f8fa; border: 1px solid #e5e8ec; border-radius: 8px; }
        li { font-family: ui-monospace, monospace; font-size: 12px; padding: 2px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #92400e; }
        a { color: #0e1b2c; }
    </style>
</head>
<body>
    <span class="badge">SAFE MODE</span>
    <h1>✓ Сайт повернутий до робочого стану</h1>
    <p>Вимкнено <strong>{{ $count }}</strong> модул{{ $count === 1 ? 'ь' : 'ів' }}. Дані в БД залишилися — увімкнення поверне функціональність.</p>

    @if($count > 0)
        <ul>
            @foreach($disabled as $key)
                <li>○ {{ $key }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        <a href="/admin/modules">Перейти в адмінку модулів</a> щоб виявити який модуль зламав сайт і поетапно увімкнути.
    </p>
    <p style="color: #9ca3af; font-size: 12px; margin-top: 32px;">
        Core-модулі (multi_warehouse) не торкаються — без них магазин не працює.
    </p>
</body>
</html>
