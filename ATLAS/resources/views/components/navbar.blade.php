<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid px-4">
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-sm btn-outline-secondary me-3 d-lg-none" id="sidebar-toggle" type="button">
            <i class="bi bi-list"></i>
        </button>

        <!-- Search Bar (Tracking Number) -->
        <div class="flex-grow-1 me-3">
            <form class="d-flex" action="{{ route('search') }}" method="GET">
                <input 
                    class="form-control form-control-sm" 
                    type="search" 
                    name="tracking_no" 
                    placeholder="Search by Tracking No..."
                    aria-label="Search">
                <button class="btn btn-sm btn-outline-secondary ms-2" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <!-- User Info & Dropdown -->
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span class="ms-2">
                        @if($user)
                            {{ $user->role }} - {{ $user->name }}
                        @else
                            Guest
                        @endif
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="document.getElementById('logout-form2').submit(); return false;">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
            <form id="logout-form2" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</nav>
