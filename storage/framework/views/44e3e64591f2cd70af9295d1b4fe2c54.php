<!-- Manage Courses Table Section -->
<div id="coursesSection" class="course-section">
    <div class="card evaluation-card evaluation-card--table mb-4" data-table-controller data-table-id="coursesTable">
        <div class="card-body pt-0">
            <div class="minors">
                <table class="table table-responsive table-hover" id="minorscoursesTable">
                    <thead>
                        <tr>
                            <th>Class Code</th>
                            <th>Subject</th>
                            <th>Subject type</th>
                            <th>Assignments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                        <tr>
                            <td>sdsadasd</td>
                            <td>eee</td>
                            <td>555</td>
                            <td>333</td>
                            <td>888</td>
                        </tr>

                    </tbody>
                    </tbody>
                </table>
            </div>

            <?php if($canDelete || $showDeleteDisabled): ?>
                <div class="evaluation-bulk-bar d-none" id="courseBulkBar">
                    <span class="fw-semibold" id="courseSelectedCount">0 Selected</span>
                    <?php if($canDelete): ?>
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger"
                            data-bulk-action="delete-course">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    <?php else: ?>
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger disabled" disabled
                            aria-disabled="true">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                    <button type="button" class="evaluation-bulk-close" data-bulk-action="clear-course"
                        title="Clear selection">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row mt-4 align-items-center">
                <div class="col-md-6 d-flex align-items-center">
                    <div class="text-muted" data-table-info></div>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-center">
                    <nav aria-label="Courses pagination">
                        <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>


<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/manage-minor-courses-table.blade.php ENDPATH**/ ?>