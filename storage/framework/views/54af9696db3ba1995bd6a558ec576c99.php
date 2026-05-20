<?php $__env->startSection('title', 'Post-Class Survey Dashboard'); ?>

<?php $__env->startSection('vendor-style'); ?>
  <?php echo app('Illuminate\Foundation\Vite')('resources/assets/vendor/libs/apex-charts/apex-charts.scss'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('vendor-script'); ?>
  <?php echo app('Illuminate\Foundation\Vite')('resources/assets/vendor/libs/apex-charts/apexcharts.js'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="row">
    <!-- Welcome Card -->
    <div class="col-xxl-8 mb-6 order-0">
      <div class="card">
        <div class="d-flex align-items-start row">
          <div class="col-sm-7">
            <div class="card-body">
              <h5 class="card-title text-primary mb-3">Welcome to Faculty Evaluation System! 📊</h5>
              <p class="mb-6">Track faculty performance and student feedback.<br>Academic Year: <?php echo e($currentAcademicYear); ?>

                - <?php echo e($currentSemester); ?> Semester</p>
              <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Reports</a>
            </div>
          </div>
          <div class="col-sm-5 text-center text-sm-left">
            <div class="card-body pb-0 px-0 px-md-6">
              <img src="<?php echo e(asset('assets/img/illustrations/man-with-laptop.png')); ?>" height="175" class="scaleX-n1-rtl"
                alt="Dashboard">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="col-lg-4 col-md-4 order-1">
      <div class="row">
        <div class="col-lg-6 col-md-12 col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="<?php echo e(asset('assets/img/icons/unicons/chart-success.png')); ?>" alt="chart success" class="rounded">
                </div>
              </div>
              <p class="mb-1">Total Responses</p>
              <h4 class="card-title mb-3"><?php echo e(number_format($totalResponses)); ?></h4>
              <small class="text-success fw-medium">
                <i class='bx bx-up-arrow-alt'></i> <?php echo e($recentResponses); ?> this week
              </small>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="<?php echo e(asset('assets/img/icons/unicons/wallet-info.png')); ?>" alt="wallet info" class="rounded">
                </div>
              </div>
              <p class="mb-1">Avg. Rating</p>
              <h4 class="card-title mb-3"><?php echo e(number_format($averageRating, 2)); ?>/4</h4>
              <small class="text-info fw-medium">
                <i class='bx bx-star'></i> Overall effectiveness
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Response Trends -->
    <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
      <div class="card">
        <div class="row row-bordered g-0">
          <div class="col-lg-8">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div class="card-title mb-0">
                <h5 class="m-0 me-2">Response Trends (Last 30 Days)</h5>
              </div>
            </div>
            <div id="responseTrendChart" class="px-3"></div>
          </div>
          <div class="col-lg-4 d-flex align-items-center">
            <div class="card-body px-xl-9">
              <div class="text-center mb-6">
                <div class="btn-group">
                  <button type="button" class="btn btn-outline-primary">
                    <?php echo e(date('Y')); ?>

                  </button>
                </div>
              </div>

              <div id="ratingDistributionChart"></div>
              <div class="text-center fw-medium my-6">
                <?php if($monthlyGrowth > 0): ?>
                  <span class="text-success">+<?php echo e(number_format($monthlyGrowth, 1)); ?>%</span>
                <?php else: ?>
                  <span class="text-danger"><?php echo e(number_format($monthlyGrowth, 1)); ?>%</span>
                <?php endif; ?>
                Monthly Growth
              </div>

              <div class="d-flex gap-3 justify-content-between">
                <div class="d-flex">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded-2 bg-label-primary">
                      <i class="bx bx-calendar bx-lg text-primary"></i>
                    </span>
                  </div>
                  <div class="d-flex flex-column">
                    <small>This Month</small>
                    <h6 class="mb-0"><?php echo e($thisMonth); ?></h6>
                  </div>
                </div>
                <div class="d-flex">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded-2 bg-label-info">
                      <i class="bx bx-time bx-lg text-info"></i>
                    </span>
                  </div>
                  <div class="d-flex flex-column">
                    <small>Last Month</small>
                    <h6 class="mb-0"><?php echo e($lastMonth); ?></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
      <div class="row">
        <div class="col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="<?php echo e(asset('assets/img/icons/unicons/paypal.png')); ?>" alt="faculty" class="rounded">
                </div>
              </div>
              <p class="mb-1">Total Faculties</p>
              <h4 class="card-title mb-3"><?php echo e($totalFaculties); ?></h4>
              <small class="text-muted fw-medium">Active faculty members</small>
            </div>
          </div>
        </div>
        <div class="col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-title d-flex align-items-start justify-content-between mb-4">
                <div class="avatar flex-shrink-0">
                  <img src="<?php echo e(asset('assets/img/icons/unicons/cc-primary.png')); ?>" alt="courses" class="rounded">
                </div>
              </div>
              <p class="mb-1">Total Courses</p>
              <h4 class="card-title mb-3"><?php echo e($totalCourses); ?></h4>
              <small class="text-muted fw-medium">Available subjects</small>
            </div>
          </div>
        </div>
        <div class="col-12 mb-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10">
                <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                  <div class="card-title mb-6">
                    <h5 class="text-nowrap mb-1">Active Evaluations</h5>
                    <span class="badge bg-label-success">CURRENT TERM</span>
                  </div>
                  <div class="mt-sm-auto">
                    <h4 class="mb-0"><?php echo e($totalEvaluations); ?></h4>
                    <span class="text-muted">Forms available</span>
                  </div>
                </div>
                <div id="activeEvaluationsChart"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Top Rated Faculties -->
    <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1 me-2">Top Rated Faculties</h5>
            <p class="card-subtitle">This Month</p>
          </div>
        </div>
        <div class="card-body">
          <ul class="p-0 m-0">
            <?php $__empty_1 = true; $__currentLoopData = $topRatedFaculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <li class="d-flex align-items-center mb-5">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class='bx bx-user'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                  <div class="me-2">
                    <h6 class="mb-0"><?php echo e($faculty->name); ?></h6>
                    <small><?php echo e($faculty->department); ?></small>
                  </div>
                  <div class="user-progress">
                    <span class="badge bg-label-success"><?php echo e(number_format($faculty->avg_rating, 2)); ?></span>
                    <small class="text-muted">(<?php echo e($faculty->response_count); ?> responses)</small>
                  </div>
                </div>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <li class="text-center text-muted">
                <p>No data available yet</p>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- Department Statistics -->
    <div class="col-md-6 col-lg-4 order-1 mb-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">Department Performance</h5>
        </div>
        <div class="card-body">
          <ul class="p-0 m-0">
            <?php $__empty_1 = true; $__currentLoopData = $departmentStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <li class="d-flex align-items-center mb-4">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class='bx bx-buildings'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                  <div class="me-2">
                    <h6 class="mb-0"><?php echo e($dept->department ?? 'Not Assigned'); ?></h6>
                    <small><?php echo e($dept->faculty_count); ?> faculty members</small>
                  </div>
                  <div class="user-progress">
                    <h6 class="mb-0"><?php echo e($dept->response_count); ?></h6>
                    <small class="text-muted">responses</small>
                  </div>
                </div>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <li class="text-center text-muted">
                <p>No department data available</p>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- Recent Feedback -->
    <div class="col-md-6 col-lg-4 order-2 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Recent Feedback</h5>
        </div>
        <div class="card-body pt-4">
          <ul class="p-0 m-0">
            <?php $__empty_1 = true; $__currentLoopData = $recentFeedback; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <li class="d-flex align-items-start mb-4">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class='bx bx-message-dots'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap flex-column gap-1">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><?php echo e($feedback->evaluation->faculty->name ?? 'Unknown Faculty'); ?></h6>
                    <small class="text-muted"><?php echo e($feedback->created_at->diffForHumans()); ?></small>
                  </div>
                  <small class="text-muted mb-1">
                    <?php echo e($feedback->schedule->facultyCourse->course->subject_code ?? 'N/A'); ?> - Rating:
                    <?php echo e($feedback->effectiveness_rating); ?>/4
                  </small>
                  <p class="mb-0 small">
                    <?php echo e(Str::limit($feedback->feedback_comments, 60)); ?>

                  </p>
                </div>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <li class="text-center text-muted">
                <p>No recent feedback available</p>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Course Performance Table -->
  <?php if($coursePerformance->count() > 0): ?>
    <div class="row">
      <div class="col-12 mb-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Course Performance Overview</h5>
            <small class="text-muted">Top performing courses</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th>Class Code</th>
                    <th>Subject Code</th>
                    <th>Average Rating</th>
                    <th>Total Responses</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $coursePerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                      <td><?php echo e($course->class_code); ?></td>
                      <td><?php echo e($course->subject_code); ?></td>
                      <td>
                        <span
                          class="badge bg-label-<?php echo e($course->avg_rating >= 3.5 ? 'success' : ($course->avg_rating >= 2.5 ? 'warning' : 'danger')); ?>">
                          <?php echo e(number_format($course->avg_rating, 2)); ?>/4
                        </span>
                      </td>
                      <td><?php echo e($course->response_count); ?></td>
                      <td>
                        <?php if($course->avg_rating >= 3.5): ?>
                          <span class="badge bg-label-success">Excellent</span>
                        <?php elseif($course->avg_rating >= 2.5): ?>
                          <span class="badge bg-label-warning">Good</span>
                        <?php else: ?>
                          <span class="badge bg-label-danger">Needs Improvement</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-script'); ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Response Trend Chart
      const responseTrendData = <?php echo json_encode($dailyResponses->pluck('count'), 15, 512) ?>;
      const responseTrendDates = <?php echo json_encode($dailyResponses->pluck('date'), 15, 512) ?>;

      if (document.getElementById('responseTrendChart')) {
        const responseTrendChart = new ApexCharts(document.getElementById('responseTrendChart'), {
          chart: {
            type: 'area',
            height: 300,
            toolbar: { show: false }
          },
          series: [{
            name: 'Responses',
            data: responseTrendData
          }],
          xaxis: {
            categories: responseTrendDates,
            type: 'datetime'
          },
          colors: ['#696cff'],
          fill: {
            type: 'gradient',
            gradient: {
              shade: 'light',
              type: 'vertical',
              opacityFrom: 0.7,
              opacityTo: 0.3
            }
          },
          stroke: {
            curve: 'smooth',
            width: 2
          }
        });
        responseTrendChart.render();
      }

      // Rating Distribution Chart
      const ratingData = <?php echo json_encode($ratingDistribution->pluck('count'), 15, 512) ?>;
      const ratingLabels = <?php echo json_encode($ratingDistribution->pluck('effectiveness_rating'), 15, 512) ?>;

      if (document.getElementById('ratingDistributionChart')) {
        const ratingChart = new ApexCharts(document.getElementById('ratingDistributionChart'), {
          chart: {
            type: 'donut',
            height: 200
          },
          series: ratingData,
          labels: ratingLabels.map(rating => {
            const labels = { '1': 'Not Effective', '2': 'Somewhat', '3': 'Effective', '4': 'Very Effective' };
            return labels[rating] || 'Unknown';
          }),
          colors: ['#ff4c51', '#ff9f00', '#28c76f', '#00d4aa'],
          legend: { show: false },
          plotOptions: {
            pie: {
              donut: {
                size: '70%'
              }
            }
          }
        });
        ratingChart.render();
      }

      // Active Evaluations Mini Chart
      if (document.getElementById('activeEvaluationsChart')) {
        const activeChart = new ApexCharts(document.getElementById('activeEvaluationsChart'), {
          chart: {
            type: 'radialBar',
            height: 100,
            sparkline: { enabled: true }
          },
          series: [<?php echo e(round(($totalEvaluations / ($totalFaculties > 0 ? $totalFaculties : 1)) * 100)); ?>],
          plotOptions: {
            radialBar: {
              hollow: { size: '50%' },
              dataLabels: {
                name: { show: false },
                value: {
                  show: true,
                  fontSize: '14px',
                  fontWeight: 'bold'
                }
              }
            }
          },
          colors: ['#28c76f']
        });
        activeChart.render();
      }
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\post-class-survey\resources\views/content/dashboard/dashboards.blade.php ENDPATH**/ ?>