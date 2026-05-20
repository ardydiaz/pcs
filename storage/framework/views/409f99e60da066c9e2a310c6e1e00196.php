<?php if($canEdit): ?>
    
    <div class="modal fade" id="courseEditModal" tabindex="-1" aria-labelledby="courseEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="courseEditForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="course_id" value="">
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="courseEditModalLabel">Edit Course</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editClassCode">Class Code</label>
                            <input type="text" name="class_code" id="editClassCode" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="editSubjectCode">Subject</label>
                            <input type="text" name="subject_code" id="editSubjectCode" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-course-primary evaluation-modal-btn" data-default-text="Save Changes" data-loading-text="Saving...">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/edit-course-form-modal.blade.php ENDPATH**/ ?>