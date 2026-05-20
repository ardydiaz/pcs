<!-- Assign Course to Existing Faculty Section -->
<div id="assignmentsSection" class="course-section d-none">
    <div class="card evaluation-card evaluation-card--table" data-table-controller data-table-id="assignmentsTable">
        <div class="card-body border-0 evaluation-controls">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="assignmentsRowsPerPage" class="text-muted small">Lines per page</label>
                        <select id="assignmentsRowsPerPage" class="form-select evaluation-page-size fw-bold"
                            style="width: auto;" data-table-length>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                        <?php if($canAdd): ?>
                            <button type="button" class="btn btn-course-primary" data-bs-toggle="modal"
                                data-bs-target="#assignmentCreateModal">
                                <i class="fa-solid fa-user-gear me-2"></i>Assign Course
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                        <div class="dropdown table-filter-dropdown">
                            <button class="filter-toggle" type="button" id="assignmentFilterToggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span>Filters</span>
                                <i class="bx bx-filter"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3">
                                <div class="mb-3">
                                    <label class="form-label text-uppercase small">Faculty</label>
                                    <select id="assignmentFilterFaculty" class="form-select">
                                        <option value="all">All</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-uppercase small">Academic Year</label>
                                    <select id="assignmentFilterYear" class="form-select">
                                        <option value="all">All</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-uppercase small">Semester</label>
                                    <select id="assignmentFilterSemester" class="form-select">
                                        <option value="all">All</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-link p-0 table-filter-reset"
                                        id="assignmentFilterReset">Reset Filters</button>
                                </div>
                            </div>
                        </div>
                        <div class="evaluation-search-wrapper">
                            <i class="bx bx-search evaluation-search-icon"></i>
                            <input type="text" id="assignmentsSearch" class="evaluation-search-input"
                                data-table-search placeholder="Search...">
                            <button type="button" class="evaluation-search-clear"
                                id="assignmentsSearchClear">&times;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 evaluation-table" id="assignmentsTable">
                    <thead>
                        <tr>
                            <?php if($canDelete || $showDeleteDisabled): ?>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input evaluation-checkbox"
                                        id="assignmentsSelectAll">
                                </th>
                            <?php endif; ?>
                            <th data-sort-key="faculty" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Faculty</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="course" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Course</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>

                            <th>
                                Subject Type
                            </th>

                            <th data-sort-key="section" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Section</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="year" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Academic Year</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="semester" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Semester</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th>
                                <span class="evaluation-sort-label">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsTableBody"></tbody>
                </table>
            </div>

            <?php if($canDelete || $showDeleteDisabled): ?>
                <div class="evaluation-bulk-bar d-none" id="assignmentBulkBar">
                    <span class="fw-semibold" id="assignmentSelectedCount">0 Selected</span>
                    <?php if($canDelete): ?>
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger"
                            data-bulk-action="delete-assignment">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    <?php else: ?>
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger disabled"
                            disabled aria-disabled="true">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                    <button type="button" class="evaluation-bulk-close" data-bulk-action="clear-assignment"
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
                    <nav aria-label="Assignments pagination">
                        <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/assign-course-table.blade.php ENDPATH**/ ?>