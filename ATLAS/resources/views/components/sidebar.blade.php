<!-- Sidebar Navigation -->
<aside class="sidebar bg-dark d-flex flex-column">
    <!-- Logo / Brand -->
    <div class="sidebar-header px-3 py-4 border-bottom border-secondary">
        @if(auth()->user()->role === 'records_officer')
            <a href="{{ route('applications.index') }}" class="text-decoration-none">
                <span class="text-white fw-bold fs-6">ATLAS</span>
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <span class="text-white fw-bold fs-6">ATLAS</span>
            </a>
        @endif
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav flex-grow-1 py-3">
        <ul class="nav flex-column">
            <!-- Dashboard (Hidden for Records Officers) -->
            @if(auth()->user()->role !== 'records_officer')
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" 
                       class="nav-link sidebar-link {{ $active === 'dashboard' ? 'active' : '' }}"
                       title="Dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            @endif

            <!-- Applications -->
            <li class="nav-item">
                <a href="{{ route('applications.index') }}" 
                   class="nav-link sidebar-link {{ str_contains($active, 'applications') ? 'active' : '' }}"
                   title="Applications">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-text">Applications</span>
                </a>
            </li>

            <!-- Processing Queue (Land Officers Only) -->
            @if(auth()->user()->role === 'land_officer')
                <li class="nav-item">
                    <a href="{{ route('processing-queue') }}" 
                       class="nav-link sidebar-link {{ str_contains($active, 'processing-queue') ? 'active' : '' }}"
                       title="Processing Queue">
                        <i class="bi bi-list-check"></i>
                        <span class="nav-text">Queue</span>
                    </a>
                </li>
            @endif

            <!-- Land Records -->
            <li class="nav-item">
                <a href="{{ route('land-records.index') }}" 
                   class="nav-link sidebar-link {{ str_contains($active, 'land-records') ? 'active' : '' }}"
                   title="Land Records">
                    <i class="bi bi-map"></i>
                    <span class="nav-text">Land Records</span>
                </a>
            </li>

            <!-- Applicants -->
            <li class="nav-item">
                <a href="{{ route('applicants.index') }}" 
                   class="nav-link sidebar-link {{ str_contains($active, 'applicants') ? 'active' : '' }}"
                   title="Applicants">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Applicants</span>
                </a>
            </li>

            <!-- User Management (Admin Only) -->
            @if(auth()->check() && auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" 
                       class="nav-link sidebar-link {{ str_contains($active, 'users') ? 'active' : '' }}"
                       title="User Management">
                        <i class="bi bi-shield-lock"></i>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
            @endif

            <!-- Reports (Not for Records Officers) -->
            @if(auth()->user()->role !== 'records_officer')
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" 
                       class="nav-link sidebar-link {{ str_contains($active, 'reports') ? 'active' : '' }}"
                       title="Reports">
                        <i class="bi bi-bar-chart"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Sidebar Footer (User Menu) -->
    <div class="sidebar-footer border-top border-secondary">
        <button class="btn btn-sm btn-outline-secondary w-100 text-white d-flex flex-column align-items-center gap-2" onclick="document.getElementById('logout-form').submit()" style="padding: 12px!important;">
            <i class="bi bi-box-arrow-right" style="font-size: 1.3rem;"></i>
            <span class="nav-text" style="font-size: 0.8rem;">Logout</span>
        </button>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>
