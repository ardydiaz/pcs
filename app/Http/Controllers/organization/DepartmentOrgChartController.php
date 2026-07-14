<?php

namespace App\Http\Controllers\organization;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DepartmentOrgChartController extends Controller
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
    ];

    public function index(Request $request)
    {
        $authUser = $request->user();
        $isAdmin = $authUser?->role === 'Admin';
        $allowedDepartments = $isAdmin ? [] : $this->departmentsForUser($authUser);
        $departmentOptions = $this->departmentOptions($allowedDepartments, $isAdmin);
        $selectedDepartment = trim((string) $request->query('department', ''));

        if ($selectedDepartment === '' || !$departmentOptions->contains($selectedDepartment)) {
            $selectedDepartment = $departmentOptions->first();
        }

        $people = $selectedDepartment
            ? $this->peopleForDepartment($selectedDepartment)
            : collect();

        $deans = $people
            ->filter(fn ($person) => $this->isDean($person['job_title']))
            ->sortBy('name')
            ->values();

        $facultyMembers = $people
            ->filter(fn ($person) => $this->isFaculty($person['job_title']))
            ->sortBy('name')
            ->values();

        return view('content.organization.department-org-chart', [
            'departmentOptions' => $departmentOptions,
            'selectedDepartment' => $selectedDepartment,
            'deans' => $deans,
            'facultyMembers' => $facultyMembers,
            'totalPeople' => $people->count(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function cleanDepartments(Request $request)
    {
        abort_unless($request->user()?->role === 'Admin', 403);

        $updatedUsers = $this->cleanDepartmentColumn(User::query(), 'department');
        $updatedFaculties = $this->cleanDepartmentColumn(Faculty::query(), 'department');

        return redirect()
            ->route('department-org-chart')
            ->with('success', "Department cleanup finished. Updated {$updatedUsers} user records and {$updatedFaculties} faculty records.");
    }

    private function departmentOptions(array $allowedDepartments, bool $isAdmin): Collection
    {
        $departments = collect(self::OFFICIAL_DEPARTMENTS);

        if ($isAdmin) {
            return $departments;
        }

        return $departments
            ->filter(fn ($department) => in_array($department, $allowedDepartments, true))
            ->values();
    }

    private function cleanDepartmentColumn($query, string $column): int
    {
        $updated = 0;

        $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->select(['id', $column])
            ->chunkById(200, function ($records) use ($column, &$updated) {
                foreach ($records as $record) {
                    $cleaned = $this->cleanDepartmentValue($record->{$column});

                    if ($cleaned !== null && $cleaned !== $record->{$column}) {
                        $record->update([$column => $cleaned]);
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    private function cleanDepartmentValue(?string $value): ?string
    {
        $rawDepartments = Faculty::normalizeDepartmentList($value);
        $mappedDepartments = collect($rawDepartments)
            ->map(fn ($department) => $this->normalizeDepartmentName($department))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($mappedDepartments)) {
            return $value;
        }

        return Faculty::serializeDepartmentList($mappedDepartments);
    }

    private function peopleForDepartment(string $department): Collection
    {
        return User::query()
            ->select([
                'id',
                'name',
                'email',
                'department',
                'job_title',
                'role',
                'status',
                'avatar',
            ])
            ->with('faculty:id,user_id,employee_no,department,job_title')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'Active');
            })
            ->where(function ($query) {
                $query->where('job_title', 'like', '%Dean%')
                    ->orWhere('job_title', 'like', '%Faculty%')
                    ->orWhereHas('faculty', function ($facultyQuery) {
                        $facultyQuery->where('job_title', 'like', '%Dean%')
                            ->orWhere('job_title', 'like', '%Faculty%');
                    });
            })
            ->get()
            ->map(fn (User $user) => $this->serializePerson($user))
            ->filter(function ($person) use ($department) {
                return in_array($department, $person['departments'], true)
                    && ($this->isDean($person['job_title']) || $this->isFaculty($person['job_title']));
            })
            ->unique('id')
            ->values();
    }

    private function serializePerson(User $user): array
    {
        $faculty = $user->faculty;
        $departments = $this->normalizeDepartments(array_merge(
            Faculty::normalizeDepartmentList($user->department ?? ''),
            Faculty::normalizeDepartmentList($faculty?->department ?? '')
        ));
        $jobTitle = trim((string) ($user->job_title ?: $faculty?->job_title ?: 'Faculty'));
        $name = trim((string) ($user->name ?: 'Unnamed User'));

        return [
            'id' => $user->id,
            'name' => $name,
            'email' => $user->email ?? '',
            'employee_no' => $faculty?->employee_no ?? '',
            'departments' => $departments,
            'department_label' => implode(', ', $departments),
            'job_title' => $jobTitle,
            'avatar' => $user->avatar,
            'initials' => $this->initials($name),
        ];
    }

    private function departmentsForUser(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return $this->normalizeDepartments(array_merge(
            Faculty::normalizeDepartmentList($user->department ?? ''),
            Faculty::normalizeDepartmentList($user->faculty?->department ?? '')
        ));
    }

    private function normalizeDepartments(array $departments): array
    {
        return collect($departments)
            ->map(fn ($department) => $this->normalizeDepartmentName($department))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeDepartmentName(?string $department): ?string
    {
        $department = trim((string) $department);

        if ($department === '') {
            return null;
        }

        foreach (self::OFFICIAL_DEPARTMENTS as $officialDepartment) {
            if (strcasecmp($department, $officialDepartment) === 0) {
                return $officialDepartment;
            }
        }

        $key = $this->departmentKey($department);
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

        return $map[$key] ?? null;
    }

    private function departmentKey(string $department): string
    {
        $key = strtolower($department);
        $key = preg_replace('/[^a-z0-9]+/', ' ', $key);

        return trim(preg_replace('/\s+/', ' ', $key));
    }

    private function isDean(?string $jobTitle): bool
    {
        return str_contains(strtolower(trim((string) $jobTitle)), 'dean');
    }

    private function isFaculty(?string $jobTitle): bool
    {
        return str_contains(strtolower(trim((string) $jobTitle)), 'faculty');
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', $name), -1, PREG_SPLIT_NO_EMPTY);

        return collect($parts)
            ->take(3)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'U';
    }
}
