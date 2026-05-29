<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            // Get the session timeout in minutes from config
            $sessionTimeout = config('session.lifetime', 120);
            
            // Get the last activity time from session
            $lastActivity = Session::get('last_activity');
            
            // If last activity exists, check if session has expired
            if ($lastActivity) {
                $timeSinceLastActivity = time() - $lastActivity;
                $timeoutInSeconds = $sessionTimeout * 60;
                
                // If session has expired
                if ($timeSinceLastActivity >= $timeoutInSeconds) {
                    // Logout the user
                    Auth::logout();
                    Session::invalidate();
                    Session::regenerateToken();
                    
                    // Redirect to login with timeout message
                    return redirect('/')->with('session_expired', 'Your session has expired due to inactivity. Please login again.');
                }
            }
            
            // Update last activity time
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
}
