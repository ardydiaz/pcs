<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Cache::get('maintenance.enabled', false)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $allowedRoutes = ['login', 'logout', 'microsoft.redirect', 'microsoft.callback'];

        if ($routeName && in_array($routeName, $allowedRoutes, true)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && $user->role === 'Admin') {
            return $next($request);
        }

        return response()
            ->view('content.pages.pages-misc-error', [], 404);
    }
}
