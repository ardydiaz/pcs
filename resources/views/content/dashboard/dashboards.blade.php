@extends('layouts/contentNavbarLayout')

@section('title', 'Post-Class Survey Dashboard')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('content')
  <div class="row">
    <!-- Welcome Card -->
    <div class="col-xxl-8 mb-6 order-0">
      <div class="card">
        <div class="d-flex align-items-start row">
          <div class="col-sm-7">
            <div class="card-body">
              <h5 class="card-title text-primary mb-3">Welcome to Faculty Evaluation System! 📊</h5>
              <p class="mb-6">Track faculty performance and student feedback.<br>Academic Year: {{ $currentAcademicYear ?? '' }}
                - {{ $currentSemester ?? '' }}</p>
              <a href="{{ route('reports') }}" class="btn btn-sm btn-outline-primary">View Reports</a>
            </div>
          </div>
          <div class="col-sm-5 text-center text-sm-left">
            <div class="card-body pb-0 px-0 px-md-6">
              <img src="{{asset('assets/img/illustrations/man-with-laptop.png')}}" height="175" class="scaleX-n1-rtl"
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
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="avatar flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="fa-solid fa-chart-column text-success"></i>
                  </span>
                </div>
                <p class="mb-0">Total Responses</p>
              </div>
              <h4 class="card-title mb-3">{{ number_format($totalResponses) }}</h4>
              <small class="text-success fw-medium">
                <i class='bx bx-up-arrow-alt'></i> {{ $recentResponses }} this week
              </small>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="avatar flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="fa-solid fa-star text-info"></i>
                  </span>
                </div>
                <p class="mb-0">Avg. Rating</p>
              </div>
              <h4 class="card-title mb-3">{{ number_format($averageRating, 2) }}/4</h4>
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
                    {{ date('Y') }}
                  </button>
                </div>
              </div>

              <div id="ratingDistributionChart"></div>
              <div class="text-center fw-medium my-6">
                @if($monthlyGrowth > 0)
                  <span class="text-success">+{{ number_format($monthlyGrowth, 1) }}%</span>
                @else
                  <span class="text-danger">{{ number_format($monthlyGrowth, 1) }}%</span>
                @endif
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
                    <h6 class="mb-0">{{ $thisMonth }}</h6>
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
                    <h6 class="mb-0">{{ $lastMonth }}</h6>
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
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="avatar flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="fa-solid fa-user-group text-primary"></i>
                  </span>
                </div>
                <p class="mb-0">Total Faculties</p>
              </div>
              <h4 class="card-title mb-3">{{ $totalFaculties }}</h4>
              <small class="text-muted fw-medium">Active faculty members</small>
            </div>
          </div>
        </div>
        <div class="col-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <div class="avatar flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="fa-solid fa-book-open text-warning"></i>
                  </span>
                </div>
                <p class="mb-0">Total Courses</p>
              </div>
              <h4 class="card-title mb-3">{{ $totalCourses }}</h4>
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
                    <h4 class="mb-0">{{ $totalEvaluations }}</h4>
                    <span class="text-muted">Forms available</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Top Rated Faculties -->
    <div class="col-12 col-lg-6 order-0 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1 me-2">Top Rated Faculties</h5>
            <p class="card-subtitle">This Month</p>
          </div>
        </div>
        <div class="card-body">
          <ul class="p-0 m-0">
            @forelse($topRatedFaculties as $faculty)
              @php
                $facultyDepartments = collect(explode(',', $faculty->department ?? ''))
                  ->map(function ($value) {
                    return trim($value);
                  })
                  ->filter(function ($value) {
                    return $value !== '';
                  })
                  ->values();
              @endphp
              <li class="d-flex align-items-center mb-5">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class='bx bx-user'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                  <div class="me-2">
                    <h6 class="mb-0">{{ $faculty->name }}</h6>
                    <div class="d-flex flex-wrap gap-1">
                      @forelse($facultyDepartments as $department)
                        <span class="badge bg-label-secondary">{{ $department }}</span>
                      @empty
                        <span class="text-muted">No department</span>
                      @endforelse
                    </div>
                  </div>
                  <div class="user-progress">
                    <span class="badge bg-label-success">{{ number_format($faculty->avg_rating, 2) }}</span>
                    <small class="text-muted">({{ $faculty->response_count }} responses)</small>
                  </div>
                </div>
              </li>
            @empty
              <li class="text-center text-muted">
                <p>No data available yet</p>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <!-- Low Rated Faculties -->
    <div class="col-12 col-lg-6 order-1 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1 me-2">Low Rated Faculties</h5>
            <p class="card-subtitle">This Month</p>
          </div>
        </div>
        <div class="card-body">
          <ul class="p-0 m-0">
            @forelse($lowRatedFaculties as $faculty)
              @php
                $facultyDepartments = collect(explode(',', $faculty->department ?? ''))
                  ->map(function ($value) {
                    return trim($value);
                  })
                  ->filter(function ($value) {
                    return $value !== '';
                  })
                  ->values();
              @endphp
              <li class="d-flex align-items-center mb-5">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-danger">
                    <i class='bx bx-user'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                  <div class="me-2">
                    <h6 class="mb-0">{{ $faculty->name }}</h6>
                    <div class="d-flex flex-wrap gap-1">
                      @forelse($facultyDepartments as $department)
                        <span class="badge bg-label-secondary">{{ $department }}</span>
                      @empty
                        <span class="text-muted">No department</span>
                      @endforelse
                    </div>
                  </div>
                  <div class="user-progress">
                    <span class="badge bg-label-danger">{{ number_format($faculty->avg_rating, 2) }}</span>
                    <small class="text-muted">({{ $faculty->response_count }} responses)</small>
                  </div>
                </div>
              </li>
            @empty
              <li class="text-center text-muted">
                <p>No data available yet</p>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <!-- Department Statistics -->
    <div class="col-12 col-lg-6 order-2 mb-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title m-0 me-2">Department Performance</h5>
        </div>
        <div class="card-body">
          <ul class="p-0 m-0">
            @forelse($departmentStats as $dept)
              <li class="d-flex align-items-center mb-4">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class='bx bx-buildings'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                  <div class="me-2">
                    <h6 class="mb-0">{{ $dept->department ?? 'Not Assigned' }}</h6>
                    <small>{{ $dept->faculty_count }} faculty members</small>
                  </div>
                  <div class="user-progress">
                    <h6 class="mb-0">{{ $dept->response_count }}</h6>
                    <small class="text-muted">responses</small>
                  </div>
                </div>
              </li>
            @empty
              <li class="text-center text-muted">
                <p>No department data available</p>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <!-- Recent Feedback -->
    <div class="col-12 col-lg-6 order-3 mb-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0 me-2">Recent Feedback</h5>
        </div>
        <div class="card-body pt-4">
          <ul class="p-0 m-0">
            @forelse($recentFeedback as $feedback)
              <li class="d-flex align-items-start mb-4">
                <div class="avatar flex-shrink-0 me-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class='bx bx-message-dots'></i>
                  </span>
                </div>
                <div class="d-flex w-100 flex-wrap flex-column gap-1">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ $feedback->evaluation->resolved_faculty_name ?? 'Unknown Faculty' }}</h6>
                    <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                  </div>
                  <small class="text-muted mb-1">
                    {{ $feedback->resolved_course_name }} - Rating:
                    {{ $feedback->effectiveness_rating }}/4
                  </small>
                  <p class="mb-0 small">
                    {{ Str::limit($feedback->feedback_comments, 60) }}
                  </p>
                </div>
              </li>
            @empty
              <li class="text-center text-muted">
                <p>No recent feedback available</p>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Course Performance Table -->
  @if($coursePerformance->count() > 0)
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
                  @foreach($coursePerformance as $course)
                    <tr>
                      <td>{{ $course->class_code }}</td>
                      <td>{{ $course->subject_code }}</td>
                      <td>
                        <span
                          class="badge bg-label-{{ $course->avg_rating >= 3.5 ? 'success' : ($course->avg_rating >= 2.5 ? 'warning' : 'danger') }}">
                          {{ number_format($course->avg_rating, 2) }}/4
                        </span>
                      </td>
                      <td>{{ $course->response_count }}</td>
                      <td>
                        @if($course->avg_rating >= 3.5)
                          <span class="badge bg-label-success">Excellent</span>
                        @elseif($course->avg_rating >= 2.5)
                          <span class="badge bg-label-warning">Good</span>
                        @else
                          <span class="badge bg-label-danger">Needs Improvement</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Response Trend Chart
      const responseTrendData = @json($dailyResponses->pluck('count'));
      const responseTrendDates = @json($dailyResponses->pluck('date'));

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
      const ratingData = @json($ratingDistribution->pluck('count'));
      const ratingLabels = @json($ratingDistribution->pluck('effectiveness_rating'));

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

      const pagedLists = document.querySelectorAll('[data-paged-list]');
      pagedLists.forEach(list => {
        const listId = list.getAttribute('data-paged-list');
        const pageSize = parseInt(list.getAttribute('data-page-size') || '10', 10);
        const items = Array.from(list.querySelectorAll('li')).filter(item => !item.hasAttribute('data-paged-list-empty'));
        const footer = document.querySelector(`[data-paged-list-footer="${listId}"]`);
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
      });
    });
  </script>
@endsection
