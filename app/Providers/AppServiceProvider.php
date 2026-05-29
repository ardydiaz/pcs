<?php

namespace App\Providers;

use App\Models\Course; // Make sure to import the Course model
use App\Models\Faculty; // Make sure to import the Faculty model
use App\Models\FacultyCourse; // Make sure to import the FacultyCourse model
use App\Models\Schedule; // Make sure to import the Schedule model
use App\Models\User; // Make sure to import the User model
use App\Observers\CourseObserver; // Make sure to import the CourseObserver
use App\Observers\FacultyCourseObserver; // Make sure to import the FacultyCourseObserver
use App\Observers\FacultyObserver; // Make sure to import the FacultyObserver
use App\Observers\ScheduleObserver; // Make sure to import the ScheduleObserver
use App\Observers\UserObserver; // Make sure to import the UserObserver
use Illuminate\Support\ServiceProvider; // Make sure to import the base ServiceProvider class
use App\Http\Controllers\user_management\AuditLogsController; // Import the AuditLogsController to log activities
use Illuminate\Support\Facades\Event; // Import Event facade to listen for model events
use App\Support\AuditLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class); // Register the UserObserver to listen for events on the User model
        Faculty::observe(FacultyObserver::class); // Register the FacultyObserver to listen for events on the Faculty model
        Course::observe(CourseObserver::class); // Register the CourseObserver to listen for events on the Course model
        FacultyCourse::observe(FacultyCourseObserver::class); // Register the FacultyCourseObserver to listen for events on the FacultyCourse model
        Schedule::observe(ScheduleObserver::class); // Register the ScheduleObserver to listen for events on the Schedule model

        // Register event listeners for logging activities in the AuditLogsController
        // Register system activity logging for user registration, login, and logout
        User::created(function ($user) {
            AuditLogger::log('user_registration', [
                'module' => 'User Management',
                'description' => "User registered: {$user->name}",
                'target_type' => User::class,
                'target_id' => $user->id,
                'after_values' => $user->only(['name', 'email', 'role', 'status', 'department', 'job_title']),
                'severity' => 'info',
            ], $user);
        });

        // Listen for the Login event to log user login activity
        \Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            AuditLogger::log('login', [
                'module' => 'Authentication',
                'description' => 'User logged in.',
                'severity' => 'info',
            ], $event->user);
        });

        //
        \Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                AuditLogger::log('logout', [
                    'module' => 'Authentication',
                    'description' => 'User logged out.',
                    'severity' => 'info',
                ], $event->user);
            }
        });

        // Listen for the Faculty created event to log faculty record creation
        Faculty::created(function ($faculty) {
            $authUser = auth()->user();
            if ($authUser) {
                AuditLogger::log('faculty_created', [
                    'module' => 'Faculty',
                    'description' => "New faculty record created: {$faculty->employee_no}",
                    'target_type' => Faculty::class,
                    'target_id' => $faculty->id,
                    'after_values' => $faculty->only(['user_id', 'employee_no', 'department', 'job_title', 'created_by']),
                    'severity' => 'info',
                ], $authUser);
            }
        });
    }
}
