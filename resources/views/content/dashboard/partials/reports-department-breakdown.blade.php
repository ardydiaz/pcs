{{-- Table Controls --}}
<div class="row mb-3 g-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Show entries</label>
        <select class="form-select form-select-sm" id="departmentPerPage" onchange="updatePerPage(this.value)">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-3">
        <label class="form-label">Search</label>
        <input type="text" class="form-control form-control-sm" id="departmentSearch"
            placeholder="Search departments..." onkeyup="filterDepartments(this.value)">
    </div>
</div>

<div class="table-responsive" id="departmentTableContainer">
    <table class="table table-hover" id="departmentTable">
        <thead>
            <tr>
                <th>Department</th>
                <th class="text-center">Faculties</th>
                <th class="text-center">Evaluations</th>
                <th class="text-center">Responses</th>
                <th class="text-center">Subject Type Split</th>
                <th class="text-center">Avg Rating</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departmentBreakdown as $dept)
                @php
                    $departmentAcronyms = [
                        'College of Nursing' => 'CON',
                        'Basic Education' => 'BED',
                        'College of Dentistry' => 'COD',
                        'College of Arts and Sciences' => 'CAS',
                        'College of Medical Technology' => 'CMT',
                        'School of Business and Management' => 'SBM',
                        'College of Optometry' => 'CO',
                        'College of Pharmacy' => 'CPH',
                        'College of Physical Therapy' => 'CPT',
                        'College of Medicine' => 'COM',
                    ];
                    $departmentAcronym = $departmentAcronyms[$dept['department']] ?? strtoupper(substr($dept['department'], 0, 2));
                @endphp
                <tr class="department-row" data-department="{{ $dept['department'] }}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    {{ $departmentAcronym }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $dept['department'] }}</h6>
                                <small class="text-muted">{{ $dept['active_evaluations'] }} active</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">{{ $dept['faculty_count'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $dept['total_evaluations'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">{{ $dept['total_responses'] }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $majorR = $dept['major_responses'] ?? 0;
                            $minorR = $dept['minor_responses'] ?? 0;
                        @endphp
                        <div class="d-flex flex-column gap-1 align-items-center">
                            @if($selectedSubjectType === 'major' || $selectedSubjectType === 'all')
                                <div class="d-flex align-items-center gap-1"
                                    title="Professional Course: {{ $majorR }} responses">
                                    <span class="badge bg-label-success">
                                        <i class="bx bx-book-open me-1"></i>Professional
                                    </span>
                                    <span class="fw-medium small">{{ $majorR }} resp.</span>
                                </div>
                            @endif
                            @if($selectedSubjectType === 'minor' || $selectedSubjectType === 'all')
                                <div class="d-flex align-items-center gap-1"
                                    title="GenEd Course: {{ $minorR }} responses">
                                    <span class="badge bg-label-warning">
                                        <i class="bx bx-book me-1"></i>GenEd
                                    </span>
                                    <span class="fw-medium small">{{ $minorR }} resp.</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="fw-medium">{{ $dept['average_rating'] }}</span>
                        <div class="department-rating-indicator">
                            <span class="department-rating-indicator-fill"
                                style="width: {{ min(100, max(0, ($dept['average_rating'] / 4) * 100)) }}%"></span>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="showFacultyModal('{{ $dept['department'] }}')">
                            <i class="bx bx-group me-1"></i>View Faculty
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="bx bx-buildings text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No department data available</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-3" id="departmentPagination">
    <div>
        <small class="text-muted">Showing <span id="departmentShowing">{{ count($departmentBreakdown) }}</span> of
            {{ count($departmentBreakdown) }} entries</small>
    </div>
</div>
