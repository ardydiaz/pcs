<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Evaluation, EvaluationResponse, Schedule, User, Faculty, Course, FacultyCourse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ZipArchive;

class ReportsController extends Controller
{
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
            // Deduplicate case-insensitively, prefer mixed-case over ALL-CAPS
            ->groupBy(fn($d) => strtolower($d))
            ->map(fn($group) => $group->first(fn($v) => $v !== strtoupper($v)) ?? $group->first())
            ->sort()
            ->values();
        if ($isDepartmentScoped) {
            $departments = $lockedDepartments
                ->reject(function ($department) use ($excludedDepartments) {
                    return in_array($department, $excludedDepartments, true);
                })
                ->values();
        }

        // Get academic years and semesters for filters
        $academicYears = Evaluation::where('is_active', true)
            ->distinct()
            ->pluck('academic_year')
            ->sort()
            ->values();
        $semesters = Evaluation::where('is_active', true)
            ->distinct()
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
            $evaluationsQuery->where(function ($query) use ($lockedDepartments) {
                foreach ($lockedDepartments as $department) {
                    $query->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                        [$department]
                    );
                }
            });
        } elseif ($isDepartmentScoped && $lockedDepartments->isEmpty()) {
            $evaluationsQuery->whereRaw('0 = 1');
        }

        if ($selectedDepartment !== 'all') {
            $evaluationsQuery->whereRaw(
                "FIND_IN_SET(?, REPLACE(faculty_department_snapshot, ', ', ','))",
                [$selectedDepartment]
            );
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
            $html = view('content.dashboard.partials.faculty-modal-table', compact('faculties'))->render();
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
        $department = $request->get('department');
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

        $facultyUserIds = Faculty::forDepartments([$department])
            ->pluck('user_id')
            ->filter()
            ->values();

        $evaluationsQuery = Evaluation::where('is_active', true);
        if ($facultyUserIds->isNotEmpty()) {
            $evaluationsQuery->where(function ($query) use ($facultyUserIds, $department) {
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
            $evaluationsQuery->whereRaw('0 = 1');
        }

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

        $responses = $responsesQuery->get();

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
                $evaluation?->resolved_faculty_department ?? '',
                $evaluation?->academic_year ?? '',
                $evaluation?->semester ?? '',
                $subjectTypeValue,
                $response->resolved_course_code,
                $section,
                $response->resolved_course_name,
                $response->effectiveness_text,
                $response->effectiveness_rating,
                $response->feedback_comments,
            ];
        }

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
            $responsesQuery = EvaluationResponse::whereIn('evaluation_id', $evaluationIds);
            if ($subjectType !== 'all') {
                $responsesQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                    $q->where('subject_type', $subjectType);
                });
            }
            $responses = $responsesQuery->get();
            if ($responses->isEmpty()) {
                continue;
            }

            $first = $facultyEvaluations->first();
            $ratings[] = [
                'faculty_name' => $first->resolved_faculty_name,
                'department' => $first->resolved_faculty_department,
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
        $normalized = array_map(static fn ($value) => trim((string) $value), $items);
        $filtered = array_filter($normalized, static fn ($value) => $value !== '');
        return array_values(array_unique($filtered));
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
