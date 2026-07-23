<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Class Survey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/images/favicon.png') }}" />
    <style>
        :root {
            --mcu-gold-burst: #ffd400;
            --mcu-gold-ignite: #ffc20e;
            --mcu-heritage-gold: #f6b62d;
            --mcu-purple-haze: #74318a;
            --mcu-heritage-purple: #5c297c;
            --mcu-purple-panther: #4a1766;
            --mcu-purple-midnight: #2c003f;
            --mcu-oat: #e8dbb3;
            --mcu-mist: #b6cfca;
            --mcu-calm-blue: #10a8b4;
            --mcu-ink: #243044;
            --mcu-muted: #718096;
            --mcu-line: #e8edf5;
            --page-bg: #f7f4fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 12% 10%, rgba(255, 194, 14, 0.18), transparent 24rem),
                radial-gradient(circle at 86% 18%, rgba(92, 41, 124, 0.16), transparent 28rem),
                linear-gradient(135deg, #fbf9ff 0%, var(--page-bg) 48%, #f3eef8 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 32px 20px;
            color: var(--mcu-ink);
            font-family: 'Roboto', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        @keyframes loginShellEnter {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes loginPanelEnter {
            from {
                opacity: 0;
                transform: translateX(24px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes brandContentEnter {
            from {
                opacity: 0;
                transform: translateX(-26px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes softFloat {
            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(14px, -12px, 0) scale(1.03);
            }
        }

        @keyframes dotDrift {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-14px);
            }
        }

        .login-backdrop {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .login-backdrop::before,
        .login-backdrop::after {
            content: "";
            position: absolute;
            width: 34rem;
            height: 34rem;
            border-radius: 999px;
            filter: blur(1px);
            opacity: 0.85;
        }

        .login-backdrop::before {
            left: -14rem;
            bottom: -16rem;
            background:
                radial-gradient(circle, rgba(255, 194, 14, 0.34), transparent 62%),
                radial-gradient(circle at 70% 30%, rgba(92, 41, 124, 0.16), transparent 58%);
            animation: softFloat 9s ease-in-out infinite;
        }

        .login-backdrop::after {
            right: -15rem;
            top: -17rem;
            background:
                radial-gradient(circle, rgba(92, 41, 124, 0.26), transparent 62%),
                radial-gradient(circle at 20% 80%, rgba(16, 168, 180, 0.14), transparent 54%);
            animation: softFloat 11s ease-in-out infinite reverse;
        }

        .dot-field {
            position: fixed;
            width: 170px;
            height: 130px;
            background-image: radial-gradient(rgba(92, 41, 124, 0.16) 1px, transparent 1px);
            background-size: 10px 10px;
            opacity: 0.42;
            pointer-events: none;
            animation: dotDrift 8s ease-in-out infinite;
        }

        .dot-field-left {
            left: 9vw;
            top: 36vh;
        }

        .dot-field-right {
            right: 10vw;
            bottom: 30vh;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(100%, 1040px);
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(360px, 0.78fr);
            overflow: hidden;
            border: 1px solid rgba(232, 237, 245, 0.95);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.74);
            box-shadow: 0 28px 70px rgba(44, 0, 63, 0.18);
            backdrop-filter: blur(18px);
            animation: loginShellEnter 0.72s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .brand-panel {
            position: relative;
            overflow: hidden;
            min-height: 610px;
            padding: 48px;
            color: #fff;
            background:
                linear-gradient(135deg, rgba(44, 0, 63, 0.96), rgba(74, 23, 102, 0.94) 52%, rgba(92, 41, 124, 0.92)),
                radial-gradient(circle at top right, rgba(255, 194, 14, 0.42), transparent 20rem);
        }

        .brand-panel::before,
        .brand-panel::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .brand-panel::before {
            width: 24rem;
            height: 24rem;
            right: -9rem;
            top: -8rem;
            background: radial-gradient(circle, rgba(255, 194, 14, 0.34), transparent 68%);
        }

        .brand-panel::after {
            width: 18rem;
            height: 18rem;
            left: -7rem;
            bottom: -7rem;
            background: radial-gradient(circle, rgba(16, 168, 180, 0.2), transparent 66%);
        }

        .brand-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            animation: brandContentEnter 0.78s cubic-bezier(0.22, 1, 0.36, 1) 0.12s both;
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 0.5rem;
            padding: 0.48rem 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .brand-kicker::before {
            content: "";
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: var(--mcu-gold-ignite);
            box-shadow: 0 0 0 0.25rem rgba(255, 194, 14, 0.18);
        }

        .brand-title {
            max-width: 560px;
            margin: 4.3rem 0 1rem;
            color: #ffffff;
            font-size: clamp(2.2rem, 4vw, 4.1rem);
            font-weight: 900;
            letter-spacing: -0.05em;
            line-height: 0.98;
        }

        .brand-title span {
            color: var(--mcu-gold-burst);
        }

        .brand-copy {
            max-width: 480px;
            margin: 0;
            color: rgba(255, 255, 255, 0.76);
            font-size: 1rem;
            line-height: 1.75;
        }

        .brand-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: auto;
            padding-top: 3rem;
        }

        .brand-stat {
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.09);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }

        .brand-stat strong {
            display: block;
            color: var(--mcu-gold-burst);
            font-size: 1.15rem;
            line-height: 1;
        }

        .brand-stat span {
            display: block;
            margin-top: 0.45rem;
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.72rem;
            line-height: 1.3;
        }

        .login-panel {
            display: flex;
            align-items: center;
            padding: 44px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(252, 250, 255, 0.98)),
                radial-gradient(circle at top, rgba(255, 194, 14, 0.12), transparent 18rem);
            animation: loginPanelEnter 0.78s cubic-bezier(0.22, 1, 0.36, 1) 0.18s both;
        }

        .login-card {
            width: 100%;
            text-align: left;
        }

        .login-card img {
            width: min(220px, 78%);
            height: auto;
            margin-bottom: 34px;
        }

        .login-title {
            max-width: 380px;
            margin: 0 0 10px;
            color: var(--mcu-ink);
            font-size: 1.55rem;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: 0;
        }

        .login-title::after {
            content: "";
            display: block;
            width: 46px;
            height: 3px;
            margin: 16px 0 0;
            background: linear-gradient(90deg, var(--mcu-gold-ignite), var(--mcu-heritage-purple));
            border-radius: 999px;
        }

        .login-subtitle {
            margin: 0 0 26px;
            color: var(--mcu-muted);
            font-size: 0.94rem;
            line-height: 1.55;
        }

        .btn-office365 {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            min-height: 52px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--mcu-heritage-purple), var(--mcu-purple-panther));
            color: #fff;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0;
            box-shadow: 0 14px 28px rgba(92, 41, 124, 0.26);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn-office365:hover,
        .btn-office365:focus {
            background: linear-gradient(135deg, var(--mcu-purple-panther), var(--mcu-purple-midnight));
            color: #fff;
            box-shadow: 0 18px 34px rgba(92, 41, 124, 0.28);
            transform: translateY(-2px);
        }

        .microsoft-mark {
            display: grid;
            grid-template-columns: repeat(2, 7px);
            grid-template-rows: repeat(2, 7px);
            gap: 2px;
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        .microsoft-mark span:nth-child(1) {
            background: #f25022;
        }

        .microsoft-mark span:nth-child(2) {
            background: #7fba00;
        }

        .microsoft-mark span:nth-child(3) {
            background: #00a4ef;
        }

        .microsoft-mark span:nth-child(4) {
            background: #ffb900;
        }

        .login-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
            padding: 14px;
            border: 1px solid var(--mcu-line);
            border-radius: 16px;
            background: #ffffff;
            text-align: left;
        }

        .footer-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(92, 41, 124, 0.09);
            color: var(--mcu-heritage-purple);
            flex: 0 0 auto;
        }

        .login-note {
            margin: 0;
            color: var(--mcu-muted);
            font-size: 0.76rem;
            line-height: 1.35;
        }

        .login-note strong {
            display: block;
            color: var(--mcu-ink);
            font-size: 0.8rem;
            font-weight: 800;
        }

        .alert {
            border-radius: 14px;
            text-align: left;
            font-size: 0.92rem;
        }

        .login-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.15rem;
        }

        .login-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.38rem 0.58rem;
            border-radius: 999px;
            background: rgba(92, 41, 124, 0.08);
            color: var(--mcu-heritage-purple);
            font-size: 0.73rem;
            font-weight: 800;
        }

        .login-meta i {
            color: var(--mcu-heritage-gold);
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
                max-width: 460px;
            }

            .brand-panel {
                min-height: auto;
                padding: 30px;
            }

            .brand-title {
                margin-top: 2.6rem;
                font-size: 2.3rem;
            }

            .brand-stats {
                padding-top: 2rem;
            }

            .login-panel {
                padding: 32px 26px;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 20px 14px;
            }

            .dot-field {
                display: none;
            }

            .brand-panel {
                padding: 24px;
            }

            .brand-title {
                font-size: 2rem;
            }

            .brand-stats {
                grid-template-columns: 1fr;
            }

            .login-panel {
                padding: 28px 20px;
            }

            .login-title {
                font-size: 1.14rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.001ms !important;
            }
        }
    </style>
