<div class="row align-items-stretch">
    <div class="col-lg-6 mb-4 d-flex">
        <div class="card w-100 h-100 report-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Rated Faculties</h5>
            </div>
            <div class="card-body">
                @forelse($facultyRatings['top_rated'] as $index => $faculty)
                    @php
                        $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                            ->map(fn ($value) => trim($value))
                            ->filter(fn ($value) => $value !== '')
                            ->values();
                        $rankNumber = $index + 1;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-{{ $rankNumber <= 3 ? 'success' : 'primary' }} rounded-pill" style="min-width: 40px; display: flex; align-items: center; justify-content: center;">
                            #{{ $rankNumber }}
                        </span>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $faculty['faculty_name'] }}</h6>
                            <small class="text-muted">{{ $facultyDepartments->first() ?? 'No department' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-medium">{{ $faculty['average_rating'] }}/4.0</div>
                            <small class="text-muted">{{ $faculty['total_responses'] }} responses</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="bx bx-star text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No rating data available</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4 d-flex">
        <div class="card w-100 h-100 report-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Low Rated Faculties</h5>
            </div>
            <div class="card-body">
                @forelse($facultyRatings['low_rated'] as $index => $faculty)
                    @php
                        $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                            ->map(fn ($value) => trim($value))
                            ->filter(fn ($value) => $value !== '')
                            ->values();
                        $rankNumber = $index + 1;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-{{ $rankNumber <= 3 ? 'danger' : 'secondary' }} rounded-pill" style="min-width: 40px; display: flex; align-items: center; justify-content: center;">
                            #{{ $rankNumber }}
                        </span>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $faculty['faculty_name'] }}</h6>
                            <small class="text-muted">{{ $facultyDepartments->first() ?? 'No department' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-medium">{{ $faculty['average_rating'] }}/4.0</div>
                            <small class="text-muted">{{ $faculty['total_responses'] }} responses</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="bx bx-trending-down text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No rating data available</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card report-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Most Evaluated Faculties</h5>
            </div>
            <div class="card-body">
                @forelse($facultyRatings['most_evaluated'] as $index => $faculty)
                    @php
                        $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                            ->map(fn ($value) => trim($value))
                            ->filter(fn ($value) => $value !== '')
                            ->values();
                        $rankNumber = $index + 1;
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-{{ $rankNumber <= 3 ? 'info' : 'secondary' }} rounded-pill" style="min-width: 40px; display: flex; align-items: center; justify-content: center;">
                            #{{ $rankNumber }}
                        </span>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $faculty['faculty_name'] }}</h6>
                            <small class="text-muted">{{ $facultyDepartments->first() ?? 'No department' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-medium">{{ $faculty['total_responses'] }} responses</div>
                            <small class="text-muted">{{ $faculty['average_rating'] }}/4.0 avg</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="bx bx-bar-chart text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No evaluation data available</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
