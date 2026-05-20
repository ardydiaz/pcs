<?php if($canDelete): ?>
    
    <div class="modal fade" id="facultyDeleteModal" tabindex="-1" aria-labelledby="facultyDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <h5 class="modal-title mb-0" id="facultyDeleteModalLabel">Delete Faculty Member</h5>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="facultyDeleteName">this faculty member</span>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-delete-action evaluation-modal-btn" id="confirmFacultyDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/faculties/delete-alert-modal.blade.php ENDPATH**/ ?>