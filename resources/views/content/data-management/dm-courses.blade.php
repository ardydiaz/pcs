@extends('layouts/contentNavbarLayout') <!-- Extend the main content layout which includes the navbar -->

<!--
    This Blade template is for the Courses Data Management page. It extends the main content layout and defines sections for the page title, styles, content, and scripts.
    The content section includes various components for managing courses and their assignments to faculty members.
    The script section initializes the interactive functionality of the page, such as sorting, filtering, and handling modals for creating, editing,
    and deleting courses and assignments.
-->
@php
    $courseItems = $courses
        ->sortByDesc(function ($course) {
            return $course->created_at ?? ($course->id ?? 0);
        })
        ->map(function ($course) {
            return [
                'id' => $course->id,
                'class_code' => $course->class_code,
                'subject_code' => $course->subject_code,
                'subject_type' => $course->subject_type,
                'assignments' => $course->faculty_courses_count ?? 0,
            ];
        })
        ->values();

    $facultyOptions = $faculties
        ->map(function ($faculty) {
            return [
                'id' => $faculty->id,
                'name' => optional($faculty->user)->name ?? ($faculty->name ?? 'Unknown'),
                'email' => optional($faculty->user)->email ?? '',
            ];
        })
        ->values();

    $assignmentItems = $facultyCourses
        ->sortByDesc(function ($assignment) {
            return $assignment->created_at ?? ($assignment->id ?? 0);
        })
        ->map(function ($assignment) {
            $faculty = optional($assignment->faculty);
            $facultyUser = optional($faculty->user);
            $course = optional($assignment->course);
            $facultyName = $facultyUser->name ?? ($faculty->name ?? 'N/A');

            return [
                'id' => $assignment->id,
                'faculty_id' => $assignment->faculty_id,
                'faculty_name' => $facultyName,
                'faculty_email' => $facultyUser->email ?? '',
                'course_id' => $assignment->course_id,
                'course_class_code' => $course->class_code ?? 'N/A',
                'course_subject_code' => $course->subject_code ?? '',
                'course_subject_type' => $course->subject_type ?? 'N/A', // new comlumn for MAJOR or MINOR
                'section' => $assignment->section ?? '',
                'academic_year' => $assignment->academic_year,
                'semester' => $assignment->semester,
            ];
        })
        ->values();

    $sectionOptions = $facultyCourses
        ->flatMap(function ($assignment) {
            return collect(explode(',', $assignment->section ?? ''))
                ->map(fn($value) => trim($value))
                ->filter();
        })
        ->unique()
        ->sort()
        ->values();
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

    $container = 'container-xxl';
    $accessLevels = collect(auth()->user()?->access_level ?? []);
    $isAdmin = auth()->user()?->role === 'Admin';
    $canManageCourses = $isAdmin || $accessLevels->contains('Manage Courses');
    $canAdd = $canManageCourses;
    $canEdit = $canManageCourses;
    $canDelete = $isAdmin;
    $showDeleteDisabled = !$isAdmin && $canManageCourses;
    $canImport = $canManageCourses;
@endphp

<!-- Section for the page title, displayed in the browser tab and used by the layout -->
@section('title', 'Data Management - Courses') <!-- Set the page title for the courses data management page -->

<!-- Section for Page Styles component rendered from partials/courses/page-style.blade.php -->
@include('content.data-management.partials.courses.page-style') <!-- Include the page-specific styles for the courses data management page -->

