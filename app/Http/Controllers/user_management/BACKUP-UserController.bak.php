<?php

namespace App\Http\Controllers\user_management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use Illuminate\Support\Facades\Auth;
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
        $users = User::with('faculty')
            ->orderBy('name')
            ->get();

        return view('content.user-management.users', [
            'users' => $users,
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
