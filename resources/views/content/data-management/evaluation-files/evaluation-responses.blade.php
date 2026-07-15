@extends('layouts/contentNavbarLayout')

@section('title', 'Evaluation Responses')

@section('page-style')
    <style>
        .responses-page {
            --mcu-purple-midnight: #3a0050;
            --mcu-purple: #5c297c;
            --mcu-purple-haze: #6f2a8f;
            --mcu-gold: #ffb736;
            --mcu-gold-soft: #fff3d4;
            --mcu-border: rgba(92, 41, 124, 0.12);
            --mcu-text: #26364d;
        }

        .responses-hero {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.28), transparent 11rem),
                radial-gradient(circle at 82% 100%, rgba(236, 15, 90, 0.28), transparent 12rem),
                linear-gradient(120deg, var(--mcu-purple-midnight), var(--mcu-purple), var(--mcu-purple-haze));
            border: 0;
            border-radius: 1.1rem;
            box-shadow: 0 1rem 2.2rem rgba(44, 0, 63, 0.18);
            color: #ffffff;
            overflow: hidden;
        }

        .responses-hero .card-body {
            position: relative;
            z-index: 1;
        }

        .responses-hero-eyebrow {
            color: var(--mcu-gold);
            display: block;
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .responses-hero-title {
            color: #ffffff;
            font-weight: 900;
            margin: 0.15rem 0 0.35rem;
        }

        .responses-hero-meta {
            color: rgba(255, 255, 255, 0.88);
            font-weight: 700;
        }

        .responses-chip-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.8rem;
        }

        .responses-chip {
            align-items: center;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            color: #ffffff;
            display: inline-flex;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.25;
            padding: 0.38rem 0.7rem;
        }

        .responses-hero-actions .btn {
            border-radius: 0.75rem;
            font-weight: 800;
            min-height: 2.75rem;
        }

        .responses-hero-actions .btn-primary {
            background: linear-gradient(135deg, var(--mcu-gold), #ffcf62) !important;
            border: 1px solid rgba(255, 220, 122, 0.5);
            color: var(--mcu-purple-midnight);
            box-shadow: 0 0.85rem 1.7rem rgba(255, 183, 54, 0.24);
        }

        .responses-hero-actions .btn-primary:hover,
        .responses-hero-actions .btn-primary:focus {
            background: linear-gradient(135deg, #ffcf62, var(--mcu-gold)) !important;
            color: var(--mcu-purple-midnight);
            transform: translateY(-1px);
        }

        .responses-hero-actions .btn-outline-secondary {
            background: rgba(58, 0, 80, 0.5);
            border-color: rgba(255, 183, 54, 0.55);
            color: #ffffff;
            box-shadow: 0 0.85rem 1.7rem rgba(44, 0, 63, 0.18);
        }

        .responses-hero-actions .btn-outline-secondary:hover,
        .responses-hero-actions .btn-outline-secondary:focus {
            background: var(--mcu-purple-midnight);
            border-color: var(--mcu-gold);
            color: var(--mcu-gold-soft);
            transform: translateY(-1px);
        }

        .responses-metric-card,
        .responses-panel {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.1), transparent 8rem),
                linear-gradient(180deg, #ffffff, #fbf7ff);
            border: 1px solid var(--mcu-border);
            border-radius: 1rem;
            box-shadow: 0 0.85rem 1.85rem rgba(44, 0, 63, 0.08);
            overflow: hidden;
        }

        .responses-metric-card {
            min-height: 8.25rem;
            position: relative;
        }

        .responses-metric-card::after {
            content: "";
            position: absolute;
            right: -2.2rem;
            bottom: -2.8rem;
            width: 5.5rem;
            height: 5.5rem;
            border: 1px solid rgba(255, 183, 54, 0.35);
            border-radius: 50%;
        }

        .responses-metric-icon {
            align-items: center;
            background: rgba(92, 41, 124, 0.1);
            border: 1px solid rgba(92, 41, 124, 0.12);
            border-radius: 0.85rem;
            color: var(--mcu-purple);
            display: inline-flex;
            font-size: 1.35rem;
            height: 2.6rem;
            justify-content: center;
            margin-bottom: 0.8rem;
            width: 2.6rem;
        }

        .responses-metric-value {
            color: var(--mcu-purple-midnight);
            font-size: 1.95rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 0.35rem;
        }

        .responses-metric-label {
            color: #52627a;
            font-size: 0.86rem;
            font-weight: 800;
            margin: 0;
        }

        .responses-panel-header {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.18), transparent 9rem),
                linear-gradient(120deg, var(--mcu-purple-midnight), var(--mcu-purple));
            border-bottom: 0;
            color: #ffffff;
            padding: 1.1rem 1.35rem;
        }

        .responses-panel-header .card-title {
            color: #ffffff;
            font-weight: 900;
        }

        .responses-distribution-item {
            background: #ffffff;
            border: 1px solid var(--mcu-border);
            border-radius: 0.9rem;
            padding: 0.9rem;
        }

        .responses-distribution-label {
            color: var(--mcu-text);
            font-weight: 800;
        }

        .responses-distribution-count {
            color: var(--mcu-purple);
            font-weight: 900;
        }

        .responses-panel .progress {
            background: #f1e8f5;
            border-radius: 999px;
            overflow: hidden;
        }

        .responses-panel .progress-bar {
            background: linear-gradient(90deg, var(--mcu-gold), var(--mcu-purple)) !important;
        }

        .responses-table-card {
            border: 1px solid var(--mcu-border);
            border-radius: 1rem;
            box-shadow: 0 1rem 2rem rgba(44, 0, 63, 0.08);
            overflow: hidden;
        }

        .responses-table {
            margin-bottom: 0;
        }

        .responses-table thead th {
            background: #fbf7ff;
            border-bottom: 1px solid rgba(92, 41, 124, 0.1);
            color: #71809a;
            font-size: 0.74rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            padding: 1rem;
            text-transform: uppercase;
        }

        .responses-table tbody td {
            border-color: rgba(92, 41, 124, 0.08);
            color: var(--mcu-text);
            padding: 1rem;
            vertical-align: middle;
        }

        .responses-table tbody tr:hover {
            background: #fbf7ff;
        }

        .responses-table thead th.sortable {
            cursor: pointer;
            user-select: none;
        }

        .responses-sort-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .responses-sort-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 12px;
            color: #94a3b8;
            font-size: 0.65rem;
        }

        .responses-sort-indicator i {
            display: none;
        }

        .responses-table thead th.sorted-asc .responses-sort-indicator,
        .responses-table thead th.sorted-desc .responses-sort-indicator {
            color: #5c297c;
        }

        .responses-table thead th.sorted-asc .responses-sort-indicator .icon-up,
        .responses-table thead th.sorted-desc .responses-sort-indicator .icon-down {
            display: inline-flex;
        }

        .responses-feedback {
            background: #ffffff;
            border: 1px solid rgba(92, 41, 124, 0.1);
            border-radius: 0.75rem;
            color: var(--mcu-text);
            max-width: 420px;
            padding: 0.7rem 0.85rem;
            white-space: normal;
            word-break: break-word;
        }

        .responses-course-code {
            color: var(--mcu-purple);
            display: block;
            font-weight: 900;
            margin-bottom: 0.15rem;
        }

        .responses-rating-badge {
            border-radius: 999px;
            font-weight: 900;
            padding: 0.42rem 0.72rem;
        }

        div[data-table-id="responsesTable"] [data-table-info] {
            color: #52627a;
            font-size: 0.875rem;
            font-weight: 700;
        }

        div[data-table-id="responsesTable"] .pagination .page-link {
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 0.9rem;
            margin: 0 0.1rem;
            color: var(--mcu-purple);
            background-color: #f4edf8;
            font-weight: 600;
            transition: background-color 0.15s ease;
        }

        div[data-table-id="responsesTable"] .pagination .page-link:hover:not(.disabled) {
            background-color: var(--mcu-gold-soft);
            color: var(--mcu-purple-midnight);
        }

        div[data-table-id="responsesTable"] .pagination .page-item.active .page-link {
            background-color: #5c297c;
            color: #ffffff;
        }

        div[data-table-id="responsesTable"] .pagination .page-item.active .page-link:hover {
            background-color: #4b2266;
            color: #ffffff;
        }

        div[data-table-id="responsesTable"] .pagination .page-link span {
            color: inherit;
        }

        div[data-table-id="responsesTable"] .pagination .page-item.disabled .page-link {
            background-color: #e2e8f0;
            color: #94a3b8;
        }

        div[data-table-id="responsesTable"] .pagination .page-item.disabled .page-link,
        div[data-table-id="responsesTable"] .pagination .page-item.active .page-link,
        div[data-table-id="responsesTable"] .pagination .page-link {
            box-shadow: none;
        }

        .table-filter-dropdown .filter-toggle {
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 0 0.75rem;
            background-color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            line-height: 1.2;
            min-height: calc(2.25rem + 2px);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .table-filter-dropdown .filter-toggle i {
            color: #94a3b8;
            font-size: 1rem;
        }

        .table-filter-dropdown .filter-toggle:focus,
        .table-filter-dropdown .filter-toggle:focus-visible {
            outline: none;
            border-color: #5c297c;
            box-shadow: 0 0 0 2px rgba(92, 41, 124, 0.12);
        }

        .table-filter-dropdown .filter-toggle.is-active {
            border-color: #5c297c;
            color: #5c297c;
        }

        .table-filter-dropdown .dropdown-menu {
            min-width: 260px;
            border-radius: 0.75rem;
            border: 1px solid var(--mcu-border);
            box-shadow: 0 10px 35px rgba(44, 0, 63, 0.14);
        }

        .table-filter-dropdown label {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #94a3b8;
        }

        .table-filter-reset {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5c297c;
        }

        .responses-export-modal .modal-content {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1.5rem 3rem rgba(44, 0, 63, 0.24);
            overflow: hidden;
        }

        .responses-export-modal .modal-header {
            align-items: center;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.24), transparent 9rem),
                linear-gradient(120deg, var(--mcu-purple-midnight), var(--mcu-purple));
            border-bottom: 0;
            color: #ffffff;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }

        .responses-export-modal .modal-title {
            color: #ffffff;
            font-weight: 900;
        }

        .responses-export-close {
            align-items: center;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 999px;
            color: #ffffff;
            display: inline-flex;
            font-size: 1.2rem;
            height: 2.15rem;
            justify-content: center;
            line-height: 1;
            padding: 0;
            transition: background-color 0.16s ease, transform 0.16s ease;
            width: 2.15rem;
        }

        .responses-export-close:hover,
        .responses-export-close:focus {
            background: rgba(255, 183, 54, 0.22);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .responses-export-modal .modal-body {
            background: #fbf7ff;
            padding: 1.5rem;
        }

        .responses-export-modal .modal-footer {
            background: #ffffff;
            border-top: 1px solid var(--mcu-border);
            gap: 0.75rem;
            padding: 1rem 1.5rem;
        }

        .responses-export-modal .modal-footer .btn {
            border-radius: 0.75rem;
            font-weight: 800;
            min-width: 7.5rem;
            padding: 0.7rem 1.15rem;
        }

        .responses-export-modal .modal-footer .btn-outline-secondary {
            background: #ffffff;
            border-color: rgba(92, 41, 124, 0.28);
            color: var(--mcu-purple);
        }

        .responses-export-modal .modal-footer .btn-outline-secondary:hover,
        .responses-export-modal .modal-footer .btn-outline-secondary:focus {
            background: var(--mcu-gold-soft);
            border-color: var(--mcu-gold);
            color: var(--mcu-purple-midnight);
        }

        .responses-export-modal .modal-footer .btn-primary {
            background: linear-gradient(135deg, var(--mcu-purple-midnight), var(--mcu-purple)) !important;
            border: 0;
            box-shadow: 0 0.75rem 1.5rem rgba(92, 41, 124, 0.2);
        }

        .responses-export-modal .modal-footer .btn-primary:hover,
        .responses-export-modal .modal-footer .btn-primary:focus {
            background: linear-gradient(135deg, var(--mcu-purple), var(--mcu-purple-haze)) !important;
        }

        .responses-export-modal .form-label {
            color: #34445e;
            font-weight: 800;
        }

        .responses-export-modal .form-control,
        .responses-export-modal .form-select {
            border-color: rgba(92, 41, 124, 0.18);
            border-radius: 0.75rem;
            min-height: 2.7rem;
        }

        .responses-export-modal .form-control:focus,
        .responses-export-modal .form-select:focus {
            border-color: var(--mcu-purple);
            box-shadow: 0 0 0 0.2rem rgba(92, 41, 124, 0.12);
        }
        .str-orange{
            background: #DB6F3C;
            padding: 4px;
            border-radius: 5px;
        }
        .str-pink{
            background: #B52C64;
            padding: 4px;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .responses-hero .d-flex {
                align-items: flex-start !important;
                flex-direction: column;
            }

            .responses-hero-actions {
                width: 100%;
            }

            .responses-hero-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="py-4 responses-page">
        {{-- Header --}}
        <div class="card mb-4 responses-hero">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="responses-hero-eyebrow">Post-Class Student Survey</span>
                        <h4 class="responses-hero-title">Evaluation Responses</h4>
                        <p class="responses-hero-meta mb-0">
                            Faculty: <strong class="str-orange">{{ $evaluation->resolved_faculty_name }}</strong> |
                            Academic Year: <strong class="str-pink">{{ $evaluation->academic_year }}</strong> |
                            Semester: <strong class="str-orange">{{ $evaluation->semester }}</strong>
                        </p>
                        @php
                            $facultyDepartments = collect(explode(',', $evaluation->resolved_faculty_department ?? ''))
                                ->map(function ($value) {
                                    return trim($value);
                                })
                                ->filter(function ($value) {
                                    return $value !== '';
                                })
                                ->values();
                        @endphp
                        <div class="responses-chip-group">
                            @forelse($facultyDepartments as $department)
                                <span class="responses-chip">{{ $department }}</span>
                            @empty
                                <span class="responses-chip">No department</span>
                            @endforelse
                        </div>
                    </div>
                    @php
                        $from = request('from');
                        $department = request('department');
                        $academicYear = request('academic_year', $evaluation->academic_year);
                        $semester = request('semester', $evaluation->semester);
                        $subjectType = request('subject_type', 'all');
                        
                        if ($from === 'reports') {
                            $backRoute = route('reports') . '?department=' . $department . '&academic_year=' . $academicYear . '&semester=' . $semester . '&subject_type=' . $subjectType;
                            $backLabel = 'Back to Reports';
                        } else {
                            $backRoute = route('dm.evaluation');
                            $backLabel = 'Back to Evaluations';
                        }
                    @endphp
                    <div class="d-flex gap-2 responses-hero-actions">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#exportResponsesModal">
                            <i class="bx bx-download me-1"></i>Export Responses
                        </button>
                        <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>{{ $backLabel }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Export Modal --}}
        <div class="modal fade responses-export-modal" id="exportResponsesModal" tabindex="-1" aria-labelledby="exportResponsesLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="GET" action="{{ route('dm.evaluation.responses.export', $evaluation) }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportResponsesLabel">Export Responses</h5>
                            <button type="button" class="responses-export-close" data-bs-dismiss="modal"
                                aria-label="Close">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="exportStartDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="exportStartDate" name="start_date">
                                <small class="text-muted">Leave blank to include all dates.</small>
                            </div>
                            <div class="mb-3">
                                <label for="exportEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="exportEndDate" name="end_date">
                            </div>
                            <div class="mb-3">
                                <label for="exportAcademicYear" class="form-label">Academic Year</label>
                                <select class="form-select" id="exportAcademicYear" name="academic_year">
                                    <option value="all">All</option>
                                    <option value="{{ $evaluation->academic_year }}" selected>
                                        {{ $evaluation->academic_year }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label for="exportSemester" class="form-label">Semester</label>
                                <select class="form-select" id="exportSemester" name="semester">
                                    <option value="all">All</option>
                                    <option value="{{ $evaluation->semester }}" selected>
                                        {{ $evaluation->semester }}
                                    </option>
                                </select>
                            </div>
                            <!-- Hidden fields to preserve filter context from dashboard -->
                            <input type="hidden" name="academic_year" value="{{ request('academic_year', $evaluation->academic_year) }}">
                            <input type="hidden" name="semester" value="{{ request('semester', $evaluation->semester) }}">
                            <input type="hidden" name="subject_type" value="{{ request('subject_type', 'all') }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-download me-1"></i>Export CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center responses-metric-card">
                    <div class="card-body">
                        <span class="responses-metric-icon"><i class="bx bx-message-square-check"></i></span>
                        <div class="responses-metric-value">{{ $responses->count() }}</div>
                        <p class="responses-metric-label">Total Responses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center responses-metric-card">
                    <div class="card-body">
                        <span class="responses-metric-icon"><i class="bx bx-star"></i></span>
                        <div class="responses-metric-value">
                            {{ $responses->count() > 0 ? number_format($responses->avg('effectiveness_rating'), 1) : '0.0' }}
                        </div>
                        <p class="responses-metric-label">Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center responses-metric-card">
                    <div class="card-body">
                        <span class="responses-metric-icon"><i class="bx bx-book-open"></i></span>
                        <div class="responses-metric-value">{{ $coursesEvaluatedCount }}</div>
                        <p class="responses-metric-label">Courses Evaluated</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center responses-metric-card">
                    <div class="card-body">
                        <span class="responses-metric-icon"><i class="bx bx-comment-detail"></i></span>
                        <div class="responses-metric-value">
                            {{ $responses->whereNotNull('feedback_comments')->where('feedback_comments', '!=', '')->count() }}
                        </div>
                        <p class="responses-metric-label">With Feedback</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rating Distribution --}}
        <div class="card mb-4 responses-panel">
            <div class="card-header responses-panel-header">
                <h5 class="card-title mb-0">Rating Distribution</h5>
            </div>
            <div class="card-body">
                @php
                    $ratingCounts = $responses->groupBy('effectiveness_rating')->map->count();
                    $totalResponses = $responses->count();
                @endphp

                @if($totalResponses > 0)
                    <div class="row">
                        @foreach(['4' => 'Very Effective', '3' => 'Effective', '2' => 'Somewhat Effective', '1' => 'Not Effective'] as $rating => $label)
                            @php
                                $count = $ratingCounts->get($rating, 0);
                                $percentage = ($count / $totalResponses) * 100;
                            @endphp
                            <div class="col-md-3">
                                <div class="responses-distribution-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="responses-distribution-label">{{ $label }}</span>
                                        <span class="responses-distribution-count">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-bar-chart display-4 mb-3"></i>
                        <p>No responses to display distribution</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Anonymous Responses Table --}}
        <div class="card responses-table-card" data-table-controller data-table-id="responsesTable">
            <div class="card-header responses-panel-header">
                <h5 class="card-title mb-0">Anonymous Responses</h5>
            </div>
            <div class="card-body border-0 evaluation-controls">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="responsesRowsPerPage" class="text-muted small">Lines per page</label>
                            <select id="responsesRowsPerPage" class="form-select evaluation-page-size fw-bold" style="width: auto;" data-table-length>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <div class="dropdown table-filter-dropdown">
                                <button class="filter-toggle" type="button" id="responsesFilterToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span>Filters</span>
                                    <i class="bx bx-filter"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3">
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Rating</label>
                                        <select id="responsesFilterRating" class="form-select">
                                            <option value="all" selected>All Ratings</option>
                                            <option value="4">Very Effective</option>
                                            <option value="3">Effective</option>
                                            <option value="2">Somewhat Effective</option>
                                            <option value="1">Not Effective</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Date Range</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="date" class="form-control" id="responsesFilterStartDate">
                                            <span class="text-muted small">to</span>
                                            <input type="date" class="form-control" id="responsesFilterEndDate">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-link p-0 table-filter-reset" id="responsesFilterReset">Reset Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table responses-table" id="responsesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th data-sort-key="course" class="sortable" data-sort-state="none">
                                    <span class="responses-sort-wrapper">
                                        <span>Course</span>
                                        <span class="responses-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="rating" data-sort-type="number" class="sortable" data-sort-state="none">
                                    <span class="responses-sort-wrapper">
                                        <span>Rating</span>
                                        <span class="responses-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>Feedback</th>
                                <th data-sort-key="date" data-sort-type="number" class="sortable" data-sort-state="none">
                                    <span class="responses-sort-wrapper">
                                        <span>Date</span>
                                        <span class="responses-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($responses as $index => $response)
                                @php
                                    $courseCode = $response->resolved_course_code;
                                    $courseName = $response->resolved_course_name;
                                    $scheduleTime = $response->resolved_schedule_time;
                                    $scheduleDays = $response->resolved_schedule_days;
                                @endphp
                                <tr data-response-row
                                    data-sort-course="{{ strtolower(trim(($courseCode ?? '') . ' ' . ($courseName ?? ''))) }}"
                                    data-sort-rating="{{ $response->effectiveness_rating }}"
                                    data-sort-date="{{ $response->created_at->timestamp }}"
                                    data-rating="{{ $response->effectiveness_rating }}"
                                    data-date="{{ $response->created_at->format('Y-m-d') }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong class="responses-course-code">{{ $courseCode }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $courseName }}</small>
                                            @if($scheduleTime || $scheduleDays)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $scheduleTime ?? 'No time' }}
                                                    @if($scheduleDays)
                                                        • {{ $scheduleDays }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge responses-rating-badge
                                                                @if($response->effectiveness_rating == '4') bg-success 
                                                                @elseif($response->effectiveness_rating == '3') bg-info 
                                                                @elseif($response->effectiveness_rating == '2') bg-warning 
                                                                @else bg-danger @endif">
                                            {{ $response->effectiveness_text }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($response->feedback_comments)
                                            <div class="responses-feedback">
                                                {{ $response->feedback_comments }}
                                            </div>
                                        @else
                                            <span class="text-muted">No feedback provided</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $response->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bx bx-bar-chart display-4 text-muted mb-3"></i>
                                            <h5 class="mb-2">No responses yet</h5>
                                            <p class="text-muted mb-0">Students haven't submitted any evaluations yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            <tr data-empty-search style="display: none;">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No matching responses found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="text-muted" data-table-info></div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <nav aria-label="Responses pagination">
                            <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('components.table-controller-script')
    <script>
        const responsesSortState = { key: null, direction: 'asc' };
        const responsesFilters = {
            rating: 'all',
            startDate: '',
            endDate: '',
        };

        document.addEventListener('DOMContentLoaded', () => {
            const controllerRoot = document.querySelector('[data-table-id="responsesTable"]');
            let responsesController = null;
            if (controllerRoot && window.TableController) {
                window.tableControllers = window.tableControllers || {};
                responsesController = new TableController(controllerRoot);
                window.tableControllers.responsesTable = responsesController;
                initResponsesSorting(responsesController);
            }

            initResponsesFilters(responsesController);
        });

        function initResponsesSorting(controller) {
            const headers = document.querySelectorAll('#responsesTable thead th[data-sort-key]');
            headers.forEach((header) => {
                header.dataset.sortState = 'none';
                header.addEventListener('click', () => handleResponsesSort(header, headers, controller));
            });
            updateResponsesSortIndicators(headers);
        }

        function handleResponsesSort(activeHeader, headers, controller) {
            const sortKey = activeHeader.dataset.sortKey;
            const sortType = activeHeader.dataset.sortType || 'string';

            if (responsesSortState.key === sortKey) {
                responsesSortState.direction = responsesSortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                responsesSortState.key = sortKey;
                responsesSortState.direction = 'asc';
            }

            const rows = Array.from(document.querySelectorAll('#responsesTable tbody tr[data-response-row]'));
            rows.sort((rowA, rowB) => {
                const valueA = rowA.dataset[`sort${sortKey.charAt(0).toUpperCase()}${sortKey.slice(1)}`];
                const valueB = rowB.dataset[`sort${sortKey.charAt(0).toUpperCase()}${sortKey.slice(1)}`];

                if (sortType === 'number') {
                    return (Number(valueA) - Number(valueB)) * (responsesSortState.direction === 'asc' ? 1 : -1);
                }

                const compare = String(valueA).localeCompare(String(valueB));
                return compare * (responsesSortState.direction === 'asc' ? 1 : -1);
            });

            const tbody = document.querySelector('#responsesTable tbody');
            rows.forEach((row) => tbody.appendChild(row));

            updateResponsesSortIndicators(headers);
            controller?.refresh?.();
        }

        function updateResponsesSortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';
            });

            if (!responsesSortState.key) return;

            headers.forEach((header) => {
                if (header.dataset.sortKey === responsesSortState.key) {
                    header.classList.add(responsesSortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
                    header.dataset.sortState = responsesSortState.direction;
                }
            });
        }

        function initResponsesFilters(controller) {
            const ratingSelect = document.getElementById('responsesFilterRating');
            const startDateInput = document.getElementById('responsesFilterStartDate');
            const endDateInput = document.getElementById('responsesFilterEndDate');
            const resetButton = document.getElementById('responsesFilterReset');
            const filterToggle = document.getElementById('responsesFilterToggle');

            if (ratingSelect) {
                ratingSelect.addEventListener('change', () => {
                    responsesFilters.rating = ratingSelect.value || 'all';
                    applyResponsesFilters(controller, { resetPage: true, filterToggle });
                });
            }

            if (startDateInput) {
                startDateInput.addEventListener('change', () => {
                    responsesFilters.startDate = startDateInput.value || '';
                    applyResponsesFilters(controller, { resetPage: true, filterToggle });
                });
            }

            if (endDateInput) {
                endDateInput.addEventListener('change', () => {
                    responsesFilters.endDate = endDateInput.value || '';
                    applyResponsesFilters(controller, { resetPage: true, filterToggle });
                });
            }

            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    responsesFilters.rating = 'all';
                    responsesFilters.startDate = '';
                    responsesFilters.endDate = '';
                    if (ratingSelect) ratingSelect.value = 'all';
                    if (startDateInput) startDateInput.value = '';
                    if (endDateInput) endDateInput.value = '';
                    applyResponsesFilters(controller, { resetPage: true, filterToggle });
                });
            }

            applyResponsesFilters(controller, { resetPage: true, filterToggle });
        }

        function applyResponsesFilters(controller, { resetPage = false, filterToggle = null } = {}) {
            const rows = document.querySelectorAll('#responsesTable tbody tr[data-response-row]');
            rows.forEach((row) => {
                const rating = row.dataset.rating || '';
                const dateValue = row.dataset.date || '';

                const ratingMatch = responsesFilters.rating === 'all' || rating === responsesFilters.rating;
                const startMatch = !responsesFilters.startDate || (dateValue && dateValue >= responsesFilters.startDate);
                const endMatch = !responsesFilters.endDate || (dateValue && dateValue <= responsesFilters.endDate);

                const shouldShow = ratingMatch && startMatch && endMatch;
                row.toggleAttribute('data-ignore', !shouldShow);
                row.style.display = shouldShow ? '' : 'none';
            });

            updateResponsesFilterToggleState(filterToggle);
            if (controller?.applyFilters) {
                controller.applyFilters({ resetPage });
            } else {
                controller?.refresh?.();
            }
        }

        function updateResponsesFilterToggleState(filterToggle) {
            if (!filterToggle) return;
            const isActive = responsesFilters.rating !== 'all'
                || responsesFilters.startDate !== ''
                || responsesFilters.endDate !== '';
            filterToggle.classList.toggle('is-active', isActive);
        }
    </script>
@endsection
