<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Test - SimpleShop</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test-card { background: white; padding: 20px; border-radius: 10px; margin: 10px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { border-left: 5px solid #28a745; }
        .info { border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="test-card success">
        <h1>🎉 Мобільний доступ працює!</h1>
        <p><strong>IP:</strong> {{ request()->ip() }}</p>
        <p><strong>User Agent:</strong> {{ request()->userAgent() }}</p>
        <p><strong>URL:</strong> {{ request()->fullUrl() }}</p>
        <p><strong>Час:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
    
    <div class="test-card info">
        <h2>🔗 Тестові посилання:</h2>
        <p><a href="{{ url('/') }}">Головна сторінка</a></p>
        <p><a href="{{ url('/smartphones') }}">Смартфони</a></p>
        <p><a href="{{ url('/clothing') }}">Одяг</a></p>
    </div>

    <div class="test-card info">
        <h2>🛠 Тест CSRF:</h2>
        <form method="POST" action="{{ url('/mobile-test') }}">
            @csrf
            <input type="text" name="test" placeholder="Тестове поле" style="padding: 10px; margin: 5px;">
            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px;">Відправити</button>
        </form>
        
        @if(request()->isMethod('post'))
            <p style="color: green;">✅ CSRF тест пройшов успішно! Дані: {{ request('test') }}</p>
        @endif
    </div>
</body>
</html>