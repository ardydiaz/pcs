<!-- Faculty Actions Card Header -->
<div class="card evaluation-card evaluation-card--table mb-4 mt-0 faculty-action-card">
    <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
        <div class="text-center text-lg-start">
            <h5 class="card-title mb-1 d-flex align-items-center gap-2 justify-content-center justify-content-lg-start"
                style="font-size: 1.2rem;">
                <i class="fa-solid fa-user-group" style="font-size: 1.5rem;"></i>
                Faculty Actions
            </h5>
            <p class="text-muted mb-0">Use the actions above to add, update, or import faculty records.</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
            @if ($canAdd)
                <button type="button" class="btn btn-faculty-action evaluation-modal-trigger" data-bs-toggle="modal"
                    data-bs-target="#facultyCreateModal">
                    <i class="fa-solid fa-user-plus me-2"></i> Add Faculty
                </button>
            @endif
            @if ($canImport)
                <button type="button" class="btn btn-faculty-action evaluation-modal-trigger" data-bs-toggle="modal"
                    data-bs-target="#facultyImportModal">
                    <i class="fa-solid fa-upload me-2"></i> Import Faculty
                </button>
            @endif
            @if ($canImportAll)
                <button type="button" class="btn btn-faculty-action evaluation-modal-trigger" data-bs-toggle="modal"
                    data-bs-target="#facultyImportAllModal">
                    <i class="fa-solid fa-file-import me-2"></i> Import Faculty and Schedule
                </button>
            @endif
        </div>
    </div>
</div>
