<!-- Faculty Table Card -->
        <div class="card evaluation-card evaluation-card--table mt-0" data-table-controller data-table-id="facultyTable">
            <div class="card-body border-0 evaluation-controls">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="facultyRowsPerPage" class="text-muted small">Lines per page</label>
                            <select id="facultyRowsPerPage" class="form-select evaluation-page-size fw-bold" style="width: auto;" data-table-length>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            <div class="dropdown table-filter-dropdown">
                                <button class="filter-toggle" type="button" id="facultyFilterToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span>Filters</span>
                                    <i class="bx bx-filter"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3">
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Department</label>
                                        <select id="facultyFilterDepartment" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase small">Job Title</label>
                                        <select id="facultyFilterJob" class="form-select">
                                            <option value="all">All</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-link p-0 table-filter-reset" id="facultyFilterReset">Reset Filters</button>
                                    </div>
                                </div>
                            </div>
                            <div class="evaluation-search-wrapper">
                                <i class="bx bx-search evaluation-search-icon"></i>
                                <input type="text" id="facultySearch" class="evaluation-search-input" data-table-search placeholder="Search...">
                                <button type="button" class="evaluation-search-clear" id="facultySearchClear">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 evaluation-table" id="facultyTable">
                        <thead>
                            <tr>
                                @if ($canDelete)
                                    <th class="evaluation-col-selection text-center">
                                        <input type="checkbox" class="form-check-input evaluation-checkbox" id="facultySelectAll">
                                    </th>
                                @endif
                                <th data-sort-key="name" class="sortable" data-sort-state="none">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Faculty Name</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="employee" data-sort-state="none" class="sortable">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Employee No.</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="department" data-sort-state="none" class="sortable">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Department</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th data-sort-key="job" data-sort-state="none" class="sortable">
                                    <span class="evaluation-sort-wrapper">
                                        <span class="evaluation-sort-label">Job Title</span>
                                        <span class="evaluation-sort-indicator">
                                            <i class="bx bx-chevron-up icon-up"></i>
                                            <i class="bx bx-chevron-down icon-down"></i>
                                        </span>
                                    </span>
                                </th>
                                <th>
                                    <span class="evaluation-sort-label">Load</span>
                                </th>
                                <th>
                                    <span class="evaluation-sort-label">Actions</span>
                                </th>
                        </tr>
                    </thead>
                    <tbody id="facultyTableBody"></tbody>
                </table>
            </div>

            @if ($canDelete)
                <div class="evaluation-bulk-bar d-none" id="facultyBulkBar">
                    <span class="fw-semibold" id="facultySelectedCount">0 Selected</span>
                    <button type="button" class="evaluation-bulk-btn evaluation-bulk-btn--danger" data-bulk-action="delete">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                    <button type="button" class="evaluation-bulk-close" data-bulk-action="clear" title="Clear selection">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            @endif

            <div class="row mt-4 align-items-center">
                <div class="col-md-6 d-flex align-items-center">
                    <div class="text-muted" data-table-info></div>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-center">
                    <nav aria-label="Table pagination">
                        <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                    </nav>
                </div>
            </div>
        </div>
