@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Reports')

@section('vendor-style')
    <style>
            .dashboard-modern {
            --dash-purple-dark: #3a0050;
            --dash-purple: #5c297c;
            --dash-purple-soft: #7a3f92;
            --dash-gold: #ffb736;
            --dash-flame: #f0184d;
            --dash-orange: #ff8738;
            --dash-blue: #12a8b5;
            --dash-card-shadow: 0 1.15rem 2.5rem rgba(58, 0, 80, 0.12);
            }
        .content-wrapper .metric-card,
        .metric-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 183, 54, 0.24) !important;
            border-radius: 1.15rem;
            background: radial-gradient(circle at 85% 15%, rgba(255,183,54,.22), transparent 7rem),
            linear-gradient(120deg, #3a0050, #5c297c, rgba(240,24,77,.76)) !important;
            color: #ffffff;
            box-shadow: 0 1.1rem 2.4rem rgba(58, 0, 80, 0.18) !important;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .content-wrapper .metric-card:hover,
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1.35rem 2.8rem rgba(58, 0, 80, 0.24) !important;
        }

        .metric-card::before {
            content: "";
            position: absolute;
        }

        .metric-card::after {
            content: "";
            position: absolute;
        }

        .metric-card-clickable {
            cursor: pointer;
        }

        .metric-card-clickable:focus {
            outline: 3px solid rgba(105, 108, 255, 0.22);
            outline-offset: 3px;
        }

        .content-wrapper .metric-card .card-body,
        .metric-card .card-body {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 158px;
            background: transparent !important;
        }

        .content-wrapper .metric-card h3,
        .content-wrapper .metric-card p,
        .content-wrapper .metric-card small,
        .content-wrapper .metric-card span,
        .content-wrapper .metric-card i,
        .content-wrapper .metric-card .text-muted,
        .content-wrapper .metric-card .text-success,
        .content-wrapper .metric-card .text-info,
        .metric-card h3,
        .metric-card p,
        .metric-card small,
        .metric-card span,
        .metric-card i,
        .metric-card .text-muted,
        .metric-card .text-success,
        .metric-card .text-info {
            color: #ffffff !important;
        }

        .content-wrapper .metric-card .avatar-initial,
        .metric-card .avatar-initial {
            background: rgba(255, 255, 255, 0.16) !important;
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #ffffff !important;
            backdrop-filter: blur(8px);
        }

        .metric-card-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-height: 1.35rem;
            margin-top: auto;
            white-space: nowrap;
        }

        .metric-view-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .metric-card-clickable:hover .metric-view-indicator {
            text-decoration: underline;
        }

        .metric-details-table th {
            color: #8a9bb3;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .metric-details-table td {
            vertical-align: middle;
        }

        .content-wrapper .filtered-result-toggle-card,
        .filtered-result-toggle-card {
            position: relative;
            border: 1px solid rgba(255, 183, 54, 0.22) !important;
            border-radius: 1.2rem;
            background: radial-gradient(circle at 92% 12%, rgba(255, 185, 54, 0), 
            transparent 10rem), radial-gradient(circle at 76% 100%, rgb(240 24 77 / 0%),
            transparent 12rem), linear-gradient(120deg, #3a0050 0%, #5c297c 52%, #8d3d8c 100%) !important;
            box-shadow: 0 1.25rem 2.6rem rgba(58, 0, 80, 0.16) !important;
            color: #ffffff !important;
            overflow: hidden;
        }



        .content-wrapper .filtered-result-toggle-card .card-body,
        .filtered-result-toggle-card .card-body {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.35rem 1.5rem 1.55rem;
            background: transparent !important;
        }

        .content-wrapper .filtered-result-title,
        .filtered-result-title {
            color: #ffffff !important;
            font-size: 1.05rem;
            font-weight: 900;
            margin: 0;
        }

        .content-wrapper .filtered-result-subtitle,
        .filtered-result-subtitle {
            color: rgba(255, 255, 255, 0.9) !important;
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin: 0.25rem 0 0;
        }

        .content-wrapper .filtered-result-chip,
        .filtered-result-chip {
            align-items: center;
            background: rgba(255, 255, 255, 0.16) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: 999px;
            color: #ffffff !important;
            display: inline-flex;
            font-size: 0.78rem;
            font-weight: 800;
            gap: 0.3rem;
            padding: 0.35rem 0.7rem;
        }

        .filtered-result-toggle-btn {
            align-items: center;
            background: #ffb736;
            border: 0;
            border-radius: 999px;
            box-shadow: 0 0.75rem 1.7rem rgba(255, 183, 54, 0.24);
            color: #3a0050;
            display: inline-flex;
            font-weight: 900;
            gap: 0.45rem;
            padding: 0.75rem 1.15rem;
            white-space: nowrap;
        }

        .filtered-result-toggle-btn:hover,
        .filtered-result-toggle-btn:focus {
            background: #ffcc1b;
            color: #3a0050;
        }

        .filtered-result-toggle-btn .bx-chevron-down {
            font-size: 1.25rem;
            transition: transform 0.2s ease;
        }

        .filtered-result-toggle-btn[aria-expanded="true"] .bx-chevron-down {
            transform: rotate(180deg);
        }

        .filtered-result-panel {
            border: 1px solid rgba(232, 223, 240, 0.9);
            border-radius: 1.15rem;
            margin-top: 0.65rem;
            padding-top: 0;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.13), transparent 13rem),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(251, 247, 255, 0.94));
            box-shadow: 0 1rem 2.3rem rgba(44, 0, 63, 0.08);
        }

        .filtered-result-panel .card-body {
            padding: 1.35rem 1.5rem 1.5rem;
        }

        @media (max-width: 767.98px) {
            .filtered-result-toggle-card .card-body {
                align-items: stretch;
                flex-direction: column;
            }

            .filtered-result-toggle-btn {
                justify-content: center;
                width: 100%;
            }
        }

        .department-export-modal {
            --mcu-purple-midnight: #3a0050;
            --mcu-purple: #5c297c;
            --mcu-purple-haze: #6f2a8f;
            --mcu-gold: #ffb736;
            --mcu-gold-soft: #fff3d4;
            --mcu-border: rgba(92, 41, 124, 0.12);
        }

        .department-export-modal .modal-content {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1.5rem 3rem rgba(44, 0, 63, 0.24);
            overflow: hidden;
        }

        .department-export-modal .modal-header {
            align-items: center;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.26), transparent 9rem),
                linear-gradient(120deg, var(--mcu-purple-midnight), var(--mcu-purple), var(--mcu-purple-haze));
            border-bottom: 0;
            color: #ffffff;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }

        .department-export-modal .modal-title {
            color: #ffffff;
            font-weight: 900;
        }

        .department-export-close {
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
            width: 2.15rem;
        }

        .department-export-close:hover,
        .department-export-close:focus {
            background: rgba(255, 183, 54, 0.22);
            color: #ffffff;
        }

        .department-export-modal .modal-body {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.08), transparent 12rem),
                #fbf7ff;
            padding: 1.5rem;
        }

        .department-export-modal .form-label {
            color: #34445e;
            font-weight: 800;
        }

        .department-export-modal .form-control,
        .department-export-modal .form-select {
            border-color: rgba(92, 41, 124, 0.18);
            border-radius: 0.75rem;
            min-height: 2.7rem;
        }

        .department-export-modal .form-control:focus,
        .department-export-modal .form-select:focus {
            border-color: var(--mcu-purple);
            box-shadow: 0 0 0 0.2rem rgba(92, 41, 124, 0.12);
        }

        .department-export-filter-note {
            background: #ffffff;
            border: 1px solid var(--mcu-border);
            border-radius: 0.85rem;
            color: #52627a;
            font-weight: 700;
            padding: 0.85rem 1rem;
        }

        .department-export-filter-note .badge {
            background: linear-gradient(135deg, var(--mcu-gold), #ffcf62) !important;
            color: var(--mcu-purple-midnight) !important;
            font-weight: 900;
        }

        .department-export-modal .modal-footer {
            background: #ffffff;
            border-top: 1px solid var(--mcu-border);
            padding: 1rem 1.5rem;
        }

        .department-export-modal .modal-footer .btn {
            border-radius: 0.75rem;
            font-weight: 800;
            min-width: 7.5rem;
            padding: 0.7rem 1.15rem;
        }

        .department-export-modal .modal-footer .btn-outline-secondary {
            background: #ffffff;
            border-color: rgba(92, 41, 124, 0.28);
            color: var(--mcu-purple);
        }

        .department-export-modal .modal-footer .btn-outline-secondary:hover,
        .department-export-modal .modal-footer .btn-outline-secondary:focus {
            background: var(--mcu-gold-soft);
            border-color: var(--mcu-gold);
            color: var(--mcu-purple-midnight);
        }

        .department-export-modal .modal-footer .btn-primary {
            background: linear-gradient(135deg, var(--mcu-purple-midnight), var(--mcu-purple)) !important;
            border: 0;
            box-shadow: 0 0.75rem 1.5rem rgba(92, 41, 124, 0.2);
        }

        .reports-faculty-modal {
            --mcu-purple-midnight: #3a0050;
            --mcu-purple: #5c297c;
            --mcu-purple-haze: #6f2a8f;
            --mcu-gold: #ffb736;
            --mcu-gold-soft: #fff3d4;
            --mcu-border: rgba(92, 41, 124, 0.12);
        }

        .reports-faculty-modal .modal-dialog {
            margin-top: 5.5rem;
            margin-bottom: 2rem;
        }

        .reports-faculty-modal .modal-content {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 1.5rem 3rem rgba(44, 0, 63, 0.24);
            overflow: hidden;
            max-height: calc(100vh - 7.5rem);
        }

        .reports-faculty-modal .modal-header {
            align-items: center;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.26), transparent 12rem),
                linear-gradient(120deg, var(--mcu-purple-midnight), var(--mcu-purple), var(--mcu-purple-haze));
            border-bottom: 0;
            color: #ffffff;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
        }

        .reports-faculty-modal .modal-title {
            color: #ffffff;
            font-weight: 900;
        }

        .reports-faculty-modal .modal-title .badge {
            background: linear-gradient(135deg, var(--mcu-gold), #ffcf62) !important;
            color: var(--mcu-purple-midnight) !important;
            font-weight: 900;
        }

        .reports-faculty-close {
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
            width: 2.15rem;
        }

        .reports-faculty-close:hover,
        .reports-faculty-close:focus {
            background: rgba(255, 183, 54, 0.22);
            color: #ffffff;
        }

        .reports-faculty-modal .modal-body {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.08), transparent 14rem),
                #fbf7ff;
            max-height: calc(100vh - 13rem);
            overflow-y: auto;
            padding: 1.5rem;
        }

        @media (max-width: 767.98px) {
            .reports-faculty-modal .modal-dialog {
                margin: 4.75rem 0.75rem 1rem;
            }

            .reports-faculty-modal .modal-content {
                max-height: calc(100vh - 5.75rem);
            }

            .reports-faculty-modal .modal-body {
                max-height: calc(100vh - 11rem);
            }
        }

        .reports-faculty-filter-summary {
            background: #ffffff;
            border: 1px solid var(--mcu-border);
            border-radius: 0.9rem;
            box-shadow: 0 0.45rem 1rem rgba(44, 0, 63, 0.05);
            color: #52627a;
        }

        .reports-faculty-filter-summary .badge,
        .reports-faculty-modal .bg-label-primary,
        .reports-faculty-modal .bg-label-secondary {
            background: #f4e9fb !important;
            color: var(--mcu-purple) !important;
            font-weight: 900;
        }

        .reports-faculty-modal .btn-outline-primary {
            border-color: rgba(92, 41, 124, 0.35);
            border-radius: 0.75rem;
            color: var(--mcu-purple);
            font-weight: 800;
        }

        .reports-faculty-modal .btn-outline-primary:hover,
        .reports-faculty-modal .btn-outline-primary:focus {
            background: linear-gradient(135deg, var(--mcu-purple-midnight), var(--mcu-purple));
            border-color: var(--mcu-purple);
            color: #ffffff;
        }

        .reports-faculty-modal .form-label {
            color: #34445e;
            font-weight: 800;
        }

        .reports-faculty-modal .form-control,
        .reports-faculty-modal .form-select {
            border-color: rgba(92, 41, 124, 0.18);
            border-radius: 0.75rem;
            min-height: 2.55rem;
        }

        .reports-faculty-modal .form-control:focus,
        .reports-faculty-modal .form-select:focus {
            border-color: var(--mcu-purple);
            box-shadow: 0 0 0 0.2rem rgba(92, 41, 124, 0.12);
        }

        .reports-faculty-modal .faculty-modal-table {
            background: #ffffff;
            border: 1px solid var(--mcu-border);
            border-radius: 0.95rem;
            margin-bottom: 0;
            overflow: hidden;
        }

        .reports-faculty-modal .faculty-modal-table thead th {
            background: #fbf7ff;
            border-bottom: 1px solid rgba(92, 41, 124, 0.1);
            color: #71809a;
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            padding: 1rem;
            text-transform: uppercase;
        }

        .reports-faculty-modal .faculty-modal-table tbody td {
            border-color: rgba(92, 41, 124, 0.08);
            color: #26364d;
            padding: 1rem;
            vertical-align: middle;
        }

        .reports-faculty-modal .faculty-modal-table tbody tr:hover {
            background: #fbf7ff;
        }

        .reports-faculty-modal .avatar-initial {
            background: linear-gradient(135deg, var(--mcu-purple), var(--mcu-purple-haze)) !important;
            box-shadow: 0 0.45rem 1rem rgba(92, 41, 124, 0.18);
            color: #ffffff !important;
            font-weight: 900;
        }

        .reports-faculty-modal .faculty-modal-table .badge {
            border-radius: 999px;
            font-weight: 900;
            padding: 0.42rem 0.65rem;
        }

        .reports-faculty-modal .faculty-modal-table .bg-info,
        .reports-faculty-modal .faculty-modal-table .bg-success {
            background: linear-gradient(135deg, #5c297c, #6f2a8f) !important;
            color: #ffffff !important;
        }

        .reports-faculty-modal .faculty-modal-table .progress {
            background: #f1e8f5;
            border-radius: 999px;
            overflow: hidden;
        }

        .reports-faculty-modal .faculty-modal-table .progress-bar {
            background: linear-gradient(90deg, var(--mcu-purple), var(--mcu-gold)) !important;
        }

        .reports-faculty-modal .faculty-rating-indicator {
            background: #edf0f4;
            border-radius: 999px;
            height: 5px;
            margin: 0.35rem auto 0;
            max-width: 5.5rem;
            overflow: hidden;
            width: 100%;
        }

        .reports-faculty-modal .faculty-rating-indicator-fill {
            background: linear-gradient(90deg, #5c297c, #ffb736);
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        .rating-progress {
            background: #edf0f4;
            border-radius: 999px;
            height: 6px;
            overflow: hidden;
        }

        .rating-progress .progress-bar {
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        .rating-progress .progress-bar.bg-success {
            background: linear-gradient(90deg, #5ee033, #35c75a) !important;
        }

        .rating-progress .progress-bar.bg-info {
            background: linear-gradient(90deg, #5c297c, #ffb736) !important;
        }

        .rating-progress .progress-bar.bg-warning {
            background: linear-gradient(90deg, #ffb736, #ff8a00) !important;
        }

        .rating-progress .progress-bar.bg-danger {
            background: linear-gradient(90deg, #ff4b35, #ec0f5a) !important;
        }

        .rating-distribution-grid {
            row-gap: 4.25rem;
        }

        .rating-distribution-item {
            padding-bottom: 0.25rem;
        }

        .rating-summary-card {
            align-items: center;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.2), transparent 4.5rem),
                linear-gradient(135deg, #ffffff, #fbf7ff);
            border: 1px solid rgba(92, 41, 124, 0.1);
            border-radius: 1rem;
            box-shadow: 0 0.65rem 1.4rem rgba(58, 0, 80, 0.07);
            display: flex;
            gap: 0.85rem;
            height: 100%;
            padding: 1rem;
        }

        .rating-summary-icon {
            align-items: center;
            border-radius: 0.9rem;
            display: inline-flex;
            flex: 0 0 2.75rem;
            height: 2.75rem;
            justify-content: center;
            width: 2.75rem;
        }

        .rating-summary-icon i {
            font-size: 1.45rem;
        }

        .rating-summary-card.positive .rating-summary-icon {
            background: rgba(94, 224, 51, 0.15);
            color: #2aaa43;
        }

        .rating-summary-card.neutral .rating-summary-icon {
            background: rgba(255, 183, 54, 0.18);
            color: #d88400;
        }

        .rating-summary-card.concern .rating-summary-icon {
            background: rgba(236, 15, 90, 0.12);
            color: #ec0f5a;
        }

        .rating-summary-value {
            color: #3a0050;
            font-size: 1.45rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .rating-summary-label {
            color: #43546b;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            margin: 0;
        }

        .department-rating-indicator {
            background: #edf0f4;
            border-radius: 999px;
            height: 6px;
            margin: 0.35rem auto 0;
            max-width: 5.5rem;
            overflow: hidden;
            width: 100%;
        }

        .department-rating-indicator-fill {
            background: linear-gradient(90deg, #5c297c, #ffb736);
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        .department-card {
            border-left: 4px solid #696cff;
        }

        .report-card {
            border: 1px solid rgba(92, 41, 124, 0.1);
            overflow: hidden;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.1), transparent 13rem),
                linear-gradient(180deg, #ffffff, #fbf7ff);
            box-shadow: 0 1rem 2.25rem rgba(58, 0, 80, 0.1);
        }

        .report-card .card-header {
            background:
                radial-gradient(circle at 85% 15%, rgba(255, 183, 54, 0.22), transparent 0 7rem),
                linear-gradient(120deg, #3a0050, #5c297c, rgba(240, 24, 77, 0.76));
            border-bottom: 0;
            color: #ffffff;
        }

        .report-card .card-header h5,
        .report-card .card-header small,
        .report-card .card-header .card-title,
        .report-card .card-header .text-muted {
            color: #ffffff !important;
        }

        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }

        .filter-section {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 183, 54, 0.18);
            border-radius: 1.2rem;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.2), transparent 13rem),
                radial-gradient(circle at 78% 120%, rgba(236, 15, 90, 0.12), transparent 16rem),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(251, 247, 255, 0.92));
            box-shadow: 0 1.15rem 2.4rem rgba(58, 0, 80, 0.1);
        }

        .filter-section .card-body {
            position: relative;
            z-index: 1;
            padding: 1.3rem 1.45rem;
        }

        .filter-section .form-label,
        .filtered-result-panel .form-label {
            color: #3a0050;
            font-size: 0.74rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .filter-section .form-control,
        .filter-section .form-select,
        .filtered-result-panel .form-control,
        .filtered-result-panel .form-select {
            border-color: rgba(92, 41, 124, 0.18);
            color: #223045;
            font-weight: 700;
            box-shadow: 0 0.45rem 1rem rgba(44, 0, 63, 0.04);
        }

        .filter-section .btn-primary {
            min-height: 3rem;
            border: 0;
            background: linear-gradient(135deg, #3a0050, #5c297c);
            box-shadow: 0 0.8rem 1.55rem rgba(58, 0, 80, 0.18);
            color: #ffffff;
        }

        .filter-section .btn-primary:hover,
        .filter-section .btn-primary:focus {
            background: linear-gradient(135deg, #4d0a68, #7a2c90);
            color: #ffffff;
        }

        #departmentTableContainer {
            border: 1px solid rgba(232, 223, 240, 0.9);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        #departmentTable thead th {
            background:
                linear-gradient(180deg, rgba(250, 247, 253, 0.98), rgba(244, 238, 250, 0.96));
            color: #75849d;
        }

        #departmentTable tbody tr {
            background: rgba(255, 255, 255, 0.74);
        }

        #departmentTable tbody tr:hover {
            background:
                linear-gradient(90deg, rgba(92, 41, 124, 0.06), rgba(255, 183, 54, 0.07));
        }

        #departmentTable h6,
        #departmentTable .fw-medium,
        #departmentTable .small {
            color: #223045;
        }

        #departmentTable .avatar-initial {
            background: linear-gradient(135deg, #5c297c, #696cff) !important;
            color: #ffffff !important;
            min-width: 2.55rem;
            padding-inline: 0.45rem;
            font-weight: 900;
        }

        #departmentTable .btn-outline-primary {
            border-color: #5c297c;
            color: #5c297c;
            font-weight: 900;
        }

        #departmentTable .btn-outline-primary:hover,
        #departmentTable .btn-outline-primary:focus {
            background: #5c297c;
            border-color: #5c297c;
            color: #ffffff;
        }
    </style>
@endsection

@section('vendor-script')
    <script>
        // Enhanced functionality for the dashboard

        // Export functionality
        function exportData() {
            const filters = {
                department: '{{ $selectedDepartment }}',
                academic_year: '{{ $selectedAcademicYear }}',
                semester: '{{ $selectedSemester }}'
            };

            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Department,Faculty Count,Total Evaluations,Active Evaluations,Total Responses,Average Rating\n";

            @foreach($departmentBreakdown as $dept)
                csvContent += "{{ $dept['department'] }},{{ $dept['faculty_count'] }},{{ $dept['total_evaluations'] }},{{ $dept['active_evaluations'] }},{{ $dept['total_responses'] }},{{ $dept['average_rating'] }}\n";
            @endforeach

                                                                                            // Create and trigger download
                                                                                            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `evaluation_report_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Real-time updates
        function refreshData() {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    // Update only the metrics cards without full page reload
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    // Update metric values
                    document.querySelectorAll('.metric-card h3').forEach((element, index) => {
                        const newValue = newDoc.querySelectorAll('.metric-card h3')[index];
                        if (newValue && element.textContent !== newValue.textContent) {
                            element.style.animation = 'pulse 0.5s';
                            element.textContent = newValue.textContent;
                        }
                    });
                })
                .catch(error => console.log('Auto-refresh failed:', error));
        }

        // Form auto-submit on filter change
        document.querySelectorAll('select[name="department"], select[name="academic_year"], select[name="semester"], select[name="subject_type"]').forEach(select => {
            select.addEventListener('change', function () {
                // Add loading state
                const button = document.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                button.disabled = true;

                // Submit form
                this.form.submit();
            });
        });

        // Tooltips for metrics
        const tooltips = {
            'Total Faculties': 'Number of faculty members in the selected filters',
            'Total Responses': 'Total evaluation responses submitted by students',
            'Average Rating': 'Overall average effectiveness rating across all evaluations',
            'Courses Evaluated': 'Number of unique courses that have received evaluations'
        };

        // Add tooltips
        document.querySelectorAll('.metric-card p').forEach(element => {
            const text = element.textContent.trim();
            if (tooltips[text]) {
                element.setAttribute('title', tooltips[text]);
                element.style.cursor = 'help';
            }
        });

        // Progress bar animations
        function animateProgressBars() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                if (bar.dataset.progressAnimated === 'true') {
                    return;
                }

                const width = bar.style.width || bar.getAttribute('aria-valuenow') || '0%';
                bar.dataset.progressAnimated = 'true';
                bar.style.width = '0%';

                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-in-out';
                    bar.style.width = width;
                }, 100);
            });
        }

        function setupPagedList(container) {
            const pageSize = parseInt(container.getAttribute('data-page-size') || '10', 10);
            const items = Array.from(container.querySelectorAll('[data-paged-item]'));
            const footer = container.querySelector('[data-paged-list-footer]');
            if (!footer || items.length === 0 || items.length <= pageSize) {
                if (footer) {
                    footer.classList.add('d-none');
                }
                return;
            }

            const showingEl = footer.querySelector('[data-paged-list-showing]');
            const totalEl = footer.querySelector('[data-paged-list-total]');
            const prevBtn = footer.querySelector('[data-paged-list-prev]');
            const nextBtn = footer.querySelector('[data-paged-list-next]');
            let currentPage = 1;
            const totalPages = Math.ceil(items.length / pageSize);

            const renderPage = () => {
                const start = (currentPage - 1) * pageSize;
                const end = start + pageSize;
                items.forEach((item, index) => {
                    item.classList.toggle('d-none', index < start || index >= end);
                });
                showingEl.textContent = `${start + 1}-${Math.min(end, items.length)}`;
                totalEl.textContent = `${items.length}`;
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages;
            };

            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    renderPage();
                }
            });
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage += 1;
                    renderPage();
                }
            });

            renderPage();
        }

        // Initialize animations on load
        document.addEventListener('DOMContentLoaded', function () {
            animateProgressBars();
            document.querySelectorAll('[data-paged-list-container]').forEach(setupPagedList);
        });

        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);

        // Add CSS animations
        const reportsDynamicStyle = document.createElement('style');
        reportsDynamicStyle.textContent = `
                                                                                            @keyframes pulse {
                                                                                                0% { transform: scale(1); }
                                                                                                50% { transform: scale(1.05); }
                                                                                                100% { transform: scale(1); }
                                                                                            }

                                                                                            .progress-bar {
                                                                                                transition: width 0.8s ease-in-out;
                                                                                            }

                                                                                            .metric-card {
                                                                                                transition: all 0.3s ease;
                                                                                            }

                                                                                            .metric-card:hover {
                                                                                                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                                                                                            }
                                                                                        `;
        document.head.appendChild(reportsDynamicStyle);
    </script>
@endsection

@section('content')
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Post-Class Survey Analytics</h4>
                <p class="text-muted mb-0">Comprehensive dashboard for evaluation insights and reporting</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card mb-4 filter-section">
            <div class="card-body">
                <form method="GET" action="{{ route('reports') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        @if (!empty($isDepartmentScoped))
                            <input type="hidden" name="department" value="{{ $selectedDepartment }}">
                            <div class="form-control d-flex flex-wrap gap-1" style="min-height: 2.5rem;">
                                @forelse($departments as $dept)
                                    <span class="badge bg-label-secondary">{{ $dept }}</span>
                                @empty
                                    <span class="text-muted">No department</span>
                                @endforelse
                            </div>
                        @else
                            <select name="department" class="form-select">
                                <option value="all" {{ $selectedDepartment == 'all' ? 'selected' : '' }}>All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ $selectedDepartment == $dept ? 'selected' : '' }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <option value="all" {{ $selectedAcademicYear == 'all' ? 'selected' : '' }}>All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" {{ $selectedAcademicYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="all" {{ $selectedSemester == 'all' ? 'selected' : '' }}>All Semesters</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem }}" {{ $selectedSemester == $sem ? 'selected' : '' }}>
                                    {{ $sem }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subject Type</label>
                        <select name="subject_type" class="form-select">
                            <option value="all" {{ $selectedSubjectType == 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="major" {{ $selectedSubjectType == 'major' ? 'selected' : '' }}>Professional Course</option>
                            <option value="minor" {{ $selectedSubjectType == 'minor' ? 'selected' : '' }}>GenEd Course</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filtered Department Result --}}
        <div class="mb-4">
            <div class="card filtered-result-toggle-card">
                <div class="card-body">
                    <div>
                        <h5 class="filtered-result-title">
                            @if($selectedDepartment !== 'all')
                                {{ $selectedDepartment }} Department Performance
                            @else
                                Department Performance Breakdown
                            @endif
                        </h5>
                        <div class="filtered-result-subtitle">
                            <span class="filtered-result-chip">
                                <i class="bx bx-buildings"></i>
                                {{ $selectedDepartment !== 'all' ? $selectedDepartment : 'All Departments' }}
                            </span>
                            <span class="filtered-result-chip">
                                <i class="bx bx-calendar"></i>
                                {{ $selectedAcademicYear !== 'all' ? $selectedAcademicYear : 'All Years' }}
                            </span>
                            <span class="filtered-result-chip">
                                <i class="bx bx-bookmark"></i>
                                @if($selectedSemester === '1st')
                                    1st Semester
                                @elseif($selectedSemester === '2nd')
                                    2nd Semester
                                @elseif(strtolower((string) $selectedSemester) === 'summer')
                                    Summer
                                @else
                                    {{ $selectedSemester !== 'all' ? $selectedSemester : 'All Semesters' }}
                                @endif
                            </span>
                            <span class="filtered-result-chip">
                                <i class="bx bx-book-open"></i>
                                @if($selectedSubjectType === 'major')
                                    Professional Course
                                @elseif($selectedSubjectType === 'minor')
                                    GenEd Course
                                @else
                                    All Types
                                @endif
                            </span>
                        </div>
                    </div>
                    <button class="filtered-result-toggle-btn" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filteredDepartmentResult" aria-expanded="true"
                        aria-controls="filteredDepartmentResult" id="filteredResultToggle">
                        <i class="bx bx-table"></i>
                        <span data-toggle-label>Hide Filtered Result</span>
                        <i class="bx bx-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="filteredDepartmentResult">
                <div class="card report-card filtered-result-panel">
                    <div class="card-body" id="reportsDepartmentBreakdown">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="mb-1">Loading department performance...</h6>
                            <p class="text-muted mb-0">The report page is ready while this section loads.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Key Metrics Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100 metric-card-clickable" role="button" tabindex="0"
                    data-metric-card data-metric="total_faculties">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    <i class="bx bx-user-check bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0">{{ $metrics['total_faculties'] }}</h3>
                                <p class="text-muted mb-0">Total Faculties</p>
                            </div>
                        </div>
                        <div class="metric-card-footer">
                            <small class="text-success">
                                {{ $metrics['active_evaluations'] }} active evaluations
                            </small>
                            <span class="metric-view-indicator">
                                <i class="bx bx-show"></i> View details
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100 metric-card-clickable" role="button" tabindex="0"
                    data-metric-card data-metric="total_responses">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-success rounded">
                                    <i class="bx bx-bar-chart bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0">{{ $metrics['total_responses'] }}</h3>
                                <p class="text-muted mb-0">Total Responses</p>
                            </div>
                        </div>
                        <div class="metric-card-footer">
                            <small class="text-info">
                                {{ $metrics['responses_with_feedback'] }} with feedback
                            </small>
                            <span class="metric-view-indicator">
                                <i class="bx bx-show"></i> View details
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100 metric-card-clickable" role="button" tabindex="0"
                    data-metric-card data-metric="average_rating">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-warning rounded">
                                    <i class="bx bx-star bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0">{{ $metrics['average_rating'] }}</h3>
                                <p class="text-muted mb-0">Average Rating</p>
                            </div>
                        </div>
                        <div class="metric-card-footer">
                            <small class="text-muted">
                                Out of 4.0 scale
                            </small>
                            <span class="metric-view-indicator">
                                <i class="bx bx-show"></i> View details
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100 metric-card-clickable" role="button" tabindex="0"
                    data-metric-card data-metric="courses_evaluated">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-info rounded">
                                    <i class="bx bx-book bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0">{{ $metrics['courses_evaluated'] }}</h3>
                                <p class="text-muted mb-0">Courses Evaluated</p>
                            </div>
                        </div>
                        <div class="metric-card-footer">
                            <small class="text-muted invisible">
                                View
                            </small>
                            <span class="metric-view-indicator">
                                <i class="bx bx-show"></i> View details
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Rating Distribution --}}
            <div class="col-lg-8 mb-4">
                <div class="card h-100 report-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Overall Rating Distribution</h5>
                        <small class="text-muted">{{ $metrics['total_responses'] }} total responses</small>
                    </div>
                    <div class="card-body">
                        @if($metrics['total_responses'] > 0)
                            <div class="row rating-distribution-grid mt-8">
                                @foreach(['4' => ['Very Effective', 'success'], '3' => ['Effective', 'info'], '2' => ['Somewhat Effective', 'warning'], '1' => ['Not Effective', 'danger']] as $rating => $info)
                                    @php
                                        $count = $metrics['rating_distribution']->get($rating, 0);
                                        $percentage = ($count / $metrics['total_responses']) * 100;
                                    @endphp
                                    <div class="col-md-6 rating-distribution-item">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-medium">{{ $info[0] }}</span>
                                            <span class="text-muted">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="progress rating-progress mb-2">
                                            <div class="progress-bar bg-{{ $info[1] }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Quick Stats --}}
                            <div class="row g-3 mt-10">
                                <div class="col-md-4">
                                    <div class="rating-summary-card positive">
                                        <span class="rating-summary-icon">
                                            <i class="bx bx-trending-up"></i>
                                        </span>
                                        <div>
                                            <div class="rating-summary-value">
                                                {{ $metrics['rating_distribution']->get('4', 0) + $metrics['rating_distribution']->get('3', 0) }}
                                            </div>
                                            <p class="rating-summary-label">Positive Ratings</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rating-summary-card neutral">
                                        <span class="rating-summary-icon">
                                            <i class="bx bx-minus-circle"></i>
                                        </span>
                                        <div>
                                            <div class="rating-summary-value">{{ $metrics['rating_distribution']->get('2', 0) }}</div>
                                            <p class="rating-summary-label">Neutral Ratings</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rating-summary-card concern">
                                        <span class="rating-summary-icon">
                                            <i class="bx bx-error-circle"></i>
                                        </span>
                                        <div>
                                            <div class="rating-summary-value">{{ $metrics['rating_distribution']->get('1', 0) }}</div>
                                            <p class="rating-summary-label">Needs Improvement</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-bar-chart display-4 text-muted mb-3"></i>
                                <h6 class="mb-2">No Data Available</h6>
                                <p class="text-muted">No evaluation responses found for the selected filters.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="col-lg-4 mb-4">
                <div class="card h-100 report-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Evaluations</h5>
                    </div>
                    <div class="card-body p-0" id="reportsRecentResponses">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2 mb-0">Loading recent evaluations...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Faculty Performance Tables --}}
        <div id="reportsFacultyRatings">
            <div class="card report-card mb-4">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading faculty performance...</p>
                </div>
            </div>
        </div>

    {{-- Metric Details Modal --}}
    <div class="modal fade" id="metricDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="metricDetailsTitle">Metric Details</h5>
                        <small class="text-muted" id="metricDetailsSubtitle">Filtered report result</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="metricDetailsBody">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2 mb-0">Loading details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <small class="text-muted" id="metricDetailsInfo"></small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="metricDetailsPrev">
                            Previous
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="metricDetailsNext">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Faculty Modal --}}
    <div class="modal fade reports-faculty-modal" id="facultyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Faculty Members - <span id="modalDepartmentName"></span>
                        @if($selectedSubjectType !== 'all')
                            <span class="badge {{ $selectedSubjectType === 'major' ? 'bg-success' : 'bg-warning text-dark' }} ms-2">
                                {{ $selectedSubjectType === 'major' ? 'Professional Course' : 'GenEd Course' }} Only
                            </span>
                        @endif
                    </h5>
                    <button type="button" class="reports-faculty-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    {{-- Active filter summary --}}
                    <div class="reports-faculty-filter-summary d-flex flex-wrap align-items-center gap-2 py-2 px-3 mb-3" id="modalFilterSummary">
                        <small class="text-muted me-1"><i class="bx bx-filter-alt me-1"></i>Showing faculty with responses for:</small>
                        @if($selectedAcademicYear !== 'all')
                            <span class="badge bg-label-primary">{{ $selectedAcademicYear }}</span>
                        @else
                            <span class="badge bg-label-secondary">All Years</span>
                        @endif
                        @if($selectedSemester !== 'all')
                            <span class="badge bg-label-primary">{{ $selectedSemester }} Semester</span>
                        @else
                            <span class="badge bg-label-secondary">All Semesters</span>
                        @endif
                        @if($selectedSubjectType !== 'all')
                            <span class="badge {{ $selectedSubjectType === 'major' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $selectedSubjectType === 'major' ? 'Professional Course' : 'GenEd Course' }}
                            </span>
                        @else
                            <span class="badge bg-label-secondary">All Subject Types</span>
                        @endif
                        <span class="ms-auto text-muted small" id="modalFacultyCount"></span>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn btn-outline-primary" id="departmentExportBtn">
                            <i class="bx bx-download me-1"></i>Export Responses
                        </button>
                    </div>
                    {{-- Faculty Table Controls --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Show entries</label>
                            <select class="form-select form-select-sm" id="facultyPerPage">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Avg Rating</label>
                            <select class="form-select form-select-sm" id="facultyRatingFilter">
                                <option value="all">All</option>
                                <option value="very_effective">Very Effective</option>
                                <option value="effective">Effective</option>
                                <option value="somewhat_effective">Somewhat Effective</option>
                                <option value="not_effective">Not Effective</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select form-select-sm" id="facultyStatusFilter">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" id="facultySearch"
                                placeholder="Search faculty...">
                        </div>
                    </div>

                    {{-- Faculty Table Container --}}
                    <div id="facultyTableContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Faculty Pagination --}}
                    <div id="facultyPagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Department Export Modal --}}
    <div class="modal fade department-export-modal" id="departmentExportModal" tabindex="-1" aria-labelledby="departmentExportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="GET" id="departmentExportForm" action="{{ route('reports.department.export') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="departmentExportModalLabel">Export Responses</h5>
                        <button type="button" class="department-export-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="department" id="departmentExportDepartment">
                        <input type="hidden" name="subject_type" value="{{ $selectedSubjectType }}">
                        <input type="hidden" name="academic_year" value="{{ $selectedAcademicYear }}">
                        <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                        <div class="mb-3">
                            <label for="departmentExportStartDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="departmentExportStartDate" name="start_date">
                            <small class="text-muted">Leave blank to include all dates.</small>
                        </div>
                        <div class="mb-3">
                            <label for="departmentExportEndDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="departmentExportEndDate" name="end_date">
                        </div>
                        <div class="mb-3 d-none">
                            <label for="departmentExportAcademicYear" class="form-label">Academic Year</label>
                            <select class="form-select" id="departmentExportAcademicYear">
                                <option value="all" {{ $selectedAcademicYear === 'all' ? 'selected' : '' }}>All</option>
                                @if($selectedAcademicYear !== 'all')
                                    <option value="{{ $selectedAcademicYear }}" selected>{{ $selectedAcademicYear }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-0 d-none">
                            <label for="departmentExportSemester" class="form-label">Semester</label>
                            <select class="form-select" id="departmentExportSemester">
                                <option value="all" {{ $selectedSemester === 'all' ? 'selected' : '' }}>All</option>
                                @if($selectedSemester !== 'all')
                                    <option value="{{ $selectedSemester }}" selected>{{ $selectedSemester }}</option>
                                @endif
                            </select>
                        </div>
                        @if($selectedSubjectType !== 'all')
                            <div class="mt-3 department-export-filter-note">
                                <small>
                                    <i class="bx bx-filter-alt me-1"></i>
                                    Subject type filter active:
                                    <span class="badge">
                                        {{ $selectedSubjectType === 'major' ? 'Professional Course' : 'Minor Course' }} Only
                                    </span>
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex gap-2">
                             <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-download me-1"></i>Export Result
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced functionality for the dashboard
        let currentDepartment = '';
        const facultySortState = { key: 'name', direction: 'asc' };
        const facultyFilters = { rating: 'all', status: 'all' };
        const metricDetailsState = { metric: null, page: 1, perPage: 10 };

        // Export functionality
        function exportData() {
            const filters = {
                department: '{{ $selectedDepartment }}',
                academic_year: '{{ $selectedAcademicYear }}',
                semester: '{{ $selectedSemester }}'
            };

            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Department,Faculty Count,Total Evaluations,Active Evaluations,Total Responses,Average Rating\n";

            @foreach($departmentBreakdown as $dept)
                csvContent += "{{ $dept['department'] }},{{ $dept['faculty_count'] }},{{ $dept['total_evaluations'] }},{{ $dept['active_evaluations'] }},{{ $dept['total_responses'] }},{{ $dept['average_rating'] }}\n";
            @endforeach

                                                // Create and trigger download
                                                const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `evaluation_report_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Department table functionality
        function updatePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            window.location.href = url.toString();
        }

        function filterDepartments(searchTerm) {
            const rows = document.querySelectorAll('.department-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const departmentName = row.getAttribute('data-department').toLowerCase();
                if (departmentName.includes(searchTerm.toLowerCase())) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('departmentShowing').textContent = visibleCount;
        }

        function bindFilteredResultToggle() {
            const panel = document.getElementById('filteredDepartmentResult');
            const toggle = document.getElementById('filteredResultToggle');
            const label = toggle?.querySelector('[data-toggle-label]');

            if (!panel || !toggle || !label) {
                return;
            }

            panel.addEventListener('shown.bs.collapse', () => {
                label.textContent = 'Hide Filtered Result';
            });

            panel.addEventListener('hidden.bs.collapse', () => {
                label.textContent = 'Show Filtered Result';
            });
        }

        function bindMetricCards() {
            document.querySelectorAll('[data-metric-card]').forEach((card) => {
                const open = () => showMetricDetails(card.dataset.metric);
                card.addEventListener('click', open);
                card.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        open();
                    }
                });
            });

            document.getElementById('metricDetailsPrev')?.addEventListener('click', () => {
                if (metricDetailsState.page > 1) {
                    showMetricDetails(metricDetailsState.metric, metricDetailsState.page - 1);
                }
            });
            document.getElementById('metricDetailsNext')?.addEventListener('click', () => {
                showMetricDetails(metricDetailsState.metric, metricDetailsState.page + 1);
            });
        }

        function showMetricDetails(metric, page = 1) {
            if (!metric) {
                return;
            }

            metricDetailsState.metric = metric;
            metricDetailsState.page = page;

            const modalEl = document.getElementById('metricDetailsModal');
            const titleEl = document.getElementById('metricDetailsTitle');
            const subtitleEl = document.getElementById('metricDetailsSubtitle');
            const bodyEl = document.getElementById('metricDetailsBody');
            const infoEl = document.getElementById('metricDetailsInfo');

            if (titleEl) {
                titleEl.textContent = 'Loading details...';
            }
            if (subtitleEl) {
                subtitleEl.textContent = 'Using the current report filters';
            }
            if (infoEl) {
                infoEl.textContent = '';
            }
            if (bodyEl) {
                bodyEl.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Loading details...</p>
                    </div>
                `;
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).show();

            const url = new URL('{{ route('reports.metric.details') }}');
            url.searchParams.set('metric', metric);
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', metricDetailsState.perPage);
            url.searchParams.set('department', '{{ $selectedDepartment }}');
            url.searchParams.set('academic_year', '{{ $selectedAcademicYear }}');
            url.searchParams.set('semester', '{{ $selectedSemester }}');
            url.searchParams.set('subject_type', '{{ $selectedSubjectType }}');

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => renderMetricDetails(data))
                .catch((error) => {
                    if (titleEl) {
                        titleEl.textContent = 'Metric Details';
                    }
                    if (bodyEl) {
                        bodyEl.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bx bx-error text-danger mb-2" style="font-size: 2rem;"></i>
                                <h6 class="text-danger mb-2">Unable to load details</h6>
                                <p class="text-muted mb-0">${escapeMetricHtml(error.message || 'Please try again.')}</p>
                            </div>
                        `;
                    }
                });
        }

        function renderMetricDetails(data) {
            const titleEl = document.getElementById('metricDetailsTitle');
            const bodyEl = document.getElementById('metricDetailsBody');
            const infoEl = document.getElementById('metricDetailsInfo');
            const prevBtn = document.getElementById('metricDetailsPrev');
            const nextBtn = document.getElementById('metricDetailsNext');

            if (titleEl) {
                titleEl.textContent = data.title || 'Metric Details';
            }

            const columns = Array.isArray(data.columns) ? data.columns : [];
            const items = Array.isArray(data.items) ? data.items : [];
            const meta = data.meta || {};
            metricDetailsState.page = Number(meta.page || 1);

            if (!bodyEl) {
                return;
            }

            if (!items.length) {
                bodyEl.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bx bx-info-circle text-muted mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-2">No result found</h6>
                        <p class="text-muted mb-0">No data matched the current filters.</p>
                    </div>
                `;
            } else {
                bodyEl.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-hover metric-details-table mb-0">
                            <thead>
                                <tr>
                                    ${columns.map((column) => `<th>${escapeMetricHtml(column)}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map((item) => `
                                    <tr>
                                        ${(item.cells || []).map((cell) => `<td>${escapeMetricHtml(cell)}</td>`).join('')}
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            if (infoEl) {
                const total = Number(meta.total);
                if (Number.isFinite(total) && total > 0) {
                    infoEl.textContent = `Showing ${meta.from}-${meta.to} of ${total} results`;
                } else if (items.length) {
                    infoEl.textContent = `Showing ${meta.from}-${meta.to} results`;
                } else {
                    infoEl.textContent = 'No results';
                }
            }
            if (prevBtn) {
                prevBtn.disabled = Number(meta.page || 1) <= 1;
            }
            if (nextBtn) {
                nextBtn.disabled = Object.prototype.hasOwnProperty.call(meta, 'has_more')
                    ? !meta.has_more
                    : Number(meta.page || 1) >= Number(meta.last_page || 1);
            }
        }

        function escapeMetricHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                })[char] || char;
            });
        }

        // Faculty modal functionality
        function resetFacultyState() {
            facultySortState.key = 'name';
            facultySortState.direction = 'asc';
            facultyFilters.rating = 'all';
            facultyFilters.status = 'all';
            const ratingSelect = document.getElementById('facultyRatingFilter');
            const statusSelect = document.getElementById('facultyStatusFilter');
            if (ratingSelect) {
                ratingSelect.value = 'all';
            }
            if (statusSelect) {
                statusSelect.value = 'all';
            }
        }

        function showFacultyModal(department) {
            currentDepartment = department;
            document.getElementById('modalDepartmentName').textContent = department;
            const exportDepartmentInput = document.getElementById('departmentExportDepartment');
            const exportModalLabel = document.getElementById('departmentExportModalLabel');
            if (exportDepartmentInput) {
                exportDepartmentInput.value = department;
            }
            if (exportModalLabel) {
                exportModalLabel.textContent = `Export Responses - ${department}`;
            }

            const modal = new bootstrap.Modal(document.getElementById('facultyModal'));
            modal.show();

            resetFacultyState();
            loadFacultyData(department, 1, 10, '');
        }

        function loadFacultyData(department, page = 1, perPage = 10, search = '') {
            const url = new URL('{{ route("reports.department.faculties") }}');
            url.searchParams.set('department', department);
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('search', search);
            url.searchParams.set('rating_filter', facultyFilters.rating);
            url.searchParams.set('status_filter', facultyFilters.status);
            url.searchParams.set('sort_key', facultySortState.key);
            url.searchParams.set('sort_dir', facultySortState.direction);
            url.searchParams.set('academic_year', '{{ $selectedAcademicYear }}');
            url.searchParams.set('semester', '{{ $selectedSemester }}');
            url.searchParams.set('subject_type', '{{ $selectedSubjectType }}');

            // Show loading
            document.getElementById('facultyTableContainer').innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading faculty data...</p>
                    </div>
                `;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers.get('content-type'));

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    // Check if the response has the expected structure
                    if (!data || typeof data !== 'object') {
                        throw new Error('Invalid response: not an object');
                    }

                    // Handle error responses
                    if (data.success === false) {
                        throw new Error(data.message || data.error || 'Server returned an error');
                    }

                    // Check for required properties
                    if (!data.hasOwnProperty('html')) {
                        throw new Error('Invalid response: missing html property');
                    }

                    if (!data.hasOwnProperty('pagination')) {
                        throw new Error('Invalid response: missing pagination property');
                    }

                    // Set the HTML content
                    document.getElementById('facultyTableContainer').innerHTML = data.html;

                    // Handle pagination - check if there's pagination content
                    const paginationContainer = document.getElementById('facultyPagination');
                    if (data.pagination && data.pagination.trim() !== '') {
                        paginationContainer.innerHTML = data.pagination;
                        paginationContainer.style.display = 'block';
                    } else {
                        // Hide pagination if no pagination needed (single page)
                        paginationContainer.innerHTML = '';
                        paginationContainer.style.display = 'none';
                    }

                    // Update pagination info if meta data is available
                    if (data.meta) {
                        updatePaginationInfo(data.meta);
                        // Update faculty count in the filter summary bar
                        const countEl = document.getElementById('modalFacultyCount');
                        if (countEl) {
                            countEl.textContent = `${data.meta.total} faculty member${data.meta.total !== 1 ? 's' : ''} found`;
                        }
                    }

                    // Bind pagination click events
                    bindPaginationEvents();
                    bindFacultySortHeaders();

                    // Success feedback
                    console.log('Faculty data loaded successfully');

                })
                .catch(error => {
                    console.error('Error loading faculty data:', error);

                    // Show detailed error message
                    document.getElementById('facultyTableContainer').innerHTML = `
                        <div class="text-center py-4">
                            <i class="bx bx-error text-danger mb-2" style="font-size: 2rem;"></i>
                            <h6 class="text-danger mb-2">Error Loading Faculty Data</h6>
                            <p class="text-muted mb-2">${error.message}</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadFacultyData('${department}', ${page}, ${perPage}, '${search}')">
                                <i class="bx bx-refresh me-1"></i>Retry
                            </button>
                        </div>
                    `;

                    // Hide pagination on error
                    document.getElementById('facultyPagination').style.display = 'none';
                });
        }

        function bindFacultySortHeaders() {
            const headers = Array.from(document.querySelectorAll('#facultyTable thead th[data-sort-key]'));
            if (headers.length === 0) {
                return;
            }

            headers.forEach((header) => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sortKey;
                    if (!sortKey) {
                        return;
                    }

                    if (facultySortState.key === sortKey) {
                        facultySortState.direction = facultySortState.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        facultySortState.key = sortKey;
                        facultySortState.direction = 'asc';
                    }

                    updateFacultySortIndicators(headers);
                    const perPage = document.getElementById('facultyPerPage').value || 10;
                    const search = document.getElementById('facultySearch').value || '';
                    loadFacultyData(currentDepartment, 1, perPage, search);
                });
            });

            updateFacultySortIndicators(headers);
        }

        function updateFacultySortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';

                if (header.dataset.sortKey === facultySortState.key) {
                    const directionClass = facultySortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
                    header.classList.add(directionClass);
                    header.dataset.sortState = facultySortState.direction;
                }
            });
        }

        // Helper function to update pagination info
        function updatePaginationInfo(meta) {
            const infoElement = document.querySelector('#facultyPagination .pagination-info');
            if (infoElement && meta.total !== undefined) {
                const from = meta.from || 0;
                const to = meta.to || 0;
                const total = meta.total || 0;

                infoElement.textContent = `Showing ${from} to ${to} of ${total} entries`;
            }
        }

        // Bind pagination events
        function bindPaginationEvents() {
            const paginationLinks = document.querySelectorAll('#facultyPagination .pagination a');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Extract page number from URL or data attribute
                    let page = 1;
                    if (this.href) {
                        const url = new URL(this.href);
                        page = url.searchParams.get('page') || 1;
                    } else if (this.dataset.page) {
                        page = this.dataset.page;
                    }

                    const perPage = document.getElementById('facultyPerPage').value;
                    const search = document.getElementById('facultySearch').value;

                    // Load the new page
                    loadFacultyData(currentDepartment, page, perPage, search);
                });
            });
        }

        function bindDepartmentExportButton() {
            const button = document.getElementById('departmentExportBtn');
            const departmentInput = document.getElementById('departmentExportDepartment');
            const academicYearInput = document.getElementById('departmentExportAcademicYear');
            const semesterInput = document.getElementById('departmentExportSemester');
            const modalTitle = document.getElementById('departmentExportModalLabel');
            const exportModalEl = document.getElementById('departmentExportModal');
            const facultyModalEl = document.getElementById('facultyModal');
            if (!button || !departmentInput) {
                return;
            }

            button.addEventListener('click', () => {
                if (!currentDepartment) {
                    return;
                }
                departmentInput.value = currentDepartment;
                if (academicYearInput) {
                    academicYearInput.value = '{{ $selectedAcademicYear }}';
                }
                if (semesterInput) {
                    semesterInput.value = '{{ $selectedSemester }}';
                }
                if (modalTitle) {
                    modalTitle.textContent = `Export Responses - ${currentDepartment}`;
                }
                if (exportModalEl) {
                    const exportModal = bootstrap.Modal.getOrCreateInstance(exportModalEl, {
                        backdrop: false,
                        focus: true,
                    });
                    exportModal.show();
                }
            });

            if (exportModalEl) {
                exportModalEl.addEventListener('hidden.bs.modal', () => {
                    if (facultyModalEl && facultyModalEl.classList.contains('show')) {
                        document.body.classList.add('modal-open');
                    }
                });
            }
        }

        // Faculty pagination function for direct page calls
        function goToFacultyPage(page) {
            const perPage = document.getElementById('facultyPerPage').value || 10;
            const search = document.getElementById('facultySearch').value || '';
            loadFacultyData(currentDepartment, page, perPage, search);
        }

        function loadReportLazySections() {
            const departmentContainer = document.getElementById('reportsDepartmentBreakdown');
            const recentContainer = document.getElementById('reportsRecentResponses');
            const facultyContainer = document.getElementById('reportsFacultyRatings');

            if (!departmentContainer && !recentContainer && !facultyContainer) {
                return;
            }

            const url = new URL('{{ route('reports.lazy.sections') }}');
            url.searchParams.set('department', '{{ $selectedDepartment }}');
            url.searchParams.set('academic_year', '{{ $selectedAcademicYear }}');
            url.searchParams.set('semester', '{{ $selectedSemester }}');
            url.searchParams.set('subject_type', '{{ $selectedSubjectType }}');
            url.searchParams.set('per_page', '{{ $perPage }}');

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }

                    return response.json();
                })
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unable to load report sections.');
                    }

                    if (departmentContainer) {
                        departmentContainer.innerHTML = data.department_html || '';
                    }
                    if (recentContainer) {
                        recentContainer.innerHTML = data.recent_html || '';
                    }
                    if (facultyContainer) {
                        facultyContainer.innerHTML = data.faculty_html || '';
                    }
                })
                .catch((error) => {
                    const fallback = `
                        <div class="text-center py-5">
                            <i class="bx bx-error text-danger mb-2" style="font-size: 2rem;"></i>
                            <h6 class="text-danger mb-2">Unable to load this section</h6>
                            <p class="text-muted mb-0">${escapeMetricHtml(error.message || 'Please refresh and try again.')}</p>
                        </div>
                    `;

                    if (departmentContainer) {
                        departmentContainer.innerHTML = fallback;
                    }
                    if (recentContainer) {
                        recentContainer.innerHTML = fallback;
                    }
                    if (facultyContainer) {
                        facultyContainer.innerHTML = `<div class="card report-card mb-4"><div class="card-body">${fallback}</div></div>`;
                    }
                });
        }

        // Faculty search and pagination handlers
        document.addEventListener('DOMContentLoaded', function () {
            // Faculty per page change
            document.getElementById('facultyPerPage').addEventListener('change', function () {
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, this.value, document.getElementById('facultySearch').value);
                }
            });

            document.getElementById('facultyRatingFilter').addEventListener('change', function () {
                facultyFilters.rating = this.value || 'all';
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, document.getElementById('facultySearch').value);
                }
            });

            document.getElementById('facultyStatusFilter').addEventListener('change', function () {
                facultyFilters.status = this.value || 'all';
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, document.getElementById('facultySearch').value);
                }
            });

            // Faculty search with debouncing
            let searchTimeout;
            document.getElementById('facultySearch').addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (currentDepartment) {
                        loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, this.value);
                    }
                }, 500);
            });
            bindDepartmentExportButton();
            bindFilteredResultToggle();
            bindMetricCards();
            loadReportLazySections();
        });

        // Form auto-submit on filter change
        document.querySelectorAll('select[name="department"], select[name="academic_year"], select[name="semester"], select[name="subject_type"]').forEach(select => {
            select.addEventListener('change', function () {
                // Add loading state
                const button = document.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                button.disabled = true;

                // Submit form
                this.form.submit();
            });
        });

        // Progress bar animations
        function animateProgressBars() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                if (bar.dataset.progressAnimated === 'true') {
                    return;
                }

                const width = bar.style.width || bar.getAttribute('aria-valuenow') || '0%';
                bar.dataset.progressAnimated = 'true';
                bar.style.width = '0%';

                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-in-out';
                    bar.style.width = width;
                }, 100);
            });
        }

        // Initialize animations on load
        document.addEventListener('DOMContentLoaded', function () {
            animateProgressBars();
        });

        // Auto-refresh every 5 minutes (only if modal is not open)
        setInterval(function () {
            const facultyModal = document.getElementById('facultyModal');
            const metricModal = document.getElementById('metricDetailsModal');
            if (!facultyModal.classList.contains('show') && !metricModal.classList.contains('show')) {
                location.reload();
            }
        }, 300000);

        // Utility function to decode HTML entities
        function decodeHtmlEntities(str) {
            const textArea = document.createElement('textarea');
            textArea.innerHTML = str;
            return textArea.value;
        }

        // Alternative method for handling JSON response with escaped HTML
        function parseJsonResponse(data) {
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    return null;
                }
            }

            if (data.html) {
                // Decode HTML entities if they exist
                data.html = decodeHtmlEntities(data.html);
            }

            if (data.pagination) {
                // Decode pagination HTML entities if they exist
                data.pagination = decodeHtmlEntities(data.pagination);
            }

            return data;
        }

        // Enhanced error handling
        window.addEventListener('error', function (e) {
            console.error('JavaScript error:', e.error);
        });

        // Add CSS animations and styles
        const style = document.createElement('style');
        style.textContent = `
                                                @keyframes pulse {
                                                    0% { transform: scale(1); }
                                                    50% { transform: scale(1.05); }
                                                    100% { transform: scale(1); }
                                                }

                                                @keyframes fadeIn {
                                                    from { opacity: 0; }
                                                    to { opacity: 1; }
                                                }

                                                .progress-bar {
                                                    transition: width 0.8s ease-in-out;
                                                }

                                                .metric-card {
                                                    transition: all 0.3s ease;
                                                }

                                                .metric-card:hover {
                                                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                                                }

                                                .department-row:hover {
                                                    background-color: #f8f9fa;
                                                }

                                                .modal-xl {
                                                    max-width: 1200px;
                                                }

                                                .table-responsive {
                                                    animation: fadeIn 0.3s ease-in-out;
                                                }

                                                .faculty-modal-table thead th.sortable {
                                                    cursor: pointer;
                                                    user-select: none;
                                                }

                                                #facultyModal.modal {
                                                    z-index: 1055;
                                                }

                                                #departmentExportModal.modal {
                                                    z-index: 1070;
                                                }

                                                .modal-backdrop {
                                                    z-index: 1050;
                                                }

                                                .faculty-sort-wrapper {
                                                    display: inline-flex;
                                                    align-items: center;
                                                    gap: 0.35rem;
                                                }

                                                .faculty-sort-indicator {
                                                    display: inline-flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    width: 12px;
                                                    color: #94a3b8;
                                                    font-size: 0.65rem;
                                                }

                                                .faculty-sort-indicator i {
                                                    display: none;
                                                }

                                                .faculty-modal-table thead th.sorted-asc .faculty-sort-indicator,
                                                .faculty-modal-table thead th.sorted-desc .faculty-sort-indicator {
                                                    color: #1d4ed8;
                                                }

                                                .faculty-modal-table thead th.sorted-asc .faculty-sort-indicator .icon-up,
                                                .faculty-modal-table thead th.sorted-desc .faculty-sort-indicator .icon-down {
                                                    display: inline-flex;
                                                }

                                                .pagination {
                                                    justify-content: center;
                                                }

                                                .pagination .page-link {
                                                    transition: all 0.2s ease;
                                                }

                                                .pagination .page-link:hover {
                                                    transform: translateY(-1px);
                                                }

                                                .spinner-border-sm {
                                                    width: 1rem;
                                                    height: 1rem;
                                                }

                                                .loading-overlay {
                                                    position: relative;
                                                }

                                                .loading-overlay::after {
                                                    content: '';
                                                    position: absolute;
                                                    top: 0;
                                                    left: 0;
                                                    right: 0;
                                                    bottom: 0;
                                                    background: rgba(255, 255, 255, 0.8);
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    z-index: 10;
                                                }
                                            `;
        document.head.appendChild(style);

        // Debug function to check response format
        function debugResponse(response) {
            console.log('Response type:', typeof response);
            console.log('Response content:', response);

            if (typeof response === 'string') {
                try {
                    const parsed = JSON.parse(response);
                    console.log('Parsed JSON:', parsed);
                    return parsed;
                } catch (e) {
                    console.error('JSON parse error:', e);
                    return null;
                }
            }

            return response;
        }
    </script>
@endsection
