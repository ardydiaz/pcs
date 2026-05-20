@if ($canAdd)
    {{-- Create Course Modal --}}
    <div class="modal fade" id="courseCreateModal" tabindex="-1" aria-labelledby="courseCreateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="courseCreateForm">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="courseCreateModalLabel">Add Major Course</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="createClassCode">Class Code</label>
                            <input type="text" name="class_code" id="createClassCode" class="form-control"
                                placeholder="e.g. IT101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="createSubjectCode">Subject</label>
                            <input type="text" name="subject_code" id="createSubjectCode" class="form-control"
                                placeholder="e.g. Introduction to IT" required>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-course-primary evaluation-modal-btn"
                            data-default-text="Save" data-loading-text="Saving...">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
