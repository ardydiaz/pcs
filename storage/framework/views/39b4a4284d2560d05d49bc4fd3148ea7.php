<?php if($canDelete): ?>
    
    <div class="modal fade" id="assignmentDeleteModal" tabindex="-1" aria-labelledby="assignmentDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="assignmentDeleteModalLabel">Delete Assignment</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">Are you sure you want to remove <span class="fw-semibold" id="assignmentDeleteName">this assignment</span>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmAssignmentDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="assignmentBulkDeleteModal" tabindex="-1" aria-labelledby="assignmentBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="assignmentBulkDeleteModalLabel">Delete Selected Assignments</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="assignmentBulkDeleteCount">0</span> assignment(s). This action cannot be undone. Continue?</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmAssignmentBulkDeleteBtn">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/delete-assignment-alert.blade.php ENDPATH**/ ?>