<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessLevelMiddleware
{
    public function handle(Request $request, Closure $next, string $require = ''): Response
    {
        $user = $request->user();
        if (!$user) {
            return $this->deny();
        }

        if ($user->role === 'Admin') {
            return $next($request);
        }

        $role = strtolower((string) $user->role);
        $isStudent = $role === 'student';
        $accessLevels = collect($user->access_level ?? []);
        $hasNonFormAccess = $accessLevels
            ->reject(function ($level) {
                return $level === 'View/Answer Forms';
            })
            ->isNotEmpty();

        $allowed = match ($require) {
            'dashboard', 'settings' => $hasNonFormAccess,
            'reports' => $accessLevels->contains('View All Reports')
                || $accessLevels->contains('View Department Reports'),
            'responses' => $accessLevels->contains('View All Reports')
                || $accessLevels->contains('View Department Reports')
                || $accessLevels->contains('Manage Evaluations')
                || $accessLevels->contains('Manage Evaluation QR/Link'),
            'faculties' => $accessLevels->contains('Manage Faculties'),
            'courses' => $accessLevels->contains('Manage Courses'),
            'schedules' => $accessLevels->contains('Manage Schedules'),
            'evaluations' => $accessLevels->contains('Manage Evaluations')
                || $accessLevels->contains('Manage Evaluation QR/Link'),
            'evaluations.manage' => $accessLevels->contains('Manage Evaluations'),
            'evaluations.qr' => $accessLevels->contains('Manage Evaluations')
                || $accessLevels->contains('Manage Evaluation QR/Link'),
            'forms' => $isStudent || $accessLevels->contains('View/Answer Forms'),
            default => false,
        };

        return $allowed ? $next($request) : $this->deny();
    }

    private function deny(): Response
    {
        return response()
            ->view('content.pages.pages-misc-error', [], 404);
    }
}
