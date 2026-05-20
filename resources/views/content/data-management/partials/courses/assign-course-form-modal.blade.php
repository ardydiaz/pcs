@if ($canAdd)
    {{-- Assign Course Modal --}}
    <div class="modal fade" id="assignmentCreateModal" tabindex="-1" aria-labelledby="assignmentCreateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="assignmentCreateForm">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="assignmentCreateModalLabel">Assign Major Course</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="createAssignmentFacultyToggle">Faculty Member</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
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
                        <div class="mb-3">
                            <label class="form-label" for="createAssignmentCourseToggle">Course</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
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

                        <div class="mb-3">
                            <label class="form-label" for="createAssignmentSection">Section</label>
                            <div class="user-dropdown w-100" data-user-dropdown data-section-dropdown>
                                <input type="text" class="form-control user-dropdown-input"
                                    id="createAssignmentSection" name="section" placeholder="Section" autocomplete="off"
                                    data-user-name-input required>
                                <div class="dropdown-menu p-2">
                                    <div class="user-dropdown-list" data-user-list>
                                        @foreach ($sectionOptions as $section)
                                            <button type="button" class="dropdown-item" data-user-option
                                                data-user-name="{{ $section }}"
                                                data-user-name-lower="{{ strtolower($section) }}"
                                                data-user-email-lower="">
                                                <span>{{ $section }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="createAssignmentYearToggle">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
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
                        <div class="mb-0">
                            <label class="form-label" for="createAssignmentSemesterToggle">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
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
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-course-primary evaluation-modal-btn"
                            data-default-text="Assign" data-loading-text="Assigning...">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
