<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="{{ asset('storage/images/logo light.png') }}" alt="App Logo" width="100" />
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  @php
    $user = auth()->user();
    $accessLevels = collect($user?->access_level ?? []);
    $isAdmin = $user && $user->role === 'Admin';
    $hasNonFormAccess = $accessLevels
      ->reject(function ($level) {
        return $level === 'View/Answer Forms';
      })
      ->isNotEmpty();
    $canViewReports = $isAdmin || $accessLevels->contains('View All Reports') || $accessLevels->contains('View Department Reports');
    $canManageFaculties = $isAdmin || $accessLevels->contains('Manage Faculties');
    $canManageCourses = $isAdmin || $accessLevels->contains('Manage Courses');
    $canManageSchedules = $isAdmin || $accessLevels->contains('Manage Schedules');
    $canManageEvaluations = $isAdmin || $accessLevels->contains('Manage Evaluations');
    $canManageEvaluationQr = $isAdmin || $accessLevels->contains('Manage Evaluation QR/Link');
    $canAccessDashboard = $isAdmin || $hasNonFormAccess;
    $canAccessSettings = $isAdmin || $hasNonFormAccess;
    $canAccessUsers = $isAdmin;
    $canAccessAuditLogs = $isAdmin; // Only admins can access audit logs

    $visibilityBySlug = [
      'dashboard' => $canAccessDashboard,
      'reports' => $canViewReports,
      'dm.faculties' => $canManageFaculties,
      'dm' => $canManageCourses,
      'dm.courses' => $canManageCourses,
      'dm.schedules' => $canManageSchedules,
      'dm.evaluation' => $canManageEvaluations || $canManageEvaluationQr || $canViewReports,
      'um.users' => $canAccessUsers,
      'um.audit-logs' => $canAccessAuditLogs, // Visibility only to admins for audit logs menu item
      'settings' => $canAccessSettings,
    ];

    $menuItems = $menuData[0]->menu;
    $visibleFlags = [];
    $menuCount = count($menuItems);
    for ($i = 0; $i < $menuCount; $i++) {
      $menu = $menuItems[$i];
      if (isset($menu->menuHeader)) {
        $showHeader = false;
        for ($j = $i + 1; $j < $menuCount; $j++) {
          if (isset($menuItems[$j]->menuHeader)) {
            break;
          }
          $slug = $menuItems[$j]->slug ?? null;
          if ($slug && ($visibilityBySlug[$slug] ?? true)) {
            $showHeader = true;
            break;
          }
        }
        $visibleFlags[$i] = $showHeader;
      } else {
        $slug = $menu->slug ?? null;
        $visibleFlags[$i] = $slug ? ($visibilityBySlug[$slug] ?? true) : true;
      }
    }
  @endphp

  <ul class="menu-inner py-1">
    @for ($index = 0; $index < $menuCount; $index++)
      @if (!($visibleFlags[$index] ?? true))
        @continue
      @endif
      @php
        $menu = $menuItems[$index];
      @endphp

      {{-- adding active and open class if child is active --}}

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
      </li>
      @else

      {{-- active menu method --}}
      @php
      $activeClass = null;
      $currentRouteName = Route::currentRouteName();

      if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
      } elseif (isset($menu->submenu)) {
        foreach ($menu->submenu as $submenu) {
          $submenuSlugs = is_array($submenu->slug ?? null) ? $submenu->slug : [($submenu->slug ?? null)];

          foreach ($submenuSlugs as $slug) {
            if ($slug && ($currentRouteName === $slug || str_starts_with((string) $currentRouteName, $slug . '.'))) {
              $activeClass = 'active open';
              break 2;
            }
          }
        }
      }
      @endphp

      {{-- main menu --}}
      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
        @isset($menu->icon)
        <i class="{{ $menu->icon }}"></i>
      @endisset
        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
        @isset($menu->badge)
        <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}</div>
      @endisset
        </a>

        {{-- submenu --}}
        @isset($menu->submenu)
        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
      @endisset
      </li>
      @endif
  @endfor
  </ul>

</aside>
