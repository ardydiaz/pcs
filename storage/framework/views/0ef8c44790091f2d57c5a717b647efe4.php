

<?php $__env->startSection('title', 'Data Management - Evaluation'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container py-4">
        
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-plus me-2"></i>Generate Evaluation Form
                </h5>
                <form method="POST" action="<?php echo e(route('dm.evaluation.generateAll')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="academic_year" id="all_academic_year" value="2025-2026">
                    <input type="hidden" name="semester" id="all_semester" value="1st">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bx bx-layer-plus me-1"></i>Generate All
                    </button>
                </form>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('dm.evaluation.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        
                        <div class="col-md-4">
                            <label for="faculty_id" class="form-label">Faculty Member</label>
                            <select name="faculty_id" id="faculty_id" class="form-select" required>
                                <option value="">-- Select Faculty --</option>
                                <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($faculty->id); ?>"><?php echo e($faculty->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="col-md-3">
                            <label for="academic_year" class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" id="academic_year" class="form-control"
                                placeholder="2024-2025" required>
                        </div>

                        
                        <div class="col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-magic-wand me-1"></i>Generate
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-list-ul me-2"></i>Evaluation Forms
                </h5>
            </div>

            
            <div class="card-body border-bottom">
                <div class="row align-items-center">
                    
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Search by faculty name, academic year, or semester...">
                        </div>
                    </div>

                    
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center">
                            <label for="rowsPerPage" class="form-label me-2 mb-0">Show:</label>
                            <select id="rowsPerPage" class="form-select" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                            <span class="ms-2 text-muted">entries</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0 align-middle" id="evaluationTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%;">Faculty Name</th>
                                <th style="width: 10%;">Academic Year</th>
                                <th style="width: 8%;">Semester</th>
                                <th style="width: 22%;">Form Link</th>
                                <th style="width: 15%;">QR Code</th>
                                <th style="width: 7%;">Status</th>
                                <th style="width: 5%;">Responses</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php $__empty_1 = true; $__currentLoopData = $evaluations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evaluation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="table-row">
                                    <td class="text-truncate">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <div class="avatar-initial bg-primary rounded-circle">
                                                    <?php echo e(substr($evaluation->faculty->name, 0, 1)); ?>

                                                </div>
                                            </div>
                                            <span class="text-truncate faculty-name" style="max-width: 120px;">
                                                <?php echo e($evaluation->faculty->name); ?>

                                            </span>
                                        </div>
                                    </td>
                                    <td class="academic-year"><?php echo e($evaluation->academic_year); ?></td>
                                    <td class="semester">
                                        <span class="badge bg-info"><?php echo e($evaluation->semester); ?></span>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm text-truncate"
                                                value="<?php echo e($evaluation->form_link); ?>" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                                onclick="copyToClipboard('<?php echo e($evaluation->form_link); ?>')">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="showQrModal(<?php echo e($evaluation->id); ?>, '<?php echo e($evaluation->faculty->name); ?>', '<?php echo e($evaluation->academic_year); ?>', '<?php echo e($evaluation->semester); ?>')">
                                                <i class="bx bx-qr"></i>
                                            </button>
                                            <a href="<?php echo e(route('dm.evaluation.qr.download', $evaluation)); ?>"
                                                class="btn btn-outline-success btn-sm">
                                                <i class="bx bx-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($evaluation->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                                            <?php echo e($evaluation->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo e($evaluation->responses->count()); ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <form method="POST"
                                                        action="<?php echo e(route('dm.evaluation.toggle', $evaluation)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PATCH'); ?>
                                                        <button type="submit" class="dropdown-item">
                                                            <i
                                                                class="bx bx-toggle-<?php echo e($evaluation->is_active ? 'left' : 'right'); ?> me-2"></i>
                                                            <?php echo e($evaluation->is_active ? 'Deactivate' : 'Activate'); ?>

                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?php echo e(route('dm.evaluation.responses', $evaluation)); ?>">
                                                        <i class="bx bx-bar-chart me-2"></i>View Responses
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form method="POST"
                                                        action="<?php echo e(route('dm.evaluation.destroy', $evaluation)); ?>"
                                                        onsubmit="return confirm('Are you sure?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bx bx-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr id="emptyRow">
                                    <td colspan="8" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bx bx-file-blank display-4 text-muted mb-3"></i>
                                            <h5 class="mb-2">No evaluation forms found</h5>
                                            <p class="text-muted mb-0">Generate your first evaluation form using the form above.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="row mt-3 align-items-center">
                    <div class="col-md-6">
                        <div id="tableInfo" class="text-muted"></div>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Table pagination">
                            <ul class="pagination pagination-sm justify-content-end mb-0" id="pagination">
                                <!-- Pagination will be generated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="bx bx-qr me-2"></i>Evaluation QR Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <h6 id="qrFacultyName" class="text-primary mb-1"></h6>
                        <small id="qrDetails" class="text-muted"></small>
                    </div>
                    <div class="qr-container mb-3">
                        <div id="qrCodeContainer"></div>
                        <div id="qrLoader" class="d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading QR Code...</span>
                            </div>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">
                        <i class="bx bx-info-circle me-1"></i>
                        Students can scan this QR code to access the evaluation form
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadQrBtn" href="" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i>Download QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Table functionality
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredRows = [];
        let allRows = [];

        document.addEventListener('DOMContentLoaded', function () {
            initializeTable();
        });

        function initializeTable() {
            allRows = Array.from(document.querySelectorAll('.table-row'));
            filteredRows = [...allRows];

            // Initialize search
            document.getElementById('searchInput').addEventListener('input', handleSearch);

            // Initialize rows per page
            document.getElementById('rowsPerPage').addEventListener('change', handleRowsPerPageChange);

            updateTable();
        }

        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            if (searchTerm === '') {
                filteredRows = [...allRows];
            } else {
                filteredRows = allRows.filter(row => {
                    const facultyName = row.querySelector('.faculty-name').textContent.toLowerCase();
                    const academicYear = row.querySelector('.academic-year').textContent.toLowerCase();
                    const semester = row.querySelector('.semester').textContent.toLowerCase();

                    return facultyName.includes(searchTerm) ||
                        academicYear.includes(searchTerm) ||
                        semester.includes(searchTerm);
                });
            }

            currentPage = 1;
            updateTable();
        }

        function handleRowsPerPageChange() {
            const selected = document.getElementById('rowsPerPage').value;
            rowsPerPage = selected === 'all' ? filteredRows.length : parseInt(selected);
            currentPage = 1;
            updateTable();
        }

        function updateTable() {
            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');

            // Show filtered and paginated rows
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = rowsPerPage === filteredRows.length ? filteredRows.length : startIndex + rowsPerPage;

            for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
                filteredRows[i].style.display = '';
            }

            // Handle empty state
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) {
                emptyRow.style.display = filteredRows.length === 0 ? '' : 'none';
            }

            updatePagination();
            updateInfo();
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const pagination = document.getElementById('pagination');

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = '';

            // Previous button
            paginationHTML += `
                        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
                                <i class="bx bx-chevron-left"></i>
                            </a>
                        </li>
                    `;

            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>`;
                if (startPage > 2) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                            </li>
                        `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a></li>`;
            }

            // Next button
            paginationHTML += `
                        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
                                <i class="bx bx-chevron-right"></i>
                            </a>
                        </li>
                    `;

            pagination.innerHTML = paginationHTML;
        }

        function updateInfo() {
            const totalEntries = filteredRows.length;
            const startEntry = totalEntries === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
            const endEntry = Math.min(currentPage * rowsPerPage, totalEntries);

            document.getElementById('tableInfo').textContent =
                `Showing ${startEntry} to ${endEntry} of ${totalEntries} entries`;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                updateTable();
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = '<i class="bx bx-check me-2"></i>Link copied to clipboard!';
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        function showQrModal(evaluationId, facultyName, academicYear, semester) {
            document.getElementById('qrFacultyName').textContent = facultyName;
            document.getElementById('qrDetails').textContent = `${academicYear} - ${semester} Semester`;

            const qrContainer = document.getElementById('qrCodeContainer');
            qrContainer.innerHTML = '<div class="spinner-border text-primary"></div>';

            fetch(`/data-management/evaluation/${evaluationId}/qr`)
                .then(res => res.text())
                .then(dataUrl => {
                    document.getElementById('qrCodeContainer').innerHTML = `<img src="${dataUrl}" class="img-fluid" alt="QR Code">`;
                })
                .catch(() => {
                    qrContainer.innerHTML = '<p class="text-danger">QR Code generation failed. Please try downloading instead.</p>';
                });

            document.getElementById('downloadQrBtn').href = `/data-management/evaluation/${evaluationId}/qr/download`;
            new bootstrap.Modal(document.getElementById('qrModal')).show();
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/content/data-management/dm-evaluation.blade.php ENDPATH**/ ?>