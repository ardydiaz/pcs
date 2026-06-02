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
                            <th>Assignments</th>
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

@endsection


@section('page-script')

    <script>
        var minorTable = null;
        var minorTableAssign = null;
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
                    });

                    $('#update-modal-assign').on('shown.bs.modal', function() {
                        initSearchableDropdowns(this);
                    });
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
