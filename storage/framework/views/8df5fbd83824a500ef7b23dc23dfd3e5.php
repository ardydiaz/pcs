<?php if($canDelete): ?>
    
    <div class="modal fade" id="facultyBulkDeleteModal" tabindex="-1" aria-labelledby="facultyBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="facultyBulkDeleteModalLabel">Delete Selected Faculty</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="facultyBulkDeleteCount">0</span> faculty record(s). This action cannot be undone. Continue?</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmFacultyBulkDeleteBtn">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/faculties/bulk-delete-modal.blade.php ENDPATH**/ ?>