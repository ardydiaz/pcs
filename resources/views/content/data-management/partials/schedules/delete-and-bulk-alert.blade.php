{{-- Delete and Bulk Delete Modals Partial --}}
@if ($canDelete)
    {{-- Delete Schedule Modal --}}
    <div class="modal fade" id="scheduleDeleteModal" tabindex="-1" aria-labelledby="scheduleDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <div class="modal-header schedule-modal-header">
                    <h5 class="modal-title mb-0" id="scheduleDeleteModalLabel">Delete Schedule</h5>
                    <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body schedule-modal-body">
                    <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="scheduleDeleteName">this schedule</span>? You can restore it later from Deleted Schedule.</p>
                </div>
                <div class="modal-footer schedule-modal-footer">
                    <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-schedule-danger schedule-modal-btn" id="confirmScheduleDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Modal --}}
    <div class="modal fade" id="scheduleBulkDeleteModal" tabindex="-1" aria-labelledby="scheduleBulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered schedule-modal-dialog schedule-modal-dialog--narrow">
            <div class="modal-content schedule-card">
                <div class="modal-header schedule-modal-header">
                    <h5 class="modal-title mb-0" id="scheduleBulkDeleteModalLabel">Delete Selected Schedules</h5>
                    <button type="button" class="schedule-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body schedule-modal-body">
                    <p class="mb-0">You are about to delete <span class="fw-semibold" id="scheduleBulkDeleteCount">0</span> schedule(s). You can restore them later from Deleted Schedule. Continue?</p>
                </div>
                <div class="modal-footer schedule-modal-footer">
                    <button type="button" class="btn btn-tertiary schedule-modal-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-schedule-danger schedule-modal-btn" id="confirmScheduleBulkDeleteBtn">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
@endif
