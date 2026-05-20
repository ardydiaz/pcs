

<?php $__env->startSection('title', 'Data Management - Courses'); ?>

<?php $__env->startSection('page-style'); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .gradient-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .gradient-courses {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .gradient-assignments {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-1px);
            color: white;
        }

        .btn-gradient-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
            color: white;
        }

        .btn-gradient-success:hover {
            background: linear-gradient(135deg, #4a9928 0%, #98d7c2 100%);
            color: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .badge-custom {
            font-size: 0.75em;
            padding: 0.375rem 0.75rem;
        }

        .semester-badge {
            font-size: 0.7em;
            padding: 0.25rem 0.5rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        window.courseManager = new CourseManager();
    });

    class CourseManager {
        constructor() {
            this.courses = <?php echo json_encode($courses, 15, 512) ?>;
            this.faculties = <?php echo json_encode($faculties, 15, 512) ?>;
            this.facultyCourses = <?php echo json_encode($facultyCourses, 15, 512) ?>;
            this.editingCourseId = null;
            this.editingAssignmentId = null;
            this.activeTab = 'courses';
            this.setupEventListeners();
            this.renderTables();
        }

        setupEventListeners() {
            // Course form
            document.getElementById('addCourseForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.addCourse();
            });

            // Faculty course assignment form
            document.getElementById('addAssignmentForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.addFacultyCourse();
            });

            // Tab switching
            document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', (e) => {
                    this.activeTab = e.target.getAttribute('data-bs-target').replace('#', '');
                });
            });
        }

        async addCourse() {
            const form = document.getElementById('addCourseForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            if (!this.validateCourseForm(data)) return;

            this.setLoading('course', true);

            try {
                const response = await fetch('<?php echo e(route("dm.courses.store")); ?>', {
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
                    this.courses.push(result.data);
                    this.renderTables();
                    form.reset();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to add course');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('course', false);
        }

        async addFacultyCourse() {
            const form = document.getElementById('addAssignmentForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            if (!this.validateAssignmentForm(data)) return;

            this.setLoading('assignment', true);

            try {
                const response = await fetch('<?php echo e(route("dm.faculty-courses.store")); ?>', {
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
                    this.facultyCourses.push(result.data);
                    this.renderTables();
                    form.reset();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to assign course');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('assignment', false);
        }

        async updateCourse(courseId) {
            const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
            const inputs = row.querySelectorAll('input, textarea');
            const data = {};

            inputs.forEach(input => {
                data[input.name] = input.value;
            });

            if (!this.validateCourseForm(data)) return;

            this.setLoading('course', true);

            try {
                const response = await fetch('<?php echo e(route("dm.courses.update", ":id")); ?>'.replace(':id', courseId), {
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
                    const index = this.courses.findIndex(c => c.id === courseId);
                    this.courses[index] = result.data;
                    this.editingCourseId = null;
                    this.renderTables();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to update course');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('course', false);
        }

        async updateFacultyCourse(assignmentId) {
            const row = document.querySelector(`tr[data-assignment-id="${assignmentId}"]`);
            const inputs = row.querySelectorAll('input, select');
            const data = {};

            inputs.forEach(input => {
                data[input.name] = input.value;
            });

            if (!this.validateAssignmentForm(data)) return;

            this.setLoading('assignment', true);

            try {
                const response = await fetch('<?php echo e(route("dm.faculty-courses.update", ":id")); ?>'.replace(':id', assignmentId), {
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
                    const index = this.facultyCourses.findIndex(fc => fc.id === assignmentId);
                    this.facultyCourses[index] = result.data;
                    this.editingAssignmentId = null;
                    this.renderTables();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to update assignment');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('assignment', false);
        }

        async deleteCourse(courseId) {
            if (!confirm('Are you sure you want to delete this course? This will also remove all faculty assignments for this course.')) return;

            this.setLoading('course', true);

            try {
                const response = await fetch('<?php echo e(route("dm.courses.destroy", ":id")); ?>'.replace(':id', courseId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.courses = this.courses.filter(c => c.id !== courseId);
                    this.facultyCourses = this.facultyCourses.filter(fc => fc.course_id !== courseId);
                    this.renderTables();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to delete course');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('course', false);
        }

        async deleteFacultyCourse(assignmentId) {
            if (!confirm('Are you sure you want to remove this course assignment?')) return;

            this.setLoading('assignment', true);

            try {
                const response = await fetch('<?php echo e(route("dm.faculty-courses.destroy", ":id")); ?>'.replace(':id', assignmentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.facultyCourses = this.facultyCourses.filter(fc => fc.id !== assignmentId);
                    this.renderTables();
                    this.showAlert('success', result.message);
                } else {
                    throw new Error(result.message || 'Failed to remove assignment');
                }
            } catch (error) {
                this.showAlert('error', error.message || 'An error occurred');
            }

            this.setLoading('assignment', false);
        }

        renderTables() {
            this.renderCoursesTable();
            this.renderAssignmentsTable();
            this.updateCounts();
        }

        renderCoursesTable() {
            const tbody = document.getElementById('coursesTableBody');
            tbody.innerHTML = '';

            this.courses.forEach(course => {
                const row = document.createElement('tr');
                row.dataset.courseId = course.id;
                row.className = 'fade-in';

                if (this.editingCourseId === course.id) {
                    row.innerHTML = this.getCourseEditRowHTML(course);
                } else {
                    row.innerHTML = this.getCourseViewRowHTML(course);
                }

                tbody.appendChild(row);
            });

            this.attachCourseEventListeners();
        }

        renderAssignmentsTable() {
            const tbody = document.getElementById('assignmentsTableBody');
            tbody.innerHTML = '';

            this.facultyCourses.forEach(assignment => {
                const row = document.createElement('tr');
                row.dataset.assignmentId = assignment.id;
                row.className = 'fade-in';

                if (this.editingAssignmentId === assignment.id) {
                    row.innerHTML = this.getAssignmentEditRowHTML(assignment);
                } else {
                    row.innerHTML = this.getAssignmentViewRowHTML(assignment);
                }

                tbody.appendChild(row);
            });

            this.attachAssignmentEventListeners();
        }

        getCourseViewRowHTML(course) {
            return `
                <td class="align-middle">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <div class="avatar-initial bg-primary rounded-circle">
                                ${course.class_code.charAt(0)}
                            </div>
                        </div>
                        <div>
                            <span class="fw-medium">${course.class_code}</span>
                        </div>
                    </div>
                </td>
                <td class="align-middle">${course.subject_code}</td>
                <td class="align-middle">
                    <span class="badge bg-info badge-custom">${course.faculty_courses_count} Assignments</span>
                </td>
                <td class="align-middle">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm edit-course-btn" data-id="${course.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-course-btn" data-id="${course.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
        }

        getCourseEditRowHTML(course) {
            return `
                <td class="align-middle">
                    <input type="text" name="class_code" class="form-control form-control-sm" value="${course.class_code}">
                </td>
                <td class="align-middle">
                    <input type="text" name="subject_code" class="form-control form-control-sm" value="${course.subject_code}">
                </td>
                <td class="align-middle">
                    <span class="badge bg-info badge-custom">${course.faculty_courses_count} Assignments</span>
                </td>
                <td class="align-middle">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm save-course-btn" data-id="${course.id}">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-course-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            `;
        }

        getAssignmentViewRowHTML(assignment) {
            const faculty = assignment.faculty;
            const course = assignment.course;
            const semesterColors = {
                '1st': 'bg-success',
                '2nd': 'bg-warning',
                'Summer': 'bg-info'
            };

            return `
                <td class="align-middle">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <div class="avatar-initial bg-secondary rounded-circle">
                                ${faculty?.name?.charAt(0).toUpperCase() || 'F'}
                            </div>
                        </div>
                        <span class="fw-medium">${faculty?.user?.name || 'N/A'}</span>
                    </div>
                </td>
                <td class="align-middle">
                    <div>
                        <span class="fw-medium">${course?.class_code || 'N/A'}</span>
                        <br>
                        <small class="text-muted">${course?.subject_code || ''}</small>
                    </div>
                </td>
                <td class="align-middle">
                    <span class="badge bg-primary badge-custom">${assignment.academic_year}</span>
                </td>
                <td class="align-middle">
                    <span class="badge ${semesterColors[assignment.semester]} semester-badge">${assignment.semester} Semester</span>
                </td>
                <td class="align-middle">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm edit-assignment-btn" data-id="${assignment.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-assignment-btn" data-id="${assignment.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
        }

        getAssignmentEditRowHTML(assignment) {
            const facultyOptions = this.faculties.map(faculty => 
                `<option value="${faculty.id}" ${faculty.id == assignment.faculty_id ? 'selected' : ''}>${faculty.name || 'N/A'}</option>`
            ).join('');

            const courseOptions = this.courses.map(course => 
                `<option value="${course.id}" ${course.id == assignment.course_id ? 'selected' : ''}>${course.class_code} - ${course.subject_code}</option>`
            ).join('');

            return `
                <td class="align-middle">
                    <select name="faculty_id" class="form-select form-select-sm">
                        ${facultyOptions}
                    </select>
                </td>
                <td class="align-middle">
                    <select name="course_id" class="form-select form-select-sm">
                        ${courseOptions}
                    </select>
                </td>
                <td class="align-middle">
                    <input type="text" name="academic_year" class="form-control form-control-sm" value="${assignment.academic_year}" placeholder="2023-2024">
                </td>
                <td class="align-middle">
                    <select name="semester" class="form-select form-select-sm">
                        <option value="1st" ${assignment.semester === '1st' ? 'selected' : ''}>1st Semester</option>
                        <option value="2nd" ${assignment.semester === '2nd' ? 'selected' : ''}>2nd Semester</option>
                        <option value="Summer" ${assignment.semester === 'Summer' ? 'selected' : ''}>Summer</option>
                    </select>
                </td>
                <td class="align-middle">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm save-assignment-btn" data-id="${assignment.id}">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-assignment-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            `;
        }

        attachCourseEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-course-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.editingCourseId = id;
                    this.renderCoursesTable();
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-course-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.deleteCourse(id);
                });
            });

            // Save buttons
            document.querySelectorAll('.save-course-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.updateCourse(id);
                });
            });

            // Cancel buttons
            document.querySelectorAll('.cancel-course-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.editingCourseId = null;
                    this.renderCoursesTable();
                });
            });
        }

        attachAssignmentEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-assignment-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.editingAssignmentId = id;
                    this.renderAssignmentsTable();
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-assignment-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.deleteFacultyCourse(id);
                });
            });

            // Save buttons
            document.querySelectorAll('.save-assignment-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = parseInt(e.currentTarget.dataset.id);
                    this.updateFacultyCourse(id);
                });
            });

            // Cancel buttons
            document.querySelectorAll('.cancel-assignment-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.editingAssignmentId = null;
                    this.renderAssignmentsTable();
                });
            });
        }

        validateCourseForm(data) {
            if (!data.class_code || !data.subject_code) {
                this.showAlert('error', 'Please fill in Class Code and name');
                return false;
            }
            return true;
        }

        validateAssignmentForm(data) {
            if (!data.faculty_id || !data.course_id || !data.academic_year || !data.semester) {
                this.showAlert('error', 'Please fill in all fields');
                return false;
            }
            return true;
        }

        updateCounts() {
            document.getElementById('coursesCount').textContent = `${this.courses.length} Courses`;
            document.getElementById('assignmentsCount').textContent = `${this.facultyCourses.length} Assignments`;
        }

        showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            alertContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
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

        setLoading(type, loading) {
            const submitBtn = document.getElementById(type === 'course' ? 'submitCourseBtn' : 'submitAssignmentBtn');
            const originalText = type === 'course' ? '<i class="fas fa-plus me-2"></i>Add Course' : '<i class="fas fa-plus me-2"></i>Assign Course';
            const loadingText = type === 'course' ? '<span class="spinner-border spinner-border-sm me-2"></span>Adding...' : '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';

            if (loading) {
                submitBtn.innerHTML = loadingText;
                submitBtn.disabled = true;
            } else {
                submitBtn.innerHTML = originalText;
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
                    <form method="POST" action="<?php echo e(route('dm.courses.import')); ?>" enctype="multipart/form-data">
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card gradient-courses">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h4 class="card-title" id="coursesCount"><?php echo e(count($courses)); ?> Courses</h4>
                    <p class="card-text mb-0">Total courses available</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card gradient-assignments">
                <div class="card-body text-center">
                    <i class="fas fa-users-cog fa-2x mb-2"></i>
                    <h4 class="card-title" id="assignmentsCount"><?php echo e(count($facultyCourses)); ?> Assignments</h4>
                    <p class="card-text mb-0">Course assignments to faculty</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills justify-content-between" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="courses-tab" data-bs-toggle="pill" data-bs-target="#courses" type="button" role="tab">
                                <i class="fas fa-book me-2"></i>Manage Courses
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="pill" data-bs-target="#assignments" type="button" role="tab">
                                <i class="fas fa-users-cog me-2"></i>Course Assignments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                                Import
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Courses Tab -->
        <div class="tab-pane fade show active" id="courses" role="tabpanel">
            <!-- Add Course Form -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus me-2 text-primary"></i>Add New Course
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="addCourseForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-code me-1"></i>Class Code
                                        </label>
                                        <input type="text" name="class_code" class="form-control" placeholder="CS101" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-book me-1"></i>Subject Code
                                        </label>
                                        <input type="text" name="subject_code" class="form-control" placeholder="Introduction to Computer Science" required>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" id="submitCourseBtn" class="btn btn-gradient-primary w-100">
                                            <i class="fas fa-plus me-2"></i>Add Course
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>All Courses
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">
                                                <i class="fas fa-code me-2"></i>Class Code
                                            </th>
                                            <th>
                                                <i class="fas fa-book me-2"></i>Subject Code
                                            </th>
                                            <th>
                                                <i class="fas fa-users me-2"></i>Assignments
                                            </th>
                                            <th width="120">
                                                <i class="fas fa-cogs me-2"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="coursesTableBody">
                                        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr data-course-id="<?php echo e($course->id); ?>" class="fade-in">
                                                <td class="align-middle ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <div class="avatar-initial bg-primary rounded-circle">
                                                                <?php echo e(substr($course->class_code, 0, 1)); ?>

                                                            </div>
                                                        </div>
                                                        <span class="fw-medium"><?php echo e($course->class_code); ?></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle"><?php echo e($course->subject_code); ?></td>
                                                <td class="align-middle">
                                                    <span class="badge bg-info badge-custom"><?php echo e($course->faculty_courses_count); ?> Assignments</span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-primary btn-sm edit-course-btn" data-id="<?php echo e($course->id); ?>" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm delete-course-btn" data-id="<?php echo e($course->id); ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if(count($courses) === 0): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Courses Found</h5>
                                    <p class="text-muted mb-0">Start by adding your first course using the form above.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Assignments Tab -->
        <div class="tab-pane fade" id="assignments" role="tabpanel">
            <!-- Add Assignment Form -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-plus me-2 text-primary"></i>Assign Course to Faculty
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="addAssignmentForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-user me-1"></i>Faculty
                                        </label>
                                        <select name="faculty_id" class="form-select" required>
                                            <option value="">Select Faculty</option>
                                            <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($faculty->id); ?>"><?php echo e($faculty->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-book me-1"></i>Course
                                        </label>
                                        <select name="course_id" class="form-select" required>
                                            <option value="">Select Course</option>
                                            <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($course->id); ?>"><?php echo e($course->class_code); ?> - <?php echo e($course->subject_code); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-calendar me-1"></i>Academic Year
                                        </label>
                                        <input type="text" name="academic_year" class="form-control" placeholder="2023-2024" required>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-calendar-alt me-1"></i>Semester
                                        </label>
                                        <select name="semester" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="1st">1st Semester</option>
                                            <option value="2nd">2nd Semester</option>
                                            <option value="Summer">Summer</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" id="submitAssignmentBtn" class="btn btn-gradient-success w-100">
                                            <i class="fas fa-plus me-2"></i>Assign Course
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-users-cog me-2 text-primary"></i>Course Assignments
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">
                                                <i class="fas fa-user me-2"></i>Faculty
                                            </th>
                                            <th>
                                                <i class="fas fa-book me-2"></i>Course
                                            </th>
                                            <th>
                                                <i class="fas fa-calendar me-2"></i>Academic Year
                                            </th>
                                            <th>
                                                <i class="fas fa-calendar-alt me-2"></i>Semester
                                            </th>
                                            <th width="120">
                                                <i class="fas fa-cogs me-2"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="assignmentsTableBody">
                                        <?php $__currentLoopData = $facultyCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr data-assignment-id="<?php echo e($assignment->id); ?>" class="fade-in">
                                                <td class="align-middle ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <div class="avatar-initial bg-secondary rounded-circle">
                                                                <?php echo e(substr($assignment->faculty->user->name ?? 'F', 0, 1)); ?>

                                                            </div>
                                                        </div>
                                                        <span class="fw-medium"><?php echo e($assignment->faculty->user->name ?? 'N/A'); ?></span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div>
                                                        <span class="fw-medium"><?php echo e($assignment->course->class_code ?? 'N/A'); ?></span>
                                                        <br>
                                                        <small class="text-muted"><?php echo e($assignment->course->subject_code ?? ''); ?></small>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-primary badge-custom"><?php echo e($assignment->academic_year); ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <?php
                                                        $semesterColors = [
                                                            '1st' => 'bg-success',
                                                            '2nd' => 'bg-warning',
                                                            'Summer' => 'bg-info'
                                                        ];
                                                    ?>
                                                    <span class="badge <?php echo e($semesterColors[$assignment->semester] ?? 'bg-secondary'); ?> semester-badge"><?php echo e($assignment->semester); ?> Semester</span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-primary btn-sm edit-assignment-btn" data-id="<?php echo e($assignment->id); ?>" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm delete-assignment-btn" data-id="<?php echo e($assignment->id); ?>" title="Remove">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if(count($facultyCourses) === 0): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Course Assignments Found</h5>
                                    <p class="text-muted mb-0">Start by assigning courses to faculty members using the form above.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/content/data-management/dm-courses.blade.php ENDPATH**/ ?>