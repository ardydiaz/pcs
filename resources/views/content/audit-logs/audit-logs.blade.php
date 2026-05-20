@extends('layouts.contentNavbarLayout')

@section('title', 'System Activity Logs')

@section('page-style')
<style>
    .logs-table {
        font-size: 0.875rem;
    }
    .action-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .action-faculty_created { background: #f3e5f5; color: #7b1fa2; }
    .action-login { background: #f3e5f5; color: #7b1fa2; }
    .action-logout { background: #f3e5f5; color: #7b1fa2; }
    .action-user_registration { background: #f3e5f5; color: #7b1fa2; }
    .user-agent-cell {
        max-width: 500px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .filter-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">System Activity Logs</h4>
            <p class="text-muted mb-0">Monitor user activities and system events</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bx bx-printer me-1"></i>Print Logs
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('um.audit-logs') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by user name..." 
                       value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Action Type</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="faculty_created" {{ request('action') === 'faculty_created' ? 'selected' : '' }}>Faculty Created</option>
                    <option value="user_registration" {{ request('action') === 'user_registration' ? 'selected' : '' }}>User Registration</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <select name="date_range" class="form-select">
                    <option value="">All Time</option>
                    <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid w-100 gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-filter-alt me-1"></i>Filter
                    </button>
                    <a href="{{ route('um.audit-logs') }}" class="btn btn-outline-secondary btn-sm">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Logs Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Activity Logs</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Show:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-muted">entries</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover logs-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 180px;">Date & Time</th>
                            <th style="width: 180px;">User</th>
                            <th style="width: 150px;">Action</th>
                            <th style="width: 80px;">Method</th>
                            <th style="width: 120px;">IP Address</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $log->name }}</div>
                                </td>
                                <td>
                                    @php
                                        $actionClass = 'action-' . str_replace(' ', '_', strtolower($log->action));
                                        $actionLabels = [
                                            'login' => 'Login',
                                            'logout' => 'Logout', 
                                            'faculty_created' => 'Faculty Created',
                                            'user_registration' => 'User Registration'
                                        ];
                                        $actionLabel = $actionLabels[$log->action] ?? ucfirst(str_replace('_', ' ', $log->action));
                                    @endphp
                                    <span class="badge action-badge {{ $actionClass }}">
                                        {{ $actionLabel }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->method)
                                        <span class="badge bg-label-secondary">{{ $log->method }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-body">{{ $log->ipAddress ?: '-' }}</code>
                                </td>
                                <td>
                                    <div class="user-agent-cell" title="{{ $log->userAgent }}">
                                        {{ $log->userAgent ?: '-' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bx bx-history bx-lg text-muted"></i>
                                    </div>
                                    <h6 class="text-muted">No activity logs found</h6>
                                    <p class="text-muted mb-0">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                    </div>
                    <div>
                        {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

function exportLogs() {
    const url = new URL(window.location);
    url.searchParams.set('export', '1');
    window.open(url.toString(), '_blank');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    setupAutoRefresh();
    
    // Add tooltips for user agents
    const userAgentCells = document.querySelectorAll('.user-agent-cell');
    userAgentCells.forEach(cell => {
        if (cell.scrollWidth > cell.clientWidth) {
            cell.style.cursor = 'help';
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+F to focus search
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.querySelector('input[name="search"]')?.focus();
    }
    
    // Ctrl+R to refresh
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        window.location.reload();
    }
});
</script>
@endsection