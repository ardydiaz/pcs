@extends('layouts.blankLayout')

@section('title', 'Student Evaluation Access')

@section('content')
<style>
    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        background:
            radial-gradient(circle at 8% 85%, rgba(255, 204, 27, 0.26), transparent 24%),
            radial-gradient(circle at 86% 12%, rgba(239, 23, 92, 0.34), transparent 30%),
            linear-gradient(135deg, #2a003d 0%, #4c075f 44%, #7b2d82 100%);
        color: #ffffff;
        font-family: Roboto, Arial, sans-serif;
        overflow-x: hidden;
    }

    body::before,
    body::after {
        content: "";
        position: fixed;
        pointer-events: none;
        z-index: 0;
    }

    body::before {
        width: 520px;
        height: 520px;
        right: -160px;
        top: -180px;
        border-radius: 50%;
        background:
            linear-gradient(135deg, rgba(255, 183, 54, 0.38), rgba(239, 23, 92, 0.34), rgba(20, 171, 187, 0.22));
        filter: blur(1px);
    }

    body::after {
        width: 420px;
        height: 420px;
        left: -170px;
        bottom: -190px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    .student-access-shell {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 36px 18px;
    }

    .student-access-card {
        width: min(820px, 100%);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 26px;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 30px 90px rgba(18, 0, 28, 0.34);
        color: #1f2937;
        overflow: hidden;
    }

    .student-access-header {
        position: relative;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 22px;
        align-items: center;
        padding: 34px 38px 38px;
        background:
            radial-gradient(circle at 96% 10%, rgba(255, 183, 54, 0.28), transparent 28%),
            radial-gradient(circle at 74% 0%, rgba(239, 23, 92, 0.22), transparent 30%),
            linear-gradient(135deg, #3a0050 0%, #5b1b76 56%, #8b3b88 100%);
        color: #ffffff;
    }

    .student-access-header::after {
        content: "";
        position: absolute;
        left: 38px;
        bottom: 22px;
        width: 90px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #ffb736, #ef175c, #14abbb);
    }

    .student-access-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .student-access-logo {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: #ffffff;
        color: #5b1b76;
        font-weight: 900;
        letter-spacing: -0.05em;
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.18);
    }

    .student-access-kicker {
        margin: 0;
        color: #ffcc1b;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .student-access-school {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.92);
        font-size: 15px;
        font-weight: 800;
    }

    .student-access-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(32px, 6vw, 52px);
        line-height: 1.02;
        font-weight: 900;
        letter-spacing: -0.02em;
        text-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
    }

    .student-access-pill {
        align-self: start;
        padding: 10px 16px;
        border: 1px solid rgba(255, 204, 27, 0.42);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .student-access-body {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 26px;
        padding: 34px 38px 38px;
    }

    .student-access-message {
        margin: 0;
        color: #263447;
        font-size: 17px;
        line-height: 1.7;
    }

    .student-access-note {
        margin-top: 20px;
        padding: 18px 20px;
        border-radius: 18px;
        border: 1px solid rgba(111, 42, 143, 0.16);
        background:
            radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.18), transparent 38%),
            #fbf7ff;
        color: #3a0050;
        font-weight: 700;
        line-height: 1.55;
    }

    .student-access-guide {
        border-radius: 22px;
        padding: 22px;
        background:
            radial-gradient(circle at 100% 0%, rgba(20, 171, 187, 0.16), transparent 36%),
            linear-gradient(160deg, #fff8e6, #ffffff);
        border: 1px solid rgba(255, 183, 54, 0.32);
    }

    .student-access-guide-title {
        margin: 0 0 14px;
        color: #3a0050;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .student-access-steps {
        display: grid;
        gap: 12px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .student-access-steps li {
        display: grid;
        grid-template-columns: 32px 1fr;
        gap: 10px;
        align-items: start;
        color: #334155;
        font-weight: 700;
        line-height: 1.45;
    }

    .student-access-step-number {
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border-radius: 12px;
        background: #5b1b76;
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
    }

    .student-access-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .student-access-button {
        border: 0;
        border-radius: 14px;
        padding: 13px 20px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .student-access-button:hover {
        transform: translateY(-1px);
    }

    .student-access-button.primary {
        background: linear-gradient(135deg, #4b0062, #6f2a8f);
        color: #ffffff;
        box-shadow: 0 14px 34px rgba(91, 27, 118, 0.3);
    }

    .student-access-button.secondary {
        background: #fff4d8;
        color: #3a0050;
        border: 1px solid rgba(255, 183, 54, 0.5);
    }

    @media (max-width: 720px) {
        .student-access-header,
        .student-access-body {
            grid-template-columns: 1fr;
            padding-left: 24px;
            padding-right: 24px;
        }

        .student-access-pill {
            width: max-content;
        }
    }
</style>

<main class="student-access-shell">
    <section class="student-access-card" aria-labelledby="student-access-title">
        <div class="student-access-header">
            <div>
                <div class="student-access-brand">
                    <div class="student-access-logo">MCU</div>
                    <div>
                        <p class="student-access-kicker">Post-Class Student Survey</p>
                        <p class="student-access-school">Manila Central University</p>
                    </div>
                </div>
                <h1 class="student-access-title" id="student-access-title">Student Evaluation Access</h1>
            </div>
            <div class="student-access-pill">Student Portal</div>
        </div>

        <div class="student-access-body">
            <div>
                <p class="student-access-message">
                    Your account is for answering evaluation forms. The dashboard is reserved for administrators, deans, department heads, and authorized faculty users.
                </p>

                <div class="student-access-note">
                    To start an evaluation, open or scan the QR evaluation link provided by your class or faculty member.
                </div>

                <div class="student-access-actions">
                    <a class="student-access-button primary" href="{{ route('login') }}">Go to Home</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="student-access-button secondary" type="submit">Logout</button>
                    </form>
                </div>
            </div>

            <div class="student-access-guide" aria-label="How to evaluate">
                <p class="student-access-guide-title">How to evaluate</p>
                <ol class="student-access-steps">
                    <li><span class="student-access-step-number">1</span><span>Scan the QR code or open the evaluation link.</span></li>
                    <li><span class="student-access-step-number">2</span><span>Select your section and course.</span></li>
                    <li><span class="student-access-step-number">3</span><span>Submit your rating and feedback once complete.</span></li>
                </ol>
            </div>
        </div>
    </section>
</main>
@endsection
