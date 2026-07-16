@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  $containerNav = $containerNav ?? 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');
  $authUser = Auth::user();
  $avatarSrc = $authUser?->avatar;
  $displayName = trim((string) ($authUser?->name ?? 'User'));
  $nameParts = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', $displayName), -1, PREG_SPLIT_NO_EMPTY);
  $avatarInitials = collect($nameParts)
    ->take(3)
    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
    ->implode('') ?: 'U';
  $avatarPalettes = [
    ['bg' => '#643189', 'text' => '#ffffff'],
    ['bg' => '#ffd121', 'text' => '#3d2a00'],
    ['bg' => '#3066be', 'text' => '#ffffff'],
    ['bg' => '#0f766e', 'text' => '#ffffff'],
    ['bg' => '#9f1239', 'text' => '#ffffff'],
    ['bg' => '#7c2d12', 'text' => '#ffffff'],
  ];
  $avatarPalette = $avatarPalettes[abs(crc32($authUser?->email ?? $displayName)) % count($avatarPalettes)];
@endphp

@once
  <style>
    .avatar-initials {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 800;
      letter-spacing: 0;
      line-height: 1;
      text-transform: uppercase;
    }

    #layout-navbar,
    #layout-navbar.navbar-detached {
      overflow: visible;
      z-index: 1080;
    }

    #layout-navbar .navbar-nav-right,
    #layout-navbar .navbar-nav,
    #layout-navbar .dropdown-user {
      overflow: visible;
      position: relative;
      z-index: 1081;
    }

    .navbar-user-toggle {
      align-items: center;
      background: rgba(255, 255, 255, 0.13);
      border: 1px solid rgba(255, 255, 255, 0.22);
      border-radius: 999px;
      box-shadow: inset 0 0 0 1px rgba(255, 183, 54, 0.12);
      display: inline-flex;
      gap: 0.45rem;
      padding: 0.28rem 0.42rem 0.28rem 0.3rem !important;
      transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .navbar-user-toggle:hover,
    .navbar-user-toggle.show {
      background: rgba(255, 183, 54, 0.22);
      box-shadow: 0 0.75rem 1.8rem rgba(58, 0, 80, 0.22);
      transform: translateY(-1px);
    }

    .navbar-user-toggle .avatar {
      width: 2.45rem;
      height: 2.45rem;
    }

    .navbar-user-toggle-chevron {
      align-items: center;
      background: rgba(58, 0, 80, 0.42);
      border: 1px solid rgba(255, 255, 255, 0.24);
      border-radius: 999px;
      color: #ffffff;
      display: inline-flex;
      height: 1.45rem;
      justify-content: center;
      width: 1.45rem;
    }

    .navbar-user-toggle[aria-expanded="true"] .navbar-user-toggle-chevron i {
      transform: rotate(180deg);
    }

    .navbar-user-toggle-chevron i {
      font-size: 1rem;
      transition: transform 0.18s ease;
    }

    .navbar-user-menu {
      border: 0;
      border-radius: 1rem;
      box-shadow: 0 1.25rem 3rem rgba(38, 0, 56, 0.24);
      margin-top: 0rem !important;
      min-width: 18rem;
      overflow: hidden;
      padding: 0;
      z-index: 1082;
      top: 4rem !important;
    }

    .navbar-user-menu-header {
      background:
        radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.28), transparent 6rem),
        linear-gradient(135deg, #3a0050, #6f2a8f);
      color: #ffffff;
      padding: 1rem;
    }

    .navbar-user-menu-header h6,
    .navbar-user-menu-header small {
      color: #ffffff !important;
    }

    .navbar-user-menu-body {
      padding: 0.55rem;
    }

    .navbar-user-menu .dropdown-item {
      align-items: center;
      border-radius: 0.75rem;
      color: #243246;
      display: flex;
      font-weight: 700;
      padding: 0.75rem 0.85rem;
    }

    .navbar-user-menu .dropdown-item:hover {
      background: #fbf7ff;
      color: #4b0062;
    }

    .navbar-user-menu .dropdown-item.logout-action {
      background: #fff4d8;
      color: #3a0050;
    }

    .navbar-user-menu .dropdown-item.logout-action:hover {
      background: #ffb736;
      color: #3a0050;
    }

    @media (max-width: 575.98px) {
      .navbar-user-toggle {
        gap: 0.25rem;
      }

      .navbar-user-toggle-chevron {
        height: 1.3rem;
        width: 1.3rem;
      }

      .navbar-user-menu {
        min-width: min(18rem, calc(100vw - 2rem));
      }
    }
  </style>
@endonce

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav
    class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme"
    id="layout-navbar">
@endif
  @if(isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
      <div class="{{$containerNav}}">
  @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span
              class="app-brand-logo demo">@include('_partials.macros', ["width" => 25, "withbg" => 'var(--bs-primary)'])</span>
            <span class="app-brand-text demo menu-text fw-bold text-heading">{{config('variables.templateName')}}</span>
          </a>
        </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
        <div
          class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
          <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="bx bx-menu bx-md"></i>
          </a>
        </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
       <!--  <div class="navbar-nav align-items-center">
          <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search bx-md"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..."
              aria-label="Search...">
          </div>
        </div> -->

        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow navbar-user-toggle" href="javascript:void(0);"
              data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open account menu">
              <div class="avatar avatar-online navbar-profile-ring">
                @if($avatarSrc)
                  <img src="{{ $avatarSrc }}" alt="{{ $displayName }} profile photo"
                    class="w-px-40 h-px-40 rounded-circle object-fit-cover">
                @else
                  <span class="avatar-initials w-px-40 h-px-40 rounded-circle"
                    style="background-color: {{ $avatarPalette['bg'] }}; color: {{ $avatarPalette['text'] }};">
                    {{ $avatarInitials }}
                  </span>
                @endif
              </div>
              <span class="navbar-user-toggle-chevron" aria-hidden="true">
                <i class="bx bx-chevron-down"></i>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end navbar-user-menu">
              <li class="navbar-user-menu-header">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online navbar-profile-ring">
                        @if($avatarSrc)
                          <img src="{{ $avatarSrc }}" alt="{{ $displayName }} profile photo"
                            class="w-px-40 h-px-40 rounded-circle object-fit-cover">
                        @else
                          <span class="avatar-initials w-px-40 h-px-40 rounded-circle"
                            style="background-color: {{ $avatarPalette['bg'] }}; color: {{ $avatarPalette['text'] }};">
                            {{ $avatarInitials }}
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">{{ ucwords($displayName) }}</h6>
                      <small>{{ ucwords($authUser?->role ?? '') }}</small>
                    </div>
                  </div>
              </li>
              <li class="navbar-user-menu-body">
                <a class="dropdown-item" href="{{ route('profile') }}">
                  <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                </a>
                <a class="dropdown-item" href="{{ route('settings') }}">
                  <i class="bx bx-cog bx-md me-3"></i><span>Settings</span>
                </a>
                <div class="dropdown-divider my-2"></div>
              </li>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
              <li class="navbar-user-menu-body pt-0">
                <a class="dropdown-item logout-action" href="#"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                </a>
              </li>
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
        </div>
      @endif
  </nav>
  <!-- / Navbar -->
