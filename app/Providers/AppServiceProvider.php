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
            $req = request();
            (new AuditLogsController)->logActivity(
                $user,
                'user_registration',
                $req->method(),
                $req->ip(),
                $req->header('User-Agent'),
                'User registered.'
            );
        });

        // Listen for the Login event to log user login activity
        \Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            $req = request();
            (new AuditLogsController)->logActivity(
                $event->user,
                'login',
                $req->method(),
                $req->ip(),
                $req->header('User-Agent'),
                'User logged in.'
            );
        });

        //
        \Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            $req = request();
            (new AuditLogsController)->logActivity(
                $event->user,
                'logout',
                $req->method(),
                $req->ip(),
                $req->header('User-Agent'),
                'User logged out.'
            );
        });

        // Listen for the Faculty created event to log faculty record creation
        Faculty::created(function ($faculty) {
            $authUser = auth()->user();
            if ($authUser) {
                $req = request();
                (new AuditLogsController)->logActivity(
                    $authUser,
                    'faculty_created',
                    $req->method(),
                    $req->ip(),
                    $req->header('User-Agent'),
                    "New faculty record created: {$faculty->employee_no}"
                );
            }
        });
    }
}
