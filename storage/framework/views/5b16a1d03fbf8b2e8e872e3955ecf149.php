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
    <?php echo $__env->make('components.table-controller-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.facultyManager = new FacultyManager();

            const controllerRoot = document.querySelector('[data-table-id="facultyTable"]');
            if (controllerRoot && window.TableController) {
                const updateBadge = (event) => {
                    const badge = document.getElementById('facultyCount');
                    if (!badge || !event.detail) {
                        return;
                    }

                    const total = window.facultyManager?.faculties?.length ?? event.detail.total ?? event.detail.filtered;
                    badge.textContent = `${event.detail.filtered} of ${total} Members`;
                };

                controllerRoot.addEventListener('table:updated', updateBadge);
                const controller = new TableController(controllerRoot);
                window.tableControllers.facultyTable = controller;

                if (controller.lastStats) {
                    updateBadge({ detail: controller.lastStats });
                }
            }
        });

        class FacultyManager {
            constructor() {
                this.faculties = <?php echo json_encode($faculties, 15, 512) ?>;
                this.users = <?php echo json_encode($users, 15, 512) ?>;
                this.editingId = null;

                this.setupEventListeners();
                this.renderTable();
            }

            setupEventListeners() {
                const addForm = document.getElementById('addFacultyForm');
                if (addForm) {
                    addForm.addEventListener('submit', (event) => {
                        event.preventDefault();
                        this.addFaculty();
                    });
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    console.warn('CSRF token not found. Ensure <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"> is present in your layout.');
                }
            }

            escapeHtml(value) {
                if (value === null || value === undefined) {
                    return '';
                }

                return String(value).replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                }[char] || char));
            }

            notifyTableController() {
                const controller = window.tableControllers?.facultyTable;
                if (controller) {
                    controller.refresh();
                }
            }

            updateCounts() {
                const badge = document.getElementById('facultyCount');
                if (badge) {
                    badge.textContent = this.faculties.length + ' Members';
                }
            }

            renderTable() {
                const tbody = document.getElementById('facultyTableBody');
                if (!tbody) {
                    return;
                }

                tbody.innerHTML = '';

                if (!this.faculties.length) {
                    tbody.innerHTML = `
                        <tr data-empty>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-search me-2"></i>No faculty members found.
                            </td>
                        </tr>
                    `;
                    this.updateCounts();
                    this.notifyTableController();
                    return;
                }

                const fragment = document.createDocumentFragment();

                this.faculties.forEach((faculty) => {
                    const row = document.createElement('tr');
                    row.dataset.id = faculty.id;
                    row.className = 'fade-in';
                    row.dataset.search = [
                        faculty.user?.name || '',
                        faculty.employee_no || '',
                        faculty.department || '',
                        faculty.position || '',
                        faculty.hire_date || '',
                    ].join(' ').toLowerCase();

                    if (this.editingId === faculty.id) {
                        row.innerHTML = this.getEditRowHTML(faculty);
                    } else {
                        row.innerHTML = this.getViewRowHTML(faculty);
                    }

                    fragment.appendChild(row);
                });

                tbody.appendChild(fragment);
                this.attachRowEventListeners();
                this.updateCounts();
                this.notifyTableController();
            }

            getViewRowHTML(faculty) {
                const userName = faculty.user?.name || 'N/A';
                const employeeNo = faculty.employee_no || 'N/A';
                const department = faculty.department || 'N/A';
                const position = faculty.position || 'N/A';
                const hireDate = faculty.hire_date ? new Date(faculty.hire_date).toLocaleDateString() : 'N/A';
                const initials = userName ? userName.charAt(0).toUpperCase() : 'F';

                return `
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <div class="avatar-initial bg-primary rounded-circle">
                                    ${this.escapeHtml(initials)}
                                </div>
                            </div>
                            <span class="fw-medium">${this.escapeHtml(userName)}</span>
                        </div>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-secondary">${this.escapeHtml(employeeNo)}</span>
                    </td>
                    <td class="align-middle">${this.escapeHtml(department)}</td>
                    <td class="align-middle">${this.escapeHtml(position)}</td>
                    <td class="align-middle">${this.escapeHtml(hireDate)}</td>
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
                const userOptions = this.users.map((user) => `
                    <option value="${user.id}" ${Number(user.id) === Number(faculty.user_id) ? 'selected' : ''}>
                        ${this.escapeHtml(user.name)}
                    </option>
                `).join('');

                return `
                    <td class="align-middle">
                        <select name="user_id" class="form-select form-select-sm">
                            ${userOptions}
                        </select>
                    </td>
                    <td class="align-middle">
                        <input type="text" name="employee_no" class="form-control form-control-sm" value="${this.escapeHtml(faculty.employee_no || '')}">
                    </td>
                    <td class="align-middle">
                        <input type="text" name="department" class="form-control form-control-sm" value="${this.escapeHtml(faculty.department || '')}">
                    </td>
                    <td class="align-middle">
                        <input type="text" name="position" class="form-control form-control-sm" value="${this.escapeHtml(faculty.position || '')}">
                    </td>
                    <td class="align-middle">
                        <input type="date" name="hire_date" class="form-control form-control-sm" value="${this.escapeHtml(faculty.hire_date || '')}">
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
                document.querySelectorAll('.edit-btn').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        const id = Number(event.currentTarget.dataset.id);
                        this.startEdit(id);
                    });
                });

                document.querySelectorAll('.delete-btn').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        const id = Number(event.currentTarget.dataset.id);
                        this.deleteFaculty(id);
                    });
                });

                document.querySelectorAll('.save-btn').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        const id = Number(event.currentTarget.dataset.id);
                        this.updateFaculty(id);
                    });
                });

                document.querySelectorAll('.cancel-btn').forEach((button) => {
                    button.addEventListener('click', () => {
                        this.cancelEdit();
                    });
                });
            }

            startEdit(facultyId) {
                this.editingId = facultyId;
                this.renderTable();
            }

            cancelEdit() {
                this.editingId = null;
                this.renderTable();
            }

            async addFaculty() {
                const form = document.getElementById('addFacultyForm');
                if (!form) {
                    return;
                }

                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                if (!this.validateForm(data)) {
                    return;
                }

                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.store")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.faculties.push(result.data);
                        this.editingId = null;
                        this.renderTable();
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

            async updateFaculty(facultyId) {
                const row = document.querySelector(`tr[data-id="${facultyId}"]`);
                if (!row) {
                    return;
                }

                const inputs = row.querySelectorAll('input, select');
                const data = {};

                inputs.forEach((input) => {
                    data[input.name] = input.value;
                });

                if (!this.validateForm(data)) {
                    return;
                }

                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.update", ":id")); ?>'.replace(':id', facultyId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        const index = this.faculties.findIndex((faculty) => faculty.id === facultyId);
                        if (index !== -1) {
                            this.faculties[index] = result.data;
                        }
                        this.editingId = null;
                        this.renderTable();
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
                if (!confirm('Are you sure you want to delete this faculty member?')) {
                    return;
                }

                this.setLoading(true);

                try {
                    const response = await fetch('<?php echo e(route("dm.faculties.destroy", ":id")); ?>'.replace(':id', facultyId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        this.faculties = this.faculties.filter((faculty) => faculty.id !== facultyId);
                        this.editingId = null;
                        this.renderTable();
                        this.showAlert('success', result.message);
                    } else {
                        throw new Error(result.message || 'Failed to delete faculty');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'An error occurred');
                }

                this.setLoading(false);
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
                if (!alertContainer) {
                    return;
                }

                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                alertContainer.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fas ${iconClass} me-2"></i> ${this.escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                setTimeout(() => {
                    const alert = alertContainer.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }

            setLoading(loading) {
                const submitBtn = document.getElementById('submitBtn');
                if (!submitBtn) {
                    return;
                }

                if (loading) {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
                    submitBtn.disabled = true;
                } else {
                    submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Faculty';
                    submitBtn.disabled = false;
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
            <div class="card" data-table-controller data-table-id="facultyTable">
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
                                <label for="facultyRowsPerPage" class="form-label me-2 mb-0">Show:</label>
                                <select id="facultyRowsPerPage" class="form-select form-select-sm" style="width: auto;" data-table-length>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">All</option>
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
                                    <input type="text" id="facultySearch" class="form-control" data-table-search
                                        placeholder="Search faculty members...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 bg-white" id="facultyTable">
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
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3">
                        <div class="pagination-info" data-table-info>
                            Showing 0 to 0 of 0 entries
                        </div>
                        <ul class="pagination pagination-sm mb-0 mt-3 mt-md-0" data-table-pagination></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\post-class-survey\resources\views/content/data-management/dm-faculties.blade.php ENDPATH**/ ?>