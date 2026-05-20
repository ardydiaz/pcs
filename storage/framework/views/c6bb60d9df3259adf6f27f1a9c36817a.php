<?php
    $scheduleItems = $schedules
        ->sortByDesc(function ($schedule) {
            return $schedule->created_at ?? ($schedule->id ?? 0);
        })
        ->map(function ($schedule) {
            $course = optional($schedule->facultyCourse)->course;
            $faculty = optional($schedule->facultyCourse)->faculty;

            return [
                'id' => $schedule->id,
                'faculty_course_id' => $schedule->faculty_course_id,
                'course_code' => optional($course)->class_code,
                'course_subject' => $course->subject_code ?? '',
                'section' => optional($schedule->facultyCourse)->section ?? '',
                'faculty_name' => optional(optional($faculty)->user)->name ?? ($faculty->name ?? ''),
                'time' => $schedule->time,
                'day' => $schedule->day,
            ];
        })
        ->values();

    $facultyCourseOptions = $facultyCourses
        ->map(function ($facultyCourse) {
            $course = optional($facultyCourse->course);
            $faculty = optional($facultyCourse->faculty);
            $courseCodeRaw = $course->class_code ?? '';
            $courseSubjectRaw = $course->subject_code ?? '';
            $facultyRawName = optional(optional($faculty)->user)->name ?? ($faculty->name ?? '');
            $facultyName = trim($facultyRawName) === '' ? '' : $facultyRawName;
            $academicYear = trim($facultyCourse->academic_year ?? '');
            $semesterRaw = trim($facultyCourse->semester ?? '');
            $sectionRaw = trim($facultyCourse->section ?? '');
            $formattedAcademicYear = $academicYear;
            $semesterDisplay = $semesterRaw;
            $sectionDisplay = $sectionRaw;
            $courseCode = trim($courseCodeRaw ?? '');
            $courseSubject = trim($courseSubjectRaw ?? '');
            $label = $course
                ? trim($courseCode . ($courseSubject ? ' - ' . $courseSubject : ''))
                : 'Course #' . $facultyCourse->id;
            $termParts = array_filter([$formattedAcademicYear, $semesterDisplay], function ($value) {
                return trim($value ?? '') !== '';
            });
            $termLabel = implode(' • ', $termParts);
            $searchTokens = array_filter(
                [
                    $label,
                    $facultyRawName,
                    $academicYear,
                    $formattedAcademicYear,
                    $semesterRaw,
                    $semesterDisplay,
                    $sectionRaw,
                ],
                function ($value) {
                    return trim($value ?? '') !== '';
                },
            );
            $searchValue = strtolower($searchTokens ? implode(' ', $searchTokens) : $label);

            return [
                'id' => $facultyCourse->id,
                'label' => $label,
                'code' => $courseCode !== '' ? $courseCode : 'N/A',
                'subject' => $courseSubject,
                'section' => $sectionDisplay,
                'faculty' => $facultyName,
                'academic_year' => $academicYear,
                'academic_year_display' => $formattedAcademicYear,
                'semester' => $semesterDisplay,
                'term' => $termLabel,
                'search' => $searchValue,
            ];
        })
        ->values();

    $dayOptions = [
        ['value' => 'M', 'label' => 'Monday'],
        ['value' => 'T', 'label' => 'Tuesday'],
        ['value' => 'W', 'label' => 'Wednesday'],
        ['value' => 'TH', 'label' => 'Thursday'],
        ['value' => 'F', 'label' => 'Friday'],
        ['value' => 'S', 'label' => 'Saturday'],
        ['value' => 'SU', 'label' => 'Sunday'],
    ];

    $generateTimeOptions = function ($intervalMinutes = 30) {
        $times = [];
        $totalMinutes = 24 * 60;
        for ($minutes = 0; $minutes < $totalMinutes; $minutes += $intervalMinutes) {
            $hour24 = intdiv($minutes, 60);
            $minute = $minutes % 60;
            $hour12 = $hour24 % 12;
            if ($hour12 === 0) {
                $hour12 = 12;
            }
            $period = $hour24 < 12 ? 'a' : 'p';
            $times[] = sprintf('%02d:%02d%s', $hour12, $minute, $period);
        }
        return $times;
    };

    $timeOptions = collect($generateTimeOptions());

    $container = 'container-xxl';
    $accessLevels = collect(auth()->user()?->access_level ?? []);
    $isAdmin = auth()->user()?->role === 'Admin';
    $canManageSchedules = $isAdmin || $accessLevels->contains('Manage Schedules');
    $canAdd = $canManageSchedules;
    $canEdit = $canManageSchedules;
    $canDelete = $isAdmin;
    $showDeleteDisabled = !$isAdmin && $canManageSchedules;
    $canImport = $canManageSchedules;
?>

<?php $__env->startSection('title', 'Data Management - Schedules'); ?> <!-- Set the page title for the schedules management page -->

