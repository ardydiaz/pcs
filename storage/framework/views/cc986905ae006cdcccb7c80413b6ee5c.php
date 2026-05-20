

<?php $__env->startSection('title', 'Data Management - Schedules'); ?>

<!-- Insert CSS and JavaScript here -->
<?php $__env->startSection('page-style'); ?>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Flatpickr Time Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .table-row:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-in-out;
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
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
    <?php echo $__env->make('components.table-controller-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.0/axios.min.js"></script>
    <script>
        // Setup CSRF token for axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Custom time range picker logic
        document.addEventListener('DOMContentLoaded', function () {
            function formatTimeRange(start, end) {
                function toCustomFormat(date) {
                    let h = date.getHours();
                    let m = date.getMinutes();
                    let period = h < 12 ? 'a' : 'p';
                    h = h % 12 || 12;
                    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}${period}`;
                }
                return `${toCustomFormat(start)} - ${toCustomFormat(end)}`;
            }

            function openTimeRangePicker(input) {
                // Use browser prompt for demo, replace with modal/timepicker for production
                let start = prompt('Enter start time (HH:mm, 24hr):', '07:00');
                let end = prompt('Enter end time (HH:mm, 24hr):', '08:30');
                if (start && end) {
                    let startDate = new Date(`1970-01-01T${start}:00`);
                    let endDate = new Date(`1970-01-01T${end}:00`);
                    input.value = formatTimeRange(startDate, endDate);
                }
            }

            document.querySelectorAll('#timePicker .fa-clock-o').forEach(function (icon) {
                icon.addEventListener('click', function () {
                    let input = icon.closest('.input-group').querySelector('input');
                    openTimeRangePicker(input);
                });
            });
            document.querySelectorAll('#editTimePicker .fa-clock-o').forEach(function (icon) {
                icon.addEventListener('click', function () {
                    let input = icon.closest('.input-group').querySelector('input');
                    openTimeRangePicker(input);
                });
            });
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';

            toast.className = `alert ${bgColor} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="d-flex align-items-center text-white">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-error'} me-2"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span>${message}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

            document.body.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 150);
            }, 3000);
        }

        let editingScheduleId = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Add new schedule
            document.getElementById('scheduleForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                let data = Object.fromEntries(formData);
                // Fix for multi-select day[]
                data.day = Array.from(this.querySelector('[name="day[]"]').selectedOptions).map(opt => opt.value);

                try {
                    const response = await axios.post('/data-management/schedules', data);

                    if (response.data.success) {
                        showToast(response.data.message, 'success');
                        addScheduleToTable(response.data.data);
                        this.reset();
                        updateTotalCount();
                    }
                } catch (error) {
                    showToast(error.response?.data?.message || 'Error creating schedule', 'error');
                }
            });

            // Edit schedule form
            document.getElementById('editScheduleForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const scheduleId = document.getElementById('editScheduleId').value;
                const formData = new FormData(this);
                let data = Object.fromEntries(formData);
                // Fix for multi-select day[]
                data.day = Array.from(this.querySelector('[name="day[]"]').selectedOptions).map(opt => opt.value);

                try {
                    const response = await axios.put(`/data-management/schedules/${scheduleId}`, data);

                    if (response.data.success) {
                        showToast(response.data.message, 'success');
                        updateScheduleInTable(scheduleId, response.data.data);
                        closeEditModal();
                    }
                } catch (error) {
                    showToast(error.response?.data?.message || 'Error updating schedule', 'error');
                }
            });

            // Close modal when clicking outside
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('click', function (e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });
            }

            // Close modal on escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && editModal && !editModal.classList.contains('d-none')) {
                    closeEditModal();
                }
            });

            initializeSchedulesTableController();
            refreshSchedulesController();
            updateTotalCount();
        });

        // Functions
        function addScheduleToTable(schedule) {
            const tbody = document.getElementById('schedulesTableBody');
            const emptyRow = tbody.querySelector('.empty-state');

            if (emptyRow) {
                emptyRow.remove();
            }

            const rowMarkup = createScheduleRow(schedule);
            tbody.insertAdjacentHTML('afterbegin', rowMarkup);
            const newRow = tbody.querySelector(`tr[data-id="${schedule.id}"]`);
            setScheduleSearchData(newRow, schedule);
            refreshSchedulesController();
        }

        function formatScheduleDay(day) {
            if (Array.isArray(day)) {
                return day.join(', ');
            }

            return day || '';
        }

        function setScheduleSearchData(row, schedule) {
            if (!row) {
                return;
            }

            const dayValue = formatScheduleDay(schedule.day);
            const createdText = schedule.created_at ? new Date(schedule.created_at).toLocaleDateString() : '';
            const searchValue = [
                schedule.faculty_course?.course?.class_code || '',
                schedule.time || '',
                dayValue,
                createdText
            ].join(' ').toLowerCase();

            row.dataset.search = searchValue;
        }

        function refreshSchedulesController() {
            const controller = window.tableControllers?.schedulesTable;
            if (controller) {
                controller.refresh();
            }
        }

        function createScheduleRow(schedule) {
            const sessionDate = new Date(schedule.session_date);
            const createdDate = new Date(schedule.created_at);

            const courseCode = schedule.faculty_course?.course?.class_code || 'N/A';
            const time = schedule.time || '';
            const day = formatScheduleDay(schedule.day);
            const created = schedule.created_at ? new Date(schedule.created_at).toLocaleDateString() : '';
            const facultyCourseId = schedule.faculty_course_id || '';

            return `
                <tr class="table-row animate-fadeIn" data-id="${schedule.id}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial rounded-circle bg-primary">
                                    <i class="bx bx-book"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0">${courseCode}</h6>
                                <small class="text-muted">ID: ${facultyCourseId}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-medium">${time}</span>
                    </td>
                    <td>
                        <span class="fw-medium">${day}</span>
                    </td>
                    <td>
                        <small class="text-muted">${created}</small>
                    </td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editSchedule(${schedule.id})" data-bs-toggle="tooltip" title="Edit">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSchedule(${schedule.id})" data-bs-toggle="tooltip" title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function updateScheduleInTable(scheduleId, schedule) {
            const row = document.querySelector(`tr[data-id="${scheduleId}"]`);
            if (row) {
                row.outerHTML = createScheduleRow(schedule);
                const updatedRow = document.querySelector(`tr[data-id="${scheduleId}"]`);
                setScheduleSearchData(updatedRow, schedule);
                refreshSchedulesController();
            }
        }

        function editSchedule(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            document.getElementById('editScheduleId').value = id;

            document.getElementById('editFacultyCourse').value =
                row.querySelector('small').textContent.replace('ID: ', '');
            document.getElementById('editTime').value =
                row.querySelector('td:nth-child(2) .fw-medium').textContent;
            // For days, you’d need to parse the string and set selected options

            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        function closeEditModal() {
            const editModal = document.getElementById('editModal');
            const modal = bootstrap.Modal.getInstance(editModal);
            if (modal) {
                modal.hide();
            }
            document.getElementById('editScheduleForm').reset();
        }

        async function deleteSchedule(id) {
            if (!confirm('Are you sure you want to delete this schedule?')) {
                return;
            }

            try {
                const response = await axios.delete(`/data-management/schedules/${id}`);

                if (response.data.success) {
                    showToast(response.data.message, 'success');
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.style.transform = 'translateX(-100%)';
                    row.style.opacity = '0';

                    setTimeout(() => {
                        row.remove();
                        refreshSchedulesController();
                        updateTotalCount();

                        // Check if table is empty
                        const tbody = document.getElementById('schedulesTableBody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <tr class="empty-state" data-empty>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <td colspan="5" class="text-center py-5">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               <div class="empty-state-content">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <i class="bx bx-calendar-x display-4 text-muted mb-3"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <h5 class="mb-2">No schedules found</h5>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <p class="text-muted mb-0">Add your first schedule using the form above.</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           </td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       </tr>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                            refreshSchedulesController();
                        }
                    }, 300);
                }
            } catch (error) {
                showToast(error.response?.data?.message || 'Error deleting schedule', 'error');
            }
        }

        function initializeSchedulesTableController() {
            const root = document.querySelector('[data-table-id="schedulesTable"]');
            if (root && window.TableController) {
                const controller = new TableController(root);
                window.tableControllers = window.tableControllers || {};
                window.tableControllers.schedulesTable = controller;
            }
        }

        function updateTotalCount() {
            const tbody = document.getElementById('schedulesTableBody');
            const rows = tbody.querySelectorAll('tr:not(.empty-state)');
            const totalElement = document.getElementById('totalCount');
            if (totalElement) {
                totalElement.textContent = rows.length;
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
                    <form method="POST" action="<?php echo e(route('dm.schedules.import')); ?>" enctype="multipart/form-data">
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

    <div class="container-fluid">
        <!-- Add New Schedule Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-shadow border-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-plus me-2 text-primary"></i>Add New Schedule
                        </h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                            Import
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="scheduleForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Faculty Course</label>
                                <select name="faculty_course_id" class="form-select input-focus" required>
                                    <option value="">Select Course</option>
                                    <?php $__currentLoopData = $facultyCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($course->id); ?>">
                                            <?php echo e($course->course->class_code ?? 'N/A'); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium">Time</label>
                                <div class="input-group date" id="timePicker">
                                    <input type="text" name="time" class="form-control timePicker input-focus" required
                                        placeholder="07:00a - 08:30a">
                                    <span class="input-group-text"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-medium">Day</label>
                                <select name="day[]" class="form-select input-focus" multiple required>
                                    <option value="M">Monday</option>
                                    <option value="T">Tuesday</option>
                                    <option value="W">Wednesday</option>
                                    <option value="TH">Thursday</option>
                                    <option value="F">Friday</option>
                                    <option value="S">Saturday</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-medium">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100 btn-primary">
                                    <i class="bx bx-plus me-1"></i>Add Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedules Table -->
        <div class="row">
            <div class="col-12">
                <div class="card card-shadow border-0" data-table-controller data-table-id="schedulesTable">
                    <div class="card-header bg-transparent border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <div class="avatar-initial rounded bg-success">
                                        <i class="bx bx-table"></i>
                                    </div>
                                </div>
                                <h5 class="card-title mb-0">Schedule List</h5>
                            </div>
                            <div class="text-muted small">
                                Total: <span class="fw-semibold text-body" id="totalCount"><?php echo e(count($schedules)); ?></span>
                                schedules
                            </div>
                        </div>
                    </div>
                    <div class="px-3 pt-3 pb-3 border-bottom">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <label for="schedulesRowsPerPage" class="form-label me-2 mb-0">Show:</label>
                                    <select id="schedulesRowsPerPage" class="form-select form-select-sm" style="width: auto;" data-table-length>
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
                                    <div class="input-group" style="max-width: 320px;">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" id="schedulesSearch" class="form-control" data-table-search
                                            placeholder="Search schedules...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="schedulesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">Course</th>
                                        <th class="fw-semibold">Time</th>
                                        <th class="fw-semibold">Day</th>
                                        <th class="fw-semibold">Created</th>
                                        <th class="fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="schedulesTableBody">
                                    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $scheduleSearch = strtolower(
                                                ($schedule->facultyCourse->course->class_code ?? '') . ' ' .
                                                ($schedule->time ?? '') . ' ' .
                                                (is_array($schedule->day) ? implode(' ', $schedule->day) : ($schedule->day ?? '')) . ' ' .
                                                ($schedule->created_at ? $schedule->created_at->format('M d, Y') : '')
                                            );
                                        ?>
                                        <tr class="table-row" data-id="<?php echo e($schedule->id); ?>" data-search="<?php echo e($scheduleSearch); ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <div class="avatar-initial rounded-circle bg-primary">
                                                            <i class="bx bx-book"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <?php echo e($schedule->facultyCourse->course->class_code ?? 'N/A'); ?>

                                                        </h6>
                                                        <small class="text-muted">ID: <?php echo e($schedule->faculty_course_id); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo e($schedule->time); ?></span>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?php echo e(is_array($schedule->day) ? implode(', ', $schedule->day) : $schedule->day); ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo e($schedule->created_at->format('M d, Y')); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick="editSchedule(<?php echo e($schedule->id); ?>)" data-bs-toggle="tooltip"
                                                        title="Edit">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="deleteSchedule(<?php echo e($schedule->id); ?>)" data-bs-toggle="tooltip"
                                                        title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr class="empty-state" data-empty>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="empty-state-content">
                                                    <i class="bx bx-calendar-x display-4 text-muted mb-3"></i>
                                                    <h5 class="mb-2">No schedules found</h5>
                                                    <p class="text-muted mb-0">Add your first schedule using the form above.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3">
                            <div class="text-muted" data-table-info>Showing 0 to 0 of 0 entries</div>
                            <ul class="pagination pagination-sm mb-0 mt-3 mt-md-0" data-table-pagination></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content card-shadow border-0">
                <div class="modal-header border-bottom-0 gradient-bg">
                    <div class="d-flex align-items-center text-white">
                        <div class="avatar avatar-sm me-3">
                            <div class="avatar-initial rounded bg-white bg-opacity-20">
                                <i class="bx bx-edit-alt text-white"></i>
                            </div>
                        </div>
                        <h5 class="modal-title text-white mb-0">Edit Schedule</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm">
                        <input type="hidden" id="editScheduleId">

                        <div class="mb-3">
                            <label class="form-label fw-medium">Faculty Course</label>
                            <select id="editFacultyCourse" name="faculty_course_id" class="form-select input-focus"
                                required>
                                <?php $__currentLoopData = $facultyCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($course->id); ?>">
                                        <?php echo e($course->course->class_code ?? 'N/A'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Time</label>
                            <div class="input-group date" id="editTimePicker">
                                <input type="text" id="editTime" name="time" class="form-control input-focus" required
                                    placeholder="07:00a - 08:30a">
                                <span class="input-group-text"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Day</label>
                            <select id="editDay" name="day[]" class="form-select input-focus" multiple required>
                                <option value="M">Monday</option>
                                <option value="T">Tuesday</option>
                                <option value="W">Wednesday</option>
                                <option value="TH">Thursday</option>
                                <option value="F">Friday</option>
                                <option value="S">Saturday</option>
                            </select>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-secondary flex-fill"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning flex-fill">
                                <i class="bx bx-check me-1"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\post-class-survey\resources\views/content/data-management/dm-schedules.blade.php ENDPATH**/ ?>