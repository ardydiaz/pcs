<?php

namespace App\Http\Controllers\data_management; // Controller for managing faculty data, including listing, creating, updating, deleting, and importing faculty records

use App\Http\Controllers\Controller; // Base controller class for handling HTTP requests related to faculty data management
use Illuminate\Http\Request; // Class for handling HTTP requests and validating input data
use Illuminate\Http\JsonResponse; // Class for sending JSON responses back to the client
use App\Models\Faculty; // Eloquent model representing the Faculty entity, used for database interactions related to faculty records
use App\Models\FacultyCourse; // Eloquent model representing the FacultyCourse entity, used for managing faculty-course assignments and related schedules
use App\Models\Schedule; // Eloquent model representing the Schedule entity, used for managing schedules associated with faculty-course assignments
use App\Models\User; // Eloquent model representing the User entity, used for managing user accounts linked to faculty records
use App\Imports\FacultyUserImport; // Import class for handling the import of faculty and user data from Excel files, utilizing the Maatwebsite Excel package for parsing and processing the data
use App\Imports\ImportAll; // Import class for handling the import of faculty, course, and schedule data from Excel files, utilizing the Maatwebsite Excel package for parsing and processing the data
use Maatwebsite\Excel\Facades\Excel; // Facade for the Maatwebsite Excel package, providing methods for importing and exporting Excel files, used in the import function to process uploaded faculty and user data from Excel files
use Illuminate\Support\Facades\DB; // Facade for database operations, used for handling transactions when creating, updating, and deleting faculty records along with their related user accounts and assignments to ensure data integrity during complex operations that involve multiple database interactions
use Illuminate\Support\Facades\Http; // Facade for making HTTP requests, integrating with external APIs to admin.mcu.edu.ph/MCU/CampusNet/FacultyLoad3.php to fetch EmployeeNo 

class FacultyController extends Controller // Controller class for managing faculty data, including listing, creating, updating, deleting, and importing faculty records, with methods for handling HTTP requests and performing database operations related to faculty management
{
    public function index() // Display a listing of faculty information
    {
        $user = auth()->user();
        $accessLevels = collect($user?->access_level ?? []);
        $canManageFaculties = $accessLevels->contains('Manage Faculties');
        $shouldFilter = $user && $user->role !== 'Admin' && !$canManageFaculties;
        $department = trim($user?->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);

        if ($shouldFilter && empty($departmentFilters)) {
            $faculties = collect();
            $users = collect();
        } else {
            $facultiesQuery = Faculty::with('user');
            $usersQuery = User::whereNotIn('id', Faculty::pluck('user_id'));

            if ($shouldFilter) {
                $facultiesQuery->forDepartments($departmentFilters);
                if (!empty($departmentFilters)) {
                    $usersQuery->whereIn('department', $departmentFilters);
                }
            }

            $faculties = $facultiesQuery->get();
            $users = $usersQuery->get();
        }

        return view('content.data-management.dm-faculties', compact('faculties', 'users'));
    }

