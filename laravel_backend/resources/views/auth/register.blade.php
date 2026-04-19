<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LNU Event Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --lnu-blue: #0047ab;
            --lnu-blue-deep: #0a2f7a;
            --lnu-yellow: #ffc107;
            --lnu-yellow-soft: #ffe082;
            --surface: #ffffff;
            --ink: #13223a;
            --muted: #54617a;
            --danger-bg: #fff1f1;
            --danger-border: #ffcccc;
            --danger-text: #b71f1f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito Sans', sans-serif;
            background:
                radial-gradient(circle at 12% 12%, rgba(255, 193, 7, 0.30), transparent 25%),
                radial-gradient(circle at 88% 22%, rgba(255, 193, 7, 0.24), transparent 18%),
                linear-gradient(145deg, #0a2f7a 0%, #0047ab 48%, #1565c0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: var(--surface);
            border-radius: 18px;
            border: 1px solid rgba(10, 47, 122, 0.08);
            box-shadow: 0 24px 55px rgba(8, 30, 82, 0.28);
            width: 100%;
            max-width: 460px;
            padding: 34px 32px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-chip {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 193, 7, 0.55);
            background: linear-gradient(135deg, #ffefb0, #fff8e1);
            color: var(--lnu-blue-deep);
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        
        .register-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 30px;
            color: var(--ink);
            margin-bottom: 8px;
            line-height: 1.2;
        }
        
        .register-header p {
            color: var(--muted);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        label {
            display: block;
            margin-bottom: 7px;
            color: var(--ink);
            font-weight: 700;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d4dcee;
            border-radius: 10px;
            font-size: 14px;
            color: #213453;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--lnu-blue);
            box-shadow: 0 0 0 4px rgba(0, 71, 171, 0.16);
            transform: translateY(-1px);
        }
        
        .field-hint {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        button {
            width: 100%;
            padding: 12px 14px;
            margin-top: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            background: linear-gradient(135deg, var(--lnu-blue), #1565c0);
            box-shadow: 0 10px 24px rgba(0, 71, 171, 0.28);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 71, 171, 0.35);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .form-errors {
            margin-bottom: 20px;
        }
        
        .form-errors ul {
            list-style: none;
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: 10px;
            padding: 12px;
        }
        
        .form-errors li {
            color: var(--danger-text);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .form-errors li:last-child {
            margin-bottom: 0;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e6ecf7;
            font-size: 14px;
            color: var(--muted);
        }
        
        .login-link a {
            color: var(--lnu-blue);
            text-decoration: none;
            font-weight: 700;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 540px) {
            body {
                padding: 14px;
            }

            .register-container {
                padding: 24px 20px;
                border-radius: 14px;
            }

            .register-header h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <span class="brand-chip">LNU System</span>
            <h1>Create Your Account</h1>
            <p>Join the LNU event platform</p>
        </div>
        
        @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label for="name">Full Name (Family Name, First Name)</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Dela Cruz, Juan"
                    pattern="^[^,]+,\s*[^,]+.*$"
                    title="Use the format: Family Name, First Name"
                    value="{{ old('name') }}"
                    required 
                    autofocus
                >
                <p class="field-hint">Format example: Dela Cruz, Juan</p>
            </div>

            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input
                    type="text"
                    id="student_id"
                    name="student_id"
                    placeholder="2026-1201"
                    pattern="^[A-Za-z0-9-]+$"
                    title="Student ID may only contain letters, numbers, and hyphens."
                    value="{{ old('student_id') }}"
                    required
                    autocomplete="off"
                >
                <p class="field-hint">Use letters, numbers, and hyphens only.</p>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="new-password"
                >
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required
                    autocomplete="new-password"
                >
            </div>
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an admin account? <a href="{{ route('admin.login') }}">Login here</a>
        </div>
    </div>
</body>
</html>
