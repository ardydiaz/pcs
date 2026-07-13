@extends('layouts/contentNavbarLayout')

@php
    $container = 'container-xxl';
    $enhancedUsers = collect($users ?? [])->map(function ($user) {
        $resolvedDepartment = trim($user->department ?? '') !== ''
            ? $user->department
            : optional($user->faculty)->department;
        $resolvedJobTitle = trim($user->job_title ?? '') !== ''
            ? $user->job_title
            : optional($user->faculty)->job_title;

        $user->resolved_department = $resolvedDepartment;
        $user->resolved_job_title = $resolvedJobTitle;
        return $user;
    });

    $roleOptions = [
        ['value' => 'Admin', 'label' => 'Admin'],
        ['value' => 'NTP', 'label' => 'NTP'],
        ['value' => 'Faculty', 'label' => 'Faculty'],
        ['value' => 'Student', 'label' => 'Student'],
    ];
    $accessLevelOptions = [
        ['value' => 'View All Reports', 'label' => 'View All Reports'],
        ['value' => 'View Department Reports', 'label' => 'View Department Reports'],
        ['value' => 'Manage Faculties', 'label' => 'Manage Faculties'],
        ['value' => 'Manage Courses', 'label' => 'Manage Courses'],
        ['value' => 'Manage Schedules', 'label' => 'Manage Schedules'],
        ['value' => 'Manage Evaluations', 'label' => 'Manage Evaluations'],
        ['value' => 'Manage Evaluation QR/Link', 'label' => 'Manage Evaluation QR/Link'],
        ['value' => 'View/Answer Forms', 'label' => 'View/Answer Forms'],
    ];

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

    $departmentOptions = (isset($departmentOptions) ? collect($departmentOptions) : $enhancedUsers
            ->flatMap(function ($user) {
                $raw = $user->resolved_department ?? '';
                return collect(explode(',', $raw))
                    ->map(function ($value) {
                        return trim($value);
                    })
                    ->filter(function ($value) {
                        return $value !== '';
                    })
                    ->values();
            }))
        ->unique()
        ->sort()
        ->values();

    $departmentSelectOptions = collect($predefinedDepartments)
        ->merge($departmentOptions)
        ->unique()
        ->sort()
        ->values();

    $jobTitleOptions = (isset($jobTitleOptions) ? collect($jobTitleOptions) : $enhancedUsers
            ->pluck('resolved_job_title'))
        ->filter(function ($jobTitle) {
            return trim($jobTitle ?? '') !== '';
        })
        ->unique()
        ->sort()
        ->values();

    $statusOptions = [
        'Active' => 'Active',
        'Inactive' => 'Inactive',
    ];

    $userPayload = $enhancedUsers->map(function ($user) {
        $departmentRaw = trim($user->resolved_department ?? '');
        $departmentList = collect(explode(',', $departmentRaw))
            ->map(function ($value) {
                return trim($value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values();
        $resolvedDepartment = $departmentList->isEmpty()
            ? '—'
            : $departmentList->implode(', ');
        $resolvedJobTitle = trim($user->resolved_job_title ?? '') !== '' ? $user->resolved_job_title : '—';
        $rawStatus = $user->status ?? '';
        $statusSlug = strtolower($rawStatus ?: 'inactive');
        $statusClass = in_array($statusSlug, ['active', 'enabled']) ? 'active' : (in_array($statusSlug, ['pending', 'invited']) ? 'pending' : 'inactive');
        $statusValue = $statusSlug === 'active'
            ? 'Active'
            : ($statusSlug === 'inactive' ? 'Inactive' : $rawStatus);
        $statusLabel = $statusValue ?: 'Inactive';
        $rawRole = $user->role ?? '';
        $roleLower = strtolower($rawRole);
        $roleValue = $roleLower === 'admin'
            ? 'Admin'
            : ($roleLower === 'ntp'
                ? 'NTP'
                : ($roleLower === 'faculty'
                    ? 'Faculty'
                    : ($roleLower === 'student' ? 'Student' : $rawRole)));
        $accessLevels = collect($user->access_level ?? [])
            ->filter(function ($level) {
                return trim($level ?? '') !== '';
            })
            ->values();

        return [
            'id' => $user->id,
            'name' => $user->name ?? 'Unnamed User',
            'email' => $user->email ?? '',
            'department_raw' => $departmentRaw,
            'departments' => $departmentList->values(),
            'department_label' => $resolvedDepartment,
            'job_title' => $resolvedJobTitle,
            'role' => $roleValue ?: 'User',
            'access_levels' => $accessLevels->values(),
            'access_levels_label' => $accessLevels->isEmpty() ? '—' : $accessLevels->implode(', '),
            'status_label' => $statusLabel,
            'status_value' => $statusValue,
            'status_slug' => $statusSlug,
            'status_class' => $statusClass,
            'is_current_user' => auth()->check() && auth()->id() === $user->id,
        ];
    })->values();
@endphp

@section('title', 'User Management - Users')

@section('page-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --um-purple: #5c297c;
            --um-yellow: #ffb736;
            --dm-pill-max: clamp(5.5rem, 16vw, 10rem);
            --dm-cell-max: clamp(8.5rem, 22vw, 13.5rem);
            --dm-stack-max: clamp(10.5rem, 28vw, 18rem);
        }

        .container-fluid {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            max-width: 100%;
        }

        .layout-page .content-wrapper > .container-xxl.container-p-y {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .layout-page .content-wrapper > .container-xxl.container-p-y > .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }

        .user-card {
            background-color: var(--bs-card-bg);
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
        }

        .user-action-card {
            background-color: var(--um-yellow);
            color: var(--um-purple);
        }

        .user-action-card .card-title {
            color: var(--um-purple);
        }

        .user-action-card .card-title i {
            color: var(--um-purple);
        }

        .user-action-card p,
        .user-action-card .text-muted {
            color: #ffffff !important;
        }

        .user-action-card .btn-user-action {
            background-color: #5c297c;
            color: #ffb736;
            border: none;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
            border-radius: 0.65rem;
        }

        .user-action-card .btn-user-action i {
            color: #ffb736;
        }

        .user-action-card .btn-user-action:hover,
        .user-action-card .btn-user-action:focus {
            background-color: #4b2266;
            color: #ffb736;
        }

        .user-action-card .btn-user-action,
        .user-action-card .btn-user-action:hover,
        .user-action-card .btn-user-action:focus {
            transform: none;
            box-shadow: none;
        }

        .btn-deleted-user {
            min-height: calc(2.25rem + 2px);
            padding: 0 1rem;
            border: 1px solid rgba(92, 41, 124, 0.18);
            border-radius: 0.55rem;
            background: #5c297c;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 0.45rem 1rem rgba(92, 41, 124, 0.14);
        }

        .btn-deleted-user:hover,
        .btn-deleted-user:focus {
            border-color: #e6a431;
            background: #ffb736;
            color: #3a0050;
            box-shadow: 0 0.55rem 1.1rem rgba(230, 164, 49, 0.2);
        }

        .btn-restore-user {
            background: linear-gradient(135deg, #5c297c, #6f2a8f);
            border: 1px solid #5c297c;
            color: #ffffff;
            border-radius: 999px;
            font-weight: 700;
            padding: 0.45rem 0.9rem;
            box-shadow: 0 0.55rem 1.2rem rgba(92, 41, 124, 0.18);
        }

        .btn-restore-user:hover,
        .btn-restore-user:focus {
            background: linear-gradient(135deg, #ffb736, #e6a431);
            border-color: #e6a431;
            color: #3a0050;
            box-shadow: 0 0.65rem 1.35rem rgba(230, 164, 49, 0.24);
        }

        .user-card--table {
            border-radius: var(--bs-card-border-radius);
            padding: 0;
        }

        .evaluation-card {
            background-color: var(--bs-card-bg);
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
        }

        .evaluation-card--table {
            border-radius: var(--bs-card-border-radius);
            padding: 0;
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

        .table-filter-dropdown {
            position: relative;
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

        .table-cell-stack > * {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
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
            display: none;
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
            display: inline-flex;
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

        .evaluation-col-selection {
            width: 48px;
            text-align: center;
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

        .modal-content.evaluation-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
            overflow: visible;
        }

        .evaluation-modal-header {
            background-color: #f5f7fb;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .evaluation-modal-body {
            padding: 1.5rem;
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

        .evaluation-modal-body .form-control[readonly] {
            background-color: #f1f5f9;
            color: #6b7280;
            border: 1px solid #e2e8f0;
            cursor: not-allowed;
            box-shadow: none !important;
            pointer-events: none;
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

        .evaluation-modal-body .mb-3 {
            margin-bottom: 1.25rem;
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

        .evaluation-modal-dialog--narrow {
            max-width: 42%;
        }

        @media (max-width: 992px) {
            .evaluation-modal-dialog--narrow {
                max-width: 60%;
            }
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

        .evaluation-bulk-btn--access {
            background: rgba(92, 41, 124, 0.1);
            color: #5c297c !important;
            border: 1px solid rgba(92, 41, 124, 0.18);
        }

        .access-preset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
            gap: .75rem;
        }

        .access-preset-btn {
            border: 1px solid #e6d9ee;
            border-radius: 12px;
            background: #ffffff;
            color: #3a0050;
            padding: .85rem;
            text-align: left;
            transition: all .18s ease;
        }

        .access-preset-btn:hover,
        .access-preset-btn.is-active {
            border-color: #ffb736;
            background: linear-gradient(135deg, rgba(255, 183, 54, .18), rgba(92, 41, 124, .08));
            box-shadow: 0 10px 24px rgba(92, 41, 124, .12);
        }

        .access-preset-title {
            display: block;
            font-weight: 700;
            font-size: .82rem;
        }

        .access-preset-caption {
            display: block;
            margin-top: .2rem;
            color: #6b7280;
            font-size: .72rem;
            line-height: 1.35;
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

        div[data-table-id="usersTable"] [data-table-info] {
            font-size: 0.875rem;
            color: #6b7280;
        }

        div[data-table-id="usersTable"] .pagination .page-link {
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

        div[data-table-id="usersTable"] .pagination .page-link:hover:not(.disabled) {
            background-color: #cbd5e1;
            color: #475569;
        }

        div[data-table-id="usersTable"] .pagination .page-item.active .page-link {
            background-color: #5c297c;
            color: #ffffff;
        }

        div[data-table-id="usersTable"] .pagination .page-item.active .page-link:hover {
            background-color: #4b2266;
            color: #ffffff;
        }

        div[data-table-id="usersTable"] .pagination .page-link span {
            color: inherit;
        }

        div[data-table-id="usersTable"] .pagination .page-item.disabled .page-link {
            background-color: #e2e8f0;
            color: #94a3b8;
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

        .evaluation-actions .dropdown-menu {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
            min-width: 180px;
        }

        .evaluation-actions .dropdown-info-message {
            font-size: 0.8rem;
            color: #94a3b8;
            line-height: 1.3;
            cursor: default;
        }

        .evaluation-col-selection {
            width: 48px;
            text-align: center;
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
            margin-bottom: 0;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 0;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-weight: 600;
            color: #0f172a;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .searchable-dropdown-toggle:hover,
        .searchable-dropdown-toggle:focus,
        .searchable-dropdown-toggle:active,
        .searchable-dropdown.show .searchable-dropdown-toggle {
            border-color: #94a3b8;
            box-shadow: none;
            color: #1e293b;
        }

        .searchable-dropdown-toggle:focus {
            outline: none;
            box-shadow: none;
        }

        .searchable-dropdown-toggle i {
            font-size: 1.25rem;
            color: #94a3b8;
        }

        .access-multiselect {
            position: relative;
        }

        .access-multiselect-toggle {
            border: 1px solid #d9dee3;
            border-radius: 0.6rem;
            padding: 0.6rem 0.85rem;
            background-color: #ffffff;
            min-height: 3.1rem;
            cursor: pointer;
            color: #0f172a;
            font-weight: 500;
            box-shadow: none;
            gap: 0.4rem;
        }

        .access-multiselect-toggle:hover,
        .access-multiselect-toggle:focus,
        .access-multiselect.show .access-multiselect-toggle {
            border-color: #94a3b8;
            background-color: #ffffff;
            box-shadow: none;
        }

        .access-multiselect-toggle:focus {
            outline: none;
        }

        .access-multiselect-content {
            min-height: 1.5rem;
        }

        .access-multiselect-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .access-multiselect-placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .access-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background-color: #e3e8f1;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .access-chip button {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 0.85rem;
            padding: 0;
            line-height: 1;
        }

        .access-multiselect-search {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .access-multiselect-search input {
            padding-left: 0.85rem;
            border-radius: 0.6rem;
        }

        .access-multiselect .dropdown-menu {
            width: 100%;
            border-radius: 0.85rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
            padding: 1rem;
            top: calc(100% + 0.5rem) !important;
            bottom: auto !important;
            transform: none !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1105;
        }

        .access-multiselect-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.5rem;
            max-height: calc((2 * 3rem) + 0.5rem);
            overflow-y: auto;
        }

        .access-multiselect-option {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.65rem;
            padding: 0.5rem 0.85rem;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .access-multiselect-option:hover,
        .access-multiselect-option:focus-within {
            border-color: #c7d2fe;
            background-color: #eef2ff;
        }

        .access-multiselect-option span {
            font-weight: 600;
            color: #0f172a;
        }

        .access-multiselect-option.is-disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .access-multiselect-toggle.is-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .department-multiselect {
            position: relative;
        }

        .department-multiselect-toggle {
            min-height: 2.5rem;
            padding: 0.35rem 0.65rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .department-multiselect-toggle:hover,
        .department-multiselect-toggle:focus,
        .department-multiselect.show .department-multiselect-toggle {
            border-color: #94a3b8;
            background-color: #ffffff;
            box-shadow: none;
        }

        .department-multiselect-toggle:focus {
            outline: none;
        }

        .department-multiselect-content {
            min-height: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            align-items: center;
        }

        .department-multiselect-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .department-multiselect-placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .department-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background-color: #e3e8f1;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .department-chip-remove {
            border: none;
            background: transparent;
            color: inherit;
            font-size: 0.85rem;
            padding: 0;
            line-height: 1;
        }

        .department-multiselect-search {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .department-multiselect-search input {
            padding-left: 0.85rem;
            border-radius: 0.6rem;
        }

        .department-multiselect .dropdown-menu {
            width: 100%;
            border-radius: 0.85rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
            padding: 1rem;
            top: calc(100% + 0.5rem) !important;
            bottom: auto !important;
            transform: none !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1105;
        }

        .department-multiselect-list {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            max-height: 220px;
            overflow-y: auto;
        }

        .department-multiselect-option {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.65rem;
            padding: 0.5rem 0.85rem;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .department-multiselect-option:hover,
        .department-multiselect-option:focus-within {
            border-color: #c7d2fe;
            background-color: #eef2ff;
        }

        .department-multiselect-option span {
            font-weight: 600;
            color: #0f172a;
        }

        .searchable-dropdown-label {
            flex: 1;
            text-align: left;
            color: #0f172a;
        }

        .searchable-dropdown-label.is-placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .searchable-dropdown .dropdown-menu {
            width: 100%;
            max-height: 320px;
            overflow: hidden;
            padding: 0.75rem;
            border-radius: 0.9rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.15);
        }

        .searchable-dropdown-list {
            max-height: 220px;
            overflow-y: auto;
            margin-top: 0.5rem;
            border-radius: 0.75rem;
            padding-right: 0.25rem;
        }

        .searchable-dropdown-list--grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.65rem;
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
            color: #111827;
        }

        .searchable-dropdown .dropdown-item span,
        .searchable-dropdown .dropdown-item small {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .searchable-dropdown .dropdown-item small {
            color: #94a3b8;
            font-weight: 400;
            font-size: 0.8rem;
        }

        .searchable-dropdown-search {
            border-radius: 0.65rem;
            padding: 0.55rem 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .user-modal-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
            overflow: visible;
        }

        .user-modal-header {
            background-color: #f5f7fb;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .user-modal-body {
            padding: 1.5rem;
        }

        .user-modal-close {
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

        .user-modal-close:focus {
            outline: none;
            box-shadow: none;
        }

        .user-modal-footer {
            border-top: none;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1rem 1.5rem;
        }

        .user-modal-footer .btn {
            min-width: 120px;
        }

        .user-modal-body .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: normal;
        }

        .user-modal-body .form-select,
        .user-modal-body .form-control {
            border-radius: 0.6rem;
            padding: 0.6rem 0.85rem;
        }

        .user-modal-dialog--narrow {
            max-width: 42%;
        }

        @media (max-width: 992px) {
            .user-modal-dialog--narrow {
                max-width: 60%;
            }
        }

        .btn-user-primary {
            background-color: #5c297c;
            border-color: #5c297c;
            color: #ffffff;
            border-radius: 0.65rem;
        }

        .btn-user-primary:hover,
        .btn-user-primary:focus {
            background-color: #4b2266;
            border-color: #4b2266;
            color: #ffffff;
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

        .user-modal-btn,
        .user-modal-btn:hover,
        .user-modal-btn:focus {
            transform: none;
            box-shadow: none;
            transition: none;
        }

        .user-modal-footer .btn-tertiary,
        .user-modal-footer .btn-tertiary:focus,
        .user-modal-footer .btn-tertiary:active {
            background-color: transparent;
            color: #475569;
            border: 1px solid transparent;
            box-shadow: none;
        }

        @media (max-width: 992px) {
            .evaluation-table {
                border-spacing: 0 0.5rem;
            }

            .evaluation-table tbody td {
                white-space: nowrap;
            }

            .evaluation-search-wrapper {
                width: 100%;
            }
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
        <div class="card user-card user-action-card mb-4">
            <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <div class="text-center text-lg-start">
                    <h5 class="card-title mb-1 d-flex align-items-center gap-2 justify-content-center justify-content-lg-start" style="font-size: 1.2rem;">
                        <i class="fa-solid fa-users-gear" style="font-size: 1.5rem;"></i>
                        User Actions
                    </h5>
                    <p class="mb-0">Use these quick actions to add new users or invite them to the platform.</p>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                    <button type="button" class="btn btn-user-action" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Add User
                    </button>
                </div>
            </div>
        </div>

        <div class="card evaluation-card evaluation-card--table" data-table-controller data-table-id="usersTable">
            <div class="card-body border-0 evaluation-controls">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 flex-wrap user-controls-stack">
                            <label for="userRowsPerPage" class="text-muted small">Lines per page</label>
                            <select id="userRowsPerPage" class="form-select evaluation-page-size fw-bold" style="width: auto;" data-table-length>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                               {{-- <option value="all">All</option> --}}
                            </select>
                            <button type="button" class="btn btn-deleted-user" data-bs-toggle="modal"
                                data-bs-target="#userDeletedModal">
                                <i class="fa-solid fa-trash-arrow-up me-2"></i>Deleted Users
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <div class="dropdown table-filter-dropdown">
                                <button class="filter-toggle" type="button" id="userFilterToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span>Filters</span>
                                    <i class="bx bx-filter"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3">
                                    <div class="mb-3">
                                        <label class="form-label small">Department</label>
                                        <select class="form-select" id="filterDepartment">
                                            <option value="all">All</option>
                                            @foreach ($departmentOptions as $department)
                                                <option value="{{ strtolower($department) }}">{{ $department }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Job Title</label>
                                        <select class="form-select" id="filterJobTitle">
                                            <option value="all">All</option>
                                            @foreach ($jobTitleOptions as $jobTitle)
                                                <option value="{{ strtolower($jobTitle) }}">{{ $jobTitle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Role</label>
                                        <select class="form-select" id="filterRole">
                                            <option value="all">All</option>
                                            @foreach ($roleOptions as $option)
                                                <option value="{{ strtolower($option['value']) }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Status</label>
                                        <select class="form-select" id="filterStatus">
                                            <option value="all">All</option>
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ strtolower($value) }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-link p-0 table-filter-reset" id="userFilterReset">Reset Filters</button>
                                    </div>
                                </div>
                            </div>
                            <div class="evaluation-search-wrapper">
                                <i class="bx bx-search evaluation-search-icon"></i>
                                <input type="text" id="userSearch" class="evaluation-search-input" data-table-search placeholder="Search...">
                                <button type="button" class="evaluation-search-clear" id="userSearchClear" aria-label="Clear search">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 evaluation-table" id="usersTable">
                        <thead>
                            <tr>
                                <th class="evaluation-col-selection text-center">
                                    <input type="checkbox" class="form-check-input evaluation-checkbox" id="userSelectAll">
                                </th>
                                <th data-sort-key="name" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Name</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>
                                    <span class="evaluation-sort-label">Email</span>
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
                                <th data-sort-key="job" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Job Title</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="role" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Role</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="access" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Access Level</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="status" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Status</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody"></tbody>
                    </table>
                </div>

                <div class="evaluation-bulk-bar d-none" id="userBulkBar">
                    <span class="fw-semibold" id="userSelectedCount">0 Selected</span>
                    <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--access" data-user-bulk-action="access">
                        <i class="bx bx-key"></i> Set Access
                    </button>
                    <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger" data-user-bulk-action="delete">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                    <button type="button" class="evaluation-bulk-close" data-user-bulk-action="clear" title="Clear selection">
                        <i class="bx bx-x"></i>
                    </button>
                </div>

                <div class="row mt-4 align-items-center">
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="text-muted" data-table-info></div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <nav aria-label="Users table pagination">
                            <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered user-modal-dialog user-modal-dialog--narrow">
        <div class="modal-content user-modal-card">
            <form method="POST" action="{{ route('um.users.store') }}" id="addUserForm">
                @csrf
                <div class="modal-header user-modal-header">
                    <h5 class="modal-title mb-0" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="user-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body user-modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="userName">Full Name</label>
                        <input type="text" class="form-control" id="userName" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="userEmail">Email Address</label>
                        <input type="email" class="form-control" id="userEmail" name="email" placeholder="name@mcu.edu.ph">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="userDepartment">Department</label>
                        <div class="dropdown w-100 department-multiselect" data-department-multiselect>
                            <button class="department-multiselect-toggle w-100 d-flex align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-auto-close="outside">
                                <div class="department-multiselect-content flex-grow-1">
                                    <div class="department-multiselect-chips" data-department-selected></div>
                                    <span class="department-multiselect-placeholder" data-department-placeholder>-- Select Department --</span>
                                </div>
                                <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="department-multiselect-search">
                                    <input type="text" class="form-control" placeholder="Search departments..." data-department-search>
                                </div>
                                <div class="department-multiselect-list" data-department-list>
                                    @foreach ($departmentSelectOptions as $department)
                                        <label class="department-multiselect-option"
                                               data-department-option
                                               data-value="{{ $department }}"
                                               data-label="{{ $department }}"
                                               data-search="{{ strtolower($department) }}">
                                            <input type="checkbox" class="form-check-input" data-department-checkbox value="{{ $department }}">
                                            <span>{{ $department }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" class="form-control" id="userDepartment" name="department" data-department-input required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="userJobTitle">Job Title</label>
                        <input type="text" class="form-control" id="userJobTitle" name="job_title" placeholder="e.g. Faculty" required>
                    </div>
                    <div class="mb-3">
                        @php
                            $rolePlaceholder = '-- Select Role --';
                        @endphp
                        <label class="form-label" for="userRole">Role</label>
                        <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                            <button class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-dropdown-toggle>
                                <span class="searchable-dropdown-label is-placeholder"
                                      data-dropdown-label
                                      data-placeholder-text="{{ $rolePlaceholder }}">{{ $rolePlaceholder }}</span>
                                <i class="bx bx-chevron-down fs-5"></i>
                            </button>
                            <div class="dropdown-menu p-2">
                                <input type="text" class="form-control searchable-dropdown-search" placeholder="Search..." data-dropdown-search>
                                <div class="searchable-dropdown-list searchable-dropdown-list--grid" data-dropdown-list>
                                    @foreach ($roleOptions as $option)
                                        <button type="button"
                                                class="dropdown-item"
                                                data-dropdown-option
                                                data-option-value="{{ $option['value'] }}"
                                                data-option-label="{{ $option['label'] }}"
                                                data-option-filter="{{ strtolower($option['label']) }}">
                                            <span>{{ $option['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="role" id="userRole" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Level</label>
                                    <div class="dropdown w-100 access-multiselect" data-access-multiselect>
                            <button class="access-multiselect-toggle w-100 d-flex align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-auto-close="outside">
                                <div class="access-multiselect-content flex-grow-1">
                                    <div class="access-multiselect-chips" data-access-selected></div>
                                    <span class="access-multiselect-placeholder" data-access-placeholder>-- Select Access Level --</span>
                                </div>
                                <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="access-multiselect-search">
                                    <input type="text" class="form-control" placeholder="Search access levels..." data-access-search>
                                </div>
                                <div class="access-multiselect-list" data-access-list>
                                    @foreach ($accessLevelOptions as $option)
                                        <label class="access-multiselect-option"
                                               data-access-option
                                               data-value="{{ $option['value'] }}"
                                               data-label="{{ $option['label'] }}"
                                               data-search="{{ strtolower($option['label']) }}">
                                            <input type="checkbox" class="form-check-input" data-access-checkbox value="{{ $option['value'] }}">
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div data-access-inputs class="d-none"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer user-modal-footer">
                    <button type="button" class="btn btn-tertiary user-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-user-primary user-modal-btn" data-default-text="Save User" data-loading-text="Saving...">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
        <div class="modal-content evaluation-card">
            <form method="POST" id="editUserForm" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editUserName">Full Name</label>
                        <input type="text" class="form-control" id="editUserName" name="name" placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editUserEmail">Email Address</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" placeholder="name@mcu.edu.ph">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editUserDepartment">Department</label>
                        <div class="dropdown w-100 department-multiselect" data-department-multiselect>
                            <button class="department-multiselect-toggle w-100 d-flex align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-auto-close="outside">
                                <div class="department-multiselect-content flex-grow-1">
                                    <div class="department-multiselect-chips" data-department-selected></div>
                                    <span class="department-multiselect-placeholder" data-department-placeholder>-- Select Department --</span>
                                </div>
                                <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="department-multiselect-search">
                                    <input type="text" class="form-control" placeholder="Search departments..." data-department-search>
                                </div>
                                <div class="department-multiselect-list" data-department-list>
                                    @foreach ($departmentSelectOptions as $department)
                                        <label class="department-multiselect-option"
                                               data-department-option
                                               data-value="{{ $department }}"
                                               data-label="{{ $department }}"
                                               data-search="{{ strtolower($department) }}">
                                            <input type="checkbox" class="form-check-input" data-department-checkbox value="{{ $department }}">
                                            <span>{{ $department }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" class="form-control" id="editUserDepartment" name="department" data-department-input>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editUserJobTitle">Job Title</label>
                        <input type="text" class="form-control" id="editUserJobTitle" name="job_title" placeholder="e.g. Faculty">
                    </div>
                    <div class="mb-3">
                        @php
                            $editRolePlaceholder = '-- Select Role --';
                        @endphp
                        <label class="form-label" for="editUserRole">Role</label>
                        <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown id="editUserRoleDropdown">
                            <button class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-dropdown-toggle>
                                <span class="searchable-dropdown-label is-placeholder"
                                      data-dropdown-label
                                      data-placeholder-text="{{ $editRolePlaceholder }}">{{ $editRolePlaceholder }}</span>
                                <i class="bx bx-chevron-down fs-5"></i>
                            </button>
                            <div class="dropdown-menu p-2">
                                <input type="text" class="form-control searchable-dropdown-search" placeholder="Search..." data-dropdown-search>
                                <div class="searchable-dropdown-list searchable-dropdown-list--grid" data-dropdown-list>
                                    @foreach ($roleOptions as $option)
                                        <button type="button"
                                                class="dropdown-item"
                                                data-dropdown-option
                                                data-option-value="{{ $option['value'] }}"
                                                data-option-label="{{ $option['label'] }}"
                                                data-option-filter="{{ strtolower($option['label']) }}">
                                            <span>{{ $option['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="role" id="editUserRole">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Level</label>
                                    <div class="dropdown w-100 access-multiselect" data-access-multiselect>
                            <button class="access-multiselect-toggle w-100 d-flex align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-auto-close="outside">
                                <div class="access-multiselect-content flex-grow-1">
                                    <div class="access-multiselect-chips" data-access-selected></div>
                                    <span class="access-multiselect-placeholder" data-access-placeholder>-- Select Access Level --</span>
                                </div>
                                <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="access-multiselect-search">
                                    <input type="text" class="form-control" placeholder="Search access levels..." data-access-search>
                                </div>
                                <div class="access-multiselect-list" data-access-list>
                                    @foreach ($accessLevelOptions as $option)
                                        <label class="access-multiselect-option"
                                               data-access-option
                                               data-value="{{ $option['value'] }}"
                                               data-label="{{ $option['label'] }}"
                                               data-search="{{ strtolower($option['label']) }}">
                                            <input type="checkbox" class="form-check-input" data-access-checkbox value="{{ $option['value'] }}">
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div data-access-inputs class="d-none"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-user-primary evaluation-modal-btn" data-default-text="Save Changes" data-loading-text="Saving...">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- Bulk Access Level Modal --}}
    <div class="modal fade" id="userBulkAccessModal" tabindex="-1" aria-labelledby="userBulkAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="userBulkAccessModalLabel">Set Access Level</h5>
                        <small class="text-white-50">Update <span id="userBulkAccessCount">0</span> selected user(s)</small>
                    </div>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">Ã—</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <div class="mb-3">
                        <label class="form-label">Preset</label>
                        <div class="access-preset-grid">
                            <button type="button" class="access-preset-btn" data-access-preset="faculty">
                                <span class="access-preset-title">Faculty Basic</span>
                                <span class="access-preset-caption">Allow faculty to view or answer forms.</span>
                            </button>
                            <button type="button" class="access-preset-btn" data-access-preset="head">
                                <span class="access-preset-title">Dean / Head</span>
                                <span class="access-preset-caption">Department reports, evaluations, QR links.</span>
                            </button>
                            <button type="button" class="access-preset-btn" data-access-preset="admin">
                                <span class="access-preset-title">Admin Full</span>
                                <span class="access-preset-caption">Every available access level.</span>
                            </button>
                            <button type="button" class="access-preset-btn" data-access-preset="forms">
                                <span class="access-preset-title">Forms Only</span>
                                <span class="access-preset-caption">Only View/Answer Forms.</span>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="bulkAccessMode">Update Mode</label>
                        <select class="form-select" id="bulkAccessMode">
                            <option value="replace">Replace existing access</option>
                            <option value="add">Add to existing access</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Access Level</label>
                        <div class="dropdown w-100 access-multiselect" data-access-multiselect id="bulkAccessMultiselect">
                            <button class="access-multiselect-toggle w-100 d-flex align-items-center text-start"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-display="static"
                                    data-bs-auto-close="outside">
                                <div class="access-multiselect-content flex-grow-1">
                                    <div class="access-multiselect-chips" data-access-selected></div>
                                    <span class="access-multiselect-placeholder" data-access-placeholder>-- Select Access Level --</span>
                                </div>
                                <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                            </button>
                            <div class="dropdown-menu p-3">
                                <div class="access-multiselect-search">
                                    <input type="text" class="form-control" placeholder="Search access levels..." data-access-search>
                                </div>
                                <div class="access-multiselect-list" data-access-list>
                                    @foreach ($accessLevelOptions as $option)
                                        <label class="access-multiselect-option"
                                               data-access-option
                                               data-value="{{ $option['value'] }}"
                                               data-label="{{ $option['label'] }}"
                                               data-search="{{ strtolower($option['label']) }}">
                                            <input type="checkbox" class="form-check-input" data-access-checkbox value="{{ $option['value'] }}">
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div data-access-inputs class="d-none"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-user-primary evaluation-modal-btn" id="confirmUserBulkAccessBtn" data-default-text="Apply Access" data-loading-text="Applying...">Apply Access</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Confirmation Modal --}}
    <div class="modal fade" id="userBulkDeleteModal" tabindex="-1" aria-labelledby="userBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="userBulkDeleteModalLabel">Delete Selected Users</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="userBulkDeleteCount">0</span> user(s). You can restore them later from Deleted Users. Continue?</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmUserBulkDeleteBtn" data-default-text="Delete Selected" data-loading-text="Deleting...">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete User Modal --}}
    <div class="modal fade" id="userDeleteModal" tabindex="-1" aria-labelledby="userDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="userDeleteModalLabel">Delete User</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="userDeleteName">this user</span>? You can restore it later from Deleted Users.</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmUserDeleteBtn" data-default-text="Delete" data-loading-text="Deleting...">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Deleted Users Modal --}}
    <div class="modal fade" id="userDeletedModal" tabindex="-1" aria-labelledby="userDeletedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable evaluation-modal-dialog">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="userDeletedModalLabel">Deleted Users</h5>
                        <small class="text-muted">Restore soft-deleted user accounts when needed.</small>
                    </div>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <div id="userDeletedAlert"></div>
                    <div id="userDeletedLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading deleted users...</span>
                        </div>
                        <p class="text-muted mt-3 mb-0">Loading deleted users...</p>
                    </div>
                    <div id="userDeletedEmpty" class="text-center py-5 d-none">
                        <i class="fa-solid fa-user-check text-muted mb-3" style="font-size: 2rem;"></i>
                        <h5 class="mb-1">No Deleted Users</h5>
                        <p class="text-muted mb-0">Soft-deleted user accounts will appear here.</p>
                    </div>
                    <div id="userDeletedTableWrap" class="table-responsive d-none">
                        <table class="table align-middle mb-0 evaluation-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Job Title</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Deleted At</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="userDeletedTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('components.table-controller-script')
    <script>
        const PILL_PALETTES = {
            purple: [{ bg: '#e4c7ff', color: '#4c1d95' }],
            blue: [{ bg: '#d3e2ff', color: '#1d4ed8' }],
            green: [{ bg: '#d1f9e0', color: '#047857' }],
            gray: [{ bg: '#e3e8f1', color: '#475569' }],
        };
        const ACCESS_LEVEL_VALUES = [
            'View All Reports',
            'View Department Reports',
            'Manage Faculties',
            'Manage Courses',
            'Manage Schedules',
            'Manage Evaluations',
            'Manage Evaluation QR/Link',
            'View/Answer Forms',
        ];
        const ACCESS_LEVEL_VIEW_ALL = 'View All Reports';
        const ACCESS_LEVEL_VIEW_DEPT = 'View Department Reports';
        const ACCESS_LEVEL_EVALUATIONS = 'Manage Evaluations';
        const ACCESS_LEVEL_EVAL_QR = 'Manage Evaluation QR/Link';
        const ACCESS_LEVEL_FORMS = 'View/Answer Forms';
        const ROLE_ADMIN = 'Admin';
        const ROLE_FACULTY = 'Faculty';
        const ROLE_STUDENT = 'Student';
        const ACCESS_PRESETS = {
            faculty: [ACCESS_LEVEL_FORMS],
            head: [ACCESS_LEVEL_VIEW_DEPT, ACCESS_LEVEL_EVALUATIONS, ACCESS_LEVEL_EVAL_QR],
            admin: ACCESS_LEVEL_VALUES,
            forms: [ACCESS_LEVEL_FORMS],
        };

        const userFilters = {
            department: 'all',
            job: 'all',
            role: 'all',
            status: 'all',
        };
        let userFilterToggleEl = null;
        const userSelection = new Set();
        let userBulkDeleteModalInstance = null;
        let userBulkAccessModalInstance = null;
        let userEditModalInstance = null;
        let userDeleteModalInstance = null;
        let pendingUserBulk = null;
        let pendingUserDeleteId = null;
        const userDeleteUrlTemplate = "{{ route('um.users.destroy', ':id') }}";
        const userBulkDeleteUrl = "{{ route('um.users.bulk-destroy') }}";
        const userBulkAccessUrl = "{{ route('um.users.bulk-access') }}";
        const userUpdateUrlTemplate = "{{ route('um.users.update', ':id') }}";
        const userListUrl = "{{ route('um.users.list') }}";
        const userDeletedListUrl = "{{ route('um.users.deleted') }}";
        const userRestoreUrlTemplate = "{{ route('um.users.restore', ':id') }}";
        const usersPayload = @json($userPayload);

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

        class UsersPage {
            constructor({ users }) {
                this.users = Array.isArray(users) ? users : [];
                this.selection = userSelection;
                this.sortState = { key: null, direction: 'asc' };
                this.filters = {
                    department: 'all',
                    job: 'all',
                    role: 'all',
                    status: 'all',
                };
                this.lastSelectionScopeKey = this.getSelectionScopeKey();

                this.tableRoot = document.querySelector('[data-table-id="usersTable"]');
                this.tableBody = document.getElementById('usersTableBody');
                this.selectAllEl = document.getElementById('userSelectAll');
                this.bulkBar = document.getElementById('userBulkBar');
                this.selectedCountEl = document.getElementById('userSelectedCount');
                this.searchInput = document.getElementById('userSearch');
                this.searchClear = document.getElementById('userSearchClear');
                this.lengthSelect = document.getElementById('userRowsPerPage');
                this.infoEl = this.tableRoot?.querySelector('[data-table-info]');
                this.paginationEl = this.tableRoot?.querySelector('[data-table-pagination]');
                this.filterToggle = document.getElementById('userFilterToggle');
                this.filterControls = {
                    department: document.getElementById('filterDepartment'),
                    job: document.getElementById('filterJobTitle'),
                    role: document.getElementById('filterRole'),
                    status: document.getElementById('filterStatus'),
                };
                this.filterResetBtn = document.getElementById('userFilterReset');
                this.sortHeaders = Array.from(document.querySelectorAll('#usersTable thead th[data-sort-key]'));
                this.controller = null;
                this.currentPage = 1;
                this.searchTerm = '';
                this.rowsPerPage = this.parseRowsPerPage(this.lengthSelect?.value || '10');
                this.lastFilteredUsers = [];
                this.meta = { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
                this.requestToken = 0;
                this.searchDebounce = null;

                this.bindBaseEvents();
                this.initSorting();
                this.initSearchInput();
                this.initPaginationControls();
                this.initFilters();
                this.renderTable();
            }

            bindBaseEvents() {
                if (this.selectAllEl) {
                    this.selectAllEl.addEventListener('change', (event) => {
                        this.handleSelectAll(event.target.checked);
                    });
                }

                if (this.bulkBar) {
                    this.bulkBar.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-user-bulk-action]');
                        if (!button) {
                            return;
                        }
                        this.handleBulkAction(button.dataset.userBulkAction);
                    });
                }

                if (this.tableRoot) {
                    this.tableRoot.addEventListener('table:updated', () => {
                        const scopeKey = this.getSelectionScopeKey();
                        if (this.selectAllEl?.checked && scopeKey !== this.lastSelectionScopeKey) {
                            this.lastSelectionScopeKey = scopeKey;
                            this.clearSelection();
                            return;
                        }
                        this.lastSelectionScopeKey = scopeKey;
                        this.syncSelectAllState();
                        this.updateBulkBar();
                        this.refreshPillPalettes();
                    });
                }
            }

            initSorting() {
                if (!Array.isArray(this.sortHeaders)) {
                    return;
                }
                this.sortHeaders.forEach((header) => {
                    header.dataset.sortState = 'none';
                    header.addEventListener('click', () => {
                        const sortKey = header.dataset.sortKey;
                        if (!sortKey) {
                            return;
                        }
                        if (this.sortState.key === sortKey) {
                            this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortState.key = sortKey;
                            this.sortState.direction = 'asc';
                        }
                        this.updateSortIndicators();
                        this.currentPage = 1;
                        this.renderTable();
                    });
                });
                this.updateSortIndicators();
            }

            initSearchInput() {
                if (!this.searchInput || !this.searchClear) {
                    return;
                }
                const toggleClear = () => {
                    this.searchTerm = this.searchInput.value.trim().toLowerCase();
                    this.searchClear.classList.toggle('is-visible', this.searchTerm !== '');
                    this.currentPage = 1;
                    window.clearTimeout(this.searchDebounce);
                    this.searchDebounce = window.setTimeout(() => this.renderTable(), 250);
                };
                this.searchInput.addEventListener('input', toggleClear);
                this.searchClear.addEventListener('click', () => {
                    this.searchInput.value = '';
                    toggleClear();
                    this.searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.searchInput.focus();
                });
                toggleClear();
            }

            initPaginationControls() {
                if (this.lengthSelect) {
                    this.lengthSelect.addEventListener('change', () => {
                        this.rowsPerPage = this.parseRowsPerPage(this.lengthSelect.value);
                        this.currentPage = 1;
                        this.renderTable();
                    });
                }

                if (this.paginationEl) {
                    this.paginationEl.addEventListener('click', (event) => {
                        const link = event.target.closest('[data-page]');
                        if (!link || link.closest('.page-item')?.classList.contains('disabled')) {
                            return;
                        }

                        event.preventDefault();
                        const page = parseInt(link.dataset.page, 10);
                        if (!Number.isNaN(page)) {
                            this.goToPage(page);
                        }
                    });
                }

                if (this.tableRoot) {
                    window.tableControllers = window.tableControllers || {};
                    window.tableControllers.usersTable = {
                        refresh: () => this.renderTable(),
                        get searchTerm() {
                            return window.usersPage?.searchTerm || '';
                        },
                        get filteredRows() {
                            return Array.from(document.querySelectorAll('#usersTable tbody tr[data-user-id]'));
                        },
                    };
                }
            }

            initFilters() {
                Object.entries(this.filterControls).forEach(([key, select]) => {
                    if (!select) return;
                    select.addEventListener('change', (event) => {
                        this.filters[key] = this.normaliseFilterValue(event.target.value || 'all');
                        this.currentPage = 1;
                        this.renderTable();
                    });
                });

                if (this.filterResetBtn) {
                    this.filterResetBtn.addEventListener('click', () => {
                        Object.keys(this.filters).forEach((key) => {
                            this.filters[key] = 'all';
                            if (this.filterControls[key]) {
                                this.filterControls[key].value = 'all';
                            }
                        });
                        this.currentPage = 1;
                        this.renderTable();
                    });
                }

                this.updateFilterToggleState();
            }

            updateSortIndicators() {
                this.sortHeaders.forEach((header) => {
                    header.classList.remove('sorted-asc', 'sorted-desc');
                    header.dataset.sortState = 'none';
                });

                if (!this.sortState.key) return;

                this.sortHeaders.forEach((header) => {
                    if (header.dataset.sortKey === this.sortState.key) {
                        const direction = this.sortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
                        header.classList.add(direction);
                        header.dataset.sortState = this.sortState.direction;
                    }
                });
            }

            renderTable() {
                this.fetchUsers();
            }

            fetchUsers() {
                if (!this.tableBody) {
                    return;
                }

                const token = ++this.requestToken;
                this.tableBody.innerHTML = this.buildLoadingRow();

                fetch(`${userListUrl}?${this.buildQueryParams().toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(async (response) => {
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(payload.message || 'Failed to load users.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        if (token !== this.requestToken) {
                            return;
                        }

                        this.users = Array.isArray(payload.data) ? payload.data : [];
                        this.meta = payload.meta || { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };
                        this.currentPage = this.meta.current_page || this.currentPage;
                        this.renderRows();
                    })
                    .catch((error) => {
                        if (token !== this.requestToken) {
                            return;
                        }
                        this.tableBody.innerHTML = this.buildErrorRow(error.message || 'Failed to load users.');
                        this.updatePaginationFromMeta();
                    });
            }

            buildQueryParams() {
                const params = new URLSearchParams();
                params.set('page', this.currentPage);
                params.set('per_page', this.rowsPerPage === Infinity ? 100 : this.rowsPerPage);
                params.set('search', this.searchTerm || '');
                params.set('sort_key', this.sortState.key || 'name');
                params.set('sort_dir', this.sortState.direction || 'asc');
                Object.entries(this.filters).forEach(([key, value]) => {
                    params.set(key, value || 'all');
                });

                return params;
            }

            renderRows() {
                if (!this.tableBody) {
                    return;
                }

                const visibleIds = new Set(this.users.map((user) => String(user.id)));
                Array.from(this.selection).forEach((id) => {
                    if (!visibleIds.has(String(id))) {
                        this.selection.delete(String(id));
                    }
                });

                const tableHtml = this.meta.total === 0
                    ? this.buildSearchEmptyRow().replace('style="display: none;"', '')
                    : this.users.map((user) => this.buildRowHTML(user)).join('');

                this.tableBody.innerHTML = tableHtml;

                this.attachRowEventListeners();
                this.attachRowSelectionHandlers();
                this.syncSelectAllState();
                this.updateBulkBar();
                this.refreshPillPalettes();

                this.updatePaginationFromMeta();
                this.emitTableUpdated(this.meta.total || 0, this.users.length);
                this.updateFilterToggleState();
            }

            refreshPillPalettes() {
                if (!this.tableRoot) {
                    return;
                }
                applyEvaluationPillColors(this.tableRoot);
            }

            getFilteredUsers() {
                const data = this.getSortedUsers();
                return data.filter((user) => this.matchesFilters(user) && this.matchesSearch(user));
            }

            getSortedUsers() {
                const data = Array.isArray(this.users) ? [...this.users] : [];
                const sortKey = this.sortState.key || 'name';
                const multiplier = this.sortState.direction === 'desc' ? -1 : 1;

                return data.sort((a, b) => {
                    const valueA = this.getSortValue(a, sortKey);
                    const valueB = this.getSortValue(b, sortKey);
                    return String(valueA ?? '').localeCompare(String(valueB ?? ''), undefined, { sensitivity: 'base' }) * multiplier;
                });
            }

            getSortValue(user, key) {
                switch (key) {
                    case 'name':
                        return user?.name ?? '';
                    case 'department':
                        return user?.department_label ?? '';
                    case 'job':
                        return user?.job_title ?? '';
                    case 'role':
                        return user?.role ?? '';
                    case 'access':
                        return user?.access_levels_label ?? '';
                    case 'status':
                        return user?.status_label ?? '';
                    default:
                        return '';
                }
            }

            matchesFilters(user) {
                if (!user) return false;

                if (this.filters.department !== 'all') {
                    const departmentValues = Array.isArray(user.departments) ? user.departments : this.parseDepartments(user.department_raw);
                    const normalized = departmentValues.map((value) => this.normaliseFilterValue(value));
                    if (!normalized.includes(this.filters.department)) {
                        return false;
                    }
                }

                if (this.filters.job !== 'all') {
                    const jobValue = this.normaliseFilterValue(user.job_title);
                    if (jobValue !== this.filters.job) {
                        return false;
                    }
                }

                if (this.filters.role !== 'all') {
                    const roleValue = this.normaliseFilterValue(user.role);
                    if (roleValue !== this.filters.role) {
                        return false;
                    }
                }

                if (this.filters.status !== 'all') {
                    const statusValue = this.normaliseFilterValue(user.status_label);
                    if (statusValue !== this.filters.status) {
                        return false;
                    }
                }

                return true;
            }

            matchesSearch(user) {
                if (!this.searchTerm) {
                    return true;
                }

                return [
                    user?.name,
                    user?.email,
                    user?.department_label,
                    user?.job_title,
                    user?.role,
                    user?.access_levels_label,
                    user?.status_label,
                ].join(' ').toLowerCase().includes(this.searchTerm);
            }

            parseRowsPerPage(value) {
                if (!value || value === 'all') {
                    return Infinity;
                }

                const parsed = parseInt(value, 10);
                return Number.isNaN(parsed) ? 10 : Math.max(parsed, 1);
            }

            getTotalPages(totalRows = this.lastFilteredUsers.length) {
                if (this.meta?.last_page) {
                    return Math.max(1, this.meta.last_page);
                }
                if (this.rowsPerPage === Infinity) {
                    return 1;
                }

                return Math.max(1, Math.ceil(totalRows / this.rowsPerPage));
            }

            getPageData(data) {
                if (this.rowsPerPage === Infinity) {
                    return data;
                }

                const start = (this.currentPage - 1) * this.rowsPerPage;
                return data.slice(start, start + this.rowsPerPage);
            }

            goToPage(page) {
                const totalPages = this.getTotalPages();
                const nextPage = Math.min(Math.max(page, 1), totalPages);
                if (nextPage === this.currentPage) {
                    return;
                }

                this.currentPage = nextPage;
                this.renderTable();
            }

            updatePagination(totalRows) {
                this.updateInfo(totalRows);

                if (!this.paginationEl) {
                    return;
                }

                const totalPages = this.getTotalPages(totalRows);
                if (this.rowsPerPage === Infinity || totalPages <= 1 || totalRows === 0) {
                    this.paginationEl.innerHTML = '';
                    this.paginationEl.classList.add('d-none');
                    return;
                }

                this.paginationEl.classList.remove('d-none');
                const createPageItem = (label, page, disabled = false, active = false, isIcon = false) => {
                    const classes = ['page-item'];
                    if (disabled) classes.push('disabled');
                    if (active) classes.push('active');
                    const icon = isIcon ? `<i class="bx ${label}"></i>` : label;

                    return `
                        <li class="${classes.join(' ')}">
                            <a class="page-link" href="#" data-page="${page}">${icon}</a>
                        </li>
                    `;
                };

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);
                const items = [
                    createPageItem('bx-chevron-left', this.currentPage - 1, this.currentPage === 1, false, true),
                ];

                if (startPage > 1) {
                    items.push(createPageItem('1', 1, false, this.currentPage === 1));
                    if (startPage > 2) {
                        items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    items.push(createPageItem(String(page), page, false, page === this.currentPage));
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                    items.push(createPageItem(String(totalPages), totalPages, false, this.currentPage === totalPages));
                }

                items.push(createPageItem('bx-chevron-right', this.currentPage + 1, this.currentPage === totalPages, false, true));
                this.paginationEl.innerHTML = items.join('');
            }

            updatePaginationFromMeta() {
                this.updateInfoFromMeta();

                if (!this.paginationEl) {
                    return;
                }

                const totalRows = this.meta?.total || 0;
                const totalPages = Math.max(1, this.meta?.last_page || 1);
                if (totalPages <= 1 || totalRows === 0) {
                    this.paginationEl.innerHTML = '';
                    this.paginationEl.classList.add('d-none');
                    return;
                }

                this.paginationEl.classList.remove('d-none');
                const createPageItem = (label, page, disabled = false, active = false, isIcon = false) => {
                    const classes = ['page-item'];
                    if (disabled) classes.push('disabled');
                    if (active) classes.push('active');
                    const icon = isIcon ? `<i class="bx ${label}"></i>` : label;

                    return `
                        <li class="${classes.join(' ')}">
                            <a class="page-link" href="#" data-page="${page}">${icon}</a>
                        </li>
                    `;
                };

                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(totalPages, this.currentPage + 2);
                const items = [
                    createPageItem('bx-chevron-left', this.currentPage - 1, this.currentPage === 1, false, true),
                ];

                if (startPage > 1) {
                    items.push(createPageItem('1', 1, false, this.currentPage === 1));
                    if (startPage > 2) {
                        items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    items.push(createPageItem(String(page), page, false, page === this.currentPage));
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        items.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    }
                    items.push(createPageItem(String(totalPages), totalPages, false, this.currentPage === totalPages));
                }

                items.push(createPageItem('bx-chevron-right', this.currentPage + 1, this.currentPage === totalPages, false, true));
                this.paginationEl.innerHTML = items.join('');
            }

            updateInfo(totalRows) {
                if (!this.infoEl) {
                    return;
                }

                const start = totalRows === 0
                    ? 0
                    : this.rowsPerPage === Infinity
                        ? 1
                        : (this.currentPage - 1) * this.rowsPerPage + 1;
                const end = this.rowsPerPage === Infinity
                    ? totalRows
                    : Math.min(this.currentPage * this.rowsPerPage, totalRows);

                this.infoEl.textContent = `Showing ${start} to ${end} of ${totalRows} entries`;
            }

            updateInfoFromMeta() {
                if (!this.infoEl) {
                    return;
                }

                this.infoEl.textContent = `Showing ${this.meta?.from || 0} to ${this.meta?.to || 0} of ${this.meta?.total || 0} entries`;
            }

            emitTableUpdated(totalRows, visibleRows) {
                if (!this.tableRoot) {
                    return;
                }

                this.tableRoot.dispatchEvent(new CustomEvent('table:updated', {
                    detail: {
                        tableId: 'usersTable',
                        total: this.users.length,
                        filtered: totalRows,
                        visible: visibleRows,
                        page: this.currentPage,
                        rowsPerPage: this.rowsPerPage,
                    },
                }));
            }

            buildEmptyStateRow() {
                return `
                    <tr data-empty>
                        <td colspan="9" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-users-gear display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No users yet</h5>
                                <p class="text-muted mb-0">Add or import users to see them listed here.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildLoadingRow() {
                return `
                    <tr data-ignore>
                        <td colspan="9" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                            <p class="text-muted mt-3 mb-0">Loading users...</p>
                        </td>
                    </tr>
                `;
            }

            buildErrorRow(message) {
                return `
                    <tr data-ignore>
                        <td colspan="9" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-triangle-exclamation display-4 text-danger mb-3"></i>
                                <h5 class="mb-2">Unable to load users</h5>
                                <p class="text-muted mb-0">${this.escapeHtml(message)}</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildSearchEmptyRow() {
                return `
                    <tr data-empty-search style="display: none;">
                        <td colspan="9" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-magnifying-glass display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No results found</h5>
                                <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildRowHTML(user) {
                const rowId = String(user.id ?? '');
                const isSelected = this.selection.has(rowId);
                const name = this.formatDisplayText(user.name || 'Unnamed User');
                const emailRaw = user.email || '';
                const email = emailRaw ? this.escapeHtml(emailRaw) : '—';
                const departmentList = Array.isArray(user.departments) && user.departments.length
                    ? user.departments
                    : ['—'];
                const jobTitle = this.formatDisplayText(user.job_title || '—');
                const roleValue = this.formatDisplayText(user.role || 'User');
                const accessLevels = Array.isArray(user.access_levels) ? user.access_levels : [];
                const accessLabel = accessLevels.length ? accessLevels.join(', ') : '—';
                const statusLabel = user.status_label || 'Inactive';
                const statusSlug = String(user.status_slug || '').toLowerCase() || 'inactive';
                const statusClass = user.status_class || (statusSlug === 'active' ? 'active' : 'inactive');
                const statusToggleLabel = statusSlug === 'active' ? 'Deactivate' : 'Activate';
                const searchTerms = [
                    name,
                    emailRaw,
                    user.department_label || '',
                    jobTitle,
                    roleValue,
                    accessLabel,
                    statusLabel,
                ].join(' ').toLowerCase();
                const updateUrl = rowId ? userUpdateUrlTemplate.replace(':id', rowId) : '#';

                return `
                    <tr class="table-row"
                        data-search="${this.escapeAttribute(searchTerms)}"
                        data-user-id="${this.escapeAttribute(rowId)}"
                        data-sort-name="${this.escapeAttribute(String(user.name || '').toLowerCase())}"
                        data-sort-department="${this.escapeAttribute(String(user.department_label || '').toLowerCase())}"
                        data-sort-job="${this.escapeAttribute(String(jobTitle).toLowerCase())}"
                        data-sort-role="${this.escapeAttribute(String(roleValue).toLowerCase())}"
                        data-sort-access="${this.escapeAttribute(String(accessLabel).toLowerCase())}"
                        data-sort-status="${this.escapeAttribute(String(statusLabel).toLowerCase())}"
                        data-user-name="${this.escapeAttribute(user.name || '')}"
                        data-user-email="${this.escapeAttribute(emailRaw)}"
                        data-user-department="${this.escapeAttribute(user.department_raw || '')}"
                        data-user-job="${this.escapeAttribute(jobTitle)}"
                        data-user-role="${this.escapeAttribute(roleValue)}"
                        data-user-access-level="${this.escapeAttribute(JSON.stringify(accessLevels))}"
                        data-user-status="${this.escapeAttribute(user.status_value || statusLabel)}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input evaluation-checkbox" data-user-row-select ${isSelected ? 'checked' : ''}>
                        </td>
                        <td>
                            <div class="table-cell-stack is-wide" title="${this.escapeAttribute(name)}">
                                <span class="table-text-truncate text-dark">${this.escapeHtml(name)}</span>
                            </div>
                        </td>
                        <td>
                            <span class="table-text-truncate">${email}</span>
                        </td>
                        <td class="evaluation-pill-cell">
                            <div class="evaluation-pill-group">
                                ${departmentList.map((department) => {
                                    const label = this.formatDisplayText(department);
                                    const pillValue = String(label || 'n/a').toLowerCase();
                                    return `
                                        <span class="evaluation-pill"
                                              data-pill-palette="purple"
                                              data-pill-value="${this.escapeAttribute(pillValue)}">
                                            ${this.escapeHtml(label || '—')}
                                        </span>
                                    `;
                                }).join('')}
                            </div>
                        </td>
                        <td>
                            <span class="table-text-truncate fw-normal">${this.escapeHtml(jobTitle)}</span>
                        </td>
                        <td>
                            <span class="evaluation-pill"
                                  data-pill-palette="blue"
                                  data-pill-value="${this.escapeAttribute(String(roleValue).toLowerCase())}">
                                ${this.escapeHtml(roleValue)}
                            </span>
                        </td>
                        <td>
                            ${accessLevels.length === 0
                                ? '<span class="text-muted">—</span>'
                                : `
                                    <div class="d-flex flex-wrap gap-1">
                                        ${accessLevels.map((accessLevel) => `
                                            <span class="evaluation-pill"
                                                  data-pill-palette="gray"
                                                  data-pill-value="${this.escapeAttribute(String(accessLevel).toLowerCase())}">
                                                ${this.escapeHtml(accessLevel)}
                                            </span>
                                        `).join('')}
                                    </div>
                                `
                            }
                        </td>
                        <td>
                            <span class="evaluation-status evaluation-status--${this.escapeAttribute(statusClass)}">
                                ${this.escapeHtml(statusLabel)}
                            </span>
                        </td>
                        <td class="actions-cell">
                            <div class="dropdown evaluation-actions">
                                <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item" data-user-action="edit" data-user-id="${this.escapeAttribute(rowId)}" data-update-url="${this.escapeAttribute(updateUrl)}">Edit</button>
                                    </li>
                                    <li>
                                        <button type="button"
                                                class="dropdown-item"
                                                data-user-toggle
                                                data-user-id="${this.escapeAttribute(rowId)}"
                                                data-toggle-url="${this.escapeAttribute(updateUrl)}"
                                                data-current-status="${this.escapeAttribute(statusSlug)}"
                                                data-default-text="${this.escapeAttribute(statusToggleLabel)}">
                                            ${this.escapeHtml(statusToggleLabel)}
                                        </button>
                                    </li>
                                    ${user.is_current_user ? `
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="dropdown-info-message px-3 py-2">
                                            <span>Cannot delete while users are<br>assigned.</span>
                                        </li>
                                    ` : `
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-user-action="delete" data-user-id="${this.escapeAttribute(rowId)}">Delete</button>
                                        </li>
                                    `}
                                </ul>
                            </div>
                        </td>
                    </tr>
                `;
            }

            attachRowEventListeners() {
                if (!this.tableBody) {
                    return;
                }

                const form = document.getElementById('editUserForm');
                this.tableBody.querySelectorAll('[data-user-action="edit"]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (!form) return;
                        const userId = button.dataset.userId;
                        const row = this.tableBody.querySelector(`tr[data-user-id="${userId}"]`);
                        if (!row) return;
                        populateUserEditForm(form, row, button.dataset.updateUrl || form.getAttribute('action') || '');
                        userEditModalInstance?.show();
                    });
                });

                this.tableBody.querySelectorAll('[data-user-action="delete"]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const userId = button.dataset.userId;
                        const row = this.tableBody.querySelector(`tr[data-user-id="${userId}"]`);
                        if (!row) return;
                        openUserDeleteModal(userId, row);
                    });
                });

                this.tableBody.querySelectorAll('[data-user-toggle]').forEach((button) => {
                    button.addEventListener('click', () => {
                        handleUserStatusToggle(button);
                    });
                });
            }

            attachRowSelectionHandlers() {
                if (!this.tableBody) {
                    return;
                }
                this.tableBody.querySelectorAll('[data-user-row-select]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const row = checkbox.closest('tr');
                        const id = row?.dataset.userId;
                        if (!id) return;
                        if (checkbox.checked) {
                            this.selection.add(String(id));
                        } else {
                            this.selection.delete(String(id));
                        }
                        row.classList.toggle('is-selected', checkbox.checked);
                        this.syncSelectAllState();
                        this.updateBulkBar();
                    });
                });
            }

            getSelectableRows() {
                if (!this.tableBody) {
                    return [];
                }
                return Array.from(this.tableBody.querySelectorAll('tr[data-user-id]'));
            }

            getSelectionScopeKey() {
                const filterKey = JSON.stringify(this.filters);
                return `${this.searchTerm}|${filterKey}`;
            }

            handleSelectAll(shouldSelect) {
                const rows = this.getSelectableRows();
                rows.forEach((row) => {
                    const checkbox = row.querySelector('[data-user-row-select]');
                    if (!checkbox) {
                        return;
                    }
                    checkbox.checked = shouldSelect;
                    if (shouldSelect) {
                        this.selection.add(String(row.dataset.userId));
                    } else {
                        this.selection.delete(String(row.dataset.userId));
                    }
                    row.classList.toggle('is-selected', shouldSelect);
                });

                this.syncSelectAllState();
                this.updateBulkBar();
            }

            handleBulkAction(action) {
                if (!action) return;
                if (action === 'clear') {
                    this.clearSelection();
                    return;
                }
                if (action === 'delete') {
                    if (this.selection.size === 0) return;
                    pendingUserBulk = { ids: Array.from(this.selection) };
                    const countEl = document.getElementById('userBulkDeleteCount');
                    if (countEl) {
                        countEl.textContent = pendingUserBulk.ids.length;
                    }
                    userBulkDeleteModalInstance?.show();
                }
                if (action === 'access') {
                    openUserBulkAccessModal();
                }
            }

            clearSelection() {
                this.selection.clear();
                this.tableBody?.querySelectorAll('tr[data-user-id]').forEach((row) => {
                    row.classList.remove('is-selected');
                    const checkbox = row.querySelector('[data-user-row-select]');
                    if (checkbox) checkbox.checked = false;
                });
                if (this.selectAllEl) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                }
                this.updateBulkBar();
                this.renderTable();
            }

            syncSelectAllState() {
                const rows = this.getSelectableRows();
                if (!this.selectAllEl) {
                    return;
                }
                if (rows.length === 0) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                    return;
                }
                const visibleSelected = rows.filter((row) => this.selection.has(String(row.dataset.userId))).length;
                if (visibleSelected === 0) {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = false;
                } else if (visibleSelected === rows.length) {
                    this.selectAllEl.checked = true;
                    this.selectAllEl.indeterminate = false;
                } else {
                    this.selectAllEl.checked = false;
                    this.selectAllEl.indeterminate = true;
                }
            }

            updateBulkBar() {
                const count = this.selection.size;
                if (this.selectedCountEl) {
                    this.selectedCountEl.textContent = `${count} Selected`;
                }
                if (this.bulkBar) {
                    this.bulkBar.classList.toggle('d-none', count === 0);
                }
            }

            updateUserStatus(userId, payload) {
                if (!userId) return;
                const targetId = String(userId);
                const user = this.users.find((entry) => String(entry.id) === targetId);
                if (!user) {
                    return;
                }
                const statusSlug = String(payload.status || '').toLowerCase()
                    || (payload.status_label === 'Active' ? 'active' : 'inactive');
                const statusLabel = payload.status_label || (statusSlug === 'active' ? 'Active' : 'Inactive');
                const statusValue = payload.status_value || statusLabel;
                user.status_slug = statusSlug;
                user.status_label = statusLabel;
                user.status_value = statusValue;
                user.status_class = statusSlug === 'active' ? 'active' : 'inactive';
                this.renderTable();
            }

            removeUsers(ids = []) {
                if (!Array.isArray(ids) || ids.length === 0) return;
                const idSet = new Set(ids.map((id) => String(id)));
                this.users = this.users.filter((user) => !idSet.has(String(user.id)));
                idSet.forEach((id) => this.selection.delete(String(id)));
                this.renderTable();
            }

            updateFilterToggleState() {
                if (!this.filterToggle) return;
                const isActive = Object.values(this.filters).some((value) => value !== 'all');
                this.filterToggle.classList.toggle('is-active', isActive);
            }

            parseDepartments(value) {
                if (value === null || value === undefined) {
                    return [];
                }
                const text = String(value).trim();
                if (text === '' || text === '—') {
                    return [];
                }
                return text
                    .split(',')
                    .map((item) => item.trim())
                    .filter((item) => item !== '');
            }

            normaliseFilterValue(value) {
                return String(value ?? '').trim().toLowerCase();
            }

            formatDisplayText(value) {
                if (value === null || value === undefined) {
                    return '';
                }
                return String(value).trim();
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            escapeAttribute(value) {
                return this.escapeHtml(value).replace(/`/g, '&#096;');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            initUserSearchableDropdowns();
            initAccessLevelMultiselects();
            initDepartmentMultiselects();
            initRoleAccessBindings();
            initAddUserFormLoading();
            window.showTemporaryToast = window.showTemporaryToast || function (message, type = 'success') {
                if (window.dmToast && typeof window.dmToast.show === 'function') {
                    window.dmToast.show({ type, message });
                    return;
                }
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 280px;';
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            };

            const bulkModalEl = document.getElementById('userBulkDeleteModal');
            if (bulkModalEl && typeof bootstrap !== 'undefined') {
                userBulkDeleteModalInstance = new bootstrap.Modal(bulkModalEl);
                bulkModalEl.addEventListener('hidden.bs.modal', () => {
                    pendingUserBulk = null;
                });
            }

            const bulkAccessModalEl = document.getElementById('userBulkAccessModal');
            if (bulkAccessModalEl && typeof bootstrap !== 'undefined') {
                userBulkAccessModalInstance = new bootstrap.Modal(bulkAccessModalEl);
                bulkAccessModalEl.addEventListener('hidden.bs.modal', () => {
                    clearAccessPresetState();
                });
            }

            const deleteModalEl = document.getElementById('userDeleteModal');
            if (deleteModalEl && typeof bootstrap !== 'undefined') {
                userDeleteModalInstance = new bootstrap.Modal(deleteModalEl);
                deleteModalEl.addEventListener('hidden.bs.modal', () => {
                    pendingUserDeleteId = null;
                });
            }

            initDeletedUsersModal();

            const editModalEl = document.getElementById('editUserModal');
            if (editModalEl && typeof bootstrap !== 'undefined') {
                userEditModalInstance = bootstrap.Modal.getOrCreateInstance(editModalEl);
            }

            const confirmBulkDeleteBtn = document.getElementById('confirmUserBulkDeleteBtn');
            if (confirmBulkDeleteBtn) {
                confirmBulkDeleteBtn.addEventListener('click', () => handleUserBulkDeleteConfirm(confirmBulkDeleteBtn));
            }

            initBulkAccessControls();

            const confirmDeleteBtn = document.getElementById('confirmUserDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', () => handleUserDeleteConfirm(confirmDeleteBtn));
            }

            window.usersPage = new UsersPage({ users: usersPayload });
        });

        function initAddUserFormLoading() {
            const form = document.getElementById('addUserForm');
            if (!form) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            form.addEventListener('submit', () => {
                if (submitBtn.disabled) return;
                const defaultText = submitBtn.dataset.defaultText || submitBtn.textContent.trim();
                const loadingText = submitBtn.dataset.loadingText || 'Saving...';
                submitBtn.dataset.defaultText = defaultText;
                submitBtn.textContent = loadingText;
                submitBtn.disabled = true;
            });
        }

        function initUserTableEnhancements(controllerRoot, controller) {
            initUserSelectionControls(controllerRoot, controller);
            initUserSorting(controller);
        }

        function initUserSelectionControls(controllerRoot, controller) {
            const selectAllEl = document.getElementById('userSelectAll');
            const bulkBar = document.getElementById('userBulkBar');
            const selectedCountEl = document.getElementById('userSelectedCount');
            const tableBody = document.querySelector('#usersTable tbody');
            if (!selectAllEl || !tableBody) return;

            let lastScopeKey = null;
            const getSelectionScopeKey = () => {
                const searchTerm = controller?.searchTerm ?? '';
                const filterKey = JSON.stringify(userFilters);
                return `${searchTerm}|${filterKey}`;
            };
            const getSelectableRows = () => {
                if (controller && Array.isArray(controller.filteredRows)) {
                    return controller.filteredRows;
                }
                return Array.from(document.querySelectorAll('#usersTable tbody tr[data-user-id]:not([data-ignore])'));
            };
            lastScopeKey = getSelectionScopeKey();

            const applySelectionToRow = (row, isSelected) => {
                const checkbox = row.querySelector('[data-user-row-select]');
                if (checkbox) {
                    checkbox.checked = isSelected;
                }
                row.classList.toggle('is-selected', isSelected);
            };

            const syncSelectAll = () => {
                const rows = getSelectableRows();
                if (rows.length === 0) {
                    selectAllEl.checked = false;
                    selectAllEl.indeterminate = false;
                    updateUserBulkBarDisplay(bulkBar, selectedCountEl);
                    return;
                }

                const visibleSelected = rows.filter((row) => userSelection.has(row.dataset.userId)).length;
                if (visibleSelected === 0) {
                    selectAllEl.checked = false;
                    selectAllEl.indeterminate = false;
                } else if (visibleSelected === rows.length) {
                    selectAllEl.checked = true;
                    selectAllEl.indeterminate = false;
                } else {
                    selectAllEl.checked = false;
                    selectAllEl.indeterminate = true;
                }

                updateUserBulkBarDisplay(bulkBar, selectedCountEl);
            };

            const toggleRowSelection = (row, shouldSelect) => {
                const id = row.dataset.userId;
                if (!id) return;
                if (shouldSelect) {
                    userSelection.add(id);
                } else {
                    userSelection.delete(id);
                }
                applySelectionToRow(row, shouldSelect);
                updateUserBulkBarDisplay(bulkBar, selectedCountEl);
            };

            selectAllEl.addEventListener('change', () => {
                const shouldSelect = selectAllEl.checked;
                getSelectableRows().forEach((row) => toggleRowSelection(row, shouldSelect));
                syncSelectAll();
                controller?.refresh?.();
            });

            tableBody.addEventListener('change', (event) => {
                const checkbox = event.target.closest('[data-user-row-select]');
                if (!checkbox) return;
                const row = checkbox.closest('tr[data-user-id]');
                if (!row) return;
                toggleRowSelection(row, checkbox.checked);
                syncSelectAll();
            });

            controllerRoot.addEventListener('table:updated', () => {
                const scopeKey = getSelectionScopeKey();
                if (selectAllEl.checked && scopeKey !== lastScopeKey) {
                    lastScopeKey = scopeKey;
                    clearUserSelection(controller);
                    return;
                }
                lastScopeKey = scopeKey;
                document.querySelectorAll('#usersTable tbody tr[data-user-id]').forEach((row) => {
                    const isSelected = userSelection.has(row.dataset.userId);
                    applySelectionToRow(row, isSelected);
                });
                syncSelectAll();
            });

            if (bulkBar) {
                bulkBar.addEventListener('click', (event) => {
                    const actionBtn = event.target.closest('[data-user-bulk-action]');
                    if (!actionBtn) return;
                    handleUserBulkAction(actionBtn.dataset.userBulkAction, controller);
                });
            }

            syncSelectAll();
        }

        function initUserEditModal() {
            const modalEl = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');
            if (!modalEl || !form || typeof bootstrap === 'undefined') {
                return;
            }

            userEditModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

            const bindButtons = () => {
                document.querySelectorAll('[data-user-action="edit"]').forEach((button) => {
                    if (button.dataset.bound === 'true') return;
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const userId = button.dataset.userId;
                        const row = document.querySelector(`#usersTable tbody tr[data-user-id="${userId}"]`);
                        if (!row) return;
                        populateUserEditForm(form, row, button.dataset.updateUrl || form.getAttribute('action') || '');
                        userEditModalInstance.show();
                    });
                });

                document.querySelectorAll('[data-user-action="delete"]').forEach((button) => {
                    if (button.dataset.bound === 'true') return;
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const userId = button.dataset.userId;
                        const row = document.querySelector(`#usersTable tbody tr[data-user-id="${userId}"]`);
                        if (!row) return;
                        openUserDeleteModal(userId, row);
                    });
                });

                document.querySelectorAll('[data-user-toggle]').forEach((button) => {
                    if (button.dataset.toggleBound === 'true') return;
                    button.dataset.toggleBound = 'true';
                    button.addEventListener('click', () => {
                        handleUserStatusToggle(button);
                    });
                });
            };

            bindButtons();

            const tableRoot = document.querySelector('[data-table-id="usersTable"]');
            tableRoot?.addEventListener('table:updated', bindButtons);
        }

        const userSortState = { key: null, direction: 'asc' };

        function initUserSorting(controller) {
            const headers = document.querySelectorAll('#usersTable thead th[data-sort-key]');
            headers.forEach((header) => {
                header.dataset.sortState = 'none';
                header.addEventListener('click', () => handleUserSort(header, headers, controller));
            });
            updateUserSortIndicators(headers);
        }

        function handleUserSort(activeHeader, headers, controller) {
            const sortKey = activeHeader.dataset.sortKey;
            const sortType = activeHeader.dataset.sortType || 'string';

            if (userSortState.key === sortKey) {
                userSortState.direction = userSortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                userSortState.key = sortKey;
                userSortState.direction = 'asc';
            }

            const rows = Array.from(document.querySelectorAll('#usersTable tbody tr[data-user-id]'));
            rows.sort((rowA, rowB) => {
                const key = sortKey.charAt(0).toUpperCase() + sortKey.slice(1);
                const valueA = rowA.dataset[`sort${key}`] || '';
                const valueB = rowB.dataset[`sort${key}`] || '';

                if (sortType === 'number') {
                    return (Number(valueA) - Number(valueB)) * (userSortState.direction === 'asc' ? 1 : -1);
                }

                const compare = String(valueA).localeCompare(String(valueB));
                return compare * (userSortState.direction === 'asc' ? 1 : -1);
            });

            const tbody = document.querySelector('#usersTable tbody');
            rows.forEach((row) => tbody.appendChild(row));

            updateUserSortIndicators(headers);
            controller?.refresh?.();
        }

        function updateUserSortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';
            });

            if (!userSortState.key) return;

            headers.forEach((header) => {
                if (header.dataset.sortKey === userSortState.key) {
                    header.classList.add(userSortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
                    header.dataset.sortState = userSortState.direction;
                }
            });
        }

        function initUserSearchableDropdowns() {
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
                        const filter = option.dataset.optionFilter || option.dataset.optionLabel || option.textContent;
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
                    const labelText = option.dataset.optionLabel
                        ?? option.querySelector('span')?.textContent?.trim()
                        ?? option.textContent.trim()
                        ?? placeholderText;

                    if (hiddenInput) {
                        hiddenInput.value = value;
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
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

        function initAccessLevelMultiselects() {
            const multiselects = document.querySelectorAll('[data-access-multiselect]');
            multiselects.forEach((multiselect) => {
                if (multiselect.accessMultiselectApi) {
                    return;
                }
                setupAccessLevelMultiselect(multiselect);
            });
        }

        function initDepartmentMultiselects() {
            const multiselects = document.querySelectorAll('[data-department-multiselect]');
            multiselects.forEach((multiselect) => {
                if (multiselect.departmentMultiselectApi) {
                    return;
                }
                setupDepartmentMultiselect(multiselect);
            });
        }

        function setupDepartmentMultiselect(multiselect) {
            const placeholder = multiselect.querySelector('[data-department-placeholder]');
            const chipsContainer = multiselect.querySelector('[data-department-selected]');
            const hiddenInput = multiselect.querySelector('[data-department-input]');
            const searchInput = multiselect.querySelector('[data-department-search]');
            const listContainer = multiselect.querySelector('[data-department-list]');
            const optionElements = Array.from(multiselect.querySelectorAll('[data-department-option]'));

            const options = [];
            const selected = new Set();

            const normalizeValues = (value) => {
                if (Array.isArray(value)) {
                    return value.map((entry) => String(entry ?? '').trim()).filter((entry) => entry !== '');
                }
                return String(value ?? '')
                    .split(',')
                    .map((entry) => entry.trim())
                    .filter((entry) => entry !== '');
            };

            const ensureOption = (value) => {
                const normalized = String(value ?? '').trim();
                if (!normalized || options.some((option) => option.value === normalized)) {
                    return;
                }
                if (!listContainer) {
                    return;
                }
                const label = document.createElement('label');
                label.className = 'department-multiselect-option';
                label.dataset.departmentOption = '';
                label.dataset.value = normalized;
                label.dataset.label = normalized;
                label.dataset.search = normalized.toLowerCase();

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input';
                checkbox.dataset.departmentCheckbox = '';
                checkbox.value = normalized;

                const span = document.createElement('span');
                span.textContent = normalized;

                label.appendChild(checkbox);
                label.appendChild(span);
                listContainer.appendChild(label);

                registerOption(label);
            };

            const syncHiddenInput = () => {
                if (hiddenInput) {
                    hiddenInput.value = Array.from(selected).join(', ');
                }
            };

            const syncChips = () => {
                if (!chipsContainer) {
                    return;
                }
                chipsContainer.innerHTML = '';
                selected.forEach((value) => {
                    const chip = document.createElement('span');
                    chip.className = 'department-chip';
                    chip.innerHTML = `
                        <span>${value}</span>
                        <button type="button" class="department-chip-remove" data-department-remove="${value}" aria-label="Remove ${value}">&times;</button>
                    `;
                    chipsContainer.appendChild(chip);
                });
                if (placeholder) {
                    placeholder.classList.toggle('d-none', selected.size > 0);
                }
            };

            const syncCheckboxes = () => {
                options.forEach(({ value, checkbox, element }) => {
                    const isSelected = selected.has(value);
                    if (checkbox) {
                        checkbox.checked = isSelected;
                    }
                    element.classList.toggle('is-selected', isSelected);
                });
            };

            const applySearchFilter = () => {
                const term = (searchInput?.value || '').trim().toLowerCase();
                const hasTerm = term !== '';
                options.forEach(({ element, searchValue }) => {
                    const matches = !hasTerm || (searchValue || '').includes(term);
                    element.classList.toggle('d-none', !matches);
                });
            };

            const setSelectedValues = (values) => {
                const normalized = normalizeValues(values);
                selected.clear();
                normalized.forEach((value) => {
                    if (value) {
                        ensureOption(value);
                        selected.add(value);
                    }
                });
                syncHiddenInput();
                syncChips();
                syncCheckboxes();
            };

            const toggleValue = (value) => {
                if (!value) {
                    return;
                }
                const key = String(value);
                if (selected.has(key)) {
                    selected.delete(key);
                } else {
                    ensureOption(key);
                    selected.add(key);
                }
                syncHiddenInput();
                syncChips();
                syncCheckboxes();
            };

            const handleChipRemoval = (event) => {
                const removeBtn = event.target.closest('[data-department-remove]');
                if (!removeBtn) {
                    return;
                }
                event.preventDefault();
                event.stopImmediatePropagation();
                const value = removeBtn.dataset.departmentRemove;
                if (value && selected.has(value)) {
                    selected.delete(value);
                    syncHiddenInput();
                    syncChips();
                    syncCheckboxes();
                }
            };

            const registerOption = (element) => {
                const value = element.dataset.value ?? '';
                const label = element.dataset.label ?? value;
                const searchValue = element.dataset.search ?? label.toLowerCase();
                const checkbox = element.querySelector('[data-department-checkbox]');
                const option = { element, value, label, searchValue, checkbox };
                options.push(option);

                element.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLInputElement) {
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    toggleValue(value);
                });

                if (checkbox) {
                    checkbox.addEventListener('change', (event) => {
                        event.stopPropagation();
                        if (event.target.checked) {
                            selected.add(String(value));
                        } else {
                            selected.delete(String(value));
                        }
                        syncHiddenInput();
                        syncChips();
                        syncCheckboxes();
                    });
                }
            };

            optionElements.forEach(registerOption);
            chipsContainer?.addEventListener('click', handleChipRemoval);
            chipsContainer?.addEventListener('mousedown', handleChipRemoval);

            if (searchInput) {
                searchInput.addEventListener('input', applySearchFilter);
            }

            multiselect.addEventListener('shown.bs.dropdown', () => {
                if (searchInput) {
                    searchInput.value = '';
                    applySearchFilter();
                    searchInput.focus();
                }
            });

            const parentForm = multiselect.closest('form');
            if (parentForm) {
                parentForm.addEventListener('reset', () => {
                    setTimeout(() => {
                        setSelectedValues([]);
                        if (searchInput) {
                            searchInput.value = '';
                            applySearchFilter();
                        }
                    }, 0);
                });
            }

            applySearchFilter();
            syncChips();
            syncCheckboxes();
            syncHiddenInput();

            multiselect.departmentMultiselectApi = {
                setSelected: setSelectedValues,
                getSelected: () => Array.from(selected),
                reset: () => setSelectedValues([]),
            };
        }

        function initRoleAccessBindings() {
            const addForm = document.getElementById('addUserForm');
            if (addForm) {
                bindRoleAccessToMultiselect(addForm, 'userRole');
            }

            const editForm = document.getElementById('editUserForm');
            if (editForm) {
                bindRoleAccessToMultiselect(editForm, 'editUserRole');
            }
        }

        function bindRoleAccessToMultiselect(form, roleInputId) {
            const roleInput = document.getElementById(roleInputId);
            const multiselect = form?.querySelector('[data-access-multiselect]');
            if (!roleInput || !multiselect) return;

            if (!multiselect.accessMultiselectApi) {
                setupAccessLevelMultiselect(multiselect);
            }

            const applyRules = (force = false) => {
                applyRoleAccessRules(roleInput.value, multiselect, force);
            };

            roleInput.addEventListener('change', () => applyRules(true));
            form.addEventListener('reset', () => {
                setTimeout(() => applyRules(true), 0);
            });
            applyRules(true);
        }

        function setupAccessLevelMultiselect(multiselect) {
            const placeholder = multiselect.querySelector('[data-access-placeholder]');
            const chipsContainer = multiselect.querySelector('[data-access-selected]');
            const inputsContainer = multiselect.querySelector('[data-access-inputs]');
            const searchInput = multiselect.querySelector('[data-access-search]');
            const optionElements = Array.from(multiselect.querySelectorAll('[data-access-option]'));
            const toggleButton = multiselect.querySelector('.access-multiselect-toggle');

            const options = optionElements.map((element) => {
                const value = element.dataset.value ?? '';
                const label = element.dataset.label ?? value;
                const searchValue = element.dataset.search ?? label.toLowerCase();
                const checkbox = element.querySelector('[data-access-checkbox]');
                return { element, value, label, searchValue, checkbox };
            });

            const selected = new Set();
            const state = { disabled: false };

            const updateOptionDisabledStates = () => {
                options.forEach(({ value, checkbox, element }) => {
                    const isDisabled = state.disabled;
                    if (checkbox) {
                        checkbox.disabled = isDisabled;
                    }
                    element.classList.toggle('is-disabled', isDisabled);
                });
            };

            const syncHiddenInputs = () => {
                if (!inputsContainer) {
                    return;
                }
                inputsContainer.innerHTML = '';
                selected.forEach((value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'access_level[]';
                    input.value = value;
                    inputsContainer.appendChild(input);
                });
            };

            const syncChips = () => {
                if (!chipsContainer) {
                    return;
                }
                chipsContainer.innerHTML = '';
                selected.forEach((value) => {
                    const label = options.find((option) => option.value === value)?.label || value;
                    const chip = document.createElement('span');
                    chip.className = 'access-chip';
                    chip.innerHTML = `
                        <span>${label}</span>
                        <button type="button" class="access-chip-remove" data-access-remove="${value}" aria-label="Remove ${label}">&times;</button>
                    `;
                    chipsContainer.appendChild(chip);
                });

                if (placeholder) {
                    placeholder.classList.toggle('d-none', selected.size > 0);
                }
            };

            const syncCheckboxes = () => {
                options.forEach(({ value, checkbox, element }) => {
                    const isSelected = selected.has(value);
                    if (checkbox) {
                        checkbox.checked = isSelected;
                    }
                    element.classList.toggle('is-selected', isSelected);
                });
                updateOptionDisabledStates();
            };

            const setSelectedValues = (values) => {
                const normalized = normaliseAccessLevels(values);
                selected.clear();
                (Array.isArray(normalized) ? normalized : []).forEach((value) => {
                    if (value) {
                        selected.add(String(value));
                    }
                });
                syncHiddenInputs();
                syncChips();
                syncCheckboxes();
            };

            const toggleValue = (value) => {
                if (state.disabled) {
                    return;
                }
                if (!value) {
                    return;
                }
                const key = String(value);
                if (selected.has(key)) {
                    selected.delete(key);
                } else {
                    selected.add(key);
                }
                const normalized = normaliseAccessLevels(Array.from(selected));
                selected.clear();
                normalized.forEach((entry) => selected.add(entry));
                syncHiddenInputs();
                syncChips();
                syncCheckboxes();
            };

            const applySearchFilter = () => {
                const term = (searchInput?.value || '').trim().toLowerCase();
                const hasTerm = term !== '';
                options.forEach(({ element, searchValue }) => {
                    const matches = !hasTerm || (searchValue || '').includes(term);
                    element.classList.toggle('d-none', !matches);
                });
            };

            options.forEach(({ element, value, checkbox }) => {
                element.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLInputElement) {
                        return;
                    }
                    if (checkbox?.disabled) {
                        event.preventDefault();
                        event.stopPropagation();
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    toggleValue(value);
                });

                if (checkbox) {
                    checkbox.addEventListener('change', (event) => {
                        if (state.disabled) {
                            event.preventDefault();
                            return;
                        }
                        if (checkbox.disabled) {
                            event.preventDefault();
                            return;
                        }
                        event.stopPropagation();
                        if (event.target.checked) {
                            selected.add(String(value));
                        } else {
                            selected.delete(String(value));
                        }
                        const normalized = normaliseAccessLevels(Array.from(selected));
                        selected.clear();
                        normalized.forEach((entry) => selected.add(entry));
                        syncHiddenInputs();
                        syncChips();
                        syncCheckboxes();
                    });
                }
            });

            const handleChipRemoval = (event) => {
                if (state.disabled) {
                    return;
                }
                const removeBtn = event.target.closest('[data-access-remove]');
                if (!removeBtn) {
                    return;
                }
                event.preventDefault();
                event.stopImmediatePropagation();
                const value = removeBtn.dataset.accessRemove;
                if (value) {
                    selected.delete(value);
                    syncHiddenInputs();
                    syncChips();
                    syncCheckboxes();
                }
            };

            chipsContainer?.addEventListener('click', handleChipRemoval);
            chipsContainer?.addEventListener('mousedown', handleChipRemoval);

            if (searchInput) {
                searchInput.addEventListener('input', applySearchFilter);
            }

            multiselect.addEventListener('shown.bs.dropdown', () => {
                if (state.disabled) {
                    return;
                }
                if (searchInput) {
                    searchInput.value = '';
                    applySearchFilter();
                    searchInput.focus();
                }
            });

            applySearchFilter();
            syncChips();
            syncCheckboxes();

            multiselect.accessMultiselectApi = {
                setSelected: setSelectedValues,
                getSelected: () => Array.from(selected),
                reset: () => setSelectedValues([]),
                setDisabled: (disabled) => {
                    state.disabled = Boolean(disabled);
                    if (toggleButton) {
                        toggleButton.disabled = state.disabled;
                        toggleButton.classList.toggle('is-disabled', state.disabled);
                    }
                    if (searchInput) {
                        searchInput.disabled = state.disabled;
                    }
                    updateOptionDisabledStates();
                    if (state.disabled && toggleButton && typeof bootstrap !== 'undefined') {
                        const dropdown = bootstrap.Dropdown.getInstance(toggleButton);
                        dropdown?.hide();
                    }
                },
            };
        }

        function initUserFilters(controller) {
            const selectEls = {
                department: document.getElementById('filterDepartment'),
                job: document.getElementById('filterJobTitle'),
                role: document.getElementById('filterRole'),
                status: document.getElementById('filterStatus'),
            };
            const resetBtn = document.getElementById('userFilterReset');
            const toggleBtn = document.getElementById('userFilterToggle');

            userFilterToggleEl = toggleBtn;

            Object.entries(selectEls).forEach(([key, select]) => {
                if (!select) return;
                select.addEventListener('change', (event) => {
                    const value = normaliseFilterValue(event.target.value || 'all');
                    userFilters[key] = value === '' ? 'all' : value;
                    applyUserFilters(controller, toggleBtn);
                });
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    Object.keys(userFilters).forEach((key) => {
                        userFilters[key] = 'all';
                        if (selectEls[key]) {
                            selectEls[key].value = 'all';
                        }
                    });
                    applyUserFilters(controller, toggleBtn);
                });
            }

            updateUserFilterToggle(toggleBtn);
            applyUserFilters(controller, toggleBtn);
        }

        function applyUserFilters(controller, toggleBtn = userFilterToggleEl) {
            const rows = Array.from(document.querySelectorAll('#usersTable tbody tr[data-user-id]'));
            rows.forEach((row) => {
                const matches = matchesUserFilters(row);
                const checkbox = row.querySelector('[data-user-row-select]');
                if (matches) {
                    row.removeAttribute('data-ignore');
                    row.style.display = '';
                } else {
                    row.setAttribute('data-ignore', 'true');
                    row.style.display = 'none';
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            });
            updateUserFilterToggle(toggleBtn);
            controller?.refresh?.();
        }

        function matchesUserFilters(row) {
            if (!row) return false;

            const departmentValues = parseDepartments(row.dataset.sortDepartment)
                .map((value) => normaliseFilterValue(value));
            if (userFilters.department !== 'all' && !departmentValues.includes(userFilters.department)) {
                return false;
            }

            const jobValue = normaliseFilterValue(row.dataset.sortJob);
            if (userFilters.job !== 'all' && jobValue !== userFilters.job) {
                return false;
            }

            const roleValue = normaliseFilterValue(row.dataset.sortRole);
            if (userFilters.role !== 'all' && roleValue !== userFilters.role) {
                return false;
            }

            const statusValue = normaliseFilterValue(row.dataset.sortStatus);
            if (userFilters.status !== 'all' && statusValue !== userFilters.status) {
                return false;
            }

            return true;
        }

        function updateUserFilterToggle(toggleBtn = userFilterToggleEl) {
            if (!toggleBtn) return;
            const isActive = Object.values(userFilters).some((value) => value !== 'all');
            toggleBtn.classList.toggle('is-active', isActive);
        }

        function normaliseFilterValue(value) {
            return String(value ?? '').trim().toLowerCase();
        }

        function parseDepartments(value) {
            if (value === null || value === undefined) {
                return [];
            }
            const text = String(value).trim();
            if (text === '' || text === '—') {
                return [];
            }
            return text
                .split(',')
                .map((item) => item.trim())
                .filter((item) => item !== '');
        }

        function normaliseAccessLevels(values = []) {
            const entries = Array.isArray(values) ? values : [];
            const valid = [];
            const seen = new Set();
            entries.forEach((value) => {
                const trimmed = String(value ?? '').trim();
                if (!trimmed || !ACCESS_LEVEL_VALUES.includes(trimmed) || seen.has(trimmed)) {
                    return;
                }
                seen.add(trimmed);
                valid.push(trimmed);
            });

            return valid;
        }

        function applyRoleAccessRules(roleValue, multiselect, force = false) {
            if (!multiselect?.accessMultiselectApi) return;

            const role = String(roleValue ?? '').trim();
            if (role === ROLE_ADMIN) {
                multiselect.accessMultiselectApi.setSelected(normaliseAccessLevels(ACCESS_LEVEL_VALUES));
                multiselect.accessMultiselectApi.setDisabled(true);
                return;
            }

            if (role === ROLE_STUDENT) {
                multiselect.accessMultiselectApi.setSelected([ACCESS_LEVEL_FORMS]);
                multiselect.accessMultiselectApi.setDisabled(true);
                return;
            }

            multiselect.accessMultiselectApi.setDisabled(false);
            if (role === ROLE_FACULTY) {
                const current = multiselect.accessMultiselectApi.getSelected();
                multiselect.accessMultiselectApi.setSelected([...current, ACCESS_LEVEL_FORMS]);
                return;
            }

            if (force) {
                multiselect.accessMultiselectApi.setSelected(
                    normaliseAccessLevels(multiselect.accessMultiselectApi.getSelected())
                );
            }
        }

        function populateUserEditForm(form, row, action) {
            if (!form || !row) return;
            if (action) {
                form.setAttribute('action', action);
            }

            const nameInput = document.getElementById('editUserName');
            const emailInput = document.getElementById('editUserEmail');
            const deptInput = document.getElementById('editUserDepartment');
            const jobInput = document.getElementById('editUserJobTitle');
            const roleDropdown = document.getElementById('editUserRoleDropdown');
            const accessMultiselect = form.querySelector('[data-access-multiselect]');
            const departmentMultiselect = form.querySelector('[data-department-multiselect]');

            if (nameInput) nameInput.value = row.dataset.userName || '';
            if (emailInput) emailInput.value = row.dataset.userEmail || '';
            if (departmentMultiselect?.departmentMultiselectApi) {
                departmentMultiselect.departmentMultiselectApi.setSelected(row.dataset.userDepartment || '');
            } else if (deptInput) {
                deptInput.value = row.dataset.userDepartment || '';
            }
            if (jobInput) jobInput.value = row.dataset.userJob || '';
            setDropdownSelection(roleDropdown, row.dataset.userRole || '');
            if (accessMultiselect && !accessMultiselect.accessMultiselectApi) {
                setupAccessLevelMultiselect(accessMultiselect);
            }
            if (accessMultiselect?.accessMultiselectApi) {
                let values = [];
                if (row.dataset.userAccessLevel) {
                    try {
                        values = JSON.parse(row.dataset.userAccessLevel);
                    } catch (error) {
                        values = [];
                    }
                }
                accessMultiselect.accessMultiselectApi.setSelected(values);
                applyRoleAccessRules(row.dataset.userRole || '', accessMultiselect, true);
            }
        }

        function updateUserBulkBarDisplay(bulkBarEl, countEl) {
            const bar = bulkBarEl || document.getElementById('userBulkBar');
            const label = countEl || document.getElementById('userSelectedCount');
            const count = userSelection.size;

            if (label) {
                label.textContent = `${count} Selected`;
            }

            if (bar) {
                bar.classList.toggle('d-none', count === 0);
            }
        }

        function handleUserBulkAction(action, controller) {
            if (!action) return;

            if (action === 'clear') {
                clearUserSelection(controller);
                return;
            }

            if (action === 'delete') {
                if (userSelection.size === 0) return;
                pendingUserBulk = { ids: Array.from(userSelection) };
                const countEl = document.getElementById('userBulkDeleteCount');
                if (countEl) {
                    countEl.textContent = pendingUserBulk.ids.length;
                }
                userBulkDeleteModalInstance?.show();
            }

            if (action === 'access') {
                openUserBulkAccessModal();
            }
        }

        function initBulkAccessControls() {
            const multiselect = document.getElementById('bulkAccessMultiselect');
            if (multiselect && !multiselect.accessMultiselectApi) {
                setupAccessLevelMultiselect(multiselect);
            }

            document.querySelectorAll('[data-access-preset]').forEach((button) => {
                button.addEventListener('click', () => {
                    const preset = button.dataset.accessPreset;
                    const levels = ACCESS_PRESETS[preset] || [];
                    multiselect?.accessMultiselectApi?.setSelected(levels);
                    clearAccessPresetState();
                    button.classList.add('is-active');
                });
            });

            const confirmBtn = document.getElementById('confirmUserBulkAccessBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => handleUserBulkAccessConfirm(confirmBtn));
            }
        }

        function clearAccessPresetState() {
            document.querySelectorAll('[data-access-preset]').forEach((button) => {
                button.classList.remove('is-active');
            });
        }

        function openUserBulkAccessModal() {
            if (userSelection.size === 0) return;

            pendingUserBulk = { ids: Array.from(userSelection) };
            const countEl = document.getElementById('userBulkAccessCount');
            if (countEl) {
                countEl.textContent = pendingUserBulk.ids.length;
            }

            const modeEl = document.getElementById('bulkAccessMode');
            if (modeEl) {
                modeEl.value = 'replace';
            }

            const multiselect = document.getElementById('bulkAccessMultiselect');
            if (multiselect && !multiselect.accessMultiselectApi) {
                setupAccessLevelMultiselect(multiselect);
            }
            multiselect?.accessMultiselectApi?.setSelected([ACCESS_LEVEL_FORMS]);
            clearAccessPresetState();
            document.querySelector('[data-access-preset="faculty"]')?.classList.add('is-active');

            userBulkAccessModalInstance?.show();
        }

        function handleUserBulkAccessConfirm(button) {
            if (!button) return;
            if (!pendingUserBulk || !Array.isArray(pendingUserBulk.ids) || pendingUserBulk.ids.length === 0) {
                return;
            }

            const multiselect = document.getElementById('bulkAccessMultiselect');
            const modeEl = document.getElementById('bulkAccessMode');
            const accessLevels = multiselect?.accessMultiselectApi?.getSelected() || [];

            if (accessLevels.length === 0) {
                showTemporaryToast('Please select at least one access level.', 'warning');
                return;
            }

            const loadingText = button.dataset.loadingText || 'Applying...';
            setButtonLoading(button, true, button.dataset.defaultText || button.textContent.trim(), loadingText);

            fetch(userBulkAccessUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    ids: pendingUserBulk.ids,
                    mode: modeEl?.value || 'replace',
                    access_level: accessLevels,
                })
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to update access levels.');
                    }
                    return payload;
                })
                .then((payload) => {
                    userBulkAccessModalInstance?.hide();
                    showTemporaryToast(payload.message || 'Access levels updated.', 'success');
                    clearUserSelection(window.usersPage);
                    window.usersPage?.renderTable();
                    pendingUserBulk = null;
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to update access levels.', 'danger');
                })
                .finally(() => {
                    setButtonLoading(button, false, button.dataset.defaultText || 'Apply Access');
                });
        }

        function openUserDeleteModal(userId, row) {
            pendingUserDeleteId = userId;
            const nameEl = document.getElementById('userDeleteName');
            if (nameEl) {
                nameEl.textContent = row?.dataset.userName || 'this user';
            }
            userDeleteModalInstance?.show();
        }

        function clearUserSelection(controller) {
            if (window.usersPage) {
                window.usersPage.clearSelection();
                return;
            }
            userSelection.clear();
            document.querySelectorAll('#usersTable tbody tr[data-user-id]').forEach((row) => {
                row.classList.remove('is-selected');
                const checkbox = row.querySelector('[data-user-row-select]');
                if (checkbox) checkbox.checked = false;
            });

            const selectAllEl = document.getElementById('userSelectAll');
            if (selectAllEl) {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = false;
            }

            updateUserBulkBarDisplay();
            controller?.refresh?.();
        }

        function setButtonLoading(button, isLoading, defaultText, loadingText) {
            if (!button) return;
            if (isLoading) {
                const initialText = defaultText || button.dataset.defaultText || button.textContent.trim();
                button.dataset.defaultText = initialText;
                button.textContent = loadingText || 'Loading...';
                button.disabled = true;
                return;
            }
            const restored = defaultText || button.dataset.defaultText || button.textContent.trim();
            button.textContent = restored;
            button.disabled = false;
        }

        function initDeletedUsersModal() {
            const modalEl = document.getElementById('userDeletedModal');
            const tableBody = document.getElementById('userDeletedTableBody');
            if (!modalEl || !tableBody) {
                return;
            }

            modalEl.addEventListener('shown.bs.modal', loadDeletedUsers);
            tableBody.addEventListener('click', (event) => {
                const button = event.target.closest('[data-restore-user]');
                if (!button) {
                    return;
                }
                restoreDeletedUser(button);
            });
        }

        function loadDeletedUsers() {
            setDeletedUsersState('loading');

            fetch(userDeletedListUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || 'Failed to load deleted users.');
                    }
                    return payload;
                })
                .then((payload) => renderDeletedUsers(payload.data || []))
                .catch((error) => {
                    setDeletedUsersState('empty');
                    showDeletedUsersAlert('danger', error.message || 'Failed to load deleted users.');
                });
        }

        function renderDeletedUsers(users) {
            const tableBody = document.getElementById('userDeletedTableBody');
            if (!tableBody) {
                return;
            }

            const rows = Array.isArray(users) ? users : [];
            tableBody.innerHTML = rows.map((user) => `
                <tr>
                    <td>${escapeUserHtml(user.name || 'Unnamed User')}</td>
                    <td>${escapeUserHtml(user.email || '—')}</td>
                    <td>${escapeUserHtml(user.department_label || '—')}</td>
                    <td>${escapeUserHtml(user.job_title || '—')}</td>
                    <td><span class="evaluation-pill" data-pill-palette="blue" data-pill-value="${escapeUserAttribute(user.role || 'user')}">${escapeUserHtml(user.role || 'User')}</span></td>
                    <td><span class="evaluation-status evaluation-status--${escapeUserAttribute(user.status_class || 'inactive')}">${escapeUserHtml(user.status_label || 'Inactive')}</span></td>
                    <td>${escapeUserHtml(user.deleted_at || 'N/A')}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-restore-user" data-restore-user="${user.id}">
                            <i class="fa-solid fa-rotate-left me-1"></i> Restore
                        </button>
                    </td>
                </tr>
            `).join('');

            setDeletedUsersState(rows.length ? 'table' : 'empty');
            applyEvaluationPillColors(document.getElementById('userDeletedModal') || document);
        }

        function restoreDeletedUser(button) {
            const userId = button?.dataset?.restoreUser;
            if (!userId) {
                return;
            }

            setButtonLoading(button, true, 'Restore', 'Restoring...');

            fetch(userRestoreUrlTemplate.replace(':id', userId), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || 'Failed to restore user.');
                    }
                    return payload;
                })
                .then((payload) => {
                    showDeletedUsersAlert('success', payload.message || 'User restored successfully.');
                    showTemporaryToast(payload.message || 'User restored successfully.', 'success');
                    setTimeout(() => {
                        loadDeletedUsers();
                        window.usersPage?.renderTable();
                    }, 500);
                })
                .catch((error) => {
                    showDeletedUsersAlert('danger', error.message || 'Failed to restore user.');
                    setButtonLoading(button, false, 'Restore');
                });
        }

        function setDeletedUsersState(state) {
            document.getElementById('userDeletedLoading')?.classList.toggle('d-none', state !== 'loading');
            document.getElementById('userDeletedEmpty')?.classList.toggle('d-none', state !== 'empty');
            document.getElementById('userDeletedTableWrap')?.classList.toggle('d-none', state !== 'table');
            const alert = document.getElementById('userDeletedAlert');
            if (alert) {
                alert.innerHTML = '';
            }
        }

        function showDeletedUsersAlert(type, message) {
            const alert = document.getElementById('userDeletedAlert');
            if (!alert) {
                showTemporaryToast(message, type === 'danger' ? 'danger' : 'success');
                return;
            }

            alert.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${escapeUserHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        function escapeUserHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeUserAttribute(value) {
            return escapeUserHtml(value).replace(/`/g, '&#096;');
        }

        function handleUserBulkDeleteConfirm(button) {
            if (!button) return;
            if (!pendingUserBulk || !Array.isArray(pendingUserBulk.ids) || pendingUserBulk.ids.length === 0) {
                return;
            }

            const loadingText = button.dataset.loadingText || 'Deleting...';
            setButtonLoading(button, true, button.dataset.defaultText || button.textContent.trim(), loadingText);

            fetch(userBulkDeleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ ids: pendingUserBulk.ids })
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete selected users.');
                    }
                    return payload;
                })
                .then((payload) => {
                    const deletedIds = payload.deleted || pendingUserBulk.ids;
                    removeUserRows(deletedIds);
                    if (!window.usersPage) {
                        deletedIds.forEach((id) => userSelection.delete(String(id)));
                        updateUserBulkBarDisplay();
                    }
                    userBulkDeleteModalInstance?.hide();
                    showTemporaryToast(payload.message || 'Selected users deleted.', 'success');
                    pendingUserBulk = null;
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to delete selected users.', 'danger');
                })
                .finally(() => {
                    setButtonLoading(button, false, button.dataset.defaultText || 'Delete Selected');
                });
        }

        function handleUserDeleteConfirm(button) {
            if (!button || !pendingUserDeleteId) return;

            const loadingText = button.dataset.loadingText || 'Deleting...';
            setButtonLoading(button, true, button.dataset.defaultText || button.textContent.trim(), loadingText);

            fetch(userDeleteUrlTemplate.replace(':id', pendingUserDeleteId), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete user.');
                    }
                    return payload;
                })
                .then((payload) => {
                    removeUserRows([pendingUserDeleteId]);
                    if (!window.usersPage) {
                        userSelection.delete(String(pendingUserDeleteId));
                        updateUserBulkBarDisplay();
                    }
                    userDeleteModalInstance?.hide();
                    showTemporaryToast(payload.message || 'User deleted.', 'success');
                    pendingUserDeleteId = null;
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to delete user.', 'danger');
                })
                .finally(() => {
                    setButtonLoading(button, false, button.dataset.defaultText || 'Delete');
                });
        }

        function handleUserStatusToggle(button) {
            if (!button) return;
            const userId = button.dataset.userId;
            const toggleUrl = button.dataset.toggleUrl;
            if (!userId || !toggleUrl) {
                return;
            }

            const row = document.querySelector(`#usersTable tbody tr[data-user-id="${userId}"]`);
            const currentStatus = String(row?.dataset.userStatus || button.dataset.currentStatus || '').toLowerCase();
            const nextStatus = currentStatus === 'active' ? 'Inactive' : 'Active';

            setButtonLoading(button, true, button.dataset.defaultText || button.textContent.trim(), 'Updating...');

            fetch(toggleUrl, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ status: nextStatus })
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || 'Failed to update user status.');
                    }
                    return payload;
                })
                .then((payload) => {
                    updateUserRowStatus(userId, payload.data || {});
                    showTemporaryToast(payload.message || 'User status updated.', 'success');
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Failed to update user status.', 'danger');
                })
                .finally(() => {
                    setButtonLoading(button, false, button.dataset.defaultText || button.textContent.trim());
                });
        }

        function updateUserRowStatus(userId, payload) {
            if (window.usersPage) {
                window.usersPage.updateUserStatus(userId, payload || {});
                return;
            }
            const row = document.querySelector(`#usersTable tbody tr[data-user-id="${userId}"]`);
            if (!row) {
                return;
            }

            const statusSlug = String(payload.status || '').toLowerCase() || (payload.status_label === 'Active' ? 'active' : 'inactive');
            const statusLabel = payload.status_label || (statusSlug === 'active' ? 'Active' : 'Inactive');
            const statusValue = payload.status_value || statusLabel;

            row.dataset.sortStatus = statusLabel.toLowerCase();
            row.dataset.userStatus = statusValue;

            const statusEl = row.querySelector('.evaluation-status');
            if (statusEl) {
                statusEl.textContent = statusLabel;
                statusEl.classList.remove('evaluation-status--active', 'evaluation-status--inactive');
                statusEl.classList.add(`evaluation-status--${statusSlug}`);
            }

            const toggleBtn = row.querySelector('[data-user-toggle]');
            if (toggleBtn) {
                const toggleLabel = payload.toggle_label || (statusSlug === 'active' ? 'Deactivate' : 'Activate');
                toggleBtn.textContent = toggleLabel;
                toggleBtn.dataset.defaultText = toggleLabel;
                toggleBtn.dataset.currentStatus = statusSlug;
            }

            applyUserFilters(window.tableControllers?.usersTable, userFilterToggleEl);
        }

        function removeUserRows(ids = []) {
            if (!ids || !ids.length) return;
            if (window.usersPage) {
                window.usersPage.removeUsers(ids);
                return;
            }
            ids.forEach((id) => {
                const row = document.querySelector(`#usersTable tbody tr[data-user-id="${id}"]`);
                if (row) {
                    row.remove();
                }
            });
            window.tableControllers?.usersTable?.refresh?.();
        }

        function setDropdownSelection(dropdown, value) {
            if (!dropdown) return;
            const label = dropdown.querySelector('[data-dropdown-label]');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const placeholderText = label?.dataset.placeholderText || '-- Select Option --';

            if (hiddenInput) {
                hiddenInput.value = value || '';
            }

            if (!label) return;

            if (!value) {
                label.textContent = placeholderText;
                label.classList.add('is-placeholder');
                return;
            }

            const option = dropdown.querySelector(`[data-option-value="${value}"]`);
            const labelText = option?.dataset.optionLabel || option?.textContent?.trim() || placeholderText;
            label.textContent = labelText;
            label.classList.remove('is-placeholder');
        }
    </script>
@endsection
