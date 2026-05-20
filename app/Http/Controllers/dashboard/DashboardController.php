<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\EvaluationResponse;
use App\Models\Schedule;
use App\Models\FacultyCourse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();
    $isAdmin = $user && $user->role === 'Admin';
    $accessLevels = collect($user?->access_level ?? []);
    $canViewAllReports = $isAdmin;

    $departmentScope = $this->resolveDepartmentScope($user);
    if (!$canViewAllReports && empty($departmentScope)) {
      $departmentScope = ['__none__'];
    }

    // Get current academic year and semester (you might want to make this dynamic)
    $currentAcademicYear = '2024-2025'; // Adjust as needed
    $currentSemester = '1st'; // Adjust as needed

    // Total Statistics
    $totalFacultiesQuery = Faculty::query();
    if (!$canViewAllReports) {
      $totalFacultiesQuery->whereHas('user', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'users.department');
      });
    }
    $totalFaculties = $totalFacultiesQuery->count();

    $facultyIds = collect();
    if (!$canViewAllReports) {
      $facultyIds = Faculty::whereHas('user', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'users.department');
      })->pluck('id');
    }

    if (!$canViewAllReports) {
      $totalCourses = $facultyIds->isEmpty()
        ? 0
        : Course::join('faculty_courses', 'courses.id', '=', 'faculty_courses.course_id')
          ->whereIn('faculty_courses.faculty_id', $facultyIds)
          ->distinct('courses.id')
          ->count('courses.id');
    } else {
      $totalCourses = Course::count();
    }

    $totalEvaluationsQuery = Evaluation::where('is_active', true);
    if (!$canViewAllReports) {
      $this->applyDepartmentFilter($totalEvaluationsQuery, $departmentScope, 'faculty_department_snapshot');
    }
    $totalEvaluations = $totalEvaluationsQuery->count();

    $totalResponsesQuery = EvaluationResponse::query();
    if (!$canViewAllReports) {
      $totalResponsesQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $totalResponses = $totalResponsesQuery->count();

    // Recent Activity (Last 7 days)
    $recentResponsesQuery = EvaluationResponse::where('created_at', '>=', Carbon::now()->subDays(7));
    if (!$canViewAllReports) {
      $recentResponsesQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $recentResponses = $recentResponsesQuery->count();

    // Average Effectiveness Rating
    $averageRatingQuery = EvaluationResponse::query();
    if (!$canViewAllReports) {
      $averageRatingQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $averageRating = $averageRatingQuery->avg('effectiveness_rating');

    // Response Rate by Department
    $facultyResponseCountsQuery = EvaluationResponse::join('evaluations', 'evaluation_responses.evaluation_id', '=', 'evaluations.id');
    if (!$canViewAllReports) {
      $this->applyDepartmentFilter($facultyResponseCountsQuery, $departmentScope, 'evaluations.faculty_department_snapshot');
    }
    $facultyResponseCounts = $facultyResponseCountsQuery
      ->select('evaluations.faculty_id', DB::raw('COUNT(evaluation_responses.id) as response_count'))
      ->groupBy('evaluations.faculty_id')
      ->pluck('response_count', 'evaluations.faculty_id');

    $departmentStatsMap = [];
    $facultiesQuery = Faculty::with('user:id,department');
    if (!$canViewAllReports) {
      $facultiesQuery->whereHas('user', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'users.department');
      });
    }
    $faculties = $facultiesQuery->get();
    foreach ($faculties as $faculty) {
      $departmentRaw = trim($faculty->user->department ?? $faculty->department ?? '');
      if ($departmentRaw === '') {
        continue;
      }
      $departments = collect(explode(',', $departmentRaw))
        ->map(function ($value) {
          return trim($value);
        })
        ->filter(function ($value) {
          return $value !== '';
        })
        ->values();
      if ($departments->isEmpty()) {
        continue;
      }
      $responseCount = (int) ($facultyResponseCounts[$faculty->user_id] ?? 0);
      foreach ($departments as $department) {
        if (!isset($departmentStatsMap[$department])) {
          $departmentStatsMap[$department] = [
            'department' => $department,
            'faculty_ids' => collect(),
            'response_count' => 0,
          ];
        }
        if (!$departmentStatsMap[$department]['faculty_ids']->contains($faculty->id)) {
          $departmentStatsMap[$department]['faculty_ids']->push($faculty->id);
        }
        $departmentStatsMap[$department]['response_count'] += $responseCount;
      }
    }

    $departmentStats = collect($departmentStatsMap)
      ->map(function ($entry) {
        return (object) [
          'department' => $entry['department'],
          'faculty_count' => $entry['faculty_ids']->count(),
          'response_count' => $entry['response_count'],
        ];
      })
      ->sortByDesc('response_count')
      ->take(5)
      ->values();

    // Top Rated Faculties (This month)
    $topRatedFaculties = User::join('evaluations', 'users.id', '=', 'evaluations.faculty_id')
      ->join('evaluation_responses', 'evaluations.id', '=', 'evaluation_responses.evaluation_id')
      ->select('users.name', 'users.department')
      ->selectRaw('AVG(evaluation_responses.effectiveness_rating) as avg_rating')
      ->selectRaw('COUNT(evaluation_responses.id) as response_count')
      ->where('evaluation_responses.created_at', '>=', Carbon::now()->startOfMonth())
      ->when(!$canViewAllReports, function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'evaluations.faculty_department_snapshot');
      })
      ->groupBy('users.id', 'users.name', 'users.department')
      ->having('response_count', '>=', 3) // At least 3 responses
      ->orderByDesc('avg_rating')
      ->limit(5)
      ->get();

    // Low Rated Faculties (This month)
    $lowRatedFaculties = User::join('evaluations', 'users.id', '=', 'evaluations.faculty_id')
      ->join('evaluation_responses', 'evaluations.id', '=', 'evaluation_responses.evaluation_id')
      ->select('users.name', 'users.department')
      ->selectRaw('AVG(evaluation_responses.effectiveness_rating) as avg_rating')
      ->selectRaw('COUNT(evaluation_responses.id) as response_count')
      ->where('evaluation_responses.created_at', '>=', Carbon::now()->startOfMonth())
      ->when(!$canViewAllReports, function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'evaluations.faculty_department_snapshot');
      })
      ->groupBy('users.id', 'users.name', 'users.department')
      ->having('response_count', '>=', 3) // At least 3 responses
      ->orderBy('avg_rating')
      ->limit(5)
      ->get();

    // Daily Response Trend (Last 30 days)
    $dailyResponsesQuery = EvaluationResponse::select(
      DB::raw('DATE(created_at) as date'),
      DB::raw('COUNT(*) as count')
    )->where('created_at', '>=', Carbon::now()->subDays(30));
    if (!$canViewAllReports) {
      $dailyResponsesQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $dailyResponses = $dailyResponsesQuery
      ->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get();

    // Rating Distribution
    $ratingDistributionQuery = EvaluationResponse::select('effectiveness_rating')
      ->selectRaw('COUNT(*) as count')
      ->groupBy('effectiveness_rating')
      ->orderBy('effectiveness_rating');
    if (!$canViewAllReports) {
      $ratingDistributionQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $ratingDistribution = $ratingDistributionQuery->get();

    // Course Performance
    $coursePerformanceQuery = Course::join('faculty_courses', 'courses.id', '=', 'faculty_courses.course_id')
      ->join('schedules', 'faculty_courses.id', '=', 'schedules.faculty_course_id')
      ->join('evaluation_responses', 'schedules.id', '=', 'evaluation_responses.schedule_id')
      ->join('evaluations', 'evaluation_responses.evaluation_id', '=', 'evaluations.id')
      ->select('courses.class_code', 'courses.subject_code')
      ->selectRaw('AVG(evaluation_responses.effectiveness_rating) as avg_rating')
      ->selectRaw('COUNT(evaluation_responses.id) as response_count')
      ->groupBy('courses.id', 'courses.class_code', 'courses.subject_code')
      ->having('response_count', '>=', 2)
      ->orderByDesc('avg_rating')
      ->limit(10);
    if (!$canViewAllReports) {
      $this->applyDepartmentFilter($coursePerformanceQuery, $departmentScope, 'evaluations.faculty_department_snapshot');
      $coursePerformance = $coursePerformanceQuery->get();
    } else {
      $coursePerformance = $coursePerformanceQuery->get();
    }

    // Recent Feedback Comments
    $recentFeedbackQuery = EvaluationResponse::with(['evaluation.faculty', 'schedule.facultyCourse.course'])
      ->whereNotNull('feedback_comments')
      ->where('feedback_comments', '!=', '')
      ->orderBy('created_at', 'desc')
      ->limit(5);
    if (!$canViewAllReports) {
      $recentFeedbackQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $recentFeedback = $recentFeedbackQuery->get();

    // Monthly comparison
    $thisMonthQuery = EvaluationResponse::where('created_at', '>=', Carbon::now()->startOfMonth());
    $lastMonthQuery = EvaluationResponse::whereBetween('created_at', [
      Carbon::now()->subMonth()->startOfMonth(),
      Carbon::now()->subMonth()->endOfMonth()
    ]);
    if (!$canViewAllReports) {
      $thisMonthQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
      $lastMonthQuery->whereHas('evaluation', function ($query) use ($departmentScope) {
        $this->applyDepartmentFilter($query, $departmentScope, 'faculty_department_snapshot');
      });
    }
    $thisMonth = $thisMonthQuery->count();
    $lastMonth = $lastMonthQuery->count();

    $monthlyGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

    // Active Evaluations
    $activeEvaluationsQuery = Evaluation::where('is_active', true)
      ->with([
        'faculty',
        'responses' => function ($query) {
          $query->where('created_at', '>=', Carbon::now()->subDays(7));
        }
      ]);
    if (!$canViewAllReports) {
      $this->applyDepartmentFilter($activeEvaluationsQuery, $departmentScope, 'faculty_department_snapshot');
    }
    $activeEvaluations = $activeEvaluationsQuery->get();

    return view('content.dashboard.dashboards', compact(
      'totalFaculties',
      'totalCourses',
      'totalEvaluations',
      'totalResponses',
      'recentResponses',
      'averageRating',
      'departmentStats',
      'topRatedFaculties',
      'lowRatedFaculties',
      'dailyResponses',
      'ratingDistribution',
      'coursePerformance',
      'recentFeedback',
      'monthlyGrowth',
      'thisMonth',
      'lastMonth',
      'activeEvaluations',
      'currentAcademicYear',
      'currentSemester'
    ));
  }

  private function normalizeDepartmentList(?string $departments): array
  {
    $departments = trim((string) ($departments ?? ''));
    if ($departments === '') {
      return [];
    }

    $decoded = json_decode($departments, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
      $items = $decoded;
    } else {
      $items = preg_split('/\s*,\s*/', $departments);
    }

    $normalized = array_map(static fn ($value) => trim((string) $value), $items);
    $filtered = array_filter($normalized, static fn ($value) => $value !== '');
    return array_values(array_unique($filtered));
  }

  private function resolveDepartmentScope(User $user): array
  {
    $departmentList = array_merge(
      $this->normalizeDepartmentList($user->department ?? ''),
      $this->normalizeDepartmentList($user->faculty?->department ?? '')
    );

    return array_values(array_unique($departmentList));
  }

  private function applyDepartmentFilter($query, array $departments, string $column): void
  {
    if (empty($departments)) {
      return;
    }

    $query->where(function ($builder) use ($departments, $column) {
      foreach ($departments as $department) {
        $builder->orWhereRaw(
          "FIND_IN_SET(?, REPLACE($column, ', ', ','))",
          [$department]
        );
      }
    });
  }
}
