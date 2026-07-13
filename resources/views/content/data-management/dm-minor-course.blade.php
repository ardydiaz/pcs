@extends('layouts/contentNavbarLayout')

@section('title', 'Tables - Basic Tables')

@include('content.data-management.partials.courses.page-style')

{{-- DATATABLE CSS --}}
@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection
@section('page-style')
    <style>
        .btn-tab {
            background: transparent;
            border: 2px solid transparent;
            color: #697a8d;
            transition: all 0.3s ease;
        }

        .btn-tab.active {
            color: #ffb736 !important;
            border-color: #ffb736 !important;
            background: rgba(255, 183, 54, 0.08) !important;
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
        }

        .course-handler-btn.is-empty {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            box-shadow: none;
            cursor: not-allowed;
        }

        .course-handlers-modal {
            border: 0;
            overflow: hidden;
        }



        .modal-header.course-handlers-modal-header .modal-title,
        .modal-header.course-handlers-modal-header .course-handlers-subtitle {
            color: #ffffff;
        }

        .course-handlers-close {
            width: 2rem;
            height: 2rem;
            flex: 0 0 auto;
            margin-left: auto;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            line-height: 1;
            padding: 0;
            transition: background-color 0.18s ease, transform 0.18s ease;
        }

        .course-handlers-close:hover,
        .course-handlers-close:focus {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .course-handlers-eyebrow {
            color: #ffcf45;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .course-handlers-summary,
        .course-handler-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .course-handlers-summary {
            margin-bottom: 1rem;
        }

        .course-handlers-summary>div,
        .course-handler-field {
            border: 1px solid #eef2f6;
            border-radius: 0.75rem;
            background: #fbfcff;
            padding: 0.85rem 1rem;
            min-width: 0;
        }

        .course-handler-fields {
            grid-template-columns: repeat(4, minmax(0, 1fr));
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

        .course-handlers-summary strong,
        .course-handler-field strong {
            display: block;
            color: #2f3b52;
            font-size: 0.92rem;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .course-handlers-list {
            display: grid;
            gap: 0.85rem;
            max-height: min(58vh, 34rem);
            overflow-y: auto;
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
        }

        .course-handler-card p {
            margin: 0;
            color: #64748b;
            font-size: 0.82rem;
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

        .course-handlers-empty {
            border: 1px dashed #d9e1ec;
            border-radius: 0.85rem;
            padding: 2rem 1rem;
            text-align: center;
            color: #64748b;
            background: #fbfcff;
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

        @media (max-width: 768px) {
            .course-handlers-summary,
            .course-handler-fields {
                grid-template-columns: 1fr;
            }

            .course-handler-card-header {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $generateAcademicYearOptions = function ($startYear = 2025, $minimumYears = 10, $futureBuffer = 5) {
            $currentYear = (int) date('Y');
            $endYear = max($startYear + $minimumYears - 1, $currentYear + $futureBuffer);
            $years = [];
            for ($year = $startYear; $year <= $endYear; $year++) {
                $years[] = sprintf('%d-%d', $year, $year + 1);
            }
            return $years;
        };

        $academicYearOptions = collect($generateAcademicYearOptions());

        $sectionOptions = $facultyCourses
            ->flatMap(function ($assignment) {
                return collect(explode(',', $assignment->section ?? ''))
                    ->map(fn($value) => trim($value))
                    ->filter();
            })
            ->unique()
            ->sort()
            ->values();
        $minorAssignmentItems = $facultyCourses
            ->sortByDesc(fn($assignment) => $assignment->id ?? 0)
            ->map(function ($assignment) {
                $faculty = optional($assignment->faculty);
                $facultyUser = optional($faculty->user);
                $course = optional($assignment->course);
                $schedules = $assignment->schedules
                    ? $assignment->schedules->map(function ($schedule) {
                        return [
                            'day' => $schedule->day ?? '',
                            'time' => $schedule->time ?? '',
                            'label' => \App\Models\Schedule::formatScheduleLabel($schedule->day ?? null, $schedule->time ?? null),
                        ];
                    })->values()
                    : collect();

                return [
                    'id' => $assignment->id,
                    'faculty_id' => $assignment->faculty_id,
                    'faculty_name' => $facultyUser->name ?? ($faculty->name ?? 'N/A'),
                    'employee_no' => $faculty->employee_no ?? '',
                    'course_id' => $assignment->course_id,
                    'course_class_code' => $course->class_code ?? 'N/A',
                    'course_subject_code' => $course->subject_code ?? '',
                    'course_subject_type' => $course->subject_type ?? 'minor',
                    'section' => $assignment->section ?? '',
                    'academic_year' => $assignment->academic_year ?? '',
                    'semester' => $assignment->semester ?? '',
                    'schedule_label' => $schedules->pluck('label')->filter()->unique()->implode(' | ') ?: 'N/A',
                ];
            })
            ->values();
        // access role
        $container = 'container-xxl';
        $accessLevels = collect(auth()->user()?->access_level ?? []);
        $isAdmin = auth()->user()?->role === 'Admin';
        $canManageCourses = $isAdmin || $accessLevels->contains('Manage Courses');
        $canAdd = $isAdmin;
        $canEdit = $canManageCourses;
        $canDelete = $isAdmin;
        $showDeleteDisabled = !$isAdmin && $canManageCourses;
        $canImport = $isAdmin;
    @endphp

    {{-- HEADER --}}
    <div class="card evaluation-card evaluation-card--table mb-4 course-action-card">
        <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">

            <div class="text-center text-lg-start">
                <h5 class="card-title mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-book"></i>
                    Minor Course
                </h5>
                 <p class="text-muted mb-0">Switch between managing courses or assignments</p>
            </div>

            {{-- TOGGLE BUTTONS --}}
            <div class="course-tabs-nav d-flex gap-5">

                <button type="button" class="btn btn-tab active" data-course-section="courses">
                    Manage Course
                </button>

                <button type="button" class="btn btn-tab" data-course-section="assignments">
                    Assign Course
                </button>

            </div>

        </div>
    </div>

    {{-- ========================= --}}
    {{-- MANAGE COURSE SECTION --}}
    {{-- ========================= --}}
    <div id="courses-section" class="course-section">

        <div class="card mb-4">
            <div class="card-header d-flex gap-5 align-items-center">
                <h5 class="mb-0">Manage Courses</h5>
                @if ($canAdd)
                    <button type="button" class="btn btn-course-primary" onclick="list_methods.addMinorCourse(this)">
                        <i class="fa-solid fa-circle-plus me-2"></i>
                        Add Course
                    </button>
                @endif
            </div>

            <div class="card-body">

                <table class="datatables-basic table table-bordered table-responsive dataTable dtr-column"
                    id="courses-table-minor">

                    <thead>
                        <tr>
                            <th>Class Code</th>
                            <th>Subject</th>
                            <th>Subject Type</th>
                            <th>Handlers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- Modal Add Minor Course-->
    <div class="modal fade" id="add-modal-minor-course">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Add Minor Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-6">
                            <label for="nameBasic" class="form-label">Class Code</label>
                            <input type="text" id="addclassCode" class="form-control" placeholder="e.g. IT101" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label for="nameBasic" class="form-label">Subject</label>
                            <input type="text" id="addsubjectCode" class="form-control"
                                placeholder="e.g. Introduction to IT" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="addBtnMinors"
                        onclick="list_methods.saveAddMinor(this);">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Update Minor-->
    <div class="modal fade" id="update-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-6">
                            <label for="nameBasic" class="form-label">Class Code</label>
                            <input type="text" id="classCode" class="form-control" placeholder="Enter class code" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label for="nameBasic" class="form-label">Subject</label>
                            <input type="text" id="subjectCode" class="form-control" placeholder="Enter subject" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateBtnMinors"
                        onclick="list_methods.saveUpdateMinor(this);" data-id="">Update</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- ASSIGN COURSE SECTION --}}
    {{-- ========================= --}}
    <div id="assignments-section" class="course-section d-none">

        <div class="card">
            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- LEFT -->
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0">Assign Courses</h5>
                    @if ($canAdd)
                        <button type="button" class="btn btn-course-primary" onclick="list_methods.assignCourse(this)">
                            <i class="fa-solid fa-circle-plus me-2"></i>
                            Assign Course
                        </button>
                    @endif
                </div>

                <!-- RIGHT FILTER -->
                <div class="dropdown table-filter-dropdown">
                    <button class="filter-toggle" type="button" id="assignmentFilterToggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <span>Filters</span>
                        <i class="bx bx-filter"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="width: 320px;">
                        <!-- FACULTY -->
                        <div class="mb-3">
                            <label class="form-label text-uppercase small">
                                Faculty
                            </label>
                            <select id="assignmentFilterFaculty" class="form-select">
                                <option value="all">All</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">
                                        {{ optional($faculty->user)->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ACADEMIC YEAR -->
                        <div class="mb-3">
                            <label class="form-label text-uppercase small">
                                Academic Year
                            </label>
                            <select id="assignmentFilterYear" class="form-select">
                                <option value="all">All</option>
                                @foreach ($academicYearOptions as $year)
                                    <option value="{{ $year }}">
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- SEMESTER -->
                        <div class="mb-3">
                            <label class="form-label text-uppercase small">
                                Semester
                            </label>
                            <select id="assignmentFilterSemester" class="form-select">
                                <option value="all">All</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <!-- RESET -->
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-link p-0 table-filter-reset"
                                id="assignmentFilterReset">
                                Reset Filters
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- BODY -->
            <div class="card-body">

                <table class="datatables-basic table table-bordered table-responsive dataTable dtr-column"
                    id="assignments-table-course">

                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Course</th>
                            <th>Subject Type</th>
                            <th>Section</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>
        </div>
    </div>
    <!-- Modal add Minor Assign-->
    <div class="modal fade" id="add-modal-assign">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Assign Minor Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="createAssignmentFacultyToggle">Faculty Member</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="faculty">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="createAssignmentFacultyToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
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
                                                $rawFacultyName =
                                                    optional($faculty->user)->name ?? ($faculty->name ?? '');
                                                $facultyEmail = optional($faculty->user)->email ?? '';
                                                $facultyDisplayName =
                                                    trim($rawFacultyName) === '' ? 'Unknown' : $rawFacultyName;
                                                $facultyFilter = strtolower(
                                                    trim($rawFacultyName . ' ' . $facultyEmail),
                                                );
                                            @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $faculty->id }}"
                                                data-option-label="{{ $facultyDisplayName }}"
                                                data-option-filter="{{ $facultyFilter }}">
                                                <span>{{ $facultyDisplayName }}</span>
                                                @if ($facultyEmail)
                                                    <small>{{ $facultyEmail }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="faculty_id" id="createAssignmentFaculty" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="createAssignmentCourseToggle">Course</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="course">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="createAssignmentCourseToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Course --">-- Select Course --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($courses as $course)
                                            @php
                                                $courseCodeRaw = $course->class_code ?? '';
                                                $courseSubjectRaw = $course->subject_code ?? '';
                                                $courseCodeDisplay = trim($courseCodeRaw);
                                                $courseSubjectDisplay = trim($courseSubjectRaw);
                                                $courseLabel = $courseCodeDisplay;
                                                if ($courseSubjectDisplay !== '') {
                                                    $courseLabel =
                                                        ($courseLabel !== '' ? $courseLabel . ' - ' : '') .
                                                        $courseSubjectDisplay;
                                                }
                                                $courseFilter = strtolower(
                                                    trim($courseCodeRaw . ' ' . $courseSubjectRaw),
                                                );
                                            @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $course->id }}"
                                                data-option-label="{{ $courseLabel }}"
                                                data-option-filter="{{ $courseFilter }}">
                                                <span>{{ $courseCodeDisplay }}</span>
                                                @if ($courseSubjectDisplay !== '')
                                                    <small>{{ $courseSubjectDisplay }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="course_id" id="createAssignmentCourse" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="sectionAssignmentCourseToggle">Section</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="section">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="sectionAssignmentCourseToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Course --">-- Select Course --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($sectionOptions as $section)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $section }}"
                                                data-option-label="{{ $section }}">
                                                <span>{{ $section }}</span>

                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="section" id="createAssignmentSection">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="createAssignmentYearToggle">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="year">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="createAssignmentYearToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Academic Year --">-- Select Academic Year
                                        --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($academicYearOptions as $year)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $year }}"
                                                data-option-label="{{ $year }}"
                                                data-option-filter="{{ strtolower($year) }}">
                                                <span>{{ $year }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="academic_year" id="createAssignmentYear" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="createAssignmentSemesterToggle">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="semester">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="createAssignmentSemesterToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Semester --">-- Select Semester --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach (['1st' => '1st Semester', '2nd' => '2nd Semester', 'Summer' => 'Summer'] as $value => $label)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $value }}"
                                                data-option-label="{{ $label }}"
                                                data-option-filter="{{ strtolower($label) }}">
                                                <span>{{ $label }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="semester" id="createAssignmentSemester" required>
                            </div>
                        </div>
                    </div>
                    <div class="faculty-load-preview mt-3" id="minorFacultyLoadPreview">
                        <div class="faculty-load-preview-empty">
                            Select a faculty member to preview the current teaching load.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="addBtnMinorsAssign"
                        onclick="list_methods.saveAddMinorAssign(this);" data-id="">Assign</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Minor Assign-->
    <div class="modal fade" id="update-modal-assign">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Edit Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="courseDropdownToggle">
                                Faculty Member
                            </label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="faculty">
                                <!-- TOGGLE BUTTON -->
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="courseDropdownToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Course --">

                                        -- Select Course --
                                    </span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <!-- DROPDOWN -->
                                <div class="dropdown-menu p-2">
                                    <!-- SEARCH -->
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <!-- LIST -->
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($faculties as $faculty)
                                            @php
                                                $rawFacultyName =
                                                    optional($faculty->user)->name ?? ($faculty->name ?? '');
                                                $facultyEmail = optional($faculty->user)->email ?? '';
                                                $facultyDisplayName =
                                                    trim($rawFacultyName) === '' ? 'Unknown' : $rawFacultyName;
                                                $facultyFilter = strtolower(
                                                    trim($rawFacultyName . ' ' . $facultyEmail),
                                                );
                                            @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $faculty->id }}"
                                                data-option-label="{{ $facultyDisplayName }}"
                                                data-option-filter="{{ $facultyFilter }}">
                                                <span>{{ $facultyDisplayName }}</span>
                                                @if ($facultyEmail)
                                                    <small>{{ $facultyEmail }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="editAssignmentCourseToggle">Course</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="course">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentCourseToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Course --">-- Select Course --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($courses as $course)
                                            @php
                                                $courseCodeRaw = $course->class_code ?? '';
                                                $courseSubjectRaw = $course->subject_code ?? '';
                                                $courseCodeDisplay = trim($courseCodeRaw);
                                                $courseSubjectDisplay = trim($courseSubjectRaw);
                                                $courseLabel = $courseCodeDisplay;
                                                if ($courseSubjectDisplay !== '') {
                                                    $courseLabel =
                                                        ($courseLabel !== '' ? $courseLabel . ' - ' : '') .
                                                        $courseSubjectDisplay;
                                                }
                                                $courseFilter = strtolower(
                                                    trim($courseCodeRaw . ' ' . $courseSubjectRaw),
                                                );
                                            @endphp
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $course->id }}"
                                                data-option-label="{{ $courseLabel }}"
                                                data-option-filter="{{ $courseFilter }}">
                                                <span>{{ $courseCodeDisplay }}</span>
                                                @if ($courseSubjectDisplay !== '')
                                                    <small>{{ $courseSubjectDisplay }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="editAssignmentSection">Section</label>
                            <input type="text" id="section" class="form-control" placeholder="Enter section" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="editAssignmentYearToggle">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="year">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentYearToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Academic Year --">-- Select Academic Year
                                        --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach ($academicYearOptions as $year)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $year }}"
                                                data-option-label="{{ $year }}"
                                                data-option-filter="{{ strtolower($year) }}">
                                                <span>{{ $year }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-6">
                            <label class="form-label" for="editAssignmentSemesterToggle">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown data-type="semester">
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentSemesterToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Semester --">-- Select Semester --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        @foreach (['1st' => '1st Semester', '2nd' => '2nd Semester', 'Summer' => 'Summer'] as $value => $label)
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="{{ $value }}"
                                                data-option-label="{{ $label }}"
                                                data-option-filter="{{ strtolower($label) }}">
                                                <span>{{ $label }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="editAssignmentFaculty">
                <input type="hidden" id="editAssignmentCourse">
                <input type="hidden" id="editAssignmentYear">
                <input type="hidden" id="editAssignmentSemester">
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateBtnMinorsAssign"
                        onclick="list_methods.saveUpdateMinorAssign(this);" data-id="">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="minorCourseHandlersModal" tabindex="-1" aria-labelledby="minorCourseHandlersTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content course-handlers-modal">
                <div class="modal-header course-handlers-modal-header">
                    <div>
                        <span class="course-handlers-eyebrow">Faculty Handlers</span>
                        <h5 class="modal-title mb-1" id="minorCourseHandlersTitle">Course Handlers</h5>
                        <div class="course-handlers-subtitle">Faculty handlers, sections, school year, semester, and
                            schedule</div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="course-handlers-summary">
                        <div>
                            <span>Class Code</span>
                            <strong id="minorCourseHandlersClassCode">N/A</strong>
                        </div>
                        <div>
                            <span>Total Handlers</span>
                            <strong id="minorCourseHandlersCount">0</strong>
                        </div>
                    </div>
                    <div class="course-handlers-list" id="minorCourseHandlersBody"></div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('page-script')

    <script>
        var minorTable = null;
        var minorTableAssign = null;
        const minorAssignmentItems = @json($minorAssignmentItems);
        var list_methods = {
            addMinorCourse: function(e) {
                $('#add-modal-minor-course').modal('show');
            },
            saveAddMinor: function(e) {
                var formData = new FormData();

                formData.append('class_code', $('#addclassCode').val());
                formData.append('subject_code', $('#addsubjectCode').val());
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({

                    url: "{{ url('data-management/add-minor-course') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(data) {

                        Swal.fire({
                            icon: "success",
                            title: data.title,
                            text: data.message
                        }).then(() => {

                            // CLOSE MODAL
                            $('#add-modal-minor-course').modal('hide');

                            // RESET INPUTS
                            $('#addclassCode').val('');
                            $('#addsubjectCode').val('');

                            // RELOAD DATATABLE
                            minorTable.ajax.reload(null, false);
                        });
                    },

                    error: function(xhr) {

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;
                            let list = '';

                            $.each(errors, function(field, messages) {
                                list += `<li>${messages[0]}</li>`;
                            });

                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: `<ul>${list}</ul>`
                            });

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: xhr.responseJSON?.message || "Something went wrong"
                            });
                        }
                    }
                });
            },
            assignCourse: function(e) {
                $('#add-modal-assign').modal('show');
            },
            viewMinorHandlers: function(e) {
                const id = $(e).attr('data-id');
                if (!id) {
                    return;
                }

                $('#minorCourseHandlersTitle').text('Loading handlers...');
                $('#minorCourseHandlersClassCode').text('N/A');
                $('#minorCourseHandlersCount').text('0');
                $('#minorCourseHandlersBody').html(`
                    <div class="course-handlers-empty">
                        <h6 class="mb-1">Loading faculty handlers...</h6>
                        <p class="mb-0">Please wait.</p>
                    </div>
                `);
                $('#minorCourseHandlersModal').modal('show');

                $.ajax({
                    url: "{{ url('data-management/minor-courses') }}/" + id + "/handlers",
                    method: 'GET',
                    success: function(response) {
                        const course = response.course || {};
                        const handlers = Array.isArray(response.handlers) ? response.handlers : [];

                        $('#minorCourseHandlersTitle').text(course.subject_code || 'Course Handlers');
                        $('#minorCourseHandlersClassCode').text(course.class_code || 'N/A');
                        $('#minorCourseHandlersCount').text(handlers.length);
                        $('#minorCourseHandlersBody').html(
                            handlers.length ?
                            handlers.map((handler) => list_methods.buildMinorHandlerCard(handler)).join('') :
                            list_methods.buildMinorHandlersEmptyState()
                        );
                    },
                    error: function(xhr) {
                        $('#minorCourseHandlersTitle').text('Course Handlers');
                        $('#minorCourseHandlersBody').html(`
                            <div class="course-handlers-empty">
                                <h6 class="mb-1">Unable to load handlers</h6>
                                <p class="mb-0">${list_methods.escapeHtml(xhr.responseJSON?.message || 'Please try again.')}</p>
                            </div>
                        `);
                    }
                });
            },
            buildMinorHandlerCard: function(handler) {
                const name = handler.faculty_name || 'N/A';
                const employeeNo = handler.employee_no || 'N/A';
                const section = handler.section || 'N/A';
                const academicYear = handler.academic_year || 'N/A';
                const semester = list_methods.formatMinorSemester(handler.semester || '');
                const schedule = handler.schedule_label || 'N/A';
                const jobTitle = handler.job_title || 'Faculty';
                const department = handler.department || '';

                return `
                    <article class="course-handler-card">
                        <div class="course-handler-card-header">
                            <div>
                                <h6>${list_methods.escapeHtml(name)}</h6>
                                <p>${list_methods.escapeHtml(jobTitle)}${department ? ` <span>&middot;</span> ${list_methods.escapeHtml(department)}` : ''}</p>
                            </div>
                            <span class="course-handler-employee">${list_methods.escapeHtml(employeeNo)}</span>
                        </div>
                        <div class="course-handler-fields">
                            ${list_methods.buildMinorHandlerField('Section', section)}
                            ${list_methods.buildMinorHandlerField('School Year', academicYear)}
                            ${list_methods.buildMinorHandlerField('Semester', semester)}
                            ${list_methods.buildMinorHandlerField('Schedule', schedule)}
                        </div>
                    </article>
                `;
            },
            buildMinorHandlerField: function(label, value) {
                return `
                    <div class="course-handler-field">
                        <span>${list_methods.escapeHtml(label)}</span>
                        <strong>${list_methods.escapeHtml(value || 'N/A')}</strong>
                    </div>
                `;
            },
            buildMinorHandlersEmptyState: function() {
                return `
                    <div class="course-handlers-empty">
                        <h6 class="mb-1">No handlers assigned</h6>
                        <p class="mb-0">This GenEd course has no faculty handler or schedule yet.</p>
                    </div>
                `;
            },
            formatMinorSemester: function(value) {
                const normalised = String(value || '').toLowerCase();
                if (normalised === '1st' || normalised === 'first') {
                    return '1st Semester';
                }
                if (normalised === '2nd' || normalised === 'second') {
                    return '2nd Semester';
                }
                if (normalised === 'summer') {
                    return 'Summer';
                }
                return value || 'N/A';
            },
            escapeHtml: function(value) {
                return String(value ?? '').replace(/[&<>"']/g, function(char) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    })[char] || char;
                });
            },
            saveAddMinorAssign: function(e) {

                var formData = new FormData();

                formData.append('faculty_id', $('#createAssignmentFaculty').val());
                formData.append('course_id', $('#createAssignmentCourse').val());
                formData.append('section', $('#createAssignmentSection').val());
                formData.append('academic_year', $('#createAssignmentYear').val());
                formData.append('semester', $('#createAssignmentSemester').val());
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ url('data-management/save-add-minor-assign') }}", // adjust route
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(res) {

                        Swal.fire({
                            icon: "success",
                            title: res.title,
                            text: res.message
                        }).then(() => {

                            $('#add-modal-assign').modal('hide');

                            // reset
                            $('#createAssignmentFaculty').val('');
                            $('#createAssignmentCourse').val('');
                            $('#createAssignmentSection').val('');
                            $('#createAssignmentYear').val('');
                            $('#createAssignmentSemester').val('');
                            renderMinorFacultyLoadPreviewEmpty(
                                'Select a faculty member to preview the current teaching load.');

                            // reset UI labels (important for your custom dropdown)
                            $('#add-modal-assign [data-dropdown-label]').each(function() {
                                const placeholder = $(this).data('placeholder-text');
                                $(this).text(placeholder).addClass('is-placeholder');
                            });

                            minorTableAssign.ajax.reload(null, false);
                            minorTable.ajax.reload(null, false);
                        });
                    },

                    error: function(xhr) {

                        const res = xhr.responseJSON;

                        // =========================
                        // CASE 1: Laravel Validation (422 errors)
                        // =========================
                        if (xhr.status === 422 && res?.errors) {

                            let list = '';

                            $.each(res.errors, function(field, messages) {
                                list += `<li>${messages[0]}</li>`;
                            });

                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: `<ul>${list}</ul>`
                            });

                            return;
                        }

                        // =========================
                        // CASE 2: Custom error (Duplicate, etc.)
                        // =========================
                        if (res?.message) {

                            Swal.fire({
                                icon: "error",
                                title: res.title || "Error",
                                text: res.message
                            });

                            return;
                        }

                        // =========================
                        // FALLBACK
                        // =========================
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Something went wrong"
                        });
                    }
                });
            },
            updateMinorCourse: function(e) {
                var id = $(e).attr('data-id');

                $.ajax({
                    url: "{{ url('data-management/minor-courses/show') }}/" + id,
                    method: 'GET',
                    success: function(response) {
                        $('#updateBtnMinors').attr('data-id', id);
                        // Fill in the modal with the fetched data
                        $('#classCode').val(response.class_code);
                        $('#subjectCode').val(response.subject_code);
                        // Open the modal
                        $('#update-modal').modal('show');
                    }
                });
            },
            saveUpdateMinor: function(e) {
                var id = $(e).data('id');

                var formData = new FormData();
                formData.append('class_code', $('#classCode').val());
                formData.append('subject_code', $('#subjectCode').val());
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ url('data-management/save-update-minors') }}/" + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        Swal.fire({
                            icon: "success",
                            title: data.title,
                            text: data.message,
                            showLoaderOnConfirm: true,
                            allowOutsideClick: false,
                            preConfirm: () => {
                                return new Promise((resolve) => {
                                    setTimeout(() => {
                                        resolve();
                                    }, 1000);
                                });
                            }
                        }).then((result) => {
                            // HIDE MODAL MANUALLY
                            $('#update-modal').modal('hide');
                            // RELOAD DATATABLE
                            minorTable.ajax.reload(null, false);
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorList = '';
                            $.each(errors, function(field, messages) {
                                errorList += `<li>${messages[0]}</li>`;
                            });
                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: `<ul>${errorList}</ul>`,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: xhr.responseJSON.title || "Error",
                                text: xhr.responseJSON.message || "Something went wrong!",
                            });
                        }
                    }
                });


            },
            saveUpdate: function(e) {
                var id = $(e).data('id');
                var formData = new FormData();
                formData.append('username', $('#username_e').val());
                formData.append('firstname', $('#firstname_e').val());
                formData.append('lastname', $('#lastname_e').val());
                formData.append('middlename', $('#middlename_e').val());
                formData.append('contact_number', $('#contact_number_e').val());
                formData.append('address', $('#address_e').val());
                formData.append('email', $('#email_e').val());
                formData.append('birthdate', $('#birth_date_data_e').val());
                formData.append('_token', "{{ csrf_token() }}");
                $.ajax({
                    url: "{{ url('users/save-update-users') }}/" + $id,
                    method: 'POST',
                    data: formData,
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the Content-Type header
                    success: function(data) {
                        Swal.fire({
                            icon: "success",
                            title: data.title,
                            text: data.message,
                            showLoaderOnConfirm: true,
                            allowOutsideClick: false,
                            preConfirm: () => {
                                return new Promise((resolve) => {
                                    setTimeout(() => {
                                        resolve();
                                    }, 1000);
                                });
                            }
                        }).then((result) => {
                            $('#modal-update-user').modal('hide');
                            tbl_user.ajax.reload();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation error
                            var errors = xhr.responseJSON.errors;
                            var errorList = '';
                            $.each(errors, function(field, messages) {
                                errorList += `<li>${messages[0]}</li>`;
                            });
                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: `<ul>${errorList}</ul>`,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: xhr.responseJSON.title || "Error",
                                text: xhr.responseJSON.message || "Something went wrong!",
                            });
                        }
                    }
                });

            },
            deleteMinorCourse: function(e) {
                var rowId = $(e).data('id');
                Swal.fire({
                    title: 'Do you want to delete?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var $data = $(e).data();
                        var data = {
                            "_token": "{{ csrf_token() }}",
                            id: $data.id,
                        };
                        $.ajax({
                            url: '{{ url('data-management/delete-minors-course') }}',
                            type: "PUT",
                            data: data,
                            dataType: "JSON",
                        }).then(function(data, res, xhr) {
                            if (xhr.status == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Course Deleted",
                                    text: data.message,
                                    showLoaderOnConfirm: true,
                                    allowOutsideClick: false,
                                    preConfirm: () => {
                                        return new Promise((resolve) => {
                                            setTimeout(() => {
                                                resolve();
                                            }, 1000);
                                        });
                                    }
                                }).then((result) => {
                                    minorTable.ajax.reload(null, false);
                                });
                            }
                        });
                    }
                });
            },
            updateAssignMinor: function(e) {
                var id = $(e).data('id');
                $('#updateBtnMinorsAssign').attr('data-id', id);
                $.ajax({
                    url: "{{ url('data-management/minor-assign-courses/show') }}/" + id,
                    method: 'GET',
                    success: function(res) {
                        // OPEN MODAL
                        $('#update-modal-assign').modal('show');

                        // SET TEXT INPUT
                        //$('#section').val(res.section);


                        $('#update-modal-assign').one('shown.bs.modal', function() {

                            $('#section').val(res.section);

                            // IMPORTANT: init ONLY ONCE per open
                            initSearchableDropdowns(this);

                            setDropdownValue('#update-modal-assign', 'faculty', res.faculty_id);
                            setDropdownValue('#update-modal-assign', 'course', res.course_id);
                            setDropdownValue('#update-modal-assign', 'year', res.academic_year);
                            setDropdownValue('#update-modal-assign', 'semester', res.semester);
                        });
                    }
                });
            },
            saveUpdateMinorAssign: function(e) {
                var id = $(e).data('id');
                var formData = new FormData();
                if ($('#editAssignmentFaculty').val()) {
                    formData.append('faculty_id', $('#editAssignmentFaculty').val());
                }

                if ($('#editAssignmentCourse').val()) {
                    formData.append('course_id', $('#editAssignmentCourse').val());
                }

                formData.append('section', $('#section').val());

                if ($('#editAssignmentYear').val()) {
                    formData.append('academic_year', $('#editAssignmentYear').val());
                }

                if ($('#editAssignmentSemester').val()) {
                    formData.append('semester', $('#editAssignmentSemester').val());
                }

                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ url('data-management/save-update-minor-assign') }}/" + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(data) {

                        Swal.fire({
                            icon: "success",
                            title: data.title,
                            text: data.message
                        }).then(() => {

                            // CLOSE MODAL
                            const modalEl = document.getElementById('update-modal-assign');
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.hide();

                            // reload table
                            minorTableAssign.ajax.reload(null, false);
                            minorTable.ajax.reload(null, false);
                        });
                    },

                    error: function(xhr) {

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;
                            let list = '';

                            $.each(errors, function(field, messages) {
                                list += `<li>${messages[0]}</li>`;
                            });

                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                html: `<ul>${list}</ul>`
                            });

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: xhr.responseJSON?.message || "Something went wrong"
                            });
                        }
                    }
                });
            },
            deleteAssignMinorCourse: function(e) {
                var rowId = $(e).data('id');
                Swal.fire({
                    title: 'Are you sure you want to delete',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var $data = $(e).data();
                        var data = {
                            "_token": "{{ csrf_token() }}",
                            id: $data.id,
                        };
                        $.ajax({
                            url: "{{ url('data-management/delete-assign-minors-course') }}",
                            type: "PUT",
                            data: data,
                            dataType: "JSON",
                        }).then(function(data, res, xhr) {
                            if (xhr.status == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Assign Course Deleted",
                                    text: data.message,
                                    showLoaderOnConfirm: true,
                                    allowOutsideClick: false,
                                    preConfirm: () => {
                                        return new Promise((resolve) => {
                                            setTimeout(() => {
                                                resolve();
                                            }, 1000);
                                        });
                                    }
                                }).then((result) => {
                                    minorTableAssign.ajax.reload(null, false);
                                    minorTable.ajax.reload(null, false);
                                });
                            }
                        });
                    }
                });
            }
        }
        window.addEventListener('load', function() {

            // =========================
            // LOAD DATATABLE SCRIPTS
            // =========================

            const script1 = document.createElement('script');
            script1.src =
                'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js';

            script1.onload = function() {

                const script2 = document.createElement('script');
                script2.src =
                    'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js';

                script2.onload = function() {


                    // =========================
                    // INIT DATATABLES
                    // =========================

                    minorTable = $('#courses-table-minor').DataTable({
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,
                        bDestroy: true,

                        ajax: "{{ route('dm.minor.courses.list') }}",

                        order: [
                            [0, 'desc']
                        ],

                        columnDefs: [{
                            targets: [3, 4],
                            orderable: false
                        }]
                    });


                    minorTableAssign = $('#assignments-table-course').DataTable({
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        responsive: true,
                        bDestroy: true,

                        ajax: {
                            url: "{{ route('dm.minor.courses.assign.list') }}",
                            data: function(d) {
                                d.faculty_id = $('#assignmentFilterFaculty').val();
                                d.academic_year = $('#assignmentFilterYear').val();
                                d.semester = $('#assignmentFilterSemester').val();
                            }
                        },

                        order: [
                            [0, 'desc']
                        ],

                        columnDefs: [{
                            targets: [6],
                            orderable: false
                        }]
                    });

                    $('#assignmentFilterFaculty, #assignmentFilterYear, #assignmentFilterSemester').on(
                        'change',
                        function() {
                            minorTableAssign.ajax.reload();
                        });
                    //reset filter
                    $('#assignmentFilterReset').on('click', function() {

                        $('#assignmentFilterFaculty').val('all');
                        $('#assignmentFilterYear').val('all');
                        $('#assignmentFilterSemester').val('all');

                        minorTableAssign.ajax.reload();
                    });

                    // =========================
                    // TOGGLE SECTION
                    // =========================

                    $('.btn-tab').on('click', function() {
                        let section = $(this).data('course-section');
                        // REMOVE ACTIVE
                        $('.btn-tab').removeClass('active');
                        // ADD ACTIVE
                        $(this).addClass('active');
                        // HIDE ALL
                        $('.course-section').addClass('d-none');
                        // SHOW TARGET
                        $('#' + section + '-section').removeClass('d-none');

                    });
                    $('#add-modal-assign').on('shown.bs.modal', function() {
                        initSearchableDropdowns(this);
                        updateMinorFacultyLoadPreview();
                    });

                    $('#add-modal-assign').on('hidden.bs.modal', function() {
                        renderMinorFacultyLoadPreviewEmpty(
                            'Select a faculty member to preview the current teaching load.');
                    });

                    $('#update-modal-assign').on('shown.bs.modal', function() {
                        initSearchableDropdowns(this);
                    });

                    bindMinorFacultyLoadPreview();
                };
                document.body.appendChild(script2);
            };
            document.body.appendChild(script1);
        });

        function initSearchableDropdowns(context = document) {
            const dropdowns = context.querySelectorAll('[data-searchable-dropdown]');
            dropdowns.forEach((dropdown) => {
                if (dropdown.dataset.dropdownInitialized === 'true') {
                    return;
                }
                dropdown.dataset.dropdownInitialized = 'true';

                const label = dropdown.querySelector('[data-dropdown-label]');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const searchInput = dropdown.querySelector('[data-dropdown-search]');
                const listWrapper = dropdown.querySelector('[data-dropdown-list]');
                const toggle = dropdown.querySelector('[data-dropdown-toggle]');
                const placeholderText = label?.dataset.placeholderText?.trim() || '-- Select Option --';

                const setLabel = (text, isPlaceholder = false) => {
                    if (!label) {
                        return;
                    }
                    label.textContent = text;
                    label.classList.toggle('is-placeholder', isPlaceholder);
                };

                const resetSelection = () => {
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    setLabel(placeholderText, true);
                };

                const getOptions = () => Array.from(dropdown.querySelectorAll('[data-dropdown-option]'));

                const applySearchFilter = () => {
                    const term = searchInput ? searchInput.value.trim().toLowerCase() : '';
                    const hasTerm = term !== '';
                    getOptions().forEach((option) => {
                        const filter = option.dataset.optionFilter ||
                            option.dataset.optionLabel ||
                            option.textContent;
                        const matches = !hasTerm || (filter && filter.toLowerCase().includes(term));
                        option.classList.toggle('d-none', !matches);
                    });

                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                };
                // const selectOption = (option) => {

                //     const value = option.dataset.optionValue;
                //     const labelText = option.dataset.optionLabel || option.textContent.trim();
                //     const type = dropdown.dataset.type;

                //     // =========================
                //     // MAP VALUES TO HIDDEN INPUTS
                //     // =========================

                //     if (type === 'faculty') {
                //         $('#createAssignmentFaculty').val(value);
                //     }

                //     if (type === 'course') {
                //         $('#createAssignmentCourse').val(value);
                //     }

                //     if (type === 'section') {
                //         $('#createAssignmentSection').val(value); // ✅ FIXED SECTION
                //     }

                //     if (type === 'year') {
                //         $('#createAssignmentYear').val(value);
                //     }

                //     if (type === 'semester') {
                //         $('#createAssignmentSemester').val(value);
                //     }

                //     // =========================
                //     // UPDATE LABEL UI
                //     // =========================
                //     const label = dropdown.querySelector('[data-dropdown-label]');
                //     if (label) {
                //         label.textContent = labelText;
                //         label.classList.remove('is-placeholder');
                //     }

                //     // =========================
                //     // CLOSE DROPDOWN
                //     // =========================
                //     if (toggle) {
                //         bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
                //     }
                // };
                const selectOption = (option) => {

                    const value = option.dataset.optionValue;
                    const labelText = option.dataset.optionLabel || option.textContent.trim();
                    const type = dropdown.dataset.type;

                    // detect modal
                    const modal = dropdown.closest('.modal');

                    // =========================
                    // CREATE MODAL
                    // =========================
                    if (modal.id === 'add-modal-assign') {

                        if (type === 'faculty') {
                            $('#createAssignmentFaculty').val(value);
                        }

                        if (type === 'course') {
                            $('#createAssignmentCourse').val(value);
                        }

                        if (type === 'section') {
                            $('#createAssignmentSection').val(value);
                        }

                        if (type === 'year') {
                            $('#createAssignmentYear').val(value);
                        }

                        if (type === 'semester') {
                            $('#createAssignmentSemester').val(value);
                        }

                        setTimeout(updateMinorFacultyLoadPreview, 0);
                    }

                    // =========================
                    // EDIT MODAL
                    // =========================
                    if (modal.id === 'update-modal-assign') {

                        if (type === 'faculty') {
                            $('#editAssignmentFaculty').val(value);
                        }

                        if (type === 'course') {
                            $('#editAssignmentCourse').val(value);
                        }

                        if (type === 'year') {
                            $('#editAssignmentYear').val(value);
                        }

                        if (type === 'semester') {
                            $('#editAssignmentSemester').val(value);
                        }
                    }

                    // =========================
                    // UPDATE LABEL
                    // =========================
                    const label = dropdown.querySelector('[data-dropdown-label]');

                    if (label) {
                        label.textContent = labelText;
                        label.classList.remove('is-placeholder');
                    }

                    // =========================
                    // CLOSE DROPDOWN
                    // =========================
                    if (toggle) {
                        bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
                    }
                };

                function capitalize(str) {
                    return str.charAt(0).toUpperCase() + str.slice(1);
                }

                function capitalize(str) {
                    return str.charAt(0).toUpperCase() + str.slice(1);
                }


                if (searchInput) {
                    searchInput.addEventListener('input', applySearchFilter);
                }

                if (listWrapper) {
                    listWrapper.addEventListener('click', (event) => {
                        const option = event.target.closest('[data-dropdown-option]');
                        if (!option) {
                            return;
                        }
                        event.preventDefault();
                        selectOption(option);
                    });
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
                            resetSelection();
                            if (searchInput) {
                                searchInput.value = '';
                            }
                            applySearchFilter();
                        }, 0);
                    });
                }

                const initialValue = hiddenInput?.value?.trim();
                if (initialValue) {
                    const initialOption = getOptions().find((option) => option.dataset.optionValue ===
                        initialValue);
                    const initialLabel = initialOption?.dataset?.optionLabel ??
                        initialOption?.querySelector('span')?.textContent?.trim();
                    setLabel(initialLabel || placeholderText, !initialLabel);
                } else {
                    resetSelection();
                }

                applySearchFilter();

                dropdown.__searchableDropdown = {
                    applyFilter: applySearchFilter,
                    resetSelection,
                    setLabel,
                    placeholder: placeholderText,
                };
            });
        }

        function bindMinorFacultyLoadPreview() {
            const fields = [
                document.getElementById('createAssignmentFaculty'),
                document.getElementById('createAssignmentYear'),
                document.getElementById('createAssignmentSemester'),
            ].filter(Boolean);

            fields.forEach((field) => {
                if (field.dataset.loadPreviewBound === 'true') {
                    return;
                }
                field.dataset.loadPreviewBound = 'true';
                field.addEventListener('change', updateMinorFacultyLoadPreview);
                field.addEventListener('input', updateMinorFacultyLoadPreview);
            });
        }

        function updateMinorFacultyLoadPreview() {
            const facultyId = $('#createAssignmentFaculty').val();
            const academicYear = $('#createAssignmentYear').val();
            const semester = $('#createAssignmentSemester').val();
            renderMinorFacultyLoadPreview(facultyId, academicYear, semester);
        }

        function renderMinorFacultyLoadPreview(facultyId, academicYear = '', semester = '') {
            const container = document.getElementById('minorFacultyLoadPreview');
            if (!container) {
                return;
            }

            if (!facultyId) {
                renderMinorFacultyLoadPreviewEmpty('Select a faculty member to preview the current teaching load.');
                return;
            }

            const allFacultyLoads = (Array.isArray(minorAssignmentItems) ? minorAssignmentItems : [])
                .filter((assignment) => Number(assignment.faculty_id) === Number(facultyId));
            const termLoads = allFacultyLoads.filter((assignment) => {
                const yearMatches = !academicYear || String(assignment.academic_year ?? '') === String(academicYear);
                const semesterMatches = !semester || normaliseMinorSemesterKey(assignment.semester) ===
                    normaliseMinorSemesterKey(semester);
                return yearMatches && semesterMatches;
            });
            const visibleLoads = (academicYear || semester) ? termLoads : allFacultyLoads;
            const selectedOption = document.querySelector(
                `#add-modal-assign [data-searchable-dropdown][data-type="faculty"] [data-dropdown-option][data-option-value="${facultyId}"]`
            );
            const facultyName = selectedOption?.dataset.optionLabel || allFacultyLoads[0]?.faculty_name ||
                'Selected faculty';
            const subtitle = academicYear || semester ?
                [academicYear || 'Any year', semester ? formatMinorSemester(semester) : 'Any semester'].filter(Boolean).join(
                    ' | ') :
                'All assigned GenEd courses';
            const listHtml = visibleLoads.length ?
                visibleLoads.slice(0, 6).map(buildMinorFacultyLoadPreviewItem).join('') :
                `<div class="faculty-load-preview-empty">No assigned course found for this selection.</div>`;
            const hiddenCount = Math.max(visibleLoads.length - 6, 0);
            const highLoadNote = allFacultyLoads.length >= 8 ?
                `<div class="faculty-load-preview-note">High load: this faculty has ${allFacultyLoads.length} total assigned courses.</div>` :
                '';

            container.innerHTML = `
                <div class="faculty-load-preview-header">
                    <div>
                        <h6 class="faculty-load-preview-title">${escapeMinorHtml(facultyName)}</h6>
                        <div class="faculty-load-preview-subtitle">${escapeMinorHtml(subtitle)}</div>
                    </div>
                    <span class="faculty-load-preview-count">${visibleLoads.length} load${visibleLoads.length === 1 ? '' : 's'}</span>
                </div>
                <div class="faculty-load-preview-list">
                    ${listHtml}
                </div>
                ${hiddenCount ? `<div class="faculty-load-preview-note">+${hiddenCount} more assigned course${hiddenCount === 1 ? '' : 's'} not shown.</div>` : ''}
                ${highLoadNote}
            `;
        }

        function buildMinorFacultyLoadPreviewItem(assignment) {
            const course = [assignment.course_class_code ?? '', assignment.course_subject_code ?? '']
                .filter(Boolean)
                .join(' - ') || 'N/A';
            const details = [
                assignment.section ? `Section: ${assignment.section}` : '',
                assignment.academic_year ?? '',
                formatMinorSemester(assignment.semester),
                assignment.schedule_label && assignment.schedule_label !== 'N/A' ? assignment.schedule_label : '',
            ].filter(Boolean).join(' | ');

            return `
                <div class="faculty-load-preview-item">
                    <strong>${escapeMinorHtml(course)}</strong>
                    <span>${escapeMinorHtml(details || 'No schedule yet')}</span>
                </div>
            `;
        }

        function renderMinorFacultyLoadPreviewEmpty(message) {
            const container = document.getElementById('minorFacultyLoadPreview');
            if (!container) {
                return;
            }
            container.innerHTML = `<div class="faculty-load-preview-empty">${escapeMinorHtml(message)}</div>`;
        }

        function formatMinorSemester(value) {
            const normalised = String(value || '').toLowerCase();
            if (normalised === '1st' || normalised === 'first') {
                return '1st Semester';
            }
            if (normalised === '2nd' || normalised === 'second') {
                return '2nd Semester';
            }
            if (normalised === 'summer') {
                return 'Summer';
            }
            return value || 'N/A';
        }

        function normaliseMinorSemesterKey(value) {
            const normalised = String(value || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            if (['1', '1st', 'first', 'firstsem', 'firstsemester', 'semester1'].includes(normalised)) {
                return '1st';
            }
            if (['2', '2nd', 'second', 'secondsem', 'secondsemester', 'semester2'].includes(normalised)) {
                return '2nd';
            }
            if (['3', '3rd', 'summer', 'summersem', 'summersemester', 'midyear'].includes(normalised)) {
                return 'summer';
            }
            return normalised;
        }

        function escapeMinorHtml(value) {
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

        function setDropdownValue(modal, type, value) {

            const dropdown = document.querySelector(
                `${modal} [data-searchable-dropdown][data-type="${type}"]`
            );

            if (!dropdown) return;

            const option = dropdown.querySelector(
                `[data-dropdown-option][data-option-value="${value}"]`
            );

            if (!option) return;

            const labelText =
                option.dataset.optionLabel ||
                option.textContent.trim();

            // ✅ 1. sync hidden inputs (CRITICAL)
            if (type === 'faculty') $('#editAssignmentFaculty').val(value);
            if (type === 'course') $('#editAssignmentCourse').val(value);
            if (type === 'year') $('#editAssignmentYear').val(value);
            if (type === 'semester') $('#editAssignmentSemester').val(value);

            // ✅ 2. sync UI label
            const label = dropdown.querySelector('[data-dropdown-label]');
            if (label) {
                label.textContent = labelText;
                label.classList.remove('is-placeholder');
            }

            // OPTIONAL: mark selected visually
            dropdown.querySelectorAll('[data-dropdown-option]')
                .forEach(el => el.classList.remove('active'));

            option.classList.add('active');
        }
    </script>

@endsection
