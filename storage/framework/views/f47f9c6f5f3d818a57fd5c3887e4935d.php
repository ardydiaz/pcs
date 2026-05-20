

<?php $__env->startSection('title', 'Data Management - Faculties'); ?>

<?php $__env->startSection('page-style'); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-1px);
            color: white;
        }

        .alert {
            border: none;
            border-radius: 10px;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        .table-controls {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .search-highlight {
            background-color: #fff3cd;
            padding: 0.1rem 0.2rem;
            border-radius: 0.25rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.facultyManager = new FacultyManager();
        });

        class FacultyManager {
            constructor() {
                this.faculties = <?php echo json_encode($faculties, 15, 512) ?>;
                this.filteredFaculties = [...this.faculties];
                this.editingId = null;
                this.currentPage = 1;
                this.rowsPerPage = 10;
                this.searchTerm = '';
                this.setupEventListeners();
                this.renderTable();
                this.updatePaginationInfo();
            }

            setupEventListeners() {
                // Add faculty form
                document.getElementById('addFacultyForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.addFaculty();
                });

                // Search input
                document.getElementById('searchInput').addEventListener('input', (e) => {
                    this.searchTerm = e.target.value.toLowerCase();
                    this.currentPage = 1;
                    this.filterFaculties();
                    this.renderTable();
                    this.updatePaginationInfo();
                });

                // Rows per page selector
                document.getElementById('rowsPerPage').addEventListener('change', (e) => {
                    this.rowsPerPage = parseInt(e.target.value);
                    this.currentPage = 1;
                    this.renderTable();
                    this.updatePaginationInfo();
                });

                // Pagination buttons
                document.getElementById('prevBtn').addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.renderTable();
                        this.updatePaginationInfo();
                    }
                });

                document.getElementById('nextBtn').addEventListener('click', () => {
                    const totalPages = Math.ceil(this.filteredFaculties.length / this.rowsPerPage);
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.renderTable();
                        this.updatePaginationInfo();
                    }
                });

                // Setup CSRF token for AJAX requests
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                if (!token) {
                    console.warn('CSRF token not found. Make sure to add <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"> to your layout.');
                }
            }

            filterFaculties() {
                if (!this.searchTerm) {
                    this.filteredFaculties = [...this.faculties];
                    return;
                }

                this.filteredFaculties = this.faculties.filter(faculty => {
                    const searchFields = [
                        faculty.user?.name || '',
                        faculty.employee_no || '',
                        faculty.department || '',
                        faculty.position || ''
                    ];

                    return searchFields.some(field =>
                        field.toLowerCase().includes(this.searchTerm)
                    );
                });
            }

            highlightText(text, searchTerm) {
                if (!searchTerm || !text) return text;

                const regex = new RegExp(`(${searchTerm})`, 'gi');
                return text.replace(regex, '<span class="search-highlight">$1</span>');
            }

            getPaginatedData() {
                const startIndex = (this.currentPage - 1) * this.rowsPerPage;
                const endIndex = startIndex + this.rowsPerPage;
                return this.filteredFaculties.slice(startIndex, endIndex);
            }

            updatePaginationInfo() {
                const totalItems = this.filteredFaculties.length;
                const totalPages = Math.ceil(totalItems / this.rowsPerPage);
                const startItem = totalItems === 0 ? 0 : (this.currentPage - 1) * this.rowsPerPage + 1;
                const endItem = Math.min(this.currentPage * this.rowsPerPage, totalItems);

                document.getElementById('paginationInfo').textContent =
                    `Showing ${startItem} to ${endItem} of ${totalItems} entries`;

                document.getElementById('currentPage').textContent = this.currentPage;
                document.getElementById('totalPages').textContent = totalPages;

                document.getElementById('prevBtn').disabled = this.currentPage === 1;
                document.getElementById('nextBtn').disabled = this.currentPage === totalPages || totalPages === 0;

                // Update faculty count badge
                document.getElementById('facultyCount').textContent = `${totalItems} Members`;
            }

            async addFaculty() {
                const form = document.getElementById('addFacultyForm');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                if (!this.validateForm(data)) return;
                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.store")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.faculties.push(result.data);
                        this.filterFaculties();
                        this.renderTable();
                        this.updatePaginationInfo();
                        form.reset();
                        this.showAlert('success', result.message);
                    } else {
                        throw new Error(result.message || 'Failed to add faculty');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'An error occurred');
                }

                this.setLoading(false);
            }

            startEdit(facultyId) {
                this.editingId = facultyId;
                this.renderTable();
            }

            cancelEdit() {
                this.editingId = null;
                this.renderTable();
            }

            async updateFaculty(facultyId) {
                const row = document.querySelector(`tr[data-id="${facultyId}"]`);
                const inputs = row.querySelectorAll('input, select');
                const data = {};

                inputs.forEach(input => {
                    data[input.name] = input.value;
                });

                if (!this.validateForm(data)) return;
                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.update", ":id")); ?>'.replace(':id', facultyId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        const index = this.faculties.findIndex(f => f.id === facultyId);
                        this.faculties[index] = result.data;
                        this.editingId = null;
                        this.filterFaculties();
                        this.renderTable();
                        this.updatePaginationInfo();
                        this.showAlert('success', result.message);
                    } else {
                        throw new Error(result.message || 'Failed to update faculty');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'An error occurred');
                }

                this.setLoading(false);
            }

            async deleteFaculty(facultyId) {
                if (!confirm('Are you sure you want to delete this faculty member?')) return;
                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.destroy", ":id")); ?>'.replace(':id', facultyId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.faculties = this.faculties.filter(f => f.id !== facultyId);
                        this.filterFaculties();

                        // Adjust current page if necessary
                        const totalPages = Math.ceil(this.filteredFaculties.length / this.rowsPerPage);
                        if (this.currentPage > totalPages && totalPages > 0) {
                            this.currentPage = totalPages;
                        }

                        this.renderTable();
                        this.updatePaginationInfo();
                        this.showAlert('success', result.message);
                    } else {
                        throw new Error(result.message || 'Failed to delete faculty');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'An error occurred');
                }

                this.setLoading(false);
            }

            renderTable() {
                const tbody = document.getElementById('facultyTableBody');
                const paginatedData = this.getPaginatedData();
                tbody.innerHTML = '';

                if (paginatedData.length === 0) {
                    tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-search me-2"></i>
                                        ${this.searchTerm ? 'No faculty members found matching your search.' : 'No faculty members found.'}
                                    </td>
                                </tr>
                            `;
                    return;
                }

                paginatedData.forEach(faculty => {
                    const row = document.createElement('tr');
                    row.dataset.id = faculty.id;
                    row.className = 'fade-in';

                    if (this.editingId === faculty.id) {
                        row.innerHTML = this.getEditRowHTML(faculty);
                    } else {
                        row.innerHTML = this.getViewRowHTML(faculty);
                    }

                    tbody.appendChild(row);
                });

                // Attach event listeners to buttons
                this.attachRowEventListeners();
            }

            getViewRowHTML(faculty) {
                const userName = faculty.user?.name || 'N/A';
                const employeeNo = faculty.employee_no || 'N/A';
                const department = faculty.department || 'N/A';
                const position = faculty.position || 'N/A';

                return `
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-primary rounded-circle">
                                            ${userName.charAt(0).toUpperCase()}
                                        </div>
                                    </div>
                                    <span class="fw-medium">${this.highlightText(userName, this.searchTerm)}</span>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-secondary">${this.highlightText(employeeNo, this.searchTerm)}</span>
                            </td>
                            <td class="align-middle">${this.highlightText(department, this.searchTerm)}</td>
                            <td class="align-middle">${this.highlightText(position, this.searchTerm)}</td>
                            <td class="align-middle">${faculty.hire_date ? new Date(faculty.hire_date).toLocaleDateString() : 'N/A'}</td>
                            <td class="align-middle">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm edit-btn" data-id="${faculty.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn" data-id="${faculty.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        `;
            }

            getEditRowHTML(faculty) {
                const userOptions = <?php echo json_encode($users, 15, 512) ?>.map(user => `
                            <option value="${user.id}" ${user.id == faculty.user_id ? 'selected' : ''}>
                                ${user.name}
                            </option>
                        `).join('');

                return `
                            <td class="align-middle">
                                <select name="user_id" class="form-select form-select-sm">
                                    ${userOptions}
                                </select>
                            </td>
                            <td class="align-middle">
                                <input type="text" name="employee_no" class="form-control form-control-sm" value="${faculty.employee_no}">
                            </td>
                            <td class="align-middle">
                                <input type="text" name="department" class="form-control form-control-sm" value="${faculty.department}">
                            </td>
                            <td class="align-middle">
                                <input type="text" name="position" class="form-control form-control-sm" value="${faculty.position}">
                            </td>
                            <td class="align-middle">
                                <input type="date" name="hire_date" class="form-control form-control-sm" value="${faculty.hire_date}">
                            </td>
                            <td class="align-middle">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm save-btn" data-id="${faculty.id}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm cancel-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        `;
            }

            attachRowEventListeners() {
                // Edit buttons
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const id = parseInt(e.currentTarget.dataset.id);
                        this.startEdit(id);
                    });
                });

                // Delete buttons
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const id = parseInt(e.currentTarget.dataset.id);
                        this.deleteFaculty(id);
                    });
                });

                // Save buttons
                document.querySelectorAll('.save-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const id = parseInt(e.currentTarget.dataset.id);
                        this.updateFaculty(id);
                    });
                });

                // Cancel buttons
                document.querySelectorAll('.cancel-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.cancelEdit();
                    });
                });
            }

            validateForm(data) {
                if (!data.user_id || !data.employee_no || !data.department || !data.position || !data.hire_date) {
                    this.showAlert('error', 'Please fill in all fields');
                    return false;
                }
                return true;
            }

            showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                alertContainer.innerHTML = `
                            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                <i class="fas ${iconClass} me-2"></i> ${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    const alert = alertContainer.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }

            setLoading(loading) {
                const submitBtn = document.getElementById('submitBtn');
                if (loading) {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
                    submitBtn.disabled = true;
                    document.body.classList.add('loading');
                } else {
                    submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Faculty';
                    submitBtn.disabled = false;
                    document.body.classList.remove('loading');
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">My Bootstrap Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form method="POST" action="<?php echo e(route('faculties.import')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="mt-2">
                            <label for="file">Choose file</label>
                            <input type="file" name="file" class="form-control">
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="mb-4"></div>

    <!-- Add Faculty Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2 text-primary"></i>Add New Faculty Member
                    </h5>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                        Import
                    </button>
                </div>
                <div class="card-body">
                    <form id="addFacultyForm">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-2">
                                <label class="form-label fw-medium">
                                    <i class="fas fa-user me-1"></i>User
                                </label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select User</option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-2">
                                <label class="form-label fw-medium">
                                    <i class="fas fa-id-badge me-1"></i>Employee No.
                                </label>
                                <input type="text" name="employee_no" class="form-control" placeholder="EMP001" required>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium">
                                    <i class="fas fa-building me-1"></i>Department
                                </label>
                                <input type="text" name="department" class="form-control" placeholder="Computer Science"
                                    required>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-medium">
                                    <i class="fas fa-briefcase me-1"></i>Position
                                </label>
                                <input type="text" name="position" class="form-control" placeholder="Professor" required>
                            </div>

                            <div class="col-md-6 col-lg-2 justify-content-end d-flex align-items-end">
                                <button type="submit" id="submitBtn" class="btn btn-gradient w-100">
                                    <i class="fas fa-plus me-2"></i>Add Faculty
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Faculty Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2 text-primary"></i>Faculty Members
                    </h5>
                    <span class="badge bg-primary" id="facultyCount"><?php echo e(count($faculties)); ?> Members</span>
                </div>

                <!-- Table Controls -->
                <div class="table-controls mx-3 mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label for="rowsPerPage" class="form-label me-2 mb-0">Show:</label>
                                <select id="rowsPerPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ms-2 text-muted">entries</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="input-group" style="max-width: 300px;">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Search faculty members...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 bg-white">
                            <thead class="bg-light">
                                <tr>
                                    <th>User</th>
                                    <th>Employee No.</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Hire Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="facultyTableBody">
                                <!-- Table rows will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div class="pagination-info" id="paginationInfo">
                            Showing 1 to 10 of <?php echo e(count($faculties)); ?> entries
                        </div>
                        <div class="d-flex align-items-center">
                            <button id="prevBtn" class="btn btn-outline-secondary btn-sm me-2" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <span class="mx-3">
                                Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                            </span>
                            <button id="nextBtn" class="btn btn-outline-secondary btn-sm ms-2">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/content/data-management/dm-faculties.blade.php ENDPATH**/ ?>