<!-- Page-specific styles component calling from partials/schedules/page-style -->
<?php echo $__env->make('content.data-management.partials.schedules.page-style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the page-specific styles for the schedules management page -->

<?php $__env->startSection('content'); ?>
    <?php
        $flashSuccess = session('success');
        $flashError = session('error');
        $pageToasts = [];
        if ($flashSuccess) {
            $pageToasts[] = ['type' => 'success', 'message' => $flashSuccess];
        }
        if ($flashError) {
            $pageToasts[] = ['type' => 'danger', 'message' => $flashError];
        }
    ?>
    <?php echo $__env->make('components.dm-toast', ['messages' => $pageToasts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container-fluid">

        <div id="scheduleAlertContainer"></div>

        <!-- Header with title and create button component -->
        <?php echo $__env->make('content.data-management.partials.schedules.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the header with the title and the create button -->

        <!-- Schedule Table Component render from partials/schedules/schedule-table-items -->
        <?php echo $__env->make('content.data-management.partials.schedules.schedule-table-items', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the schedule table and the bulk action bar -->
    </div>

    <!-- Create Schedule Modal Component render from partials/schedules/create-schedule-form-modal -->
    <?php echo $__env->make('content.data-management.partials.schedules.create-schedule-form-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the create schedule modal -->

    <!-- Edit Schedule Modal Component render from partials/schedules/edit-schedule-form-modal -->
    <?php echo $__env->make('content.data-management.partials.schedules.edit-schedule-form-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the edit schedule modal -->

    <!-- Delete Schedule and Bulk Delete Modal Component render from partials/schedules/delete-and-bulk-alert -->
    <?php echo $__env->make('content.data-management.partials.schedules.delete-and-bulk-alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the delete confirmation modal and the bulk delete confirmation modal -->

    <!-- Excel Import Modal Component render from partials/schedules/excel-import -->
    <?php echo $__env->make('content.data-management.partials.schedules.excel-import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- This includes the excel import modal -->

<?php $__env->stopSection(); ?>

<! -- Page-specific scripts --!>
    <?php $__env->startSection('page-script'); ?>
        <?php echo $__env->make('components.table-controller-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
            const PILL_COLOR_CACHE = new Map();

            function getPillColor(value, paletteName) {
                const palette = PILL_PALETTES[paletteName];
                if (!palette || !palette.length) {
                    return null;
                }
                const key = `${paletteName}::${String(value ?? '').toLowerCase()}`;
                if (PILL_COLOR_CACHE.has(key)) {
                    return PILL_COLOR_CACHE.get(key);
                }
                const valueString = String(value ?? '').toLowerCase();
                const hash = valueString ?
                    Array.from(valueString).reduce((acc, char) => acc + char.charCodeAt(0), 0) :
                    0;
                const color = palette[hash % palette.length];
                PILL_COLOR_CACHE.set(key, color);
                return color;
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

            function applyPillPalettes(root = document) {
                const scope = root instanceof Element ? root : document.body;
                scope.querySelectorAll('[data-pill-palette]').forEach(stylePillElement);
            }

            const schedulePermissions = {
                canAdd: <?php echo json_encode($canAdd, 15, 512) ?>,
                canEdit: <?php echo json_encode($canEdit, 15, 512) ?>,
                canDelete: <?php echo json_encode($canDelete, 15, 512) ?>,
                showDeleteDisabled: <?php echo json_encode($showDeleteDisabled, 15, 512) ?>,
                canImport: <?php echo json_encode($canImport, 15, 512) ?>,
            };

            document.addEventListener('DOMContentLoaded', () => {
                initScheduleCourseDropdowns();
                initScheduleDayMultiselects();
                initScheduleTimeDropdowns();
                window.schedulePage = new SchedulePage({
                    schedules: <?php echo json_encode($scheduleItems, 15, 512) ?>,
                    facultyCourses: <?php echo json_encode($facultyCourseOptions, 15, 512) ?>,
                    tableRoot: document.querySelector('[data-table-id="schedulesTable"]'),
                });
            });

            function initScheduleCourseDropdowns() {
                const dropdowns = document.querySelectorAll('[data-course-dropdown]');
                dropdowns.forEach((dropdown) => {
                    if (dropdown.courseDropdownApi) {
                        return;
                    }
                    setupScheduleCourseDropdown(dropdown);
                });
            }

            // Helper function to render course information with multiple faculty support
            const renderCourseInfoDisplay = (faculty, section, academicYear, semester) => {
                if (!faculty && !section && !academicYear && !semester) {
                    return '<span class="text-muted text-center">No faculty information available</span>';
                }

                let html = '<div class="row g-2">';

                // Render faculty as badges with color code #6610f2
                if (faculty) {
                    const facultyList = faculty.split(/[,;]/).map(f => f.trim()).filter(f => f);
                    if (facultyList.length > 0) {
                        html += '<div class="col-12">';
                        html += '<strong>Faculty:</strong><div class="mt-2 d-flex flex-wrap gap-2">';
                        facultyList.forEach(f => {
                            html += `<span class="badge" style="background-color: #4c1d95;">${f}</span>`;
                        });
                        html += '</div></div>';
                    }
                }

                if (section) {
                    html += `<div class="col-12"><strong>Section:</strong> <span>${section}</span></div>`;
                }
                if (academicYear) {
                    html += `<div class="col-12"><strong>Academic Year:</strong> <span>${academicYear}</span></div>`;
                }
                if (semester) {
                    html += `<div class="col-12"><strong>Semester:</strong> <span>${semester}</span></div>`;
                }
                html += '</div>';
                return html;
            };

            function setupScheduleCourseDropdown(dropdown) {
                const label = dropdown.querySelector('[data-course-dropdown-label]');
                const hiddenInput = dropdown.querySelector('input[name="faculty_course_id"]');
                const searchInput = dropdown.querySelector('[data-course-search]');
                const optionButtons = Array.from(dropdown.querySelectorAll('[data-course-option]'));
                const listWrapper = dropdown.querySelector('[data-course-list]');
                const placeholderText = label?.dataset.placeholderText?.trim() || '-- Select Course --';

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

                const applySearchFilter = () => {
                    const term = searchInput ? searchInput.value.trim().toLowerCase() : '';
                    const hasTerm = term !== '';
                    optionButtons.forEach((option) => {
                        const searchValue = option.dataset.courseSearch || '';
                        const matches = !hasTerm || searchValue.includes(term);
                        option.classList.toggle('d-none', !matches);
                    });
                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                };

                const setValueById = (courseId, fallbackLabel = '') => {
                    const targetId = courseId ? String(courseId) : '';
                    if (!hiddenInput) {
                        return;
                    }

                    if (!targetId) {
                        resetSelection();
                        return;
                    }

                    const match = optionButtons.find((option) => option.dataset.courseId === targetId);
                    if (match) {
                        hiddenInput.value = match.dataset.courseId;
                        setLabel(match.dataset.courseLabel || match.textContent.trim(), false);
                        return;
                    }

                    hiddenInput.value = targetId;
                    const fallback = fallbackLabel || `Course #${targetId}`;
                    setLabel(fallback, false);
                };

                if (!hiddenInput || !hiddenInput.value) {
                    setLabel(placeholderText, true);
                } else {
                    setValueById(hiddenInput.value);
                }

                if (searchInput) {
                    searchInput.addEventListener('input', applySearchFilter);
                }

                optionButtons.forEach((option) => {
                    option.addEventListener('click', () => {
                        setValueById(option.dataset.courseId);

                        // Update faculty display for both Edit and Create modals
                        const form = dropdown.closest('form');
                        const modalId = form?.id === 'createScheduleForm' ? 'createFacultyDisplay' :
                            'editFacultyDisplay';
                        const facultyDisplay = form?.querySelector(`#${modalId}`);

                        if (facultyDisplay && option.dataset.faculty) {
                            const faculty = option.dataset.faculty || '';
                            const section = option.dataset.section || '';
                            const academicYear = option.dataset.academicYear || '';
                            const semester = option.dataset.semester || '';

                            // Auto-update the display with selected course information
                            facultyDisplay.innerHTML = renderCourseInfoDisplay(faculty, section, academicYear,
                                semester);
                            facultyDisplay.style.minHeight = 'auto';
                        }
                    });

                    // Also trigger update when option is hovered for preview
                    option.addEventListener('mouseenter', () => {
                        const form = dropdown.closest('form');
                        const modalId = form?.id === 'createScheduleForm' ? 'createFacultyDisplay' :
                            'editFacultyDisplay';
                        const facultyDisplay = form?.querySelector(`#${modalId}`);

                        if (facultyDisplay) {
                            const faculty = option.dataset.faculty || '';
                            const section = option.dataset.section || '';
                            const academicYear = option.dataset.academicYear || '';
                            const semester = option.dataset.semester || '';

                            facultyDisplay.innerHTML = renderCourseInfoDisplay(faculty, section, academicYear,
                                semester);
                            facultyDisplay.style.opacity = '0.7';
                        }
                    });

                    // Reset opacity when mouse leaves
                    option.addEventListener('mouseleave', () => {
                        const form = dropdown.closest('form');
                        const modalId = form?.id === 'createScheduleForm' ? 'createFacultyDisplay' :
                            'editFacultyDisplay';
                        const facultyDisplay = form?.querySelector(`#${modalId}`);

                        if (facultyDisplay && hiddenInput?.value) {
                            facultyDisplay.style.opacity = '1';
                        }
                    });
                });

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
                        resetSelection();
                        if (searchInput) {
                            searchInput.value = '';
                        }
                        applySearchFilter();
                    });
                }

                applySearchFilter();

                dropdown.courseDropdownApi = {
                    reset: resetSelection,
                    setValueById,
                };
            }

            function initScheduleDayMultiselects() {
                const multiselects = document.querySelectorAll('[data-day-multiselect]');
                multiselects.forEach((multiselect) => {
                    if (multiselect.dayMultiselectApi) {
                        return;
                    }
                    setupScheduleDayMultiselect(multiselect);
                });
            }

            function setupScheduleDayMultiselect(multiselect) {
                const placeholder = multiselect.querySelector('[data-day-placeholder]');
                const chipsContainer = multiselect.querySelector('[data-day-selected]');
                const inputsContainer = multiselect.querySelector('[data-day-inputs]');
                const searchInput = multiselect.querySelector('[data-day-search]');
                const optionElements = Array.from(multiselect.querySelectorAll('[data-day-option]'));

                const options = optionElements.map((element) => {
                    const value = element.dataset.value ?? '';
                    const label = element.dataset.label ?? value;
                    const searchValue = element.dataset.search ?? label.toLowerCase();
                    const checkbox = element.querySelector('[data-day-checkbox]');
                    return {
                        element,
                        value,
                        label,
                        searchValue,
                        checkbox
                    };
                });

                const selected = new Set();

                const getLabel = (value) => {
                    const code = (value || '').toUpperCase();
                    const mapping = {
                        M: 'Mon',
                        T: 'Tue',
                        W: 'Wed',
                        TH: 'Thu',
                        F: 'Fri',
                        S: 'Sat',
                        SU: 'Sun',
                    };
                    if (mapping[code]) {
                        return mapping[code];
                    }
                    return options.find((option) => option.value === value)?.label || value;
                };

                const syncHiddenInputs = () => {
                    if (!inputsContainer) {
                        return;
                    }
                    inputsContainer.innerHTML = '';
                    selected.forEach((value) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'day[]';
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
                        const chip = document.createElement('span');
                        chip.className = 'day-chip';
                        chip.innerHTML = `
                        <span>${getLabel(value)}</span>
                        <button type="button" class="day-chip-remove" data-day-remove="${value}" aria-label="Remove ${getLabel(value)}">&times;</button>
                    `;
                        chipsContainer.appendChild(chip);
                    });

                    if (placeholder) {
                        placeholder.classList.toggle('d-none', selected.size > 0);
                    }
                };

                const syncCheckboxes = () => {
                    options.forEach(({
                        value,
                        checkbox,
                        element
                    }) => {
                        const isSelected = selected.has(value);
                        if (checkbox) {
                            checkbox.checked = isSelected;
                        }
                        element.classList.toggle('is-selected', isSelected);
                    });
                };

                const setSelectedValues = (values) => {
                    selected.clear();
                    (Array.isArray(values) ? values : []).forEach((value) => {
                        if (value) {
                            selected.add(String(value));
                        }
                    });
                    syncHiddenInputs();
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
                        selected.add(key);
                    }
                    syncHiddenInputs();
                    syncChips();
                    syncCheckboxes();
                };

                const applySearchFilter = () => {
                    const term = (searchInput?.value || '').trim().toLowerCase();
                    const hasTerm = term !== '';
                    options.forEach(({
                        element,
                        searchValue
                    }) => {
                        const matches = !hasTerm || (searchValue || '').includes(term);
                        element.classList.toggle('d-none', !matches);
                    });
                };

                options.forEach(({
                    element,
                    value,
                    checkbox
                }) => {
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
                            syncHiddenInputs();
                            syncChips();
                            syncCheckboxes();
                        });
                    }
                });

                const handleChipRemoval = (event) => {
                    const removeBtn = event.target.closest('[data-day-remove]');
                    if (!removeBtn) {
                        return;
                    }
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    const value = removeBtn.dataset.dayRemove;
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
                    if (searchInput) {
                        searchInput.value = '';
                        applySearchFilter();
                        searchInput.focus();
                    }
                });

                applySearchFilter();
                syncChips();
                syncCheckboxes();

                multiselect.dayMultiselectApi = {
                    setSelected: setSelectedValues,
                    getSelected: () => Array.from(selected),
                    reset: () => setSelectedValues([]),
                };
            }

            function initScheduleTimeDropdowns(context = document) {
                const scope = context || document;
                const dropdowns = scope.querySelectorAll('[data-time-dropdown]');
                dropdowns.forEach((dropdown) => {
                    if (dropdown.timeDropdownApi) {
                        return;
                    }
                    setupScheduleTimeDropdown(dropdown);
                });
            }

            function setupScheduleTimeDropdown(dropdown) {
                const label = dropdown.querySelector('[data-time-label]');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const searchInput = dropdown.querySelector('[data-time-search]');
                const listWrapper = dropdown.querySelector('[data-time-list]');
                const placeholderText = label?.dataset.placeholderText?.trim() || '-- Select Time --';
                const optionButtons = [];

                const registerOption = (option) => {
                    optionButtons.push(option);
                    option.addEventListener('click', () => {
                        setValue(option.dataset.optionValue || '');
                    });
                };

                dropdown.querySelectorAll('[data-time-option]').forEach(registerOption);

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
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    setLabel(placeholderText, true);
                };

                const ensureOptionButton = (value) => {
                    if (!value || !listWrapper) {
                        return null;
                    }
                    const existing = optionButtons.find((option) => option.dataset.optionValue === value);
                    if (existing) {
                        return existing;
                    }
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'dropdown-item';
                    button.setAttribute('data-time-option', '');
                    button.dataset.optionValue = value;
                    button.dataset.optionFilter = value.toLowerCase();
                    const span = document.createElement('span');
                    span.textContent = value;
                    button.appendChild(span);
                    listWrapper.appendChild(button);
                    registerOption(button);
                    return button;
                };

                const setValue = (value) => {
                    const target = (value || '').trim();
                    if (!target) {
                        resetSelection();
                        return;
                    }
                    ensureOptionButton(target);
                    if (hiddenInput) {
                        hiddenInput.value = target;
                        hiddenInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }
                    const match = optionButtons.find((option) => option.dataset.optionValue === target);
                    const displayLabel = match?.dataset.optionLabel || target;
                    setLabel(displayLabel, false);
                };

                const applySearchFilter = () => {
                    const term = (searchInput?.value || '').trim().toLowerCase();
                    const hasTerm = term !== '';
                    optionButtons.forEach((option) => {
                        const filter = option.dataset.optionFilter || option.textContent || '';
                        const matches = !hasTerm || filter.toLowerCase().includes(term);
                        option.classList.toggle('d-none', !matches);
                    });
                    if (listWrapper) {
                        listWrapper.classList.toggle('is-unlimited', hasTerm);
                    }
                };

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

                if (searchInput) {
                    searchInput.addEventListener('input', applySearchFilter);
                }

                if (hiddenInput && hiddenInput.value) {
                    setValue(hiddenInput.value);
                } else {
                    resetSelection();
                }

                applySearchFilter();

                dropdown.timeDropdownApi = {
                    reset: resetSelection,
                    setValue,
                };
            }

            class SchedulePage {
                constructor({
                    schedules,
                    facultyCourses,
                    tableRoot
                }) {
                    this.schedules = Array.isArray(schedules) ? schedules : [];
                    this.facultyCourses = Array.isArray(facultyCourses) ? facultyCourses : [];
                    this.tableRoot = tableRoot || null;
                    this.tableBody = document.getElementById('schedulesTableBody');
                    this.selectAllEl = document.getElementById('scheduleSelectAll');
                    this.bulkBar = document.getElementById('scheduleBulkBar');
                    this.selectedCountEl = document.getElementById('scheduleSelectedCount');
                    this.filters = {
                        time: 'all',
                        day: 'all'
                    };
                    this.lastSelectionScopeKey = this.getSelectionScopeKey();
                    this.filterControls = {
                        time: document.getElementById('scheduleFilterTime'),
                        day: document.getElementById('scheduleFilterDay'),
                    };
                    this.filterResetBtn = document.getElementById('scheduleFilterReset');
                    this.filterToggle = document.getElementById('scheduleFilterToggle');

                    this.createModalEl = document.getElementById('scheduleCreateModal');
                    this.editModalEl = document.getElementById('scheduleEditModal');
                    this.deleteModalEl = document.getElementById('scheduleDeleteModal');
                    this.bulkDeleteModalEl = document.getElementById('scheduleBulkDeleteModal');

                    const hasBootstrap = typeof bootstrap !== 'undefined' && bootstrap?.Modal;
                    this.createModal = this.createModalEl && hasBootstrap ? new bootstrap.Modal(this.createModalEl) : null;
                    this.editModal = this.editModalEl && hasBootstrap ? new bootstrap.Modal(this.editModalEl) : null;
                    this.deleteModal = this.deleteModalEl && hasBootstrap ? new bootstrap.Modal(this.deleteModalEl) : null;
                    this.bulkDeleteModal = this.bulkDeleteModalEl && hasBootstrap ? new bootstrap.Modal(this
                        .bulkDeleteModalEl) : null;

                    this.alertContainer = document.getElementById('scheduleAlertContainer');

                    this.selection = new Set();
                    this.sortState = {
                        key: null,
                        direction: 'asc'
                    };
                    this.pendingDeleteId = null;
                    this.pendingBulkIds = null;
                    this.currentEditId = null;

                    this.controller = null;

                    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                    this.init();
                }

                init() {
                    this.bindForms();
                    this.bindSearchInputs();
                    this.bindBulkBar();
                    this.initSorting();
                    this.initFilters();
                    this.renderTable();
                }

                bindForms() {
                    const createForm = document.getElementById('createScheduleForm');
                    if (createForm) {
                        createForm.addEventListener('submit', (event) => {
                            event.preventDefault();
                            const data = this.collectScheduleFormData(createForm);
                            if (!data) return;
                            this.handleCreateSchedule(createForm, data);
                        });
                        this.createModalEl?.addEventListener('hidden.bs.modal', () => {
                            createForm.reset();
                        });
                        createForm.addEventListener('reset', () => {
                            this.resetCourseDropdowns(createForm);
                            this.resetDayMultiselects(createForm);
                            this.setTimeFields(createForm, '');
                            // Reset faculty display
                            const facultyDisplay = createForm.querySelector('#createFacultyDisplay');
                            if (facultyDisplay) {
                                facultyDisplay.innerHTML =
                                    '<span class="text-muted text-center">Select a course to view faculty information</span>';
                            }
                        });
                    }

                    const editForm = document.getElementById('editScheduleForm');
                    if (editForm) {
                        editForm.addEventListener('submit', (event) => {
                            event.preventDefault();
                            const data = this.collectScheduleFormData(editForm);
                            if (!data || !this.currentEditId) return;
                            this.handleUpdateSchedule(editForm, this.currentEditId, data);
                        });
                        editForm.addEventListener('reset', () => {
                            this.resetCourseDropdowns(editForm);
                            this.resetDayMultiselects(editForm);
                            this.setTimeFields(editForm, '');
                        });
                    }

                    const importForm = document.getElementById('scheduleImportForm');
                    if (importForm) {
                        importForm.addEventListener('submit', () => {
                            const submitBtn = importForm.querySelector('[type="submit"]');
                            this.toggleButtonLoading(submitBtn, true, 'Import', 'Importing...');
                        });
                    }

                    const confirmDeleteBtn = document.getElementById('confirmScheduleDeleteBtn');
                    if (confirmDeleteBtn) {
                        confirmDeleteBtn.addEventListener('click', () => {
                            if (this.pendingDeleteId !== null) {
                                this.executeDeleteSchedule(confirmDeleteBtn, this.pendingDeleteId);
                            }
                        });
                    }

                    const confirmBulkBtn = document.getElementById('confirmScheduleBulkDeleteBtn');
                    if (confirmBulkBtn) {
                        confirmBulkBtn.addEventListener('click', () => {
                            if (Array.isArray(this.pendingBulkIds) && this.pendingBulkIds.length) {
                                this.executeBulkDelete(confirmBulkBtn, this.pendingBulkIds.slice());
                            }
                        });
                    }
                }

                bindSearchInputs() {
                    const searchInput = document.getElementById('schedulesSearch');
                    const clearBtn = document.getElementById('schedulesSearchClear');
                    if (!searchInput || !clearBtn) return;

                    const toggle = () => {
                        clearBtn.classList.toggle('is-visible', searchInput.value.trim() !== '');
                    };

                    searchInput.addEventListener('input', toggle);
                    clearBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        toggle();
                        searchInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    });

                    toggle();
                }

                bindBulkBar() {
                    if (!this.bulkBar) return;

                    this.bulkBar.addEventListener('click', (event) => {
                        const button = event.target.closest('[data-bulk-action]');
                        if (!button) return;

                        const action = button.dataset.bulkAction;
                        if (action === 'clear') {
                            this.clearSelection();
                        } else if (action === 'delete') {
                            this.promptBulkDelete();
                        }
                    });
                }

                renderTable() {
                    if (!this.tableBody) return;

                    const data = this.getFilteredSchedules();
                    const availableIds = new Set(data.map((schedule) => String(schedule.id)));
                    Array.from(this.selection).forEach((id) => {
                        if (!availableIds.has(String(id))) {
                            this.selection.delete(String(id));
                        }
                    });

                    const bodyHtml = !Array.isArray(data) || data.length === 0 ?
                        this.buildEmptyStateRow() :
                        data.map((schedule) => this.buildRow(schedule)).join('');

                    this.tableBody.innerHTML = bodyHtml + this.buildSearchEmptyRow();

                    this.attachRowEvents();
                    this.bindSelectionHandlers();
                    this.syncSelectAllState();
                    this.updateBulkBar();
                    this.ensureController();
                    this.updateFilterToggleState();
                    this.refreshPillPalettes();
                }

                initSorting() {
                    this.sortHeaders = Array.from(document.querySelectorAll('#schedulesTable thead th[data-sort-key]'));
                    this.sortHeaders.forEach((header) => {
                        header.dataset.sortState = header.dataset.sortState || 'none';
                        header.addEventListener('click', () => {
                            const sortKey = header.dataset.sortKey;
                            if (!sortKey) {
                                return;
                            }
                            if (this.sortState.key === sortKey) {
                                this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' :
                                    'asc';
                            } else {
                                this.sortState.key = sortKey;
                                this.sortState.direction = 'asc';
                            }
                            this.updateSortIndicators();
                            this.renderTable();
                        });
                    });
                    this.updateSortIndicators();
                }

                updateSortIndicators() {
                    if (!Array.isArray(this.sortHeaders)) {
                        return;
                    }
                    this.sortHeaders.forEach((header) => {
                        header.dataset.sortState = 'none';
                        if (header.dataset.sortKey === this.sortState.key) {
                            header.dataset.sortState = this.sortState.direction;
                        }
                    });
                }

                getFilteredSchedules() {
                    const data = this.getSortedSchedules();
                    return data.filter((schedule) => this.matchesFilters(schedule));
                }

                getSortedSchedules() {
                    const data = Array.isArray(this.schedules) ? [...this.schedules] : [];
                    if (!this.sortState.key) {
                        return data.sort((a, b) => Number(b?.id ?? 0) - Number(a?.id ?? 0));
                    }
                    const multiplier = this.sortState.direction === 'asc' ? 1 : -1;
                    return data.sort((a, b) => {
                        const valueA = this.getScheduleSortValue(a, this.sortState.key);
                        const valueB = this.getScheduleSortValue(b, this.sortState.key);
                        return String(valueA ?? '').localeCompare(String(valueB ?? ''), undefined, {
                            sensitivity: 'base'
                        }) * multiplier;
                    });
                }

                getScheduleSortValue(schedule, key) {
                    switch (key) {
                        case 'course':
                            return schedule?.course_code ?? '';
                        default:
                            return '';
                    }
                }

                initFilters() {
                    this.refreshFilterOptions();

                    Object.entries(this.filterControls).forEach(([key, select]) => {
                        if (!select) return;
                        select.addEventListener('change', (event) => {
                            this.filters[key] = event.target.value || 'all';
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
                            this.renderTable();
                        });
                    }
                }

                refreshFilterOptions() {
                    const timeOptions = this.getUniqueFilterValues(
                        this.schedules,
                        (schedule) => schedule?.time ?? '',
                        (value) => value || 'N/A'
                    );
                    const dayOptions = this.getUniqueFilterValues(
                        this.schedules,
                        (schedule) => schedule?.day ?? '',
                        (value) => {
                            const labels = this.formatDayDisplay(value);
                            return labels.length ? labels.join(', ') : 'N/A';
                        }
                    );

                    this.populateFilterSelect(this.filterControls.time, timeOptions);
                    this.populateFilterSelect(this.filterControls.day, dayOptions);
                    this.updateFilterToggleState();
                }

                getUniqueFilterValues(items, accessor, formatter) {
                    const seen = new Map();
                    const list = Array.isArray(items) ? items : [];
                    list.forEach((item) => {
                        const raw = accessor(item) ?? '';
                        const value = this.normaliseValue(raw);
                        if (!value || seen.has(value)) {
                            return;
                        }
                        const label = formatter ? formatter(raw, item) : raw;
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

                matchesFilters(schedule) {
                    if (!schedule) return false;
                    if (this.filters.time !== 'all') {
                        const timeValue = this.normaliseValue(schedule.time ?? '');
                        if (timeValue !== this.filters.time) {
                            return false;
                        }
                    }
                    if (this.filters.day !== 'all') {
                        const dayValue = this.normaliseValue(schedule.day ?? '');
                        if (dayValue !== this.filters.day) {
                            return false;
                        }
                    }
                    return true;
                }

                updateFilterToggleState() {
                    if (!this.filterToggle) return;
                    const isActive = Object.values(this.filters).some((value) => value !== 'all');
                    this.filterToggle.classList.toggle('is-active', isActive);
                }

                buildRow(schedule) {
                    const id = String(schedule.id);
                    const isSelected = this.selection.has(id);
                    const courseCodeSource = schedule.course_code ?? '';
                    const courseCodeRaw = this.formatCourseText(courseCodeSource, 'N/A');
                    const courseCode = this.escapeHtml(courseCodeRaw);
                    const courseSubjectSource = schedule.course_subject ?? '';
                    const subtitleSource = courseSubjectSource !== '' ? courseSubjectSource : schedule.faculty_name ?? '';
                    const courseSubtitleRaw = courseSubjectSource !== '' ?
                        this.formatCourseText(courseSubjectSource, '') :
                        this.formatDisplayText(subtitleSource);
                    const courseSubtitle = this.escapeHtml(courseSubtitleRaw);
                    const timeParts = this.splitTimeRange(schedule.time);
                    const dayDisplay = this.formatDayDisplay(schedule.day);
                    const daySearch = dayDisplay.join(' ');
                    const searchTerms = [
                        courseCodeSource,
                        subtitleSource,
                        schedule.time ?? '',
                        timeParts.join(' '),
                        daySearch,
                        schedule.day ?? '',
                    ].join(' ').toLowerCase();

                    const courseValueAttr = 'schedule-course';
                    const courseContent = courseCode ?
                        `
                        <div class="table-cell-stack is-wide" title="${this.escapeAttribute([courseCodeSource || '', subtitleSource].filter(Boolean).join(' — '))}">
                            <span class="schedule-pill schedule-pill--course"
                                  data-pill-palette="purple"
                                  data-pill-value="${courseValueAttr}">${courseCode}</span>
                            ${courseSubtitle ? `<small class="d-block mt-1 text-muted">${courseSubtitle}</small>` : ''}
                        </div>
                      ` :
                        '<span class="text-muted">N/A</span>';

                    const timeContent = timeParts.length ?
                        timeParts
                        .map((part) => `<span class="schedule-pill"
                                              data-pill-palette="blue"
                                              data-pill-value="schedule-time">${this.escapeHtml(part)}</span>`)
                        .join('') :
                        '<span class="text-muted">N/A</span>';

                    const dayContent = dayDisplay.length ?
                        dayDisplay
                        .map((label) => `<span class="schedule-pill"
                                              data-pill-palette="gray"
                                              data-pill-value="schedule-day">${this.escapeHtml(label)}</span>`)
                        .join('') :
                        '<span class="text-muted">N/A</span>';

                    const sectionSource = schedule.section ?? '';
                    const sectionDisplay = sectionSource ?
                        `<span class="schedule-pill"
                            data-pill-palette="green"
                            data-pill-value="schedule-section">${this.escapeHtml(sectionSource)}</span>` :
                        '<span class="text-muted">—</span>';

                    const selectionCell = (schedulePermissions.canDelete || schedulePermissions.showDeleteDisabled) ?
                        `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input schedule-checkbox" data-row-select value="${id}" ${isSelected ? 'checked' : ''}>
                        </td>
                    ` :
                        '';

                    const actionItems = `
                    ${schedulePermissions.canEdit ? `
                                        <li>
                                            <button type="button" class="dropdown-item" data-action="edit" data-id="${id}">Edit</button>
                                        </li>
                                    ` : ''}
                    ${schedulePermissions.canEdit && (schedulePermissions.canDelete || schedulePermissions.showDeleteDisabled) ? '<li><hr class="dropdown-divider"></li>' : ''}
                    ${schedulePermissions.canDelete ? `
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-action="delete" data-id="${id}" data-name="${this.escapeAttribute(courseCodeRaw)}">Delete</button>
                                        </li>
                                    ` : ''}
                    ${(!schedulePermissions.canDelete && schedulePermissions.showDeleteDisabled) ? `
                                        <li>
                                            <button type="button" class="dropdown-item disabled text-muted" disabled aria-disabled="true">Delete</button>
                                        </li>
                                    ` : ''}
                `;

                    const actionsCell = (schedulePermissions.canEdit || schedulePermissions.canDelete || schedulePermissions
                            .showDeleteDisabled) ?
                        `
                        <td class="actions-cell">
                            <div class="dropdown">
                                <button class="schedule-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${actionItems}
                                </ul>
                            </div>
                        </td>
                    ` :
                        '<td class="text-muted">—</td>';

                    return `
                    <tr data-schedule-id="${id}" class="${isSelected ? 'is-selected' : ''}" data-search="${this.escapeAttribute(searchTerms)}">
                        ${selectionCell}
                        <td>
                            ${courseContent}
                        </td>
                        <td>
                            ${sectionDisplay}
                        </td>
                        <td>
                            <div class="schedule-pill-group">${timeContent}</div>
                        </td>
                        <td>
                            <div class="schedule-pill-group">${dayContent}</div>
                        </td>
                        ${actionsCell}
                    </tr>
                `;
                }

                buildEmptyStateRow() {
                    return `
                    <tr data-empty>
                        <td colspan="<?php echo e($canDelete || $showDeleteDisabled ? 6 : 5); ?>" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-calendar-days display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No schedules found</h5>
                                <p class="text-muted mb-0">Add a schedule using the button above.</p>
                            </div>
                        </td>
                    </tr>
                `;
                }

                buildSearchEmptyRow() {
                    return `
                    <tr data-empty-search style="display: none;">
                        <td colspan="<?php echo e($canDelete || $showDeleteDisabled ? 6 : 5); ?>" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fa-solid fa-magnifying-glass display-4 text-muted mb-3"></i>
                                <h5 class="mb-2">No results found</h5>
                                <p class="text-muted mb-0">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                `;
                }

                attachRowEvents() {
                    if (!this.tableBody) return;

                    this.tableBody.querySelectorAll('[data-action="edit"]').forEach((button) => {
                        if (button.dataset.bound === 'true') return;
                        button.dataset.bound = 'true';
                        button.addEventListener('click', () => {
                            const id = Number(button.dataset.id);
                            this.openEditModal(id);
                        });
                    });

                    this.tableBody.querySelectorAll('[data-action="delete"]').forEach((button) => {
                        if (button.dataset.bound === 'true') return;
                        button.dataset.bound = 'true';
                        button.addEventListener('click', () => {
                            const id = Number(button.dataset.id);
                            const name = button.dataset.name || 'this schedule';
                            this.openDeleteModal(id, name);
                        });
                    });
                }

                bindSelectionHandlers() {
                    if (!this.tableBody) return;

                    const checkboxes = Array.from(this.tableBody.querySelectorAll('[data-row-select]'));
                    checkboxes.forEach((checkbox) => {
                        const id = checkbox.value;
                        checkbox.checked = this.selection.has(id);
                        const row = checkbox.closest('tr');
                        if (row) {
                            row.classList.toggle('is-selected', checkbox.checked);
                        }

                        checkbox.onchange = () => {
                            if (checkbox.checked) {
                                this.selection.add(id);
                            } else {
                                this.selection.delete(id);
                            }
                            if (row) {
                                row.classList.toggle('is-selected', checkbox.checked);
                            }
                            this.syncSelectAllState();
                            this.updateBulkBar();
                        };
                    });

                    if (this.selectAllEl) {
                        this.selectAllEl.onchange = () => {
                            const shouldSelect = this.selectAllEl.checked;
                            this.getSelectableRows().forEach((row) => {
                                const checkbox = row.querySelector('[data-row-select]');
                                if (!checkbox) {
                                    return;
                                }
                                checkbox.checked = shouldSelect;
                                const id = checkbox.value;
                                if (shouldSelect) {
                                    this.selection.add(id);
                                } else {
                                    this.selection.delete(id);
                                }
                                row.classList.toggle('is-selected', shouldSelect);
                            });
                            this.syncSelectAllState();
                            this.updateBulkBar();
                        };
                    }
                }

                getSelectableRows() {
                    if (this.controller && Array.isArray(this.controller.filteredRows)) {
                        return this.controller.filteredRows;
                    }
                    if (!this.tableBody) {
                        return [];
                    }
                    return Array.from(this.tableBody.querySelectorAll('tr[data-schedule-id]'));
                }

                getSelectionScopeKey() {
                    const searchTerm = this.controller?.searchTerm ?? '';
                    const filterKey = JSON.stringify(this.filters);
                    return `${searchTerm}|${filterKey}`;
                }

                syncSelectAllState() {
                    if (!this.selectAllEl) return;

                    const rows = this.getSelectableRows();
                    if (!rows.length) {
                        this.selectAllEl.checked = false;
                        this.selectAllEl.indeterminate = false;
                        return;
                    }

                    const selectedVisible = rows.filter((row) => this.selection.has(row.dataset.scheduleId)).length;
                    if (selectedVisible === 0) {
                        this.selectAllEl.checked = false;
                        this.selectAllEl.indeterminate = false;
                    } else if (selectedVisible === rows.length) {
                        this.selectAllEl.checked = true;
                        this.selectAllEl.indeterminate = false;
                    } else {
                        this.selectAllEl.checked = false;
                        this.selectAllEl.indeterminate = true;
                    }
                }

                updateBulkBar() {
                    if (!this.bulkBar || !this.selectedCountEl) return;

                    const count = this.selection.size;
                    this.selectedCountEl.textContent = `${count} Selected`;
                    this.bulkBar.classList.toggle('d-none', count === 0);
                }

                ensureController() {
                    if (!this.tableRoot || typeof TableController === 'undefined') return;

                    if (this.controller) {
                        this.controller.refresh();
                    } else {
                        this.controller = new TableController(this.tableRoot);
                        window.tableControllers = window.tableControllers || {};
                        window.tableControllers.schedulesTable = this.controller;
                    }

                    this.tableRoot.addEventListener('table:updated', () => {
                        const scopeKey = this.getSelectionScopeKey();
                        if (this.selectAllEl?.checked && scopeKey !== this.lastSelectionScopeKey) {
                            this.lastSelectionScopeKey = scopeKey;
                            this.clearSelection();
                        } else {
                            this.lastSelectionScopeKey = scopeKey;
                        }
                        this.attachRowEvents();
                        this.bindSelectionHandlers();
                        this.syncSelectAllState();
                        this.updateBulkBar();
                        this.refreshPillPalettes();
                    });
                }

                refreshPillPalettes() {
                    if (!this.tableRoot) {
                        return;
                    }
                    applyPillPalettes(this.tableRoot);
                }

                openEditModal(scheduleId) { // Edit Schedule Modal functions and data population
                    const schedule = this.schedules.find((item) => Number(item.id) === Number(scheduleId));
                    if (!schedule) {
                        this.showAlert('error', 'Selected schedule record was not found.');
                        return;
                    }

                    this.currentEditId = scheduleId;
                    const form = document.getElementById('editScheduleForm');
                    if (!form) return;

                    form.querySelector('[name="schedule_id"]').value = schedule.id;
                    const courseCodeLabel = this.formatCourseText(schedule.course_code, '');
                    const courseSubjectLabel = this.formatCourseText(schedule.course_subject, '');
                    const fallbackCourseLabel = [courseCodeLabel, courseSubjectLabel]
                        .filter((part) => part && part.trim() !== '')
                        .join(' - ') || courseCodeLabel || `Course #${schedule.faculty_course_id ?? ''}`;
                    this.setCourseDropdownSelection(form, schedule.faculty_course_id ?? '', fallbackCourseLabel);

                    // Update faculty information display
                    const courseOption = form.querySelector(
                        `[data-course-option][data-course-id="${schedule.faculty_course_id}"]`);
                    if (courseOption) {
                        const facultyDisplay = form.querySelector('#editFacultyDisplay');
                        if (facultyDisplay) {
                            const faculty = courseOption.dataset.faculty || '';
                            const section = courseOption.dataset.section || '';
                            const academicYear = courseOption.dataset.academicYear || '';
                            const semester = courseOption.dataset.semester || '';

                            facultyDisplay.innerHTML = renderCourseInfoDisplay(faculty, section, academicYear, semester);
                        }
                    }

                    this.setTimeFields(form, schedule.time ?? '');
                    this.setDaySelection(form, this.splitDayString(schedule.day));

                    this.editModal?.show();
                }

                openDeleteModal(scheduleId, courseCode) {
                    const label = document.getElementById('scheduleDeleteName');
                    if (label) {
                        const rawLabel = this.formatCourseText(courseCode, '');
                        label.textContent = rawLabel || 'this schedule';
                    }
                    this.pendingDeleteId = scheduleId;
                    this.deleteModal?.show();
                }

                promptBulkDelete() {
                    const ids = Array.from(this.selection).map((id) => Number(id)).filter((id) => !Number.isNaN(id));
                    if (!ids.length) return;

                    this.pendingBulkIds = ids;
                    const counter = document.getElementById('scheduleBulkDeleteCount');
                    if (counter) {
                        counter.textContent = ids.length;
                    }
                    this.bulkDeleteModal?.show();
                }

                clearSelection() {
                    this.selection.clear();
                    if (this.tableBody) {
                        this.tableBody.querySelectorAll('[data-row-select]').forEach((checkbox) => {
                            checkbox.checked = false;
                        });
                        this.tableBody.querySelectorAll('tr').forEach((row) => {
                            row.classList.remove('is-selected');
                        });
                    }
                    if (this.selectAllEl) {
                        this.selectAllEl.checked = false;
                        this.selectAllEl.indeterminate = false;
                    }
                    this.updateBulkBar();
                }

                async handleCreateSchedule(form, data) {
                    const submitBtn = form.querySelector('[type="submit"]');
                    this.toggleButtonLoading(submitBtn, true, 'Save', 'Saving...');

                    try {
                        const response = await fetch('<?php echo e(route('dm.schedules.store')); ?>', {
                            method: 'POST',
                            headers: this.defaultHeaders(),
                            body: JSON.stringify(data),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to add schedule.');
                        }

                        this.schedules.push(this.normaliseSchedule(payload.data));
                        this.refreshFilterOptions();
                        this.renderTable();
                        this.createModal?.hide();
                        form.reset();
                        this.resetCourseDropdowns(form);
                        this.resetDayMultiselects(form);
                        this.showAlert('success', payload.message || 'Schedule added successfully.');
                    } catch (error) {
                        this.showAlert('error', error.message || 'Failed to add schedule.');
                    } finally {
                        this.toggleButtonLoading(submitBtn, false, 'Save');
                    }
                }

                async handleUpdateSchedule(form, scheduleId, data) {
                    const submitBtn = form.querySelector('[type="submit"]');
                    this.toggleButtonLoading(submitBtn, true, 'Save Changes', 'Saving...');

                    try {
                        const response = await fetch(`<?php echo e(route('dm.schedules.update', ':id')); ?>`.replace(':id',
                            scheduleId), {
                            method: 'PUT',
                            headers: this.defaultHeaders(),
                            body: JSON.stringify(data),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to update schedule.');
                        }

                        const normalised = this.normaliseSchedule(payload.data);
                        const index = this.schedules.findIndex((item) => Number(item.id) === Number(scheduleId));
                        if (index !== -1) {
                            this.schedules[index] = normalised;
                        }
                        this.refreshFilterOptions();
                        this.editModal?.hide();
                        this.renderTable();
                        this.showAlert('success', payload.message || 'Schedule updated successfully.');
                    } catch (error) {
                        this.showAlert('error', error.message || 'Failed to update schedule.');
                    } finally {
                        this.toggleButtonLoading(submitBtn, false, 'Save Changes');
                        this.currentEditId = null;
                    }
                }

                async executeDeleteSchedule(button, scheduleId) {
                    this.toggleButtonLoading(button, true, 'Delete', 'Deleting...');

                    try {
                        const response = await fetch(`<?php echo e(route('dm.schedules.destroy', ':id')); ?>`.replace(':id',
                            scheduleId), {
                            method: 'DELETE',
                            headers: this.deleteHeaders(),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to delete schedule.');
                        }

                        this.schedules = this.schedules.filter((schedule) => Number(schedule.id) !== Number(scheduleId));
                        this.selection.delete(String(scheduleId));
                        this.refreshFilterOptions();
                        this.deleteModal?.hide();
                        this.renderTable();
                        this.showAlert('success', payload.message || 'Schedule deleted successfully.');
                    } catch (error) {
                        this.showAlert('error', error.message || 'Failed to delete schedule.');
                    } finally {
                        this.toggleButtonLoading(button, false, 'Delete');
                        this.pendingDeleteId = null;
                    }
                }

                async executeBulkDelete(button, ids) {
                    this.toggleButtonLoading(button, true, 'Delete Selected', 'Deleting...');

                    try {
                        const response = await fetch('<?php echo e(route('dm.schedules.bulk-destroy')); ?>', {
                            method: 'POST',
                            headers: this.defaultHeaders(),
                            body: JSON.stringify({
                                ids
                            }),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok || !payload.success) {
                            throw new Error(payload.message || 'Failed to delete selected schedules.');
                        }

                        const idSet = new Set(ids.map((id) => Number(id)));
                        this.schedules = this.schedules.filter((schedule) => !idSet.has(Number(schedule.id)));
                        ids.forEach((id) => this.selection.delete(String(id)));
                        this.refreshFilterOptions();
                        this.bulkDeleteModal?.hide();
                        this.renderTable();
                        this.showAlert('success', payload.message || 'Selected schedules deleted successfully.');
                    } catch (error) {
                        this.showAlert('error', error.message || 'Failed to delete selected schedules.');
                    } finally {
                        this.toggleButtonLoading(button, false, 'Delete Selected');
                        this.pendingBulkIds = null;
                    }
                }

                collectScheduleFormData(form) {
                    const formData = new FormData(form);
                    const data = {};
                    const dayValues = [];
                    let timeStart = '';
                    let timeEnd = '';
                    formData.forEach((value, key) => {
                        if (key === 'day[]') {
                            dayValues.push(value);
                            return;
                        }
                        if (key === 'time_start') {
                            timeStart = (value || '').trim();
                            return;
                        }
                        if (key === 'time_end') {
                            timeEnd = (value || '').trim();
                            return;
                        }
                        data[key] = value;
                    });

                    if (dayValues.length) {
                        data.day = dayValues;
                    }

                    if (!Array.isArray(data.day) || !data.day.length) {
                        this.showAlert('error', 'Select at least one day.');
                        return null;
                    }

                    if (!data.faculty_course_id || !timeStart || !timeEnd) {
                        this.showAlert('error', 'Please complete all required fields.');
                        return null;
                    }

                    data.time = `${timeStart} to ${timeEnd}`;

                    return data;
                }

                resetCourseDropdowns(form) {
                    if (!form) return;
                    form.querySelectorAll('[data-course-dropdown]').forEach((dropdown) => {
                        dropdown.courseDropdownApi?.reset();
                    });
                }

                setCourseDropdownSelection(form, courseId, fallbackLabel = '') {
                    if (!form) return;
                    const dropdown = form.querySelector('[data-course-dropdown]');
                    if (dropdown?.courseDropdownApi) {
                        dropdown.courseDropdownApi.setValueById(courseId, fallbackLabel);
                    } else {
                        const input = form.querySelector('[name="faculty_course_id"]');
                        if (input) {
                            input.value = courseId ?? '';
                        }
                    }
                }

                resetDayMultiselects(form) {
                    if (!form) return;
                    form.querySelectorAll('[data-day-multiselect]').forEach((multiselect) => {
                        multiselect.dayMultiselectApi?.reset();
                    });
                }

                setDaySelection(form, codes) {
                    if (!form) return;
                    const values = Array.isArray(codes) ? codes.map(String) : [];
                    const multiselect = form.querySelector('[data-day-multiselect]');
                    if (multiselect?.dayMultiselectApi) {
                        multiselect.dayMultiselectApi.setSelected(values);
                        return;
                    }

                    const legacySelect = form.querySelector('select[name="day[]"]');
                    if (legacySelect) {
                        Array.from(legacySelect.options).forEach((option) => {
                            option.selected = values.includes(option.value);
                        });
                    }
                }

                setTimeDropdownValue(form, fieldName, value) {
                    if (!form || !fieldName) {
                        return;
                    }
                    const input = form.querySelector(`input[name="${fieldName}"]`);
                    if (!input) {
                        return;
                    }
                    const dropdown = input.closest('[data-time-dropdown]');
                    if (dropdown?.timeDropdownApi) {
                        dropdown.timeDropdownApi.setValue(value);
                    } else {
                        input.value = value ?? '';
                    }
                }

                setTimeFields(form, timeString) {
                    if (!form) return;
                    const [start = '', end = ''] = this.splitTimeRange(timeString);
                    this.setTimeDropdownValue(form, 'time_start', start);
                    this.setTimeDropdownValue(form, 'time_end', end);
                }

                splitDayString(dayString) {
                    if (!dayString) return [];
                    const codes = [];
                    let index = 0;
                    const upper = String(dayString).toUpperCase();
                    while (index < upper.length) {
                        if (upper.startsWith('TH', index)) {
                            codes.push('TH');
                            index += 2;
                        } else if (upper.startsWith('SU', index)) {
                            codes.push('SU');
                            index += 2;
                        } else {
                            codes.push(upper.charAt(index));
                            index += 1;
                        }
                    }
                    return codes;
                }

                splitTimeRange(timeString) {
                    if (!timeString) {
                        return [];
                    }
                    const normalized = String(timeString)
                        .replace(/\s+/g, ' ')
                        .trim();
                    if (!normalized) {
                        return [];
                    }
                    const tokens = normalized
                        .split(/(?:\bto\b|[-–—])/gi)
                        .map((part) => part.replace(/\s+/g, ' ').trim())
                        .filter((part) => part.length > 0);
                    return tokens.slice(0, 2);
                }

                formatDayDisplay(dayString) {
                    const mapping = {
                        M: 'Mon',
                        T: 'Tue',
                        W: 'Wed',
                        TH: 'Thu',
                        F: 'Fri',
                        S: 'Sat',
                        SU: 'Sun',
                    };
                    return this.splitDayString(dayString).map((code) => mapping[code] || code);
                }

                normaliseSchedule(payload) {
                    if (!payload) return {};
                    const facultyCourse = payload.faculty_course;
                    const course = facultyCourse?.course;
                    const faculty = facultyCourse?.faculty;
                    return {
                        id: payload.id,
                        faculty_course_id: payload.faculty_course_id,
                        course_code: course?.class_code,
                        course_subject: course?.subject_code ?? '',
                        faculty_name: faculty?.user?.name ?? faculty?.name ?? '',
                        time: payload.time,
                        day: payload.day,
                    };
                }

                toggleButtonLoading(button, isLoading, defaultText, loadingText = 'Saving...') {
                    if (!button) return;

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
                    if (!this.alertContainer) return;
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    this.alertContainer.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${this.escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
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
                    if (value === null || value === undefined) return '';
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

                formatCourseText(value, fallback = '') {
                    if (value === null || value === undefined) {
                        return fallback;
                    }
                    const trimmed = String(value).trim();
                    return trimmed === '' ? fallback : trimmed;
                }

                normaliseValue(value) {
                    return String(value ?? '')
                        .trim()
                        .toLowerCase();
                }
            }
        </script>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/dm-schedules.blade.php ENDPATH**/ ?>