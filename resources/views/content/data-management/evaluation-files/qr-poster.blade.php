@php
    $formatSemesterLabel = function ($value) {
        $raw = trim((string) ($value ?? ''));
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/', '', $raw));

        return [
            '1' => '1st Semester',
            '1st' => '1st Semester',
            '1stsemester' => '1st Semester',
            '2' => '2nd Semester',
            '2nd' => '2nd Semester',
            '2ndsemester' => '2nd Semester',
            'summer' => 'Summer',
        ][$normalized] ?? $raw;
    };

    $facultyName = $evaluation->resolved_faculty_name;
    $programLabel = $evaluation->resolved_program_label;
    $department = $evaluation->resolved_faculty_department ?: 'No department';
    $semesterLabel = $formatSemesterLabel($evaluation->semester);
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Poster - {{ $facultyName }}</title>
    <style>
        :root {
            --brand: #5c297c;
            --gold: #ffb736;
            --ink: #202124;
            --muted: #5f6368;
            --line: #e7e2ec;
            --surface: #ffffff;
            --soft: #faf7ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f1f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 8px 24px rgba(32, 33, 36, 0.08);
        }

        .toolbar-title {
            margin: 0;
            font-size: 1rem;
            color: var(--brand);
            font-weight: 700;
        }

        .toolbar-subtitle {
            display: block;
            margin-top: 0.2rem;
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 400;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .poster-action-btn {
            border: 0;
            border-radius: 0.55rem;
            padding: 0.7rem 1rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
        }

        .poster-action-btn--primary {
            background: var(--brand);
            color: #fff;
        }

        .poster-action-btn--secondary {
            background: #fff;
            color: var(--brand);
            border: 1px solid var(--line);
        }

        .poster-stack {
            display: grid;
            gap: 1.5rem;
            padding: 1.5rem;
            max-width: 980px;
            margin: 0 auto;
        }

        .poster {
            min-height: 10.5in;
            padding: 0.5in;
            background:
                radial-gradient(circle at 94% 7%, rgba(255, 183, 54, 0.28), transparent 12%),
                radial-gradient(circle at 82% 20%, rgba(16, 170, 184, 0.24), transparent 18%),
                linear-gradient(145deg, #ffffff 0%, #ffffff 58%, #f7efff 100%);
            border-radius: 0.9rem;
            border: 1px solid var(--line);
            box-shadow: 0 18px 45px rgba(32, 33, 36, 0.12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            page-break-after: always;
            position: relative;
            isolation: isolate;
        }

        .poster::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 1.72in;
            background:
                radial-gradient(circle at 86% 18%, rgba(255, 183, 54, 0.35), transparent 13%),
                radial-gradient(circle at 72% 48%, rgba(239, 20, 95, 0.24), transparent 15%),
                linear-gradient(135deg, #300047 0%, #5c297c 58%, #7b2e91 100%);
            z-index: -1;
        }

        .poster::after {
            content: "";
            position: absolute;
            top: 0.2in;
            right: -0.5in;
            width: 2.25in;
            height: 2.25in;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            z-index: -1;
        }

        .poster-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 3px solid var(--gold);
            padding-bottom: 1rem;
            color: #ffffff;
        }

        .eyebrow {
            margin: 0 0 0.35rem;
            color: var(--gold);
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .title {
            margin: 0;
            font-size: 2rem;
            line-height: 1.08;
            color: #ffffff;
        }

        .term {
            align-self: flex-start;
            padding: 0.55rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.32);
            font-weight: 700;
            white-space: nowrap;
            box-shadow: inset 0 0 0 1px rgba(255, 183, 54, 0.2);
        }

        .details {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .info-card {
            border: 1px solid rgba(92, 41, 124, 0.12);
            border-radius: 0.75rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 10px 24px rgba(48, 0, 71, 0.06);
        }

        .label {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .value {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .program {
            color: var(--brand);
        }

        .qr-section {
            text-align: center;
            padding: 1.25rem;
            border-radius: 1rem;
            background:
                radial-gradient(circle at 12% 12%, rgba(255, 183, 54, 0.16), transparent 18%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), #fbf8ff);
            border: 1px solid rgba(92, 41, 124, 0.16);
            box-shadow: 0 18px 34px rgba(48, 0, 71, 0.08);
        }

        .qr-frame {
            position: relative;
            width: min(3.2in, 100%);
            margin: 0 auto 0.9rem;
        }

        .qr-section img.qr-code-image {
            width: min(3.2in, 100%);
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .qr-logo {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 24%;
            aspect-ratio: 1 / 1;
            object-fit: contain;
            padding: 5%;
            border-radius: 0.22in;
            background: #ffffff;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 0 1px rgba(17, 24, 39, 0.08);
        }

        .scan-text {
            margin: 0;
            color: var(--brand);
            font-size: 1.2rem;
            font-weight: 800;
        }

        .link-box {
            margin-top: 1rem;
            padding: 0.8rem;
            border-radius: 0.6rem;
            background: #f4ecfa;
            color: var(--ink);
            font-size: 0.9rem;
            word-break: break-all;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            color: var(--muted);
            border-top: 1px solid var(--line);
            padding-top: 0.8rem;
            font-size: 0.85rem;
        }

        .empty-state {
            max-width: 760px;
            margin: 3rem auto;
            padding: 2rem;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            text-align: center;
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 0.15in;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .poster-stack {
                display: block;
                padding: 0;
                max-width: none;
            }

            .poster {
                width: 100%;
                min-height: 0;
                height: 10.55in;
                padding: 0.28in;
                box-shadow: none;
                border: 0;
                border-radius: 0;
                margin: 0;
                page-break-inside: avoid;
                break-inside: avoid;
                page-break-after: always;
                overflow: hidden;
            }

            .poster::before {
                height: 1.25in;
            }

            .poster::after {
                width: 1.55in;
                height: 1.55in;
                right: -0.35in;
            }

            .poster:last-child {
                page-break-after: auto;
            }

            .poster-header {
                padding-bottom: 0.45rem;
            }

            .eyebrow {
                margin-bottom: 0.2rem;
                font-size: 0.72rem;
            }

            .title {
                font-size: 1.45rem;
            }

            .term {
                padding: 0.35rem 0.55rem;
                font-size: 0.78rem;
            }

            .details {
                gap: 0.45rem;
                margin: 0.55rem 0;
            }

            .info-card {
                border-radius: 0.45rem;
                padding: 0.45rem 0.55rem;
            }

            .label {
                font-size: 0.62rem;
                margin-bottom: 0.12rem;
            }

            .value {
                font-size: 0.82rem;
                line-height: 1.2;
            }

            .qr-section {
                padding: 0.7rem;
                border-radius: 0.55rem;
            }

            .qr-frame {
                width: 2.55in;
                margin-bottom: 0.45rem;
            }

            .qr-section img.qr-code-image {
                width: 2.55in;
            }

            .scan-text {
                font-size: 0.9rem;
            }

            .link-box {
                margin-top: 0.45rem;
                padding: 0.45rem;
                font-size: 0.7rem;
            }

            .footer {
                padding-top: 0.45rem;
                font-size: 0.68rem;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div>
            <h1 class="toolbar-title">QR Poster Generator</h1>
            <span class="toolbar-subtitle">{{ $facultyName }} | {{ $evaluation->academic_year }} - {{ $semesterLabel }}</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="poster-action-btn poster-action-btn--primary" onclick="downloadPosterPdf()">
                Download PDF
            </button>
            <button type="button" class="poster-action-btn poster-action-btn--secondary" onclick="printPoster()">
                Print
            </button>
        </div>
    </div>

    @if ($schedules->isEmpty())
        <div class="empty-state">
            <h2>No schedules found</h2>
            <p>No course schedules are attached to this faculty evaluation for the selected academic year and semester.</p>
        </div>
    @else
        <main class="poster-stack">
            @foreach ($schedules as $schedule)
                @php
                    $facultyCourse = $schedule->facultyCourse;
                    $course = $facultyCourse?->course;
                    $subject = $course?->subject_code ?? 'Subject not available';
                    $section = $facultyCourse?->section ?? 'N/A';
                    $scheduleText = \App\Models\Schedule::formatScheduleLabel($schedule->day, $schedule->time);
                @endphp

                <section class="poster">
                    <div>
                        <div class="poster-header">
                            <div>
                                <p class="eyebrow">Post-Class Student Survey</p>
                                <h2 class="title">Scan to Evaluate</h2>
                            </div>
                            <div class="term">{{ $evaluation->academic_year }} | {{ $semesterLabel }}</div>
                        </div>

                        <div class="details">
                            <div class="info-card">
                                <span class="label">Faculty</span>
                                <p class="value">{{ $facultyName }}</p>
                            </div>
                            <div class="info-card">
                                <span class="label">Department</span>
                                <p class="value">{{ $department }}</p>
                            </div>
                            @if ($programLabel !== '')
                                <div class="info-card" style="grid-column: 1 / -1;">
                                    <span class="label">Program</span>
                                    <p class="value program">{{ $programLabel }}</p>
                                </div>
                            @endif
                            <div class="info-card" style="grid-column: 1 / -1;">
                                <span class="label">Subject</span>
                                <p class="value">{{ $subject }}</p>
                            </div>
                            <div class="info-card">
                                <span class="label">Section</span>
                                <p class="value">{{ $section }}</p>
                            </div>
                            <div class="info-card">
                                <span class="label">Schedule</span>
                                <p class="value">{{ $scheduleText }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="qr-section">
                            <div class="qr-frame">
                                <img src="{{ $qrCodeDataUri }}" alt="Evaluation QR code" class="qr-code-image">
                                @if (!empty($qrLogoDataUri))
                                    <img src="{{ $qrLogoDataUri }}" alt="MCU logo" class="qr-logo">
                                @endif
                            </div>
                            <p class="scan-text">Scan this QR code after class</p>
                            <div class="link-box">{{ $evaluation->form_link }}</div>
                        </div>
                    </div>

                    <div class="footer">
                        <span>Manila Central University</span>
                        <span>Post-Class Survey System</span>
                    </div>
                </section>
            @endforeach
        </main>
    @endif
    <script>
        function downloadPosterPdf() {
            document.title = @json('QR Poster - ' . $facultyName . ' - ' . $evaluation->academic_year . ' - ' . $semesterLabel);
            window.print();
        }

        function printPoster() {
            window.print();
        }
    </script>
</body>

</html>
