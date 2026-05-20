<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $normalizeDepartments = function (?string $departments): array {
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
        };

        $serializeDepartments = function (array $departments): ?string {
            $normalized = array_map(static fn ($value) => trim((string) $value), $departments);
            $filtered = array_filter($normalized, static fn ($value) => $value !== '');
            $unique = array_values(array_unique($filtered));

            return $unique === [] ? null : implode(', ', $unique);
        };

        DB::table('evaluations')
            ->select(['id', 'faculty_id'])
            ->orderBy('id')
            ->chunkById(200, function ($evaluations) use ($normalizeDepartments, $serializeDepartments) {
                $userIds = $evaluations->pluck('faculty_id')->filter()->unique()->values();

                if ($userIds->isEmpty()) {
                    return;
                }

                $users = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');

                $faculties = DB::table('faculties')
                    ->whereIn('user_id', $userIds)
                    ->get()
                    ->keyBy('user_id');

                foreach ($evaluations as $evaluation) {
                    $user = $users->get($evaluation->faculty_id);
                    if (!$user) {
                        continue;
                    }

                    $faculty = $faculties->get($evaluation->faculty_id);
                    $departmentList = array_merge(
                        $normalizeDepartments($user->department ?? ''),
                        $normalizeDepartments($faculty->department ?? '')
                    );

                    DB::table('evaluations')
                        ->where('id', $evaluation->id)
                        ->update([
                            'faculty_name_snapshot' => $user->name,
                            'faculty_email_snapshot' => $user->email,
                            'faculty_department_snapshot' => $serializeDepartments($departmentList),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No-op: snapshot refresh is intentionally irreversible.
    }
};
