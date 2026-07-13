{{-- Edit Schedule Modal Partial --}}
@if ($canEdit)
    <div class="modal fade" id="scheduleEditModal" tabindex="-1" aria-labelledby="scheduleEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <form id="editScheduleForm">
                    @csrf
                    <input type="hidden" name="schedule_id" value="">
                    <div class="modal-header schedule-modal-header">
                        <h5 class="modal-title mb-0" id="scheduleEditModalLabel">Edit Schedule</h5>
                        <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body schedule-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editFacultyCourse">Faculty Course</label>
                            <div class="dropdown w-100 course-dropdown" data-course-dropdown>
                                <button class="course-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-display="static"
                                        data-course-dropdown-toggle>
                                    <span class="course-dropdown-label is-placeholder"
                                          data-course-dropdown-label
                                          data-placeholder-text="-- Select Course --">-- Select Course --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control course-dropdown-search" placeholder="Search courses..." data-course-search>
                                    <div class="course-dropdown-list" data-course-list>
                                        @foreach($facultyCourseOptions as $option)
                                            <button type="button"
                                                    class="dropdown-item"
                                                    data-course-option
                                                    data-course-id="{{ $option['id'] }}"
                                                    data-course-label="{{ $option['label'] }}"
                                                    data-course-search="{{ $option['search'] }}"
                                                    data-faculty="{{ $option['faculty'] ?? '' }}"
                                                    data-section="{{ $option['section'] ?? '' }}"
                                                    data-academic-year="{{ $option['academic_year_display'] ?? '' }}"
                                                    data-semester="{{ $option['semester'] ?? '' }}">
                                                <span class="course-dropdown-title">
                                                    {{ $option['code'] }}
                                                    @if(!empty($option['subject']))
                                                        - {{ $option['subject'] }}
                                                    @endif
                                                </span>
                                                @if(!empty($option['faculty']) || !empty($option['academic_year_display']) || !empty($option['semester']) || !empty($option['section']))
                                                    <small class="course-dropdown-info" title="{{ trim(implode(' | ', array_filter([$option['faculty'], $option['section'], $option['academic_year_display'], $option['semester']]))) }}">
                                                        @if(!empty($option['faculty']))
                                                            <span>{{ $option['faculty'] }}</span>
                                                        @endif
                                                        @if(!empty($option['section']))
                                                            <span>Section. {{ $option['section'] }}</span>
                                                        @endif
                                                        @if(!empty($option['academic_year_display']))
                                                            <span>{{ $option['academic_year_display'] }}</span>
                                                        @endif
                                                        @if(!empty($option['semester']))
                                                            <span>{{ $option['semester'] }}</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="faculty_course_id" id="editFacultyCourse" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Faculty Information</label>
                            <div id="editFacultyDisplay" class="card bg-light p-3" style="min-height: 80px; display: flex; align-items: center; justify-content: center;">
                                <span class="text-muted text-center">Select a course to view faculty information</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <label class="form-label mb-0" for="editTimeStart">Time</label>
                                <button type="button" class="schedule-open-hour-btn" data-open-hour-clear>
                                    <i class="bx bx-time-five"></i>
                                    Clear Time
                                </button>
                            </div>
                            <div class="time-range-group">
                                <div class="dropdown time-dropdown" data-time-dropdown>
                                    <button class="time-dropdown-toggle"
                                            type="button"
                                            id="editTimeStartToggle"
                                            data-bs-toggle="dropdown"
                                            data-bs-display="static"
                                            data-time-dropdown-toggle>
                                        <span class="time-dropdown-label is-placeholder"
                                              data-time-label
                                              data-placeholder-text="-- Select Time --">-- Select Time --</span>
                                        <i class="bx bx-chevron-down fs-5 text-muted"></i>
                                    </button>
                                    <div class="dropdown-menu p-2">
                                        <input type="text" class="form-control time-dropdown-search" placeholder="Search time..." data-time-search>
                                        <div class="time-dropdown-list" data-time-list>
                                            @foreach($timeOptions as $time)
                                                <button type="button"
                                                        class="dropdown-item"
                                                        data-time-option
                                                        data-option-value="{{ $time }}"
                                                        data-option-label="{{ $time }}"
                                                        data-option-filter="{{ strtolower($time) }}">
                                                    <span>{{ $time }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <input type="hidden" name="time_start" id="editTimeStart">
                                </div>
                                <span class="time-range-separator">to</span>
                                <div class="dropdown time-dropdown" data-time-dropdown>
                                    <button class="time-dropdown-toggle"
                                            type="button"
                                            id="editTimeEndToggle"
                                            data-bs-toggle="dropdown"
                                            data-bs-display="static"
                                            data-time-dropdown-toggle>
                                        <span class="time-dropdown-label is-placeholder"
                                              data-time-label
                                              data-placeholder-text="-- Select Time --">-- Select Time --</span>
                                        <i class="bx bx-chevron-down fs-5 text-muted"></i>
                                    </button>
                                    <div class="dropdown-menu p-2">
                                        <input type="text" class="form-control time-dropdown-search" placeholder="Search time..." data-time-search>
                                        <div class="time-dropdown-list" data-time-list>
                                            @foreach($timeOptions as $time)
                                                <button type="button"
                                                        class="dropdown-item"
                                                        data-time-option
                                                        data-option-value="{{ $time }}"
                                                        data-option-label="{{ $time }}"
                                                        data-option-filter="{{ strtolower($time) }}">
                                                    <span>{{ $time }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <input type="hidden" name="time_end" id="editTimeEnd">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Day(s)</label>
                            <div class="dropdown w-100 day-multiselect" data-day-multiselect>
                                <button class="day-multiselect-toggle w-100 d-flex align-items-center text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-display="static"
                                        data-bs-auto-close="outside">
                                    <div class="day-multiselect-content flex-grow-1">
                                        <div class="day-multiselect-chips" data-day-selected></div>
                                        <span class="day-multiselect-placeholder" data-day-placeholder>-- Select Day(s) --</span>
                                    </div>
                                    <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                                </button>
                                <div class="dropdown-menu p-3">
                                    <div class="day-multiselect-search">
                                        <input type="text" class="form-control" placeholder="Search days..." data-day-search>
                                    </div>
                                    <div class="day-multiselect-list" data-day-list>
                                        @foreach($dayOptions as $day)
                                            <label class="day-multiselect-option"
                                                   data-day-option
                                                   data-value="{{ $day['value'] }}"
                                                   data-label="{{ $day['label'] }}"
                                                   data-search="{{ strtolower($day['label']) }}">
                                                <input type="checkbox" class="form-check-input" data-day-checkbox value="{{ $day['value'] }}">
                                                <span>{{ $day['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div data-day-inputs class="d-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer schedule-modal-footer">
                        <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-schedule-primary schedule-modal-btn" data-default-text="Save Changes" data-loading-text="Saving...">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
