@extends('layouts.contentNavbarLayout')

@section('title', 'System Activity Logs')

@section('page-style')
<style>
    .logs-table {
        font-size: 0.875rem;
    }

    .filter-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .log-description {
        max-width: 360px;
        white-space: normal;
    }

    .log-json {
        max-width: 420px;
        max-height: 180px;
        overflow: auto;
        margin: 0;
        font-size: 0.75rem;
        white-space: pre-wrap;
    }

    .severity-info { background: #e7f1ff; color: #0d47a1; }
    .severity-warning { background: #fff4db; color: #8a5a00; }
    .severity-danger { background: #fde7e9; color: #9f1239; }
    .severity-critical { background: #f3e8ff; color: #581c87; }

    .security-hero {
        background:
            radial-gradient(circle at 86% 10%, rgba(255, 183, 54, 0.22), transparent 28%),
            linear-gradient(135deg, #3a0050, #6f2a8f 58%, #a8327d);
        border-radius: 1rem;
        color: #fff;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1rem 2rem rgba(58, 0, 80, 0.18);
    }

    .security-hero h4,
    .security-hero p {
        color: #fff;
    }

    .security-stat-card {
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.9rem;
        background: rgba(255, 255, 255, 0.12);
        padding: 0.85rem;
        min-height: 100%;
    }

    .security-stat-card span {
        display: block;
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .security-stat-card strong {
        display: block;
        color: #fff;
        font-size: 1.55rem;
        line-height: 1;
        margin-top: 0.35rem;
    }

    .security-alert-list {
        border-radius: 0.85rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.75rem;
    }

    .security-alert-item {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.45rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.82rem;
    }

    .security-alert-item:last-child {
        border-bottom: 0;
    }
</style>
@endsection

@section('content')
<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">System Activity Logs</h4>
            <p class="text-muted mb-0">Monitor user activity, affected modules, and record changes.</p>
        </div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i>Print Logs
        </button>
    </div>

    <div class="security-hero">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-7">
                <h4 class="mb-1">Access Monitoring</h4>
                <p class="mb-3">Trace successful logins, failed attempts, denied outsider domains, IP addresses, and device information.</p>
                <div class="row g-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="security-stat-card">
                            <span>Logins Today</span>
                            <strong>{{ number_format($securityStats['successful_logins_today'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="security-stat-card">
                            <span>Failed/Denied</span>
                            <strong>{{ number_format($securityStats['failed_or_denied_today'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="security-stat-card">
                            <span>Unique IPs</span>
                            <strong>{{ number_format($securityStats['unique_ips_today'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="security-stat-card">
                            <span>Outsiders 7d</span>
                            <strong>{{ number_format($securityStats['outsider_attempts_7_days'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="security-alert-list h-100">
                    <div class="fw-bold mb-2">Recent Security Alerts</div>
                    @forelse(($recentSecurityAlerts ?? collect()) as $alert)
                        <div class="security-alert-item">
                            <div>
                                <strong>{{ ucwords(str_replace('_', ' ', $alert->action ?? 'Alert')) }}</strong>
                                <div>{{ $alert->email ?: $alert->name ?: 'Unknown account' }}</div>
                            </div>
                            <div class="text-end">
                                <div>{{ $alert->ipAddress ?: '-' }}</div>
                                <small>{{ optional($alert->created_at)->format('M d h:i A') }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="security-alert-item">
                            <div>No failed or denied login attempts recorded.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('um.audit-logs') }}" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                    placeholder="Name, email, IP, description"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Module</label>
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ ($filters['module'] ?? '') === $module ? 'selected' : '' }}>
                            {{ $module }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Severity</label>
                <select name="severity" class="form-select">
                    <option value="">All Severities</option>
                    @foreach($severities as $severity)
                        <option value="{{ $severity }}" {{ ($filters['severity'] ?? '') === $severity ? 'selected' : '' }}>
                            {{ ucfirst($severity) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Quick Date Range</label>
                <select name="date_range" class="form-select">
                    <option value="">All Time</option>
                    <option value="today" {{ ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ ($filters['date_range'] ?? '') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ ($filters['date_range'] ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Rows</label>
                <select name="per_page" class="form-select">
                    @foreach([10, 20, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 20) == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-filter-alt me-1"></i>Filter
                </button>
                <a href="{{ route('um.audit-logs') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Activity Logs</h5>
            <span class="text-muted">{{ number_format($logs->total()) }} result{{ $logs->total() === 1 ? '' : 's' }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover logs-table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>IP</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $severity = $log->severity ?: 'info';
                                $hasChanges = !empty($log->before_values) || !empty($log->after_values);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $log->name ?: 'System' }}</div>
                                    <small class="text-muted">{{ $log->email ?: $log->role ?: '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">
                                        {{ ucwords(str_replace('_', ' ', $log->action ?? '-')) }}
                                    </span>
                                    <div><small class="text-muted">{{ $log->method ?: '-' }}</small></div>
                                </td>
                                <td>{{ $log->module ?: '-' }}</td>
                                <td class="log-description">
                                    {{ $log->description ?: '-' }}
                                    @if($log->target_type || $log->target_id)
                                        <div>
                                            <small class="text-muted">
                                                Target: {{ class_basename($log->target_type) ?: '-' }} #{{ $log->target_id ?: '-' }}
                                            </small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge severity-{{ $severity }}">{{ ucfirst($severity) }}</span>
                                </td>
                                <td>
                                    <code class="text-body">{{ $log->ipAddress ?: '-' }}</code>
                                    <div>
                                        <small class="text-muted" title="{{ $log->userAgent }}">
                                            {{ \Illuminate\Support\Str::limit($log->userAgent ?: 'Unknown device', 42) }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($hasChanges)
                                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#logChanges{{ $log->id }}">
                                            View
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @if($hasChanges)
                                <tr class="collapse" id="logChanges{{ $log->id }}">
                                    <td colspan="8" class="bg-light">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="fw-medium mb-1">Before</div>
                                                <pre class="log-json">{{ json_encode($log->before_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '-' }}</pre>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="fw-medium mb-1">After</div>
                                                <pre class="log-json">{{ json_encode($log->after_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '-' }}</pre>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted" title="{{ $log->userAgent }}">User agent: {{ $log->userAgent ?: '-' }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bx bx-history bx-lg text-muted"></i>
                                    </div>
                                    <h6 class="text-muted">No activity logs found</h6>
                                    <p class="text-muted mb-0">Try adjusting your search or filters.</p>
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
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.querySelector('input[name="search"]')?.focus();
    }
});
</script>
@endsection
