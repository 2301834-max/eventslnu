<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LNU Event Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --lnu-blue: #0047ab;
            --lnu-blue-deep: #0a2f7a;
            --lnu-blue-soft: #1664c8;
            --lnu-yellow: #ffc107;
            --lnu-yellow-soft: #ffe082;
            --surface: #ffffff;
            --ink: #13223a;
            --muted: #536078;
            --danger-bg: #fff1f1;
            --danger-border: #ffcbcb;
            --danger-text: #b91d1d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            min-height: 100vh;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 12% 16%, rgba(255, 193, 7, 0.26), transparent 22%),
                radial-gradient(circle at 88% 20%, rgba(255, 193, 7, 0.22), transparent 20%),
                linear-gradient(145deg, var(--lnu-blue-deep) 0%, var(--lnu-blue) 48%, var(--lnu-blue-soft) 100%);
        }

        .login-shell {
            width: 100%;
            max-width: 980px;
            background: var(--surface);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 62px rgba(8, 29, 80, 0.3);
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            animation: shell-in 420ms ease-out;
        }

        .brand-panel {
            background:
                linear-gradient(160deg, rgba(255, 255, 255, 0.09), rgba(255, 255, 255, 0.03)),
                linear-gradient(135deg, #0a2f7a 0%, #0047ab 58%, #176bd0 100%);
            color: #fff;
            padding: 44px 38px;
            position: relative;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            right: -58px;
            bottom: -58px;
            background: radial-gradient(circle, rgba(255, 193, 7, 0.45), rgba(255, 193, 7, 0.08));
            pointer-events: none;
        }

        .chip {
            display: inline-block;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #12295e;
            background: linear-gradient(135deg, var(--lnu-yellow-soft), #fff6d9);
            border-radius: 999px;
            padding: 7px 13px;
            margin-bottom: 18px;
        }

        .brand-panel h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 34px;
            line-height: 1.15;
            margin-bottom: 14px;
        }

        .brand-panel p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 330px;
            line-height: 1.55;
        }

        .panel-points {
            margin-top: 28px;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .panel-points li {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.92);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-points li::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--lnu-yellow);
            box-shadow: 0 0 0 5px rgba(255, 193, 7, 0.2);
        }

        .form-panel {
            padding: 42px 36px;
        }

        .form-panel h2 {
            font-family: 'Montserrat', sans-serif;
            color: var(--ink);
            font-size: 28px;
            margin-bottom: 6px;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 14px;
        }

        .access-note {
            margin-bottom: 22px;
            padding: 12px 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.22), rgba(255, 245, 209, 0.92));
            border: 1px solid rgba(255, 193, 7, 0.4);
            color: #5a4700;
            font-size: 13px;
            line-height: 1.5;
        }

        .alert {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert p + p {
            margin-top: 6px;
        }

        .field {
            margin-bottom: 15px;
        }

        .field label {
            display: block;
            color: var(--ink);
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .field input[type="email"],
        .field input[type="password"] {
            width: 100%;
            border: 1px solid #d4dceb;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            color: #243550;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }

        .field input[type="email"]:focus,
        .field input[type="password"]:focus {
            outline: none;
            border-color: var(--lnu-blue);
            box-shadow: 0 0 0 4px rgba(0, 71, 171, 0.16);
            transform: translateY(-1px);
        }

        .remember {
            margin: 8px 0 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--muted);
            font-size: 14px;
        }

        .remember input {
            accent-color: var(--lnu-blue);
        }

        .cta {
            margin-top: 8px;
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 12px 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--lnu-blue), #1565c0);
            box-shadow: 0 12px 26px rgba(0, 71, 171, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(0, 71, 171, 0.36);
        }

        .cta:active {
            transform: translateY(0);
        }

        .switch-link {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            color: var(--muted);
        }

        .switch-link a {
            color: var(--lnu-blue);
            text-decoration: none;
            font-weight: 700;
        }

        .switch-link a:hover {
            text-decoration: underline;
        }

        .admin-link {
            margin-top: 8px;
            text-align: center;
            font-size: 13px;
        }

        .admin-link a {
            color: #204d92;
            text-decoration: none;
            font-weight: 700;
        }

        .admin-link a:hover {
            text-decoration: underline;
        }

        @keyframes shell-in {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 880px) {
            .login-shell {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .brand-panel {
                padding: 28px 24px 22px;
            }

            .brand-panel h1 {
                font-size: 30px;
            }

            .form-panel {
                padding: 28px 24px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="brand-panel">
            <span class="chip">LNU Admin</span>
            <h1>Smart Event Control Center</h1>
            <p>Sign in to manage events, attendance, and student records from one secure dashboard.</p>
            <ul class="panel-points">
                <li>Track event participation in real time</li>
                <li>Approve registrations quickly</li>
                <li>Export reports with one click</li>
            </ul>
        </div>

        <div class="form-panel">
            <h2>Admin Login</h2>
            <p class="subtitle">Use your administrator credentials to continue.</p>
            <div class="access-note">
                This page is for administrators only.
            </div>

            @if($errors->any())
                <div class="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="your.email@example.com"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <label class="remember" for="remember">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    Keep me signed in
                </label>

                <button type="submit" class="cta">Login</button>
            </form>

        </div>
    </div>
</body>
</html>
