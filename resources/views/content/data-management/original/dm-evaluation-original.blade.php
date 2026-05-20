@extends('layouts/contentNavbarLayout')

@section('title', 'Data Management - Evaluation')

@section('page-style')
    <style>
        .evaluation-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
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
        }

        .evaluation-table thead th {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
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
        }

        .evaluation-table thead th:nth-child(2),
        .evaluation-table tbody td:nth-child(2) {
            min-width: 220px;
        }

        .evaluation-table thead th:nth-child(3),
        .evaluation-table tbody td:nth-child(3) {
            min-width: 160px;
        }

        .evaluation-table thead th:nth-child(5),
        .evaluation-table tbody td:nth-child(5) {
            min-width: 200px;
        }

        .evaluation-table thead th:nth-child(7),
        .evaluation-table tbody td:nth-child(7) {
            min-width: 130px;
        }

        .evaluation-table thead th:nth-child(8),
        .evaluation-table tbody td:nth-child(8) {
            min-width: 110px;
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


        .evaluation-search-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 0.4rem 0.75rem;
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

        .evaluation-pill-cell {
            text-align: left;
        }

        .evaluation-col-selection {
            width: 48px;
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
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .evaluation-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: #e2e8f0;
            color: #1f2937;
            text-transform: capitalize;
            justify-content: center;
        }

        .evaluation-pill[data-value="1st"],
        .evaluation-pill[data-value="first"],
        .evaluation-pill[data-value="1st semester"] {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .evaluation-pill[data-value="2nd"],
        .evaluation-pill[data-value="second"],
        .evaluation-pill[data-value="2nd semester"] {
            background-color: #e9d5ff;
            color: #6b21a8;
        }

        .evaluation-pill[data-value="summer"] {
            background-color: #cffafe;
            color: #0e7490;
        }

        .evaluation-pill[data-value="2024-2025"] {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .evaluation-pill[data-value="2025-2026"] {
            background-color: #ddd6fe;
            color: #5b21b6;
        }

        .evaluation-pill[data-value="2026-2027"] {
            background-color: #bbf7d0;
            color: #047857;
        }

        .evaluation-status--active {
            color: #047857;
            background-color: #d1fae5;
        }

        .evaluation-status--inactive {
            color: #92400e;
            background-color: #fef3c7;
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
            max-width: 150px;
            font-size: 0.85rem;
            color: #334155;
        }

        .evaluation-copy-btn,
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

        .evaluation-copy-btn:hover,
        .evaluation-icon-btn:hover {
            background-color: #e2e8f0;
            color: #1f2937;
        }

        .evaluation-count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
        }

        .evaluation-actions .dropdown-menu {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.15);
        }

        .evaluation-actions .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .evaluation-bulk-bar {
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: 120px;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            background-color: #ffffff;
            border-radius: 999px;
            padding: 0.55rem 1.4rem;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
            z-index: 1080;
        }

        .evaluation-bulk-btn {
            border: none;
            background-color: #f1f5f9;
            color: #1f2937;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .evaluation-table td.actions-cell {
            text-align: left;
        }

        .evaluation-table td.actions-cell .dropdown {
            display: inline-flex;
        }

        .evaluation-bulk-btn:hover {
            background-color: #e2e8f0;
        }

        .evaluation-bulk-btn--danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .evaluation-bulk-btn--danger:hover {
            background-color: #fecaca;
        }

        .evaluation-bulk-close {
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1.2rem;
            padding: 0.15rem;
            transition: color 0.2s ease;
        }

        .evaluation-bulk-close:hover {
            color: #475569;
        }

        .evaluation-bulk-bar.d-none {
            display: none !important;
        }

        div[data-table-id="evaluationTable"] [data-table-info] {
            color: #6b7280;
            font-size: 0.875rem;
        }

        div[data-table-id="evaluationTable"] .pagination .page-link {
            border: none;
            border-radius: 0.75rem;
            padding: 0.5rem 0.9rem;
            margin: 0 0.1rem;
            color: #475569;
            background-color: #f1f5f9;
        }

        div[data-table-id="evaluationTable"] .pagination .page-link:hover {
            background-color: #e2e8f0;
            color: #1f2937;
        }

        div[data-table-id="evaluationTable"] .pagination .page-item.active .page-link {
            background-color: #4f46e5;
            color: #ffffff;
        }

        div[data-table-id="evaluationTable"] .pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #cbd5f5;
        }

        .evaluation-table thead th.sorted-asc,
        .evaluation-table thead th.sorted-desc {
            color: #1d4ed8;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator {
            color: #1d4ed8;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator i:first-child,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator i:last-child {
            color: #1d4ed8;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator i:last-child,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator i:first-child {
            color: #cbd5f5;
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
    </style>
@endsection

@section('content')
    <div class="container py-4">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Generate New Evaluation Form --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-plus me-2"></i>Generate Evaluation Form
                </h5>
                <form method="POST" action="{{ route('dm.evaluation.generateAll') }}">
                    @csrf
                    <input type="hidden" name="academic_year" id="all_academic_year" value="2025-2026">
                    <input type="hidden" name="semester" id="all_semester" value="1st">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bx bx-layer-plus me-1"></i>Generate All
                    </button>
                </form>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dm.evaluation.store') }}">
                    @csrf
                    <div class="row g-3">
                        {{-- Faculty Dropdown --}}
                        <div class="col-md-4">
                            <label for="faculty_id" class="form-label">Faculty Member</label>
                            <select name="faculty_id" id="faculty_id" class="form-select" required>
                                <option value="">-- Select Faculty --</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Academic Year --}}
                        <div class="col-md-3">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" id="academic_year" class="form-control"
                                placeholder="2024-2025" required>
                        </div>

                        {{-- Semester --}}
                        <div class="col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        {{-- Generate Button --}}
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-magic-wand me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Evaluation Forms Table --}}
        <div class="card evaluation-card" data-table-controller data-table-id="evaluationTable">
            <div class="card-header border-0 pb-0">
                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-list-ul"></i>
                    Evaluation Forms
                </h5>
            </div>

            {{-- Table Controls --}}
            <div class="card-body border-0 pt-3 evaluation-controls mt-4">
                <div class="row align-items-center g-3">
                    {{-- Rows per page dropdown --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
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

                    {{-- Search Bar --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <div class="evaluation-search-wrapper">
                                <i class="bx bx-search evaluation-search-icon"></i>
                                <input type="text" id="evaluationSearch" class="evaluation-search-input" data-table-search
                                    placeholder="Search...">
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
                                <th class="evaluation-col-selection text-center">
                                    <input type="checkbox" class="form-check-input evaluation-checkbox"
                                        id="evaluationSelectAll" data-select-all>
                                </th>
                                <th data-sort-key="faculty" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Faculty Name</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="year" data-sort-type="string" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Academic Year</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>Semester</th>
                                <th>Form Link</th>
                                <th>Status</th>
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
                            @forelse($evaluations as $evaluation)
                                @php
                                    $status = $evaluation->is_active ? 'active' : 'inactive';
                                    $searchTerms = strtolower($evaluation->faculty->name . ' ' . $evaluation->academic_year . ' ' . $evaluation->semester . ' ' . $evaluation->form_link);
                                @endphp
                                <tr class="table-row" data-evaluation-id="{{ $evaluation->id }}"
                                    data-sort-faculty="{{ strtolower($evaluation->faculty->name) }}"
                                    data-sort-year="{{ $evaluation->academic_year }}"
                                    data-sort-semester="{{ strtolower($evaluation->semester) }}"
                                    data-sort-status="{{ $status }}" data-sort-responses="{{ $evaluation->responses->count() }}"
                                    data-search="{{ $searchTerms }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input evaluation-checkbox" data-row-select
                                            value="{{ $evaluation->id }}">
                                    </td>
                                    <td>
                                        <span class="faculty-name">{{ ucwords(strtolower($evaluation->faculty->name)) }}</span>
                                    </td>
                                    <td class="evaluation-pill-cell">
                                        <span class="evaluation-pill"
                                            data-value="{{ $evaluation->academic_year }}">{{ $evaluation->academic_year }}</span>
                                    </td>
                                    <td class="evaluation-pill-cell">
                                        <span class="evaluation-pill"
                                            data-value="{{ strtolower($evaluation->semester) }}">{{ $evaluation->semester }}</span>
                                    </td>
                                    <td>
                                        <div class="evaluation-link-box">
                                            <span class="evaluation-link text-truncate">{{ $evaluation->form_link }}</span>
                                            <button type="button" class="evaluation-icon-btn"
                                                onclick="copyToClipboard('{{ $evaluation->form_link }}')" title="Copy link">
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
                                        <span class="evaluation-count-pill">{{ $evaluation->responses->count() }}</span>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="dropdown">
                                            <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('dm.evaluation.responses', $evaluation) }}">
                                                        View Responses
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item"
                                                        onclick="showQrModal({{ $evaluation->id }}, '{{ ucwords(strtolower($evaluation->faculty->name)) }}', '{{ $evaluation->academic_year }}', '{{ $evaluation->semester }}')">
                                                        Preview QR
                                                    </button>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('dm.evaluation.qr.download', $evaluation) }}">
                                                        Download QR
                                                    </a>
                                                </li>
                                                <li>
                                                    <form method="POST"
                                                        action="{{ route('dm.evaluation.toggle', $evaluation) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            {{ $evaluation->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form method="POST"
                                                        action="{{ route('dm.evaluation.destroy', $evaluation) }}"
                                                        onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow" data-empty>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bx bx-file-blank display-4 text-muted mb-3"></i>
                                            <h5 class="mb-2">No evaluation forms found</h5>
                                            <p class="text-muted mb-0">Generate your first evaluation form using the form above.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="evaluation-bulk-bar d-none" id="evaluationBulkBar">
                    <span class="fw-semibold" id="evaluationSelectedCount">0 Selected</span>
                    <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger" data-bulk-action="delete">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                    <button type="button" class="evaluation-bulk-close" data-bulk-action="clear" title="Clear selection">
                        <i class="bx bx-x"></i>
                    </button>
                </div>

                {{-- Pagination and Info --}}
                <div class="row mt-4 align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted" data-table-info></div>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Table pagination">
                            <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Modal --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">
                        Evaluation QR Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
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
                        <i class="bx bx-info-circle me-1"></i>
                        Students can scan this QR code to access the evaluation form
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadQrBtn" href="" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i>Download QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
    @include('components.table-controller-script')
    <script>
        const evaluationSelection = new Set();
        const evaluationSortState = { key: null, direction: 'asc' };

        document.addEventListener('DOMContentLoaded', function () {
            const controllerRoot = document.querySelector('[data-table-id="evaluationTable"]');
            if (controllerRoot && window.TableController) {
                window.tableControllers = window.tableControllers || {};
                const evaluationController = new TableController(controllerRoot);
                window.tableControllers.evaluationTable = evaluationController;
                initEvaluationTableEnhancements(controllerRoot, evaluationController);
            }

            initEvaluationSearch();
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

            if (tableBody) {
                tableBody.addEventListener('change', (event) => {
                    const checkbox = event.target.closest('[data-row-select]');
                    if (!checkbox) {
                        return;
                    }

                    const row = checkbox.closest('tr[data-evaluation-id]');
                    if (!row) {
                        return;
                    }

                    const id = row.dataset.evaluationId;
                    if (checkbox.checked) {
                        evaluationSelection.add(id);
                    } else {
                        evaluationSelection.delete(id);
                    }

                    row.classList.toggle('is-selected', checkbox.checked);
                    syncEvaluationSelectAll(selectAllEl);
                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                });
            }

            if (selectAllEl) {
                selectAllEl.addEventListener('change', (event) => {
                    const shouldSelect = event.target.checked;
                    document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]').forEach((row) => {
                        const checkbox = row.querySelector('[data-row-select]');
                        if (!checkbox) {
                            return;
                        }

                        checkbox.checked = shouldSelect;
                        row.classList.toggle('is-selected', shouldSelect);

                        const id = row.dataset.evaluationId;
                        if (shouldSelect) {
                            evaluationSelection.add(id);
                        } else {
                            evaluationSelection.delete(id);
                        }
                    });

                    syncEvaluationSelectAll(selectAllEl);
                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                });
            }

            controllerRoot.addEventListener('table:updated', () => {
                document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]').forEach((row) => {
                    const id = row.dataset.evaluationId;
                    const checkbox = row.querySelector('[data-row-select]');
                    const isSelected = evaluationSelection.has(id);

                    if (checkbox) {
                        checkbox.checked = isSelected;
                    }

                    row.classList.toggle('is-selected', isSelected);
                });

                syncEvaluationSelectAll(selectAllEl);
                updateEvaluationBulkBar(bulkBar, selectedCountEl);
            });

            if (bulkBar) {
                bulkBar.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-bulk-action]');
                    if (!button) {
                        return;
                    }

                    handleEvaluationBulkAction(button.dataset.bulkAction, {
                        bulkBar,
                        selectedCountEl,
                        selectAllEl,
                        controller,
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

            if (evaluationSortState.key === sortKey) {
                evaluationSortState.direction = evaluationSortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                evaluationSortState.key = sortKey;
                evaluationSortState.direction = 'asc';
            }

            const datasetKey = 'sort' + sortKey.charAt(0).toUpperCase() + sortKey.slice(1);
            const rows = Array.from(document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]'));
            const emptyRow = document.querySelector('#evaluationTable tbody tr[data-empty]');

            rows.sort((a, b) => {
                let valueA = a.dataset[datasetKey] ?? '';
                let valueB = b.dataset[datasetKey] ?? '';

                if (sortType === 'number') {
                    valueA = parseFloat(valueA) || 0;
                    valueB = parseFloat(valueB) || 0;
                    return valueA - valueB;
                }

                return valueA.localeCompare(valueB);
            });

            if (evaluationSortState.direction === 'desc') {
                rows.reverse();
            }

            const tbody = document.querySelector('#evaluationTable tbody');
            rows.forEach((row) => tbody.appendChild(row));
            if (emptyRow) {
                tbody.appendChild(emptyRow);
            }

            updateEvaluationSortIndicators(headers);
            controller.refresh();
        }

        function updateEvaluationSortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';
            });

            if (!evaluationSortState.key) {
                return;
            }

            headers.forEach((header) => {
                if (header.dataset.sortKey === evaluationSortState.key) {
                    header.classList.add(
                        evaluationSortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc'
                    );
                    header.dataset.sortState = evaluationSortState.direction;
                }
            });
        }

        function syncEvaluationSelectAll(selectAllEl) {
            if (!selectAllEl) {
                return;
            }

            const rows = Array.from(document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]'));
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
            if (!bulkBar || !selectedCountEl) {
                return;
            }

            const count = evaluationSelection.size;
            selectedCountEl.textContent = `${count} Selected`;
            bulkBar.classList.toggle('d-none', count === 0);
        }

        function handleEvaluationBulkAction(action, helpers) {
            const { bulkBar, selectedCountEl, selectAllEl, controller } = helpers;

            if (!action) {
                return;
            }

            if (action === 'clear') {
                clearEvaluationSelection({ bulkBar, selectedCountEl, selectAllEl, controller });
                return;
            }

            const ids = Array.from(evaluationSelection);
            if (!ids.length) {
                return;
            }

            if (action === 'delete') {
                handleEvaluationBulkDelete(ids, { bulkBar, selectedCountEl, selectAllEl, controller });
            }
        }

        function clearEvaluationSelection({ bulkBar, selectedCountEl, selectAllEl, controller }) {
            evaluationSelection.clear();
            document.querySelectorAll('#evaluationTable tbody tr[data-evaluation-id]').forEach((row) => {
                row.classList.remove('is-selected');
                const checkbox = row.querySelector('[data-row-select]');
                if (checkbox) {
                    checkbox.checked = false;
                }
            });

            if (selectAllEl) {
                selectAllEl.checked = false;
                selectAllEl.indeterminate = false;
            }

            updateEvaluationBulkBar(bulkBar, selectedCountEl);
            controller?.refresh?.();
        }

        function handleEvaluationBulkDelete(ids, { bulkBar, selectedCountEl, selectAllEl, controller }) {
            if (!confirm(`Delete ${ids.length} selected record(s)?`)) {
                return;
            }

            fetch('{{ route('dm.evaluation.bulkDestroy') }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids })
            })
                .then(async (response) => {
                    if (!response.ok) {
                        const error = await response.json().catch(() => ({ message: 'Bulk delete failed.' }));
                        throw new Error(error.message || 'Bulk delete failed.');
                    }
                    return response.json();
                })
                .then(() => {
                    ids.forEach((id) => {
                        const row = document.querySelector(`tr[data-evaluation-id="${id}"]`);
                        if (row) {
                            row.remove();
                        }
                        evaluationSelection.delete(id);
                    });

                    if (selectAllEl) {
                        selectAllEl.checked = false;
                        selectAllEl.indeterminate = false;
                    }

                    updateEvaluationBulkBar(bulkBar, selectedCountEl);
                    controller?.refresh?.();
                    showTemporaryToast('Selected evaluation forms deleted successfully.', 'success');
                })
                .catch((error) => {
                    showTemporaryToast(error.message || 'Bulk delete failed.', 'danger');
                });
        }

        function showTemporaryToast(message, type = 'success') {
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

            if (!searchInput || !clearButton) {
                return;
            }

            const toggleClearVisibility = () => {
                if (searchInput.value.trim() === '') {
                    clearButton.classList.remove('is-visible');
                } else {
                    clearButton.classList.add('is-visible');
                }
            };

            searchInput.addEventListener('input', toggleClearVisibility);

            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                toggleClearVisibility();
                const event = new Event('input', { bubbles: true });
                searchInput.dispatchEvent(event);
            });

            toggleClearVisibility();
        }


        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = '<i class="bx bx-check me-2"></i>Link copied to clipboard!';
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        function showQrModal(evaluationId, facultyName, academicYear, semester) {
            document.getElementById('qrFacultyName').textContent = facultyName;
            document.getElementById('qrDetails').textContent = `${academicYear} - ${semester} Semester`;

            const qrContainer = document.getElementById('qrCodeContainer');
            qrContainer.innerHTML = '<div class="spinner-border text-primary"></div>';

            fetch(`/data-management/evaluation/${evaluationId}/qr`)
                .then(res => res.text())
                .then(dataUrl => {
                    document.getElementById('qrCodeContainer').innerHTML = `<img src="${dataUrl}" class="img-fluid" alt="QR Code">`;
                })
                .catch(() => {
                    qrContainer.innerHTML = '<p class="text-danger">QR Code generation failed. Please try downloading instead.</p>';
                });

            document.getElementById('downloadQrBtn').href = `/data-management/evaluation/${evaluationId}/qr/download`;
            new bootstrap.Modal(document.getElementById('qrModal')).show();
        }
    </script>
@endsection