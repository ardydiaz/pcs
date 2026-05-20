<?php if($canImport): ?>
    
    <div class="modal fade" id="courseImportModal" tabindex="-1" aria-labelledby="courseImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form method="POST" action="<?php echo e(route('dm.courses.import')); ?>" enctype="multipart/form-data"
                    id="courseImportForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="courseImportModalLabel">Import Courses</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-0">
                            <label class="form-label" for="courseImportFile">Select File</label>
                            <input type="file" class="form-control" id="courseImportFile" name="file"
                                accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">Headers: classcode, subjectcode, section, employeeno,
                                academicyear, semester</small>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-course-primary evaluation-modal-btn"
                            data-default-text="Import" data-loading-text="Importing...">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/excel-import-modal.blade.php ENDPATH**/ ?>