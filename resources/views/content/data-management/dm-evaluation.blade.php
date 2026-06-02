@extends('layouts/contentNavbarLayout')

@php
    $container = 'container-xxl';
    $sortedEvaluations = $evaluations
        ->sortByDesc(function ($evaluation) {
            return $evaluation->created_at ?? ($evaluation->id ?? 0);
        })
        ->values();

    $availableAcademicYears = collect($academicYearOptions ?? [])
        ->filter(function ($year) {
            return trim($year ?? '') !== '';
        })
        ->unique()
        ->sort()
        ->values();

    $availableSemesters = collect($semesterOptions ?? [])
        ->filter(function ($option) {
            return is_array($option) ? trim($option['value'] ?? '') !== '' : trim($option ?? '') !== '';
        })
        ->map(function ($option) {
            if (is_array($option)) {
                $value = trim($option['value']);
                $label = trim($option['label'] ?? $value);
                return ['value' => $value, 'label' => $label !== '' ? $label : $value];
            }
            $value = trim($option);
            return ['value' => $value, 'label' => $value];
        })
        ->unique('value')
        ->sortBy('label')
        ->values();

    $predefinedDepartments = [
        'Academic Department',
        'Admissions and Financial Aid Department',
        'Basic Education',
        'Campus Development Department',
        'College of Arts & Sciences',
        'College of Dentistry',
        'College of Medical Technology',
        'College of Medicine',
        'College of Nursing',
        'College of Optometry',
        'College of Pharmacy',
        'College of Physical Therapy',
        'Executive Vice Chair',
        'Executive Vice President',
        'External Affairs Office',
        'Finance Department',
        'Human Resource Department',
        'Information Technology Department',
        'Institute of Education',
        'Institutional Research Office',
        'Internal Audit Office',
        'Lead Institute',
        'Library Services Department',
        'Marketing Department',
        'Office of the President',
        'Office of the Registrar',
        'Quality Assurance Office',
        'Research Ethics Office',
        'School of Business and Management',
        'Student Affairs Services',
    ];

    $departmentFilterOptions = collect($predefinedDepartments)
        ->merge(
            $evaluations->flatMap(function ($evaluation) {
                return collect(explode(',', $evaluation->resolved_faculty_department ?? ''))
                    ->map(function ($value) {
                        return trim($value);
                    })
                    ->filter(function ($value) {
                        return $value !== '';
                    });
            }),
        )
        ->unique()
        ->sort()
        ->values();

    $accessLevels = collect(auth()->user()?->access_level ?? []);
    $isAdmin = auth()->user()?->role === 'Admin';
    $canManageEvaluations = $isAdmin || $accessLevels->contains('Manage Evaluations');
    $canManageEvaluationQr = $isAdmin || $accessLevels->contains('Manage Evaluation QR/Link');
    $canViewReports =
        $isAdmin || $accessLevels->contains('View All Reports') || $accessLevels->contains('View Department Reports');
    $canEvaluationQrOnly = $canManageEvaluationQr && !$canManageEvaluations;
    $canAdd = $isAdmin;
    $canEdit = $canManageEvaluations;
    $canDelete = $isAdmin;
    $showDeleteDisabled = !$isAdmin;
    $canImport = $canManageEvaluations;

    $currentUser = auth()->user();
    $allowedDepartmentFilterOptions = collect();
    if (!$isAdmin && $currentUser) {
        $allowedDepartmentFilterOptions = collect(
            array_merge(
                \App\Models\Faculty::normalizeDepartmentList($currentUser->department ?? ''),
                \App\Models\Faculty::normalizeDepartmentList(optional($currentUser->faculty)->department ?? ''),
            ),
        )
            ->unique()
            ->sort()
            ->values()
            ->map(function ($department) {
                return [
                    'value' => strtolower($department),
                    'label' => $department,
                ];
            });
    }
@endphp

@section('title', 'Data Management - Evaluation')

