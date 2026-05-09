<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Офлайн | SimpleShop</title>
    <meta name="theme-color" content="#000000">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            text-align: center;
            max-width: 480px;
        }
        .icon {
            font-size: 5rem;
            margin-bottom: 2rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 2rem;
            color: #333;
        }
        .btn {
            display: inline-block;
            background: #000;
            color: #fff;
            font-weight: 900;
            font-size: 0.875rem;
            text-transform: uppercase;
            padding: 1rem 2rem;
            border: 4px solid #000;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .btn:hover {
            background: #fff;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📡</div>
        <h1>Немає з'єднання</h1>
        <p>Схоже, ви офлайн. Перевірте підключення до інтернету та спробуйте ще раз.</p>
        <button class="btn" onclick="window.location.reload()">СПРОБУВАТИ ЗНОВУ</button>
    </div>
</body>
</html>
