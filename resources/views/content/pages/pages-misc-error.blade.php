@extends('layouts/blankLayout')

@section('title', 'Access Not Available')

@section('content')
@php
    $user = auth()->user();
    $role = strtolower((string) $user?->role);
    $isFaculty = $role === 'faculty';
    $isStudent = $role === 'student';
    $homeUrl = $isStudent && Route::has('student.evaluation-access')
        ? route('student.evaluation-access')
        : url('/');
@endphp

<style>
    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        margin: 0;
        background:
            radial-gradient(circle at 8% 84%, rgba(255, 204, 27, 0.22), transparent 24%),
            radial-gradient(circle at 88% 10%, rgba(239, 23, 92, 0.28), transparent 30%),
            linear-gradient(135deg, #2a003d 0%, #4b0062 48%, #762a84 100%);
        font-family: Roboto, Arial, sans-serif;
        color: #ffffff;
        overflow-x: hidden;
    }

    body::before,
    body::after {
        content: "";
        position: fixed;
        pointer-events: none;
        z-index: 0;
        border-radius: 999px;
    }

    body::before {
        width: 520px;
        height: 520px;
        top: -170px;
        right: -170px;
        background: linear-gradient(135deg, rgba(255, 183, 54, 0.4), rgba(239, 23, 92, 0.32), rgba(20, 171, 187, 0.18));
    }

    body::after {
        width: 380px;
        height: 380px;
        bottom: -180px;
        left: -160px;
        background: rgba(255, 255, 255, 0.1);
    }

    .access-error-shell {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 34px 16px;
    }

    .access-error-card {
        width: min(780px, 100%);
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.98);
        color: #1f2937;
        box-shadow: 0 30px 90px rgba(18, 0, 28, 0.34);
    }

    .access-error-header {
        position: relative;
        padding: 32px 36px 38px;
        background:
            radial-gradient(circle at 96% 4%, rgba(255, 183, 54, 0.28), transparent 28%),
            radial-gradient(circle at 76% 0%, rgba(239, 23, 92, 0.24), transparent 34%),
            linear-gradient(135deg, #3a0050 0%, #5b1b76 56%, #8b3b88 100%);
        color: #ffffff;
    }

    .access-error-header::after {
        content: "";
        position: absolute;
        left: 36px;
        bottom: 22px;
        width: 92px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #ffb736, #ef175c, #14abbb);
    }

    .access-error-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .access-error-logo {
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

    .access-error-kicker {
        margin: 0;
        color: #ffcc1b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .access-error-school {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.92);
        font-size: 15px;
        font-weight: 800;
    }

    .access-error-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(32px, 6vw, 50px);
        line-height: 1.04;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .access-error-body {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 28px;
        align-items: center;
        padding: 34px 36px 38px;
    }

    .access-error-message {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.75;
        max-width: 560px;
    }

    .access-error-note {
        margin-top: 20px;
        padding: 20px 22px;
        border-radius: 18px;
        border: 1px solid rgba(91, 27, 118, 0.24);
        background:
            radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.2), transparent 38%),
            linear-gradient(135deg, #fff8e6, #ffffff);
        color: #2a003d;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.65;
        box-shadow: 0 12px 30px rgba(58, 0, 80, 0.06);
    }

    .access-error-visual {
        display: grid;
        place-items: center;
        min-height: 230px;
        border-radius: 24px;
        border: 1px solid rgba(255, 183, 54, 0.28);
        background:
            radial-gradient(circle at 18% 20%, rgba(255, 204, 27, 0.22), transparent 20%),
            radial-gradient(circle at 82% 24%, rgba(20, 171, 187, 0.18), transparent 18%),
            linear-gradient(160deg, #fff8e6, #ffffff);
    }

    .access-error-code {
        display: grid;
        place-items: center;
        width: 150px;
        height: 150px;
        border-radius: 999px;
        background: linear-gradient(135deg, #4b0062, #6f2a8f);
        color: #ffffff;
        box-shadow: 0 22px 50px rgba(91, 27, 118, 0.25);
        text-align: center;
    }

    .access-error-code strong {
        display: block;
        font-size: 42px;
        line-height: 1;
        font-weight: 900;
    }

    .access-error-code span {
        display: block;
        margin-top: 8px;
        color: #ffcc1b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .access-error-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .access-error-button {
        border: 0;
        border-radius: 14px;
        padding: 13px 20px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .access-error-button:hover {
        transform: translateY(-1px);
    }

    .access-error-button.primary {
        background: linear-gradient(135deg, #4b0062, #6f2a8f);
        color: #ffffff;
        box-shadow: 0 14px 34px rgba(91, 27, 118, 0.3);
    }

    .access-error-button.secondary {
        background: #fff4d8;
        color: #3a0050;
        border: 1px solid rgba(255, 183, 54, 0.5);
    }

    @media (max-width: 720px) {
        .access-error-shell {
            align-items: flex-start;
            padding: 22px 14px;
        }

        .access-error-card {
            border-radius: 24px;
        }

        .access-error-header,
        .access-error-body {
            grid-template-columns: 1fr;
            padding-left: 22px;
            padding-right: 22px;
        }

        .access-error-title {
            font-size: 34px;
        }

        .access-error-message {
            font-size: 16px;
            line-height: 1.7;
        }

        .access-error-note {
            font-size: 15px;
            line-height: 1.6;
        }

        .access-error-visual {
            min-height: 180px;
        }

        .access-error-code {
            width: 128px;
            height: 128px;
        }

        .access-error-actions {
            flex-direction: column;
        }

        .access-error-button,
        .access-error-actions form {
            width: 100%;
        }

        .access-error-button {
            display: block;
            text-align: center;
        }
    }
</style>

<main class="access-error-shell">
    <section class="access-error-card" aria-labelledby="access-error-title">
        <div class="access-error-header">
            <div class="access-error-brand">
                <div class="access-error-logo">MCU</div>
                <div>
                    <p class="access-error-kicker">Post-Class Student Survey</p>
                    <p class="access-error-school">Manila Central University</p>
                </div>
            </div>
            <h1 class="access-error-title" id="access-error-title">Access Not Available</h1>
        </div>

        <div class="access-error-body">
            <div>
                <p class="access-error-message">
                    @if ($isFaculty)
                        Your faculty account is active, but it does not have permission to open this page yet.
                    @elseif ($isStudent)
                        Your student account is only allowed to answer evaluation forms through a QR evaluation link.
                    @else
                        This page is not available for your account or the link may no longer exist.
                    @endif
                </p>

                <div class="access-error-note">
                    @if ($isFaculty)
                        Please contact the IT Department if you need access to reports, faculty records, courses, schedules, or evaluations.
                    @elseif ($isStudent)
                        Please open or scan the QR evaluation link provided by your class or faculty member.
                    @else
                        Please return to the home page or contact the system administrator if you believe this is a mistake.
                    @endif
                </div>

                <div class="access-error-actions">
                    <a href="{{ $homeUrl }}" class="access-error-button primary">Back to Home</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="access-error-button secondary" type="submit">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>

            <div class="access-error-visual" aria-hidden="true">
                <div class="access-error-code">
                    <div>
                        <strong>404</strong>
                        <span>No Access</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
