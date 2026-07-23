@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

@php
  $displayName = trim((string) ($user->name ?? 'User'));
  $avatarSrc = $user->avatar;
  $nameParts = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', $displayName), -1, PREG_SPLIT_NO_EMPTY);
  $avatarInitials = collect($nameParts)->take(3)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U';
  $roleLabel = ucwords((string) ($user->role ?? 'User'));
  $statusLabel = $user->status ?: 'Active';
@endphp

@section('content')
<style>
  .profile-page {
    --mcu-purple-midnight: #3a0050;
    --mcu-purple: #5b1b76;
    --mcu-purple-haze: #6f2a8f;
    --mcu-gold: #ffb736;
    --mcu-pink: #ef175c;
    --mcu-blue: #14abbb;
    color: #243246;
  }

  .profile-hero {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(92, 41, 124, 0.12);
    border-radius: 1.25rem;
background: radial-gradient(circle at 85% 15%, rgba(255, 183, 54, 0.22), transparent 0 7rem), linear-gradient(120deg, #3a0050, #5c297c, rgba(240, 24, 77, 0.76));
    box-shadow: 0 1.1rem 2.4rem rgba(58, 0, 80, 0.14);
    color: #fff;
    padding: 2rem;
  }


  .profile-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 1.25rem;
  }

  .profile-avatar {
    display: grid;
    place-items: center;
    width: 7rem;
    height: 7rem;
    flex: 0 0 auto;
    overflow: hidden;
    border: 4px solid #ffb736;
    border-radius: 999px;
    background: #fff;
    color: #5b1b76;
    font-size: 1.8rem;
    font-weight: 900;
    box-shadow: 0 1.2rem 2rem rgba(0, 0, 0, 0.22);
  }

  .profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .profile-kicker {
    margin: 0 0 0.35rem;
    color: #ffcc1b;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }

  .profile-name {
    margin: 0;
    color: #fff;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 900;
    line-height: 1.05;
  }

  .profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
  }

  .profile-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.13);
    color: #fff;
    font-size: 0.82rem;
    font-weight: 800;
    padding: 0.42rem 0.75rem;
  }

  .profile-card {
    border: 1px solid rgba(92, 41, 124, 0.1);
    border-radius: 1.1rem;
    background:
      radial-gradient(circle at 100% 0%, rgba(255, 183, 54, 0.08), transparent 9rem),
      #ffffff;
    box-shadow: 0 0.75rem 1.8rem rgba(58, 0, 80, 0.08);
  }

  .profile-info-card {
    height: 100%;
  }

  .profile-card-header {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1rem;
  }

  .profile-card-icon {
    display: grid;
    place-items: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.8rem;
    background: #fbf0ff;
    color: #5b1b76;
    font-size: 1.3rem;
  }

  .profile-card-title {
    margin: 0;
    color: #243246;
    font-size: 1rem;
    font-weight: 900;
  }

  .profile-info-list {
    display: grid;
    gap: 0.75rem;
    margin: 0;
  }

  .profile-info-row {
    display: grid;
    gap: 0.2rem;
    padding: 0.85rem 0.95rem;
    border: 1px solid rgba(92, 41, 124, 0.1);
    border-radius: 0.9rem;
    background: #fbf9ff;
  }

  .profile-info-row span {
    color: #6b7a90;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .profile-info-row strong {
    color: #243246;
    font-size: 0.98rem;
    font-weight: 800;
    overflow-wrap: anywhere;
  }

  .profile-stat {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    background: radial-gradient(circle at 85% 15%, rgba(255, 183, 54, 0.22), transparent 0 7rem), linear-gradient(120deg, #3a0050, #5c297c, rgba(240, 24, 77, 0.76));
    color: #fff;
    padding: 1.2rem;
  }

  .profile-stat small,
  .profile-stat strong {
    position: relative;
    z-index: 1;
    color: #fff;
  }

  .profile-stat small {
    display: block;
    margin-bottom: 0.65rem;
    font-weight: 800;
  }

  .profile-stat strong {
    display: block;
    font-size: 1.8rem;
    font-weight: 900;
    line-height: 1;
  }

  .profile-access-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
  }

  .profile-access-badge {
    border-radius: 999px;
    background: #fff4d8;
    color: #3a0050;
    font-weight: 800;
    padding: 0.48rem 0.75rem;
  }

  @media (max-width: 767.98px) {
    .profile-hero {
      padding: 1.35rem;
    }

    .profile-hero-content {
      align-items: flex-start;
      flex-direction: column;
    }

    .profile-avatar {
      width: 5.8rem;
      height: 5.8rem;
      font-size: 1.45rem;
    }
  }
</style>

<div class="profile-page">
  <div class="profile-hero mb-4">
    <div class="profile-hero-content">
      <div class="profile-avatar">
        @if($avatarSrc)
          <img src="{{ $avatarSrc }}" alt="{{ $displayName }} profile photo">
        @else
          {{ $avatarInitials }}
        @endif
      </div>
      <div>
        <p class="profile-kicker">My Profile</p>
        <h1 class="profile-name">{{ $displayName }}</h1>
        <div class="profile-meta">
          <span class="profile-chip"><i class="bx bx-id-card"></i>{{ $roleLabel }}</span>
          <span class="profile-chip"><i class="bx bx-check-circle"></i>{{ $statusLabel }}</span>
          @if($employeeNo)
            <span class="profile-chip"><i class="bx bx-barcode"></i>Employee No. {{ $employeeNo }}</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="profile-card profile-info-card p-4">
        <div class="profile-card-header">
          <span class="profile-card-icon"><i class="bx bx-user"></i></span>
          <h5 class="profile-card-title">Account Information</h5>
        </div>
        <div class="profile-info-list">
          <div class="profile-info-row">
            <span>Email</span>
            <strong>{{ $user->email ?: 'No email listed' }}</strong>
          </div>
          <div class="profile-info-row">
            <span>Department</span>
            <strong>{{ !empty($departments) ? implode(', ', $departments) : 'No department listed' }}</strong>
          </div>
          <div class="profile-info-row">
            <span>Job Title</span>
            <strong>{{ $jobTitle ?: 'No job title listed' }}</strong>
          </div>
          <div class="profile-info-row">
            <span>Provider</span>
            <strong>{{ $user->provider ? ucwords($user->provider) : 'Local Account' }}</strong>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="profile-card p-4 mb-4">
        <div class="profile-card-header">
          <span class="profile-card-icon"><i class="bx bx-bar-chart-alt-2"></i></span>
          <h5 class="profile-card-title">Profile Summary</h5>
        </div>
        <div class="row g-3">
          <div class="col-sm-6 col-xl-3">
            <div class="profile-stat">
              <small>Assignments</small>
              <strong>{{ $stats['assignments'] }}</strong>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="profile-stat">
              <small>Active Evaluations</small>
              <strong>{{ $stats['active_evaluations'] }}</strong>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="profile-stat">
              <small>Responses</small>
              <strong>{{ $stats['responses'] }}</strong>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="profile-stat">
              <small>Avg. Rating</small>
              <strong>{{ $stats['average_rating'] }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="profile-card p-4">
        <div class="profile-card-header">
          <span class="profile-card-icon"><i class="bx bx-shield-quarter"></i></span>
          <h5 class="profile-card-title">Access Level</h5>
        </div>
        <div class="profile-access-list">
          @forelse($accessLevels as $level)
            <span class="profile-access-badge">{{ $level }}</span>
          @empty
            <span class="profile-access-badge">No access level assigned</span>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
