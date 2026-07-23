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

        .purple {
            background-attachment: #e4c7ff;
            color: #4c1d95;
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

        #courseAlertContainer,
        #courseAlertContainer+.card {
            margin-top: 0 !important;
        }

        #courseAlertContainer:not(:empty) {
            margin-bottom: 1.5rem;
        }

        .evaluation-card {
            background-color: var(--bs-card-bg);
            border: var(--bs-card-border-width) solid var(--bs-card-border-color);
            border-radius: var(--bs-card-border-radius);
            box-shadow: var(--bs-card-box-shadow, 0 2px 6px rgba(67, 89, 113, 0.12));
        }

        .course-action-card {
            background-color: #5c297c;
            color: #ffffff;
        }

        .course-action-card .card-title {
            color: #ffb736;
        }

        .course-action-card .card-title i {
            color: #ffb736;
        }

        .course-action-card p,
        .course-action-card .text-muted {
            color: #ffffff !important;
        }

        .course-action-card .btn-course-action {
            background-color: #ffb736;
            color: #5c297c;
            border: none;
            font-weight: 600;
        }

        .course-action-card .btn-course-action i {
            color: #5c297c;
        }

        .course-action-card .btn-course-action:hover,
        .course-action-card .btn-course-action:focus {
            background-color: #e6a431;
            color: #5c297c;
        }

        .btn-course-primary {
            background-color: #5c297c;
            border-color: #5c297c;
            color: #ffffff;
        }

        .btn-course-primary:hover,
        .btn-course-primary:focus {
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

        .btn-deleted-course,
        .btn-deleted-minor-course,
        .btn-deleted-assignment {
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

        .btn-deleted-course:hover,
        .btn-deleted-course:focus,
        .btn-deleted-minor-course:hover,
        .btn-deleted-minor-course:focus,
        .btn-deleted-assignment:hover,
        .btn-deleted-assignment:focus {
            border-color: #e6a431;
            background: #ffb736;
            color: #3a0050;
            box-shadow: 0 0.55rem 1.1rem rgba(230, 164, 49, 0.2);
        }

        .btn-restore-course,
        .btn-restore-assignment {
            background: linear-gradient(135deg, #5c297c, #6f2a8f);
            border: 1px solid #5c297c;
            color: #ffffff;
            border-radius: 999px;
            font-weight: 700;
            padding: 0.45rem 0.9rem;
            box-shadow: 0 0.55rem 1.2rem rgba(92, 41, 124, 0.18);
        }

        .btn-restore-course:hover,
        .btn-restore-course:focus,
        .btn-restore-assignment:hover,
        .btn-restore-assignment:focus {
            background: linear-gradient(135deg, #ffb736, #e6a431);
            border-color: #e6a431;
            color: #3a0050;
            box-shadow: 0 0.65rem 1.35rem rgba(230, 164, 49, 0.24);
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

        .evaluation-sort-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .evaluation-table thead th.sortable {
            cursor: pointer;
            user-select: none;
        }

        .evaluation-sort-indicator {
            display: inline-flex;
            flex-direction: column;
            color: #cbd5f5;
            font-size: 0.65rem;
        }

        .evaluation-table thead th.sortable .evaluation-sort-indicator .icon-up,
        .evaluation-table thead th.sortable .evaluation-sort-indicator .icon-down {
            display: none;
        }

        .evaluation-table thead th.sorted-asc .evaluation-sort-indicator .icon-up,
        .evaluation-table thead th.sorted-desc .evaluation-sort-indicator .icon-down {
            display: inline-flex;
            color: #1d4ed8;
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

        .course-pill--class {
            letter-spacing: 0.05em;
        }

        .evaluation-count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
        }

        .evaluation-icon-btn {
            border: 1px solid rgba(92, 41, 124, 0.14);
            background: linear-gradient(135deg, rgba(92, 41, 124, 0.1), rgba(255, 183, 54, 0.14));
            color: #5c297c;
            border-radius: 0.75rem;
            width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            box-shadow: 0 0.45rem 1rem rgba(44, 0, 63, 0.08);
        }

        .evaluation-icon-btn:hover,
        .evaluation-icon-btn:focus,
        .evaluation-icon-btn.show {
            background: linear-gradient(135deg, #5c297c, #74318a);
            border-color: rgba(255, 183, 54, 0.62);
            color: #ffffff;
            box-shadow: 0 0.8rem 1.35rem rgba(92, 41, 124, 0.2);
            transform: translateY(-1px);
        }

        .evaluation-icon-btn:hover i,
        .evaluation-icon-btn:focus i,
        .evaluation-icon-btn.show i {
            color: #ffd400;
        }

        .course-handler-btn {
            border: 1px solid rgba(92, 41, 124, 0.18);
            background: linear-gradient(135deg, #fff8e8, #f4e8ff);
            color: #5c297c;
            border-radius: 999px;
            min-width: 3.2rem;
            height: 2.15rem;
            padding: 0 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(92, 41, 124, 0.08);
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .course-handler-btn i {
            font-size: 1.05rem;
        }

        .course-handler-btn:hover:not(:disabled),
        .course-handler-btn:focus:not(:disabled) {
            background: linear-gradient(135deg, #5c297c, #8c3f97);
            border-color: rgba(255, 183, 54, 0.6);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(92, 41, 124, 0.22);
        }

        .course-handler-btn:hover:not(:disabled) i,
        .course-handler-btn:focus:not(:disabled) i {
            color: #ffd400;
        }

        .course-handler-btn.is-empty {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            box-shadow: none;
            cursor: not-allowed;
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

        .course-handlers-modal {
            border: 0;
            overflow: hidden;
        }

        .course-handlers-modal-header {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.28), transparent 34%),
                linear-gradient(135deg, #3a0050, #6f2a8f);
            border-bottom: 0;
            color: #ffffff;
            position: relative;
        }

        .course-handlers-modal-header .modal-title,
        .course-handlers-modal-header .course-handlers-subtitle,
        .course-handlers-modal-header .evaluation-modal-close {
            color: #ffffff;
        }

        .course-handlers-eyebrow {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0.25rem;
            color: #ffcf45;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .course-handlers-subtitle {
            font-size: 0.85rem;
            opacity: 0.82;
        }

        .course-handlers-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .course-handlers-summary>div {
            border: 1px solid #eef2f6;
            border-radius: 0.75rem;
            background: #fbfcff;
            padding: 0.85rem 1rem;
            min-width: 0;
        }

        .course-handlers-summary span,
        .course-handler-field span {
            display: block;
            color: #8a9bb3;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .course-handlers-summary strong {
            display: block;
            color: #2f3b52;
            font-size: 0.95rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .course-handlers-list {
            display: grid;
            gap: 0.85rem;
            max-height: min(58vh, 34rem);
            overflow-y: auto;
            padding-right: 0.2rem;
        }

        .course-handler-card {
            border: 1px solid #e8edf5;
            border-radius: 0.8rem;
            background: linear-gradient(180deg, #ffffff, #fbfcff);
            padding: 1rem;
        }

        .course-handler-card-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 0.85rem;
        }

        .course-handler-card h6 {
            margin: 0 0 0.25rem;
            color: #2f3b52;
            font-weight: 800;
            line-height: 1.25;
        }

        .course-handler-card p {
            margin: 0;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .course-handler-employee {
            flex: 0 0 auto;
            border-radius: 999px;
            background: #f4e8ff;
            color: #5c297c;
            padding: 0.25rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .course-handler-fields {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .course-handler-field {
            border: 1px solid #eef2f6;
            border-radius: 0.65rem;
            background: #ffffff;
            padding: 0.75rem;
            min-width: 0;
        }

        .course-handler-field strong {
            display: block;
            color: #2f3b52;
            font-size: 0.9rem;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .course-handlers-empty {
            border: 1px dashed #d9e1ec;
            border-radius: 0.85rem;
            padding: 2rem 1rem;
            text-align: center;
            color: #64748b;
            background: #fbfcff;
        }

        .course-handlers-empty i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 999px;
            background: #fff1c7;
            color: #5c297c;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }

        .course-handlers-empty h6 {
            color: #2f3b52;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .course-handlers-empty p {
            margin: 0;
        }

        .faculty-load-preview {
            border: 1px solid #e8edf5;
            border-radius: 0.85rem;
            background: linear-gradient(180deg, #ffffff, #fbfcff);
            padding: 1rem;
        }

        .faculty-load-preview-empty {
            color: #8a9bb3;
            font-size: 0.88rem;
            text-align: center;
            padding: 0.35rem 0;
        }

        .faculty-load-preview-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .faculty-load-preview-title {
            margin: 0;
            color: #2f3b52;
            font-size: 0.95rem;
            font-weight: 800;
        }

        .faculty-load-preview-subtitle {
            color: #64748b;
            font-size: 0.78rem;
        }

        .faculty-load-preview-count {
            border-radius: 999px;
            background: #f4e8ff;
            color: #5c297c;
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .faculty-load-preview-list {
            display: grid;
            gap: 0.55rem;
            max-height: 12rem;
            overflow-y: auto;
            padding-right: 0.15rem;
        }

        .faculty-load-preview-item {
            border: 1px solid #eef2f6;
            border-radius: 0.65rem;
            padding: 0.65rem 0.75rem;
            background: #ffffff;
        }

        .faculty-load-preview-item strong {
            display: block;
            color: #2f3b52;
            font-size: 0.84rem;
            line-height: 1.25;
        }

        .faculty-load-preview-item span {
            display: block;
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 0.2rem;
        }

        .faculty-load-preview-note {
            margin-top: 0.75rem;
            border-radius: 0.65rem;
            background: #fff8e8;
            color: #7a4b00;
            padding: 0.55rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .evaluation-modal-body .form-select {
            border: 1px solid #d9dee3;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.4;
            transition: border-color 0.2s ease;
            box-shadow: none;
        }

        .evaluation-modal-body .form-select:hover,
        .evaluation-modal-body .form-select:focus,
        .evaluation-modal-body .form-select:active {
            border-color: #94a3b8;
            box-shadow: none;
        }

        .evaluation-modal-body .form-select.is-placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .evaluation-modal-body .form-select option {
            color: #0f172a;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .evaluation-modal-body .form-select option[value=""] {
            color: #94a3b8;
            font-weight: 400;
        }

        .evaluation-modal-dialog--narrow {
            max-width: 42%;
        }

        .evaluation-alert-container .alert {
            border: none;
            border-radius: 0.9rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
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
            box-shadow: none;
            transform: none;
            outline: none;
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

        .course-tabs-panel {
            width: auto;
            flex: 0 0 auto;
            background-color: transparent;
            padding: 0;
            margin-left: auto;
        }

        .course-tabs-header {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .course-tabs-nav {
            display: flex;
            gap: 1.25rem;
            flex: 0 0 auto;
            padding-bottom: 0;
        }

        .course-tab-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-left: 1.25rem;
        }

        .btn-tab {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0.35rem 0;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            letter-spacing: 0.08em;
            border-bottom: 2px solid transparent;
        }

        .btn-tab.active {
            color: #ffb736;
            border-color: #ffb736;
        }

        .btn-tab:hover,
        .btn-tab:focus,
        .btn-tab.active:hover,
        .btn-tab.active:focus {
            box-shadow: none;
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

        .evaluation-bulk-btn--danger {
            background-color: rgba(220, 38, 38, 0.08);
            color: #dc2626 !important;
            border: 1px solid rgba(220, 38, 38, 0.25);
            border-radius: 999px;
            padding: 0.25rem 0.9rem;
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

        .course-section.d-none {
            display: none !important;
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
            min-height: 3.25rem;
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

        .user-dropdown .dropdown-item small {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 400;
            width: 100%;
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

        div[data-table-id="coursesTable"] [data-table-info],
        div[data-table-id="assignmentsTable"] [data-table-info] {
            color: #6b7280;
            font-size: 0.875rem;
        }

        div[data-table-id="coursesTable"] .pagination .page-link,
        div[data-table-id="assignmentsTable"] .pagination .page-link {
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

        div[data-table-id="coursesTable"] .pagination .page-link:hover:not(.disabled),
        div[data-table-id="assignmentsTable"] .pagination .page-link:hover:not(.disabled) {
            background-color: #cbd5e1;
            color: #475569;
        }

        div[data-table-id="coursesTable"] .pagination .page-item.active .page-link,
        div[data-table-id="assignmentsTable"] .pagination .page-item.active .page-link {
            background-color: #5c297c;
            color: #ffffff;
        }

        div[data-table-id="coursesTable"] .pagination .page-item.active .page-link:hover,
        div[data-table-id="assignmentsTable"] .pagination .page-item.active .page-link:hover {
            background-color: #4b2266;
            color: #ffffff;
        }

        div[data-table-id="coursesTable"] .pagination .page-link span,
        div[data-table-id="assignmentsTable"] .pagination .page-link span {
            color: inherit;
        }

        div[data-table-id="coursesTable"] .pagination .page-item.disabled .page-link,
        div[data-table-id="assignmentsTable"] .pagination .page-item.disabled .page-link {
            background-color: #e2e8f0;
            color: #94a3b8;
        }

        @media (max-width: 992px) {
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

            .course-handlers-summary,
            .course-handler-fields {
                grid-template-columns: 1fr;
            }

            .course-handler-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .course-handlers-list {
                max-height: 60vh;
            }

            .evaluation-bulk-bar {
                bottom: 90px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .course-tab-actions {
                width: 100%;
                justify-content: flex-end;
                margin-left: 0;
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
    </style>
@endsection
