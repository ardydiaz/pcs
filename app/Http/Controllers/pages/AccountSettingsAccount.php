<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationResponse;
use App\Models\Faculty;
use App\Models\FacultyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
    $maintenanceEnabled = Cache::get('maintenance.enabled', false);

    return view('content.pages.pages-account-settings-account', compact('maintenanceEnabled'));
  }

  public function profile(Request $request)
  {
    $user = $request->user()->load('faculty');
    $faculty = $user->faculty;
    $departmentSource = $faculty?->department ?: $user->department;
    $departments = Faculty::normalizeDepartmentList($departmentSource);
    $role = strtolower((string) $user->role);

    $stats = [
      'assignments' => 0,
      'active_evaluations' => 0,
      'responses' => 0,
      'average_rating' => 'N/A',
    ];

    if ($faculty) {
      $stats['assignments'] = FacultyCourse::where('faculty_id', $faculty->id)->count();
      $stats['active_evaluations'] = Evaluation::where('faculty_id', $user->id)
        ->where('is_active', true)
        ->count();
      $responsesQuery = EvaluationResponse::whereHas('evaluation', function ($query) use ($user) {
        $query->where('faculty_id', $user->id);
      });
      $stats['responses'] = (clone $responsesQuery)->count();
      $averageRating = (clone $responsesQuery)->avg('effectiveness_rating');
      $stats['average_rating'] = $averageRating ? number_format((float) $averageRating, 2) . '/4' : 'N/A';
    } elseif ($role === 'student') {
      $stats['responses'] = EvaluationResponse::where('student_user_id', $user->id)->count();
    }

    return view('content.pages.profile', [
      'user' => $user,
      'faculty' => $faculty,
      'departments' => $departments,
      'jobTitle' => $faculty?->job_title ?: $user->job_title,
      'employeeNo' => $faculty?->employee_no,
      'accessLevels' => collect($user->access_level ?? [])->values(),
      'stats' => $stats,
    ]);
  }

  public function updateMaintenance(Request $request)
  {
    $enabled = $request->boolean('maintenance_mode');

    Cache::forever('maintenance.enabled', $enabled);

    return back();
  }
}
