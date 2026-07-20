<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Evaluation, EvaluationResponse, Schedule, User, Faculty, Course, FacultyCourse};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Support\AuditLogger;
use Carbon\Carbon;
use ZipArchive;

class ReportsController extends Controller
{
    private const OFFICIAL_DEPARTMENTS = [
        'College of Nursing',
        'College of Dentistry',
        'College of Arts and Sciences',
        'College of Medical Technology',
        'College of Medicine',
        'College of Optometry',
        'College of Pharmacy',
        'College of Physical Therapy',
        'Basic Education',
        'School of Business and Management',
        'Institute of Education'
    ];

    private const EXCLUDED_DEPARTMENTS = [
        'Academic Department',
        'Research Ethics Office',
        'Senior High School',
    ];

    public function index(Request $request)
    {
        $excludedDepartments = self::EXCLUDED_DEPARTMENTS;
        $user = $request->user();
        $accessLevels = collect($user?->access_level ?? []);
        $isAdmin = $user && $user->role === 'Admin';
        $hasReportAccess = $isAdmin
            || $accessLevels->contains('View All Reports')
            || $accessLevels->contains('View Department Reports');

        if (!$hasReportAccess) {
            return response()
                ->view('content.pages.pages-misc-error', [], 404);
        }

        $lockedDepartment = null;
        $lockedDepartments = collect();
        $isDepartmentScoped = false;
        if (!$isAdmin) {
            $lockedDepartment = trim($user->department ?? '');
            $lockedDepartment = $lockedDepartment !== '' ? $lockedDepartment : '__none__';
            $lockedDepartments = collect($this->resolveDepartmentScope($user))
                ->filter(function ($value) {
                    return $value !== '' && $value !== '__none__';
                })
                ->values();
            $isDepartmentScoped = true;
        }

        $selectedDepartment = $request->get('department', 'all');
        if ($selectedDepartment !== 'all') {
            $selectedDepartment = $this->normalizeDepartmentName($selectedDepartment) ?? $selectedDepartment;
        }
        $selectedAcademicYear = $request->get('academic_year', 'all');
        $selectedSemester = $request->get('semester', 'all');
        $selectedSubjectType = $request->get('subject_type', 'all');
        $perPageRaw = $request->get('per_page', 10);
        $perPage = $perPageRaw === 'all' ? 'all' : (int) $perPageRaw;

        // Validate subject_type value
        if (!in_array($selectedSubjectType, ['all', 'major', 'minor'], true)) {
            $selectedSubjectType = 'all';
        }

        if (in_array($selectedDepartment, $excludedDepartments, true)) {
            $selectedDepartment = 'all';
        }

        if ($isDepartmentScoped) {
            if ($lockedDepartments->isEmpty()) {
                $selectedDepartment = '__none__';
            } elseif ($selectedDepartment !== 'all' && !$lockedDepartments->contains($selectedDepartment)) {
                $selectedDepartment = 'all';
            }
        }

        // Get all departments for filter dropdown
        $departments = Evaluation::where('is_active', true)
            ->whereNotNull('faculty_department_snapshot')
            ->pluck('faculty_department_snapshot')
            ->flatMap(function ($department) {
                return $this->normalizeDepartmentList($department);
            })
            ->reject(function ($department) use ($excludedDepartments) {
                return in_array($department, $excludedDepartments, true);
            })
            ->filter(fn ($department) => in_array($department, self::OFFICIAL_DEPARTMENTS, true))
            ->unique()
            ->sortBy(fn ($department) => array_search($department, self::OFFICIAL_DEPARTMENTS, true))
            ->values();
        if ($isDepartmentScoped) {
            $departments = $lockedDepartments
                ->reject(function ($department) use ($excludedDepartments) {
                    return in_array($department, $excludedDepartments, true);
                })
                ->filter(fn ($department) => in_array($department, self::OFFICIAL_DEPARTMENTS, true))
                ->unique()
                ->values();
        }

        // Get academic years and semesters for filters
        // Pull from FacultyCourse instead of Evaluation to ensure consistency with actual response data
        $academicYears = FacultyCourse::distinct()
            ->pluck('academic_year')
            ->sort()
            ->values();
        $semesters = FacultyCourse::distinct()
            ->pluck('semester')
            ->sort()
            ->values();

        // Base query for evaluations with department filtering
        $evaluationsQuery = Evaluation::with(['responses.schedule.facultyCourse.course'])
            ->where('is_active', true);

        foreach ($excludedDepartments as $excludedDepartment) {
            $evaluationsQuery->whereRaw(
                "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$excludedDepartment]
            );
        }

        if ($isDepartmentScoped && $lockedDepartments->isNotEmpty()) {
            $this->whereAnyDepartment($evaluationsQuery, $lockedDepartments->all(), 'faculty_department_snapshot');
        } elseif ($isDepartmentScoped && $lockedDepartments->isEmpty()) {
            $evaluationsQuery->whereRaw('0 = 1');
        }

        if ($selectedDepartment !== 'all') {
            $this->whereAnyDepartment($evaluationsQuery, [$selectedDepartment], 'faculty_department_snapshot');
        }

        // Apply academic year and semester filters
        if ($selectedAcademicYear !== 'all') {
            $evaluationsQuery->where('academic_year', $selectedAcademicYear);
        }
        if ($selectedSemester !== 'all') {
            $evaluationsQuery->where('semester', $selectedSemester);
        }

        $evaluations = $evaluationsQuery->get();

        // Calculate metrics
        $metrics = $this->calculateMetrics($evaluations, $selectedDepartment, $selectedAcademicYear, $selectedSemester, $selectedSubjectType);

        // Get department-wise breakdown with pagination
        $departmentBreakdown = $this->getDepartmentBreakdown(
            $selectedDepartment,
            $selectedAcademicYear,
            $selectedSemester,
            $perPage,
            $lockedDepartments->values()->all(),
            $excludedDepartments,
            $selectedSubjectType
        );

        // Get recent responses for activity feed
        $recentResponses = $this->getRecentResponses(
            $selectedDepartment,
            $selectedAcademicYear,
            $selectedSemester,
            10,
            $lockedDepartments->values()->all(),
            $excludedDepartments,
            $selectedSubjectType
        );

        // Get top/bottom rated faculties
        $facultyRatings = $this->getFacultyRatings(
            $selectedDepartment,
            $selectedAcademicYear,
            $selectedSemester,
            $lockedDepartments->values()->all(),
            $excludedDepartments,
            $selectedSubjectType
        );

        return view('content.dashboard.dashboard-reports', compact(
            'metrics',
            'departments',
            'academicYears',
            'semesters',
            'selectedDepartment',
            'selectedAcademicYear',
            'selectedSemester',
            'selectedSubjectType',
            'departmentBreakdown',
            'recentResponses',
            'facultyRatings',
            'perPage',
            'isDepartmentScoped',
            'lockedDepartment'
        ));
    }

