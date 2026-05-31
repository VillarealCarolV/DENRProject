@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header with Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0 fw-bold">
                    <i class="fas fa-users me-2 text-primary"></i>User Management
                </h3>
                <small class="text-muted">Manage system users and their roles</small>
            </div>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New User
                </a>
            @endif
        </div>

        <!-- Status Messages -->
        @if($message = session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($message = session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr data-user-id="{{ $user->id }}">
                                <td class="ps-4">
                                    <strong>{{ $user->name }}</strong>
                                    @if(auth()->user()->id === $user->id)
                                        <span class="badge bg-info ms-2">You</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>
                                    @php
                                        $roleBadgeColor = match($user->role) {
                                            'admin' => 'bg-danger',
                                            'records_officer' => 'bg-warning text-dark',
                                            'land_officer' => 'bg-info',
                                            'user' => 'bg-secondary',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadgeColor }}">
                                        {{ $roles[$user->role] ?? ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle-check me-1"></i>Active
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('users.show', $user->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="View User">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->role === 'admin' || auth()->user()->id === $user->id)
                                            <a href="{{ route('users.edit', $user->id) }}" 
                                               class="btn btn-outline-warning" 
                                               title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->role === 'admin' && auth()->user()->id !== $user->id)
                                            <button class="btn btn-outline-danger delete-btn-handler" 
                                                    data-url="{{ route('users.destroy', $user->id) }}"
                                                    data-name="User {{ $user->name }}"
                                                    data-row-selector="tr[data-user-id='{{ $user->id }}']"
                                                    title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                                    <p>No users found. <a href="{{ route('users.create') }}">Create one now</a></p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .btn-group-sm .btn {
        padding: 0.35rem 0.5rem;
        font-size: 0.85rem;
    }
</style>
@endsection
