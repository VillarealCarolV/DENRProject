@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-user me-2 text-primary"></i>User Profile
                    </h4>
                    <small class="text-muted">View user information</small>
                </div>
                @if(auth()->user()->role === 'admin' || auth()->user()->id === $user->id)
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                @endif
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">
                <!-- User Information -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-user me-2 text-secondary"></i>Full Name
                        </label>
                        <p class="h5 mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
                        </label>
                        <p class="h5 mb-0">
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Role Information -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-shield-alt me-2 text-secondary"></i>User Role
                        </label>
                        @php
                            $roleBadgeColor = match($user->role) {
                                'admin' => 'bg-danger',
                                'records_officer' => 'bg-warning text-dark',
                                'land_officer' => 'bg-info',
                                'user' => 'bg-secondary',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <p class="mb-0">
                            <span class="badge {{ $roleBadgeColor }} fs-6">
                                {{ $roles[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-check-circle me-2 text-secondary"></i>Account Status
                        </label>
                        <p class="mb-0">
                            <span class="badge bg-success">Active</span>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Account Dates -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-calendar me-2 text-secondary"></i>Member Since
                        </label>
                        <p class="h6 mb-0">
                            {{ $user->created_at->format('F d, Y') }}
                            <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-muted">
                            <i class="fas fa-sync-alt me-2 text-secondary"></i>Last Updated
                        </label>
                        <p class="h6 mb-0">
                            {{ $user->updated_at->format('F d, Y') }}
                            <small class="text-muted d-block">{{ $user->updated_at->diffForHumans() }}</small>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Role Description -->
                <div class="alert alert-light border-0 bg-light-blue" role="alert">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-info-circle me-2 text-info"></i>Role Description
                    </h6>
                    @switch($user->role)
                        @case('admin')
                            <p class="mb-0">
                                Administrators have full access to all system features including user management, 
                                application processing, and system settings.
                            </p>
                            @break
                        @case('records_officer')
                            <p class="mb-0">
                                Records Officers can create new applications, manage applicant records, and view 
                                land officer assessments. They process initial application intake.
                            </p>
                            @break
                        @case('land_officer')
                            <p class="mb-0">
                                Land Officers can assess applications for land divisions, determine lot types, 
                                calculate subdivision areas, and provide assessment remarks.
                            </p>
                            @break
                        @case('user')
                            <p class="mb-0">
                                Regular users have view-only access to the system. They can view their own 
                                applications and notifications but cannot make changes.
                            </p>
                            @break
                        @default
                            <p class="mb-0">No description available for this role.</p>
                    @endswitch
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4">
                    @if(auth()->user()->role === 'admin' || auth()->user()->id === $user->id)
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                    @endif
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-blue {
        background-color: #d1ecf1 !important;
    }

    .form-label {
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    hr {
        border-top-color: #e9ecef;
    }
</style>
@endsection
