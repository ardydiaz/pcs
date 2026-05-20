
<!-- Schedule table card -->
<div class="card schedule-card schedule-card--table" data-table-controller data-table-id="schedulesTable">
    <div class="card-body border-0 schedule-controls">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <label for="schedulesRowsPerPage" class="text-muted small">Lines per page</label>
                    <select id="schedulesRowsPerPage" class="form-select schedule-page-size fw-bold" style="width: auto;"
                        data-table-length>
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
                        <button class="filter-toggle" type="button" id="scheduleFilterToggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span>Filters</span>
                            <i class="bx bx-filter"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3">
                            <div class="mb-3">
                                <label class="form-label text-uppercase small">Time</label>
                                <select id="scheduleFilterTime" class="form-select">
                                    <option value="all">All</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-uppercase small">Day(s)</label>
                                <select id="scheduleFilterDay" class="form-select">
                                    <option value="all">All</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-link p-0 table-filter-reset"
                                    id="scheduleFilterReset">Reset Filters</button>
                            </div>
                        </div>
                    </div>
                    <div class="schedule-search-wrapper">
                        <i class="bx bx-search schedule-search-icon"></i>
                        <input type="text" id="schedulesSearch" class="schedule-search-input" data-table-search
                            placeholder="Search...">
                        <button type="button" class="schedule-search-clear" id="schedulesSearchClear">&times;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 schedule-table" id="schedulesTable">
                <thead>
                    <tr>
                        <?php if($canDelete || $showDeleteDisabled): ?>
                            <th class="text-center">
                                <input type="checkbox" class="form-check-input schedule-checkbox"
                                    id="scheduleSelectAll">
                            </th>
                        <?php endif; ?>
                        <th data-sort-key="course" class="sortable" data-sort-state="none">
                            <span class="d-inline-flex align-items-center">
                                Course
                                <span class="sort-indicator ms-1">
                                    <i class="bx bx-chevron-up icon-up"></i>
                                    <i class="bx bx-chevron-down icon-down"></i>
                                </span>
                            </span>
                        </th>
                        <th>Section</th>
                        <th>Time</th>
                        <th>Day(s)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="schedulesTableBody"></tbody>
            </table>
        </div>

        <?php if($canDelete || $showDeleteDisabled): ?>
            <div class="schedule-bulk-bar d-none" id="scheduleBulkBar">
                <span class="fw-semibold" id="scheduleSelectedCount">0 Selected</span>
                <?php if($canDelete): ?>
                    <button type="button" class="schedule-bulk-btn schedule-bulk-btn--danger"
                        data-bulk-action="delete">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                <?php else: ?>
                    <button type="button" class="schedule-bulk-btn schedule-bulk-btn--danger disabled" disabled
                        aria-disabled="true">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                <?php endif; ?>
                <button type="button" class="schedule-bulk-close" data-bulk-action="clear" title="Clear selection">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        <?php endif; ?>

        <div class="row mt-4 align-items-center">
            <div class="col-md-6 d-flex align-items-center">
                <div class="text-muted" data-table-info></div>
            </div>
            <div class="col-md-6 d-flex justify-content-end align-items-center">
                <nav aria-label="Schedules pagination">
                    <ul class="pagination justify-content-end mb-0" data-table-pagination></ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/schedules/schedule-table-items.blade.php ENDPATH**/ ?>