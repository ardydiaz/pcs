@extends('layouts/contentNavbarLayout')

@section('title', 'Department Org Chart')

@section('page-style')
    <style>
        :root {
            --org-purple: #5c297c;
            --org-purple-dark: #3a0050;
            --org-purple-midnight: #2b003f;
            --org-gold: #ffb736;
            --org-yellow: #ffd400;
            --org-calm-blue: #13a8b5;
            --org-fresh-green: #b9d63f;
            --org-flame-red: #f71920;
            --org-blazing-pink: #ed145b;
            --org-muted: #64748b;
            --org-line: #cbd5e1;
        }

        .org-page {
            max-width: 100%;
            position: relative;
        }

        .org-hero {
            border: 0;
            border-radius: 1.25rem;
            background:
                radial-gradient(circle at 91% 86%, rgba(255, 183, 54, 0.18) 0 7rem, transparent 7.1rem),
                linear-gradient(112deg, transparent 0 71%, rgba(255, 183, 54, 0.78) 71.2% 79%, transparent 79.2%),
                linear-gradient(115deg, transparent 0 66%, rgba(237, 20, 91, 0.78) 66.2% 83%, transparent 83.2%),
                linear-gradient(90deg, var(--org-purple-midnight), var(--org-purple-dark) 45%, #5c1f75 72%, #8a3d82);
            color: #ffffff;
            box-shadow: 0 1.25rem 3rem rgba(43, 0, 63, 0.22);
            overflow: hidden;
            position: relative;
        }

        .org-hero::before,
        .org-hero::after {
            content: "";
            position: absolute;
            pointer-events: none;
        }

        .org-hero::before {
            right: 7.2rem;
            top: -8.5rem;
            width: 18rem;
            height: 18rem;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 183, 54, 0.55), rgba(237, 20, 91, 0.55));
            opacity: 0.9;
        }

        .org-hero::after {
            right: 2rem;
            bottom: -6.5rem;
            width: 12.5rem;
            height: 12.5rem;
            border-radius: 50%;
            background: rgba(255, 183, 54, 0.08);
            border: 1px solid rgba(255, 183, 54, 0.35);
        }

        .org-hero h4,
        .org-hero p {
            color: inherit;
        }

        .org-hero .card-body {
            position: relative;
            z-index: 1;
        }

        .org-hero-eyebrow {
            color: var(--org-gold);
            font-size: 0.74rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .org-hero-title {
            font-size: clamp(1.75rem, 2vw, 2.35rem);
            font-weight: 900;
            letter-spacing: 0;
        }

        .org-hero-line {
            width: 5.5rem;
            height: 0.22rem;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--org-gold), var(--org-purple));
        }

        .org-hero-pill {
            border: 1px solid rgba(255, 183, 54, 0.35);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-weight: 800;
            padding: 0.55rem 0.85rem;
            backdrop-filter: blur(8px);
        }

        .org-filter-card,
        .org-chart-card {
            border: 1px solid rgba(92, 41, 124, 0.12);
            border-radius: 1.1rem;
            box-shadow: 0 0.9rem 2.4rem rgba(43, 0, 63, 0.09);
            overflow: hidden;
        }

        .org-filter-card {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.34), transparent 18rem),
                radial-gradient(circle at 0% 100%, rgba(92, 41, 124, 0.16), transparent 16rem),
                linear-gradient(135deg, #fffaf0, #f7efff 52%, #eefcff);
        }

        .org-chart-card {
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.22), transparent 20rem),
                radial-gradient(circle at 0% 0%, rgba(58, 0, 80, 0.18), transparent 18rem),
                linear-gradient(135deg, #f7f1ff, #fffaf0 52%, #ffffff);
        }

        .org-filter-card .card-body,
        .org-chart-card .card-body {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.58), rgba(255, 255, 255, 0.82));
            backdrop-filter: blur(6px);
        }

        .org-filter-label {
            color: var(--org-purple);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .org-access-note {
            color: var(--org-muted);
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 0.45rem;
        }

        .org-filter-action {
            align-self: flex-start;
            padding-top: 2.55rem;
        }

        .org-primary-btn {
            background: linear-gradient(135deg, var(--org-purple-dark), var(--org-purple));
            border: 0;
            color: #ffffff;
            font-weight: 800;
            border-radius: 0.75rem;
            box-shadow: 0 0.7rem 1.2rem rgba(92, 41, 124, 0.2);
        }

        .org-primary-btn:hover,
        .org-primary-btn:focus {
            background: linear-gradient(135deg, var(--org-gold), var(--org-yellow));
            color: var(--org-purple-dark);
        }

        .org-clean-btn {
            background: rgba(255, 183, 54, 0.2);
            border: 1px solid rgba(255, 183, 54, 0.55);
            color: var(--org-purple-dark);
            font-weight: 800;
            border-radius: 0.75rem;
        }

        .org-clean-btn:hover,
        .org-clean-btn:focus {
            background: var(--org-gold);
            border-color: var(--org-gold);
            color: var(--org-purple-dark);
        }

        .org-alert {
            border: 1px solid rgba(92, 41, 124, 0.18);
            border-left: 0.35rem solid var(--org-purple);
            border-radius: 0.85rem;
            background: #fffaf0;
            color: #2f3b52;
            box-shadow: 0 0.6rem 1.6rem rgba(58, 0, 80, 0.08);
        }

        .org-chart-scroll {
            overflow-x: auto;
            padding: 1.4rem 0.25rem 0.75rem;
        }

        .org-chart {
            min-width: min(100%, 960px);
            text-align: center;
        }

        .org-level {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
        }

        .org-level-label {
            color: var(--org-purple);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 0.9rem;
        }

        .org-connector {
            width: 2px;
            height: 2rem;
            margin: 0.45rem auto;
            background: linear-gradient(var(--org-gold), var(--org-purple-dark));
            position: relative;
        }

        .org-connector::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -0.2rem;
            width: 0.55rem;
            height: 0.55rem;
            border-right: 2px solid var(--org-purple-dark);
            border-bottom: 2px solid var(--org-purple-dark);
            transform: translateX(-50%) rotate(45deg);
            background: #ffffff;
        }

        .org-faculty-row {
            border-top: 2px dashed rgba(92, 41, 124, 0.2);
            padding-top: 1.35rem;
            margin-top: 0.25rem;
            position: relative;
            gap: 2.4rem 1rem;
        }

        .org-faculty-row .org-node {
            overflow: visible;
        }

        .org-faculty-row .org-node::after {
            content: "";
            position: absolute;
            left: 50%;
            top: -1.35rem;
            width: 2px;
            height: 1.35rem;
            background: linear-gradient(180deg, rgba(92, 41, 124, 0.42), rgba(255, 183, 54, 0.72));
            transform: translateX(-50%);
            z-index: 0;
        }

        .org-node {
            width: min(15.5rem, 100%);
            border: 1px solid rgba(58, 0, 80, 0.18);
            border-radius: 1.05rem;
            background:
                linear-gradient(180deg, #ffffff, #fbf8ff);
            padding: 1rem;
            box-shadow: 0 0.9rem 1.8rem rgba(43, 0, 63, 0.12);
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .org-node::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 5rem;
            border-radius: 1.05rem 1.05rem 0 0;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.22), transparent 7rem),
                linear-gradient(135deg, var(--org-purple-dark), var(--org-purple));
            border-bottom: 0.22rem solid var(--org-gold);
            z-index: 0;
        }

        .org-node > * {
            position: relative;
            z-index: 1;
        }

        .org-node:hover {
            transform: translateY(-3px);
            box-shadow: 0 1.2rem 2.2rem rgba(43, 0, 63, 0.12);
        }

        .org-node--dean {
            border-color: rgba(255, 183, 54, 0.72);
            background:
                linear-gradient(180deg, #ffffff, #fffaf0);
            box-shadow: 0 1rem 2.1rem rgba(58, 0, 80, 0.14);
        }

        .org-avatar {
            width: 5.4rem;
            height: 5.4rem;
            margin: 0.2rem auto 0.9rem;
            border-radius: 50%;
            padding: 0.28rem;
            background: linear-gradient(135deg, var(--org-gold), #ffffff 42%, var(--org-purple));
            box-shadow: 0 0.75rem 1.2rem rgba(58, 0, 80, 0.22);
        }

        .org-avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #ffffff;
            color: var(--org-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-weight: 900;
            font-size: 1.25rem;
        }

        .org-avatar-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .org-name {
            color: var(--org-purple-dark);
            font-size: 0.95rem;
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 0.25rem;
        }

        .org-title {
            color: var(--org-purple);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.35rem;
        }

        .org-department {
            border-radius: 0.7rem;
            background: linear-gradient(135deg, rgba(58, 0, 80, 0.06), rgba(255, 183, 54, 0.16));
            color: #2f3b52;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.3;
            margin: 0.55rem 0;
            padding: 0.45rem 0.55rem;
            border: 1px solid rgba(58, 0, 80, 0.12);
        }

        .org-department-label {
            color: #8a9bb3;
            display: block;
            font-size: 0.62rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
        }

        .org-meta {
            color: var(--org-muted);
            font-size: 0.78rem;
            line-height: 1.35;
        }

        .org-summary-pill {
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255, 183, 54, 0.2), rgba(92, 41, 124, 0.08));
            color: var(--org-purple-dark);
            padding: 0.45rem 0.8rem;
            font-weight: 800;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 183, 54, 0.35);
        }

        .org-empty {
            border: 1px dashed #d9e1ec;
            border-radius: 1rem;
            background:
                radial-gradient(circle at 50% 0%, rgba(255, 183, 54, 0.12), transparent 12rem),
                #fbfcff;
            color: var(--org-muted);
            padding: 2.5rem 1rem;
            text-align: center;
        }

        .org-select-card {
            border: 1px solid rgba(92, 41, 124, 0.1);
            border-radius: 0.9rem;
            background: rgba(255, 255, 255, 0.72);
            padding: 1rem;
        }

        .org-chart-heading {
            border-bottom: 1px solid rgba(92, 41, 124, 0.1);
            background:
                linear-gradient(90deg, rgba(58, 0, 80, 0.08), rgba(255, 183, 54, 0.1), transparent);
            border-radius: 0.9rem;
            padding-bottom: 1rem;
            padding: 1rem;
        }

        .org-selected-name {
            color: var(--org-purple-dark);
            font-weight: 900;
        }

        @media (max-width: 991.98px) {
            .org-filter-action {
                padding-top: 0;
            }
        }

        @media print {
            .layout-menu,
            .layout-navbar,
            .org-no-print,
            .content-footer {
                display: none !important;
            }

            .layout-page,
            .content-wrapper,
            .container-xxl {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .org-chart-card {
                box-shadow: none;
                border: 0;
            }

            .org-chart-scroll {
                overflow: visible;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid org-page">
        @if (session('success'))
            <div class="alert org-alert org-no-print mb-4" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card org-hero mb-4">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="org-hero-eyebrow mb-2">Department Structure</div>
                    <h4 class="org-hero-title mb-2">Department Organization Chart</h4>
                    <div class="org-hero-line mb-3"></div>
                    <p class="mb-0">View the Dean to Faculty structure for each official department.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="org-hero-pill">
                        <i class="bx bx-building-house me-1"></i> {{ $selectedDepartment ?: 'No Department' }}
                    </span>
                    <span class="org-hero-pill">
                        <i class="bx bx-shield-quarter me-1"></i> {{ $isAdmin ? 'Admin View' : 'Department View' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card org-filter-card mb-4 org-no-print">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <form method="GET" action="{{ route('department-org-chart') }}" class="col-lg-{{ $isAdmin ? '9' : '12' }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-8">
                                <div class="org-select-card">
                                    <label for="department" class="org-filter-label mb-2">Department</label>
                                    <select id="department" name="department" class="form-select">
                                        @forelse ($departmentOptions as $department)
                                            <option value="{{ $department }}" @selected($department === $selectedDepartment)>
                                                {{ $department }}
                                            </option>
                                        @empty
                                            <option value="">No department available</option>
                                        @endforelse
                                    </select>
                                    <div class="org-access-note">
                                        @if ($isAdmin)
                                            Admin access: you can view every official department.
                                        @else
                                            Department access: you can view only your assigned department.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 org-filter-action">
                                <button type="submit" class="btn org-primary-btn w-100">
                                    <i class="bx bx-filter-alt me-1"></i> View Org Chart
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($isAdmin)
                        <form method="POST"
                              action="{{ route('department-org-chart.clean') }}"
                              class="col-lg-3 org-filter-action"
                              onsubmit="return confirm('Clean old department labels into the official department list? Unknown values will be kept unchanged.');">
                            @csrf
                            <button type="submit" class="btn org-clean-btn w-100">
                                <i class="bx bx-brush me-1"></i> Clean Old Departments
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card org-chart-card">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4 org-chart-heading">
                    <div>
                        <div class="org-filter-label mb-1">Selected Department</div>
                        <h5 class="mb-0 org-selected-name">{{ $selectedDepartment ?: 'No Department Selected' }}</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="org-summary-pill">{{ $deans->count() }} Dean{{ $deans->count() === 1 ? '' : 's' }}</span>
                        <span class="org-summary-pill">{{ $facultyMembers->count() }} Faculty</span>
                    </div>
                </div>

                @if (!$selectedDepartment)
                    <div class="org-empty">
                        <h5 class="mb-1">No Department Found</h5>
                        <p class="mb-0">Add department values to users or faculty records to generate an org chart.</p>
                    </div>
                @elseif ($deans->isEmpty() && $facultyMembers->isEmpty())
                    <div class="org-empty">
                        <h5 class="mb-1">No Dean or Faculty Found</h5>
                        <p class="mb-0">This chart only includes records with job title containing Dean or Faculty.</p>
                    </div>
                @else
                    <div class="org-chart-scroll">
                        <div class="org-chart">
                            <div class="org-level-label">Dean</div>
                            <div class="org-level">
                                @forelse ($deans as $person)
                                    @include('content.organization.partials.org-person-card', ['person' => $person, 'type' => 'dean'])
                                @empty
                                    <div class="org-node org-node--dean">
                                        <div class="org-avatar">
                                            <div class="org-avatar-inner">
                                                <i class="bx bx-building-house"></i>
                                            </div>
                                        </div>
                                        <div class="org-name">{{ $selectedDepartment }}</div>
                                        <div class="org-title">No Dean Assigned</div>
                                        <div class="org-meta">Faculty are shown below this department node.</div>
                                    </div>
                                @endforelse
                            </div>

                            @if ($facultyMembers->isNotEmpty())
                                <div class="org-connector"></div>
                                <div class="org-level-label">Faculty</div>
                                <div class="org-level org-faculty-row">
                                    @foreach ($facultyMembers as $person)
                                        @include('content.organization.partials.org-person-card', ['person' => $person, 'type' => 'faculty'])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
