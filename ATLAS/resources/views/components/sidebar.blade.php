<!-- Sidebar Navigation -->
<aside class="sidebar bg-dark d-flex flex-column">
    <!-- Logo / Brand -->
    <div class="sidebar-header px-3 py-4 border-bottom border-secondary">
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <span class="text-white fw-bold fs-6">ATLAS</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav flex-grow-1 py-3">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" 
                   class="nav-link sidebar-link {{ $active === 'dashboard' ? 'active' : '' }}"
                   title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Applications -->
            <li class="nav-item">
                <a href="{{ route('applications.index') }}" 
                   class="nav-link sidebar-link {{ str_contains($active, 'applications') ? 'active' : '' }}"
                   title="Applications">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-text">Applications</span>
                </a>
            </li>

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

            <!-- Reports -->
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" 
                   class="nav-link sidebar-link {{ str_contains($active, 'reports') ? 'active' : '' }}"
                   title="Reports">
                    <i class="bi bi-bar-chart"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Footer (User Menu) -->
    <div class="sidebar-footer border-top border-secondary px-3 py-3">
        <button class="btn btn-sm btn-outline-secondary w-100 text-white" onclick="document.getElementById('logout-form').submit()">
            <i class="bi bi-box-arrow-right"></i>
            <span class="nav-text">Logout</span>
        </button>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>