</head>

<body>
    <div class="login-backdrop" aria-hidden="true"></div>
    <div class="dot-field dot-field-left" aria-hidden="true"></div>
    <div class="dot-field dot-field-right" aria-hidden="true"></div>

    <main class="login-shell" aria-label="Post-Class Survey login">
        <section class="brand-panel" aria-label="System overview">
            <div class="brand-content">
                <span class="brand-kicker">Manila Central University</span>
                <h1 class="brand-title">Post-Class <span>Survey</span> System</h1>
                <p class="brand-copy">
                    A centralized space for faculty evaluation, QR access, department insights, and academic feedback
                    monitoring.
                </p>

               
            </div>
        </section>

        <section class="login-panel" aria-label="Login">
            <div class="login-card">
                <img src="{{ asset('storage/images/logo_color.png') }}" alt="Manila Central University logo" class="logo" />
                <div class="login-meta" aria-label="Login features">
                    <span><i class="fa-solid fa-shield-halved"></i> Secure SSO</span>
                    <span><i class="fa-solid fa-chart-line"></i> Evaluation Portal</span>
                </div>
                <h2 class="login-title">Welcome back</h2>
                <p class="login-subtitle">Sign in with your MCU Microsoft account to continue to the Post-Class Survey dashboard.</p>

                @if (session('session_expired'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Session Expired!</strong> {{ session('session_expired') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        {{ $errors->first('msg') ?? $errors->first() }}
                    </div>
                @endif

                <a href="{{ url('/auth/microsoft/redirect') }}" class="btn btn-office365 w-100">
                    <span class="microsoft-mark" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                    <span>Continue with Microsoft 365</span>
                </a>

                <div class="login-footer">
                    <span class="footer-icon" aria-hidden="true">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <p class="login-note">
                        <strong>Protected institutional access</strong>
                        Your login is verified through Microsoft single sign-on.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
