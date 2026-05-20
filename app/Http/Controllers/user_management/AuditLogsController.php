<?php

namespace App\Http\Controllers\user_management; // Define the namespace for the controller

use Illuminate\Http\Request; // Make sure to import the Request class if you plan to use it in your controller
use App\Models\AuditLogs; // Import the AuditLogs model to interact with the audit_logs table
use App\Models\User; // Import the User model to get user information for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade to get the authenticated user information
use Illuminate\Support\Facades\Log; // Import Log facade for logging purposes
use App\Http\Controllers\Controller; // Import the base Controller class

class AuditLogsController extends Controller // Define the controller class
{
    // log system activity for user actions (registration, login, log out, new faculty record, updated course schedule)
    public function logActivity($user, $action, $method, $ipAddress, $userAgent)
    {
        AuditLogs::create([
            'name' => $user->name, // Get the name of the user performing the action e.g John Doe
            'action' => $action, // Description of the action performed e.g faculty_created, login, logout
            'method' => $method, // HTTP method used for the action e.g GET, POST, PUT, DELETE
            'ipAddress' => $ipAddress, // IP address from which the action was performed e.g 192.168.1.1
            'userAgent' => $userAgent, // User agent string of the browser or client used for the action e.g Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36
        ]); // AppServiceProvider will call this method to log activities when certain events occur (e.g user registration, login, logout)
    }

    // Display listing of users registered, logged and logged out logs
    public function index(Request $request) // Method to display system activity logs
    {
        $search = $request->input('search'); // Get search input from request
        $actionFilter = $request->input('action'); // Get action filter from request
        $dateRange = $request->input('date_range'); // Get date range filter from request
        $perPage = $request->input('per_page', 20); // Get per page setting, default 20

        $query = AuditLogs::query(); // Query without eager loading roles
        
        // Search by user name
        if ($search) { 
            $query->where('name', 'like', '%' . $search . '%'); 
        }
        
        // Filter by action type
        if ($actionFilter) {
            $query->where('action', $actionFilter);
        }
        
        // Filter by date range
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                    break;
            }
        }
 
        // Retrieve system activity logs ordered by creation date, paginated
        $logs = $query->orderBy('created_at', 'desc')->paginate((int) $perPage); 
 
        // Render the view with logs data
        return view('content.audit-logs.audit-logs', [
            'logs' => $logs, // Pass the logs data to the view
            'filters' => [ // Return current filters
                'search' => $search, // Current search term
                'action' => $actionFilter, // Current action filter
                'date_range' => $dateRange, // Current date range filter
            ],
        ]);
    }
}
