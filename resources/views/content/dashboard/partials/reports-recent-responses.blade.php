<div class="recent-activity p-3">
    @forelse($recentResponses as $response)
        <div class="d-flex align-items-center mb-3">
            <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-light text-dark">
                    {{ substr($response->resolved_course_code ?? 'N', 0, 2) }}
                </span>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">{{ $response->resolved_course_code }}</h6>
                <div class="d-flex align-items-center">
                    <span class="badge bg-{{
                        $response->effectiveness_rating == '4' ? 'success' :
                            ($response->effectiveness_rating == '3' ? 'info' :
                                ($response->effectiveness_rating == '2' ? 'warning' : 'danger'))
                    }} me-2">{{ $response->effectiveness_rating }}</span>
                    <small class="text-muted">{{ $response->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4">
            <i class="bx bx-time text-muted mb-2" style="font-size: 2rem;"></i>
            <p class="text-muted mb-0">No recent activity</p>
        </div>
    @endforelse
</div>
