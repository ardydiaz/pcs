<?php

namespace App\Http\Controllers\user_management; // Define the namespace for the controller

use Illuminate\Http\Request; // Make sure to import the Request class if you plan to use it in your controller
use App\Models\AuditLogs; // Import the AuditLogs model to interact with the audit_logs table
use App\Models\User; // Import the User model to get user information for logging
use Illuminate\Support\Facades\Auth; // Import Auth facade to get the authenticated user information
use Illuminate\Support\Facades\Log; // Import Log facade for logging purposes
use App\Http\Controllers\Controller; // Import the base Controller class
use App\Support\AuditLogger;

class AuditLogsController extends Controller // Define the controller class
{
    // log system activity for user actions (registration, login, log out, new faculty record, updated course schedule)
    public function logActivity($user, $action, $method = null, $ipAddress = null, $userAgent = null, $description = null, array $context = [])
    {
        AuditLogger::log($action, array_merge([
            'description' => $description,
            'method' => $method,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ], $context), $user);
    }

    // Display listing of users registered, logged and logged out logs
    public function index(Request $request) // Method to display system activity logs
    {
        $search = $request->input('search'); // Get search input from request
        $actionFilter = $request->input('action'); // Get action filter from request
        $moduleFilter = $request->input('module');
        $severityFilter = $request->input('severity');
        $dateRange = $request->input('date_range'); // Get date range filter from request
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = $request->input('per_page', 20); // Get per page setting, default 20

        $query = AuditLogs::query(); // Query without eager loading roles
        
        if ($search) { 
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('ipAddress', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by action type
        if ($actionFilter) {
            $query->where('action', $actionFilter);
        }

        if ($moduleFilter) {
            $query->where('module', $moduleFilter);
        }

        if ($severityFilter) {
            $query->where('severity', $severityFilter);
        }
        
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

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
 
        // Retrieve system activity logs ordered by creation date, paginated
        $logs = $query->orderBy('created_at', 'desc')->paginate((int) $perPage); 
        $actions = AuditLogs::whereNotNull('action')->distinct()->orderBy('action')->pluck('action');
        $modules = AuditLogs::whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $severities = AuditLogs::whereNotNull('severity')->distinct()->orderBy('severity')->pluck('severity');
 
        // Render the view with logs data
        return view('content.audit-logs.audit-logs', [
            'logs' => $logs, // Pass the logs data to the view
            'filters' => [ // Return current filters
                'search' => $search, // Current search term
                'action' => $actionFilter, // Current action filter
                'module' => $moduleFilter,
                'severity' => $severityFilter,
                'date_range' => $dateRange, // Current date range filter
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'actions' => $actions,
            'modules' => $modules,
            'severities' => $severities,
        ]);
    }
}
