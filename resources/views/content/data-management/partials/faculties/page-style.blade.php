@section('page-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

        .layout-page .content-wrapper > .container-xxl.container-p-y {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .layout-page .content-wrapper > .container-xxl.container-p-y > .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }

        #alertContainer,
        #alertContainer + .card {
            margin-top: 0 !important;
        }

        #alertContainer:not(:empty) {
            margin-bottom: 1.5rem;
        }

        .evaluation-card {
            background-color: var(--bs-card-bg);
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
        }

        .faculty-action-card {
            background-color: #5c297c;
            color: #ffffff;
        }

        .faculty-action-card .card-title {
            color: #ffb736;
        }

        .faculty-action-card .card-title i {
            color: #ffb736;
        }

        .faculty-action-card p {
            color: #ffffff;
        }

        .faculty-action-card .text-muted {
            color: #ffffff !important;
        }

        .faculty-action-card .btn-faculty-action {
            background-color: #ffb736;
            color: #5c297c;
            border: none;
            font-weight: 600;
        }

        .faculty-action-card .btn-faculty-action i {
            color: #5c297c;
        }

        .faculty-action-card .btn-faculty-action:hover,
        .faculty-action-card .btn-faculty-action:focus {
            background-color: #e6a431;
            color: #5c297c;
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

        .table-cell-stack > * {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .table-cell-stack.is-wide {
            max-width: var(--dm-stack-max);
        }

        .faculty-assignment-cell {
            min-width: 18rem;
            max-width: 26rem;
            white-space: normal !important;
        }

        .faculty-assignment-list {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .faculty-assignment-item {
            display: grid;
            gap: 0.12rem;
            padding: 0.5rem 0.65rem;
            border: 1px solid #eef2f7;
            border-radius: 0.5rem;
            background: #fbfdff;
        }

        .faculty-assignment-subject {
            color: #334155;
            font-weight: 700;
            line-height: 1.25;
            white-space: normal;
        }

        .faculty-assignment-meta,
        .faculty-assignment-schedule {
            color: #64748b;
            line-height: 1.25;
        }

        .faculty-assignment-schedule {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .faculty-assignment-more {
            color: #5c297c;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .faculty-load-cell {
            width: 96px;
            text-align: center;
        }

        .faculty-load-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-width: 4rem;
            height: 2.25rem;
            padding: 0 0.75rem;
            border: 1px solid #e5def0;
            border-radius: 999px;
            background: #f7f2fb;
            color: #5c297c;
            font-weight: 800;
            line-height: 1;
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .faculty-load-btn:hover,
        .faculty-load-btn:focus {
            background: #efe3f7;
            border-color: #d9c3e8;
            color: #4b2266;
            transform: translateY(-1px);
        }

        .faculty-load-btn.is-empty,
        .faculty-load-btn:disabled {
            cursor: not-allowed;
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
            transform: none;
        }

        .faculty-load-btn i {
            font-size: 1.1rem;
        }

        .faculty-load-modal-header {
            align-items: flex-start;
            gap: 1rem;
            padding: 1.1rem 1.5rem;
        }

        .faculty-load-modal-heading {
            display: grid;
            gap: 0.35rem;
            min-width: 0;
        }

        .faculty-load-modal-eyebrow {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1;
            text-transform: uppercase;
        }

        .faculty-load-modal-title {
            color: #1f2937;
            font-size: 1.08rem;
            font-weight: 800;
            line-height: 1.25;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .faculty-load-modal-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.15rem;
        }

        .faculty-load-modal-summary-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.32rem 0.55rem;
            border: 1px solid #e8edf5;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            font-size: 0.78rem;
            line-height: 1.15;
        }

        .faculty-load-modal-summary-label {
            color: #94a3b8;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .faculty-load-modal-summary-item strong {
            font-weight: 800;
        }

        .faculty-load-modal-list {
            display: grid;
            gap: 0.55rem;
            max-height: min(65vh, 34rem);
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .faculty-load-modal-item {
            padding: 0.85rem;
            border: 1px solid #eef2f7;
            border-radius: 0.5rem;
            background: #fbfdff;
        }

        .faculty-load-modal-main {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .faculty-load-modal-subject {
            color: #334155;
            font-weight: 800;
            line-height: 1.3;
        }

        .faculty-load-modal-type {
            flex: 0 0 auto;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: #eaf3ff;
            color: #1d4ed8;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .faculty-load-modal-fields {
            display: grid;
            grid-template-columns: 1.15fr 0.9fr 0.9fr 1.35fr;
            gap: 0.45rem;
        }

        .faculty-load-modal-field {
            display: grid;
            gap: 0.1rem;
            min-width: 0;
            padding: 0.5rem 0.6rem;
            border-radius: 0.45rem;
            background: #ffffff;
            border: 1px solid #eef2f7;
        }

        .faculty-load-modal-label {
            color: #94a3b8;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .faculty-load-modal-value {
            color: #334155;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        @media (max-width: 767.98px) {
            .faculty-load-modal-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .faculty-load-modal-field.is-wide {
                grid-column: 1 / -1;
            }
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

        .evaluation-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-weight: 600;
            font-size: 0.75rem;
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

        .evaluation-count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
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

        .evaluation-bulk-bar #facultySelectedCount {
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

        .btn.btn-sm {
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            border-radius: 0.4rem;
        }

        .btn.btn-primary {
            background-color: #2563eb;
            border: none;
            color: #ffffff;
        }

        .btn.btn-primary:hover,
        .btn.btn-primary:focus,
        .btn.btn-primary:active {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: none;
        }

        .btn.btn-secondary {
            background-color: #dc2626;
            border: none;
            color: #ffffff;
        }

        .btn.btn-secondary:hover,
        .btn.btn-secondary:focus,
        .btn.btn-secondary:active {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
            box-shadow: none;
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

        div[data-table-id="facultyTable"] [data-table-info] {
            color: #6b7280;
            font-size: 0.875rem;
        }

        div[data-table-id="facultyTable"] .pagination .page-link {
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 0.9rem;
            margin: 0 0.1rem;
            color: #64748b;
            background-color: #e2e8f0;
            font-weight: 600;
            transition: background-color 0.15s ease;
        }

        div[data-table-id="facultyTable"] .pagination .page-link:hover:not(.disabled) {
            background-color: #cbd5e1;
            color: #475569;
        }

        div[data-table-id="facultyTable"] .pagination .page-item.active .page-link {
            background-color: #5c297c;
            color: #ffffff;
        }

        div[data-table-id="facultyTable"] .pagination .page-item.active .page-link:hover {
            background-color: #4b2266;
            color: #ffffff;
        }

        div[data-table-id="facultyTable"] .pagination .page-link span {
            color: inherit;
        }

        div[data-table-id="facultyTable"] .pagination .page-item.disabled .page-link {
            background-color: #e2e8f0;
            color: #94a3b8;
        }

        div[data-table-id="facultyTable"] .pagination .page-item.disabled .page-link,
        div[data-table-id="facultyTable"] .pagination .page-item.active .page-link,
        div[data-table-id="facultyTable"] .pagination .page-link {
            box-shadow: none;
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

        .evaluation-modal-btn.btn-danger {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        .evaluation-modal-btn.btn-danger:hover,
        .evaluation-modal-btn.btn-danger:focus {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
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

        .evaluation-modal-dialog--narrow {
            max-width: 42%;
        }

        @media (max-width: 992px) {
            .evaluation-modal-dialog--narrow {
                max-width: 60%;
            }

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
            .evaluation-table tbody td {
                padding: 0.75rem;
            }

            .evaluation-bulk-bar {
                bottom: 90px;
                flex-wrap: wrap;
                justify-content: center;
            }
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

        .table-filter-dropdown .filter-toggle:focus,
        .table-filter-dropdown .filter-toggle:focus-visible {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .table-filter-dropdown .filter-toggle.is-active {
            border-color: #5c297c;
            color: #5c297c;
        }

        .table-filter-dropdown .dropdown-menu {
            min-width: 260px;
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

        .table-filter-reset {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5c297c;
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

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-input {
            border-radius: 0.6rem;
        }

        .user-dropdown.show .user-dropdown-input {
            border-color: #94a3b8;
            box-shadow: none;
        }

        .user-dropdown-toggle {
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

        .user-dropdown-toggle:hover,
        .user-dropdown-toggle:focus,
        .user-dropdown-toggle:active,
        .user-dropdown.show .user-dropdown-toggle {
            border-color: #94a3b8;
            background-color: #ffffff;
            box-shadow: none;
        }

        .user-dropdown-toggle:focus {
            outline: none;
        }

        .user-dropdown-toggle i {
            color: #94a3b8;
        }

        .user-dropdown-label {
            font-weight: 500;
            color: var(--bs-body-color, #4b5563);
            font-size: 0.95rem;
        }

        .user-dropdown-label.is-placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .user-dropdown .dropdown-menu {
            width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
            padding: 0.75rem 0.85rem;
            top: calc(100% + 0.4rem) !important;
            left: 0 !important;
            right: 0 !important;
            transform: none !important;
            margin-top: 0 !important;
            z-index: 1085;
        }

        .user-dropdown .dropdown-menu::before {
            display: none;
        }

        .user-dropdown-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.65rem;
            max-height: calc((2 * 3.25rem) + 0.75rem);
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .user-dropdown-list.is-unlimited {
            max-height: calc((2 * 3.25rem) + 0.75rem);
            overflow-y: auto;
        }

        .user-dropdown .dropdown-item {
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
        }

        .user-dropdown .dropdown-item:hover,
        .user-dropdown .dropdown-item:focus {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        .user-dropdown .dropdown-item span,
        .user-dropdown .dropdown-item small {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-dropdown .dropdown-item {
            min-height: 3.25rem;
        }

        .user-dropdown .dropdown-item small {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 400;
            width: 100%;
        }

        .user-dropdown-search {
            border-radius: 0.5rem;
            margin-bottom: 0.6rem;
        }

        .department-multiselect {
            position: relative;
        }

        .department-multiselect-toggle {
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
            line-height: 1;
            cursor: pointer;
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
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.5rem;
            max-height: calc((2 * 3rem) + 0.5rem);
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

    </style>
@endsection
