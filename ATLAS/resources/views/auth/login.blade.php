@extends('layouts.guest')

@section('content')
<div class="login-card">
    <!-- Session Status -->
    @if ($errors->any())
        <div class="session-status">
            ⚠️ Login failed. Please check your credentials and try again.
        </div>
    @endif

    <!-- Profile Circle with Logo -->
    <div class="profile-circle">
        <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="DENR Logo">
    </div>

    <!-- Title -->
    <h1 class="login-title">
        Automated Tracking of Land Application System
    </h1>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="login-form">
        @csrf

        <!-- Email Address Input -->
        <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input 
                id="email" 
                class="form-input @error('email') is-invalid @enderror" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                placeholder="Username or Email"
                autocomplete="email"
            >
        </div>
        {{-- @error('email')
            <span class="error-message">{{ $message }}</span>
        @enderror --}}

        <!-- Password Input -->
        <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input 
                id="password" 
                class="form-input @error('password') is-invalid @enderror" 
                type="password" 
                name="password" 
                required 
                placeholder="Password"
                autocomplete="current-password"
            >
        </div>
        @error('password')
            <span class="error-message">{{ $message }}</span>
        @enderror

        <!-- Remember Me Checkbox -->
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
            <input 
                id="remember_me" 
                type="checkbox" 
                name="remember" 
                style="width: 18px; height: 18px; cursor: pointer; accent-color: white;"
            >
            <label for="remember_me" style="color: white; font-size: 13px; cursor: pointer; margin: 0;">
                Remember me
            </label>
        </div>

        <!-- Login Button -->
        <button type="submit" class="login-btn">
            LOGIN
        </button>
    </form>
</div>
@endsection
