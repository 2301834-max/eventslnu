<!DOCTYPE html>
<html>
<head>
    <title>Debug Login Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        input, button { display: block; width: 100%; margin: 10px 0; padding: 10px; font-size: 14px; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .info { background: #e3f2fd; padding: 10px; border-radius: 3px; margin: 10px 0; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Debug Admin Login Test</h2>
        
        <div class="info">
            <strong>Session ID:</strong> {{ session()->getId() }}<br>
            <strong>CSRF Token (hidden):</strong> {{ substr(csrf_token(), 0, 10) }}...<br>
            <strong>Session Driver:</strong> {{ config('session.driver') }}<br>
            <strong>Cookie Name:</strong> {{ config('session.cookie') }}
        </div>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            
            <label>Email:</label>
            <input type="email" name="email" value="admin@lnusystem.local" required>
            
            <label>Password:</label>
            <input type="password" name="password" value="password123" required>
            
            <button type="submit">Login</button>
        </form>

        @if($errors->any())
            <div style="background: #ffebee; padding: 10px; border-radius: 3px; color: red;">
                <strong>Errors:</strong>
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
