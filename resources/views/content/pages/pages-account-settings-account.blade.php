@extends('layouts/contentNavbarLayout')

@section('title', 'Settings')

@php
  $displayName = trim((string) ($user->name ?? 'User'));
  $avatarSrc = $user->avatar;
  $nameParts = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', $displayName), -1, PREG_SPLIT_NO_EMPTY);
  $avatarInitials = collect($nameParts)->take(3)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U';
  $roleLabel = ucwords((string) ($user->role ?? 'User'));
@endphp

@section('content')
<style>
  .settings-page {
    --mcu-purple-midnight: #3a0050;
    --mcu-purple: #5b1b76;
    --mcu-purple-haze: #6f2a8f;
    --mcu-gold: #ffb736;
    --mcu-pink: #ef175c;
    color: #243246;
  }

  .settings-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
    background:
      linear-gradient(90deg, var(--mcu-purple-midnight), var(--mcu-purple) 45%, #5c1f75 72%, #8a3d82) !important;
    box-shadow: 0 1.1rem 2.4rem rgba(58, 0, 80, 0.14);
    color: #ffffff;
    padding: 1.75rem;
  }

  .settings-kicker {
    margin: 0 0 0.35rem;
    color: #ffcc1b;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }

  .settings-title {
    margin: 0;
    color: #ffffff;
    font-size: clamp(1.9rem, 4vw, 2.7rem);
    font-weight: 900;
  }

  .settings-subtitle {
    margin: 0.5rem 0 0;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 700;
  }

  .settings-card {
    border: 1px solid rgba(92, 41, 124, 0.1);
    border-radius: 1.1rem;
    background:
      radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.08), transparent 9rem),
      #ffffff;
    box-shadow: 0 0.75rem 1.8rem rgba(58, 0, 80, 0.08);
  }

  .settings-card-header {
    align-items: center;
    border-bottom: 1px solid rgba(92, 41, 124, 0.09);
    display: flex;
    gap: 0.7rem;
    padding: 1.25rem 1.35rem;
  }

  .settings-card-icon {
    display: grid;
    place-items: center;
    width: 2.55rem;
    height: 2.55rem;
    border-radius: 0.85rem;
    background: #fbf0ff;
    color: #5b1b76;
    font-size: 1.35rem;
  }

  .settings-card-title {
    margin: 0;
    color: #243246;
    font-size: 1.05rem;
    font-weight: 900;
  }

  .settings-card-body {
    padding: 1.35rem;
  }

  .settings-avatar {
    display: grid;
    place-items: center;
    width: 7rem;
    height: 7rem;
    flex: 0 0 auto;
    overflow: hidden;
    border: 4px solid #ffb736;
    border-radius: 999px;
    background: #ffffff;
    color: #5b1b76;
    font-size: 1.75rem;
    font-weight: 900;
    box-shadow: 0 1rem 1.8rem rgba(58, 0, 80, 0.16);
  }

  .settings-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .settings-info-grid {
    display: grid;
    gap: 0.85rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .settings-info-box {
    border: 1px solid rgba(92, 41, 124, 0.1);
    border-radius: 0.9rem;
    background: #fbf9ff;
    padding: 0.85rem 0.95rem;
  }

  .settings-info-box span {
    display: block;
    color: #6b7a90;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .settings-info-box strong {
    display: block;
    color: #243246;
    font-size: 0.98rem;
    font-weight: 800;
    margin-top: 0.2rem;
    overflow-wrap: anywhere;
  }

  .settings-access-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
  }

  .settings-access-badge {
    border-radius: 999px;
    background: #fff4d8;
    color: #3a0050;
    font-weight: 800;
    padding: 0.48rem 0.75rem;
  }

  .settings-admin-panel {
    border: 1px solid rgba(239, 23, 92, 0.16);
    border-radius: 1rem;
    background:
      radial-gradient(circle at 100% 0%, rgba(239, 23, 92, 0.08), transparent 9rem),
      #fffafd;
    padding: 1rem;
  }

  .settings-button-primary {
    background: linear-gradient(135deg, #4b0062, #6f2a8f);
    border: 0;
    color: #ffffff;
    font-weight: 900;
  }

  .settings-button-primary:hover {
    color: #ffffff;
    box-shadow: 0 0.75rem 1.7rem rgba(91, 27, 118, 0.24);
  }

  @media (max-width: 767.98px) {
    .settings-info-grid {
      grid-template-columns: 1fr;
    }

    .settings-avatar-row {
      align-items: flex-start !important;
      flex-direction: column;
    }

    .settings-avatar {
      width: 5.8rem;
      height: 5.8rem;
      font-size: 1.4rem;
    }
  }
</style>

<div class="settings-page">
  <div class="settings-hero mb-4">
    <p class="settings-kicker">Account Settings</p>
    <h1 class="settings-title">Settings</h1>
    <p class="settings-subtitle">
      Manage your profile photo and review your account details.
      @if($user->role === 'Admin')
        Admin system options are available below.
      @endif
    </p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="settings-card">
        <div class="settings-card-header">
          <span class="settings-card-icon"><i class="bx bx-image-add"></i></span>
          <h5 class="settings-card-title">Profile Photo</h5>
        </div>
        <div class="settings-card-body">
          <div class="settings-avatar-row d-flex align-items-center gap-4">
            <div class="settings-avatar">
              @if($avatarSrc)
                <img src="{{ $avatarSrc }}" alt="{{ $displayName }} profile photo">
              @else
                {{ $avatarInitials }}
              @endif
            </div>
            <div class="flex-grow-1">
              <h5 class="mb-1">{{ $displayName }}</h5>
              <p class="mb-3 text-muted">{{ $roleLabel }}</p>
              <form method="POST" action="{{ route('settings.avatar.update') }}" enctype="multipart/form-data" class="mb-2">
                @csrf
                <input class="form-control mb-3" type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp" required>
                <button type="submit" class="btn settings-button-primary">
                  <i class="bx bx-upload me-1"></i>Upload Photo
                </button>
              </form>
              @if($avatarSrc)
                <form method="POST" action="{{ route('settings.avatar.remove') }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-secondary">
                    <i class="bx bx-reset me-1"></i>Remove Photo
                  </button>
                </form>
              @endif
              <small class="d-block mt-3 text-muted">Allowed JPG, PNG, GIF, or WEBP. Maximum size 2MB.</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="settings-card h-100">
        <div class="settings-card-header">
          <span class="settings-card-icon"><i class="bx bx-id-card"></i></span>
          <h5 class="settings-card-title">Account Information</h5>
        </div>
        <div class="settings-card-body">
          <div class="settings-info-grid">
            <div class="settings-info-box">
              <span>Name</span>
              <strong>{{ $displayName }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Email</span>
              <strong>{{ $user->email ?: 'No email listed' }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Role</span>
              <strong>{{ $roleLabel }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Status</span>
              <strong>{{ $user->status ?: 'Active' }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Department</span>
              <strong>{{ !empty($departments) ? implode(', ', $departments) : 'No department listed' }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Job Title</span>
              <strong>{{ $jobTitle ?: 'No job title listed' }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Employee No.</span>
              <strong>{{ $employeeNo ?: 'No employee number linked' }}</strong>
            </div>
            <div class="settings-info-box">
              <span>Provider</span>
              <strong>{{ $user->provider ? ucwords($user->provider) : 'Local Account' }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="settings-card h-100">
        <div class="settings-card-header">
          <span class="settings-card-icon"><i class="bx bx-shield-quarter"></i></span>
          <h5 class="settings-card-title">Access Level</h5>
        </div>
        <div class="settings-card-body">
          <div class="settings-access-list">
            @forelse($accessLevels as $level)
              <span class="settings-access-badge">{{ $level }}</span>
            @empty
              <span class="settings-access-badge">No access level assigned</span>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    @if($user->role === 'Admin')
      <div class="col-lg-5">
        <div class="settings-card h-100">
          <div class="settings-card-header">
            <span class="settings-card-icon"><i class="bx bx-cog"></i></span>
            <h5 class="settings-card-title">Admin System Options</h5>
          </div>
          <div class="settings-card-body">
            <div class="settings-admin-panel">
              <form method="POST" action="{{ route('settings.maintenance') }}">
                @csrf
                <input type="hidden" name="maintenance_mode" value="0">
                <div class="d-flex justify-content-between gap-3 align-items-start">
                  <div>
                    <h6 class="mb-1 fw-bold">Maintenance Mode</h6>
                    <p class="mb-0 text-muted">Show the maintenance page to non-admin users while maintenance is active.</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="maintenanceModeToggle" name="maintenance_mode"
                      value="1" onchange="this.form.submit()" {{ $maintenanceEnabled ? 'checked' : '' }}>
                  </div>
                </div>
                <div class="mt-3">
                  <span class="badge {{ $maintenanceEnabled ? 'bg-label-danger' : 'bg-label-success' }}">
                    {{ $maintenanceEnabled ? 'Maintenance on' : 'Maintenance off' }}
                  </span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