<!-- Section for Course Page Content -->
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

    @if (session('error'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach (explode('<br>', session('error')) as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('components.dm-toast', ['messages' => $pageToasts])

    <!-- Main Container -->
    <div class="container-fluid">

        <div id="courseAlertContainer" class="evaluation-alert-container"></div>

        <!-- Courses Header Component -->
        @include('content.data-management.partials.courses.header') <!-- This includes the header section with title and action buttons -->

        <!-- Manage Courses Table Component -->
        @include('content.data-management.partials.courses.manage-courses-table') <!-- This includes the table for managing courses -->

        <!-- Assign Course to Existing Faculty Table Component -->
        @include('content.data-management.partials.courses.assign-course-table') <!-- This includes the table for managing course assignments -->





        <!-- Assign Minors Course to Existing Faculty Table Component -->
        {{-- @include('content.data-management.partials.courses.minors-assign-course-table') <!-- This includes the table for minor courses --> --}}
    </div> <!-- End of Main Container -->

    <!-- New Course Form Modal Component -->
    @include('content.data-management.partials.courses.add-course-form-modal') <!-- This is included as a separate partial component for better organization -->

    <!-- Edit Course Form Modal Component -->
    @include('content.data-management.partials.courses.edit-course-form-modal') <!-- This is included as a separate partial component for better organization -->

    <!-- Delete Course Alert & Bulk Component -->
    @include('content.data-management.partials.courses.delete-alert') <!-- This is included as a separate partial component for better organization -->

    <!-- Assign Course Form Modal Component -->
    @include('content.data-management.partials.courses.assign-course-form-modal') <!-- This is included as a separate partial component for better organization -->

    <!-- Edit Assignment Modal Component -->
    @include('content.data-management.partials.courses.edit-assignment-modal') <!-- This is included as a separate partial component for better organization -->

    <!-- Delete Assignment Alert & Bulk Component -->
    @include('content.data-management.partials.courses.delete-assignment-alert') <!-- This is included as a separate partial component for better organization -->

    <!-- Excel Import Modal Component -->
    @include('content.data-management.partials.courses.excel-import-modal') <!-- This is included as a separate partial component for better organization -->
@endsection <!-- End of Course Page Content Section -->

<!-- Section for Page Scripts. Functions and Event Listeners for the courses data management page -->
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
            const palette = element.dataset.pillPalette;
            if (!palette) {
                return;
            }
            const color = getPillColor(element.dataset.pillValue || element.textContent, palette);
            if (!color) {
                return;
            }
            element.style.setProperty('--pill-bg', color.bg);
            element.style.setProperty('--pill-color', color.color);
        }

        function applyPillPalettes(root = document) {
            const scope = root instanceof Element ? root : document.body;
            scope.querySelectorAll('[data-pill-palette]').forEach(stylePillElement);
        }

        const coursePermissions = {
            canAdd: @json($canAdd),
            canEdit: @json($canEdit),
            canDelete: @json($canDelete),
            showDeleteDisabled: @json($showDeleteDisabled),
            canImport: @json($canImport),
        };

        document.addEventListener('DOMContentLoaded', () => {
            initUserDropdowns();
            initSearchableDropdowns();
            window.coursePage = new CoursePage({
                courses: @json($courseItems),
                assignments: @json($assignmentItems),
                faculties: @json($facultyOptions),
                coursesRoot: document.querySelector('[data-table-id="coursesTable"]'),
                assignmentsRoot: document.querySelector('[data-table-id="assignmentsTable"]'),
            });
        });

        function initUserDropdowns() {
            const dropdowns = document.querySelectorAll('[data-user-dropdown]');
            if (!dropdowns.length) {
                return;
            }

            dropdowns.forEach((dropdown) => {
                if (dropdown.dataset.dropdownInitialized === 'true') {
                    return;
                }
                dropdown.dataset.dropdownInitialized = 'true';

                const hiddenInput = dropdown.querySelector('input[name="user_id"]');
                const nameInput = dropdown.querySelector('[data-user-name-input]');
                const listWrapper = dropdown.querySelector('[data-user-list]');
                const menu = dropdown.querySelector('.dropdown-menu');
                let selectedName = '';
                let ignoreBlurClose = false;
                let pointerSelection = false;

                const resetSelection = () => {
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    if (nameInput) {
                        nameInput.value = '';
                    }
                    selectedName = '';
                };

                const getOptions = () => Array.from(dropdown.querySelectorAll('[data-user-option]'));

                const applySearchFilter = () => {
                    const term = nameInput ? nameInput.value.trim().toLowerCase() : '';
                    const hasTerm = term !== '';
                    let matchesCount = 0;

                    getOptions().forEach((option) => {
                        const name = option.dataset.userNameLower || '';
                        const email = option.dataset.userEmailLower || '';
                        const matches = !hasTerm || name.includes(term) || email.includes(term);
                        option.classList.toggle('d-none', !matches);
                        if (matches) {
                            matchesCount += 1;
                        }
                    });

                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                    if (menu) {
                        menu.classList.toggle('d-none', hasTerm && matchesCount === 0);
                    }
                };

                const openDropdown = () => {
                    if (menu) {
                        menu.classList.add('show');
                    }
                    dropdown.classList.add('show');
                };

                const closeDropdown = () => {
                    if (menu) {
                        menu.classList.remove('show');
                    }
                    dropdown.classList.remove('show');
                };

                const selectOption = (option) => {
                    if (!option) {
                        return;
                    }
                    if (hiddenInput) {
                        hiddenInput.value = option.dataset.userId || '';
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    if (nameInput) {
                        nameInput.value = option.dataset.userName || '';
                        selectedName = nameInput.value.trim();
                    }
                    closeDropdown();
                };

                if (listWrapper) {
                    listWrapper.addEventListener('pointerdown', (event) => {
                        const option = event.target.closest('[data-user-option]');
                        if (!option) {
                            return;
                        }
                        pointerSelection = true;
                        ignoreBlurClose = true;
                        event.preventDefault();
                        selectOption(option);
                        setTimeout(() => {
                            pointerSelection = false;
                            ignoreBlurClose = false;
                        }, 0);
                    });
                    listWrapper.addEventListener('click', (event) => {
                        if (pointerSelection) {
                            return;
                        }
                        const option = event.target.closest('[data-user-option]');
                        if (!option) {
                            return;
                        }
                        event.preventDefault();
                        selectOption(option);
                    });
                }

                if (nameInput) {
                    nameInput.addEventListener('input', () => {
                        const currentName = nameInput.value.trim();
                        if (currentName !== selectedName) {
                            selectedName = '';
                            if (hiddenInput) {
                                hiddenInput.value = '';
                                hiddenInput.dispatchEvent(new Event('change', {
                                    bubbles: true
                                }));
                            }
                        }
                        applySearchFilter();
                        openDropdown();
                    });
                    nameInput.addEventListener('focus', () => {
                        applySearchFilter();
                        openDropdown();
                    });
                    nameInput.addEventListener('blur', () => {
                        setTimeout(() => {
                            if (ignoreBlurClose) {
                                return;
                            }
                            if (dropdown.contains(document.activeElement)) {
                                return;
                            }
                            closeDropdown();
                        }, 120);
                    });
                    nameInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            closeDropdown();
                        }
                    });
                }

                document.addEventListener('click', (event) => {
                    if (!dropdown.contains(event.target)) {
                        closeDropdown();
                    }
                });

                const parentForm = dropdown.closest('form');
                if (parentForm) {
                    parentForm.addEventListener('reset', () => {
                        setTimeout(() => {
                            resetSelection();
                            applySearchFilter();
                        }, 0);
                    });
                }

                const initialValue = hiddenInput?.value?.trim();
                if (initialValue) {
                    const option = getOptions().find((opt) => opt.dataset.userId === initialValue);
                    if (option && nameInput) {
                        nameInput.value = option.dataset.userName || option.textContent.trim();
                        selectedName = nameInput.value.trim();
                    } else {
                        resetSelection();
                    }
                } else {
                    resetSelection();
                }

                applySearchFilter();

                dropdown.__userDropdown = {
                    applyFilter: applySearchFilter,
                    resetSelection,
                    setName: (text) => {
                        if (!nameInput) {
                            return;
                        }
                        nameInput.value = text;
                        selectedName = text.trim();
                    },
                };
            });
        }

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

                const selectOption = (option) => {
                    const value = option?.dataset?.optionValue ?? '';
                    const labelText = option?.dataset?.optionLabel ??
                        option?.querySelector('span')?.textContent?.trim() ??
                        option?.textContent?.trim() ??
                        placeholderText;

                    if (hiddenInput) {
                        hiddenInput.value = value;
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    setLabel(labelText, !value);

                    if (toggle && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                        const instance = bootstrap.Dropdown.getOrCreateInstance(toggle);
                        instance.hide();
                    }
                };

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

        class CoursePage {
            constructor({
                courses,
                minorcourses,
                assignments,
                faculties,
                coursesRoot,
                assignmentsRoot
            }) {
                this.courses = Array.isArray(courses) ? courses : [];
                this.assignments = Array.isArray(assignments) ? assignments : [];
                this.faculties = Array.isArray(faculties) ? faculties : [];

                this.coursesRoot = coursesRoot || null;
                this.assignmentsRoot = assignmentsRoot || null;

                this.courseTableBody = document.getElementById('coursesTableBody');
                this.minorcourseTableBody = document.getElementById('minorsCoursesTableBody'); //new add  
                this.assignmentTableBody = document.getElementById('assignmentsTableBody');
                this.minorassignmentTableBody = document.getElementById('minorassignmentsTableBody');

                this.courseSelectAll = document.getElementById('coursesSelectAll');
                this.assignmentSelectAll = document.getElementById('assignmentsSelectAll');

                this.courseBulkBar = document.getElementById('courseBulkBar');
                this.assignmentBulkBar = document.getElementById('assignmentBulkBar');
                this.courseSelectedCountEl = document.getElementById('courseSelectedCount');
                this.assignmentSelectedCountEl = document.getElementById('assignmentSelectedCount');
                this.assignmentFilters = {
                    faculty: 'all',
                    year: 'all',
                    semester: 'all'
                };
                this.assignmentFilterControls = {
                    faculty: document.getElementById('assignmentFilterFaculty'),
                    year: document.getElementById('assignmentFilterYear'),
                    semester: document.getElementById('assignmentFilterSemester'),
                };
                this.assignmentFilterReset = document.getElementById('assignmentFilterReset');
                this.assignmentFilterToggle = document.getElementById('assignmentFilterToggle');

                this.courseCreateModalEl = document.getElementById('courseCreateModal');
                this.courseEditModalEl = document.getElementById('courseEditModal');
                this.courseDeleteModalEl = document.getElementById('courseDeleteModal');
                this.courseBulkDeleteModalEl = document.getElementById('courseBulkDeleteModal');

                this.assignmentCreateModalEl = document.getElementById('assignmentCreateModal');
                this.assignmentEditModalEl = document.getElementById('assignmentEditModal');
                this.assignmentDeleteModalEl = document.getElementById('assignmentDeleteModal');
                this.assignmentBulkDeleteModalEl = document.getElementById('assignmentBulkDeleteModal');

                const hasBootstrap = typeof bootstrap !== 'undefined' && bootstrap?.Modal;
                this.courseCreateModal = this.courseCreateModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .courseCreateModalEl) : null;
                this.courseEditModal = this.courseEditModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .courseEditModalEl) : null;
                this.courseDeleteModal = this.courseDeleteModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .courseDeleteModalEl) : null;
                this.courseBulkDeleteModal = this.courseBulkDeleteModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .courseBulkDeleteModalEl) : null;

                this.assignmentCreateModal = this.assignmentCreateModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .assignmentCreateModalEl) : null;
                this.assignmentEditModal = this.assignmentEditModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .assignmentEditModalEl) : null;
                this.assignmentDeleteModal = this.assignmentDeleteModalEl && hasBootstrap ? new bootstrap.Modal(this
                    .assignmentDeleteModalEl) : null;
                this.assignmentBulkDeleteModal = this.assignmentBulkDeleteModalEl && hasBootstrap ? new bootstrap.Modal(
                    this.assignmentBulkDeleteModalEl) : null;

                this.alertContainer = document.getElementById('courseAlertContainer');

                this.courseSelection = new Set();
                this.assignmentSelection = new Set();
                this.courseSortState = {
                    key: null,
                    direction: 'asc'
                };
                this.assignmentSortState = {
                    key: null,
                    direction: 'asc'
                };
                this.courseSortHeaders = [];
                this.assignmentSortHeaders = [];

                this.pendingCourseDeleteId = null;
                this.pendingAssignmentDeleteId = null;
                this.pendingCourseBulk = null;
                this.pendingAssignmentBulk = null;

                this.currentSection = 'courses';

                this.sectionButtons = document.querySelectorAll('[data-course-section]');
                this.sections = {
                    courses: document.getElementById('coursesSection'),
                    assignments: document.getElementById('assignmentsSection'),
                };

                this.courseController = null;
                this.assignmentController = null;
                this.lastCourseScopeKey = this.getCourseSelectionScopeKey();
                this.lastAssignmentScopeKey = this.getAssignmentSelectionScopeKey();

                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                this.init();
            }

            init() {
                this.bindSectionButtons();
                this.bindForms();
                this.bindSearchInputs();
                this.bindBulkBars();
                this.initCourseSorting();
                this.initAssignmentSorting();
                this.initAssignmentFilters();
                this.renderCourseTable();
                this.renderAssignmentTable();
                this.refreshCourseDropdownOptions();
                this.refreshSectionDropdownOptions();
                this.initSelectStyling();
                this.setActiveSection('courses');
            }

            bindSectionButtons() {
                this.sectionButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const section = button.dataset.courseSection;
                        if (section) {
                            this.setActiveSection(section);
                        }
                    });
                });
            }

            setActiveSection(section) {
                if (!['courses', 'assignments'].includes(section)) {
                    return;
                }

                this.currentSection = section;
                Object.entries(this.sections).forEach(([key, element]) => {
                    if (!element) {
                        return;
                    }
                    element.classList.toggle('d-none', key !== section);
                });

                this.sectionButtons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.courseSection === section);
                });
            }

            bindForms() {
                const courseCreateForm = document.getElementById('courseCreateForm');
                if (courseCreateForm) {
                    courseCreateForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const data = this.formToObject(courseCreateForm);
                        this.handleCreateCourse(courseCreateForm, data);
                    });
                    this.courseCreateModalEl?.addEventListener('hidden.bs.modal', () => {
                        courseCreateForm.reset();
                    });
                }

                const courseEditForm = document.getElementById('courseEditForm');
                if (courseEditForm) {
                    courseEditForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const data = this.formToObject(courseEditForm);
                        this.handleUpdateCourse(courseEditForm, data);
                    });
                }

                const courseImportForm = document.getElementById('courseImportForm');
                if (courseImportForm) {
                    courseImportForm.addEventListener('submit', () => {
                        const submitBtn = courseImportForm.querySelector('[type="submit"]');
                        this.toggleButtonLoading(submitBtn, true, 'Import', 'Importing...');
                    });
                }

                const confirmCourseDelete = document.getElementById('confirmCourseDeleteBtn');
                if (confirmCourseDelete) {
                    confirmCourseDelete.addEventListener('click', () => {
                        if (this.pendingCourseDeleteId) {
                            this.handleDeleteCourse(confirmCourseDelete, this.pendingCourseDeleteId);
                        }
                    });
                }

                const confirmCourseBulk = document.getElementById('confirmCourseBulkDeleteBtn');
                if (confirmCourseBulk) {
                    confirmCourseBulk.addEventListener('click', () => {
                        if (Array.isArray(this.pendingCourseBulk) && this.pendingCourseBulk.length) {
                            this.executeBulkCourseDelete(confirmCourseBulk, this.pendingCourseBulk.slice());
                        }
                    });
                }

                const assignmentCreateForm = document.getElementById('assignmentCreateForm');
                if (assignmentCreateForm) {
                    assignmentCreateForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const data = this.formToObject(assignmentCreateForm);
                        this.handleCreateAssignment(assignmentCreateForm, data);
                    });
                    this.assignmentCreateModalEl?.addEventListener('hidden.bs.modal', () => {
                        assignmentCreateForm.reset();
                    });
                }

                const assignmentEditForm = document.getElementById('assignmentEditForm');
                if (assignmentEditForm) {
                    assignmentEditForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const data = this.formToObject(assignmentEditForm);
                        this.handleUpdateAssignment(assignmentEditForm, data);
                    });
                }

                const confirmAssignmentDelete = document.getElementById('confirmAssignmentDeleteBtn');
                if (confirmAssignmentDelete) {
                    confirmAssignmentDelete.addEventListener('click', () => {
                        if (this.pendingAssignmentDeleteId) {
                            this.handleDeleteAssignment(confirmAssignmentDelete, this
                                .pendingAssignmentDeleteId);
                        }
                    });
                }

                const confirmAssignmentBulk = document.getElementById('confirmAssignmentBulkDeleteBtn');
                if (confirmAssignmentBulk) {
                    confirmAssignmentBulk.addEventListener('click', () => {
                        if (Array.isArray(this.pendingAssignmentBulk) && this.pendingAssignmentBulk.length) {
                            this.executeBulkAssignmentDelete(confirmAssignmentBulk, this.pendingAssignmentBulk
                                .slice());
                        }
                    });
                }
            }

            initSelectStyling() {
                const selects = document.querySelectorAll('.evaluation-modal-body select.form-select');
                if (!selects.length) {
                    return;
                }

                selects.forEach((select) => {
                    const updateState = () => this.updateSelectPlaceholderState(select);
                    updateState();
                    select.addEventListener('change', updateState);

                    const parentForm = select.closest('form');
                    if (parentForm) {
                        parentForm.addEventListener('reset', () => {
                            setTimeout(updateState, 0);
                        });
                    }
                });
            }

            updateSelectPlaceholderState(select) {
                if (!select) {
                    return;
                }

                const value = select.value;
                const hasValue = value != null && String(value).trim() !== '';
                select.classList.toggle('is-placeholder', !hasValue);
            }

            setDropdownValue(hiddenInput, value, labelText = '') {
                if (!hiddenInput) {
                    return;
                }

                hiddenInput.value = value ?? '';
                const dropdown = hiddenInput.closest('[data-searchable-dropdown]');
                if (!dropdown) {
                    return;
                }

                const labelEl = dropdown.querySelector('[data-dropdown-label]');
                const placeholder = labelEl?.dataset?.placeholderText?.trim() ||
                    dropdown.__searchableDropdown?.placeholder ||
                    '-- Select Option --';

                if (value && value !== '') {
                    let displayLabel = labelText && labelText.trim() !== '' ? labelText : '';
                    if (!displayLabel) {
                        const option = Array.from(dropdown.querySelectorAll('[data-dropdown-option]'))
                            .find((opt) => opt.dataset.optionValue === String(value));
                        displayLabel = option?.dataset?.optionLabel ??
                            option?.querySelector('span')?.textContent?.trim() ??
                            placeholder;
                    }
                    if (dropdown.__searchableDropdown?.setLabel) {
                        dropdown.__searchableDropdown.setLabel(displayLabel, false);
                    } else if (labelEl) {
                        labelEl.textContent = displayLabel;
                        labelEl.classList.remove('is-placeholder');
                    }
                } else if (dropdown.__searchableDropdown?.setLabel) {
                    dropdown.__searchableDropdown.setLabel(placeholder, true);
                } else if (labelEl) {
                    labelEl.textContent = placeholder;
                    labelEl.classList.add('is-placeholder');
                }
            }

            ensureDropdownOption(hiddenInput, {
                value,
                label,
                description = ''
            }) {
                if (!hiddenInput || value == null) {
                    return;
                }

                const dropdown = hiddenInput.closest('[data-searchable-dropdown]');
                if (!dropdown) {
                    return;
                }

                const list = dropdown.querySelector('[data-dropdown-list]');
                if (!list) {
                    return;
                }

                const valueStr = String(value);
                const existing = Array.from(list.querySelectorAll('[data-dropdown-option]'))
                    .find((option) => option.dataset.optionValue === valueStr);
                if (existing) {
                    return existing;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'dropdown-item';
                button.setAttribute('data-dropdown-option', '');
                button.dataset.optionValue = valueStr;

                const resolvedLabel = label && label.trim() !== '' ? label : `Option #${valueStr}`;
                button.dataset.optionLabel = resolvedLabel;
                button.dataset.optionFilter = (resolvedLabel + ' ' + (description || '')).toLowerCase();

                const span = document.createElement('span');
                span.textContent = resolvedLabel;
                button.appendChild(span);

                if (description) {
                    const small = document.createElement('small');
                    small.textContent = description;
                    button.appendChild(small);
                }

                list.appendChild(button);
                dropdown.__searchableDropdown?.applyFilter?.();
                return button;
            }

            refreshCourseDropdownOptions() {
                const targetInputs = [
                    document.getElementById('createAssignmentCourse'),
                    document.getElementById('editAssignmentCourse'),
                ].filter(Boolean);

                if (!targetInputs.length) {
                    return;
                }

                const optionsHtml = (this.courses || [])
                    .slice()
                    .sort((a, b) => {
                        const labelA = this.getCourseDropdownLabel(a).toLowerCase();
                        const labelB = this.getCourseDropdownLabel(b).toLowerCase();
                        return labelA.localeCompare(labelB);
                    })
                    .map((course) => this.buildCourseDropdownOption(course))
                    .join('');

                targetInputs.forEach((input) => {
                    this.replaceDropdownOptions(input, optionsHtml);
                });
            }

            getCourseDropdownLabel(course) {
                if (!course) {
                    return '';
                }
                const code = this.formatCourseText(course.class_code, '');
                const subject = this.formatCourseText(course.subject_code, '');
                const fallback = course.id != null ? `Course #${course.id}` : 'Course';
                return [code || null, subject || null].filter(Boolean).join(' - ') || fallback;
            }

            buildCourseDropdownOption(course) {
                if (!course) {
                    return '';
                }
                const code = this.formatCourseText(course.class_code, '');
                const subject = this.formatCourseText(course.subject_code, '');
                const spanValue = code || subject || `Course #${course.id ?? ''}`;
                const includeSubject = Boolean(code && subject);
                const subjectMarkup = includeSubject ? `<small>${this.escapeHtml(subject)}</small>` : '';
                const labelAttr = this.escapeAttribute(this.getCourseDropdownLabel(course));
                const searchValue = `${course.class_code ?? ''} ${course.subject_code ?? ''}`.trim().toLowerCase();
                const searchAttr = this.escapeAttribute(searchValue || spanValue.toLowerCase());

                return `
                    <button type="button"
                            class="dropdown-item"
                            data-dropdown-option
                            data-option-value="${this.escapeAttribute(String(course.id ?? ''))}"
                            data-option-label="${labelAttr}"
                            data-option-filter="${searchAttr}">
                        <span>${this.escapeHtml(spanValue)}</span>
                        ${subjectMarkup}
                    </button>
                `;
            }

            replaceDropdownOptions(hiddenInput, optionsHtml) {
                if (!hiddenInput) {
                    return;
                }
                const dropdown = hiddenInput.closest('[data-searchable-dropdown]');
                if (!dropdown) {
                    return;
                }
                const list = dropdown.querySelector('[data-dropdown-list]');
                if (!list) {
                    return;
                }

                if (optionsHtml && optionsHtml.trim() !== '') {
                    list.innerHTML = optionsHtml;
                } else {
                    list.innerHTML = '<div class="text-muted small px-2 py-1">No courses available</div>';
                }

                dropdown.__searchableDropdown?.applyFilter?.();

                const currentValue = hiddenInput.value;
                if (currentValue) {
                    const match = Array.from(list.querySelectorAll('[data-dropdown-option]'))
                        .find((option) => option.dataset.optionValue === currentValue);
                    if (match) {
                        const labelText = match.dataset.optionLabel ||
                            match.querySelector('span')?.textContent?.trim() ||
                            '';
                        this.setDropdownValue(hiddenInput, currentValue, labelText);
                        return;
                    }
                }

                hiddenInput.value = '';
                dropdown.__searchableDropdown?.resetSelection?.();
            }

            bindSearchInputs() {
                this.bindSearchPair(
                    document.getElementById('coursesSearch'),
                    document.getElementById('coursesSearchClear')
                );
                this.bindSearchPair(
                    document.getElementById('assignmentsSearch'),
                    document.getElementById('assignmentsSearchClear')
                );
            }

            bindSearchPair(input, clear) {
                if (!input || !clear) {
                    return;
                }

                const toggle = () => {
                    if (input.value.trim() === '') {
                        clear.classList.remove('is-visible');
                    } else {
                        clear.classList.add('is-visible');
                    }
                };

                input.addEventListener('input', toggle);
                clear.addEventListener('click', () => {
                    input.value = '';
                    toggle();
                    input.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                });

                toggle();
            }

            bindBulkBars() {
                if (this.courseBulkBar) {
                    this.courseBulkBar.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-bulk-action]');
                        if (!button) {
                            return;
                        }
                        const action = button.dataset.bulkAction;
                        if (action === 'clear-course') {
                            this.clearCourseSelection();
                        } else if (action === 'delete-course') {
                            this.promptBulkCourseDelete();
                        }
                    });
                }

                if (this.assignmentBulkBar) {
                    this.assignmentBulkBar.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-bulk-action]');
                        if (!button) {
                            return;
                        }
                        const action = button.dataset.bulkAction;
                        if (action === 'clear-assignment') {
                            this.clearAssignmentSelection();
                        } else if (action === 'delete-assignment') {
                            this.promptBulkAssignmentDelete();
                        }
                    });
                }
            }

            initCourseSorting() {
                this.courseSortHeaders = Array.from(document.querySelectorAll('#coursesTable thead th[data-sort-key]'));
                this.courseSortHeaders.forEach((header) => {
                    header.dataset.sortState = header.dataset.sortState || 'none';
                    header.addEventListener('click', () => {
                        const sortKey = header.dataset.sortKey;
                        if (!sortKey) {
                            return;
                        }

                        if (this.courseSortState.key === sortKey) {
                            this.courseSortState.direction = this.courseSortState.direction === 'asc' ?
                                'desc' : 'asc';
                        } else {
                            this.courseSortState.key = sortKey;
                            this.courseSortState.direction = 'asc';
                        }

                        this.updateCourseSortIndicators();
                        this.renderCourseTable();
                    });
                });
                this.updateCourseSortIndicators();
            }

            initAssignmentSorting() {
                this.assignmentSortHeaders = Array.from(document.querySelectorAll(
                    '#assignmentsTable thead th[data-sort-key]'));
                this.assignmentSortHeaders.forEach((header) => {
                    header.dataset.sortState = header.dataset.sortState || 'none';
                    header.addEventListener('click', () => {
                        const sortKey = header.dataset.sortKey;
                        if (!sortKey) {
                            return;
                        }

                        if (this.assignmentSortState.key === sortKey) {
                            this.assignmentSortState.direction = this.assignmentSortState.direction ===
                                'asc' ? 'desc' : 'asc';
                        } else {
                            this.assignmentSortState.key = sortKey;
                            this.assignmentSortState.direction = 'asc';
                        }

                        this.updateAssignmentSortIndicators();
                        this.renderAssignmentTable();
                    });
                });
                this.updateAssignmentSortIndicators();
            }

            updateCourseSortIndicators() {
                if (!Array.isArray(this.courseSortHeaders)) {
                    return;
                }

                this.courseSortHeaders.forEach((header) => {
                    header.classList.remove('sorted-asc', 'sorted-desc');
                    header.dataset.sortState = 'none';

                    if (header.dataset.sortKey === this.courseSortState.key) {
                        const state = this.courseSortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
                        header.classList.add(state);
                        header.dataset.sortState = this.courseSortState.direction;
                    }
                });
            }

            updateAssignmentSortIndicators() {
                if (!Array.isArray(this.assignmentSortHeaders)) {
                    return;
                }

                this.assignmentSortHeaders.forEach((header) => {
                    header.classList.remove('sorted-asc', 'sorted-desc');
                    header.dataset.sortState = 'none';

                    if (header.dataset.sortKey === this.assignmentSortState.key) {
                        const state = this.assignmentSortState.direction === 'asc' ? 'sorted-asc' :
                            'sorted-desc';
                        header.classList.add(state);
                        header.dataset.sortState = this.assignmentSortState.direction;
                    }
                });
            }

            renderCourseTable() {
                if (!this.courseTableBody) {
                    return;
                }

                const data = this.getSortedCourses();
                const availableIds = new Set(data.map((course) => String(course.id)));
                Array.from(this.courseSelection).forEach((id) => {
                    if (!availableIds.has(String(id))) {
                        this.courseSelection.delete(String(id));
                    }
                });

                const bodyHtml = !Array.isArray(data) || data.length === 0 ?
                    this.buildCourseEmptyRow() :
                    data.map((course) => this.buildCourseRow(course)).join('');

                const courseColspan = (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ? 5 : 4;
                this.courseTableBody.innerHTML = bodyHtml + this.buildSearchEmptyRow(courseColspan);

                this.attachCourseRowEvents();
                this.bindCourseSelectionHandlers();
                this.syncCourseSelection();
                this.updateCourseBulkBar();
                this.ensureCourseController();
                if (this.coursesRoot) {
                    applyPillPalettes(this.coursesRoot);
                }
            }

            renderAssignmentTable() {
                if (!this.assignmentTableBody) {
                    return;
                }

                const data = this.getFilteredAssignments();
                const availableIds = new Set(data.map((assignment) => String(assignment.id)));
                Array.from(this.assignmentSelection).forEach((id) => {
                    if (!availableIds.has(String(id))) {
                        this.assignmentSelection.delete(String(id));
                    }
                });

                const bodyHtml = !Array.isArray(data) || data.length === 0 ?
                    this.buildAssignmentEmptyRow() :
                    data.map((assignment) => this.buildAssignmentRow(assignment)).join('');

                const assignmentColspan = (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ? 7 : 6;
                this.assignmentTableBody.innerHTML = bodyHtml + this.buildSearchEmptyRow(assignmentColspan);

                this.attachAssignmentRowEvents();
                this.bindAssignmentSelectionHandlers();
                this.syncAssignmentSelection();
                this.updateAssignmentBulkBar();
                this.ensureAssignmentController();
                this.updateAssignmentFilterToggleState();
                if (this.assignmentsRoot) {
                    applyPillPalettes(this.assignmentsRoot);
                }
            }

            getSortedCourses() {
                const data = Array.isArray(this.courses) ? [...this.courses] : [];
                if (!this.courseSortState.key) {
                    return data.sort((a, b) => Number(b?.id ?? 0) - Number(a?.id ?? 0));
                }

                const multiplier = this.courseSortState.direction === 'asc' ? 1 : -1;
                return data.sort((a, b) => {
                    const valueA = this.getCourseSortValue(a, this.courseSortState.key);
                    const valueB = this.getCourseSortValue(b, this.courseSortState.key);

                    if (typeof valueA === 'number' && typeof valueB === 'number') {
                        return (valueA - valueB) * multiplier;
                    }

                    return String(valueA ?? '').localeCompare(String(valueB ?? ''), undefined, {
                        sensitivity: 'base'
                    }) * multiplier;
                });
            }

            getCourseSortValue(course, key) {
                switch (key) {
                    case 'code':
                        return course?.class_code ?? '';
                    case 'subject':
                        return course?.subject_code ?? '';
                    case 'assignments':
                        return Number(course?.assignments ?? 0);
                    default:
                        return '';
                }
            }

            getFilteredAssignments() {
                const data = this.getSortedAssignments();
                return data.filter((assignment) => this.matchesAssignmentFilters(assignment));
            }

            getSortedAssignments() {
                const data = Array.isArray(this.assignments) ? [...this.assignments] : [];
                if (!this.assignmentSortState.key) {
                    return data.sort((a, b) => Number(b?.id ?? 0) - Number(a?.id ?? 0));
                }

                const multiplier = this.assignmentSortState.direction === 'asc' ? 1 : -1;
                return data.sort((a, b) => {
                    const valueA = this.getAssignmentSortValue(a, this.assignmentSortState.key);
                    const valueB = this.getAssignmentSortValue(b, this.assignmentSortState.key);
                    return String(valueA ?? '').localeCompare(String(valueB ?? ''), undefined, {
                        sensitivity: 'base'
                    }) * multiplier;
                });
            }

            getAssignmentSortValue(assignment, key) {
                switch (key) {
                    case 'faculty':
                        return assignment?.faculty_name ?? '';
                    case 'course':
                        return assignment?.course_class_code ?? '';
                    case 'section':
                        return assignment?.section ?? '';
                    case 'year':
                        return assignment?.academic_year ?? '';
                    case 'semester':
                        return this.formatSemester(assignment?.semester);
                    default:
                        return '';
                }
            }

            ensureCourseController() {
                if (!this.coursesRoot || typeof TableController === 'undefined') {
                    return;
                }

                if (this.courseController) {
                    this.courseController.refresh();
                } else {
                    this.courseController = new TableController(this.coursesRoot);
                    window.tableControllers = window.tableControllers || {};
                    window.tableControllers.coursesTable = this.courseController;
                }
                if (this.coursesRoot && !this.coursesRoot.dataset.pillPaletteBound) {
                    this.coursesRoot.dataset.pillPaletteBound = 'true';
                    this.coursesRoot.addEventListener('table:updated', () => {
                        applyPillPalettes(this.coursesRoot);
                    });
                }
                if (this.coursesRoot && !this.coursesRoot.dataset.selectionScopeBound) {
                    this.coursesRoot.dataset.selectionScopeBound = 'true';
                    this.lastCourseScopeKey = this.getCourseSelectionScopeKey();
                    this.coursesRoot.addEventListener('table:updated', () => {
                        const scopeKey = this.getCourseSelectionScopeKey();
                        if (this.courseSelectAll?.checked && scopeKey !== this.lastCourseScopeKey) {
                            this.lastCourseScopeKey = scopeKey;
                            this.clearCourseSelection();
                        } else {
                            this.lastCourseScopeKey = scopeKey;
                        }
                    });
                }
            }

            ensureAssignmentController() {
                if (!this.assignmentsRoot || typeof TableController === 'undefined') {
                    return;
                }

                if (this.assignmentController) {
                    this.assignmentController.refresh();
                } else {
                    this.assignmentController = new TableController(this.assignmentsRoot);
                    window.tableControllers = window.tableControllers || {};
                    window.tableControllers.assignmentsTable = this.assignmentController;
                }
                if (this.assignmentsRoot && !this.assignmentsRoot.dataset.pillPaletteBound) {
                    this.assignmentsRoot.dataset.pillPaletteBound = 'true';
                    this.assignmentsRoot.addEventListener('table:updated', () => {
                        applyPillPalettes(this.assignmentsRoot);
                    });
                }
                if (this.assignmentsRoot && !this.assignmentsRoot.dataset.selectionScopeBound) {
                    this.assignmentsRoot.dataset.selectionScopeBound = 'true';
                    this.lastAssignmentScopeKey = this.getAssignmentSelectionScopeKey();
                    this.assignmentsRoot.addEventListener('table:updated', () => {
                        const scopeKey = this.getAssignmentSelectionScopeKey();
                        if (this.assignmentSelectAll?.checked && scopeKey !== this.lastAssignmentScopeKey) {
                            this.lastAssignmentScopeKey = scopeKey;
                            this.clearAssignmentSelection();
                        } else {
                            this.lastAssignmentScopeKey = scopeKey;
                        }
                    });
                }
            }

            buildCourseRow(course) {
                const id = String(course.id);
                const isSelected = this.courseSelection.has(id);
                const classCodeRaw = this.formatCourseText(course.class_code, 'N/A');
                const classCode = this.escapeHtml(classCodeRaw);
                const classCodeAttr = 'course-code';
                const subjectRaw = this.formatCourseText(course.subject_code, 'N/A');
                const subject = this.escapeHtml(subjectRaw);
                const subjectType = course.subject_type === 'major' ? '<span class="badge bg-success">Major</span>' :
                    course.subject_type === 'minor' ?
                    '<span class="badge bg-warning text-dark">Minor</span>' :
                    '<span class="badge bg-secondary">N/A</span>';
                const assignments = Number(course.assignments ?? 0);
                const searchTerms = [course.class_code ?? '', course.subject_code ?? ''].join(' ').toLowerCase();

                const selectionCell = (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ?
                    `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input evaluation-checkbox" data-row-select value="${id}" ${isSelected ? 'checked' : ''}>
                        </td>
                    ` :
                    '';

                const courseActions = `
                    ${coursePermissions.canEdit ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item" data-action="edit-course" data-id="${id}">Edit</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                    ${coursePermissions.canEdit && (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ? '<li><hr class="dropdown-divider"></li>' : ''}
                    ${coursePermissions.canDelete ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item text-danger" data-action="delete-course" data-id="${id}" data-name="${this.escapeAttribute(classCodeRaw)}">Delete</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                    ${(!coursePermissions.canDelete && coursePermissions.showDeleteDisabled) ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item disabled text-muted" disabled aria-disabled="true">Delete</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                `;

                const actionsCell = (coursePermissions.canEdit || coursePermissions.canDelete || coursePermissions
                        .showDeleteDisabled) ?
                    `
                        <td class="actions-cell">
                            <div class="dropdown evaluation-actions">
                                <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${courseActions}
                                </ul>
                            </div>
                        </td>
                    ` :
                    '<td class="text-muted">—</td>';

                return `
                    <tr data-course-id="${id}" class="${isSelected ? 'is-selected' : ''}" data-search="${this.escapeAttribute(searchTerms)}">
                        ${selectionCell}
                        <td>
                            <span class="evaluation-pill course-pill--class"
                                  data-pill-palette="purple"
                                  data-pill-value="${classCodeAttr}">${classCode}</span>
                        </td>
                        <td>
                            <span class="table-text-truncate is-wide" title="${this.escapeAttribute(subjectRaw)}">${subject}</span>
                        </td>
                        <td>
                            <span class="table-text-truncate is-wide" title="">${subjectType}</span>
                        </td>
                        <td>
                            <span class="evaluation-count-pill">${assignments}</span>
                        </td>
                        ${actionsCell}
                    </tr>
                `;
            }

            buildAssignmentRow(assignment) {
                console.log(assignment);
                const id = String(assignment.id);
                const isSelected = this.assignmentSelection.has(id);
                const facultyName = this.formatDisplayText(assignment.faculty_name ?? 'N/A');
                const facultyEmailRaw = assignment.faculty_email ?? '';
                const facultyEmail = this.escapeHtml(facultyEmailRaw);
                const courseCodeSource = assignment.course_class_code ?? '';
                const courseCodeRaw = this.formatCourseText(courseCodeSource, 'N/A');
                const courseCode = this.escapeHtml(courseCodeRaw);
                const subjectType = assignment.course_subject_type === 'major' ?
                    '<span class="badge bg-success">Major</span>' :
                    assignment.course_subject_type === 'minor' ?
                    '<span class="badge bg-warning text-dark">Minor</span>' :
                    '<span class="badge bg-secondary">N/A</span>';
                const courseCodeAttr = 'course-code';
                const courseSubjectSource = assignment.course_subject_code ?? '';
                const courseSubjectRaw = this.formatCourseText(courseSubjectSource, '');
                const courseSubject = this.escapeHtml(courseSubjectRaw);
                const sectionRaw = this.formatCourseText(assignment.section ?? '', 'N/A');
                const section = this.escapeHtml(sectionRaw);
                const sectionAttr = this.escapeAttribute(sectionRaw);
                const academicYearRaw = assignment.academic_year ?? 'N/A';
                const academicYear = this.escapeHtml(academicYearRaw);
                const academicYearAttr = this.escapeAttribute(academicYearRaw);
                const semesterRaw = this.formatSemester(assignment.semester);
                const semester = this.escapeHtml(semesterRaw);
                const semesterAttr = this.escapeAttribute(String(semesterRaw));
                const searchTerms = [
                    facultyName,
                    facultyEmail,
                    courseCodeSource,
                    courseSubjectSource,
                    assignment.section ?? '',
                    assignment.academic_year ?? '',
                    assignment.semester ?? ''
                ].join(' ').toLowerCase();

                const selectionCell = (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ?
                    `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input evaluation-checkbox" data-row-select value="${id}" ${isSelected ? 'checked' : ''}>
                        </td>
                    ` :
                    '';

                const assignmentActions = `
                    ${coursePermissions.canEdit ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item" data-action="edit-assignment" data-id="${id}">Edit</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                    ${coursePermissions.canEdit && (coursePermissions.canDelete || coursePermissions.showDeleteDisabled) ? '<li><hr class="dropdown-divider"></li>' : ''}
                    ${coursePermissions.canDelete ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item text-danger" data-action="delete-assignment" data-id="${id}" data-name="${this.escapeAttribute(facultyName)}">Delete</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                    ${(!coursePermissions.canDelete && coursePermissions.showDeleteDisabled) ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" class="dropdown-item disabled text-muted" disabled aria-disabled="true">Delete</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ` : ''}
                `;

                const actionsCell = (coursePermissions.canEdit || coursePermissions.canDelete || coursePermissions
                        .showDeleteDisabled) ?
                    `
                        <td class="actions-cell">
                            <div class="dropdown evaluation-actions">
                                <button class="evaluation-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${assignmentActions}
                                </ul>
                            </div>
                        </td>
                    ` :
                    '<td class="text-muted">—</td>';

                return `
                    <tr data-assignment-id="${id}" class="${isSelected ? 'is-selected' : ''}" data-search="${this.escapeAttribute(searchTerms)}">
                        ${selectionCell}
                        <td>
                            <div class="table-cell-stack is-wide" title="${this.escapeAttribute([facultyName, facultyEmailRaw].filter(Boolean).join(' • '))}">
                                <span class="text-dark d-block">${this.escapeHtml(facultyName)}</span>
                                ${facultyEmail ? `<small class="text-muted">${facultyEmail}</small>` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="table-cell-stack is-wide" title="${this.escapeAttribute([assignment.course_class_code ?? '', courseSubjectSource].filter(Boolean).join(' — '))}">
                                <span class="evaluation-pill course-pill--class"
                                      data-pill-palette="purple"
                                      data-pill-value="${courseCodeAttr}">${courseCode}</span>
                                ${courseSubject ? `<small class="text-muted d-block mt-1">${courseSubject}</small>` : ''}
                            </div>
                        </td>

                        <td>
                           ${subjectType}
                        </td>
                        <td>
                            <span class="evaluation-pill"
                                  data-pill-palette="green"
                                  data-pill-value="${sectionAttr}">${section}</span>
                        </td>

                        
                         
                        <td>
                            <span class="evaluation-pill"
                                  data-pill-palette="blue"
                                  data-pill-value="${academicYearAttr}">${academicYear}</span>
                        </td>
                        <td>
                            <span class="evaluation-pill"
                                  data-pill-palette="gray"
                                  data-pill-value="${semesterAttr}">${semester}</span>
                        </td>
                        ${actionsCell}
                    </tr>
                `;
            }

            buildCourseEmptyRow() {
                return `
                    <tr data-empty>
                        <td colspan="{{ $canDelete || $showDeleteDisabled ? 5 : 4 }}" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-book display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No courses available</h5>
                                <p class="text-muted mb-0">Add a course using the button above to get started.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildAssignmentEmptyRow() {
                return `
                    <tr data-empty>
                        <td colspan="{{ $canDelete || $showDeleteDisabled ? 7 : 6 }}" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-book display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No assignments found</h5>
                                <p class="text-muted mb-0">Assign a course using the button above to populate this list.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildSearchEmptyRow(colspan) {
                return `
                    <tr data-empty-search style="display: none;">
                        <td colspan="${colspan}" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-magnifying-glass display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No results found</h5>
                                <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            attachCourseRowEvents() {
                if (!this.courseTableBody) {
                    return;
                }

                this.courseTableBody.querySelectorAll('[data-action="edit-course"]').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        this.openCourseEditModal(id);
                    });
                });

                this.courseTableBody.querySelectorAll('[data-action="delete-course"]').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        const name = button.dataset.name || 'this course';
                        this.openCourseDeleteModal(id, name);
                    });
                });
            }

            attachAssignmentRowEvents() {
                if (!this.assignmentTableBody) {
                    return;
                }

                this.assignmentTableBody.querySelectorAll('[data-action="edit-assignment"]').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        this.openAssignmentEditModal(id);
                    });
                });

                this.assignmentTableBody.querySelectorAll('[data-action="delete-assignment"]').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }
                    button.dataset.bound = 'true';
                    button.addEventListener('click', () => {
                        const id = Number(button.dataset.id);
                        const name = button.dataset.name || 'this assignment';
                        this.openAssignmentDeleteModal(id, name);
                    });
                });
            }

            bindCourseSelectionHandlers() {
                if (!this.courseTableBody) {
                    return;
                }

                const checkboxes = Array.from(this.courseTableBody.querySelectorAll('[data-row-select]'));
                checkboxes.forEach((checkbox) => {
                    const id = checkbox.value;
                    checkbox.checked = this.courseSelection.has(id);
                    const row = checkbox.closest('tr');
                    if (row) {
                        row.classList.toggle('is-selected', checkbox.checked);
                    }

                    checkbox.onchange = () => {
                        if (checkbox.checked) {
                            this.courseSelection.add(id);
                        } else {
                            this.courseSelection.delete(id);
                        }
                        if (row) {
                            row.classList.toggle('is-selected', checkbox.checked);
                        }
                        this.syncCourseSelection();
                        this.updateCourseBulkBar();
                    };
                });

                if (this.courseSelectAll) {
                    this.courseSelectAll.onchange = () => {
                        const shouldSelect = this.courseSelectAll.checked;
                        this.getCourseSelectableRows().forEach((row) => {
                            const checkbox = row.querySelector('[data-row-select]');
                            if (!checkbox) {
                                return;
                            }
                            checkbox.checked = shouldSelect;
                            const id = checkbox.value;
                            if (shouldSelect) {
                                this.courseSelection.add(id);
                            } else {
                                this.courseSelection.delete(id);
                            }
                            row.classList.toggle('is-selected', shouldSelect);
                        });
                        this.syncCourseSelection();
                        this.updateCourseBulkBar();
                    };
                }
            }

            bindAssignmentSelectionHandlers() {
                if (!this.assignmentTableBody) {
                    return;
                }

                const checkboxes = Array.from(this.assignmentTableBody.querySelectorAll('[data-row-select]'));
                checkboxes.forEach((checkbox) => {
                    const id = checkbox.value;
                    checkbox.checked = this.assignmentSelection.has(id);
                    const row = checkbox.closest('tr');
                    if (row) {
                        row.classList.toggle('is-selected', checkbox.checked);
                    }

                    checkbox.onchange = () => {
                        if (checkbox.checked) {
                            this.assignmentSelection.add(id);
                        } else {
                            this.assignmentSelection.delete(id);
                        }
                        if (row) {
                            row.classList.toggle('is-selected', checkbox.checked);
                        }
                        this.syncAssignmentSelection();
                        this.updateAssignmentBulkBar();
                    };
                });

                if (this.assignmentSelectAll) {
                    this.assignmentSelectAll.onchange = () => {
                        const shouldSelect = this.assignmentSelectAll.checked;
                        this.getAssignmentSelectableRows().forEach((row) => {
                            const checkbox = row.querySelector('[data-row-select]');
                            if (!checkbox) {
                                return;
                            }
                            checkbox.checked = shouldSelect;
                            const id = checkbox.value;
                            if (shouldSelect) {
                                this.assignmentSelection.add(id);
                            } else {
                                this.assignmentSelection.delete(id);
                            }
                            row.classList.toggle('is-selected', shouldSelect);
                        });
                        this.syncAssignmentSelection();
                        this.updateAssignmentBulkBar();
                    };
                }
            }

            getCourseSelectableRows() {
                if (this.courseController && Array.isArray(this.courseController.filteredRows)) {
                    return this.courseController.filteredRows;
                }
                if (!this.courseTableBody) {
                    return [];
                }
                return Array.from(this.courseTableBody.querySelectorAll('tr[data-course-id]'));
            }

            getAssignmentSelectableRows() {
                if (this.assignmentController && Array.isArray(this.assignmentController.filteredRows)) {
                    return this.assignmentController.filteredRows;
                }
                if (!this.assignmentTableBody) {
                    return [];
                }
                return Array.from(this.assignmentTableBody.querySelectorAll('tr[data-assignment-id]'));
            }

            getCourseSelectionScopeKey() {
                return this.courseController?.searchTerm ?? '';
            }

            getAssignmentSelectionScopeKey() {
                const searchTerm = this.assignmentController?.searchTerm ?? '';
                const filterKey = JSON.stringify(this.assignmentFilters);
                return `${searchTerm}|${filterKey}`;
            }

            syncCourseSelection() {
                if (!this.courseSelectAll) {
                    return;
                }

                const rows = this.getCourseSelectableRows();
                if (!rows.length) {
                    this.courseSelectAll.checked = false;
                    this.courseSelectAll.indeterminate = false;
                    return;
                }

                const selectedVisible = rows.filter((row) => this.courseSelection.has(row.dataset.courseId)).length;
                if (selectedVisible === 0) {
                    this.courseSelectAll.checked = false;
                    this.courseSelectAll.indeterminate = false;
                } else if (selectedVisible === rows.length) {
                    this.courseSelectAll.checked = true;
                    this.courseSelectAll.indeterminate = false;
                } else {
                    this.courseSelectAll.checked = false;
                    this.courseSelectAll.indeterminate = true;
                }
            }

            syncAssignmentSelection() {
                if (!this.assignmentSelectAll) {
                    return;
                }

                const rows = this.getAssignmentSelectableRows();
                if (!rows.length) {
                    this.assignmentSelectAll.checked = false;
                    this.assignmentSelectAll.indeterminate = false;
                    return;
                }

                const selectedVisible = rows.filter((row) => this.assignmentSelection.has(row.dataset.assignmentId))
                    .length;
                if (selectedVisible === 0) {
                    this.assignmentSelectAll.checked = false;
                    this.assignmentSelectAll.indeterminate = false;
                } else if (selectedVisible === rows.length) {
                    this.assignmentSelectAll.checked = true;
                    this.assignmentSelectAll.indeterminate = false;
                } else {
                    this.assignmentSelectAll.checked = false;
                    this.assignmentSelectAll.indeterminate = true;
                }
            }

            updateCourseBulkBar() {
                if (!this.courseBulkBar || !this.courseSelectedCountEl) {
                    return;
                }

                const count = this.courseSelection.size;
                this.courseSelectedCountEl.textContent = `${count} Selected`;
                this.courseBulkBar.classList.toggle('d-none', count === 0);
            }

            updateAssignmentBulkBar() {
                if (!this.assignmentBulkBar || !this.assignmentSelectedCountEl) {
                    return;
                }

                const count = this.assignmentSelection.size;
                this.assignmentSelectedCountEl.textContent = `${count} Selected`;
                this.assignmentBulkBar.classList.toggle('d-none', count === 0);
            }

            promptBulkCourseDelete() {
                const ids = Array.from(this.courseSelection).map((id) => Number(id)).filter((id) => !Number.isNaN(id));
                if (!ids.length) {
                    return;
                }
                this.pendingCourseBulk = ids;
                const counter = document.getElementById('courseBulkDeleteCount');
                if (counter) {
                    counter.textContent = ids.length;
                }
                this.courseBulkDeleteModal?.show();
            }

            promptBulkAssignmentDelete() {
                const ids = Array.from(this.assignmentSelection).map((id) => Number(id)).filter((id) => !Number.isNaN(
                    id));
                if (!ids.length) {
                    return;
                }
                this.pendingAssignmentBulk = ids;
                const counter = document.getElementById('assignmentBulkDeleteCount');
                if (counter) {
                    counter.textContent = ids.length;
                }
                this.assignmentBulkDeleteModal?.show();
            }

            clearCourseSelection() {
                this.courseSelection.clear();
                if (this.courseTableBody) {
                    this.courseTableBody.querySelectorAll('[data-row-select]').forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                    this.courseTableBody.querySelectorAll('tr').forEach((row) => {
                        row.classList.remove('is-selected');
                    });
                }
                if (this.courseSelectAll) {
                    this.courseSelectAll.checked = false;
                    this.courseSelectAll.indeterminate = false;
                }
                this.updateCourseBulkBar();
            }

            clearAssignmentSelection() {
                this.assignmentSelection.clear();
                if (this.assignmentTableBody) {
                    this.assignmentTableBody.querySelectorAll('[data-row-select]').forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                    this.assignmentTableBody.querySelectorAll('tr').forEach((row) => {
                        row.classList.remove('is-selected');
                    });
                }
                if (this.assignmentSelectAll) {
                    this.assignmentSelectAll.checked = false;
                    this.assignmentSelectAll.indeterminate = false;
                }
                this.updateAssignmentBulkBar();
            }

            removeAssignmentsForCourseIds(courseIds = []) {
                const ids = Array.isArray(courseIds) ? courseIds : [courseIds];
                const idSet = new Set(
                    ids
                    .map((id) => Number(id))
                    .filter((id) => !Number.isNaN(id))
                );
                if (!idSet.size) {
                    return;
                }

                const removedAssignmentIds = [];
                this.assignments = this.assignments.filter((assignment) => {
                    const shouldRemove = idSet.has(Number(assignment.course_id));
                    if (shouldRemove) {
                        removedAssignmentIds.push(String(assignment.id));
                    }
                    return !shouldRemove;
                });

                if (!removedAssignmentIds.length) {
                    return;
                }

                removedAssignmentIds.forEach((assignmentId) => this.assignmentSelection.delete(assignmentId));
                this.refreshAssignmentFilterOptions();
                this.refreshSectionDropdownOptions();
                this.renderAssignmentTable();
            }

            updateAssignmentsForCourseDetails(course) {
                if (!course || course.id == null) {
                    return;
                }

                const courseId = Number(course.id);
                let hasChanges = false;

                this.assignments = this.assignments.map((assignment) => {
                    if (Number(assignment.course_id) !== courseId) {
                        return assignment;
                    }

                    hasChanges = true;
                    return {
                        ...assignment,
                        course_class_code: course.class_code ?? assignment.course_class_code ?? '',
                        course_subject_code: course.subject_code ?? assignment.course_subject_code ?? '',
                    };
                });

                if (hasChanges) {
                    this.renderAssignmentTable();
                }
            }

            openCourseEditModal(courseId) {
                const course = this.courses.find((item) => Number(item.id) === Number(courseId));
                if (!course) {
                    this.showAlert('error', 'Selected course record was not found.');
                    return;
                }

                this.currentCourseId = courseId;
                const form = document.getElementById('courseEditForm');
                if (!form) {
                    return;
                }

                form.querySelector('[name="course_id"]').value = course.id;
                form.querySelector('[name="class_code"]').value = course.class_code ?? '';
                form.querySelector('[name="subject_code"]').value = course.subject_code ?? '';

                this.courseEditModal?.show();
            }

            openCourseDeleteModal(courseId, courseName) {
                const nameDisplay = document.getElementById('courseDeleteName');
                if (nameDisplay) {
                    nameDisplay.textContent = courseName;
                }
                this.pendingCourseDeleteId = courseId;
                this.courseDeleteModal?.show();
            }

            openAssignmentEditModal(assignmentId) {
                const assignment = this.assignments.find((item) => Number(item.id) === Number(assignmentId));
                if (!assignment) {
                    this.showAlert('error', 'Selected assignment record was not found.');
                    return;
                }

                this.currentAssignmentId = assignmentId;
                const form = document.getElementById('assignmentEditForm');
                if (!form) {
                    return;
                }

                const facultyField = form.querySelector('input[name="faculty_id"]');
                const courseField = form.querySelector('input[name="course_id"]');
                const sectionField = form.querySelector('input[name="section"]');
                const yearField = form.querySelector('input[name="academic_year"]');
                const semesterField = form.querySelector('input[name="semester"]');

                form.querySelector('[name="assignment_id"]').value = assignment.id;
                if (facultyField) {
                    const facultyValue = assignment.faculty_id != null ? String(assignment.faculty_id) : '';
                    const facultyName = this.formatDisplayText(assignment.faculty_name ?? '');
                    const facultyEmail = assignment.faculty_email ?? '';
                    this.ensureDropdownOption(facultyField, {
                        value: facultyValue,
                        label: facultyName || `Faculty #${facultyValue}`,
                        description: facultyEmail,
                    });
                    this.setDropdownValue(facultyField, facultyValue, facultyName || `Faculty #${facultyValue}`);
                }
                if (courseField) {
                    const courseValue = assignment.course_id != null ? String(assignment.course_id) : '';
                    const courseCode = this.formatCourseText(assignment.course_class_code, '');
                    const courseSubject = this.formatCourseText(assignment.course_subject_code, '');
                    const courseLabel = [courseCode, courseSubject].filter(Boolean).join(' - ') ||
                        `Course #${courseValue}`;
                    this.ensureDropdownOption(courseField, {
                        value: courseValue,
                        label: courseLabel,
                        description: courseSubject,
                    });
                    this.setDropdownValue(courseField, courseValue, courseLabel);
                }
                if (sectionField) {
                    sectionField.value = assignment.section ?? '';
                }
                if (yearField) {
                    const yearValue = assignment.academic_year ?? '';
                    if (yearValue) {
                        this.ensureDropdownOption(yearField, {
                            value: yearValue,
                            label: yearValue,
                        });
                    }
                    this.setDropdownValue(yearField, yearValue, yearValue);
                }
                if (semesterField) {
                    const semesterValue = assignment.semester ?? '';
                    const semesterLabel = this.formatSemester(semesterValue);
                    if (semesterValue) {
                        this.ensureDropdownOption(semesterField, {
                            value: semesterValue,
                            label: semesterLabel,
                        });
                    }
                    this.setDropdownValue(semesterField, semesterValue, semesterLabel);
                }

                this.assignmentEditModal?.show();
            }

            openAssignmentDeleteModal(assignmentId, name) {
                const nameDisplay = document.getElementById('assignmentDeleteName');
                if (nameDisplay) {
                    nameDisplay.textContent = name;
                }
                this.pendingAssignmentDeleteId = assignmentId;
                this.assignmentDeleteModal?.show();
            }


            async handleCreateCourse(form, data) {
                if (!this.validateCourseForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Save', 'Saving...');

                try {
                    const response = await fetch('{{ route('dm.courses.store') }}', {
                        method: 'POST',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify(data),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to add course.');
                    }

                    this.courses.push(this.normaliseCourse(payload.data));
                    this.refreshCourseDropdownOptions();
                    this.renderCourseTable();
                    this.courseCreateModal?.hide();
                    form.reset();
                    this.showAlert('success', payload.message || 'Course added successfully.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to add course.');
                } finally {
                    this.toggleButtonLoading(submitBtn, false, 'Save');
                }
            }

            async handleUpdateCourse(form, data) {
                if (!this.currentCourseId) {
                    return;
                }

                if (!this.validateCourseForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Save Changes', 'Saving...');

                try {
                    const response = await fetch(`{{ route('dm.courses.update', ':id') }}`.replace(':id', this
                        .currentCourseId), {
                        method: 'PUT',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify(data),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to update course.');
                    }

                    const normalised = this.normaliseCourse(payload.data);
                    const index = this.courses.findIndex((item) => Number(item.id) === Number(this.currentCourseId));
                    if (index !== -1) {
                        this.courses[index] = normalised;
                    }
                    this.refreshCourseDropdownOptions();
                    this.updateAssignmentsForCourseDetails(normalised);
                    this.courseEditModal?.hide();
                    this.renderCourseTable();
                    this.showAlert('success', payload.message || 'Course updated successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to update course.');
                } finally {
                    this.toggleButtonLoading(submitBtn, false, 'Save Changes');
                    this.currentCourseId = null;
                }
            }

            async handleDeleteCourse(button, courseId) {
                this.toggleButtonLoading(button, true, 'Delete', 'Deleting...');

                try {
                    const response = await fetch(`{{ route('dm.courses.destroy', ':id') }}`.replace(':id', courseId), {
                        method: 'DELETE',
                        headers: this.deleteHeaders(),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete course.');
                    }

                    this.courses = this.courses.filter((course) => Number(course.id) !== Number(courseId));
                    this.courseSelection.delete(String(courseId));
                    this.refreshCourseDropdownOptions();
                    this.removeAssignmentsForCourseIds(courseId);
                    this.courseDeleteModal?.hide();
                    this.renderCourseTable();
                    this.showAlert('success', payload.message || 'Course deleted successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to delete course.');
                } finally {
                    this.toggleButtonLoading(button, false, 'Delete');
                    this.pendingCourseDeleteId = null;
                }
            }

            async executeBulkCourseDelete(button, ids) {
                this.toggleButtonLoading(button, true, 'Delete Selected', 'Deleting...');

                try {
                    const response = await fetch('{{ route('dm.courses.bulk-destroy') }}', {
                        method: 'POST',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify({
                            ids
                        }),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete selected courses.');
                    }

                    const idSet = new Set(ids.map((id) => Number(id)));
                    this.courses = this.courses.filter((course) => !idSet.has(Number(course.id)));
                    ids.forEach((id) => this.courseSelection.delete(String(id)));
                    this.refreshCourseDropdownOptions();
                    this.removeAssignmentsForCourseIds(ids);
                    this.courseBulkDeleteModal?.hide();
                    this.renderCourseTable();
                    this.showAlert('success', payload.message || 'Selected courses deleted successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to delete selected courses.');
                } finally {
                    this.toggleButtonLoading(button, false, 'Delete Selected');
                    this.pendingCourseBulk = null;
                }
            }

            async handleCreateAssignment(form, data) {
                if (!this.validateAssignmentForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Assign', 'Assigning...');

                try {
                    const response = await fetch('{{ route('dm.faculty-courses.store') }}', {
                        method: 'POST',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify(data),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to assign course.');
                    }

                    this.assignments.push(this.normaliseAssignment(payload.data));
                    this.refreshAssignmentFilterOptions();
                    this.refreshSectionDropdownOptions();
                    this.assignmentCreateModal?.hide();
                    form.reset();
                    this.renderAssignmentTable();
                    this.showAlert('success', payload.message || 'Course assigned successfully.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to assign course.');
                } finally {
                    this.toggleButtonLoading(submitBtn, false, 'Assign');
                }
            }

            async handleUpdateAssignment(form, data) {
                if (!this.currentAssignmentId) {
                    return;
                }

                if (!this.validateAssignmentForm(data)) {
                    return;
                }

                const submitBtn = form.querySelector('[type="submit"]');
                this.toggleButtonLoading(submitBtn, true, 'Save Changes', 'Saving...');

                try {
                    const response = await fetch(`{{ route('dm.faculty-courses.update', ':id') }}`.replace(':id', this
                        .currentAssignmentId), {
                        method: 'PUT',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify(data),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to update assignment.');
                    }

                    const normalised = this.normaliseAssignment(payload.data);
                    const index = this.assignments.findIndex((item) => Number(item.id) === Number(this
                        .currentAssignmentId));
                    if (index !== -1) {
                        this.assignments[index] = normalised;
                    }
                    this.refreshAssignmentFilterOptions();
                    this.refreshSectionDropdownOptions();
                    this.assignmentEditModal?.hide();
                    this.renderAssignmentTable();
                    this.showAlert('success', payload.message || 'Assignment updated successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to update assignment.');
                } finally {
                    this.toggleButtonLoading(submitBtn, false, 'Save Changes');
                    this.currentAssignmentId = null;
                }
            }

            async handleDeleteAssignment(button, assignmentId) {
                this.toggleButtonLoading(button, true, 'Delete', 'Deleting...');

                try {
                    const response = await fetch(`{{ route('dm.faculty-courses.destroy', ':id') }}`.replace(':id',
                        assignmentId), {
                        method: 'DELETE',
                        headers: this.deleteHeaders(),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete assignment.');
                    }

                    this.assignments = this.assignments.filter((assignment) => Number(assignment.id) !== Number(
                        assignmentId));
                    this.assignmentSelection.delete(String(assignmentId));
                    this.refreshAssignmentFilterOptions();
                    this.refreshSectionDropdownOptions();
                    this.assignmentDeleteModal?.hide();
                    this.renderAssignmentTable();
                    this.showAlert('success', payload.message || 'Assignment deleted successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to delete assignment.');
                } finally {
                    this.toggleButtonLoading(button, false, 'Delete');
                    this.pendingAssignmentDeleteId = null;
                }
            }

            async executeBulkAssignmentDelete(button, ids) {
                this.toggleButtonLoading(button, true, 'Delete Selected', 'Deleting...');

                try {
                    const response = await fetch('{{ route('dm.faculty-courses.bulk-destroy') }}', {
                        method: 'POST',
                        headers: this.defaultHeaders(),
                        body: JSON.stringify({
                            ids
                        }),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Failed to delete selected assignments.');
                    }

                    const idSet = new Set(ids.map((id) => Number(id)));
                    this.assignments = this.assignments.filter((assignment) => !idSet.has(Number(assignment.id)));
                    ids.forEach((id) => this.assignmentSelection.delete(String(id)));
                    this.refreshAssignmentFilterOptions();
                    this.refreshSectionDropdownOptions();
                    this.assignmentBulkDeleteModal?.hide();
                    this.renderAssignmentTable();
                    this.showAlert('success', payload.message || 'Selected assignments deleted successfully.');
                } catch (error) {
                    this.showAlert('error', error.message || 'Failed to delete selected assignments.');
                } finally {
                    this.toggleButtonLoading(button, false, 'Delete Selected');
                    this.pendingAssignmentBulk = null;
                }
            }

            validateCourseForm(data) {
                const required = ['class_code', 'subject_code'];
                const missing = required.filter((key) => !data[key] || String(data[key]).trim() === '');
                if (missing.length) {
                    this.showAlert('error', 'Please complete all required course fields.');
                    return false;
                }
                return true;
            }

            validateAssignmentForm(data) {
                const required = ['faculty_id', 'course_id', 'section', 'academic_year', 'semester'];
                const missing = required.filter((key) => !data[key] || String(data[key]).trim() === '');
                if (missing.length) {
                    this.showAlert('error', 'Please complete all required assignment fields.');
                    return false;
                }
                return true;
            }

            toggleButtonLoading(button, isLoading, defaultText, loadingText = 'Saving...') {
                if (!button) {
                    return;
                }

                const idleText = defaultText ?? button.dataset.defaultText ?? button.textContent.trim();
                const busyText = loadingText ?? button.dataset.loadingText ?? 'Saving...';

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

            showAlert(type, message) {
                if (window.dmToast && typeof window.dmToast.show === 'function') {
                    const toastType = type === 'error' ? 'danger' : type;
                    window.dmToast.show({
                        type: toastType,
                        message
                    });
                    return;
                }
                if (!this.alertContainer) {
                    return;
                }
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                this.alertContainer.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${this.escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            normaliseCourse(payload) {
                if (!payload) {
                    return {};
                }
                return {
                    id: payload.id,
                    class_code: payload.class_code,
                    subject_code: payload.subject_code,
                    assignments: payload.faculty_courses_count ?? payload.assignments ?? 0,
                };
            }

            normaliseAssignment(payload) {
                if (!payload) {
                    return {};
                }
                const faculty = payload.faculty || {};
                const facultyUser = faculty.user || {};
                const course = payload.course || {};

                return {
                    id: payload.id,
                    faculty_id: payload.faculty_id,
                    faculty_name: facultyUser.name ?? faculty.name ?? 'N/A',
                    faculty_email: facultyUser.email ?? '',
                    course_id: payload.course_id,
                    course_class_code: course.class_code ?? 'N/A',
                    course_subject_code: course.subject_code ?? '',
                    section: payload.section ?? '',
                    academic_year: payload.academic_year,
                    semester: payload.semester,
                };
            }

            formToObject(form) {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                return data;
            }

            defaultHeaders() {
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                };
            }

            deleteHeaders() {
                return {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                };
            }

            escapeHtml(value) {
                if (value === null || value === undefined) {
                    return '';
                }
                return String(value).replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                } [char] || char));
            }

            escapeAttribute(value) {
                return this.escapeHtml(value).replace(/"/g, '&quot;');
            }

            formatDisplayText(value) {
                if (value === null || value === undefined) {
                    return '';
                }
                return String(value);
            }

            formatSemester(value) {
                if (!value) {
                    return 'N/A';
                }
                const normalised = String(value).toLowerCase();
                if (normalised === '1st' || normalised === 'first') {
                    return '1st Semester';
                }
                if (normalised === '2nd' || normalised === 'second') {
                    return '2nd Semester';
                }
                if (normalised === 'summer') {
                    return 'Summer';
                }
                return value;
            }

            formatCourseText(value, fallback = '') {
                if (value === null || value === undefined) {
                    return fallback;
                }
                const trimmed = String(value).trim();
                return trimmed === '' ? fallback : trimmed;
            }

            initAssignmentFilters() {
                this.refreshAssignmentFilterOptions();

                Object.entries(this.assignmentFilterControls).forEach(([key, select]) => {
                    if (!select) return;
                    select.addEventListener('change', (event) => {
                        this.assignmentFilters[key] = event.target.value || 'all';
                        this.renderAssignmentTable();
                    });
                });

                if (this.assignmentFilterReset) {
                    this.assignmentFilterReset.addEventListener('click', () => {
                        Object.keys(this.assignmentFilters).forEach((key) => {
                            this.assignmentFilters[key] = 'all';
                            if (this.assignmentFilterControls[key]) {
                                this.assignmentFilterControls[key].value = 'all';
                            }
                        });
                        this.renderAssignmentTable();
                    });
                }
            }

            refreshAssignmentFilterOptions() {
                const facultyOptions = this.getAssignmentFilterOptions(
                    (assignment) => assignment?.faculty_name ?? '',
                    (value) => this.formatDisplayText(value)
                );
                const yearOptions = this.getAssignmentFilterOptions(
                    (assignment) => assignment?.academic_year ?? '',
                    (value) => value
                );
                const semesterOptions = this.getAssignmentFilterOptions(
                    (assignment) => assignment?.semester ?? '',
                    (value) => this.formatSemester(value)
                );

                this.populateFilterSelect(this.assignmentFilterControls.faculty, facultyOptions);
                this.populateFilterSelect(this.assignmentFilterControls.year, yearOptions);
                this.populateFilterSelect(this.assignmentFilterControls.semester, semesterOptions);
                this.updateAssignmentFilterToggleState();
            }

            refreshSectionDropdownOptions() {
                const options = new Set();
                (this.assignments || []).forEach((assignment) => {
                    const raw = assignment?.section ?? '';
                    String(raw)
                        .split(',')
                        .map((entry) => entry.trim())
                        .filter((entry) => entry !== '')
                        .forEach((entry) => options.add(entry));
                });

                const sorted = Array.from(options).sort((a, b) => a.localeCompare(b));
                const dropdowns = document.querySelectorAll('[data-section-dropdown]');

                dropdowns.forEach((dropdown) => {
                    const list = dropdown.querySelector('[data-user-list]');
                    if (!list) {
                        return;
                    }

                    list.innerHTML = '';
                    sorted.forEach((section) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'dropdown-item';
                        button.dataset.userOption = '';
                        button.dataset.userName = section;
                        button.dataset.userNameLower = section.toLowerCase();
                        button.dataset.userEmailLower = '';

                        const span = document.createElement('span');
                        span.textContent = section;
                        button.appendChild(span);
                        list.appendChild(button);
                    });

                    dropdown.__userDropdown?.applyFilter?.();
                });
            }

            getAssignmentFilterOptions(accessor, formatter) {
                const seen = new Map();
                (this.assignments || []).forEach((assignment) => {
                    const raw = accessor(assignment) ?? '';
                    const value = this.normaliseValue(raw);
                    if (!value || seen.has(value)) {
                        return;
                    }
                    const label = formatter ? formatter(raw, assignment) : raw;
                    seen.set(value, label);
                });
                return Array.from(seen.entries())
                    .sort((a, b) => a[1].localeCompare(b[1]))
                    .map(([value, label]) => ({
                        value,
                        label
                    }));
            }

            populateFilterSelect(select, options) {
                if (!select) return;
                const previous = select.value;
                const entries = ['<option value="all">All</option>']
                    .concat(options.map((option) => `<option value="${option.value}">${option.label}</option>`));
                select.innerHTML = entries.join('');
                select.value = options.some((option) => option.value === previous) ? previous : 'all';
            }

            matchesAssignmentFilters(assignment) {
                if (!assignment) {
                    return false;
                }
                if (this.assignmentFilters.faculty !== 'all') {
                    const value = this.normaliseValue(assignment.faculty_name ?? '');
                    if (value !== this.assignmentFilters.faculty) {
                        return false;
                    }
                }
                if (this.assignmentFilters.year !== 'all') {
                    const value = this.normaliseValue(assignment.academic_year ?? '');
                    if (value !== this.assignmentFilters.year) {
                        return false;
                    }
                }
                if (this.assignmentFilters.semester !== 'all') {
                    const value = this.normaliseValue(assignment.semester ?? '');
                    if (value !== this.assignmentFilters.semester) {
                        return false;
                    }
                }
                return true;
            }

            updateAssignmentFilterToggleState() {
                if (!this.assignmentFilterToggle) return;
                const isActive = Object.values(this.assignmentFilters).some((value) => value !== 'all');
                this.assignmentFilterToggle.classList.toggle('is-active', isActive);
            }

            normaliseValue(value) {
                return String(value ?? '')
                    .trim()
                    .toLowerCase();
            }
        }
    </script>
@endsection
