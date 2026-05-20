

<?php $__env->startSection('title', 'Dashboard - Reports'); ?>

<?php $__env->startSection('vendor-style'); ?>
    <style>
        .metric-card {
            transition: transform 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
        }

        .rating-progress {
            height: 6px;
        }

        .department-card {
            border-left: 4px solid #696cff;
        }

        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('vendor-script'); ?>
    <script>
        // Enhanced functionality for the dashboard

        // Export functionality
        function exportData() {
            const filters = {
                department: '<?php echo e($selectedDepartment); ?>',
                academic_year: '<?php echo e($selectedAcademicYear); ?>',
                semester: '<?php echo e($selectedSemester); ?>'
            };

            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Department,Faculty Count,Total Evaluations,Active Evaluations,Total Responses,Average Rating\n";

            <?php $__currentLoopData = $departmentBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                csvContent += "<?php echo e($dept['department']); ?>,<?php echo e($dept['faculty_count']); ?>,<?php echo e($dept['total_evaluations']); ?>,<?php echo e($dept['active_evaluations']); ?>,<?php echo e($dept['total_responses']); ?>,<?php echo e($dept['average_rating']); ?>\n";
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                                                            // Create and trigger download
                                                                                            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `evaluation_report_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Real-time updates
        function refreshData() {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    // Update only the metrics cards without full page reload
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    // Update metric values
                    document.querySelectorAll('.metric-card h3').forEach((element, index) => {
                        const newValue = newDoc.querySelectorAll('.metric-card h3')[index];
                        if (newValue && element.textContent !== newValue.textContent) {
                            element.style.animation = 'pulse 0.5s';
                            element.textContent = newValue.textContent;
                        }
                    });
                })
                .catch(error => console.log('Auto-refresh failed:', error));
        }

        // Form auto-submit on filter change
        document.querySelectorAll('select[name="department"], select[name="academic_year"], select[name="semester"]').forEach(select => {
            select.addEventListener('change', function () {
                // Add loading state
                const button = document.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                button.disabled = true;

                // Submit form
                this.form.submit();
            });
        });

        // Tooltips for metrics
        const tooltips = {
            'Total Faculties': 'Number of faculty members in the selected filters',
            'Total Responses': 'Total evaluation responses submitted by students',
            'Average Rating': 'Overall average effectiveness rating across all evaluations',
            'Courses Evaluated': 'Number of unique courses that have received evaluations'
        };

        // Add tooltips
        document.querySelectorAll('.metric-card p').forEach(element => {
            const text = element.textContent.trim();
            if (tooltips[text]) {
                element.setAttribute('title', tooltips[text]);
                element.style.cursor = 'help';
            }
        });

        // Progress bar animations
        function animateProgressBars() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-in-out';
                    bar.style.width = width;
                }, 100);
            });
        }

        function setupPagedList(container) {
            const pageSize = parseInt(container.getAttribute('data-page-size') || '10', 10);
            const items = Array.from(container.querySelectorAll('[data-paged-item]'));
            const footer = container.querySelector('[data-paged-list-footer]');
            if (!footer || items.length === 0 || items.length <= pageSize) {
                if (footer) {
                    footer.classList.add('d-none');
                }
                return;
            }

            const showingEl = footer.querySelector('[data-paged-list-showing]');
            const totalEl = footer.querySelector('[data-paged-list-total]');
            const prevBtn = footer.querySelector('[data-paged-list-prev]');
            const nextBtn = footer.querySelector('[data-paged-list-next]');
            let currentPage = 1;
            const totalPages = Math.ceil(items.length / pageSize);

            const renderPage = () => {
                const start = (currentPage - 1) * pageSize;
                const end = start + pageSize;
                items.forEach((item, index) => {
                    item.classList.toggle('d-none', index < start || index >= end);
                });
                showingEl.textContent = `${start + 1}-${Math.min(end, items.length)}`;
                totalEl.textContent = `${items.length}`;
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages;
            };

            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    renderPage();
                }
            });
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage += 1;
                    renderPage();
                }
            });

            renderPage();
        }

        // Initialize animations on load
        document.addEventListener('DOMContentLoaded', function () {
            animateProgressBars();
            document.querySelectorAll('[data-paged-list-container]').forEach(setupPagedList);
        });

        // Auto-refresh every 5 minutes
        setInterval(refreshData, 300000);

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
                                                                                            @keyframes pulse {
                                                                                                0% { transform: scale(1); }
                                                                                                50% { transform: scale(1.05); }
                                                                                                100% { transform: scale(1); }
                                                                                            }

                                                                                            .progress-bar {
                                                                                                transition: width 0.8s ease-in-out;
                                                                                            }

                                                                                            .metric-card {
                                                                                                transition: all 0.3s ease;
                                                                                            }

                                                                                            .metric-card:hover {
                                                                                                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                                                                                            }
                                                                                        `;
        document.head.appendChild(style);
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Post-Class Survey Analytics</h4>
                <p class="text-muted mb-0">Comprehensive dashboard for evaluation insights and reporting</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i>Print Report
                </button>
                <button class="btn btn-primary" onclick="exportData()">
                    <i class="bx bx-download me-1"></i>Export Data
                </button>
            </div>
        </div>

        
        <div class="card mb-4 filter-section">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('reports')); ?>" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <?php if(!empty($isDepartmentScoped)): ?>
                            <input type="hidden" name="department" value="<?php echo e($selectedDepartment); ?>">
                            <div class="form-control d-flex flex-wrap gap-1" style="min-height: 2.5rem;">
                                <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <span class="badge bg-label-secondary"><?php echo e($dept); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-muted">No department</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <select name="department" class="form-select">
                                <option value="all" <?php echo e($selectedDepartment == 'all' ? 'selected' : ''); ?>>All Departments</option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept); ?>" <?php echo e($selectedDepartment == $dept ? 'selected' : ''); ?>>
                                        <?php echo e($dept); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <option value="all" <?php echo e($selectedAcademicYear == 'all' ? 'selected' : ''); ?>>All Years</option>
                            <?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year); ?>" <?php echo e($selectedAcademicYear == $year ? 'selected' : ''); ?>>
                                    <?php echo e($year); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="all" <?php echo e($selectedSemester == 'all' ? 'selected' : ''); ?>>All Semesters</option>
                            <?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sem); ?>" <?php echo e($selectedSemester == $sem ? 'selected' : ''); ?>>
                                    <?php echo e($sem); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    <i class="bx bx-user-check bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo e($metrics['total_faculties']); ?></h3>
                                <p class="text-muted mb-0">Total Faculties</p>
                            </div>
                        </div>
                        <small class="text-success">
                            <?php echo e($metrics['active_evaluations']); ?> active evaluations
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-success rounded">
                                    <i class="bx bx-bar-chart bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo e($metrics['total_responses']); ?></h3>
                                <p class="text-muted mb-0">Total Responses</p>
                            </div>
                        </div>
                        <small class="text-info">
                            <?php echo e($metrics['responses_with_feedback']); ?> with feedback
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-warning rounded">
                                    <i class="bx bx-star bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo e($metrics['average_rating']); ?></h3>
                                <p class="text-muted mb-0">Average Rating</p>
                            </div>
                        </div>
                        <small class="text-muted">
                            Out of 4.0 scale
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card metric-card h-100">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-info rounded">
                                    <i class="bx bx-book bx-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo e($metrics['courses_evaluated']); ?></h3>
                                <p class="text-muted mb-0">Courses Evaluated</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Overall Rating Distribution</h5>
                        <small class="text-muted"><?php echo e($metrics['total_responses']); ?> total responses</small>
                    </div>
                    <div class="card-body">
                        <?php if($metrics['total_responses'] > 0): ?>
                            <div class="row">
                                <?php $__currentLoopData = ['4' => ['Very Effective', 'success'], '3' => ['Effective', 'info'], '2' => ['Somewhat Effective', 'warning'], '1' => ['Not Effective', 'danger']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $count = $metrics['rating_distribution']->get($rating, 0);
                                        $percentage = ($count / $metrics['total_responses']) * 100;
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-medium"><?php echo e($info[0]); ?></span>
                                            <span class="text-muted"><?php echo e($count); ?> (<?php echo e(number_format($percentage, 1)); ?>%)</span>
                                        </div>
                                        <div class="progress rating-progress mb-2">
                                            <div class="progress-bar bg-<?php echo e($info[1]); ?>" style="width: <?php echo e($percentage); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>

                            
                            <div class="row mt-4">
                                <div class="col-md-4 text-center">
                                    <h6 class="text-success">
                                        <?php echo e($metrics['rating_distribution']->get('4', 0) + $metrics['rating_distribution']->get('3', 0)); ?>

                                    </h6>
                                    <small class="text-muted">Positive Ratings</small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6 class="text-warning"><?php echo e($metrics['rating_distribution']->get('2', 0)); ?></h6>
                                    <small class="text-muted">Neutral Ratings</small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6 class="text-danger"><?php echo e($metrics['rating_distribution']->get('1', 0)); ?></h6>
                                    <small class="text-muted">Needs Improvement</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bx bx-bar-chart display-4 text-muted mb-3"></i>
                                <h6 class="mb-2">No Data Available</h6>
                                <p class="text-muted">No evaluation responses found for the selected filters.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Evaluations</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="recent-activity p-3">
                            <?php $__empty_1 = true; $__currentLoopData = $recentResponses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <span class="avatar-initial rounded bg-light text-dark">
                                                            <?php echo e(substr($response->resolved_course_code ?? 'N', 0, 2)); ?>

                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo e($response->resolved_course_code); ?>

                                                        </h6>
                                                        <div class="d-flex align-items-center">
                                                            <span
                                                                class="badge bg-<?php echo e($response->effectiveness_rating == '4' ? 'success' :
                                ($response->effectiveness_rating == '3' ? 'info' :
                                    ($response->effectiveness_rating == '2' ? 'warning' : 'danger'))); ?> me-2"><?php echo e($response->effectiveness_rating); ?></span>
                                                            <small class="text-muted"><?php echo e($response->created_at->diffForHumans()); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-4">
                                    <i class="bx bx-time text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php if($selectedDepartment !== 'all'): ?>
                                <?php echo e($selectedDepartment); ?> Department Performance
                            <?php else: ?>
                                Department Performance Breakdown
                            <?php endif; ?>
                        </h5>
                        <?php if($selectedDepartment !== 'all'): ?>
                            <small class="text-muted">Showing data for <?php echo e($selectedDepartment); ?> department only</small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Show entries</label>
                                <select class="form-select form-select-sm" id="departmentPerPage"
                                    onchange="updatePerPage(this.value)">
                                    <option value="10" <?php echo e($perPage == 10 ? 'selected' : ''); ?>>10</option>
                                    <option value="25" <?php echo e($perPage == 25 ? 'selected' : ''); ?>>25</option>
                                    <option value="50" <?php echo e($perPage == 50 ? 'selected' : ''); ?>>50</option>
                                    <option value="100" <?php echo e($perPage == 100 ? 'selected' : ''); ?>>100</option>
                                </select>
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control form-control-sm" id="departmentSearch"
                                    placeholder="Search departments..." onkeyup="filterDepartments(this.value)">
                            </div>
                        </div>

                        <div class="table-responsive" id="departmentTableContainer">
                            <table class="table table-hover" id="departmentTable">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-center">Faculties</th>
                                        <th class="text-center">Evaluations</th>
                                        <th class="text-center">Responses</th>
                                        <th class="text-center">Avg Rating</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $departmentBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="department-row" data-department="<?php echo e($dept['department']); ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <span class="avatar-initial bg-primary rounded">
                                                            <?php echo e(substr($dept['department'], 0, 2)); ?>

                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo e($dept['department']); ?></h6>
                                                        <small class="text-muted"><?php echo e($dept['active_evaluations']); ?>

                                                            active</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark"><?php echo e($dept['faculty_count']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo e($dept['total_evaluations']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success"><?php echo e($dept['total_responses']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-medium"><?php echo e($dept['average_rating']); ?></span>
                                                <div class="progress rating-progress mt-1">
                                                    <div class="progress-bar bg-<?php echo e($dept['average_rating'] >= 3 ? 'success' : ($dept['average_rating'] >= 2 ? 'warning' : 'danger')); ?>"
                                                        style="width: <?php echo e(($dept['average_rating'] / 4) * 100); ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="showFacultyModal('<?php echo e($dept['department']); ?>')">
                                                    <i class="bx bx-group me-1"></i>View Faculty
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bx bx-buildings text-muted mb-2" style="font-size: 2rem;"></i>
                                                <p class="text-muted mb-0">No department data available</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        
                        <div class="d-flex justify-content-between align-items-center mt-3" id="departmentPagination">
                            <div>
                                <small class="text-muted">Showing <span
                                        id="departmentShowing"><?php echo e(count($departmentBreakdown)); ?></span> of
                                    <?php echo e(count($departmentBreakdown)); ?> entries</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row align-items-stretch">
            
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Rated Faculties</h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $facultyRatings['top_rated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                                    ->map(function ($value) {
                                        return trim($value);
                                    })
                                    ->filter(function ($value) {
                                        return $value !== '';
                                    })
                                    ->values();
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="badge bg-<?php echo e($index < 3 ? 'success' : 'primary'); ?> rounded-pill">
                                        #<?php echo e($index + 1); ?>

                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo e($faculty['faculty_name']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php $__empty_2 = true; $__currentLoopData = $facultyDepartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="badge bg-label-secondary"><?php echo e($department); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <span class="text-muted">No department</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium"><?php echo e($faculty['average_rating']); ?>/4.0</div>
                                    <small class="text-muted"><?php echo e($faculty['total_responses']); ?> responses</small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-3">
                                <i class="bx bx-star text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">No rating data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Low Rated Faculties</h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $facultyRatings['low_rated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                                    ->map(function ($value) {
                                        return trim($value);
                                    })
                                    ->filter(function ($value) {
                                        return $value !== '';
                                    })
                                    ->values();
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="badge bg-<?php echo e($index < 3 ? 'danger' : 'secondary'); ?> rounded-pill">
                                        #<?php echo e($index + 1); ?>

                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo e($faculty['faculty_name']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php $__empty_2 = true; $__currentLoopData = $facultyDepartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="badge bg-label-secondary"><?php echo e($department); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <span class="text-muted">No department</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium"><?php echo e($faculty['average_rating']); ?>/4.0</div>
                                    <small class="text-muted"><?php echo e($faculty['total_responses']); ?> responses</small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-3">
                                <i class="bx bx-trending-down text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">No rating data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Most Evaluated Faculties</h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $facultyRatings['most_evaluated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $facultyDepartments = collect(explode(',', $faculty['department'] ?? ''))
                                    ->map(function ($value) {
                                        return trim($value);
                                    })
                                    ->filter(function ($value) {
                                        return $value !== '';
                                    })
                                    ->values();
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="badge bg-<?php echo e($index < 3 ? 'info' : 'secondary'); ?> rounded-pill">
                                        #<?php echo e($index + 1); ?>

                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo e($faculty['faculty_name']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php $__empty_2 = true; $__currentLoopData = $facultyDepartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <span class="badge bg-label-secondary"><?php echo e($department); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <span class="text-muted">No department</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium"><?php echo e($faculty['total_responses']); ?> responses</div>
                                    <small class="text-muted"><?php echo e($faculty['average_rating']); ?>/4.0 avg</small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-3">
                                <i class="bx bx-bar-chart text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">No evaluation data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="facultyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Faculty Members - <span id="modalDepartmentName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn btn-outline-primary" id="departmentExportBtn">
                            <i class="bx bx-download me-1"></i>Export Responses
                        </button>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Show entries</label>
                            <select class="form-select form-select-sm" id="facultyPerPage">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Avg Rating</label>
                            <select class="form-select form-select-sm" id="facultyRatingFilter">
                                <option value="all">All</option>
                                <option value="very_effective">Very Effective</option>
                                <option value="effective">Effective</option>
                                <option value="somewhat_effective">Somewhat Effective</option>
                                <option value="not_effective">Not Effective</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select form-select-sm" id="facultyStatusFilter">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" id="facultySearch"
                                placeholder="Search faculty...">
                        </div>
                    </div>

                    
                    <div id="facultyTableContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    
                    <div id="facultyPagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="departmentExportModal" tabindex="-1" aria-labelledby="departmentExportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="GET" id="departmentExportForm" action="<?php echo e(route('reports.department.export')); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="departmentExportModalLabel">Export Responses</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="department" id="departmentExportDepartment">
                        <div class="mb-3">
                            <label for="departmentExportStartDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="departmentExportStartDate" name="start_date">
                            <small class="text-muted">Leave blank to include all dates.</small>
                        </div>
                        <div class="mb-3">
                            <label for="departmentExportEndDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="departmentExportEndDate" name="end_date">
                        </div>
                        <div class="mb-3">
                            <label for="departmentExportAcademicYear" class="form-label">Academic Year</label>
                            <select class="form-select" id="departmentExportAcademicYear" name="academic_year">
                                <option value="all" selected>All</option>
                                <?php if($selectedAcademicYear !== 'all'): ?>
                                    <option value="<?php echo e($selectedAcademicYear); ?>"><?php echo e($selectedAcademicYear); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="departmentExportSemester" class="form-label">Semester</label>
                            <select class="form-select" id="departmentExportSemester" name="semester">
                                <option value="all" selected>All</option>
                                <?php if($selectedSemester !== 'all'): ?>
                                    <option value="<?php echo e($selectedSemester); ?>"><?php echo e($selectedSemester); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-download me-1"></i>Export CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced functionality for the dashboard
        let currentDepartment = '';
        const facultySortState = { key: 'name', direction: 'asc' };
        const facultyFilters = { rating: 'all', status: 'all' };

        // Export functionality
        function exportData() {
            const filters = {
                department: '<?php echo e($selectedDepartment); ?>',
                academic_year: '<?php echo e($selectedAcademicYear); ?>',
                semester: '<?php echo e($selectedSemester); ?>'
            };

            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add headers
            csvContent += "Department,Faculty Count,Total Evaluations,Active Evaluations,Total Responses,Average Rating\n";

            <?php $__currentLoopData = $departmentBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                csvContent += "<?php echo e($dept['department']); ?>,<?php echo e($dept['faculty_count']); ?>,<?php echo e($dept['total_evaluations']); ?>,<?php echo e($dept['active_evaluations']); ?>,<?php echo e($dept['total_responses']); ?>,<?php echo e($dept['average_rating']); ?>\n";
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                // Create and trigger download
                                                const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `evaluation_report_${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Department table functionality
        function updatePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            window.location.href = url.toString();
        }

        function filterDepartments(searchTerm) {
            const rows = document.querySelectorAll('.department-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const departmentName = row.getAttribute('data-department').toLowerCase();
                if (departmentName.includes(searchTerm.toLowerCase())) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('departmentShowing').textContent = visibleCount;
        }

        // Faculty modal functionality
        function resetFacultyState() {
            facultySortState.key = 'name';
            facultySortState.direction = 'asc';
            facultyFilters.rating = 'all';
            facultyFilters.status = 'all';
            const ratingSelect = document.getElementById('facultyRatingFilter');
            const statusSelect = document.getElementById('facultyStatusFilter');
            if (ratingSelect) {
                ratingSelect.value = 'all';
            }
            if (statusSelect) {
                statusSelect.value = 'all';
            }
        }

        function showFacultyModal(department) {
            currentDepartment = department;
            document.getElementById('modalDepartmentName').textContent = department;
            const exportDepartmentInput = document.getElementById('departmentExportDepartment');
            const exportModalLabel = document.getElementById('departmentExportModalLabel');
            if (exportDepartmentInput) {
                exportDepartmentInput.value = department;
            }
            if (exportModalLabel) {
                exportModalLabel.textContent = `Export Responses - ${department}`;
            }

            const modal = new bootstrap.Modal(document.getElementById('facultyModal'));
            modal.show();

            resetFacultyState();
            loadFacultyData(department, 1, 10, '');
        }

        function loadFacultyData(department, page = 1, perPage = 10, search = '') {
            const url = new URL('<?php echo e(route("reports.department.faculties")); ?>');
            url.searchParams.set('department', department);
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('search', search);
            url.searchParams.set('rating_filter', facultyFilters.rating);
            url.searchParams.set('status_filter', facultyFilters.status);
            url.searchParams.set('sort_key', facultySortState.key);
            url.searchParams.set('sort_dir', facultySortState.direction);
            url.searchParams.set('academic_year', '<?php echo e($selectedAcademicYear); ?>');
            url.searchParams.set('semester', '<?php echo e($selectedSemester); ?>');

            // Show loading
            document.getElementById('facultyTableContainer').innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading faculty data...</p>
                    </div>
                `;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers.get('content-type'));

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    // Check if the response has the expected structure
                    if (!data || typeof data !== 'object') {
                        throw new Error('Invalid response: not an object');
                    }

                    // Handle error responses
                    if (data.success === false) {
                        throw new Error(data.message || data.error || 'Server returned an error');
                    }

                    // Check for required properties
                    if (!data.hasOwnProperty('html')) {
                        throw new Error('Invalid response: missing html property');
                    }

                    if (!data.hasOwnProperty('pagination')) {
                        throw new Error('Invalid response: missing pagination property');
                    }

                    // Set the HTML content
                    document.getElementById('facultyTableContainer').innerHTML = data.html;

                    // Handle pagination - check if there's pagination content
                    const paginationContainer = document.getElementById('facultyPagination');
                    if (data.pagination && data.pagination.trim() !== '') {
                        paginationContainer.innerHTML = data.pagination;
                        paginationContainer.style.display = 'block';
                    } else {
                        // Hide pagination if no pagination needed (single page)
                        paginationContainer.innerHTML = '';
                        paginationContainer.style.display = 'none';
                    }

                    // Update pagination info if meta data is available
                    if (data.meta) {
                        updatePaginationInfo(data.meta);
                    }

                    // Bind pagination click events
                    bindPaginationEvents();
                    bindFacultySortHeaders();

                    // Success feedback
                    console.log('Faculty data loaded successfully');

                })
                .catch(error => {
                    console.error('Error loading faculty data:', error);

                    // Show detailed error message
                    document.getElementById('facultyTableContainer').innerHTML = `
                        <div class="text-center py-4">
                            <i class="bx bx-error text-danger mb-2" style="font-size: 2rem;"></i>
                            <h6 class="text-danger mb-2">Error Loading Faculty Data</h6>
                            <p class="text-muted mb-2">${error.message}</p>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadFacultyData('${department}', ${page}, ${perPage}, '${search}')">
                                <i class="bx bx-refresh me-1"></i>Retry
                            </button>
                        </div>
                    `;

                    // Hide pagination on error
                    document.getElementById('facultyPagination').style.display = 'none';
                });
        }

        function bindFacultySortHeaders() {
            const headers = Array.from(document.querySelectorAll('#facultyTable thead th[data-sort-key]'));
            if (headers.length === 0) {
                return;
            }

            headers.forEach((header) => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sortKey;
                    if (!sortKey) {
                        return;
                    }

                    if (facultySortState.key === sortKey) {
                        facultySortState.direction = facultySortState.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        facultySortState.key = sortKey;
                        facultySortState.direction = 'asc';
                    }

                    updateFacultySortIndicators(headers);
                    const perPage = document.getElementById('facultyPerPage').value || 10;
                    const search = document.getElementById('facultySearch').value || '';
                    loadFacultyData(currentDepartment, 1, perPage, search);
                });
            });

            updateFacultySortIndicators(headers);
        }

        function updateFacultySortIndicators(headers) {
            headers.forEach((header) => {
                header.classList.remove('sorted-asc', 'sorted-desc');
                header.dataset.sortState = 'none';

                if (header.dataset.sortKey === facultySortState.key) {
                    const directionClass = facultySortState.direction === 'asc' ? 'sorted-asc' : 'sorted-desc';
                    header.classList.add(directionClass);
                    header.dataset.sortState = facultySortState.direction;
                }
            });
        }

        // Helper function to update pagination info
        function updatePaginationInfo(meta) {
            const infoElement = document.querySelector('#facultyPagination .pagination-info');
            if (infoElement && meta.total !== undefined) {
                const from = meta.from || 0;
                const to = meta.to || 0;
                const total = meta.total || 0;

                infoElement.textContent = `Showing ${from} to ${to} of ${total} entries`;
            }
        }

        // Bind pagination events
        function bindPaginationEvents() {
            const paginationLinks = document.querySelectorAll('#facultyPagination .pagination a');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Extract page number from URL or data attribute
                    let page = 1;
                    if (this.href) {
                        const url = new URL(this.href);
                        page = url.searchParams.get('page') || 1;
                    } else if (this.dataset.page) {
                        page = this.dataset.page;
                    }

                    const perPage = document.getElementById('facultyPerPage').value;
                    const search = document.getElementById('facultySearch').value;

                    // Load the new page
                    loadFacultyData(currentDepartment, page, perPage, search);
                });
            });
        }

        function bindDepartmentExportButton() {
            const button = document.getElementById('departmentExportBtn');
            const departmentInput = document.getElementById('departmentExportDepartment');
            const modalTitle = document.getElementById('departmentExportModalLabel');
            const exportModalEl = document.getElementById('departmentExportModal');
            const facultyModalEl = document.getElementById('facultyModal');
            if (!button || !departmentInput) {
                return;
            }

            button.addEventListener('click', () => {
                if (!currentDepartment) {
                    return;
                }
                departmentInput.value = currentDepartment;
                if (modalTitle) {
                    modalTitle.textContent = `Export Responses - ${currentDepartment}`;
                }
                if (exportModalEl) {
                    const exportModal = bootstrap.Modal.getOrCreateInstance(exportModalEl, {
                        backdrop: false,
                        focus: true,
                    });
                    exportModal.show();
                }
            });

            if (exportModalEl) {
                exportModalEl.addEventListener('hidden.bs.modal', () => {
                    if (facultyModalEl && facultyModalEl.classList.contains('show')) {
                        document.body.classList.add('modal-open');
                    }
                });
            }
        }

        // Faculty pagination function for direct page calls
        function goToFacultyPage(page) {
            const perPage = document.getElementById('facultyPerPage').value || 10;
            const search = document.getElementById('facultySearch').value || '';
            loadFacultyData(currentDepartment, page, perPage, search);
        }

        // Faculty search and pagination handlers
        document.addEventListener('DOMContentLoaded', function () {
            // Faculty per page change
            document.getElementById('facultyPerPage').addEventListener('change', function () {
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, this.value, document.getElementById('facultySearch').value);
                }
            });

            document.getElementById('facultyRatingFilter').addEventListener('change', function () {
                facultyFilters.rating = this.value || 'all';
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, document.getElementById('facultySearch').value);
                }
            });

            document.getElementById('facultyStatusFilter').addEventListener('change', function () {
                facultyFilters.status = this.value || 'all';
                if (currentDepartment) {
                    loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, document.getElementById('facultySearch').value);
                }
            });

            // Faculty search with debouncing
            let searchTimeout;
            document.getElementById('facultySearch').addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (currentDepartment) {
                        loadFacultyData(currentDepartment, 1, document.getElementById('facultyPerPage').value, this.value);
                    }
                }, 500);
            });
            bindDepartmentExportButton();
        });

        // Form auto-submit on filter change
        document.querySelectorAll('select[name="department"], select[name="academic_year"], select[name="semester"]').forEach(select => {
            select.addEventListener('change', function () {
                // Add loading state
                const button = document.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                button.disabled = true;

                // Submit form
                this.form.submit();
            });
        });

        // Progress bar animations
        function animateProgressBars() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.transition = 'width 1s ease-in-out';
                    bar.style.width = width;
                }, 100);
            });
        }

        // Initialize animations on load
        document.addEventListener('DOMContentLoaded', function () {
            animateProgressBars();
        });

        // Auto-refresh every 5 minutes (only if modal is not open)
        setInterval(function () {
            const modal = document.getElementById('facultyModal');
            if (!modal.classList.contains('show')) {
                location.reload();
            }
        }, 300000);

        // Utility function to decode HTML entities
        function decodeHtmlEntities(str) {
            const textArea = document.createElement('textarea');
            textArea.innerHTML = str;
            return textArea.value;
        }

        // Alternative method for handling JSON response with escaped HTML
        function parseJsonResponse(data) {
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    return null;
                }
            }

            if (data.html) {
                // Decode HTML entities if they exist
                data.html = decodeHtmlEntities(data.html);
            }

            if (data.pagination) {
                // Decode pagination HTML entities if they exist
                data.pagination = decodeHtmlEntities(data.pagination);
            }

            return data;
        }

        // Enhanced error handling
        window.addEventListener('error', function (e) {
            console.error('JavaScript error:', e.error);
        });

        // Add CSS animations and styles
        const style = document.createElement('style');
        style.textContent = `
                                                @keyframes pulse {
                                                    0% { transform: scale(1); }
                                                    50% { transform: scale(1.05); }
                                                    100% { transform: scale(1); }
                                                }

                                                @keyframes fadeIn {
                                                    from { opacity: 0; }
                                                    to { opacity: 1; }
                                                }

                                                .progress-bar {
                                                    transition: width 0.8s ease-in-out;
                                                }

                                                .metric-card {
                                                    transition: all 0.3s ease;
                                                }

                                                .metric-card:hover {
                                                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                                                }

                                                .department-row:hover {
                                                    background-color: #f8f9fa;
                                                }

                                                .modal-xl {
                                                    max-width: 1200px;
                                                }

                                                .table-responsive {
                                                    animation: fadeIn 0.3s ease-in-out;
                                                }

                                                .faculty-modal-table thead th.sortable {
                                                    cursor: pointer;
                                                    user-select: none;
                                                }

                                                #facultyModal.modal {
                                                    z-index: 1055;
                                                }

                                                #departmentExportModal.modal {
                                                    z-index: 1070;
                                                }

                                                .modal-backdrop {
                                                    z-index: 1050;
                                                }

                                                .faculty-sort-wrapper {
                                                    display: inline-flex;
                                                    align-items: center;
                                                    gap: 0.35rem;
                                                }

                                                .faculty-sort-indicator {
                                                    display: inline-flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    width: 12px;
                                                    color: #94a3b8;
                                                    font-size: 0.65rem;
                                                }

                                                .faculty-sort-indicator i {
                                                    display: none;
                                                }

                                                .faculty-modal-table thead th.sorted-asc .faculty-sort-indicator,
                                                .faculty-modal-table thead th.sorted-desc .faculty-sort-indicator {
                                                    color: #1d4ed8;
                                                }

                                                .faculty-modal-table thead th.sorted-asc .faculty-sort-indicator .icon-up,
                                                .faculty-modal-table thead th.sorted-desc .faculty-sort-indicator .icon-down {
                                                    display: inline-flex;
                                                }

                                                .pagination {
                                                    justify-content: center;
                                                }

                                                .pagination .page-link {
                                                    transition: all 0.2s ease;
                                                }

                                                .pagination .page-link:hover {
                                                    transform: translateY(-1px);
                                                }

                                                .spinner-border-sm {
                                                    width: 1rem;
                                                    height: 1rem;
                                                }

                                                .loading-overlay {
                                                    position: relative;
                                                }

                                                .loading-overlay::after {
                                                    content: '';
                                                    position: absolute;
                                                    top: 0;
                                                    left: 0;
                                                    right: 0;
                                                    bottom: 0;
                                                    background: rgba(255, 255, 255, 0.8);
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    z-index: 10;
                                                }
                                            `;
        document.head.appendChild(style);

        // Debug function to check response format
        function debugResponse(response) {
            console.log('Response type:', typeof response);
            console.log('Response content:', response);

            if (typeof response === 'string') {
                try {
                    const parsed = JSON.parse(response);
                    console.log('Parsed JSON:', parsed);
                    return parsed;
                } catch (e) {
                    console.error('JSON parse error:', e);
                    return null;
                }
            }

            return response;
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/postclasssurvey.mcu.edu.ph/resources/views/content/dashboard/dashboard-reports.blade.php ENDPATH**/ ?>