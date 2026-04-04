@extends('layouts.auth')

@section('content')
<div class="login-card">
    <div class="login-header">
        <h2><i class="fas fa-map-marked-alt me-2 text-primary"></i> ATLAS</h2>
        <p>Land Allocation Management System</p>
    </div>
    
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong><i class="fas fa-exclamation-circle me-2"></i>Login Failed!</strong>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label fw-bold">Username</label>
                <input 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}"
                    placeholder="Enter your username"
                    required
                    autofocus>
                @error('username')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password</label>
                <input 
                    type="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2 fw-bold">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>

        <hr>
        <p class="text-muted text-center small mb-0">
            <strong><i class="fas fa-info-circle me-1"></i> Demo Credentials:</strong><br>
            <span class="text-monospace">Username:</span> <code>admin</code><br>
            <span class="text-monospace">Password:</span> <code>admin</code>
        </p>
    </div>
</div>
@endsection
