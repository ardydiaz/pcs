@if ($canDelete)
    {{-- Delete Course Modal --}}
    <div class="modal fade" id="courseDeleteModal" tabindex="-1" aria-labelledby="courseDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="courseDeleteModalLabel">Delete Course</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="courseDeleteName">this course</span>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmCourseDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Courses Modal --}}
    <div class="modal fade" id="courseBulkDeleteModal" tabindex="-1" aria-labelledby="courseBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="courseBulkDeleteModalLabel">Delete Selected Courses</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="courseBulkDeleteCount">0</span> course(s). This action cannot be undone. Continue?</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmCourseBulkDeleteBtn">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
@endif
