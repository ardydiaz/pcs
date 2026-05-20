<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
    $maintenanceEnabled = Cache::get('maintenance.enabled', false);

    return view('content.pages.pages-account-settings-account', compact('maintenanceEnabled'));
  }

  public function updateMaintenance(Request $request)
  {
    $enabled = $request->boolean('maintenance_mode');

    Cache::forever('maintenance.enabled', $enabled);

    return back();
  }
}
