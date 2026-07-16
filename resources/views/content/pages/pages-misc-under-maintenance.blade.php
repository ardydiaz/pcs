@extends('layouts.blankLayout')

@section('title', 'System Maintenance')

@section('content')
@php
    $isAuthenticated = auth()->check();
@endphp

<style>
    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        margin: 0;
        background:
            radial-gradient(circle at 10% 84%, rgba(255, 204, 27, 0.22), transparent 24%),
            radial-gradient(circle at 88% 10%, rgba(239, 23, 92, 0.28), transparent 30%),
            linear-gradient(135deg, #2a003d 0%, #4b0062 48%, #762a84 100%);
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
        border-radius: 999px;
    }

    body::before {
        width: 520px;
        height: 520px;
        top: -170px;
        right: -170px;
        background: linear-gradient(135deg, rgba(255, 183, 54, 0.4), rgba(239, 23, 92, 0.34), rgba(20, 171, 187, 0.2));
    }

    body::after {
        width: 380px;
        height: 380px;
        bottom: -180px;
        left: -160px;
        background: rgba(255, 255, 255, 0.1);
    }

    .maintenance-shell {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 34px 16px;
    }

    .maintenance-card {
        width: min(820px, 100%);
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.98);
        color: #1f2937;
        box-shadow: 0 30px 90px rgba(18, 0, 28, 0.34);
    }

    .maintenance-header {
        position: relative;
        padding: 32px 36px 38px;
        background:
            radial-gradient(circle at 96% 4%, rgba(255, 183, 54, 0.3), transparent 28%),
            radial-gradient(circle at 76% 0%, rgba(239, 23, 92, 0.24), transparent 34%),
            linear-gradient(135deg, #3a0050 0%, #5b1b76 56%, #8b3b88 100%);
        color: #ffffff;
    }

    .maintenance-header::after {
        content: "";
        position: absolute;
        left: 36px;
        bottom: 22px;
        width: 92px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #ffb736, #ef175c, #14abbb);
    }

    .maintenance-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }

    .maintenance-logo {
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

    .maintenance-kicker {
        margin: 0;
        color: #ffcc1b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .maintenance-school {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.92);
        font-size: 15px;
        font-weight: 800;
    }

    .maintenance-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(34px, 6vw, 54px);
        line-height: 1.04;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .maintenance-body {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 28px;
        align-items: center;
        padding: 34px 36px 38px;
    }

    .maintenance-message {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.75;
    }

    .maintenance-note {
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

    .maintenance-visual {
        display: grid;
        place-items: center;
        min-height: 240px;
        border-radius: 24px;
        border: 1px solid rgba(255, 183, 54, 0.28);
        background:
            radial-gradient(circle at 18% 20%, rgba(255, 204, 27, 0.22), transparent 20%),
            radial-gradient(circle at 82% 24%, rgba(20, 171, 187, 0.18), transparent 18%),
            linear-gradient(160deg, #fff8e6, #ffffff);
    }

    .maintenance-code {
        display: grid;
        place-items: center;
        width: 160px;
        height: 160px;
        border-radius: 999px;
        background: linear-gradient(135deg, #4b0062, #6f2a8f);
        color: #ffffff;
        box-shadow: 0 22px 50px rgba(91, 27, 118, 0.25);
        text-align: center;
    }

    .maintenance-code strong {
        display: block;
        font-size: 44px;
        line-height: 1;
        font-weight: 900;
    }

    .maintenance-code span {
        display: block;
        margin-top: 8px;
        color: #ffcc1b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .maintenance-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .maintenance-button {
        border: 0;
        border-radius: 14px;
        padding: 13px 20px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }

    .maintenance-button.primary {
        background: linear-gradient(135deg, #4b0062, #6f2a8f);
        color: #ffffff;
        box-shadow: 0 14px 34px rgba(91, 27, 118, 0.3);
    }

    .maintenance-button.secondary {
        background: #fff4d8;
        color: #3a0050;
        border: 1px solid rgba(255, 183, 54, 0.5);
    }

    @media (max-width: 720px) {
        .maintenance-shell {
            align-items: flex-start;
            padding: 22px 14px;
        }

        .maintenance-card {
            border-radius: 24px;
        }

        .maintenance-header,
        .maintenance-body {
            grid-template-columns: 1fr;
            padding-left: 22px;
            padding-right: 22px;
        }

        .maintenance-title {
            font-size: 34px;
        }

        .maintenance-message {
            font-size: 16px;
        }

        .maintenance-note {
            font-size: 15px;
        }

        .maintenance-visual {
            min-height: 190px;
        }

        .maintenance-code {
            width: 132px;
            height: 132px;
        }

        .maintenance-actions {
            flex-direction: column;
        }

        .maintenance-button,
        .maintenance-actions form {
            width: 100%;
        }

        .maintenance-button {
            display: block;
            text-align: center;
        }
    }
</style>

<main class="maintenance-shell">
    <section class="maintenance-card" aria-labelledby="maintenance-title">
        <div class="maintenance-header">
            <div class="maintenance-brand">
                <div class="maintenance-logo">MCU</div>
                <div>
                    <p class="maintenance-kicker">Post-Class Student Survey</p>
                    <p class="maintenance-school">Manila Central University</p>
                </div>
            </div>
            <h1 class="maintenance-title" id="maintenance-title">System Maintenance</h1>
        </div>

        <div class="maintenance-body">
            <div>
                <p class="maintenance-message">
                    The system is temporarily unavailable while we perform scheduled maintenance and improvements.
                </p>

                <div class="maintenance-note">
                    Please try again later. If this is urgent, contact the Information Technology Department for assistance.
                </div>

                <div class="maintenance-actions">
                    <a href="{{ url('/') }}" class="maintenance-button primary">Check Again</a>
                    @if($isAuthenticated)
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="maintenance-button secondary" type="submit">Logout</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="maintenance-visual" aria-hidden="true">
                <div class="maintenance-code">
                    <div>
                        <strong>503</strong>
                        <span>Maintenance</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
