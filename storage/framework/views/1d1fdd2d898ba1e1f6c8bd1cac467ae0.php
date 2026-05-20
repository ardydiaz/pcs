<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo">
    <a href="<?php echo e(url('/')); ?>" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img src="<?php echo e(asset('storage/images/logo light.png')); ?>" alt="App Logo" width="100" />
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <?php
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
      'dm.courses' => $canManageCourses,
      'dm.schedules' => $canManageSchedules,
      'dm.evaluation' => $canManageEvaluations || $canManageEvaluationQr,
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
  ?>

  <ul class="menu-inner py-1">
    <?php for($index = 0; $index < $menuCount; $index++): ?>
      <?php if(!($visibleFlags[$index] ?? true)): ?>
        <?php continue; ?>
      <?php endif; ?>
      <?php
        $menu = $menuItems[$index];
      ?>

      

      
      <?php if(isset($menu->menuHeader)): ?>
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text"><?php echo e(__($menu->menuHeader)); ?></span>
      </li>
      <?php else: ?>

      
      <?php
      $activeClass = null;
      $currentRouteName = Route::currentRouteName();

      if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
      } elseif (isset($menu->submenu)) {
        if (gettype($menu->slug) === 'array') {
        foreach ($menu->slug as $slug) {
        if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
        $activeClass = 'active open';
        }
        }
        } else {
        if (str_contains($currentRouteName, $menu->slug) and strpos($currentRouteName, $menu->slug) === 0) {
        $activeClass = 'active open';
        }
        }
      }
      ?>

      
      <li class="menu-item <?php echo e($activeClass); ?>">
        <a href="<?php echo e(isset($menu->url) ? url($menu->url) : 'javascript:void(0);'); ?>"
        class="<?php echo e(isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link'); ?>" <?php if(isset($menu->target) and !empty($menu->target)): ?> target="_blank" <?php endif; ?>>
        <?php if(isset($menu->icon)): ?>
        <i class="<?php echo e($menu->icon); ?>"></i>
      <?php endif; ?>
        <div><?php echo e(isset($menu->name) ? __($menu->name) : ''); ?></div>
        <?php if(isset($menu->badge)): ?>
        <div class="badge rounded-pill bg-<?php echo e($menu->badge[0]); ?> text-uppercase ms-auto"><?php echo e($menu->badge[1]); ?></div>
      <?php endif; ?>
        </a>

        
        <?php if(isset($menu->submenu)): ?>
        <?php echo $__env->make('layouts.sections.menu.submenu', ['menu' => $menu->submenu], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php endif; ?>
      </li>
      <?php endif; ?>
  <?php endfor; ?>
  </ul>

</aside>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/layouts/sections/menu/verticalMenu.blade.php ENDPATH**/ ?>