@section('page-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --dm-pill-max: clamp(5.5rem, 16vw, 10rem);
            --dm-cell-max: clamp(8.5rem, 22vw, 13.5rem);
            --dm-stack-max: clamp(10.5rem, 28vw, 18rem);
        }

        .container-fluid {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            max-width: 100%;
        }

        .layout-page .content-wrapper>.container-xxl.container-p-y {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .layout-page .content-wrapper>.container-xxl.container-p-y>.container-fluid {
            padding-left: 0;
            padding-right: 0;
        }

        .evaluation-card {
            background-color: var(--bs-card-bg);
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
        }

        .evaluation-action-card {
            background-color: #5c297c;
            color: #ffffff;
        }

        .evaluation-action-card .card-title {
            color: #ffb736;
        }

        .evaluation-action-card .card-title i {
            color: #ffb736;
        }

        .evaluation-action-card p {
            color: #ffffff;
        }

        .evaluation-action-card .text-muted {
            color: #ffffff !important;
        }

        .evaluation-action-card .btn-evaluation-action {
            background-color: #ffb736;
            color: #5c297c;
            border: none;
            font-weight: 600;
        }

        .evaluation-action-card .btn-evaluation-action i {
            color: #5c297c;
        }

        .evaluation-action-card .btn-evaluation-action:hover,
        .evaluation-action-card .btn-evaluation-action:focus {
            background-color: #e6a431;
            color: #5c297c;
        }

        .evaluation-action-card .btn-evaluation-action.is-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-faculty-primary {
            background-color: #5c297c;
            border-color: #5c297c;
            color: #ffffff;
        }

        .btn-faculty-primary:hover,
        .btn-faculty-primary:focus {
            background-color: #4b2266;
            border-color: #4b2266;
            color: #ffffff;
        }

        .btn-delete-action {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
        }

        .btn-delete-action:hover,
        .btn-delete-action:focus {
            background-color: #b92c39;
            border-color: #b92c39;
            color: #ffffff;
        }

        .evaluation-card--table {
            border-radius: var(--bs-card-border-radius);
            padding: 0;
        }

        .modal-content.evaluation-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
            overflow: visible;
        }

        .searchable-dropdown {
            position: relative;
        }

        .searchable-dropdown-toggle {
            border: 1px solid #d9dee3;
            border-radius: 0.6rem;
            padding: 0.6rem 0.85rem;
            background-color: #ffffff;
            width: 100%;
            cursor: pointer;
            color: #0f172a;
            font-weight: 500;
            box-shadow: none;
            transition: border-color 0.2s ease;
        }

        .searchable-dropdown-toggle:hover,
        .searchable-dropdown-toggle:focus,
        .searchable-dropdown-toggle:active,
        .searchable-dropdown.show .searchable-dropdown-toggle {
            border-color: #94a3b8;
            background-color: #ffffff;
            box-shadow: none;
        }

        .searchable-dropdown-toggle:focus {
            outline: none;
        }

        .searchable-dropdown-toggle i {
            color: #94a3b8;
        }

        .searchable-dropdown-label {
            font-weight: 500;
            color: var(--bs-body-color, #4b5563);
            font-size: 0.95rem;
        }

        .searchable-dropdown-label.is-placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .searchable-dropdown .dropdown-menu {
            width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
            padding: 0.75rem 0.85rem;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0;
            background-clip: padding-box;
            top: calc(100% + 0.4rem) !important;
            left: 0 !important;
            right: 0 !important;
            transform: none !important;
            margin-top: 0 !important;
            z-index: 1085;
        }

        .searchable-dropdown .dropdown-menu::before {
            display: none;
        }

        .searchable-dropdown-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.65rem;
            max-height: calc((2 * 3.25rem) + 0.75rem);
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .searchable-dropdown-list.is-unlimited {
            max-height: calc((2 * 3.25rem) + 0.75rem);
            overflow-y: auto;
        }

        .searchable-dropdown .dropdown-item {
            border-radius: 0.45rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.15rem;
            font-weight: 500;
            color: #111827;
            width: 100%;
            padding: 0.6rem 0.75rem;
            background-color: #ffffff;
            border: 1px solid transparent;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            min-height: 3.25rem;
        }

        .searchable-dropdown .dropdown-item:hover,
        .searchable-dropdown .dropdown-item:focus {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        .searchable-dropdown .dropdown-item span,
        .searchable-dropdown .dropdown-item small {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .searchable-dropdown .dropdown-item small {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 400;
            width: 100%;
        }

        .searchable-dropdown-search {
            border-radius: 0.5rem;
            margin-bottom: 0.6rem;
        }

        .evaluation-card--table .card-body {
            padding: var(--bs-card-spacer-y, 1.5rem) var(--bs-card-spacer-x, 1.5rem);
        }

        .evaluation-card--table .card-body.pt-0 {
            padding-top: 0 !important;
        }

        .evaluation-card--table .card-body.border-0,
        .evaluation-card--table .card-body.pt-0,
        .evaluation-card--table .card-body.pb-0 {
            padding-left: var(--bs-card-spacer-x, 1.5rem) !important;
            padding-right: var(--bs-card-spacer-x, 1.5rem) !important;
        }

        .evaluation-controls label {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }

        .evaluation-table thead th {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            font-weight: 600;
            color: #94a3b8;
            border-bottom: 1px solid #eef2f6;
            padding: 0.85rem 0.75rem 0.7rem;
            background-color: #ffffff;
            vertical-align: middle;
            text-align: left;
            line-height: 1.2;
        }

        .evaluation-table thead th,
        .evaluation-table tbody td {
            white-space: nowrap;
        }

        .table-text-truncate {
            display: inline-block;
            vertical-align: middle;
            min-width: 0;
            max-width: var(--dm-cell-max);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-text-truncate.is-wide {
            max-width: var(--dm-stack-max);
        }

        .table-cell-stack {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
            max-width: var(--dm-stack-max);
            align-items: flex-start;
        }

        .table-cell-stack>* {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .table-cell-stack.is-wide {
            max-width: var(--dm-stack-max);
        }

        .evaluation-table thead th:first-child,
        .evaluation-table tbody td:first-child {
            width: 48px;
            white-space: normal;
        }

        .evaluation-table thead th.sortable {
            cursor: pointer;
            user-select: none;
        }

        .evaluation-sort-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .evaluation-table thead th.sortable.text-center .evaluation-sort-wrapper {
            justify-content: center;
        }

        .evaluation-table thead th.sortable:not(.text-center) .evaluation-sort-wrapper {
            justify-content: flex-start;
        }

        .evaluation-sort-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 12px;
            color: #94a3b8;
            font-size: 0.65rem;
        }

        .evaluation-sort-indicator i {
            display: none;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator {
            color: #1d4ed8;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator .icon-up,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator .icon-down {
            display: inline-flex;
        }

        .evaluation-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .evaluation-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .evaluation-table tbody tr.is-selected {
            background-color: #eef2ff;
            box-shadow: inset 0 0 0 1px #c7d2fe;
        }

        .evaluation-table tbody td {
            padding: 1rem 0.75rem;
            color: #1f2937;
            font-size: 0.85rem;
            vertical-align: middle;
            text-align: left;
        }

        .evaluation-table tbody td.evaluation-pill-cell,
        .evaluation-table tbody td.actions-cell {
            text-align: left;
        }

        .evaluation-pill,
        .evaluation-count-pill {
            display: inline-block;
            padding: 0.25rem 0.85rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: var(--pill-bg, #e2e8f0);
            color: var(--pill-color, #475569);
            max-width: var(--dm-pill-max);
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .evaluation-pill {
            text-align: center;
        }

        .evaluation-pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .evaluation-count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
        }

        .evaluation-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .evaluation-status--active {
            background-color: #dcfce7;
            color: #166534;
        }

        .evaluation-status--inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .evaluation-link-box {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.45rem 0.65rem;
            width: 220px;
        }

        .evaluation-link {
            max-width: 160px;
            font-size: 0.85rem;
            color: #334155;
            text-decoration: none;
        }

        .evaluation-icon-btn {
            border: none;
            background-color: #f8fafc;
            color: #475569;
            border-radius: 0.75rem;
            width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .evaluation-icon-btn:hover {
            background-color: #e2e8f0;
            color: #1f2937;
        }

        .evaluation-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: nowrap;
            margin-bottom: 2.5rem;
        }

        .evaluation-toolbar-title {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: #111827;
        }

        .evaluation-toolbar-title i {
            font-size: 1.8rem;
            color: #111827;
        }

        .evaluation-search-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            min-height: calc(2.25rem + 2px);
            padding: 0 0.75rem;
            max-width: 340px;
            width: 100%;
            transition: border-color 0.2s ease;
        }

        .evaluation-search-wrapper:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .evaluation-search-icon {
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .evaluation-search-input {
            border: none;
            outline: none;
            flex: 1;
            font-size: 0.95rem;
            background: transparent;
            height: 100%;
            padding-top: 0;
            padding-bottom: 0;
        }

        .evaluation-search-input::placeholder {
            color: #9ca3af;
        }

        .evaluation-search-clear {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1.2rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
            display: none;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 999px;
            align-items: center;
            justify-content: center;
        }

        .evaluation-search-clear.is-visible {
            display: inline-flex;
        }

        .evaluation-search-clear:hover {
            color: #1f2937;
            background-color: rgba(148, 163, 184, 0.15);
        }

        .evaluation-bulk-bar {
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: 24px;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            background-color: #ffffff;
            color: #1f2937;
            padding: 0.85rem 1.5rem;
            border-radius: 999px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            border: 1px solid #e2e8f0;
            z-index: 1040;
        }

        .evaluation-bulk-bar .evaluation-bulk-btn {
            color: #1f2937;
        }

        .evaluation-bulk-bar .evaluation-bulk-btn--danger {
            color: #dc2626 !important;
        }

        .evaluation-bulk-bar #evaluationSelectedCount {
            color: #1f2937;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.55rem 1.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: none;
            box-shadow: none;
        }

        .btn:hover,
        .btn:focus,
        .btn:active {
            transform: none !important;
            box-shadow: none !important;
        }

        .btn.btn-sm {
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            border-radius: 0.4rem;
        }

        .btn.btn-tertiary {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0.55rem 1.2rem;
        }

        .btn.btn-tertiary:hover,
        .btn.btn-tertiary:focus,
        .btn.btn-tertiary:active {
            background: transparent;
            color: #64748b;
            box-shadow: none;
        }

        div[data-table-id="evaluationTable"] [data-table-info] {
            color: #6b7280;
            font-size: 0.875rem;
        }

        div[data-table-id="evaluationTable"] .pagination .page-link {
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 0.9rem;
            margin: 0 0.1rem;
            color: #64748b;
            background-color: #e2e8f0;
            font-weight: 600;
            transition: background-color 0.15s ease;
            box-shadow: none;
        }

        div[data-table-id="evaluationTable"] .pagination .page-link:hover:not(.disabled) {
            background-color: #cbd5e1;
            color: #475569;
        }

        div[data-table-id="evaluationTable"] .pagination .page-item.active .page-link {
            background-color: #5c297c;
            color: #ffffff;
        }

        div[data-table-id="evaluationTable"] .pagination .page-item.active .page-link:hover {
            background-color: #4b2266;
            color: #ffffff;
        }

        div[data-table-id="evaluationTable"] .pagination .page-link span {
            color: inherit;
        }

        div[data-table-id="evaluationTable"] .pagination .page-item.disabled .page-link {
            background-color: #e2e8f0;
            color: #94a3b8;
        }

        .evaluation-bulk-bar.d-none {
            display: none !important;
        }

        .evaluation-bulk-btn {
            border: none;
            background: transparent;
            color: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
        }

        .evaluation-bulk-btn--danger {
            background-color: rgba(220, 38, 38, 0.08);
            color: #dc2626 !important;
            border: 1px solid rgba(220, 38, 38, 0.25);
        }

        .evaluation-bulk-btn--danger i {
            color: #dc2626 !important;
        }

        .evaluation-bulk-close {
            border: none;
            background: transparent;
            color: #475569;
            font-size: 1.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            cursor: pointer;
        }

        .evaluation-checkbox {
            width: 1.05rem;
            height: 1.05rem;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
        }

        .evaluation-checkbox:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .evaluation-modal-header {
            background-color: #f5f7fb;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .evaluation-modal-body {
            padding: 1.5rem;
        }

        .evaluation-modal-close {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            font-size: 1.35rem;
            line-height: 1;
            padding: 0;
        }

        .evaluation-modal-close:focus {
            outline: none;
            box-shadow: none;
        }

        .evaluation-modal-footer {
            border-top: none;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1rem 1.5rem;
        }

        .evaluation-modal-footer .btn {
            min-width: 120px;
        }

        .evaluation-modal-body .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: normal;
        }

        .evaluation-modal-body .form-select,
        .evaluation-modal-body .form-control {
            border-radius: 0.6rem;
            padding: 0.6rem 0.85rem;
        }

        .evaluation-modal-dialog--narrow {
            max-width: 42%;
        }

        @media (max-width: 992px) {
            .evaluation-modal-dialog--narrow {
                max-width: 60%;
            }
        }

        .evaluation-modal-trigger,
        .evaluation-modal-btn {
            transition: none;
        }

        .evaluation-modal-trigger:hover,
        .evaluation-modal-trigger:focus,
        .evaluation-modal-trigger:active,
        .evaluation-modal-btn:hover,
        .evaluation-modal-btn:focus,
        .evaluation-modal-btn:active {
            transform: none;
            filter: none;
            box-shadow: none;
        }

        @media (max-width: 992px) {
            .evaluation-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .evaluation-card--table {
                padding: 1.25rem;
            }

            .evaluation-table tbody td {
                padding: 0.85rem 0.6rem;
            }
        }

        @media (max-width: 768px) {
            .evaluation-bulk-bar {
                bottom: 90px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .evaluation-table tbody td {
                padding: 0.75rem;
            }
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem 0;
            color: #64748b;
        }

        .empty-state h5 {
            font-weight: 600;
            color: #111827;
        }

        .empty-state p {
            color: #6b7280;
        }

        .table-filter-dropdown .filter-toggle {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
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

        .table-filter-dropdown .filter-toggle:focus-visible,
        .table-filter-dropdown .filter-toggle:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .table-filter-dropdown .filter-toggle.is-active {
            border-color: #5c297c;
            color: #5c297c;
        }

        .table-filter-dropdown .dropdown-menu {
            min-width: 280px;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.1);
        }

        .table-filter-dropdown label {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #94a3b8;
        }

        .table-filter-dropdown .form-select {
            border-radius: 0.6rem;
        }

        .table-filter-reset {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5c297c;
        }
    </style>
@endsection

@section('content')
    @php
        $flashSuccess = session('success');
        $flashError = session('error');
        $pageToasts = [];
        if ($flashSuccess) {
            $pageToasts[] = ['type' => 'success', 'message' => $flashSuccess];
        }
        if ($flashError) {
            $pageToasts[] = ['type' => 'danger', 'message' => $flashError];
        }
    @endphp
    @include('components.dm-toast', ['messages' => $pageToasts])

    <div class="container-fluid">

        <div class="card evaluation-card evaluation-card--table mb-4 evaluation-action-card">
            <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <div class="text-center text-lg-start">
                    <h5 class="card-title mb-1 d-flex align-items-center gap-2 justify-content-center justify-content-lg-start"
                        style="font-size: 1.2rem;">
                        <i class="fa-solid fa-file-invoice" style="font-size: 1.5rem;"></i>
                        Evaluation Actions
                    </h5>
                    <p class="text-muted mb-0">Use the actions above to generate evaluation forms individually or for every
                        faculty member.</p>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                    <button type="button" class="btn btn-evaluation-action {{ $canAdd ? '' : 'is-disabled' }}"
                        @if (!$canAdd) disabled aria-disabled="true" @endif data-mdb-ripple-init
                        data-bs-toggle="modal" data-bs-target="#generateAllModal">
                        <i class="fa-solid fa-file-circle-plus me-2"></i>
                        Generate All
                    </button>
                    <button type="button" class="btn btn-evaluation-action {{ $canAdd ? '' : 'is-disabled' }}"
                        @if (!$canAdd) disabled aria-disabled="true" @endif data-bs-toggle="modal"
                        data-bs-target="#generateEvaluationModal" data-mdb-ripple-init>
                        <i class="fa-solid fa-file-pen me-2"></i>
                        Create Form
                    </button>
                </div>
            </div>
        </div>

        <div class="card evaluation-card evaluation-card--table" data-table-controller data-table-id="evaluationTable">
            <div class="card-body border-0 evaluation-controls">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="evaluationRowsPerPage" class="text-muted small">Lines per page</label>
                            <select id="evaluationRowsPerPage" class="form-select evaluation-page-size fw-bold"
                                style="width: auto;" data-table-length>
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
                                <button class="filter-toggle" type="button" id="evaluationFilterToggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span>Filters</span>
                                    <i class="bx bx-filter"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3">
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Faculty Name</label>
                                        <select id="evaluationFilterFaculty" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Department</label>
                                        <select id="evaluationFilterDepartment" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Academic Year</label>
                                        <select id="evaluationFilterYear" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Semester</label>
                                        <select id="evaluationFilterSemester" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Status</label>
                                        <select id="evaluationFilterStatus" class="form-select">
                                            <option value="all">All</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-link p-0 table-filter-reset"
                                            id="evaluationFilterReset">Reset Filters</button>
                                    </div>
                                </div>
                            </div>
                            <div class="evaluation-search-wrapper">
                                <i class="bx bx-search evaluation-search-icon"></i>
                                <input type="text" id="evaluationSearch" class="evaluation-search-input"
                                    data-table-search placeholder="Search...">
                                <button type="button" class="evaluation-search-clear"
                                    id="evaluationSearchClear">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 evaluation-table" id="evaluationTable">
                        <thead>
                            <tr>
                                @if ($canDelete || $showDeleteDisabled)
                                    <th class="evaluation-col-selection text-center">
                                        <input type="checkbox" class="form-check-input evaluation-checkbox"
                                            id="evaluationSelectAll" data-select-all
                                            @if (!$canDelete) disabled aria-disabled="true" @endif>
                                    </th>
                                @endif
                                <th data-sort-key="faculty" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Faculty Name</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="department" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Department</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="year" data-sort-type="string" class="sortable"
                                    data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Academic Year</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="semester" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Semester</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>Form Link</th>
                                <th data-sort-key="status" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Status</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="responses" data-sort-type="number" class="sortable"
                                    data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Responses</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach ($sortedEvaluations as $evaluation)
                                @php
                                    $status = $evaluation->is_active ? 'active' : 'inactive';
                                    $statusTerm = $evaluation->is_active ? 'active' : 'inactive';
                                    $facultyRawName = $evaluation->resolved_faculty_name;
                                    $facultyDisplayName = trim($facultyRawName) === '' ? 'Unknown' : $facultyRawName;
                                    $facultyEmail = $evaluation->resolved_faculty_email;
                                    $facultyTitle = trim(
                                        $facultyDisplayName . ($facultyEmail ? ' • ' . $facultyEmail : ''),
                                    );
                                    $facultyDepartmentRaw = $evaluation->resolved_faculty_department ?? '';
                                    $facultyDepartments = collect(explode(',', $facultyDepartmentRaw))
                                        ->map(function ($value) {
                                            return trim($value);
                                        })
                                        ->filter(function ($value) {
                                            return $value !== '';
                                        })
                                        ->values();
                                    $facultyDepartmentLabel = $facultyDepartments->isEmpty()
                                        ? 'N/A'
                                        : $facultyDepartments->implode(', ');
                                    $facultyDepartmentSort = $facultyDepartments->isEmpty()
                                        ? 'n/a'
                                        : strtolower($facultyDepartments->implode(','));
                                    $searchTerms = strtolower(
                                        $facultyRawName .
                                            ' ' .
                                            $facultyEmail .
                                            ' ' .
                                            $facultyDepartmentLabel .
                                            ' ' .
                                            $evaluation->academic_year .
                                            ' ' .
                                            $evaluation->semester .
                                            ' ' .
                                            $statusTerm .
                                            ' ' .
                                            $evaluation->form_link,
                                    );
                                @endphp
                                <tr class="table-row" data-evaluation-id="{{ $evaluation->id }}"
                                    data-sort-faculty="{{ strtolower($facultyRawName) }}"
                                    data-sort-department="{{ $facultyDepartmentSort }}"
                                    data-sort-year="{{ $evaluation->academic_year }}"
                                    data-sort-semester="{{ strtolower($evaluation->semester) }}"
                                    data-sort-status="{{ $status }}"
                                    data-sort-responses="{{ $evaluation->responses->count() }}"
                                    data-label-faculty="{{ $facultyDisplayName }}"
                                    data-label-department="{{ $facultyDepartmentLabel }}"
                                    data-label-semester="{{ $evaluation->semester }}"
                                    data-label-status="{{ $evaluation->is_active ? 'Active' : 'Inactive' }}"
                                    data-search="{{ $searchTerms }}">
                                    @if ($canDelete || $showDeleteDisabled)
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input evaluation-checkbox"
                                                data-row-select value="{{ $evaluation->id }}"
                                                @if (!$canDelete) disabled aria-disabled="true" @endif>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="table-cell-stack is-wide" title="{{ $facultyTitle }}">
                                            <span class="faculty-name">{{ $facultyDisplayName }}</span>
                                            @if ($facultyEmail)
                                                <small class="text-muted">{{ $facultyEmail }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="evaluation-pill-cell">
                                        <div class="evaluation-pill-group">
                                            @forelse ($facultyDepartments as $department)
                                                <span class="evaluation-pill" data-pill-palette="green"
                                                    data-pill-value="{{ strtolower($department) }}">{{ $department }}</span>
                                            @empty
                                                <span class="evaluation-pill" data-pill-palette="green"
                                                    data-pill-value="n/a">N/A</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="evaluation-pill-cell">
                                        <span class="evaluation-pill" data-pill-palette="purple"
                                            data-pill-value="{{ strtolower($evaluation->academic_year) }}">{{ $evaluation->academic_year }}</span>
                                    </td>
                                    <td class="evaluation-pill-cell">
                                        <span class="evaluation-pill" data-pill-palette="blue"
                                            data-pill-value="{{ strtolower($evaluation->semester) }}">{{ $evaluation->semester }}</span>
                                    </td>
                                    <td>
                                        <div class="evaluation-link-box">
                                            <span
                                                class="evaluation-link text-truncate">{{ $evaluation->form_link }}</span>
                                            <button type="button" class="evaluation-icon-btn"
                                                onclick="copyToClipboard('{{ $evaluation->form_link }}')"
                                                title="Copy link">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="evaluation-status evaluation-status--{{ $status }}">
                                            {{ $evaluation->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="evaluation-count-pill" data-pill-palette="gray"
                                            data-pill-value="responses">{{ $evaluation->responses->count() }}</span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="dropdown evaluation-actions">
                                            <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if ($canViewReports || $canManageEvaluations || $canManageEvaluationQr)
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('dm.evaluation.responses', $evaluation) }}?from=dm">
                                                            View Responses
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button type="button" class="dropdown-item" data-evaluation-preview
                                                        data-evaluation-id="{{ $evaluation->id }}"
                                                        data-evaluation-faculty="{{ e($facultyDisplayName) }}"
                                                        data-evaluation-year="{{ $evaluation->academic_year }}"
                                                        data-evaluation-semester="{{ $evaluation->semester }}">
                                                        Preview QR
                                                    </button>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('dm.evaluation.qr.download', $evaluation) }}">
                                                        Download QR
                                                    </a>
                                                </li>
                                                @if ($canEdit)
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                            data-evaluation-toggle
                                                            data-toggle-url="{{ route('dm.evaluation.toggle', $evaluation) }}"
                                                            data-evaluation-id="{{ $evaluation->id }}"
                                                            data-current-status="{{ $status }}"
                                                            data-default-text="{{ $evaluation->is_active ? 'Deactivate' : 'Activate' }}">
                                                            {{ $evaluation->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </li>
                                                @elseif ($canEvaluationQrOnly)
                                                    <li>
                                                        <span class="dropdown-item disabled" aria-disabled="true">
                                                            {{ $evaluation->is_active ? 'Deactivate' : 'Activate' }}
                                                        </span>
                                                    </li>
                                                @endif
                                                @if (($canEdit && $canDelete) || $canEvaluationQrOnly || $showDeleteDisabled)
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                @endif
                                                @if ($canDelete)
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger"
                                                            data-evaluation-delete
                                                            data-evaluation-name="{{ e($facultyDisplayName) }}"
                                                            data-delete-url="{{ route('dm.evaluation.destroy', $evaluation) }}"
                                                            data-evaluation-id="{{ $evaluation->id }}">
                                                            Delete
                                                        </button>
                                                    </li>
                                                @elseif ($canEvaluationQrOnly || $showDeleteDisabled)
                                                    <li>
                                                        <button type="button" class="dropdown-item disabled" disabled
                                                            aria-disabled="true">
                                                            Delete
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr data-empty style="{{ $sortedEvaluations->isEmpty() ? '' : 'display: none;' }}">
                                <td colspan="{{ $canDelete || $showDeleteDisabled ? 9 : 8 }}" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-file-invoice display-4 text-muted mb-3"></i>
                                        <h5 class="mb-2">No evaluation forms found</h5>
                                        <p class="text-muted mb-0">Generate your first evaluation form using the controls
                                            above.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr data-empty-search style="display: none;">
                                <td colspan="{{ $canDelete || $showDeleteDisabled ? 9 : 8 }}" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-magnifying-glass display-4 text-muted mb-3"></i>
                                        <h5 class="mb-2">No results found</h5>
                                        <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($canDelete || $showDeleteDisabled)
                    <div class="evaluation-bulk-bar d-none" id="evaluationBulkBar">
                        <span class="fw-semibold" id="evaluationSelectedCount">0 Selected</span>
                        @if ($canDelete)
                            <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger"
                                data-bulk-action="delete">
                                <i class="bx bx-trash"></i> Delete
                            </button>
                        @else
                            <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger disabled"
                                disabled aria-disabled="true">
                                <i class="bx bx-trash"></i> Delete
                            </button>
                        @endif
                        <button type="button" class="evaluation-bulk-close" data-bulk-action="clear"
                            title="Clear selection">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                @endif

                <div class="row mt-4 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="text-muted" data-table-info></div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <nav aria-label="Table pagination">
                            <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Generate All Evaluation Forms Modal --}}
    <div class="modal fade" id="generateAllModal" tabindex="-1" aria-labelledby="generateAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form method="POST" action="{{ route('dm.evaluation.generateAll') }}" id="generateAllFormModal">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="generateAllModalLabel">Generate All Evaluation Forms</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            @php
                                $generateAllYearPlaceholder = '-- Select Academic Year --';
                            @endphp
                            <label for="generateAllAcademicYear" class="form-label">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static"
                                    data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="{{ $generateAllYearPlaceholder }}">{{ $generateAllYearPlaceholder }}</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @forelse($availableAcademicYears as $year)
                                            @php $yearValue = trim($year); @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $yearValue }}"
                                                data-option-label="{{ $yearValue }}"
                                                data-option-filter="{{ strtolower($yearValue) }}">
                                                <span>{{ $yearValue }}</span>
                                            </button>
                                        @empty
                                            <div class="text-muted small px-2 py-1">No academic years found.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <input type="hidden" name="academic_year" id="generateAllAcademicYear" required>
                            </div>
                        </div>
                        <div class="mb-0">
                            @php
                                $generateAllSemesterPlaceholder = '-- Select Semester --';
                            @endphp
                            <label class="form-label" for="generateAllSemester">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static"
                                    data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="{{ $generateAllSemesterPlaceholder }}">{{ $generateAllSemesterPlaceholder }}</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @forelse($availableSemesters as $option)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $option['value'] }}"
                                                data-option-label="{{ $option['label'] }}"
                                                data-option-filter="{{ strtolower($option['label']) }}">
                                                <span>{{ $option['label'] }}</span>
                                            </button>
                                        @empty
                                            <div class="text-muted small px-2 py-1">No semesters found.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <input type="hidden" name="semester" id="generateAllSemester" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-faculty-primary evaluation-modal-btn"
                            data-default-text="Generate Forms" data-loading-text="Generating...">Generate Forms</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Generate Evaluation Form Modal --}}
    <div class="modal fade" id="generateEvaluationModal" tabindex="-1" aria-labelledby="generateEvaluationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form method="POST" action="{{ route('dm.evaluation.store') }}" id="generateEvaluationForm">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="generateEvaluationModalLabel">Create Evaluation Form</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="modalFacultyId">Faculty Member</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static"
                                    data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Faculty --">-- Select Faculty --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($faculties as $faculty)
                                            @php
                                                $rawFacultyName = $faculty->name ?? '';
                                                $facultyName =
                                                    trim($rawFacultyName) === '' ? 'Unknown' : $rawFacultyName;
                                                $facultyEmail = $faculty->email ?? '';
                                                $keywords = strtolower(
                                                    trim(($faculty->name ?? 'Unknown') . ' ' . $facultyEmail),
                                                );
                                            @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $faculty->id }}"
                                                data-option-label="{{ $facultyName }}"
                                                data-option-filter="{{ $keywords }}">
                                                <span>{{ $facultyName }}</span>
                                                @if ($facultyEmail)
                                                    <small>{{ $facultyEmail }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="faculty_id" id="modalFacultyId" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            @php
                                $modalYearPlaceholder = '-- Select Academic Year --';
                            @endphp
                            <label for="modalAcademicYear" class="form-label">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static"
                                    data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="{{ $modalYearPlaceholder }}">{{ $modalYearPlaceholder }}</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @forelse($availableAcademicYears as $year)
                                            @php $yearValue = trim($year); @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $yearValue }}"
                                                data-option-label="{{ $yearValue }}"
                                                data-option-filter="{{ strtolower($yearValue) }}">
                                                <span>{{ $yearValue }}</span>
                                            </button>
                                        @empty
                                            <div class="text-muted small px-2 py-1">No academic years found.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <input type="hidden" name="academic_year" id="modalAcademicYear" required>
                            </div>
                        </div>
                        <div class="mb-0">
                            @php
                                $semesterPlaceholder = '-- Select Semester --';
                            @endphp
                            <label class="form-label" for="modalSemester">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" data-bs-toggle="dropdown" data-bs-display="static"
                                    data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="{{ $semesterPlaceholder }}">{{ $semesterPlaceholder }}</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @forelse($availableSemesters as $option)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $option['value'] }}"
                                                data-option-label="{{ $option['label'] }}"
                                                data-option-filter="{{ strtolower($option['label']) }}">
                                                <span>{{ $option['label'] }}</span>
                                            </button>
                                        @empty
                                            <div class="text-muted small px-2 py-1">No semesters found.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <input type="hidden" name="semester" id="modalSemester" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-faculty-primary evaluation-modal-btn"
                            data-default-text="Generate" data-loading-text="Generating...">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Single Delete Confirmation Modal --}}
    <div class="modal fade" id="evaluationDeleteModal" tabindex="-1" aria-labelledby="evaluationDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="evaluationDeleteModalLabel">Delete Evaluation Form</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                        aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold"
                            id="evaluationDeleteName">this evaluation form</span>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn"
                        id="confirmEvaluationDeleteBtn" data-default-text="Delete"
                        data-loading-text="Deleting...">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Confirmation Modal --}}
    <div class="modal fade" id="evaluationBulkDeleteModal" tabindex="-1"
        aria-labelledby="evaluationBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="evaluationBulkDeleteModalLabel">Delete Selected Forms</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                        aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold"
                            id="evaluationBulkDeleteCount">0</span> evaluation form(s). This action cannot be undone.
                        Continue?</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn"
                        id="confirmEvaluationBulkDeleteBtn" data-default-text="Delete Selected"
                        data-loading-text="Deleting...">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Modal --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="qrModalLabel">Evaluation QR Code</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                        aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body text-center">
                    <div class="mb-3">
                        <h6 id="qrFacultyName" class="text-primary mb-1"></h6>
                        <small id="qrDetails" class="text-muted"></small>
                    </div>
                    <div class="qr-container mb-3">
                        <div id="qrCodeContainer"></div>
                        <div id="qrLoader" class="d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading QR Code...</span>
                            </div>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">
                        Students can scan this QR code to access the evaluation form
                    </p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                        data-bs-dismiss="modal">Close</button>
                    <a id="downloadQrBtn" href="" class="btn btn-faculty-primary evaluation-modal-btn">Download QR
                        Code</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('components.table-controller-script')
    <script>
        const PILL_PALETTES = {
            purple: [{
                bg: '#e4c7ff',
                color: '#4c1d95'
            }],
            blue: [{
                bg: '#d3e2ff',
                color: '#1d4ed8'
            }],
            green: [{
                bg: '#d1f9e0',
                color: '#047857'
            }],
            gray: [{
                bg: '#e3e8f1',
                color: '#475569'
            }],
        };

        function getPillColor(value, paletteName) {
            const palette = PILL_PALETTES[paletteName];
            if (!palette || !palette.length) {
                return null;
            }
            return palette[0];
        }

        function stylePillElement(element) {
            const paletteName = element.dataset.pillPalette;
            if (!paletteName) {
                return;
            }
            const color = getPillColor(element.dataset.pillValue || element.textContent, paletteName);
            if (!color) {
                return;
            }
            element.style.setProperty('--pill-bg', color.bg);
            element.style.setProperty('--pill-color', color.color);
        }

        function applyEvaluationPillColors(root = document) {
            const scope = root instanceof Element ? root : document.body;
            scope.querySelectorAll('[data-pill-palette]').forEach(stylePillElement);
        }


        const evaluationPermissions = {
            canAdd: @json($canAdd),
            canEdit: @json($canEdit),
            canDelete: @json($canDelete),
            canImport: @json($canImport),
        };

        const evaluationSelection = new Set();
        const evaluationSortState = {
            key: null,
            direction: 'asc'
        };
        const evaluationFilters = {
            faculty: 'all',
            department: 'all',
            year: 'all',
            semester: 'all',
            status: 'all',
        };
        const evaluationAllowedDepartmentOptions = @json($allowedDepartmentFilterOptions);
        let evaluationDeleteModalInstance;
        let evaluationBulkDeleteModalInstance;
        let evaluationTableController = null;
        let pendingEvaluationDeleteContext = null;
        let pendingEvaluationBulk = null;
        let evaluationFilterControls = null;
        let evaluationFilterToggleEl = null;
        let currentQrObjectUrl = null;

        function deriveLoadingText(text) {
            if (!text) {
                return 'Processing...';
            }

            const baseWord = text.trim().split(/\s+/)[0]?.toLowerCase() || '';
            if (!baseWord) {
                return 'Processing...';
            }

            let progressive = baseWord;
            if (baseWord.endsWith('ie')) {
                progressive = `${baseWord.slice(0, -2)}ying`;
            } else if (baseWord.endsWith('e') && !baseWord.endsWith('ee')) {
                progressive = `${baseWord.slice(0, -1)}ing`;
            } else if (/[bcdfghjklmnpqrstvwxyz][aeiou][bcdfghjklmnpqrstvwxyz]$/.test(baseWord) && baseWord.length === 3) {
                progressive = `${baseWord}${baseWord.slice(-1)}ing`;
            } else {
                progressive = `${baseWord}ing`;
            }

            return `${progressive}...`;
        }

        function toggleButtonLoading(button, isLoading, defaultText = null, loadingText = null) {
            if (!button) return;

            const idleText = defaultText ?? button.dataset.defaultText ?? button.textContent.trim();
            const busyText = loadingText ?? button.dataset.loadingText ?? deriveLoadingText(idleText);

            if (isLoading) {
                button.dataset.defaultText = idleText;
                button.dataset.loadingText = busyText;
                button.disabled = true;
                button.textContent = busyText;
            } else {
                const original = button.dataset.defaultText || idleText;
                button.disabled = false;
                button.textContent = original;
            }
        }

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const controllerRoot = document.querySelector('[data-table-id="evaluationTable"]');
            if (controllerRoot && window.TableController) {
                window.tableControllers = window.tableControllers || {};
                const evaluationController = new TableController(controllerRoot);
                window.tableControllers.evaluationTable = evaluationController;
                evaluationTableController = evaluationController;
                initEvaluationTableEnhancements(controllerRoot, evaluationController);
                initEvaluationFilters(evaluationController);
            }
            applyEvaluationPillColors();

            initEvaluationSearch();
            attachEvaluationDeleteHandlers();
            attachEvaluationActionHandlers();
            initEvaluationFormLoading();
            initEvaluationSelectDropdowns();
            updateEvaluationEmptyState();

            const deleteModalEl = document.getElementById('evaluationDeleteModal');
            if (deleteModalEl) {
                evaluationDeleteModalInstance = new bootstrap.Modal(deleteModalEl);
                deleteModalEl.addEventListener('hidden.bs.modal', () => {
                    pendingEvaluationDeleteContext = null;
                });
            }

            const bulkModalEl = document.getElementById('evaluationBulkDeleteModal');
            if (bulkModalEl) {
                evaluationBulkDeleteModalInstance = new bootstrap.Modal(bulkModalEl);
                bulkModalEl.addEventListener('hidden.bs.modal', () => {
                    pendingEvaluationBulk = null;
                });
            }

            const qrModalEl = document.getElementById('qrModal');
            if (qrModalEl) {
                qrModalEl.addEventListener('hidden.bs.modal', () => {
                    if (currentQrObjectUrl) {
                        URL.revokeObjectURL(currentQrObjectUrl);
                        currentQrObjectUrl = null;
                    }
                    const qrContainer = document.getElementById('qrCodeContainer');
                    if (qrContainer) {
                        qrContainer.innerHTML = '';
                    }
                });
            }

            const confirmDeleteBtn = document.getElementById('confirmEvaluationDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', () => {
                    if (!pendingEvaluationDeleteContext) {
                        return;
                    }

                    toggleButtonLoading(
                        confirmDeleteBtn,
                        true,
                        confirmDeleteBtn.dataset.defaultText || 'Delete',
                        confirmDeleteBtn.dataset.loadingText || 'Deleting...'
                    );

                    evaluationDeleteModalInstance?.hide();
                    handleEvaluationDelete(pendingEvaluationDeleteContext, confirmDeleteBtn)
                        .finally(() => {
                            pendingEvaluationDeleteContext = null;
                        });
                });
            }

            const confirmBulkBtn = document.getElementById('confirmEvaluationBulkDeleteBtn');
            if (confirmBulkBtn) {
                confirmBulkBtn.addEventListener('click', () => {
                    if (pendingEvaluationBulk) {
                        const context = pendingEvaluationBulk;
                        pendingEvaluationBulk = null;
                        handleEvaluationBulkDelete(context.ids, context.helpers, {
                            loadingButton: confirmBulkBtn
                        });
                    }
                });
            }
        });

        function initEvaluationTableEnhancements(controllerRoot, controller) {
            initEvaluationSelection(controllerRoot, controller);
            initEvaluationSorting(controller);
        }

        function initEvaluationSelection(controllerRoot, controller) {
            const bulkBar = document.getElementById('evaluationBulkBar');
            const selectedCountEl = document.getElementById('evaluationSelectedCount');
            const selectAllEl = document.getElementById('evaluationSelectAll');
            const tableBody = document.querySelector('#evaluationTable tbody');
            let lastScopeKey = null;
            const getSelectionScopeKey = () => {
                const searchTerm = controller?.searchTerm ?? '';
                const filterKey = JSON.stringify(evaluationFilters);
                return `${searchTerm}|${filterKey}`;
            };
            const getSelectableRows = () => {
                if (controller && Array.isArray(controller.filteredRows)) {
                    return controller.filteredRows;
                }
                return Array.from(document.querySelectorAll(
                    '#evaluationTable tbody tr[data-evaluation-id]:not([data-ignore])'));
            };
            lastScopeKey = getSelectionScopeKey();

            if (tableBody) {
                tableBody.addEventListener('change', (event) => {
                    const checkbox = event.target.closest('[data-row-select]');
                    if (!checkbox) return;

                    const row = checkbox.closest('tr[data-evaluation-id]');
                    if (!row) return;

                    const id = row.dataset.evaluationId;
                    if (checkbox.checked) {
                        evaluationSelection.add(id);
                    } else {
                        evaluationSelection.delete(id);
                    }

                    row.classList.toggle('is-selected', checkbox.checked);
                    syncEvaluationSelectAll(selectAllEl, controller);
                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                });
            }

            if (selectAllEl) {
                selectAllEl.addEventListener('change', (event) => {
                    const shouldSelect = event.target.checked;
                    getSelectableRows().forEach((row) => {
                        const checkbox = row.querySelector('[data-row-select]');
                        if (!checkbox) return;

                        checkbox.checked = shouldSelect;
                        row.classList.toggle('is-selected', shouldSelect);

                        const id = row.dataset.evaluationId;
                        if (shouldSelect) {
                            evaluationSelection.add(id);
                        } else {
                            evaluationSelection.delete(id);
                        }
                    });

                    syncEvaluationSelectAll(selectAllEl, controller);
                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                });
            }

            controllerRoot.addEventListener('table:updated', () => {
                const scopeKey = getSelectionScopeKey();
                if (selectAllEl?.checked && scopeKey !== lastScopeKey) {
                    lastScopeKey = scopeKey;
                    clearEvaluationSelection({
                        bulkBar,
                        selectedCountEl,
                        selectAllEl,
                        controller
                    });
                    return;
                }
                lastScopeKey = scopeKey;
                document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]:not([data-ignore])')
                    .forEach((row) => {
                        const id = row.dataset.evaluationId;
                        const checkbox = row.querySelector('[data-row-select]');
                        const isSelected = evaluationSelection.has(id);

                        if (checkbox) checkbox.checked = isSelected;
                        row.classList.toggle('is-selected', isSelected);
                    });

                syncEvaluationSelectAll(selectAllEl, controller);
                updateEvaluationBulkBar(bulkBar, selectedCountEl);
                attachEvaluationDeleteHandlers();
                attachEvaluationActionHandlers();
                applyEvaluationPillColors(controllerRoot);
            });

            if (bulkBar) {
                bulkBar.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-bulk-action]');
                    if (!button) return;

                    handleEvaluationBulkAction(button.dataset.bulkAction, {
                        bulkBar,
                        selectedCountEl,
                        selectAllEl,
                        controller
                    });
                });
            }
        }

        function initEvaluationSorting(controller) {
            const headers = document.querySelectorAll('#evaluationTable thead th[data-sort-key]');
            headers.forEach((header) => {
                header.dataset.sortState = 'none';
                header.addEventListener('click', () => handleEvaluationSort(header, headers, controller));
            });
            updateEvaluationSortIndicators(headers);
        }

        function handleEvaluationSort(activeHeader, headers, controller) {
            const sortKey = activeHeader.dataset.sortKey;
            const sortType = activeHeader.dataset.sortType || 'string';
            const direction = evaluationSortState.direction === 'asc' ? 1 : -1;

            if (evaluationSortState.key === sortKey) {
                evaluationSortState.direction = evaluationSortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                evaluationSortState.key = sortKey;
                evaluationSortState.direction = 'asc';
            }

            const rows = Array.from(document.querySelectorAll(
                '#evaluationTable tbody tr[data-evaluation-id]:not([data-ignore])'));
            rows.sort((rowA, rowB) => {
                const valueA = rowA.dataset[`sort${sortKey.charAt(0).toUpperCase()}${sortKey.slice(1)}`];
                const valueB = rowB.dataset[`sort${sortKey.charAt(0).toUpperCase()}${sortKey.slice(1)}`];

                if (sortType === 'number') {
                    return (Number(valueA) - Number(valueB)) * (evaluationSortState.direction === 'asc' ? 1 : -1);
                }

                const compare = String(valueA).localeCompare(String(valueB));
                return compare * (evaluationSortState.direction === 'asc' ? 1 : -1);
            });

            const tbody = document.querySelector('#evaluationTable tbody');
            rows.forEach((row) => tbody.appendChild(row));

            updateEvaluationSortIndicators(headers);
            controller.refresh();
        }

        function updateEvaluationSortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';
            });

            if (!evaluationSortState.key) return;

            headers.forEach((header) => {
                if (header.dataset.sortKey === evaluationSortState.key) {
                    header.classList.add(evaluationSortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
                    header.dataset.sortState = evaluationSortState.direction;
                }
            });
        }

        function syncEvaluationSelectAll(selectAllEl, controller) {
            if (!selectAllEl) return;

            const rows = controller && Array.isArray(controller.filteredRows) ?
                controller.filteredRows :
                Array.from(document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]:not([data-ignore])'));
            if (rows.length === 0) {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = false;
                return;
            }

            const selectedVisible = rows.filter((row) => evaluationSelection.has(row.dataset.evaluationId)).length;

            if (selectedVisible === 0) {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = false;
            } else if (selectedVisible === rows.length) {
                selectAllEl.checked = true;
                selectAllEl.indeterminate = false;
            } else {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = true;
            }
        }

        function updateEvaluationBulkBar(bulkBar, selectedCountEl) {
            if (!bulkBar || !selectedCountEl) return;

            const count = evaluationSelection.size;
            selectedCountEl.textContent = `${count} Selected`;
            bulkBar.classList.toggle('d-none', count === 0);
        }

        function updateEvaluationEmptyState() {
            const body = document.querySelector('#evaluationTable tbody');
            if (!body) {
                return;
            }
            const emptyRow = body.querySelector('tr[data-empty]');
            if (!emptyRow) {
                return;
            }
            const hasRows = body.querySelector('tr[data-evaluation-id]') !== null;
            emptyRow.style.display = hasRows ? 'none' : '';
        }

        function removeEvaluationRow(evaluationId, options = {}) {
            const {
                skipRefresh = false
            } = options;
            if (!evaluationId) {
                return false;
            }

            const row = document.querySelector(`#evaluationTable tbody tr[data-evaluation-id="${evaluationId}"]`);
            if (!row) {
                return false;
            }

            row.remove();
            evaluationSelection.delete(String(evaluationId));

            const selectAllEl = document.getElementById('evaluationSelectAll');
            const bulkBar = document.getElementById('evaluationBulkBar');
            const selectedCountEl = document.getElementById('evaluationSelectedCount');

            syncEvaluationSelectAll(selectAllEl, evaluationTableController);
            updateEvaluationBulkBar(bulkBar, selectedCountEl);
            if (!skipRefresh) {
                updateEvaluationEmptyState();
                refreshEvaluationFilterOptions();
                applyEvaluationFilters(evaluationTableController, evaluationFilterToggleEl);
                evaluationTableController?.refresh?.();
            }
            return true;
        }

        function handleEvaluationBulkAction(action, helpers) {
            const {
                bulkBar,
                selectedCountEl,
                selectAllEl,
                controller
            } = helpers;
            if (!action) return;

            if (action === 'clear') {
                clearEvaluationSelection({
                    bulkBar,
                    selectedCountEl,
                    selectAllEl,
                    controller
                });
                return;
            }

            const ids = Array.from(evaluationSelection);
            if (!ids.length) return;

            if (action === 'delete') {
                pendingEvaluationBulk = {
                    ids,
                    helpers: {
                        bulkBar,
                        selectedCountEl,
                        selectAllEl,
                        controller
                    }
                };
                const countEl = document.getElementById('evaluationBulkDeleteCount');
                if (countEl) countEl.textContent = ids.length;
                evaluationBulkDeleteModalInstance?.show();
            }
        }

        function clearEvaluationSelection({
            bulkBar,
            selectedCountEl,
            selectAllEl,
            controller
        }) {
            evaluationSelection.clear();
            document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]').forEach((row) => {
                row.classList.remove('is-selected');
                const checkbox = row.querySelector('[data-row-select]');
                if (checkbox) checkbox.checked = false;
            });

            if (selectAllEl) {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = false;
            }

            updateEvaluationBulkBar(bulkBar, selectedCountEl);
            controller?.refresh?.();
        }

        function handleEvaluationBulkDelete(ids, {
            bulkBar,
            selectedCountEl,
            selectAllEl,
            controller
        }, options = {}) {
            const {
                loadingButton
            } = options;
            if (loadingButton) {
                toggleButtonLoading(
                    loadingButton,
                    true,
                    loadingButton.dataset.defaultText || 'Delete Selected',
                    loadingButton.dataset.loadingText || 'Deleting...'
                );
            }

            return fetch('{{ route('dm.evaluation.bulkDestroy') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        ids
                    })
                })
                .then(async (response) => {
                    if (!response.ok) {
                        const error = await response.json().catch(() => ({
                            message: 'Bulk delete failed.'
                        }));
                        throw new Error(error.message || 'Bulk delete failed.');
                    }
                    return response.json();
                })
                .then((payload) => {
                    ids.forEach((id) => {
                        removeEvaluationRow(id, {
                            skipRefresh: true
                        });
                    });

                    if (selectAllEl) {
                        selectAllEl.checked = false;
                        selectAllEl.indeterminate = false;
                    }

                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                    refreshEvaluationFilterOptions();
                    updateEvaluationEmptyState();
                    controller?.refresh?.();
                    applyEvaluationFilters(controller, evaluationFilterToggleEl);
                    evaluationBulkDeleteModalInstance?.hide();
                    const successMessage = payload?.message || 'Selected evaluation forms deleted successfully.';
                    showTemporaryToast(successMessage, 'success');
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Bulk delete failed.', 'danger');
                })
                .finally(() => {
                    if (loadingButton) {
                        toggleButtonLoading(loadingButton, false);
                    }
                });
        }

        function handleEvaluationDelete(context, loadingButton) {
            if (!context || !context.deleteUrl) {
                toggleButtonLoading(loadingButton, false);
                return Promise.resolve();
            }

            return fetch(context.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || 'Failed to delete evaluation form.');
                    }
                    return payload;
                })
                .then((payload) => {
                    removeEvaluationRow(context.id);
                    showTemporaryToast(payload.message || 'Evaluation form deleted successfully.', 'success');
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to delete evaluation form.', 'danger');
                })
                .finally(() => {
                    toggleButtonLoading(loadingButton, false);
                });
        }

        function handleEvaluationToggle(button) {
            if (!button) {
                return;
            }
            const evaluationId = button.dataset.evaluationId;
            const toggleUrl = button.dataset.toggleUrl;
            if (!evaluationId || !toggleUrl) {
                return;
            }

            toggleButtonLoading(button, true);

            fetch(toggleUrl, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({}),
                })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || 'Failed to update evaluation status.');
                    }
                    return payload;
                })
                .then((payload) => {
                    updateEvaluationRowStatus(evaluationId, payload.data || {});
                    showTemporaryToast(payload.message || 'Evaluation status updated successfully.', 'success');
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to update evaluation status.', 'danger');
                })
                .finally(() => {
                    toggleButtonLoading(button, false);
                });
        }

        function updateEvaluationRowStatus(evaluationId, payload) {
            const row = document.querySelector(`#evaluationTable tbody tr[data-evaluation-id="${evaluationId}"]`);
            if (!row) {
                return;
            }

            const statusSlug = String(payload.status || (payload.is_active ? 'active' : 'inactive'));
            const statusLabel = payload.status_label || (statusSlug === 'active' ? 'Active' : 'Inactive');
            row.dataset.sortStatus = statusSlug;
            row.dataset.labelStatus = statusLabel;

            const statusEl = row.querySelector('.evaluation-status');
            if (statusEl) {
                statusEl.textContent = statusLabel;
                statusEl.classList.remove('evaluation-status--active', 'evaluation-status--inactive');
                statusEl.classList.add(`evaluation-status--${statusSlug}`);
            }

            const toggleBtn = row.querySelector('[data-evaluation-toggle]');
            if (toggleBtn) {
                const toggleLabel = payload.toggle_label || (statusSlug === 'active' ? 'Deactivate' : 'Activate');
                toggleBtn.textContent = toggleLabel;
                toggleBtn.dataset.defaultText = toggleLabel;
                toggleBtn.dataset.currentStatus = statusSlug;
            }

            applyEvaluationFilters(evaluationTableController, evaluationFilterToggleEl);
        }

        function attachEvaluationDeleteHandlers() {
            document.querySelectorAll('[data-evaluation-delete]').forEach((button) => {
                if (button.dataset.bound === 'true') return;

                button.dataset.bound = 'true';
                button.addEventListener('click', () => {
                    pendingEvaluationDeleteContext = {
                        id: button.dataset.evaluationId,
                        name: button.dataset.evaluationName || 'this evaluation form',
                        deleteUrl: button.dataset.deleteUrl,
                    };
                    const nameEl = document.getElementById('evaluationDeleteName');
                    if (nameEl && pendingEvaluationDeleteContext) {
                        nameEl.textContent = pendingEvaluationDeleteContext.name;
                    }
                    evaluationDeleteModalInstance?.show();
                });
            });
        }

        function attachEvaluationActionHandlers() {
            document.querySelectorAll('[data-evaluation-preview]').forEach((button) => {
                if (button.dataset.bound === 'true') return;

                button.dataset.bound = 'true';
                button.addEventListener('click', () => {
                    const evaluationId = button.dataset.evaluationId;
                    const facultyName = button.dataset.evaluationFaculty || '';
                    const academicYear = button.dataset.evaluationYear || '';
                    const semester = button.dataset.evaluationSemester || '';

                    if (!evaluationId) return;
                    showQrModal(evaluationId, facultyName, academicYear, semester);
                });
            });

            document.querySelectorAll('[data-evaluation-toggle]').forEach((button) => {
                if (button.dataset.toggleBound === 'true') return;

                button.dataset.toggleBound = 'true';
                button.addEventListener('click', () => {
                    handleEvaluationToggle(button);
                });
            });
        }

        function showTemporaryToast(message, type = 'success') {
            if (window.dmToast && typeof window.dmToast.show === 'function') {
                window.dmToast.show({
                    type,
                    message
                });
                return;
            }
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 280px;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function initEvaluationSearch() {
            const searchInput = document.getElementById('evaluationSearch');
            const clearButton = document.getElementById('evaluationSearchClear');

            if (!searchInput || !clearButton) return;

            const toggleClearVisibility = () => {
                clearButton.classList.toggle('is-visible', searchInput.value.trim() !== '');
            };

            searchInput.addEventListener('input', toggleClearVisibility);

            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                toggleClearVisibility();
                searchInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });

            toggleClearVisibility();
        }

        function initEvaluationFilters(controller) {
            const selectEls = {
                faculty: document.getElementById('evaluationFilterFaculty'),
                department: document.getElementById('evaluationFilterDepartment'),
                year: document.getElementById('evaluationFilterYear'),
                semester: document.getElementById('evaluationFilterSemester'),
                status: document.getElementById('evaluationFilterStatus'),
            };
            const resetBtn = document.getElementById('evaluationFilterReset');
            const toggleBtn = document.getElementById('evaluationFilterToggle');

            evaluationFilterControls = selectEls;
            evaluationFilterToggleEl = toggleBtn;

            populateEvaluationFilterOptions(selectEls);
            updateEvaluationFilterToggle(toggleBtn);

            Object.entries(selectEls).forEach(([key, select]) => {
                if (!select) return;
                select.addEventListener('change', (event) => {
                    evaluationFilters[key] = event.target.value || 'all';
                    applyEvaluationFilters(controller, toggleBtn);
                });
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    Object.keys(evaluationFilters).forEach((key) => {
                        evaluationFilters[key] = 'all';
                        if (selectEls[key]) {
                            selectEls[key].value = 'all';
                        }
                    });
                    applyEvaluationFilters(controller, toggleBtn);
                });
            }
        }

        function populateEvaluationFilterOptions(selectEls) {
            const options = collectEvaluationFilterOptions();
            fillEvaluationFilterSelect(selectEls.faculty, options.faculty);
            fillEvaluationFilterSelect(selectEls.department, options.department);
            fillEvaluationFilterSelect(selectEls.year, options.year);
            fillEvaluationFilterSelect(selectEls.semester, options.semester);
        }

        function refreshEvaluationFilterOptions() {
            if (!evaluationFilterControls) {
                return;
            }
            populateEvaluationFilterOptions(evaluationFilterControls);
            updateEvaluationFilterToggle(evaluationFilterToggleEl);
        }

        function collectEvaluationFilterOptions() {
            const rows = Array.from(document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]'));
            const sets = {
                faculty: new Map(),
                department: new Map(),
                year: new Map(),
                semester: new Map(),
            };

            rows.forEach((row) => {
                const facultyValue = normaliseFilterValue(row.dataset.sortFaculty);
                if (facultyValue && !sets.faculty.has(facultyValue)) {
                    sets.faculty.set(facultyValue, row.dataset.labelFaculty || row.dataset.sortFaculty ||
                        'Unknown');
                }

                const departmentValues = parseDepartments(row.dataset.sortDepartment);
                const departmentLabels = parseDepartments(row.dataset.labelDepartment);
                departmentValues.forEach((value, index) => {
                    const normalised = normaliseFilterValue(value);
                    if (!normalised || sets.department.has(normalised)) {
                        return;
                    }
                    const label = departmentLabels[index] || value || 'N/A';
                    sets.department.set(normalised, label);
                });

                const yearValue = normaliseFilterValue(row.dataset.sortYear);
                if (yearValue && !sets.year.has(yearValue)) {
                    sets.year.set(yearValue, row.dataset.sortYear || 'N/A');
                }

                const semesterValue = normaliseFilterValue(row.dataset.sortSemester);
                if (semesterValue && !sets.semester.has(semesterValue)) {
                    const label = row.dataset.labelSemester || row.dataset.sortSemester || 'N/A';
                    sets.semester.set(semesterValue, label);
                }
            });

            const toOptions = (map) => Array.from(map.entries())
                .sort((a, b) => a[1].localeCompare(b[1]))
                .map(([value, label]) => ({
                    value,
                    label
                }));

            const departmentOptions = Array.isArray(evaluationAllowedDepartmentOptions) &&
                evaluationAllowedDepartmentOptions.length > 0 ?
                evaluationAllowedDepartmentOptions :
                toOptions(sets.department);

            return {
                faculty: toOptions(sets.faculty),
                department: departmentOptions,
                year: toOptions(sets.year),
                semester: toOptions(sets.semester),
            };
        }

        function fillEvaluationFilterSelect(select, options) {
            if (!select) return;
            const previous = select.value;
            const entries = ['<option value="all">All</option>']
                .concat(options.map((option) => `<option value="${option.value}">${option.label}</option>`));
            select.innerHTML = entries.join('');
            select.value = options.some((option) => option.value === previous) ? previous : 'all';
        }

        function applyEvaluationFilters(controller, toggleBtn) {
            const rows = Array.from(document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]'));
            const bulkBar = document.getElementById('evaluationBulkBar');
            const selectedCountEl = document.getElementById('evaluationSelectedCount');
            const selectAllEl = document.getElementById('evaluationSelectAll');

            rows.forEach((row) => {
                const matches = matchesEvaluationFilters(row);
                const checkbox = row.querySelector('[data-row-select]');
                if (matches) {
                    row.removeAttribute('data-ignore');
                    row.style.display = '';
                } else {
                    row.setAttribute('data-ignore', 'true');
                    row.style.display = 'none';
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                    evaluationSelection.delete(row.dataset.evaluationId);
                }
            });

            syncEvaluationSelectAll(selectAllEl, controller);
            updateEvaluationBulkBar(bulkBar, selectedCountEl);
            updateEvaluationFilterToggle(toggleBtn);

            controller?.refresh?.();
        }

        function matchesEvaluationFilters(row) {
            if (!row) return false;
            if (evaluationFilters.faculty !== 'all') {
                const facultyValue = normaliseFilterValue(row.dataset.sortFaculty);
                if (facultyValue !== evaluationFilters.faculty) {
                    return false;
                }
            }
            if (evaluationFilters.department !== 'all') {
                const departmentValues = parseDepartments(row.dataset.sortDepartment)
                    .map((value) => normaliseFilterValue(value));
                if (!departmentValues.includes(evaluationFilters.department)) {
                    return false;
                }
            }
            if (evaluationFilters.year !== 'all') {
                const yearValue = normaliseFilterValue(row.dataset.sortYear);
                if (yearValue !== evaluationFilters.year) {
                    return false;
                }
            }
            if (evaluationFilters.semester !== 'all') {
                const semesterValue = normaliseFilterValue(row.dataset.sortSemester);
                if (semesterValue !== evaluationFilters.semester) {
                    return false;
                }
            }
            if (evaluationFilters.status !== 'all') {
                const statusValue = normaliseFilterValue(row.dataset.sortStatus);
                if (statusValue !== evaluationFilters.status) {
                    return false;
                }
            }
            return true;
        }

        function updateEvaluationFilterToggle(toggleBtn) {
            if (!toggleBtn) return;
            const isActive = Object.values(evaluationFilters).some((value) => value !== 'all');
            toggleBtn.classList.toggle('is-active', isActive);
        }

        function normaliseFilterValue(value) {
            return String(value ?? '')
                .trim()
                .toLowerCase();
        }

        function parseDepartments(value) {
            if (value === null || value === undefined) {
                return [];
            }
            const text = String(value).trim();
            if (text === '') {
                return [];
            }
            return text
                .split(',')
                .map((item) => item.trim())
                .filter((item) => item !== '');
        }

        function initEvaluationFormLoading() {
            const forms = [
                document.getElementById('generateEvaluationForm'),
                document.getElementById('generateAllFormModal'),
            ].filter(Boolean);

            forms.forEach((form) => {
                if (form.dataset.loadingInitialized === 'true') return;
                form.dataset.loadingInitialized = 'true';

                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) return;

                form.addEventListener('submit', () => {
                    if (!form.checkValidity()) {
                        return;
                    }

                    toggleButtonLoading(submitBtn, true);
                });
            });
        }

        function initEvaluationSelectDropdowns() {
            const dropdowns = document.querySelectorAll('[data-searchable-dropdown]');
            dropdowns.forEach((dropdown) => {
                if (dropdown.dataset.dropdownInitialized === 'true') return;
                dropdown.dataset.dropdownInitialized = 'true';

                const label = dropdown.querySelector('[data-dropdown-label]');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const searchInput = dropdown.querySelector('[data-dropdown-search]');
                const listWrapper = dropdown.querySelector('[data-dropdown-list]');
                const toggleBtn = dropdown.querySelector('[data-dropdown-toggle]');
                const placeholderText = label?.dataset.placeholderText?.trim() || '-- Select Option --';

                const setLabel = (text, isPlaceholder = false) => {
                    if (!label) return;
                    label.textContent = text;
                    label.classList.toggle('is-placeholder', isPlaceholder);
                };

                const getOptions = () => Array.from(dropdown.querySelectorAll('[data-dropdown-option]'));

                const applySearchFilter = () => {
                    const term = searchInput ? searchInput.value.trim().toLowerCase() : '';
                    const hasTerm = term !== '';

                    getOptions().forEach((option) => {
                        const filter = option.dataset.optionFilter || option.dataset.optionLabel ||
                            option.textContent;
                        const matches = !hasTerm || (filter && filter.toLowerCase().includes(term));
                        option.classList.toggle('d-none', !matches);
                    });

                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                };

                const selectOption = (option) => {
                    if (!option) return;
                    const value = option.dataset.optionValue ?? '';
                    const labelText = option.dataset.optionLabel ??
                        option.querySelector('span')?.textContent?.trim() ??
                        option.textContent.trim() ??
                        placeholderText;

                    if (hiddenInput) {
                        hiddenInput.value = value;
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    setLabel(labelText, !value);

                    if (toggleBtn && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                        const instance = bootstrap.Dropdown.getOrCreateInstance(toggleBtn);
                        instance.hide();
                    }
                };

                if (listWrapper) {
                    listWrapper.addEventListener('click', (event) => {
                        const option = event.target.closest('[data-dropdown-option]');
                        if (!option) return;
                        event.preventDefault();
                        selectOption(option);
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', applySearchFilter);
                }

                dropdown.addEventListener('shown.bs.dropdown', () => {
                    if (searchInput) {
                        searchInput.value = '';
                        applySearchFilter();
                        searchInput.focus();
                    } else {
                        applySearchFilter();
                    }
                });

                const parentForm = dropdown.closest('form');
                if (parentForm) {
                    parentForm.addEventListener('reset', () => {
                        setTimeout(() => {
                            if (hiddenInput) hiddenInput.value = '';
                            setLabel(placeholderText, true);
                            if (searchInput) searchInput.value = '';
                            applySearchFilter();
                        }, 0);
                    });
                }

                const initialValue = hiddenInput?.value?.trim();
                if (initialValue) {
                    const option = getOptions().find((opt) => opt.dataset.optionValue === initialValue);
                    if (option) {
                        setLabel(option.dataset.optionLabel || option.textContent.trim(), false);
                    } else {
                        setLabel(placeholderText, false);
                    }
                } else {
                    setLabel(placeholderText, true);
                }

                applySearchFilter();
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                if (window.dmToast && typeof window.dmToast.show === 'function') {
                    window.dmToast.show({
                        type: 'success',
                        title: 'Link copied',
                        message: 'Link copied to clipboard!',
                    });
                } else {
                    showTemporaryToast('Link copied to clipboard!', 'success');
                }
            }).catch(() => {
                showTemporaryToast('Unable to copy the link. Please try again.', 'danger');
            });
        }

        function showQrModal(evaluationId, facultyName, academicYear, semester) {
            document.getElementById('qrFacultyName').textContent = facultyName;
            document.getElementById('qrDetails').textContent = `${academicYear} - ${semester} Semester`;

            const qrContainer = document.getElementById('qrCodeContainer');
            const qrLoader = document.getElementById('qrLoader');
            if (qrContainer) {
                qrContainer.innerHTML = '';
            }
            if (qrLoader) {
                qrLoader.classList.remove('d-none');
            }

            const qrUrl = `/data-management/evaluation/${evaluationId}/qr?ts=${Date.now()}`;
            fetch(qrUrl, {
                    cache: 'no-store'
                })
                .then((res) => {
                    if (!res.ok) {
                        throw new Error('QR request failed');
                    }
                    return res.blob();
                })
                .then((blob) => {
                    if (currentQrObjectUrl) {
                        URL.revokeObjectURL(currentQrObjectUrl);
                    }
                    currentQrObjectUrl = URL.createObjectURL(blob);
                    if (qrContainer) {
                        qrContainer.innerHTML = `<img src="${currentQrObjectUrl}" class="img-fluid" alt="QR Code">`;
                    }
                })
                .catch(() => {
                    if (qrContainer) {
                        qrContainer.innerHTML =
                            '<p class="text-danger">QR Code generation failed. Please try downloading instead.</p>';
                    }
                })
                .finally(() => {
                    if (qrLoader) {
                        qrLoader.classList.add('d-none');
                    }
                });

            const downloadBtn = document.getElementById('downloadQrBtn');
            if (downloadBtn) {
                downloadBtn.href = `/data-management/evaluation/${evaluationId}/qr/download`;
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('qrModal')).show();
        }
    </script>
@endsection
