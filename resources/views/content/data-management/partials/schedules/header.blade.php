{{-- Modern Schedule Header --}}
<div class="module-hero mb-4">
    <div class="module-hero__content">
        <div class="module-hero__eyebrow">
            <i class="fa-solid fa-calendar-days"></i>
            Schedule Directory
        </div>
        <div class="module-hero__main">
            <div>
                <h4 class="module-hero__title">Manage Schedules</h4>
                <p class="module-hero__subtitle">Create, edit, filter, and review course schedules including open-hour entries.</p>
            </div>
            <div class="module-hero__actions">
                @if ($canAdd)
                    <button type="button" class="btn module-hero__action-primary" data-bs-toggle="modal"
                        data-bs-target="#scheduleCreateModal">
                        <i class="fa-solid fa-calendar-plus me-2"></i>Add Schedule
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="module-hero__stats">
        <div class="module-hero__stat">
            <span class="module-hero__stat-icon"><i class="fa-solid fa-clock"></i></span>
            <span class="module-hero__stat-label">Time Mode</span>
            <strong class="module-hero__stat-value">Open</strong>
        </div>
        <div class="module-hero__stat">
            <span class="module-hero__stat-icon"><i class="fa-solid fa-filter"></i></span>
            <span class="module-hero__stat-label">Filters</span>
            <strong class="module-hero__stat-value">Ready</strong>
        </div>
    </div>
</div>
