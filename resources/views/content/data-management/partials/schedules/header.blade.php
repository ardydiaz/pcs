{{-- Schedule Header Card Partial --}}
<!-- Schedule Header Card -->
<div class="card schedule-card schedule-card--table mb-4 schedule-action-card">
    <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
        <div class="text-center text-lg-start">
            <h5 class="card-title mb-1 d-flex align-items-center gap-2 justify-content-center justify-content-lg-start"
                style="font-size: 1.2rem;">
                <i class="fa-solid fa-calendar-days" style="font-size: 1.5rem;"></i>
                Schedule Actions
            </h5>
            <p class="text-muted mb-0">Use the actions above to add, update, or import schedules.</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
            @if ($canAdd)
                <button type="button" class="btn btn-schedule-action" data-bs-toggle="modal"
                    data-bs-target="#scheduleCreateModal">
                    <i class="fa-solid fa-calendar-plus me-2"></i>Add Schedule
                </button>
            @endif
            {{-- @if ($canImport)
                <button type="button" class="btn btn-schedule-action" data-bs-toggle="modal" data-bs-target="#scheduleImportModal">
                    <i class="fa-solid fa-upload me-2"></i>Import
                </button>
            @endif --}}
        </div>
    </div>
</div>
