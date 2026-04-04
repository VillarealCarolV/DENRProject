<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ATLAS') }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Sidebar -->
    <x-sidebar :active="request()->route()->getName()" />

    <!-- Main Content Area -->
    <div class="d-flex flex-column" style="min-height: 100vh;">
        <!-- Top Navbar -->
        <x-navbar :user="auth()->user()" />

        <!-- Page Content -->
        <main class="flex-grow-1 bg-light">
            <!-- Breadcrumbs -->
            <x-breadcrumbs :breadcrumbs="$breadcrumbs ?? []" />

            <!-- Sub Navigation (if needed) -->
            @if(isset($tabs))
                <x-sub-nav :tabs="$tabs" :active="$activeTab ?? ''" />
            @endif

            <!-- Page Content -->
            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
