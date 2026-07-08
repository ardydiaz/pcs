<?php

namespace App\Http\Controllers\user_management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display the list of users in the management console.
     */
    public function index()
    {
        return view('content.user-management.users', [
            'users' => collect(),
            'departmentOptions' => $this->getDistinctListOptions(['users.department', 'faculties.department']),
            'jobTitleOptions' => $this->getDistinctListOptions(['users.job_title', 'faculties.job_title']),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $perPage = in_array((string) $perPage, ['10', '25', '50', '100'], true)
            ? (int) $perPage
            : 10;

        $sortKey = $request->input('sort_key', 'name');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $search = trim((string) $request->input('search', ''));

        $query = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.department',
                'users.job_title',
                'users.role',
                'users.access_level',
                'users.status',
            ])
            ->with('faculty:id,user_id,department,job_title')
            ->leftJoin('faculties as faculty_profiles', 'faculty_profiles.user_id', '=', 'users.id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $builder
                    ->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.department', 'like', $like)
                    ->orWhere('users.job_title', 'like', $like)
                    ->orWhere('users.role', 'like', $like)
                    ->orWhere('users.status', 'like', $like)
                    ->orWhere('users.access_level', 'like', $like)
                    ->orWhere('faculty_profiles.department', 'like', $like)
                    ->orWhere('faculty_profiles.job_title', 'like', $like);
            });
        }

        $this->applyListFilter($query, $request, 'department', ['users.department', 'faculty_profiles.department']);
        $this->applyListFilter($query, $request, 'job', ['users.job_title', 'faculty_profiles.job_title']);
        $this->applyListFilter($query, $request, 'role', ['users.role']);
        $this->applyListFilter($query, $request, 'status', ['users.status']);

        $sortColumns = [
            'name' => 'users.name',
            'department' => DB::raw('COALESCE(NULLIF(users.department, ""), faculty_profiles.department, "")'),
            'job' => DB::raw('COALESCE(NULLIF(users.job_title, ""), faculty_profiles.job_title, "")'),
            'role' => 'users.role',
            'access' => 'users.access_level',
            'status' => 'users.status',
        ];

        $query->orderBy($sortColumns[$sortKey] ?? 'users.name', $sortDir)
            ->orderBy('users.id');

        $users = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $users->getCollection()
                ->map(fn (User $user) => $this->serializeUserForList($user))
                ->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem() ?? 0,
                'to' => $users->lastItem() ?? 0,
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'department' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:Admin,NTP,Faculty,Student'],
            'access_level' => ['nullable', 'array'],
            'access_level.*' => ['in:View All Reports,View Department Reports,Manage Faculties,Manage Courses,Manage Schedules,Manage Evaluations,Manage Evaluation QR/Link,View/Answer Forms'],
        ]);

        $accessLevels = $this->normaliseAccessLevels($validated['role'], $request->input('access_level', []));

        $validated['department'] = Faculty::serializeDepartmentList(
            Faculty::normalizeDepartmentList($validated['department'])
        );

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'role' => $validated['role'],
            'access_level' => $accessLevels,
            'status' => 'Active',
            'password' => Hash::make(Str::random(32)),
        ]);

        return redirect()
            ->route('um.users')
            ->with('success', 'User added successfully.');
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:Admin,NTP,Faculty,Student'],
            'access_level' => ['nullable', 'array'],
            'access_level.*' => ['in:View All Reports,View Department Reports,Manage Faculties,Manage Courses,Manage Schedules,Manage Evaluations,Manage Evaluation QR/Link,View/Answer Forms'],
            'status' => ['nullable', 'in:Active,Inactive'],
        ]);

        $role = $validated['role'] ?? $user->role;
        $validated['access_level'] = $this->normaliseAccessLevels($role, $request->input('access_level', []));

        if (array_key_exists('department', $validated)) {
            $validated['department'] = Faculty::serializeDepartmentList(
                Faculty::normalizeDepartmentList($validated['department'])
            );
        }

        $user->update($validated);
        if ($user->faculty) {
            $facultyUpdates = [];
            if (array_key_exists('department', $validated)) {
                $facultyUpdates['department'] = $validated['department'];
            }
            if (array_key_exists('job_title', $validated)) {
                $facultyUpdates['job_title'] = $validated['job_title'];
            }
            if (!empty($facultyUpdates)) {
                $user->faculty->update($facultyUpdates);
            }
        }

        if ($request->expectsJson()) {
            $status = $user->status ?? 'Inactive';
            $statusSlug = strtolower($status);
            $statusLabel = $statusSlug === 'active' ? 'Active' : ($statusSlug === 'inactive' ? 'Inactive' : $status);
            $toggleLabel = $statusSlug === 'active' ? 'Deactivate' : 'Activate';

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => [
                    'status' => $statusSlug,
                    'status_label' => $statusLabel,
                    'status_value' => $statusLabel,
                    'toggle_label' => $toggleLabel,
                ],
            ]);
        }

        return redirect()
            ->route('um.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Bulk delete users.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = collect($validated['ids'])
            ->unique()
            ->filter(function ($id) {
                return (int) $id !== (int) Auth::id();
            })
            ->values();

        if ($ids->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No deletable users selected.',
            ], 422);
        }

        $deleted = User::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} users deleted successfully."
                : 'User deleted successfully.',
            'deleted' => $ids,
        ]);
    }

    private function applyListFilter($query, Request $request, string $key, array $columns): void
    {
        $value = strtolower(trim((string) $request->input($key, 'all')));
        if ($value === '' || $value === 'all') {
            return;
        }

        $query->where(function ($builder) use ($columns, $value) {
            foreach ($columns as $column) {
                $builder->orWhereRaw("LOWER(COALESCE({$column}, '')) LIKE ?", ['%' . $value . '%']);
            }
        });
    }

    private function getDistinctListOptions(array $columns)
    {
        $options = collect();

        foreach ($columns as $column) {
            [$table, $field] = explode('.', $column, 2);
            $options = $options->merge(
                DB::table($table)
                    ->whereNotNull($field)
                    ->where($field, '!=', '')
                    ->pluck($field)
            );
        }

        return $options
            ->flatMap(function ($value) {
                return collect(explode(',', (string) $value))
                    ->map(fn ($item) => trim($item))
                    ->filter(fn ($item) => $item !== '');
            })
            ->unique()
            ->sort()
            ->values();
    }

    private function serializeUserForList(User $user): array
    {
        $resolvedDepartment = trim($user->department ?? '') !== ''
            ? $user->department
            : optional($user->faculty)->department;
        $resolvedJobTitle = trim($user->job_title ?? '') !== ''
            ? $user->job_title
            : optional($user->faculty)->job_title;

        $departmentRaw = trim($resolvedDepartment ?? '');
        $departmentList = collect(explode(',', $departmentRaw))
            ->map(fn ($value) => trim($value))
            ->filter(fn ($value) => $value !== '')
            ->values();
        $resolvedDepartmentLabel = $departmentList->isEmpty()
            ? '—'
            : $departmentList->implode(', ');
        $resolvedJobTitle = trim($resolvedJobTitle ?? '') !== '' ? $resolvedJobTitle : '—';
        $rawStatus = $user->status ?? '';
        $statusSlug = strtolower($rawStatus ?: 'inactive');
        $statusClass = in_array($statusSlug, ['active', 'enabled'], true)
            ? 'active'
            : (in_array($statusSlug, ['pending', 'invited'], true) ? 'pending' : 'inactive');
        $statusValue = $statusSlug === 'active'
            ? 'Active'
            : ($statusSlug === 'inactive' ? 'Inactive' : $rawStatus);
        $rawRole = $user->role ?? '';
        $roleLower = strtolower($rawRole);
        $roleValue = $roleLower === 'admin'
            ? 'Admin'
            : ($roleLower === 'ntp'
                ? 'NTP'
                : ($roleLower === 'faculty'
                    ? 'Faculty'
                    : ($roleLower === 'student' ? 'Student' : $rawRole)));
        $accessLevels = collect($user->access_level ?? [])
            ->filter(fn ($level) => trim($level ?? '') !== '')
            ->values();

        return [
            'id' => $user->id,
            'name' => $user->name ?? 'Unnamed User',
            'email' => $user->email ?? '',
            'department_raw' => $departmentRaw,
            'departments' => $departmentList->values(),
            'department_label' => $resolvedDepartmentLabel,
            'job_title' => $resolvedJobTitle,
            'role' => $roleValue ?: 'User',
            'access_levels' => $accessLevels->values(),
            'access_levels_label' => $accessLevels->isEmpty() ? '—' : $accessLevels->implode(', '),
            'status_label' => $statusValue ?: 'Inactive',
            'status_value' => $statusValue ?: 'Inactive',
            'status_slug' => $statusSlug,
            'status_class' => $statusClass,
            'is_current_user' => auth()->check() && auth()->id() === $user->id,
        ];
    }

    private function normaliseAccessLevels(string $role, array $accessLevels): array
    {
        $allLevels = [
            'View All Reports',
            'View Department Reports',
            'Manage Faculties',
            'Manage Courses',
            'Manage Schedules',
            'Manage Evaluations',
            'Manage Evaluation QR/Link',
            'View/Answer Forms',
        ];

        if ($role === 'Admin') {
            $accessLevels = $allLevels;
        } elseif ($role === 'Student') {
            $accessLevels = ['View/Answer Forms'];
        }

        $levels = collect($accessLevels)
            ->filter(function ($level) {
                return trim($level ?? '') !== '';
            })
            ->unique()
            ->values();

        if ($levels->contains('View All Reports')) {
            $levels = $levels->reject(function ($level) {
                return $level === 'View Department Reports';
            });
        }

        if ($levels->contains('Manage Evaluations')) {
            $levels = $levels->reject(function ($level) {
                return $level === 'Manage Evaluation QR/Link';
            });
        }

        return $levels->values()->all();
    }
}