    // Function to integrate Faculty to https://admin.mcu.edu.ph/MCU/HRNet/FindEmployee.rs.php fetch EmployeeNo using their Surname
    // Reminder: everyday & when changes on the internet provider need to login at https://admin.mcu.edu.ph/MCU/HRNet/HRFindEmployee.html.php ,
    // to get new session cookies and update .env MCU_PHPSESSID and MCU_MFA_SESSION values for this to work
    public function searchEmployeeNo(Request $request) // Search for employee numbers by surname using the MCU HRNet POST API 
    {
        $search = strtolower($request->input('query', ''));

        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required.'
            ], 422);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Origin' => 'https://admin.mcu.edu.ph',
            'Referer' => 'https://admin.mcu.edu.ph/MCU/HRNet/HRFindEmployee.html.php',
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Cookie' => 'PHPSESSID=' . env('MCU_PHPSESSID') . '; mfa_session=' . env('MCU_MFA_SESSION'),
        ])
            ->asForm()
            ->post('https://admin.mcu.edu.ph/MCU/HRNet/FindEmployee.rs.php', [
                'C' => 'jsrs1',
                'F' => 'myRSList',
                'P0' => '[' . $search . '||5]', // Search by surname with wildcard, limit to 5 results
            ]);

        preg_match('/<textarea[^>]*>(.*?)<\/textarea>/s', $response->body(), $matches);
        $parsed = isset($matches[1]) ? trim($matches[1]) : null;

        if (!$parsed) {
            return response()->json([
                'success' => false,
                'message' => 'No employees found or session expired.',
                'debug' => app()->environment('local') ? $response->body() : null,
            ]);
        }

        $lines = array_filter(explode("\n", $parsed));
        $employees = [];

        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line));

            if (count($parts) >= 2) {
                $employees[] = [
                    'employee_no' => $parts[0],
                    'name' => $parts[1],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function import(Request $request) // Import faculty and user data from Excel file 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        $import = new FacultyUserImport();
        Excel::import($import, $request->file('file')); // Use the FacultyUserImport class to process the uploaded Excel file

        $skipped = $import->getSkippedRecords();
        $imported = $import->getImportedCount();

        // Return JSON response for AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $imported > 0 
                    ? "{$imported} faculty record" . ($imported !== 1 ? 's' : '') . " imported successfully!"
                    : 'No new records to import.',
                'imported' => $imported,
                'skipped' => $skipped,
            ]);
        }

        // Fallback: Redirect with session flash for non-AJAX requests
        return redirect()->back()->with('success', "{$imported} faculty record" . ($imported !== 1 ? 's' : '') . " imported successfully!");
    }

    // public function importAll(Request $request) // Import all from faculties, courses, and schedules data from Excel file
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv'
    //     ]);

    //     $import = new ImportAll();
    //     Excel::import($import, $request->file('file')); // Use the ImportAll class to process the uploaded Excel file

    //     $skipped = $import->getSkippedRecords();
    //     $imported = $import->getImportedCount();
    //     $debug = $import->getDebugLog();

    //     // Return JSON response for AJAX request
    //     if ($request->wantsJson()) {
    //         $response = [
    //             'success' => true,
    //             'message' => $imported > 0 
    //                 ? "{$imported} record" . ($imported !== 1 ? 's' : '') . " imported successfully!"
    //                 : 'No new records to import.',
    //             'imported' => $imported,
    //             'skipped' => count($skipped),
    //         ];

    //         // Include debug info if records were skipped
    //         if (!empty($skipped)) {
    //             $response['skipped_details'] = array_slice($skipped, 0, 5); // Show first 5 skipped records
    //             $response['debug_message'] = 'Review "Skipped Records" in import results to see detailed error messages';
    //         }

    //         return response()->json($response);
    //     }

    //     // Fallback: Redirect with session flash for non-AJAX requests
    //     return redirect()->back()->with('success', "{$imported} record" . ($imported !== 1 ? 's' : '') . " imported successfully!");
    // } // // use excel format example to import all data (faculties, courses, schedules) from excel file in ImportAll_Template.csv


    public function importAll(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    $import = new ImportAll();

    Excel::import($import, $request->file('file'));

    $imported = $import->getImportedCount();
    $skipped = $import->getSkippedRecords();

    return response()->json([
        'success' => $imported > 0,
        'message' => $imported > 0
            ? "$imported records imported successfully"
            : "No records imported",
        'imported' => $imported,
        'skipped' => count($skipped),
        'skipped_details' => array_slice($skipped, 0, 5),
    ]);
}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:faculties,user_id',
            'user_name' => 'nullable|string|max:255',
            'employee_no' => 'required|string|unique:faculties,employee_no',
            'department' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
        ]);

        if (empty($validated['user_id']) && empty($validated['user_name'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a faculty name or select a registered user.',
            ], 422);
        }

        $validated['department'] = Faculty::serializeDepartmentList(
            Faculty::normalizeDepartmentList($validated['department'])
        );

        $faculty = DB::transaction(function () use ($validated) {
            $user = null;
            if (!empty($validated['user_id'])) {
                $user = User::find($validated['user_id']);
            } elseif (!empty($validated['user_name'])) {
                $user = User::create([
                    'name' => $validated['user_name'],
                    'department' => $validated['department'],
                    'job_title' => $validated['job_title'],
                    'role' => 'Faculty',
                    'status' => 'Active',
                ]);
            }

            $faculty = Faculty::create([
                'user_id' => $user?->id,
                'employee_no' => $validated['employee_no'],
                'department' => $validated['department'],
                'job_title' => $validated['job_title'],
                'created_by' => auth()->user()?->name, // Store the name of the authenticated user who created this faculty record for audit purposes
            ]);
            if ($user) {
                $user->update([
                    'department' => $validated['department'],
                    'job_title' => $validated['job_title'],
                ]);
            }
            return $faculty;
        });
        $faculty->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Faculty member added successfully!',
            'data' => $faculty
        ]);
    }

    public function update(Request $request, Faculty $faculty): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:faculties,user_id,' . $faculty->id,
            'employee_no' => 'required|string|unique:faculties,employee_no,' . $faculty->id,
            'department' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
        ]);

        $validated['department'] = Faculty::serializeDepartmentList(
            Faculty::normalizeDepartmentList($validated['department'])
        );

        DB::transaction(function () use ($faculty, $validated) {
            $faculty->update($validated);
            $user = $faculty->user;
            if ($user) {
                $user->update([
                    'department' => $validated['department'],
                    'job_title' => $validated['job_title'],
                ]);
            }
        });
        $faculty->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Faculty member updated successfully!',
            'data' => $faculty
        ]);
    }

    public function destroy(Faculty $faculty): JsonResponse
    {
        DB::transaction(function () use ($faculty) {
            $this->deleteFacultyAssignments([$faculty->id]);
            $faculty->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Faculty member deleted successfully!'
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:faculties,id',
        ]);

        $ids = collect($validated['ids'])->unique()->all();

        $deleted = DB::transaction(function () use ($ids) {
            $this->deleteFacultyAssignments($ids);
            return Faculty::whereIn('id', $ids)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} faculty members deleted successfully!"
                : 'Faculty member deleted successfully!',
            'deleted' => $ids,
        ]);
    }

    /**
     * Remove faculty-course assignments and schedules for the given faculty ids.
     */
    private function deleteFacultyAssignments(array $facultyIds): void
    {
        $facultyIds = collect($facultyIds)->filter()->unique()->values();

        if ($facultyIds->isEmpty()) {
            return;
        }

        $facultyCourseIds = FacultyCourse::whereIn('faculty_id', $facultyIds)->pluck('id')->all();

        if (empty($facultyCourseIds)) {
            return;
        }

        Schedule::whereIn('faculty_course_id', $facultyCourseIds)->delete();
        FacultyCourse::whereIn('id', $facultyCourseIds)->delete();
    }

    /**
     * Debug endpoint to test searchEmployeeNo with detailed output
     */
    public function debugSearchEmployee()
    {
        $phpSessionId = env('MCU_PHPSESSID', '');
        $mfaSession = env('MCU_MFA_SESSION', '');
        
        $debugInfo = [
            'app_env' => env('APP_ENV'),
            'cookies_configured' => [
                'MCU_PHPSESSID' => !empty($phpSessionId) ? 'YES (length: ' . strlen($phpSessionId) . ')' : 'NO',
                'MCU_MFA_SESSION' => !empty($mfaSession) ? 'YES (length: ' . strlen($mfaSession) . ')' : 'NO',
            ],
            'recent_logs' => $this->getRecentLogs(50),
        ];
        
        $html = '<html><head><title>Employee Search Debug</title>';
        $html .= '<style>body { font-family: Arial, sans-serif; margin: 20px; } 
                 .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
                 .search-form { background: white; border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
                 input, button { padding: 8px; margin: 5px; }
                 .log-entry { background: white; border-left: 3px solid #333; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 12px; }
                 .error { border-left-color: #d32f2f; } .warning { border-left-color: #f57c00; } .info { border-left-color: #1976d2; }
                 .section h2 { margin-top: 0; }
                 </style></head><body>';
        
        $html .= '<h1>MCU HRNet Employee Search Debug</h1>';
        
        // Configuration Info
        $html .= '<div class="section"><h2>Configuration Status</h2>';
        $html .= '<p><strong>Environment:</strong> ' . $debugInfo['app_env'] . '</p>';
        $html .= '<p><strong>MCU_PHPSESSID:</strong> ' . $debugInfo['cookies_configured']['MCU_PHPSESSID'] . '</p>';
        $html .= '<p><strong>MCU_MFA_SESSION:</strong> ' . $debugInfo['cookies_configured']['MCU_MFA_SESSION'] . '</p>';
        $html .= '</div>';
        
        // Test Form
        $html .= '<div class="search-form">';
        $html .= '<h2>Test Search</h2>';
        $html .= '<form id="testForm">';
        $html .= '<div>';
        $html .= '<label for="surname">Search Surname:</label><br>';
        $html .= '<input type="text" id="surname" name="surname" placeholder="e.g., RODILLAS" style="width: 300px;" required>';
        $html .= '</div><br>';
        $html .= '<button type="button" onclick="testSearch()">Test Search</button>';
        $html .= '<div id="result" style="margin-top: 20px; display:none;"></div>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Recent Logs
        $html .= '<div class="section"><h2>Recent Logs (Last 50 entries)</h2>';
        if (!empty($debugInfo['recent_logs'])) {
            foreach ($debugInfo['recent_logs'] as $log) {
                $level = strtolower($log['level']);
                $class = 'log-entry ' . ($level === 'error' ? 'error' : ($level === 'warning' ? 'warning' : 'info'));
                $html .= '<div class="' . $class . '">';
                $html .= '<strong>[' . $log['level'] . ']</strong> ';
                $html .= '<em>' . $log['time'] . '</em><br>';
                $html .= htmlspecialchars($log['message']) . '<br>';
                if (!empty($log['context'])) {
                    $html .= '<pre>' . htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT)) . '</pre>';
                }
                $html .= '</div>';
            }
        } else {
            $html .= '<p>No logs found. Check logs by running: tail -f storage/logs/laravel.log</p>';
        }
        $html .= '</div>';
        
        $html .= '<script>
        function testSearch() {
            const surname = document.getElementById("surname").value;
            const resultDiv = document.getElementById("result");
            resultDiv.style.display = "block";
            resultDiv.innerHTML = "<p>Searching...</p>";
            
            fetch("' . route("faculties.search-employee") . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]")?.content || "",
                },
                body: JSON.stringify({ query: surname })
            })
            .then(r => r.json())
            .then(data => {
                let html = "<h3>Response:</h3>";
                html += "<pre>" + JSON.stringify(data, null, 2) + "</pre>";
                if (data.success && data.data.length > 0) {
                    html += "<h4>Employees Found:</h4><ul>";
                    data.data.forEach(emp => {
                        html += "<li><strong>" + emp.employee_no + "</strong> - " + emp.name + "</li>";
                    });
                    html += "</ul>";
                }
                resultDiv.innerHTML = html;
            })
            .catch(e => {
                resultDiv.innerHTML = "<p style=\"color: red;\">Error: " + e.message + "</p>";
            });
        }
        </script>';
        
        $html .= '</body></html>';
        
        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Extract recent log entries
     */
    private function getRecentLogs(int $limit = 50): array
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $entries = [];
        $currentEntry = null;
        
        foreach (array_reverse($lines) as $line) {
            if (preg_match('/^\[.*?\]\s+(\w+)\.\(\w+\):\s+(.+)$/', $line, $matches)) {
                if ($currentEntry && count($entries) < $limit) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'level' => $matches[1],
                    'time' => substr($line, 1, 19),
                    'message' => $matches[2],
                    'context' => null,
                ];
            } elseif ($currentEntry && (strpos($line, '{') === 0 || strpos($line, '[') === 0)) {
                $json = json_decode($line, true);
                if ($json) {
                    $currentEntry['context'] = $json;
                }
            }
        }
        
        if ($currentEntry && count($entries) < $limit) {
            $entries[] = $currentEntry;
        }
        
        return array_reverse($entries);
    }
}
