<!DOCTYPE html>
<html>
<head>
    <title>CSRF Test Form</title>
</head>
<body>
    <h1>CSRF Test Form</h1>
    <form method="POST" action="/test/csrf-post">
        @csrf
        <button type="submit">Test CSRF</button>
    </form>
    
    <div>
        <h2>Debug Info:</h2>
        <p>Session ID: <code>{{ session()->getId() }}</code></p>
        <p>CSRF Token: <code>{{ csrf_token() }}</code></p>
        <p>Session Driver: <code>{{ config('session.driver') }}</code></p>
    </div>
</body>
</html>
