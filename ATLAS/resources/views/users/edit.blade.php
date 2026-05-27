@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom py-3">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-user-edit me-2 text-primary"></i>Edit User
                </h4>
                <small class="text-muted">Update user information and role</small>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-user me-2 text-secondary"></i>Full Name
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               placeholder="Enter user's full name"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope me-2 text-secondary"></i>Email Address
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               placeholder="Enter email address"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Role Selection (Only for Admins) -->
                    @if(auth()->user()->role === 'admin')
                        <div class="mb-3">
                            <label for="role" class="form-label fw-bold">
                                <i class="fas fa-shield-alt me-2 text-secondary"></i>User Role
                            </label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                @foreach($roles as $roleKey => $roleName)
                                    <option value="{{ $roleKey }}" {{ old('role', $user->role) === $roleKey ? 'selected' : '' }}>
                                        {{ $roleName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted d-block mt-1">
                                <strong>Administrator:</strong> Full system access<br>
                                <strong>Records Officer:</strong> Can create and manage applications<br>
                                <strong>Land Officer:</strong> Can assess applications for land divisions<br>
                                <strong>Regular User:</strong> View-only access
                            </small>
                        </div>
                    @else
                        <!-- Display role as read-only for non-admin users -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-shield-alt me-2 text-secondary"></i>Current Role
                            </label>
                            <div class="p-2 bg-light rounded border">
                                <span class="badge bg-info">{{ $roles[$user->role] ?? ucfirst($user->role) }}</span>
                            </div>
                            <small class="form-text text-muted d-block mt-1">
                                Only administrators can change user roles.
                            </small>
                        </div>
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif

                    <hr class="my-4">

                    <!-- Password Change Section -->
                    <div class="mb-3">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-key me-2 text-secondary"></i>Change Password (Optional)
                        </h5>
                        <p class="text-muted small">Leave blank to keep the current password</p>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-lock me-2 text-secondary"></i>New Password
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password (or leave blank)">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted d-block mt-1">
                                Password must be at least 8 characters long.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="fas fa-lock me-2 text-secondary"></i>Confirm New Password
                            </label>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Confirm new password">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        color: #212529;
        margin-bottom: 0.5rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    hr {
        border-top-color: #e9ecef;
    }
</style>
@endsection
