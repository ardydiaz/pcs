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
            --mcu-purple: #643189;
            --mcu-purple-dark: #4f246f;
            --mcu-gold: #ffd121;
            --page-bg: #f8f7fc;
            --text-main: #273040;
            --text-muted: #71798a;
            --line-soft: #e8e5ef;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--page-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 32px 18px;
            color: var(--text-main);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
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
            inset: auto -12vw 7vh;
            height: 230px;
            background:
                linear-gradient(100deg, transparent 9%, rgba(100, 49, 137, 0.1) 10%, rgba(100, 49, 137, 0.06) 32%, transparent 62%),
                linear-gradient(100deg, transparent 14%, rgba(255, 209, 33, 0.38) 15%, transparent 16%);
            transform: skewY(9deg);
        }

        .login-backdrop::after {
            inset: auto -10vw 8vh;
            background: linear-gradient(145deg, transparent 48%, rgba(100, 49, 137, 0.06) 49%, transparent 74%);
            transform: skewY(-14deg);
        }

        .dot-field {
            position: fixed;
            width: 170px;
            height: 130px;
            background-image: radial-gradient(rgba(100, 49, 137, 0.13) 1px, transparent 1px);
            background-size: 10px 10px;
            opacity: 0.5;
            pointer-events: none;
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
            width: min(100%, 448px);
        }

        .login-card {
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96);
            min-height: 402px;
            padding: 32px 32px 25px;
            border-radius: 10px;
            border: 1px solid rgba(100, 49, 137, 0.09);
            box-shadow: 0 18px 45px rgba(24, 18, 36, 0.18);
            text-align: center;
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, var(--mcu-gold), #f7ba20, var(--mcu-purple));
        }

        .login-card img {
            width: min(232px, 78%);
            height: auto;
            margin-bottom: 27px;
        }

        .login-title {
            max-width: 340px;
            margin: 0 auto 9px;
            font-size: 1.25rem;
            line-height: 1.16;
            font-weight: 800;
            letter-spacing: 0;
        }

        .login-title::after {
            content: "";
            display: block;
            width: 32px;
            height: 2px;
            margin: 13px auto 0;
            background: var(--mcu-gold);
            border-radius: 999px;
        }

        .login-subtitle {
            margin-bottom: 21px;
            color: var(--text-muted);
            font-size: 0.84rem;
        }

        .btn-office365 {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            min-height: 46px;
            border: 0;
            border-radius: 7px;
            background: linear-gradient(135deg, #7137d8, var(--mcu-purple));
            color: #fff;
            font-weight: 700;
            font-size: 0.93rem;
            letter-spacing: 0;
            box-shadow: 0 13px 24px rgba(100, 49, 137, 0.28);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn-office365:hover,
        .btn-office365:focus {
            background: linear-gradient(135deg, #6c34cf, var(--mcu-purple-dark));
            color: #fff;
            box-shadow: 0 16px 28px rgba(100, 49, 137, 0.26);
            transform: translateY(-1px);
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
            gap: 11px;
            margin-top: 25px;
            padding-top: 16px;
            border-top: 1px solid var(--line-soft);
            text-align: left;
        }

        .footer-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: rgba(100, 49, 137, 0.08);
            color: var(--mcu-purple);
            flex: 0 0 auto;
        }

        .login-note {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.72rem;
            line-height: 1.35;
        }

        .login-note strong {
            display: block;
            color: #4b5361;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .alert {
            border-radius: 10px;
            text-align: left;
            font-size: 0.92rem;
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 14px;
            }

            .dot-field {
                display: none;
            }

            .login-card {
                min-height: auto;
                padding: 32px 22px 24px;
            }

            .login-title {
                font-size: 1.14rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-backdrop" aria-hidden="true"></div>
    <div class="dot-field dot-field-left" aria-hidden="true"></div>
    <div class="dot-field dot-field-right" aria-hidden="true"></div>

    <main class="login-shell">
        <section class="login-card" aria-label="Login">
            <img src="{{ asset('storage/images/logo light.png') }}" alt="Manila Central University logo" class="logo" />
            <h1 class="login-title">Welcome to MCU Post-Class Survey</h1>
            <p class="login-subtitle">Please sign-in to your account</p>

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
                <span>Login with Office365</span>
            </a>

            <div class="login-footer">
                <span class="footer-icon" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <p class="login-note">
                    <strong>Secure login powered by Microsoft</strong>
                    Your data is protected and secure
                </p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
