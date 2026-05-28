<div class="table-responsive">
    <table class="table table-hover faculty-modal-table" id="facultyTable">
        <thead>
            <tr>
                <th data-sort-key="name" class="sortable" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Faculty Name</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th>Email</th>
                <th>Job Title</th>
                <th class="text-center sortable" data-sort-key="evaluations" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Evaluations</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="responses" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Responses</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="avg_rating" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Avg Rating</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="status" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Status</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($faculties as $faculty)
                @php
                    $facultyName = data_get($faculty, 'name', 'Unknown');
                    $facultyEmail = data_get($faculty, 'email');
                    $facultyJobTitle = data_get($faculty, 'job_title');
                    $facultyStatus = data_get($faculty, 'status', 'active');
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    {{ substr($facultyName, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $facultyName }}</h6>
                            </div>
                        </div>
                    </td>
                    <td>{{ $facultyEmail ?? 'N/A' }}</td>
                    <td>{{ $facultyJobTitle ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $faculty->evaluation_count }}</span>
                        <br>
                        <small class="text-success">{{ $faculty->active_evaluations }} active</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">{{ $faculty->response_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($faculty->average_rating > 0)
                            <span class="fw-medium">{{ $faculty->average_rating }}/4.0</span>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-{{ $faculty->average_rating >= 3 ? 'success' : ($faculty->average_rating >= 2 ? 'warning' : 'danger') }}"
                                    style="width: {{ ($faculty->average_rating / 4) * 100 }}%"></div>
                            </div>
                        @else
                            <span class="text-muted">No ratings</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $facultyStatus === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($facultyStatus) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if(!empty($faculty->evaluation_id))
                            <a class="btn btn-sm btn-outline-primary"
                                href="{{ route('dm.evaluation.responses', $faculty->evaluation_id) }}?from=reports&department={{ $selectedDepartment }}&academic_year={{ $selectedAcademicYear }}&semester={{ $selectedSemester }}&subject_type={{ $selectedSubjectType }}">
                                <i class="bx bx-show me-1"></i>View Responses
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bx bx-user-x text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No faculty members found</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