    public function getMetricDetails(Request $request): JsonResponse
    {
        $user = $request->user();
        $accessLevels = collect($user?->access_level ?? []);
        $isAdmin = $user && $user->role === 'Admin';
        $hasReportAccess = $isAdmin
            || $accessLevels->contains('View All Reports')
            || $accessLevels->contains('View Department Reports');

        if (!$hasReportAccess) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 404);
        }

        $metric = $request->get('metric', 'total_faculties');
        if (!in_array($metric, ['total_faculties', 'total_responses', 'average_rating', 'courses_evaluated'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid metric.'], 422);
        }

        $excludedDepartments = self::EXCLUDED_DEPARTMENTS;
        $allowedDepartments = [];
        if (!$isAdmin) {
            $allowedDepartments = collect($this->resolveDepartmentScope($user))
                ->filter(fn($value) => $value !== '')
                ->values()
                ->all();
            if (empty($allowedDepartments)) {
                return response()->json([
                    'success' => true,
                    'title' => $this->metricTitle($metric),
                    'columns' => $this->metricColumns($metric),
                    'items' => [],
                    'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1],
                ]);
            }
        }

        $department = $request->get('department', 'all');
        if (in_array($department, $excludedDepartments, true)) {
            $department = 'all';
        }
        if (!$isAdmin && $department !== 'all' && !in_array($department, $allowedDepartments, true)) {
            $department = 'all';
        }

        $academicYear = $request->get('academic_year', 'all');
        $semester = $request->get('semester', 'all');
        $subjectType = $request->get('subject_type', 'all');
        if (!in_array($subjectType, ['all', 'major', 'minor'], true)) {
            $subjectType = 'all';
        }

        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(25, max(5, (int) $request->get('per_page', 10)));

        $cacheKey = 'reports.metric.details.' . md5(json_encode([
            'metric' => $metric,
            'department' => $department,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'subject_type' => $subjectType,
            'allowed_departments' => $allowedDepartments,
            'page' => $page,
            'per_page' => $perPage,
        ]));

        $result = Cache::remember($cacheKey, now()->addMinutes(3), function () use (
            $metric,
            $department,
            $academicYear,
            $semester,
            $subjectType,
            $allowedDepartments,
            $excludedDepartments,
            $page,
            $perPage
        ) {
            return match ($metric) {
                'total_faculties' => $this->buildMetricFacultyRows($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments, $page, $perPage),
                'total_responses' => $this->buildMetricResponseRows($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments, $page, $perPage),
                'average_rating' => $this->buildMetricRatingRows($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments, $page, $perPage),
                'courses_evaluated' => $this->buildMetricCourseRows($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments, $page, $perPage),
            };
        });

        return response()->json([
            'success' => true,
            'title' => $this->metricTitle($metric),
            'columns' => $this->metricColumns($metric),
            'items' => $result['items'],
            'meta' => $result['meta'],
        ]);
    }

    //filter and paginate faculties for the department-faculties modal
    public function getDepartmentFaculties(Request $request)
    {
        // Get all request parameters for filtering and pagination data of every kinds of departments
        $department = $request->get('department'); // Get all request parameters for filtering and pagination data of every kinds of departments
        
        $excludedDepartments = self::EXCLUDED_DEPARTMENTS;
        if (in_array($department, $excludedDepartments, true)) {
            return response()
                ->json(['success' => false, 'message' => 'Unauthorized.'], 404);
        }

        // Get all request parameters for filtering and pagination data of every kinds of departments
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        $academicYear = $request->get('academic_year', 'all');
        $semester = $request->get('semester', 'all');
        $subjectType = $request->get('subject_type', 'all');
        if (!in_array($subjectType, ['all', 'major', 'minor'], true)) {
            $subjectType = 'all';
        }
        $ratingFilter = $request->get('rating_filter', 'all');
        $statusFilter = $request->get('status_filter', 'all');
        $sortKey = $request->get('sort_key', 'name');
        $sortDir = strtolower((string) $request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Authorization check: only allow if user has access to reports and the department is within their scope (if not admin)
        $user = $request->user();
        $accessLevels = collect($user?->access_level ?? []);
        $isAdmin = $user && $user->role === 'Admin';
        $hasReportAccess = $isAdmin
            || $accessLevels->contains('View All Reports')
            || $accessLevels->contains('View Department Reports');

        if (!$hasReportAccess) {
            return response()
                ->json(['success' => false, 'message' => 'Unauthorized.'], 404);
        }

        // If not admin, check if the requested department is within the user's allowed departments
        if (!$isAdmin) {
            $allowedDepartments = collect($this->resolveDepartmentScope($user))
                ->filter(function ($value) {
                    return $value !== '';
                });
            if ($allowedDepartments->isEmpty() || !$allowedDepartments->contains($department)) {
                return response()
                    ->json(['success' => false, 'message' => 'Unauthorized.'], 404);
            }
        }

        // Fetch faculties and their evaluations for the requested department
        try {
            $faculties = Faculty::with('user')->forDepartments([$department])->get();
            $facultyUserIds = $faculties->pluck('user_id')->filter()->values();

            $baseEvaluationsQuery = Evaluation::where('is_active', true);
            if ($facultyUserIds->isNotEmpty()) {
                $baseEvaluationsQuery->where(function ($query) use ($facultyUserIds, $department) {
                    $query->whereIn('faculty_id', $facultyUserIds)
                        ->orWhere(function ($query) use ($department) {
                            $query->whereNull('faculty_id')
                                ->whereRaw(
                                    "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                                    [$department]
                                );
                        });
                });
            } else {
                $baseEvaluationsQuery->whereRaw('0 = 1');
            }

            if ($academicYear !== 'all') {
                $baseEvaluationsQuery->where('academic_year', $academicYear);
            }
            if ($semester !== 'all') {
                $baseEvaluationsQuery->where('semester', $semester);
            }

            $evaluationStats = (clone $baseEvaluationsQuery)
                ->select('faculty_id')
                ->selectRaw('COUNT(*) as evaluation_count')
                ->groupBy('faculty_id')
                ->get()
                ->keyBy('faculty_id');

            $latestEvaluationIds = (clone $baseEvaluationsQuery)
                ->select('faculty_id')
                ->selectRaw('MAX(id) as latest_id')
                ->groupBy('faculty_id')
                ->get()
                ->keyBy('faculty_id');

            $ratingStatsQuery = EvaluationResponse::join('evaluations', 'evaluation_responses.evaluation_id', '=', 'evaluations.id')
                ->where('evaluations.is_active', true);
            if ($facultyUserIds->isNotEmpty()) {
                $ratingStatsQuery->where(function ($query) use ($facultyUserIds, $department) {
                    $query->whereIn('evaluations.faculty_id', $facultyUserIds)
                        ->orWhere(function ($query) use ($department) {
                            $query->whereNull('evaluations.faculty_id')
                                ->whereRaw(
                                    "FIND_IN_SET(?, REPLACE(evaluations.faculty_department_snapshot, ', ', ','))",
                                    [$department]
                                );
                        });
                });
            } else {
                $ratingStatsQuery->whereRaw('0 = 1');
            }

            if ($academicYear !== 'all') {
                $ratingStatsQuery->where('evaluations.academic_year', $academicYear);
            }
            if ($semester !== 'all') {
                $ratingStatsQuery->where('evaluations.semester', $semester);
            }

            // Apply subject_type filter via schedule → faculty_course → course
            if ($subjectType !== 'all') {
                $ratingStatsQuery
                    ->join('schedules', 'evaluation_responses.schedule_id', '=', 'schedules.id')
                    ->join('faculty_courses', 'schedules.faculty_course_id', '=', 'faculty_courses.id')
                    ->join('courses', 'faculty_courses.course_id', '=', 'courses.id')
                    ->where('courses.subject_type', $subjectType);
            }

            $ratingStats = $ratingStatsQuery
                ->select('evaluations.faculty_id as faculty_id')
                ->selectRaw('COUNT(evaluation_responses.id) as response_count')
                ->selectRaw('AVG(evaluation_responses.effectiveness_rating) as average_rating')
                ->groupBy('evaluations.faculty_id')
                ->get()
                ->keyBy('faculty_id');

            // --- Resolve NULL faculty_id responses by matching faculty_name_snapshot to faculty user names ---
            // Responses from evaluations where faculty_id IS NULL are grouped under key "" above.
            // We re-attribute them to the correct faculty by matching faculty_name_snapshot → user name.
            $nullKeyStats = $ratingStats->get('') ?? $ratingStats->get(null);
            if ($nullKeyStats !== null) {
                // Fetch all NULL-faculty_id evaluations for this department/year/semester
                $nullEvalQuery = Evaluation::where('is_active', true)
                    ->whereNull('faculty_id')
                    ->whereRaw("FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))", [$department]);
                if ($academicYear !== 'all') {
                    $nullEvalQuery->where('academic_year', $academicYear);
                }
                if ($semester !== 'all') {
                    $nullEvalQuery->where('semester', $semester);
                }
                $nullEvals = $nullEvalQuery->get();

                // Build a name → faculty_user_id map from the faculties we already loaded
                $nameToFacultyId = $faculties->mapWithKeys(function ($faculty) {
                    $name = strtolower(trim($faculty->user?->name ?? ''));
                    return $name !== '' ? [$name => $faculty->user_id] : [];
                });

                // For each NULL eval, find the matching faculty and accumulate stats
                $nullAccumulator = []; // faculty_user_id => [total_rating, count]
                foreach ($nullEvals as $nullEval) {
                    $snapshotName = strtolower(trim($nullEval->faculty_name_snapshot ?? ''));
                    $matchedFacultyId = $nameToFacultyId->get($snapshotName);
                    if (!$matchedFacultyId) {
                        continue;
                    }

                    $respQuery = EvaluationResponse::where('evaluation_id', $nullEval->id);
                    if ($subjectType !== 'all') {
                        $respQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                            $q->where('subject_type', $subjectType);
                        });
                    }
                    $resps = $respQuery->get();

                    if ($resps->isEmpty()) {
                        continue;
                    }

                    if (!isset($nullAccumulator[$matchedFacultyId])) {
                        $nullAccumulator[$matchedFacultyId] = ['sum' => 0, 'count' => 0];
                    }
                    $nullAccumulator[$matchedFacultyId]['sum'] += $resps->sum('effectiveness_rating');
                    $nullAccumulator[$matchedFacultyId]['count'] += $resps->count();
                }

                // Merge accumulated NULL-eval stats into $ratingStats
                foreach ($nullAccumulator as $fId => $acc) {
                    if ($acc['count'] === 0) {
                        continue;
                    }
                    $existing = $ratingStats->get($fId);
                    if ($existing) {
                        $totalCount = $existing->response_count + $acc['count'];
                        $totalSum = ($existing->average_rating * $existing->response_count) + $acc['sum'];
                        $existing->response_count = $totalCount;
                        $existing->average_rating = $totalCount > 0 ? $totalSum / $totalCount : 0;
                    } else {
                        $ratingStats->put($fId, (object) [
                            'faculty_id' => $fId,
                            'response_count' => $acc['count'],
                            'average_rating' => $acc['sum'] / $acc['count'],
                        ]);
                    }
                }

                // Remove the NULL key entry — it's now been redistributed
                $ratingStats->forget('');
                $ratingStats->forget(null);
            }

            $facultyRows = $faculties->map(function ($faculty) use ($evaluationStats, $ratingStats, $latestEvaluationIds) {
                $facultyId = $faculty->user_id;
                $evalStat = $facultyId ? $evaluationStats->get($facultyId) : null;
                $ratingStat = $facultyId ? $ratingStats->get($facultyId) : null;
                $latestEvaluation = $facultyId ? $latestEvaluationIds->get($facultyId) : null;

                $average = $ratingStat ? (float) $ratingStat->average_rating : 0;
                return (object) [
                    'evaluation_id' => $latestEvaluation?->latest_id,
                    'name' => $faculty->user?->name ?? 'Unknown',
                    'email' => $faculty->user?->email,
                    'job_title' => $faculty->job_title ?? $faculty->user?->job_title,
                    'department' => $faculty->department ?? $faculty->user?->department,
                    'status' => strtolower((string) ($faculty->user?->status ?? 'active')),
                    'evaluation_count' => (int) ($evalStat?->evaluation_count ?? 0),
                    'active_evaluations' => (int) ($evalStat?->evaluation_count ?? 0),
                    'response_count' => (int) ($ratingStat?->response_count ?? 0),
                    'average_rating' => $average > 0 ? round($average, 2) : 0,
                ];
            })->values();

            // Only show faculty who have at least one response under the current filters
            $facultyRows = $facultyRows->filter(function ($faculty) {
                return $faculty->average_rating > 0;
            })->values();

            if ($search) {
                $needle = strtolower(trim($search));
                $facultyRows = $facultyRows->filter(function ($faculty) use ($needle) {
                    return str_contains(strtolower($faculty->name ?? ''), $needle)
                        || str_contains(strtolower($faculty->email ?? ''), $needle)
                        || str_contains(strtolower($faculty->department ?? ''), $needle);
                })->values();
            }

            if ($ratingFilter !== 'all') {
                $facultyRows = $facultyRows->filter(function ($faculty) use ($ratingFilter) {
                    $average = (float) ($faculty->average_rating ?? 0);
                    $bucket = $this->getAverageRatingBucket($average);
                    return $bucket === $ratingFilter;
                })->values();
            }

            if ($statusFilter !== 'all') {
                $statusNeedle = strtolower(trim((string) $statusFilter));
                $facultyRows = $facultyRows->filter(function ($faculty) use ($statusNeedle) {
                    return strtolower((string) ($faculty->status ?? '')) === $statusNeedle;
                })->values();
            }

            $allowedSorts = ['name', 'evaluations', 'responses', 'avg_rating', 'status'];
            if (!in_array($sortKey, $allowedSorts, true)) {
                $sortKey = 'name';
            }

            $sorter = function ($faculty) use ($sortKey) {
                switch ($sortKey) {
                    case 'evaluations':
                        return (int) ($faculty->evaluation_count ?? 0);
                    case 'responses':
                        return (int) ($faculty->response_count ?? 0);
                    case 'avg_rating':
                        return (float) ($faculty->average_rating ?? 0);
                    case 'status':
                        return strtolower((string) ($faculty->status ?? ''));
                    case 'name':
                    default:
                        return strtolower((string) ($faculty->name ?? ''));
                }
            };

            $facultyRows = $sortDir === 'desc'
                ? $facultyRows->sortByDesc($sorter)->values()
                : $facultyRows->sortBy($sorter)->values();

            $totalRows = $facultyRows->count();
            $usePagination = $perPage !== 'all';
            $pageSize = $usePagination ? max((int) $perPage, 1) : max($totalRows, 1);
            $faculties = $this->paginateCollection($facultyRows, $pageSize);

            // Generate the HTML and pagination
            $selectedAcademicYear = $academicYear;
            $selectedSemester = $semester;
            $selectedSubjectType = $subjectType;
            $selectedDepartment = $department;
            $html = view('content.dashboard.partials.faculty-modal-table', compact('faculties', 'selectedDepartment', 'selectedAcademicYear', 'selectedSemester', 'selectedSubjectType'))->render();
            $pagination = $usePagination
                ? $faculties->appends($request->all())->links('pagination::bootstrap-4')->render()
                : '';

            // Add debugging information
            \Log::info('Faculty Modal Response', [
                'department' => $department,
                'faculty_count' => $faculties->total(),
                'total_pages' => $faculties->lastPage(),
                'current_page' => $faculties->currentPage(),
                'has_html' => !empty($html),
                'has_pagination' => !empty($pagination)
            ]);

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'meta' => [
                    'current_page' => $faculties->currentPage(),
                    'last_page' => $faculties->lastPage(),
                    'per_page' => $faculties->perPage(),
                    'total' => $faculties->total(),
                    'from' => $faculties->firstItem(),
                    'to' => $faculties->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Faculty Modal Error', [
                'error' => $e->getMessage(),
                'department' => $department,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load faculty data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportDepartmentFaculties(Request $request)
    {
        $department = $this->normalizeDepartmentName($request->get('department')) ?? trim((string) $request->get('department'));
        $excludedDepartments = self::EXCLUDED_DEPARTMENTS;

        if (!$department || in_array($department, $excludedDepartments, true)) {
            return redirect()->back()->with('error', 'Invalid department selection.');
        }

        $user = $request->user();
        $accessLevels = collect($user?->access_level ?? []);
        $isAdmin = $user && $user->role === 'Admin';
        $hasReportAccess = $isAdmin
            || $accessLevels->contains('View All Reports')
            || $accessLevels->contains('View Department Reports');

        if (!$hasReportAccess) {
            return response()->view('content.pages.pages-misc-error', [], 404);
        }

        if (!$isAdmin) {
            $allowedDepartments = collect($this->resolveDepartmentScope($user))
                ->filter(function ($value) {
                    return $value !== '';
                });
            if ($allowedDepartments->isEmpty() || !$allowedDepartments->contains($department)) {
                return response()->view('content.pages.pages-misc-error', [], 404);
            }
        }

        $academicYear = $request->get('academic_year', 'all');
        $semester = $request->get('semester', 'all');
        $subjectType = $request->get('subject_type', 'all');
        if (!in_array($subjectType, ['all', 'major', 'minor'], true)) {
            $subjectType = 'all';
        }
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $start = null;
        $end = null;

        if ($startDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
            } catch (\Exception $e) {
                $start = null;
            }
        }

        if ($endDate) {
            try {
                $end = Carbon::parse($endDate)->endOfDay();
            } catch (\Exception $e) {
                $end = null;
            }
        }

        if ($start && $end && $end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $evaluationsQuery = Evaluation::where('is_active', true);
        $this->whereAnyDepartment($evaluationsQuery, [$department], 'faculty_department_snapshot');

        if ($academicYear !== 'all') {
            $evaluationsQuery->where('academic_year', $academicYear);
        }

        if ($semester !== 'all') {
            $evaluationsQuery->where('semester', $semester);
        }

        $evaluations = $evaluationsQuery->get();
        if ($evaluations->isEmpty()) {
            return redirect()->back()->with('error', 'No evaluations found for the selected filters.');
        }

        $evaluationIds = $evaluations->pluck('id')->filter()->values();

        // Collect all responses across all faculty
        $responsesQuery = EvaluationResponse::with(['evaluation', 'schedule.facultyCourse.course'])
            ->whereIn('evaluation_id', $evaluationIds);

        if ($start) {
            $responsesQuery->where('created_at', '>=', $start);
        }
        if ($end) {
            $responsesQuery->where('created_at', '<=', $end);
        }

        // Apply subject_type filter
        if ($subjectType !== 'all') {
            $responsesQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                $q->where('subject_type', $subjectType);
            });
        }

        $responses = $responsesQuery->get()
            ->filter(fn ($response) => $this->responseHandledByDepartment($response, $department))
            ->values();

        if ($responses->isEmpty()) {
            return redirect()->back()->with('error', 'No responses found for the selected filters.');
        }

        // Sort responses by faculty name alphabetically, then by created_at within each faculty
        $responses = $responses->sortBy(function ($response) {
            $facultyName = $response->evaluation?->resolved_faculty_name ?? 'Unknown';
            $createdAt = $response->created_at?->timestamp ?? 0;
            return [$facultyName, $createdAt];
        })->values();

        // Generate filename
        $departmentSlug = preg_replace('/[^A-Za-z0-9]+/', '_', ucwords(strtolower($department)));
        $departmentSlug = trim($departmentSlug, '_');
        $dateTag = now()->format('Y-m-d_H-i-s');
        $subjectTag = $subjectType !== 'all' ? '_' . ucfirst($subjectType) : '';
        $csvFilename = "Evaluation_Responses_{$departmentSlug}{$subjectTag}_{$dateTag}.csv";

        // Prepare data for export
        $headers = [
            'FACULTY NAME',
            'FACULTY DEPARTMENT',
            'ACADEMIC YEAR',
            'SEMESTER',
            'SUBJECT TYPE',
            'COURSE CODE',
            'SECTION',
            'COURSE NAME',
            'EFFECTIVENESS',
            'EFFECTIVENESS RATING',
            'FEEDBACK COMMENTS',
        ];

        $data = [];
        foreach ($responses as $response) {
            $evaluation = $response->evaluation;
            $course = optional(optional($response->schedule)->facultyCourse)->course;
            $subjectTypeValue = $course ? ucfirst(strtolower($course->subject_type ?? '')) : '';
            
            // Convert subject type to display label
            if ($subjectTypeValue === 'Major') {
                $subjectTypeValue = 'Professional Course';
            } elseif ($subjectTypeValue === 'Minor') {
                $subjectTypeValue = 'Minor Course';
            }
            
            $section = optional(optional($response->schedule)->facultyCourse)->section ?? '';
            
            // Clean up faculty name - remove extra spaces and fix encoding
            $facultyName = $evaluation?->resolved_faculty_name ?? 'Unknown';
            
            // Fix encoding issues - convert from ISO-8859-1 to UTF-8 if needed
            $facultyName = iconv('UTF-8', 'UTF-8//IGNORE', $facultyName);
            
            // Remove extra spaces
            $facultyName = preg_replace('/\s+/', ' ', trim($facultyName));
            
            $data[] = [
                $facultyName,
                $department,
                $evaluation?->academic_year ?? '',
                $this->formatSemesterLabel($evaluation?->semester ?? ''),
                $subjectTypeValue,
                $response->resolved_course_code,
                $section,
                $response->resolved_course_name,
                $response->effectiveness_text,
                $response->effectiveness_rating,
                $response->feedback_comments,
            ];
        }

        AuditLogger::log('report_excel_exported', [
            'module' => 'Reports',
            'description' => "Exported report responses for {$department}.",
            'after_values' => [
                'department' => $department,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'subject_type' => $subjectType,
                'start_date' => $start?->toDateString(),
                'end_date' => $end?->toDateString(),
                'evaluation_count' => $evaluations->count(),
                'response_count' => $responses->count(),
                'file_name' => $csvFilename,
            ],
            'severity' => 'info',
        ]);

        // Use Laravel Excel to export with styling
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($headers, $data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles {
                private $headers;
                private $data;

                public function __construct($headers, $data)
                {
                    $this->headers = $headers;
                    $this->data = $data;
                }

                public function array(): array
                {
                    return array_merge([$this->headers], $this->data);
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    // Style header row
                    $sheet->getStyle('1:1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '5C297C'],
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Auto-fit columns
                    foreach (range('A', 'K') as $column) {
                        $sheet->getColumnDimension($column)->setAutoSize(true);
                    }

                    return [];
                }
            },
            $csvFilename
        );
    }

    private function calculateMetrics($evaluations, $department, $academicYear, $semester, string $subjectType = 'all')
    {
        $allResponses = collect();
        foreach ($evaluations as $evaluation) {
            $allResponses = $allResponses->concat($evaluation->responses);
        }

        // Filter responses by subject_type if needed
        if ($subjectType !== 'all') {
            $allResponses = $allResponses->filter(function ($response) use ($subjectType) {
                $course = optional(optional($response->schedule)->facultyCourse)->course;
                return $course && strtolower((string) ($course->subject_type ?? '')) === $subjectType;
            })->values();
        }

        return [
            'total_evaluations' => $evaluations->count(),
            'active_evaluations' => $evaluations->count(),
            'total_responses' => $allResponses->count(),
            'total_faculties' => $evaluations->pluck('resolved_faculty_name')->unique()->count(),
            'average_rating' => $allResponses->count() > 0 ? round($allResponses->avg('effectiveness_rating'), 2) : 0,
            'courses_evaluated' => $allResponses->map(function ($response) {
                return $response->schedule_id ?? $response->course_code_snapshot ?? '';
            })->filter()->unique()->count(),
            'responses_with_feedback' => $allResponses->whereNotNull('feedback_comments')
                ->where('feedback_comments', '!=', '')->count(),
            'rating_distribution' => $allResponses->groupBy('effectiveness_rating')->map->count(),
        ];
    }

    private function getAverageRatingBucket(float $average): string
    {
        if ($average >= 3.5) {
            return 'very_effective';
        }
        if ($average >= 2.5) {
            return 'effective';
        }
        if ($average >= 1.5) {
            return 'somewhat_effective';
        }
        if ($average > 0) {
            return 'not_effective';
        }
        return 'no_ratings';
    }

    private function getDepartmentBreakdown($selectedDepartment, $academicYear, $semester, $perPage = null, array $allowedDepartments = [], array $excludedDepartments = [], string $subjectType = 'all')
    {
        if (in_array($selectedDepartment, $excludedDepartments, true)) {
            return collect();
        }
        $facultyDepartmentCounts = $this->buildFacultyDepartmentCounts($allowedDepartments, $excludedDepartments);
        $evaluationsQuery = Evaluation::where('is_active', true);

        if ($academicYear !== 'all') {
            $evaluationsQuery->where('academic_year', $academicYear);
        }
        if ($semester !== 'all') {
            $evaluationsQuery->where('semester', $semester);
        }

        if (!empty($excludedDepartments)) {
            foreach ($excludedDepartments as $excludedDepartment) {
                $evaluationsQuery->whereRaw(
                    "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                    [$excludedDepartment]
                );
            }
        }

        if (!empty($allowedDepartments)) {
            $evaluationsQuery->where(function ($query) use ($allowedDepartments) {
                foreach ($allowedDepartments as $department) {
                    $query->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$department]
                    );
                }
            });
        }

        $evaluations = $evaluationsQuery->get();
        $breakdown = [];

        // Helper to get filtered responses for a set of evaluation IDs
        $getResponses = function ($evaluationIds) use ($subjectType) {
            $query = EvaluationResponse::whereIn('evaluation_id', $evaluationIds);
            if ($subjectType !== 'all') {
                $query->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                    $q->where('subject_type', $subjectType);
                });
            }
            return $query->get();
        };

        // Helper to get major/minor response counts and avg ratings for a set of evaluation IDs
        $getSubjectTypeSplit = function ($evaluationIds) {
            if ($evaluationIds->isEmpty()) {
                return ['major_responses' => 0, 'minor_responses' => 0, 'major_avg_rating' => 0, 'minor_avg_rating' => 0];
            }
            $rows = EvaluationResponse::whereIn('evaluation_responses.evaluation_id', $evaluationIds)
                ->join('schedules', 'evaluation_responses.schedule_id', '=', 'schedules.id')
                ->join('faculty_courses', 'schedules.faculty_course_id', '=', 'faculty_courses.id')
                ->join('courses', 'faculty_courses.course_id', '=', 'courses.id')
                ->whereIn('courses.subject_type', ['major', 'minor'])
                ->selectRaw('courses.subject_type, COUNT(*) as cnt, AVG(evaluation_responses.effectiveness_rating) as avg_rating')
                ->groupBy('courses.subject_type')
                ->get()
                ->keyBy('subject_type');

            return [
                'major_responses' => (int) ($rows->get('major')?->cnt ?? 0),
                'minor_responses' => (int) ($rows->get('minor')?->cnt ?? 0),
                'major_avg_rating' => $rows->get('major') ? round((float) $rows->get('major')->avg_rating, 2) : 0,
                'minor_avg_rating' => $rows->get('minor') ? round((float) $rows->get('minor')->avg_rating, 2) : 0,
            ];
        };

        if ($selectedDepartment !== 'all') {
            $deptEvaluations = $evaluations->filter(function ($evaluation) use ($selectedDepartment) {
                $departments = $this->normalizeDepartmentList($evaluation->resolved_faculty_department ?? '');
                return in_array($selectedDepartment, $departments, true);
            });

            $responses = $getResponses($deptEvaluations->pluck('id'));
            $split = $getSubjectTypeSplit($deptEvaluations->pluck('id'));

            $breakdown[] = [
                'department' => $selectedDepartment,
                'faculty_count' => $facultyDepartmentCounts[$selectedDepartment] ?? 0,
                'total_evaluations' => $deptEvaluations->count(),
                'active_evaluations' => $deptEvaluations->count(),
                'total_responses' => $responses->count(),
                'average_rating' => $responses->count() > 0 ? round($responses->avg('effectiveness_rating'), 2) : 0,
                'major_responses' => $split['major_responses'],
                'minor_responses' => $split['minor_responses'],
                'major_avg_rating' => $split['major_avg_rating'],
                'minor_avg_rating' => $split['minor_avg_rating'],
            ];
        } else {
            $departmentGroups = [];
            foreach ($evaluations as $evaluation) {
                $departments = $this->normalizeDepartmentList($evaluation->resolved_faculty_department ?? '');
                if (!empty($excludedDepartments)) {
                    $departments = array_values(array_diff($departments, $excludedDepartments));
                }
                if (!empty($allowedDepartments)) {
                    $departments = array_values(array_intersect($departments, $allowedDepartments));
                }
                foreach ($departments as $dept) {
                    $departmentGroups[$dept] = $departmentGroups[$dept] ?? collect();
                    $departmentGroups[$dept]->push($evaluation);
                }
            }

            foreach ($departmentGroups as $dept => $deptEvaluations) {
                $responses = $getResponses($deptEvaluations->pluck('id'));
                $split = $getSubjectTypeSplit($deptEvaluations->pluck('id'));

                $breakdown[] = [
                    'department' => $dept,
                    'faculty_count' => $facultyDepartmentCounts[$dept] ?? 0,
                    'total_evaluations' => $deptEvaluations->count(),
                    'active_evaluations' => $deptEvaluations->count(),
                    'total_responses' => $responses->count(),
                    'average_rating' => $responses->count() > 0 ? round($responses->avg('effectiveness_rating'), 2) : 0,
                    'major_responses' => $split['major_responses'],
                    'minor_responses' => $split['minor_responses'],
                    'major_avg_rating' => $split['major_avg_rating'],
                    'minor_avg_rating' => $split['minor_avg_rating'],
                ];
            }
        }

        return collect($breakdown)->sortByDesc('total_responses');
    }

    private function getRecentResponses($department, $academicYear, $semester, $limit = 10, array $allowedDepartments = [], array $excludedDepartments = [], string $subjectType = 'all')
    {
        $query = EvaluationResponse::with([
            'evaluation',
            'schedule.facultyCourse.course'
        ])->whereHas('evaluation', function ($q) use ($department) {
            $q->where('is_active', true);
            if ($department !== 'all') {
                $q->whereRaw(
                    "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                    [$department]
                );
            }
        });

        if (!empty($excludedDepartments)) {
            $query->whereHas('evaluation', function ($q) use ($excludedDepartments) {
                foreach ($excludedDepartments as $departmentItem) {
                    $q->whereRaw(
                        "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$departmentItem]
                    );
                }
            });
        }

        if (!empty($allowedDepartments)) {
            $query->whereHas('evaluation', function ($q) use ($allowedDepartments) {
                $q->where(function ($inner) use ($allowedDepartments) {
                    foreach ($allowedDepartments as $departmentItem) {
                        $inner->orWhereRaw(
                            "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                            [$departmentItem]
                        );
                    }
                });
            });
        }

        if ($academicYear !== 'all') {
            $query->whereHas('evaluation', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        if ($semester !== 'all') {
            $query->whereHas('evaluation', function ($q) use ($semester) {
                $q->where('semester', $semester);
            });
        }

        // Filter by subject_type via the schedule → facultyCourse → course relationship
        if ($subjectType !== 'all') {
            $query->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                $q->where('subject_type', $subjectType);
            });
        }

        return $query->latest()->limit($limit)->get();
    }

    private function getFacultyRatings($department, $academicYear, $semester, array $allowedDepartments = [], array $excludedDepartments = [], string $subjectType = 'all')
    {
        $evaluationsQuery = Evaluation::where('is_active', true);

        if ($academicYear !== 'all') {
            $evaluationsQuery->where('academic_year', $academicYear);
        }
        if ($semester !== 'all') {
            $evaluationsQuery->where('semester', $semester);
        }

        if (!empty($excludedDepartments)) {
            foreach ($excludedDepartments as $excludedDepartment) {
                $evaluationsQuery->whereRaw(
                    "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                    [$excludedDepartment]
                );
            }
        }

        if (!empty($allowedDepartments)) {
            $evaluationsQuery->where(function ($query) use ($allowedDepartments) {
                foreach ($allowedDepartments as $departmentItem) {
                    $query->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$departmentItem]
                    );
                }
            });
        }

        if ($department !== 'all') {
            $evaluationsQuery->whereRaw(
                "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$department]
            );
        }

        $evaluations = $evaluationsQuery->get();
        $ratings = [];

        $facultyGroups = $evaluations->groupBy(function ($evaluation) {
            return strtolower($evaluation->resolved_faculty_name ?? '');
        });

        foreach ($facultyGroups as $facultyEvaluations) {
            $evaluationIds = $facultyEvaluations->pluck('id');
            $responsesQuery = EvaluationResponse::with(['schedule.facultyCourse.course'])
                ->whereIn('evaluation_id', $evaluationIds);
            if ($subjectType !== 'all') {
                $responsesQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                    $q->where('subject_type', $subjectType);
                });
            }
            $responses = $responsesQuery->get();

            if ($department !== 'all') {
                $responses = $responses
                    ->filter(fn ($response) => $this->responseHandledByDepartment($response, $department))
                    ->values();
            }

            if ($responses->isEmpty()) {
                continue;
            }

            $first = $facultyEvaluations->first();
            $ratings[] = [
                'faculty_name' => $first->resolved_faculty_name,
                'department' => $department !== 'all' ? $department : $first->resolved_faculty_department,
                'average_rating' => round($responses->avg('effectiveness_rating'), 2),
                'total_responses' => $responses->count(),
                'courses_count' => $responses->unique('schedule_id')->count()
            ];
        }

        $ratingsCollection = collect($ratings);

        return [
            'top_rated' => $ratingsCollection->sortByDesc('average_rating')->values()->take(5),
            'low_rated' => $ratingsCollection->sortBy('average_rating')->values()->take(5),
            'most_evaluated' => $ratingsCollection->sortByDesc('total_responses')->values()->take(5)
        ];
    }

    private function getFacultyKey($evaluation): string
    {
        $facultyId = data_get($evaluation, 'faculty_id');
        if ($facultyId) {
            return 'id:' . $facultyId;
        }
        $name = strtolower(trim((string) data_get($evaluation, 'resolved_faculty_name', '')));
        return $name === '' ? 'unknown' : 'name:' . $name;
    }

    private function metricTitle(string $metric): string
    {
        return match ($metric) {
            'total_faculties' => 'Total Faculties',
            'total_responses' => 'Total Responses',
            'average_rating' => 'Average Rating Details',
            'courses_evaluated' => 'Courses Evaluated',
            default => 'Metric Details',
        };
    }

    private function metricColumns(string $metric): array
    {
        return match ($metric) {
            'total_faculties' => ['Faculty', 'Department', 'Evaluations', 'Responses', 'Average Rating'],
            'total_responses' => ['Faculty', 'Course', 'Rating', 'Feedback', 'Submitted'],
            'average_rating' => ['Faculty', 'Department', 'Average Rating', 'Responses', 'Courses'],
            'courses_evaluated' => ['Course', 'Subject Type', 'Responses', 'Average Rating', 'Faculty Handlers'],
            default => [],
        };
    }

    private function metricEvaluationQuery(string $department, string $academicYear, string $semester, array $allowedDepartments, array $excludedDepartments)
    {
        $query = Evaluation::where('is_active', true);

        if ($academicYear !== 'all') {
            $query->where('academic_year', $academicYear);
        }
        if ($semester !== 'all') {
            $query->where('semester', $semester);
        }

        foreach ($excludedDepartments as $excludedDepartment) {
            $query->whereRaw(
                "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$excludedDepartment]
            );
        }

        if (!empty($allowedDepartments)) {
            $query->where(function ($inner) use ($allowedDepartments) {
                foreach ($allowedDepartments as $allowedDepartment) {
                    $inner->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$allowedDepartment]
                    );
                }
            });
        }

        if ($department !== 'all') {
            $query->whereRaw(
                "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$department]
            );
        }

        return $query;
    }

    private function metricResponseQuery(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments)
    {
        $query = EvaluationResponse::with(['evaluation', 'schedule.facultyCourse.course'])
            ->whereHas('evaluation', function ($evaluationQuery) use ($department, $academicYear, $semester, $allowedDepartments, $excludedDepartments) {
                $this->applyMetricEvaluationFilters($evaluationQuery, $department, $academicYear, $semester, $allowedDepartments, $excludedDepartments);
            });

        if ($subjectType !== 'all') {
            $query->whereHas('schedule.facultyCourse.course', function ($courseQuery) use ($subjectType) {
                $courseQuery->where('subject_type', $subjectType);
            });
        }

        return $query;
    }

    private function applyMetricEvaluationFilters($query, string $department, string $academicYear, string $semester, array $allowedDepartments, array $excludedDepartments): void
    {
        $query->where('is_active', true);

        if ($academicYear !== 'all') {
            $query->where('academic_year', $academicYear);
        }
        if ($semester !== 'all') {
            $query->where('semester', $semester);
        }
        foreach ($excludedDepartments as $excludedDepartment) {
            $query->whereRaw(
                "NOT FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$excludedDepartment]
            );
        }
        if (!empty($allowedDepartments)) {
            $query->where(function ($inner) use ($allowedDepartments) {
                foreach ($allowedDepartments as $allowedDepartment) {
                    $inner->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$allowedDepartment]
                    );
                }
            });
        }
        if ($department !== 'all') {
            $query->whereRaw(
                "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$department]
            );
        }
    }

    private function buildMetricFacultyRows(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments, int $page, int $perPage): array
    {
        $responseCountSql = $subjectType === 'all'
            ? 'COUNT(evaluation_responses.id)'
            : "SUM(CASE WHEN courses.subject_type = ? THEN 1 ELSE 0 END)";
        $averageSql = $subjectType === 'all'
            ? 'AVG(evaluation_responses.effectiveness_rating)'
            : "AVG(CASE WHEN courses.subject_type = ? THEN evaluation_responses.effectiveness_rating ELSE NULL END)";

        $bindings = $subjectType === 'all' ? [] : [$subjectType, $subjectType];

        $query = DB::table('evaluations')
            ->leftJoin('users', 'users.id', '=', 'evaluations.faculty_id')
            ->leftJoin('evaluation_responses', 'evaluation_responses.evaluation_id', '=', 'evaluations.id')
            ->leftJoin('schedules', 'schedules.id', '=', 'evaluation_responses.schedule_id')
            ->leftJoin('faculty_courses', 'faculty_courses.id', '=', 'schedules.faculty_course_id')
            ->leftJoin('courses', 'courses.id', '=', 'faculty_courses.course_id')
            ->selectRaw("
                evaluations.faculty_id,
                COALESCE(NULLIF(users.name, ''), NULLIF(evaluations.faculty_name_snapshot, ''), 'Unknown') as faculty_name,
                COALESCE(NULLIF(users.department, ''), NULLIF(evaluations.faculty_department_snapshot, ''), 'No department') as department,
                COUNT(DISTINCT evaluations.id) as evaluation_count,
                {$responseCountSql} as response_count,
                {$averageSql} as average_rating
            ", $bindings);

        $this->applyMetricEvaluationFiltersToQuery($query, $department, $academicYear, $semester, $allowedDepartments, $excludedDepartments);

        $paginator = $query
            ->groupBy(
                'evaluations.faculty_id',
                'users.name',
                'evaluations.faculty_name_snapshot',
                'users.department',
                'evaluations.faculty_department_snapshot'
            )
            ->orderBy('faculty_name')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return $this->formatMetricPaginator($paginator, function ($row) {
            $responseCount = (int) ($row->response_count ?? 0);
            return [
                'cells' => [
                    $row->faculty_name ?: 'Unknown',
                    $row->department ?: 'No department',
                    (string) $row->evaluation_count,
                    (string) $responseCount,
                    $responseCount > 0 ? round((float) $row->average_rating, 2) . '/4.0' : 'N/A',
                ],
            ];
        });
    }

    private function buildMetricResponseRows(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments, int $page, int $perPage): array
    {
        $query = $this->metricResponseBaseQuery($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments)
            ->selectRaw("
                evaluation_responses.id,
                COALESCE(NULLIF(users.name, ''), NULLIF(evaluations.faculty_name_snapshot, ''), 'Unknown') as faculty_name,
                COALESCE(NULLIF(courses.class_code, ''), NULLIF(evaluation_responses.course_code_snapshot, ''), 'N/A') as course_code,
                evaluation_responses.effectiveness_rating,
                evaluation_responses.feedback_comments,
                evaluation_responses.created_at
            ")
            ->orderByDesc('evaluation_responses.created_at');

        $paginator = $query->simplePaginate($perPage, ['*'], 'page', $page);

        return $this->formatMetricPaginator($paginator, function ($row) {
            return [
                'cells' => [
                    $row->faculty_name ?: 'Unknown',
                    $row->course_code ?: 'N/A',
                    (string) $row->effectiveness_rating . '/4',
                    Str::limit((string) ($row->feedback_comments ?: 'No feedback'), 90),
                    $row->created_at ? Carbon::parse($row->created_at)->format('M d, Y h:i A') : 'N/A',
                ],
            ];
        });
    }

    private function buildMetricRatingRows(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments, int $page, int $perPage): array
    {
        $query = $this->metricResponseBaseQuery($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments)
            ->selectRaw("
                evaluations.faculty_id,
                COALESCE(NULLIF(users.name, ''), NULLIF(evaluations.faculty_name_snapshot, ''), 'Unknown') as faculty_name,
                COALESCE(NULLIF(users.department, ''), NULLIF(evaluations.faculty_department_snapshot, ''), 'No department') as department,
                AVG(evaluation_responses.effectiveness_rating) as average_rating,
                COUNT(evaluation_responses.id) as response_count,
                COUNT(DISTINCT evaluation_responses.schedule_id) as course_count
            ")
            ->groupBy(
                'evaluations.faculty_id',
                'users.name',
                'evaluations.faculty_name_snapshot',
                'users.department',
                'evaluations.faculty_department_snapshot'
            )
            ->having('response_count', '>', 0)
            ->orderByDesc('average_rating');

        $paginator = $query->simplePaginate($perPage, ['*'], 'page', $page);

        return $this->formatMetricPaginator($paginator, function ($row) {
            return [
                'cells' => [
                    $row->faculty_name ?: 'Unknown',
                    $row->department ?: 'No department',
                    round((float) $row->average_rating, 2) . '/4.0',
                    (string) $row->response_count,
                    (string) $row->course_count,
                ],
            ];
        });
    }

    private function buildMetricCourseRows(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments, int $page, int $perPage): array
    {
        $query = $this->metricResponseBaseQuery($department, $academicYear, $semester, $subjectType, $allowedDepartments, $excludedDepartments)
            ->selectRaw("
                COALESCE(courses.id, CONCAT('snapshot:', COALESCE(evaluation_responses.course_code_snapshot, 'N/A'))) as course_key,
                TRIM(CONCAT(
                    COALESCE(NULLIF(courses.class_code, ''), NULLIF(evaluation_responses.course_code_snapshot, ''), 'N/A'),
                    CASE WHEN COALESCE(courses.subject_code, evaluation_responses.course_name_snapshot, '') <> ''
                        THEN CONCAT(' - ', COALESCE(courses.subject_code, evaluation_responses.course_name_snapshot))
                        ELSE ''
                    END
                )) as course_label,
                COALESCE(courses.subject_type, 'N/A') as subject_type,
                COUNT(evaluation_responses.id) as response_count,
                AVG(evaluation_responses.effectiveness_rating) as average_rating,
                GROUP_CONCAT(DISTINCT COALESCE(NULLIF(users.name, ''), NULLIF(evaluations.faculty_name_snapshot, '')) ORDER BY users.name SEPARATOR ', ') as handlers
            ")
            ->groupBy(
                'courses.id',
                'courses.class_code',
                'courses.subject_code',
                'courses.subject_type',
                'evaluation_responses.course_code_snapshot',
                'evaluation_responses.course_name_snapshot'
            )
            ->orderByDesc('response_count');

        $paginator = $query->simplePaginate($perPage, ['*'], 'page', $page);

        return $this->formatMetricPaginator($paginator, function ($row) {
            $subjectType = match ($row->subject_type) {
                'major' => 'Professional',
                'minor' => 'GenEd',
                default => 'N/A',
            };

            return [
                'cells' => [
                    $row->course_label ?: 'N/A',
                    $subjectType,
                    (string) $row->response_count,
                    $row->response_count > 0 ? round((float) $row->average_rating, 2) . '/4.0' : 'N/A',
                    $row->handlers ?: 'N/A',
                ],
            ];
        });
    }

    private function metricResponseBaseQuery(string $department, string $academicYear, string $semester, string $subjectType, array $allowedDepartments, array $excludedDepartments)
    {
        $query = DB::table('evaluation_responses')
            ->join('evaluations', 'evaluations.id', '=', 'evaluation_responses.evaluation_id')
            ->leftJoin('users', 'users.id', '=', 'evaluations.faculty_id')
            ->leftJoin('schedules', 'schedules.id', '=', 'evaluation_responses.schedule_id')
            ->leftJoin('faculty_courses', 'faculty_courses.id', '=', 'schedules.faculty_course_id')
            ->leftJoin('courses', 'courses.id', '=', 'faculty_courses.course_id');

        $this->applyMetricEvaluationFiltersToQuery($query, $department, $academicYear, $semester, $allowedDepartments, $excludedDepartments);

        if ($subjectType !== 'all') {
            $query->where('courses.subject_type', $subjectType);
        }

        return $query;
    }

    private function applyMetricEvaluationFiltersToQuery($query, string $department, string $academicYear, string $semester, array $allowedDepartments, array $excludedDepartments): void
    {
        $query->where('evaluations.is_active', true);

        if ($academicYear !== 'all') {
            $query->where('evaluations.academic_year', $academicYear);
        }
        if ($semester !== 'all') {
            $query->where('evaluations.semester', $semester);
        }

        foreach ($excludedDepartments as $excludedDepartment) {
            $query->whereRaw(
                "NOT FIND_IN_SET(?, REPLACE(evaluations.faculty_department_snapshot, ', ', ','))",
                [$excludedDepartment]
            );
        }

        if (!empty($allowedDepartments)) {
            $query->where(function ($inner) use ($allowedDepartments) {
                foreach ($allowedDepartments as $allowedDepartment) {
                    $inner->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(evaluations.faculty_department_snapshot, ', ', ','))",
                        [$allowedDepartment]
                    );
                }
            });
        }

        if ($department !== 'all') {
            $query->whereRaw(
                "FIND_IN_SET(?, REPLACE(evaluations.faculty_department_snapshot, ', ', ','))",
                [$department]
            );
        }
    }

    private function formatMetricPaginator($paginator, callable $mapper): array
    {
        $items = collect($paginator->items())->map($mapper)->values();
        $page = $paginator->currentPage();
        $perPage = $paginator->perPage();
        $from = $items->isEmpty() ? 0 : (($page - 1) * $perPage) + 1;
        $to = $items->isEmpty() ? 0 : $from + $items->count() - 1;
        $hasMore = method_exists($paginator, 'hasMorePages') ? $paginator->hasMorePages() : false;

        return [
            'items' => $items,
            'meta' => [
                'total' => null,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => $hasMore ? $page + 1 : $page,
                'has_more' => $hasMore,
                'from' => $from,
                'to' => $to,
            ],
        ];
    }

    private function buildFacultyDepartmentCounts(array $allowedDepartments = [], array $excludedDepartments = []): array
    {
        $faculties = Faculty::with('user:id,department')->get();
        $counts = [];

        foreach ($faculties as $faculty) {
            $raw = trim((string) ($faculty->department ?? $faculty->user?->department ?? ''));
            if ($raw === '') {
                continue;
            }
            $departments = Faculty::normalizeDepartmentList($raw);
            foreach ($departments as $department) {
                if (in_array($department, $excludedDepartments, true)) {
                    continue;
                }
                if (!empty($allowedDepartments) && !in_array($department, $allowedDepartments, true)) {
                    continue;
                }
                $counts[$department] = ($counts[$department] ?? 0) + 1;
            }
        }

        return $counts;
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
        $normalized = array_map(function ($value) {
            $value = preg_replace('/\s+/', ' ', trim((string) $value));
            return $this->normalizeDepartmentName($value) ?? $value;
        }, $items);
        $filtered = array_filter($normalized, static fn ($value) => $value !== '');
        return array_values(array_unique($filtered));
    }

    private function formatSemesterLabel(?string $semester): string
    {
        $value = trim((string) ($semester ?? ''));
        $normalized = strtolower($value);

        return match ($normalized) {
            '1st', 'first', 'first semester', '1st semester' => '1st Semester',
            '2nd', 'second', 'second semester', '2nd semester' => '2nd Semester',
            'summer', 'summer semester' => 'Summer',
            default => $value,
        };
    }

    private function responseHandledByDepartment($response, string $department): bool
    {
        $facultyCourse = optional($response->schedule)->facultyCourse;
        $course = optional($facultyCourse)->course;
        $assignmentDepartments = $this->normalizeDepartmentList(optional($facultyCourse)->department ?? '');

        if (!empty($assignmentDepartments)) {
            return in_array($department, $assignmentDepartments, true);
        }

        $inferredDepartment = $this->inferDepartmentFromAssignment(
            optional($facultyCourse)->section ?? '',
            $response->resolved_course_code ?? optional($course)->class_code ?? '',
            $response->resolved_course_name ?? optional($course)->subject_code ?? ''
        );

        return $inferredDepartment === $department;
    }

    private function inferDepartmentFromAssignment(?string $section, ?string $courseCode, ?string $courseName): ?string
    {
        $sectionText = strtoupper(trim((string) $section));

        if ($sectionText !== '') {
            $sectionDepartment = $this->matchDepartmentPattern($sectionText, [
                'College of Dentistry' => '/\b(DDM|DMD|DDS|MSD|MSDO)\b/',
                'College of Nursing' => '/\b(BSN|CON)\b/',
                'College of Arts and Sciences' => '/\b(CAS|BS-?PSYCH|BSPSYCH|PSYCH|ABCOMM|AB-?COMM|BA COMM|BSIT|BIO|MICROBIO)\b/',
                'College of Medical Technology' => '/\b(CMT|BS-?MT|BSMT|MT)\b/',
                'College of Medicine' => '/\b(COM|MED|MEDICINE)\b/',
                'College of Optometry' => '/\b(OP|OPT|COO)\b/',
                'College of Pharmacy' => '/\b(BSPH|PHARMA|PHARMACY|CPH)\b/',
                'College of Physical Therapy' => '/\b(BSPT|PT|CPT)\b/',
                'Basic Education' => '/\b(BED|BEDD|BASIC EDUCATION|ELEMENTARY|JHS|SHS|GRADE)\b/',
                'School of Business and Management' => '/\b(SBM|BSBA|MAN-?ADM|MBA)\b/',
            ]);

            if ($sectionDepartment !== null) {
                return $sectionDepartment;
            }
        }

        $text = strtoupper(trim(implode(' ', array_filter([
            (string) $courseCode,
            (string) $courseName,
        ]))));

        if ($text === '') {
            return null;
        }

        return $this->matchDepartmentPattern($text, [
            'College of Dentistry' => '/\b(DDM|DMD|DDS|MSD|MSDO|CLD|ORS|CCC|CCS|PID|DPH|NUT|ORT|DME|CAR|ANA|GMA|PRO|PDO|PER|GPA|ICL)\b/',
            'College of Nursing' => '/\b(BSN|CON)\b/',
            'College of Arts and Sciences' => '/\b(CAS|BS-?PSYCH|BSPSYCH|PSYCH|ABCOMM|AB-?COMM|BA COMM|BSIT|BIO|MICROBIO|ZOO|STS|PEE)\b/',
            'College of Medical Technology' => '/\b(CMT|BS-?MT|BSMT|MT)\b/',
            'College of Medicine' => '/\b(COM|MED|MEDICINE)\b/',
            'College of Optometry' => '/\b(OP|OPT|COO)\b/',
            'College of Pharmacy' => '/\b(BSPH|PHARMA|PHARMACY|CPH)\b/',
            'College of Physical Therapy' => '/\b(BSPT|PT|CPT)\b/',
            'Basic Education' => '/\b(BED|BEDD|BASIC EDUCATION|ELEMENTARY|JHS|SHS|GRADE)\b/',
            'School of Business and Management' => '/\b(SBM|BSBA|MAN-?ADM|MBA)\b/',
        ]);
    }

    private function matchDepartmentPattern(string $text, array $patterns): ?string
    {
        foreach ($patterns as $department => $pattern) {
            if (preg_match($pattern, $text)) {
                return $department;
            }
        }

        return null;
    }

    private function normalizeDepartmentName(?string $department): ?string
    {
        $department = preg_replace('/\s+/', ' ', trim((string) ($department ?? '')));

        if ($department === '') {
            return null;
        }

        foreach (self::OFFICIAL_DEPARTMENTS as $officialDepartment) {
            if (strcasecmp($department, $officialDepartment) === 0) {
                return $officialDepartment;
            }
        }

        $map = [
            'nursing' => 'College of Nursing',
            'con' => 'College of Nursing',
            'bs nursing' => 'College of Nursing',
            'bsn' => 'College of Nursing',
            'bachelor of science in nursing' => 'College of Nursing',
            'dentistry' => 'College of Dentistry',
            'cod' => 'College of Dentistry',
            'dmd' => 'College of Dentistry',
            'dds' => 'College of Dentistry',
            'msd' => 'College of Dentistry',
            'msdo' => 'College of Dentistry',
            'master of science in dentistry' => 'College of Dentistry',
            'master of science in dentistry with specialization in orthodontics' => 'College of Dentistry',
            'cas' => 'College of Arts and Sciences',
            'arts and sciences' => 'College of Arts and Sciences',
            'bachelor of arts in communication' => 'College of Arts and Sciences',
            'communication' => 'College of Arts and Sciences',
            'psychology' => 'College of Arts and Sciences',
            'bs psych' => 'College of Arts and Sciences',
            'bspsych' => 'College of Arts and Sciences',
            'ab communication' => 'College of Arts and Sciences',
            'bachelor of science in psychology' => 'College of Arts and Sciences',
            'bsit' => 'College of Arts and Sciences',
            'cas bs in information technology' => 'College of Arts and Sciences',
            'information technology' => 'College of Arts and Sciences',
            'cmt' => 'College of Medical Technology',
            'bs mt' => 'College of Medical Technology',
            'bsmt' => 'College of Medical Technology',
            'medical technology' => 'College of Medical Technology',
            'bachelor of science in medical technology' => 'College of Medical Technology',
            'medicine' => 'College of Medicine',
            'com' => 'College of Medicine',
            'college of medicine' => 'College of Medicine',
            'optometry' => 'College of Optometry',
            'coo' => 'College of Optometry',
            'pharmacy' => 'College of Pharmacy',
            'cop' => 'College of Pharmacy',
            'physical therapy' => 'College of Physical Therapy',
            'pt' => 'College of Physical Therapy',
            'cpt' => 'College of Physical Therapy',
            'bed' => 'Basic Education',
            'bedd' => 'Basic Education',
            'bed d' => 'Basic Education',
            'beded' => 'Basic Education',
            'basic education department' => 'Basic Education',
            'business' => 'School of Business and Management',
            'business and management' => 'School of Business and Management',
            'school of business' => 'School of Business and Management',
            'sbm' => 'School of Business and Management',
            'bsba' => 'School of Business and Management',
        ];

        return $map[$this->departmentKey($department)] ?? null;
    }

    private function departmentAliases(string $department): array
    {
        $officialDepartment = $this->normalizeDepartmentName($department) ?? $department;

        $aliases = [
            'College of Nursing' => ['College of Nursing', 'Nursing', 'CON', 'BS Nursing', 'BSN', 'BACHELOR OF SCIENCE IN NURSING'],
            'College of Dentistry' => ['College of Dentistry', 'Dentistry', 'COD', 'DMD', 'DDS', 'MSD', 'MSDO', 'Master of Science in Dentistry', 'Master of Science in Dentistry with specialization in Orthodontics'],
            'College of Arts and Sciences' => ['College of Arts and Sciences', 'CAS', 'Arts and Sciences', 'BACHELOR OF ARTS IN COMMUNICATION', 'Communication', 'Psychology', 'BS Psych', 'BSPSYCH', 'AB Communication', 'BACHELOR OF SCIENCE IN PSYCHOLOGY', 'BSIT', 'CAS-BS IN INFORMATION TECHNOLOGY', 'Information Technology'],
            'College of Medical Technology' => ['College of Medical Technology', 'CMT', 'BS MT', 'BSMT', 'Medical Technology', 'CMT - BS IN MEDICAL TECHNOLOGY', 'BACHELOR OF SCIENCE IN MEDICAL TECHNOLOGY'],
            'College of Medicine' => ['College of Medicine', 'Medicine', 'COM', 'COLLEGE OF MEDICINE'],
            'College of Optometry' => ['College of Optometry', 'Optometry', 'COO'],
            'College of Pharmacy' => ['College of Pharmacy', 'Pharmacy', 'COP'],
            'College of Physical Therapy' => ['College of Physical Therapy', 'Physical Therapy', 'PT', 'CPT'],
            'Basic Education' => ['Basic Education', 'BED', 'BEDD', 'BEdD', 'Bed D', 'BEDED', 'Basic Education Department', 'Institute of Education'],
            'School of Business and Management' => ['School of Business and Management', 'Business', 'Business and Management', 'School of Business', 'SBM', 'BSBA'],
        ];

        return array_values(array_unique($aliases[$officialDepartment] ?? [$officialDepartment]));
    }

    private function departmentKey(string $department): string
    {
        $key = strtolower($department);
        $key = preg_replace('/[^a-z0-9]+/', ' ', $key);
        return trim(preg_replace('/\s+/', ' ', $key));
    }

    private function whereAnyDepartment($query, array $departments, string $column): void
    {
        $aliases = collect($departments)
            ->flatMap(fn ($department) => $this->departmentAliases((string) $department))
            ->filter()
            ->unique()
            ->values();

        if ($aliases->isEmpty()) {
            $query->whereRaw('0 = 1');
            return;
        }

        $query->where(function ($builder) use ($aliases, $column) {
            foreach ($aliases as $department) {
                $builder->orWhereRaw(
                    "FIND_IN_SET(?, REPLACE($column, ', ', ','))",
                    [$department]
                );
            }
        });
    }

    private function resolveDepartmentScope(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $departmentList = array_merge(
            $this->normalizeDepartmentList($user->department ?? ''),
            $this->normalizeDepartmentList($user->faculty?->department ?? '')
        );

        return array_values(array_unique($departmentList));
    }

    private function paginateCollection($items, int $perPage)
    {
        $page = request()->get('page', 1);
        $page = is_numeric($page) ? (int) $page : 1;
        $offset = max($page - 1, 0) * $perPage;

        $total = $items->count();
        $pagedItems = $items->slice($offset, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
