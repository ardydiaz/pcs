@if ($canAdd)
    {{-- Add Faculty Modal --}}
    <div class="modal fade" id="facultyCreateModal" tabindex="-1" aria-labelledby="facultyCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="addFacultyForm">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="facultyCreateModalLabel">Add Faculty Member</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="createUserName">Faculty Name</label>
                            <div class="user-dropdown w-100" data-user-dropdown>
                                <input type="text" name="user_name" id="createUserName" class="form-control user-dropdown-input" placeholder="Faculty Name" autocomplete="off" data-user-name-input required>
                                <div class="dropdown-menu p-2">
                                    <div class="user-dropdown-list" data-user-list>
                                        @foreach($eligibleUsers as $user)
                                            @php
                                                $rawUserName = $user->name ?? '';
                                                $userDisplayName = trim($rawUserName) === '' ? 'Unknown' : $rawUserName;
                                            @endphp
                                            <button type="button"
                                                    class="dropdown-item"
                                                    data-user-option
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $userDisplayName }}"
                                                    data-user-name-lower="{{ strtolower($user->name ?? '') }}"
                                                    data-user-email-lower="{{ strtolower($user->email ?? '') }}">
                                                <span>{{ $userDisplayName }}</span>
                                                @if($user->email)
                                                    <small>{{ $user->email }}</small>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="hidden" name="user_id" id="createUserId">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="createEmployeeNo">Employee Number</label>
                            <input type="text" name="employee_no" id="createEmployeeNo" class="form-control" placeholder="EMP-0001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="searchEmployeeSurname">Search Employee by Surname</label>
                            <div class="input-group position-relative">
                                <input type="text" name="search_surname" id="searchEmployeeSurname" class="form-control" placeholder="Enter surname to search..." autocomplete="off">
                                <span class="input-group-text" id="employeeSearchSpinner" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                </span>
                            </div>
                            <div id="employeeSearchResults" class="dropdown-menu p-2 w-100" style="display: none; position: absolute; border: 1px solid #dee2e6; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000;">
                                <div id="employeeSearchList" class="list-unstyled mb-0">
                                    <!-- Search results will be populated here -->
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">Search MCU HRNet database to auto-fill employee number</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="createDepartment">Department</label>
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
                                <input type="hidden" name="department" id="createDepartment" data-department-input required>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="createJobTitle">Job Title</label>
                            <div class="user-dropdown w-100" data-user-dropdown data-job-dropdown>
                                <input type="text"
                                       name="job_title"
                                       id="createJobTitle"
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
                        <button type="submit" class="btn btn-faculty-primary evaluation-modal-btn" data-default-text="Save" data-loading-text="Saving...">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const searchInput = document.getElementById('searchEmployeeSurname');
            const spinner = document.getElementById('employeeSearchSpinner');
            const resultsContainer = document.getElementById('employeeSearchResults');
            const resultsList = document.getElementById('employeeSearchList');
            const employeeNoInput = document.getElementById('createEmployeeNo');
            let searchTimeout;

            if (!searchInput) return;

            // Search for employees by surname
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    resultsContainer.style.display = 'none';
                    resultsList.innerHTML = '';
                    return;
                }

                // Show spinner
                spinner.style.display = 'inline-flex';

                // Debounce API call
                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch('{{ route("faculties.search-employee") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ query: query })
                        });

                        const data = await response.json();

                        if (data.success && data.data.length > 0) {
                            resultsList.innerHTML = data.data
                                .map(emp => `
                                    <a href="#" class="list-group-item list-group-item-action employee-option" data-employee-no="${emp.employee_no}" data-employee-name="${emp.name}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>${emp.employee_no}</strong>
                                                <br>
                                                <small class="text-muted">${emp.name}</small>
                                            </div>
                                        </div>
                                    </a>
                                `)
                                .join('');
                            
                            // Add click handlers to options
                            document.querySelectorAll('.employee-option').forEach(option => {
                                option.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    employeeNoInput.value = option.dataset.employeeNo;
                                    searchInput.value = '';
                                    resultsContainer.style.display = 'none';
                                    resultsList.innerHTML = '';
                                });
                            });

                            resultsContainer.style.display = 'block';
                        } else {
                            resultsList.innerHTML = '<div class="list-group-item" style="cursor: not-allowed;"><small class="text-muted">No employees found</small></div>';
                            resultsContainer.style.display = 'block';
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        resultsList.innerHTML = '<div class="list-group-item" style="cursor: not-allowed;"><small class="text-danger">Error searching employees</small></div>';
                        resultsContainer.style.display = 'block';
                    } finally {
                        spinner.style.display = 'none';
                    }
                }, 500);
            });

            // Close results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.style.display = 'none';
                }
            });

            // Prevent form submission on Enter in search field
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        })();
    </script>
@endif
