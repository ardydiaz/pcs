
<?php if($canDelete): ?>
    
    <div class="modal fade" id="scheduleDeleteModal" tabindex="-1" aria-labelledby="scheduleDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <div class="modal-header schedule-modal-header">
                    <h5 class="modal-title mb-0" id="scheduleDeleteModalLabel">Delete Schedule</h5>
                    <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body schedule-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="scheduleDeleteName">this schedule</span>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer schedule-modal-footer">
                    <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-schedule-danger schedule-modal-btn" id="confirmScheduleDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="scheduleBulkDeleteModal" tabindex="-1" aria-labelledby="scheduleBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <div class="modal-header schedule-modal-header">
                    <h5 class="modal-title mb-0" id="scheduleBulkDeleteModalLabel">Delete Selected Schedules</h5>
                    <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body schedule-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="scheduleBulkDeleteCount">0</span> schedule(s). This action cannot be undone. Continue?</p>
                </div>
                <div class="modal-footer schedule-modal-footer">
                    <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-schedule-danger schedule-modal-btn" id="confirmScheduleBulkDeleteBtn">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/schedules/delete-and-bulk-alert.blade.php ENDPATH**/ ?>