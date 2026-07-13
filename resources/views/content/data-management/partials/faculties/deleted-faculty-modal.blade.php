@if ($canDelete)
    <div class="modal fade" id="facultyDeletedModal" tabindex="-1" aria-labelledby="facultyDeletedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content evaluation-card">
                <div class="modal-header evaluation-modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="facultyDeletedModalLabel">Deleted Faculty</h5>
                        <p class="text-muted mb-0 small">Restore soft-deleted faculty records when needed.</p>
                    </div>
                    <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                        aria-label="Close">&times;</button>
                </div>
                <div class="modal-body evaluation-modal-body">
                    <div id="facultyDeletedAlert"></div>
                    <div id="facultyDeletedLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-3 mb-0">Loading deleted faculty...</p>
                    </div>
                    <div id="facultyDeletedEmpty" class="text-center py-5 d-none">
                        <i class="fa-solid fa-trash-arrow-up text-muted mb-3" style="font-size: 2rem;"></i>
                        <h5 class="mb-1">No Deleted Faculty</h5>
                        <p class="text-muted mb-0">Soft-deleted faculty records will appear here.</p>
                    </div>
                    <div id="facultyDeletedTableWrap" class="table-responsive d-none">
                        <table class="table align-middle mb-0 evaluation-table">
                            <thead>
                                <tr>
                                    <th>Faculty Name</th>
                                    <th>Employee No.</th>
                                    <th>Department</th>
                                    <th>Job Title</th>
                                    <th>Deleted At</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="facultyDeletedTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer evaluation-modal-footer">
                    <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
