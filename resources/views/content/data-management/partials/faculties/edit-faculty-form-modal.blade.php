@if ($canEdit)
    {{-- Edit Faculty Modal --}}
    <div class="modal fade" id="facultyEditModal" tabindex="-1" aria-labelledby="facultyEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="editFacultyForm">
                    @csrf
                    <input type="hidden" name="faculty_id" value="">
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="facultyEditModalLabel">Edit Faculty Member</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editFacultyName">Faculty Name</label>
                            <input type="text" id="editFacultyName" class="form-control" readonly tabindex="-1">
                            <input type="hidden" name="user_id" id="editUserId">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editEmployeeNo">Employee Number</label>
                            <input type="text" name="employee_no" id="editEmployeeNo" class="form-control" readonly tabindex="-1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editDepartment">Department</label>
                            <div class="dropdown w-100 department-multiselect" data-department-multiselect>
                                <button class="department-multiselect-toggle w-100 d-flex align-items-center text-start"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-display="static"
                                        data-bs-auto-close="outside">
                                    <div class="department-multiselect-content flex-grow-1">
                                        <div class="department-multiselect-chips" data-department-selected></div>
                                        <span class="department-multiselect-placeholder" data-department-placeholder>-- Select Department --</span>
                                    </div>
                                    <i class="bx bx-chevron-down fs-5 ms-2 text-muted"></i>
                                </button>
                                <div class="dropdown-menu p-3">
                                    <div class="department-multiselect-search">
                                        <input type="text" class="form-control" placeholder="Search departments..." data-department-search>
                                    </div>
                                    <div class="department-multiselect-list" data-department-list>
                                        @foreach ($departmentSelectOptions as $department)
                                            <label class="department-multiselect-option"
                                                   data-department-option
                                                   data-value="{{ $department }}"
                                                   data-label="{{ $department }}"
                                                   data-search="{{ strtolower($department) }}">
                                                <input type="checkbox" class="form-check-input" data-department-checkbox value="{{ $department }}">
                                                <span>{{ $department }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="department" id="editDepartment" data-department-input required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editJobTitle">Job Title</label>
                            <div class="user-dropdown w-100" data-user-dropdown data-job-dropdown>
                                <input type="text"
                                       name="job_title"
                                       id="editJobTitle"
                                       class="form-control user-dropdown-input"
                                       placeholder="Job Title"
                                       autocomplete="off"
                                       data-user-name-input
                                       required>
                                <div class="dropdown-menu p-2">
                                    <div class="user-dropdown-list" data-user-list>
                                        @foreach($jobTitleOptions as $jobTitle)
                                            <button type="button"
                                                    class="dropdown-item"
                                                    data-user-option
                                                    data-user-name="{{ $jobTitle }}"
                                                    data-user-name-lower="{{ strtolower($jobTitle) }}"
                                                    data-user-email-lower="">
                                                <span>{{ $jobTitle }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-faculty-primary evaluation-modal-btn" data-default-text="Save Changes" data-loading-text="Saving...">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
