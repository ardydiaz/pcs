<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Class Survey Form</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/images/favicon.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 20px;
            display: none;
        }

        .section-card.active {
            display: block;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-section h1 {
            color: #764ba2;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-section .subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .department-pill-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .department-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background-color: #eef2ff;
            color: #4338ca;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .consent-section {
            border-left: 4px solid #764ba2;
            padding-left: 20px;
        }

        .rating-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .rating-option {
            text-align: center;
            flex: 1;
            margin: 0 10px;
            min-width: 120px;
        }

        .rating-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-bottom: 10px;
        }

        .rating-option label {
            display: block;
            font-weight: 500;
            color: #495057;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .btn-outline-secondary {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .progress-bar-custom {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }

        .form-select:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }

        .illustration {
            text-align: center;
            margin-bottom: 30px;
        }

        .illustration svg {
            max-width: 200px;
            height: auto;
        }

        .section-title {
            color: #764ba2;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
        }

        /* Thank you screen styles */
        .thank-you-screen {
            text-align: center;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            margin-bottom: 20px;
            display: none;
        }

        .thank-you-screen.active {
            display: block;
            animation: fadeIn 0.5s ease-in;
        }

        .thank-you-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .cooldown-timer {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }

        .pulse {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        :root {
            --mcu-purple-midnight: #3a0050;
            --mcu-purple: #5c297c;
            --mcu-purple-haze: #6f2a8f;
            --mcu-gold: #ffb736;
            --mcu-gold-soft: #fff3d4;
            --mcu-mist: #b7d0cc;
            --mcu-text: #1f2937;
            --mcu-muted: #667085;
            --mcu-border: #eadff0;
        }

        body {
            color: var(--mcu-text);
            background:
                radial-gradient(circle at 82% 12%, rgba(255, 183, 54, 0.22), transparent 18rem),
                radial-gradient(circle at 12% 86%, rgba(183, 208, 204, 0.34), transparent 22rem),
                linear-gradient(135deg, #2b003d 0%, #4b1168 45%, #6f2a8f 100%);
            position: relative;
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
            width: 34rem;
            height: 34rem;
            right: -12rem;
            top: -10rem;
            border-radius: 50%;
            background: rgba(255, 183, 54, 0.16);
        }

        body::after {
            width: 28rem;
            height: 28rem;
            left: -10rem;
            bottom: -12rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .form-container {
            position: relative;
            z-index: 1;
            max-width: 1080px;
            padding: 24px;
        }

        .survey-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 0 auto 18px;
            padding: 14px 18px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            backdrop-filter: blur(12px);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
        }

        .survey-brand-main {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
        }

        .survey-brand-logo {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: #ffffff;
            object-fit: contain;
            padding: 8px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.16);
        }

        .survey-brand-title {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mcu-gold);
        }

        .survey-brand-subtitle {
            margin: 0.15rem 0 0;
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
        }

        .survey-brand-term {
            flex: 0 0 auto;
            border-radius: 999px;
            background: rgba(255, 183, 54, 0.18);
            color: #ffffff;
            font-weight: 800;
            padding: 0.55rem 0.9rem;
            border: 1px solid rgba(255, 183, 54, 0.34);
        }

        .evaluation-context {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(220px, 0.65fr);
            gap: 1rem;
            margin: 0 auto 18px;
            padding: 18px;
            border-radius: 22px;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.18), transparent 14rem),
                rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 22px 58px rgba(15, 23, 42, 0.16);
        }

        .evaluation-context-label {
            margin: 0 0 0.25rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mcu-muted);
        }

        .evaluation-context-name {
            margin: 0;
            color: var(--mcu-purple);
            font-size: clamp(1.35rem, 2vw, 2rem);
            font-weight: 900;
            line-height: 1.1;
        }

        .evaluation-context-program {
            margin: 0.55rem 0 0;
            color: #27364f;
            font-weight: 700;
        }

        .evaluation-context .department-pill-group {
            justify-content: flex-start;
        }

        .evaluation-context-meta {
            display: grid;
            gap: 0.75rem;
        }

        .evaluation-context-item {
            border-radius: 16px;
            border: 1px solid #eadff0;
            background: #ffffff;
            padding: 0.85rem 1rem;
        }

        .evaluation-context-value {
            margin: 0;
            color: #1f2937;
            font-weight: 800;
            line-height: 1.25;
        }

        .progress-bar-custom {
            height: 8px;
            max-width: 980px;
            margin: 0 auto 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.34);
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(90deg, var(--mcu-gold), #ffe08a, var(--mcu-purple-haze));
            border-radius: inherit;
        }

        .section-card,
        .thank-you-screen {
            border: 1px solid rgba(255, 255, 255, 0.58);
            border-radius: 24px;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.12), transparent 18rem),
                #ffffff;
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.22);
        }

        .section-card {
            padding: 42px;
        }

        .header-section {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f0e8f5;
        }

        .header-section h1 {
            color: var(--mcu-purple);
            letter-spacing: 0;
        }

        .header-section .subtitle,
        .text-muted,
        .form-text {
            color: var(--mcu-muted) !important;
        }

        .department-pill {
            background: rgba(92, 41, 124, 0.1);
            color: var(--mcu-purple);
            border: 1px solid rgba(92, 41, 124, 0.14);
        }

        .section-title {
            color: var(--mcu-purple);
            border-bottom: 0;
            padding-bottom: 0.85rem;
            position: relative;
        }

        .section-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 76px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--mcu-gold), var(--mcu-purple));
        }

        .text-primary {
            color: var(--mcu-purple) !important;
        }

        .consent-section {
            border-left: 0;
            padding: 1.25rem;
            border-radius: 18px;
            background: #fbf8fd;
            border: 1px solid var(--mcu-border);
        }

        .form-control,
        .form-select {
            min-height: 48px;
            border-radius: 14px;
            border-color: #ded6e6;
            color: var(--mcu-text);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--mcu-purple);
            box-shadow: 0 0 0 0.22rem rgba(92, 41, 124, 0.14);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.12);
        }

        .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.12);
        }

        .consent-section.is-invalid,
        .rating-container.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1);
        }

        .rating-container.is-invalid .rating-option {
            border-color: rgba(220, 53, 69, 0.45);
            background: #fff8f8;
        }

        .field-error-message {
            color: #dc3545;
            display: none;
            font-size: 0.86rem;
            font-weight: 700;
            margin-top: 0.45rem;
        }

        .field-error-message.is-visible {
            display: block;
        }

        .course-preview {
            display: none;
            margin-top: 0.8rem;
            padding: 0.85rem 1rem;
            border-radius: 16px;
            border: 1px solid var(--mcu-border);
            background: #fbf8fd;
            color: var(--mcu-text);
            font-size: 0.92rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .course-preview-text {
            display: block;
            max-width: 100%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .course-preview.is-visible {
            display: block;
        }

        .course-preview-label {
            display: block;
            color: var(--mcu-purple);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .form-check-input:checked {
            background-color: var(--mcu-purple);
            border-color: var(--mcu-purple);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--mcu-purple-midnight), var(--mcu-purple-haze));
            border: 0;
            box-shadow: 0 14px 30px rgba(92, 41, 124, 0.24);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, #2d003f, var(--mcu-purple));
            box-shadow: 0 16px 34px rgba(92, 41, 124, 0.3);
        }

        .btn-outline-secondary,
        .btn-outline-primary {
            border-color: rgba(92, 41, 124, 0.35);
            color: var(--mcu-purple);
            background: #ffffff;
        }

        .btn-outline-secondary:hover,
        .btn-outline-primary:hover {
            background: var(--mcu-gold-soft);
            border-color: var(--mcu-gold);
            color: var(--mcu-purple-midnight);
        }

        .rating-container {
            gap: 1rem;
        }

        .rating-option {
            margin: 0;
            padding: 1rem;
            border: 1px solid var(--mcu-border);
            border-radius: 18px;
            background: #ffffff;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .rating-option:hover {
            transform: translateY(-2px);
            border-color: rgba(92, 41, 124, 0.36);
            box-shadow: 0 12px 28px rgba(92, 41, 124, 0.12);
        }

        .rating-option:has(input[type="radio"]:checked) {
            border-color: var(--mcu-purple);
            background: linear-gradient(180deg, #ffffff, #fbf6ff);
            box-shadow: 0 12px 30px rgba(92, 41, 124, 0.14);
        }

        .rating-option input[type="radio"] {
            accent-color: var(--mcu-purple);
        }

        .cooldown-timer {
            color: var(--mcu-purple);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 16px;
            }

            .survey-brand {
                align-items: flex-start;
                flex-direction: column;
                border-radius: 18px;
                position: sticky;
                top: 0;
                z-index: 20;
                margin-bottom: 14px;
                background:
                    radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.26), transparent 34%),
                    linear-gradient(135deg, rgba(58, 0, 80, 0.96), rgba(111, 42, 143, 0.92));
                -webkit-backdrop-filter: blur(16px);
                backdrop-filter: blur(16px);
                box-shadow: 0 18px 42px rgba(20, 0, 32, 0.35);
            }

            .survey-brand-term {
                align-self: flex-start;
            }

            .evaluation-context {
                grid-template-columns: 1fr;
                padding: 16px;
                border-radius: 18px;
            }

            .section-card {
                padding: 26px 20px;
                border-radius: 20px;
            }

            #schedule_id {
                font-size: 0.9rem;
                min-width: 0;
                text-overflow: ellipsis;
            }

            .course-preview {
                font-size: 0.86rem;
                padding: 0.75rem 0.85rem;
            }

            .rating-option {
                flex: 1 1 100%;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        @php
            $programLabel = $evaluation->resolved_program_label;
            $facultyDepartments = collect(explode(',', $evaluation->resolved_faculty_department ?? ''))
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values();
        @endphp

        <div class="survey-brand">
            <div class="survey-brand-main">
                <img src="{{ asset('storage/images/logo_color.png') }}" alt="MCU logo" class="survey-brand-logo">
                <div>
                    <p class="survey-brand-title">Post-Class Student Survey</p>
                    <p class="survey-brand-subtitle">Manila Central University</p>
                </div>
            </div>
            <div class="survey-brand-term">{{ $evaluation->academic_year }} | {{ $evaluation->semester }} Semester</div>
        </div>

        <div class="evaluation-context" id="evaluationContext">
            <div>
                <p class="evaluation-context-label">Faculty to Evaluate</p>
                <h1 class="evaluation-context-name">{{ $evaluation->resolved_faculty_name }}</h1>
                @if ($programLabel !== '')
                    <p class="evaluation-context-program">{{ $programLabel }}</p>
                @endif
                <div class="department-pill-group justify-content-start">
                    @forelse($facultyDepartments as $department)
                        <span class="department-pill">{{ $department }}</span>
                    @empty
                        <span class="department-pill">No department</span>
                    @endforelse
                </div>
            </div>
            <div class="evaluation-context-meta">
                <div class="evaluation-context-item">
                    <p class="evaluation-context-label">School Year</p>
                    <p class="evaluation-context-value">{{ $evaluation->academic_year }}</p>
                </div>
                <div class="evaluation-context-item">
                    <p class="evaluation-context-label">Semester</p>
                    <p class="evaluation-context-value">{{ $evaluation->semester }} Semester</p>
                </div>
            </div>
        </div>

        <!-- Progress Bar (hidden during thank you screens) -->
        <div class="progress-bar-custom" id="progressBarContainer">
            <div class="progress-fill" id="progressBar" style="width: 25%"></div>
        </div>

        <!-- Thank You Screen for Successful Submission -->
        <div class="thank-you-screen" id="successThankYou">
            <div class="thank-you-icon">✅</div>
            <h2 class="text-success mb-4">Thank You!</h2>
            <h4 class="text-primary mb-3">Your evaluation has been submitted successfully</h4>
            <p class="lead mb-4">
                We appreciate your feedback! Your responses help us improve the quality of education.
            </p>
            <div class="alert alert-info">
                <strong>Important:</strong> Your responses have been recorded and cannot be modified.
            </div>
            <button type="button" class="btn btn-primary mt-3" onclick="resetForm()">
                Evaluate Another Course
            </button>
        </div>

        <!-- Thank You Screen for Cooldown Period -->
        <div class="thank-you-screen" id="cooldownThankYou">
            <div class="thank-you-icon">⏰</div>
            <h2 class="text-primary mb-4">Thank You for Your Recent Evaluation!</h2>
            <h4 class="mb-3">You've already evaluated this course recently</h4>
            <p class="lead mb-4">
                To prevent duplicate submissions, please wait a moment before submitting another evaluation for this
                course.
            </p>
            <div class="cooldown-timer pulse" id="cooldownDisplay">01:00</div>
            <p class="text-muted">Time remaining</p>
            <div class="alert alert-light mt-4">
                <strong>Feel free to:</strong> Evaluate other courses or return later
            </div>
            <button type="button" class="btn btn-outline-primary mt-3" onclick="backToForm()">
                Select Different Course
            </button>
        </div>

        <form id="evaluationForm" method="POST"
            action="{{ route('evaluation.submit', last(explode('/', $evaluation->form_link))) }}">
            @csrf

            <!-- Section 1: Header & Privacy Consent -->
            <div class="section-card active" data-section="1">
                <h3 class="section-title">Privacy Consent</h3>

                <div class="consent-section">
                    <h4 class="text-primary mb-3">DATA PRIVACY STATEMENT</h4>

                    <p class="mb-3">
                        Manila Central University (MCU) is committed to protecting the privacy of its data subjects and
                        ensuring the safety and security of their personal data under its control and custody.
                    </p>

                    <p class="mb-3">
                        This policy provides information on how the MCU will <strong>collect, use, process, share,
                            secure</strong> and
                        <strong>dispose</strong> of personal data, in accordance with <strong>Republic Act. No.
                            10173</strong>, also known as the
                        <strong>Data Privacy Act of 2012</strong> and its Implementing Rules and Regulations.
                    </p>

                    <p class="mb-4">
                        I understand that my personal information is protected by <strong>RA 10173 (Data Privacy Act of
                            2012)</strong> to
                        provide truthful information. <span class="text-danger">*</span>
                    </p>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="privacy_consent" id="consent_accept"
                            value="accept" required>
                        <label class="form-check-label" for="consent_accept">Accept</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="privacy_consent" id="consent_decline"
                            value="decline">
                        <label class="form-check-label" for="consent_decline">Decline</label>
                    </div>

                    <div class="field-error-message" id="privacyConsentError">
                        Please accept the privacy consent before proceeding.
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 2: Course Selection -->
            <div class="section-card" data-section="2">
                <h3 class="section-title">Course Selection</h3>

                @php
                    $evaluatedScheduleIds = collect($evaluatedScheduleIds ?? [])->map(fn ($id) => (int) $id)->all();
                    $sectionOptions = $schedules
                        ->map(function ($schedule) {
                            return \App\Support\SectionNormalizer::normalize(optional($schedule->facultyCourse)->section);
                        })
                        ->filter(function ($section) {
                            return $section !== null && trim($section) !== '';
                        })
                        ->unique()
                        ->values();
                @endphp

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Select your section:</h5>
                    <select name="section" id="section" class="form-select" required>
                        <option value="">Select your answer</option>
                        @foreach ($sectionOptions as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                        @endforeach
                    </select>
                    <div class="field-error-message" id="sectionError">
                        Please select your section before proceeding.
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Select your course:</h5>
                    <select name="schedule_id" id="schedule_id" class="form-select" required
                        onchange="checkCooldownForSchedule()">
                        <option value="">Select your answer</option>
                        @foreach ($schedules as $schedule)
                            @php
                                $scheduleLabel = \App\Models\Schedule::formatScheduleLabel($schedule->day, $schedule->time);
                                $course = $schedule->facultyCourse->course;
                                $normalizedSection = \App\Support\SectionNormalizer::normalize($schedule->facultyCourse->section ?? '');
                                $fullCourseLabel = trim(($course->class_code ?? '') . ' - ' . ($course->subject_code ?? '') . ' - ' . $normalizedSection . ' - ' . $scheduleLabel);
                                $isAlreadyEvaluated = in_array((int) $schedule->id, $evaluatedScheduleIds, true);
                                $displayCourseLabel = $isAlreadyEvaluated
                                    ? 'Already evaluated today - ' . $fullCourseLabel
                                    : $fullCourseLabel;
                                $compactCourseLabel = trim(($course->class_code ?? '') . ' - ' . $normalizedSection . ' - ' . $scheduleLabel);
                                $shortCourseLabel = \Illuminate\Support\Str::limit($compactCourseLabel, 58);
                                $shortEvaluatedLabel = \Illuminate\Support\Str::limit('Already evaluated today - ' . $compactCourseLabel, 74);
                            @endphp
                            <option value="{{ $schedule->id }}"
                                data-section="{{ $normalizedSection }}"
                                data-course-label="{{ $fullCourseLabel }}"
                                data-already-evaluated="{{ $isAlreadyEvaluated ? '1' : '0' }}"
                                title="{{ $displayCourseLabel }}"
                                @disabled($isAlreadyEvaluated)>
                                {{ $isAlreadyEvaluated ? $shortEvaluatedLabel : $shortCourseLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="course-preview" id="selectedCoursePreview" aria-live="polite">
                        <span class="course-preview-label">Selected course</span>
                        <span class="course-preview-text" id="selectedCoursePreviewText"></span>
                    </div>
                    <div class="form-text">Class Code - Subject Code - Section - Schedule</div>
                    <div class="field-error-message" id="scheduleError">
                        Please select your course before proceeding.
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 3: Rating -->
            <div class="section-card" data-section="3">
                <h3 class="section-title">Subject Rating</h3>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Effectiveness Rating</h5>
                    <p class="mb-4">
                        How would you rate the faculty member's <strong>effectiveness</strong> in <strong>delivering the
                            lesson</strong>
                        and <strong>facilitating learning</strong>? <span class="text-danger">*</span>
                    </p>

                    <div class="rating-container">
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_1" value="1"
                                required>
                            <label for="rating_1">Not Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_2" value="2"
                                required>
                            <label for="rating_2">Somewhat Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_3" value="3"
                                required>
                            <label for="rating_3">Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_4" value="4"
                                required>
                            <label for="rating_4">Very Effective</label>
                        </div>
                    </div>
                    <div class="field-error-message" id="effectivenessRatingError">
                        Please select an effectiveness rating before proceeding.
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 4: Feedback -->
            <div class="section-card" data-section="4">
                <h3 class="section-title">Feedback</h3>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Additional Comments</h5>
                    <p class="mb-3"><strong>Additional Comments on the Faculty Member:</strong></p>
                    <p class="mb-4">
                        Please share any feedback on the faculty's <strong>teaching style</strong>,
                        <strong>clarity</strong>,
                        <strong>engagement</strong>, or <strong>areas for improvement</strong>
                    </p>

                    <textarea name="feedback_comments" id="feedback_comments" class="form-control" rows="6"
                        placeholder="Enter your feedback" minlength="20" maxlength="500" required>{{ old('feedback_comments') }}</textarea>
                    <div class="field-error-message" id="feedbackError">
                        Feedback must be 20 to 500 characters.
                    </div>
                    <small class="text-muted d-block mt-2" id="feedbackHelp">
                        Please write 20 to 500 characters. One-word answers like "good" or "bad" are too short.
                    </small>
                    <small class="d-block mt-1" id="feedbackCounter">
                        20 more characters needed.
                    </small>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary"
                        onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 5: Submit -->
            <div class="section-card" data-section="5">
                <div class="text-center">
                    <div class="illustration mb-4">
                        <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="100" cy="75" r="50" fill="#667eea" opacity="0.2" />
                            <path d="M70 75 L90 95 L130 55" stroke="#667eea" stroke-width="4" fill="none"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <h3 class="text-primary mb-3">Review Your Responses</h3>
                    <p class="mb-4">Please review your answers before submitting the evaluation.</p>

                    <div class="alert alert-info mb-4">
                        <strong>Note:</strong> Once submitted, you cannot modify your responses.
                        Make sure all information is accurate.
                    </div>

                    @unless ($canSubmitEvaluation ?? false)
                        <div class="alert alert-warning mb-4">
                            <strong>View only:</strong> You can open this evaluation form, but only student accounts are allowed to submit an evaluation.
                        </div>
                    @endunless

                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="prevSection()">Previous</button>
                        @if ($canSubmitEvaluation ?? false)
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Submit Evaluation
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-lock me-2"></i>Student Only
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Error Messages (only for non-cooldown errors) -->
    @if (session('error') && !str_contains(session('error'), 'Please wait'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentSection = 1;
        const totalSections = 5;
        let cooldownTimer = null;
        const sectionSelect = document.getElementById('section');
        const scheduleSelect = document.getElementById('schedule_id');
        const selectedCoursePreview = document.getElementById('selectedCoursePreview');
        const selectedCoursePreviewText = document.getElementById('selectedCoursePreviewText');
        const feedbackMinLength = 20;
        const feedbackMaxLength = 500;

        function updateProgressBar() {
            const progress = (currentSection / totalSections) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function showSection(sectionNumber) {
            // Hide thank you screens
            hideAllThankYouScreens();

            // Show progress bar
            document.getElementById('progressBarContainer').style.display = 'block';

            // Hide all form sections
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });

            // Show target section
            document.querySelector(`[data-section="${sectionNumber}"]`).classList.add('active');
            updateProgressBar();
        }

        function hideAllThankYouScreens() {
            document.getElementById('successThankYou').classList.remove('active');
            document.getElementById('cooldownThankYou').classList.remove('active');
        }

        function showSuccessThankYou() {
            // Hide form and progress bar
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById('progressBarContainer').style.display = 'none';

            // Show success thank you screen
            document.getElementById('successThankYou').classList.add('active');
        }

        function showCooldownThankYou(scheduleId) {
            // Hide form and progress bar
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById('progressBarContainer').style.display = 'none';

            // Show cooldown thank you screen
            document.getElementById('cooldownThankYou').classList.add('active');

            // Start countdown if schedule ID is available
            if (scheduleId) {
                startCooldownCountdown(scheduleId);
            }
        }

        function nextSection() {
            if (validateCurrentSection()) {
                if (currentSection < totalSections) {
                    currentSection++;
                    showSection(currentSection);
                }
            }
        }

        function prevSection() {
            if (currentSection > 1) {
                currentSection--;
                showSection(currentSection);
            }
        }

        function validateCurrentSection() {
            const currentCard = document.querySelector(`[data-section="${currentSection}"]`);
            const requiredFields = currentCard.querySelectorAll('[required]');

            for (let field of requiredFields) {
                if (field.type === 'radio') {
                    const radioGroup = currentCard.querySelectorAll(`[name="${field.name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isChecked) {
                        showFieldError(field);
                        return false;
                    } else {
                        clearFieldError(field);
                    }
                } else if (field.name === 'feedback_comments' && !isFeedbackValid(field.value)) {
                    showFieldError(field);
                    return false;
                } else if (!field.value.trim()) {
                    showFieldError(field);
                    return false;
                } else {
                    clearFieldError(field);
                }
            }

            if (currentSection === 1) {
                const consentValue = document.querySelector('input[name="privacy_consent"]:checked')?.value;
                if (consentValue === 'decline') {
                    showInlineError('privacy_consent');
                    return false;
                }
            }

            return true;
        }

        function showFieldError(field) {
            const fieldName = field.name || field.id;

            if (field.type === 'radio') {
                showInlineError(fieldName);
                return;
            }

            field.classList.add('is-invalid');
            showInlineError(fieldName);
            field.focus({
                preventScroll: true
            });
            field.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function clearFieldError(field) {
            const fieldName = field.name || field.id;

            if (field.type === 'radio') {
                clearInlineError(fieldName);
                return;
            }

            field.classList.remove('is-invalid');
            clearInlineError(fieldName);
        }

        function showInlineError(fieldName) {
            const errorMap = {
                privacy_consent: 'privacyConsentError',
                section: 'sectionError',
                schedule_id: 'scheduleError',
                effectiveness_rating: 'effectivenessRatingError',
                feedback_comments: 'feedbackError',
            };

            const groupMap = {
                privacy_consent: '.consent-section',
                effectiveness_rating: '.rating-container',
            };

            const errorEl = document.getElementById(errorMap[fieldName]);
            if (fieldName === 'feedback_comments') {
                updateFeedbackCounter(true);
            }
            errorEl?.classList.add('is-visible');

            if (fieldName === 'feedback_comments') {
                document.getElementById('feedbackHelp')?.classList.add('d-none');
            }

            const fieldEl = document.querySelector(`[name="${fieldName}"]`);
            fieldEl?.classList.add('is-invalid');

            const groupEl = groupMap[fieldName] ? document.querySelector(groupMap[fieldName]) : null;
            groupEl?.classList.add('is-invalid');

            (groupEl || fieldEl || errorEl)?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function clearInlineError(fieldName) {
            const errorMap = {
                privacy_consent: 'privacyConsentError',
                section: 'sectionError',
                schedule_id: 'scheduleError',
                effectiveness_rating: 'effectivenessRatingError',
                feedback_comments: 'feedbackError',
            };

            const groupMap = {
                privacy_consent: '.consent-section',
                effectiveness_rating: '.rating-container',
            };

            document.getElementById(errorMap[fieldName])?.classList.remove('is-visible');
            document.querySelectorAll(`[name="${fieldName}"]`).forEach((field) => {
                field.classList.remove('is-invalid');
            });
            if (groupMap[fieldName]) {
                document.querySelector(groupMap[fieldName])?.classList.remove('is-invalid');
            }

            if (fieldName === 'feedback_comments') {
                document.getElementById('feedbackHelp')?.classList.remove('d-none');
                updateFeedbackCounter();
            }
        }

        function feedbackLength(value) {
            return (value || '').trim().length;
        }

        function isFeedbackValid(value) {
            const length = feedbackLength(value);
            return length >= feedbackMinLength && length <= feedbackMaxLength;
        }

        function updateFeedbackCounter(forceError = false) {
            const field = document.getElementById('feedback_comments');
            const counter = document.getElementById('feedbackCounter');
            const error = document.getElementById('feedbackError');
            if (!field || !counter) {
                return;
            }

            const length = feedbackLength(field.value);
            const remainingMin = Math.max(feedbackMinLength - length, 0);
            const remainingMax = Math.max(feedbackMaxLength - length, 0);
            const isTooShort = length < feedbackMinLength;
            const isTooLong = length > feedbackMaxLength;

            if (isTooShort) {
                counter.textContent = `${remainingMin} more character${remainingMin === 1 ? '' : 's'} needed.`;
                counter.className = 'd-block mt-1 text-danger';
                if (error) {
                    error.textContent = `Please enter at least ${feedbackMinLength} characters. ${remainingMin} more needed.`;
                }
            } else if (isTooLong) {
                counter.textContent = `Too long by ${length - feedbackMaxLength} character${length - feedbackMaxLength === 1 ? '' : 's'}.`;
                counter.className = 'd-block mt-1 text-danger';
                if (error) {
                    error.textContent = `Feedback must not exceed ${feedbackMaxLength} characters.`;
                }
            } else {
                counter.textContent = `${length}/${feedbackMaxLength} characters. ${remainingMax} remaining.`;
                counter.className = 'd-block mt-1 text-success';
                if (error) {
                    error.textContent = 'Feedback must be 20 to 500 characters.';
                }
            }

            const shouldShowError = forceError || isTooShort || isTooLong;
            field.classList.toggle('is-invalid', shouldShowError && length > 0);
        }

        document.getElementById('feedback_comments')?.addEventListener('input', function() {
            updateFeedbackCounter();
            if (isFeedbackValid(this.value)) {
                clearFieldError(this);
            }
        });

        document.querySelectorAll('input[name="privacy_consent"]').forEach((field) => {
            field.addEventListener('change', () => clearInlineError('privacy_consent'));
        });

        document.querySelectorAll('input[name="effectiveness_rating"]').forEach((field) => {
            field.addEventListener('change', () => clearInlineError('effectiveness_rating'));
        });

        document.getElementById('section')?.addEventListener('change', function() {
            if (this.value.trim()) {
                clearFieldError(this);
            }
        });

        document.getElementById('schedule_id')?.addEventListener('change', function() {
            if (this.value.trim()) {
                clearFieldError(this);
            }
        });

        // Handle form submission
        document.getElementById('evaluationForm').addEventListener('submit', function(e) {
            if (!validateCurrentSection()) {
                e.preventDefault();
                return false;
            }

            // Check cooldown before allowing submission
            const scheduleId = scheduleSelect?.value;
            if (scheduleId && isInCooldown(scheduleId)) {
                e.preventDefault();
                showCooldownThankYou(scheduleId);
                return false;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            submitBtn.disabled = true;
        });

        // Cooldown management
        function isInCooldown(scheduleId) {
            const lastSubmission = localStorage.getItem(`lastSubmission_${scheduleId}`);
            if (!lastSubmission) return false;

            const cooldownEnd = parseInt(lastSubmission) + (60 * 1000); // 1 minute in milliseconds
            return Date.now() < cooldownEnd;
        }

        function getRemainingCooldown(scheduleId) {
            const lastSubmission = localStorage.getItem(`lastSubmission_${scheduleId}`);
            if (!lastSubmission) return 0;

            const cooldownEnd = parseInt(lastSubmission) + (60 * 1000);
            const remaining = cooldownEnd - Date.now();
            return remaining > 0 ? Math.ceil(remaining / 1000) : 0;
        }

        function checkCooldownForSchedule() {
            updateSelectedCoursePreview();
            const scheduleId = scheduleSelect?.value;
            if (scheduleId && isInCooldown(scheduleId)) {
                showCooldownThankYou(scheduleId);
            }
        }

        function updateSelectedCoursePreview() {
            if (!scheduleSelect || !selectedCoursePreview || !selectedCoursePreviewText) {
                return;
            }

            const selectedOption = scheduleSelect.selectedOptions?.[0];
            const label = selectedOption?.dataset?.courseLabel || '';

            selectedCoursePreviewText.textContent = label;
            selectedCoursePreview.classList.toggle('is-visible', label !== '');
        }

        function startCooldownCountdown(scheduleId) {
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
            }

            function updateCountdown() {
                const remaining = getRemainingCooldown(scheduleId);
                if (remaining <= 0) {
                    clearInterval(cooldownTimer);
                    // Auto return to course selection
                    currentSection = 2;
                    showSection(currentSection);
                } else {
                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;
                    const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    document.getElementById('cooldownDisplay').textContent = timeString;
                }
            }

            // If we don't have a valid schedule ID or cooldown info, try to extract from server error
            if (!scheduleId || !isInCooldown(scheduleId)) {
                // Try to get initial countdown from server-side session error message
                @if (session('error') && str_contains(session('error'), 'Please wait'))
                    const errorMessage = '{{ session('error') }}';
                    const timeMatch = errorMessage.match(/(\d+):(\d+)/);
                    if (timeMatch) {
                        const minutes = parseInt(timeMatch[1]);
                        const seconds = parseInt(timeMatch[2]);
                        const totalSeconds = (minutes * 60) + seconds;

                        // Set a fake timestamp for countdown
                        const fakeTimestamp = Date.now() - (60000 - (totalSeconds * 1000));
                        localStorage.setItem(`lastSubmission_${scheduleId}`, fakeTimestamp.toString());
                    }
                @endif
            }

            updateCountdown();
            cooldownTimer = setInterval(updateCountdown, 1000);
        }

        function recordSubmission(scheduleId) {
            localStorage.setItem(`lastSubmission_${scheduleId}`, Date.now().toString());
        }

        function resetForm() {
            // Reset form
            document.getElementById('evaluationForm').reset();
            currentSection = 1;
            showSection(currentSection);
            filterSchedulesBySection();
            updateFeedbackCounter();
        }

        function backToForm() {
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
            }
            currentSection = 2; // Go back to course selection
            showSection(currentSection);
            // Clear the course selection to allow choosing a different course
            if (scheduleSelect) {
                scheduleSelect.value = '';
            }
            if (sectionSelect) {
                sectionSelect.value = '';
            }
            filterSchedulesBySection();
            updateSelectedCoursePreview();
        }

        function filterSchedulesBySection(resetSchedule = true) {
            if (!sectionSelect || !scheduleSelect) {
                return;
            }
            const selectedSection = sectionSelect.value.trim();
            const options = scheduleSelect.querySelectorAll('option[data-section]');
            options.forEach((option) => {
                const matches = selectedSection !== '' && option.dataset.section === selectedSection;
                const alreadyEvaluated = option.dataset.alreadyEvaluated === '1';
                option.hidden = !matches;
                option.disabled = !matches || alreadyEvaluated;
            });
            scheduleSelect.disabled = selectedSection === '';
            if (resetSchedule) {
                scheduleSelect.value = '';
            }
            updateSelectedCoursePreview();
        }

        function applySectionFromSchedule(scheduleId) {
            if (!sectionSelect || !scheduleSelect || !scheduleId) {
                return;
            }
            const selectedOption = scheduleSelect.querySelector(`option[value="${scheduleId}"]`);
            if (!selectedOption) {
                return;
            }
            const sectionValue = selectedOption.dataset.section || '';
            sectionSelect.value = sectionValue;
            filterSchedulesBySection(false);
            updateSelectedCoursePreview();
        }

        // Check for successful submission and show thank you screen
        @if (session('success'))
            window.addEventListener('load', function() {
                const scheduleId = '{{ old('schedule_id') }}';
                if (scheduleId) {
                    recordSubmission(scheduleId);
                    applySectionFromSchedule(scheduleId);
                }
                showSuccessThankYou();
            });
        @endif

        // Check for cooldown error and show cooldown thank you screen
        @if (session('error') && str_contains(session('error'), 'Please wait'))
            // Show cooldown thank you screen when there's a cooldown error
            window.addEventListener('load', function() {
                const scheduleId = '{{ old('schedule_id') }}';
                // Force show cooldown thank you screen immediately
                showCooldownThankYou(scheduleId);

                // Also set the schedule dropdown to the previously selected value
                if (scheduleSelect && scheduleId) {
                    scheduleSelect.value = scheduleId;
                }
                applySectionFromSchedule(scheduleId);
                updateSelectedCoursePreview();
            });
        @endif

        if (sectionSelect) {
            sectionSelect.addEventListener('change', () => {
                filterSchedulesBySection();
            });
            filterSchedulesBySection(false);
        }

        if (scheduleSelect) {
            scheduleSelect.addEventListener('change', updateSelectedCoursePreview);
            updateSelectedCoursePreview();
        }

        updateFeedbackCounter();
        updateProgressBar();
    </script>
</body>

</html>
