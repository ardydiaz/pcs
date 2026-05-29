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
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
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
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
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
                      <small class="text-muted">{{ ucwords($authUser?->role ?? '') }}</small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider my-1"></div>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('settings') }}">
                  <i class="bx bx-cog bx-md me-3"></i><span>Settings</span>
                </a>
              </li>
              <li>
                <div class="dropdown-divider my-1"></div>
              </li>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
              <li>
                <a class="dropdown-item" href="#"
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
