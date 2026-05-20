<!-- Manage Courses Table Section -->
<div id="coursesSection" class="course-section">
    <div class="card evaluation-card evaluation-card--table mb-4" data-table-controller data-table-id="coursesTable">
        <div class="card-body border-0 evaluation-controls">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label for="coursesRowsPerPage" class="text-muted small">Lines per page</label>
                        <select id="coursesRowsPerPage" class="form-select evaluation-page-size fw-bold"
                            style="width: auto;" data-table-length>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">All</option>
                        </select>
                        @if ($canAdd)
                            <button type="button" class="btn btn-course-primary" data-bs-toggle="modal"
                                data-bs-target="#courseCreateModal">
                                <i class="fa-solid fa-circle-plus me-2"></i>Add Course
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        <div class="evaluation-search-wrapper">
                            <i class="bx bx-search evaluation-search-icon"></i>
                            <input type="text" id="coursesSearch" class="evaluation-search-input" data-table-search
                                placeholder="Search...">
                            <button type="button" class="evaluation-search-clear"
                                id="coursesSearchClear">&times;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 evaluation-table" id="coursesTable">
                    <thead>
                        <tr>
                            @if ($canDelete || $showDeleteDisabled)
                                <th>
                                    <input type="checkbox" class="form-check-input evaluation-checkbox"
                                        id="coursesSelectAll">
                                </th>
                            @endif
                            <th data-sort-key="code" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Class Code</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="subject" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Subject</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="subjecttype" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Subject Type</span>
                                    <span class="evaluation-sort-indicator">
                                        <i class="bx bx-chevron-up icon-up"></i>
                                        <i class="bx bx-chevron-down icon-down"></i>
                                    </span>
                                </span>
                            </th>
                            <th data-sort-key="assignments" class="sortable" data-sort-state="none">
                                <span class="evaluation-sort-wrapper">
                                    <span class="evaluation-sort-label">Assignments</span>
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
                    <tbody id="coursesTableBody"></tbody>
                </table>
            </div>

            @if ($canDelete || $showDeleteDisabled)
                <div class="evaluation-bulk-bar d-none" id="courseBulkBar">
                    <span class="fw-semibold" id="courseSelectedCount">0 Selected</span>
                    @if ($canDelete)
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger"
                            data-bulk-action="delete-course">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    @else
                        <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger disabled" disabled
                            aria-disabled="true">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    @endif
                    <button type="button" class="evaluation-bulk-close" data-bulk-action="clear-course"
                        title="Clear selection">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            @endif

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
