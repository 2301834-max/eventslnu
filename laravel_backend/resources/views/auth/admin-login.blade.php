<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LNU Smart Events System</title>
    <style>
        :root {
            --brand-900: #061a3a;
            --brand-800: #082653;
            --brand-700: #0b3f86;
            --accent: #f6c343;
            --accent-strong: #e6ad16;
            --page: #eef3f8;
            --surface: #ffffff;
            --line: #d9e2ef;
            --ink: #111827;
            --muted: #64748b;
            --danger-bg: #fff1f2;
            --danger-line: #fecdd3;
            --danger-text: #be123c;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px;
            background:
                linear-gradient(90deg, rgba(6, 26, 58, 0.05) 1px, transparent 1px),
                linear-gradient(180deg, rgba(6, 26, 58, 0.05) 1px, transparent 1px),
                var(--page);
            background-size: 44px 44px;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-shell {
            width: min(100%, 1040px);
            min-height: 620px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(420px, 0.95fr);
            overflow: hidden;
            border: 1px solid rgba(8, 38, 83, 0.12);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.18);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px;
            background:
                linear-gradient(145deg, rgba(246, 195, 67, 0.13), transparent 44%),
                linear-gradient(135deg, var(--brand-900), var(--brand-800) 52%, var(--brand-700));
            color: #ffffff;
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }

        .brand-logo img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .brand-role {
            display: block;
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 13px;
            font-weight: 600;
        }

        .hero-copy {
            max-width: 430px;
            padding: 72px 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: var(--accent);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 28px;
            height: 2px;
            border-radius: 999px;
            background: var(--accent);
        }

        .hero-copy h1 {
            margin: 0;
            font-size: clamp(36px, 4.4vw, 56px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        .hero-copy p {
            max-width: 390px;
            margin: 20px 0 0;
            color: rgba(255, 255, 255, 0.76);
            font-size: 16px;
            line-height: 1.7;
        }

        .panel-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .metric {
            min-height: 84px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
        }

        .metric strong {
            display: block;
            color: #ffffff;
            font-size: 18px;
            line-height: 1;
        }

        .metric span {
            display: block;
            margin-top: 8px;
            color: rgba(255, 255, 255, 0.66);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
        }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            background: #fbfdff;
        }

        .form-card {
            width: 100%;
            max-width: 390px;
        }

        .form-heading {
            margin-bottom: 28px;
        }

        .form-heading h2 {
            margin: 0;
            color: var(--ink);
            font-size: 30px;
            line-height: 1.2;
            letter-spacing: 0;
        }

        .form-heading p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.55;
        }

        .access-note {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
            padding: 12px 14px;
            border: 1px solid rgba(230, 173, 22, 0.32);
            border-radius: 8px;
            background: #fff9e8;
            color: #6f5100;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid var(--danger-line);
            border-radius: 8px;
            background: var(--danger-bg);
            color: var(--danger-text);
            font-size: 14px;
            line-height: 1.45;
        }

        .alert p {
            margin: 0;
        }

        .alert p + p {
            margin-top: 6px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: #26364d;
            font-size: 13px;
            font-weight: 800;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            top: 50%;
            left: 15px;
            width: 19px;
            height: 19px;
            transform: translateY(-50%);
            color: #718096;
            pointer-events: none;
        }

        .field input {
            width: 100%;
            min-height: 52px;
            padding: 13px 14px 13px 46px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            color: var(--ink);
            font-size: 15px;
            outline: none;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .field input::placeholder {
            color: #94a3b8;
        }

        .field input:focus {
            border-color: var(--brand-700);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(11, 63, 134, 0.12);
        }

        .cta {
            width: 100%;
            min-height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 6px;
            border: 0;
            border-radius: 8px;
            background: var(--brand-900);
            color: #ffffff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 800;
            transition: background 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .cta:hover {
            background: var(--brand-800);
            box-shadow: 0 14px 28px rgba(6, 26, 58, 0.26);
            transform: translateY(-1px);
        }

        .cta:focus-visible {
            outline: 3px solid rgba(246, 195, 67, 0.55);
            outline-offset: 3px;
        }

        .cta svg {
            width: 18px;
            height: 18px;
        }

        .login-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 22px;
            font-size: 14px;
        }

        .login-links a {
            color: var(--brand-700);
            font-weight: 800;
            text-decoration: none;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            body {
                padding: 20px;
                place-items: start center;
            }

            .login-shell {
                min-height: 0;
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .brand-panel {
                padding: 24px;
            }

            .hero-copy {
                padding: 36px 0 24px;
            }

            .hero-copy h1 {
                font-size: 34px;
            }

            .panel-metrics {
                display: none;
            }

            .form-panel {
                padding: 30px 24px 34px;
            }
        }

        @media (max-width: 460px) {
            body {
                padding: 0;
                background: #fbfdff;
            }

            .login-shell {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .brand-panel {
                padding: 20px;
            }

            .brand-logo {
                width: 50px;
                height: 50px;
            }

            .brand-logo img {
                width: 39px;
                height: 39px;
            }

            .hero-copy {
                padding: 28px 0 12px;
            }

            .hero-copy h1 {
                font-size: 30px;
            }

            .hero-copy p {
                margin-top: 12px;
                font-size: 14px;
            }

            .form-heading h2 {
                font-size: 26px;
            }

        }
    </style>
</head>
<body>
    <main class="login-shell" aria-label="Admin sign in">
        <section class="brand-panel" aria-label="LNU Smart Events">
            <div class="brand-mark">
                <div class="brand-logo">
                    <img src="{{ asset('images/lnu-logo.png') }}" alt="LNU logo">
                </div>
                <div>
                    <span class="brand-name">LNU Smart Events</span>
                    <span class="brand-role">Admin Console</span>
                </div>
            </div>

            <div class="hero-copy">
                <span class="eyebrow">Secure access</span>
                <h1>Manage campus events with confidence.</h1>
                <p>Sign in to oversee registrations, attendance scans, student records, and reporting from one focused workspace.</p>
            </div>

            <div class="panel-metrics" aria-label="Platform highlights">
                <div class="metric">
                    <strong>Live</strong>
                    <span>Attendance tracking</span>
                </div>
                <div class="metric">
                    <strong>QR</strong>
                    <span>Event check-ins</span>
                </div>
                <div class="metric">
                    <strong>PDF</strong>
                    <span>Exportable reports</span>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="form-card">
                <div class="form-heading">
                    <h2>Admin Sign In</h2>
                    <p>Use your administrator account to continue to the dashboard.</p>
                </div>

                @if($errors->any())
                    <div class="alert" role="alert">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 6.5h16v11H4v-11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="m5 7.5 7 5.2 7-5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@lnusystem.local"
                                required
                                autofocus
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M6 10h12v9H6v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M12 14v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <button type="submit" class="cta">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M13 5 20 12l-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Sign in
                    </button>
                </form>

                <div class="login-links">
                    <a href="{{ route('super-admin.login') }}">SuperAdmin</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
