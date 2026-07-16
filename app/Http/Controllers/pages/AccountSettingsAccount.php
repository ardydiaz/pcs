<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationResponse;
use App\Models\Faculty;
use App\Models\FacultyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AccountSettingsAccount extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user()->load('faculty');
    $faculty = $user->faculty;
    $departments = Faculty::normalizeDepartmentList($faculty?->department ?: $user->department);
    $jobTitle = $faculty?->job_title ?: $user->job_title;
    $employeeNo = $faculty?->employee_no;
    $accessLevels = collect($user->access_level ?? [])->values();
    $maintenanceEnabled = Cache::get('maintenance.enabled', false);

    return view('content.pages.pages-account-settings-account', compact(
      'user',
      'faculty',
      'departments',
      'jobTitle',
      'employeeNo',
      'accessLevels',
      'maintenanceEnabled'
    ));
  }

  public function updateAvatar(Request $request)
  {
    $validated = $request->validate([
      'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
    ], [
      'avatar.required' => 'Please choose a profile photo first.',
      'avatar.image' => 'The selected file must be an image.',
      'avatar.max' => 'The profile photo must not be larger than 2MB.',
    ]);

    $user = $request->user();
    $this->deleteStoredAvatar($user->avatar);

    $path = $validated['avatar']->store('profile-avatars', 'public');
    $user->forceFill([
      'avatar' => Storage::url($path),
    ])->save();

    return back()->with('success', 'Profile photo updated successfully.');
  }

  public function removeAvatar(Request $request)
  {
    $user = $request->user();
    $this->deleteStoredAvatar($user->avatar);

    $user->forceFill([
      'avatar' => null,
    ])->save();

    return back()->with('success', 'Profile photo removed successfully.');
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

  private function deleteStoredAvatar(?string $avatar): void
  {
    $avatar = (string) $avatar;
    if (!str_starts_with($avatar, '/storage/')) {
      return;
    }

    $path = ltrim(substr($avatar, strlen('/storage/')), '/');
    if ($path !== '') {
      Storage::disk('public')->delete($path);
    }
  }
}
