<!-- Course Actions Header Section -->
<div class="card evaluation-card evaluation-card--table mb-4 course-action-card">
    <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
        <div class="text-center text-lg-start">
            <h5 class="card-title mb-1 d-flex align-items-center gap-2 justify-content-center justify-content-lg-start"
                style="font-size: 1.2rem;">
                <i class="fa-solid fa-book" style="font-size: 1.5rem;"></i>
                Course Actions
            </h5>
            <p class="text-muted mb-0">Switch between managing courses or assignments, or import records from a file.</p>
        </div>
        <div class="course-tabs-panel">
            <div class="course-tabs-header">
                <div class="course-tabs-nav">
                    <button type="button" class="btn btn-tab active" data-course-section="courses">
                        Manage Course
                    </button>
                    <button type="button" class="btn btn-tab" data-course-section="assignments">
                        Assign Course
                    </button>
                </div>
                @if ($canImport)
                    <div class="course-tab-actions">
                        <button type="button" class="btn btn-course-action" data-bs-toggle="modal"
                            data-bs-target="#courseImportModal">
                            <i class="fa-solid fa-upload me-2"></i> Import
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
