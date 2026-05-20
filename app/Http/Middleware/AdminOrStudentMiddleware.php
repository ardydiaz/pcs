<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrStudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->check() ? strtolower((string) auth()->user()->role) : null;

        if ($role && in_array($role, ['admin', 'student'], true)) {
            return $next($request);
        }

        return response()
            ->view('content.pages.pages-misc-error', [], 404);
    }
}
