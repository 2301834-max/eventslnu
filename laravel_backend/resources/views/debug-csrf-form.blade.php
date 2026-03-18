<!DOCTYPE html>
<html>
<head>
    <title>CSRF Debug Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .info { background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 3px; font-size: 12px; font-family: monospace; }
        input, button { display: block; width: 100%; margin: 10px 0; padding: 10px; font-size: 14px; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>CSRF Token Debug Test</h2>
        
        <div class="info">
            <strong>Session ID:</strong> {{ $sessionId }}<br>
            <strong>CSRF Token:</strong> {{ substr($csrfToken, 0, 20) }}...<br>
            <strong>Method:</strong> file<br>
            <strong>Timestamp:</strong> {{ now() }}
        </div>

        <p>This form tests if CSRF token is properly submitted and validated.</p>

        <form method="POST" action="/debug/csrf-post">
            @csrf
            
            <input type="hidden" name="test" value="csrf_test">
            <button type="submit">Submit CSRF Test</button>
        </form>

        <h3>Check the logs:</h3>
        <code>docker exec lnusystem_app tail -30 storage/logs/laravel.log</code>
    </div>
</body>
</html>
