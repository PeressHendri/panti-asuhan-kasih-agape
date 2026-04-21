<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | Panti Kasih Agape</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f1f5f9;
            --primary-color: #3b82f6;
            --bg-color: #f8fafc;
            --text-color: #0f172a;
            --sidebar-text: #475569;
            --sidebar-border: #e2e8f0;
            --header-height: 70px;
        }

        body {
            color: var(--text-color);
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-color);
        }

        /* SIDEBAR STYLES */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            overflow-x: hidden;
            overflow-y: auto;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            border-right: 1px solid var(--sidebar-border);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--sidebar-border);
            height: var(--header-height);
            overflow: hidden;
        }

        .sidebar .brand-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .sidebar .nav-item {
            padding: 0.25rem 1rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            color: var(--sidebar-text);
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* MAIN CONTENT STYLES */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .header {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .content-body {
            padding: 2rem;
        }

        /* CARD STYLES */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
        }

        /* UTILS */
        .text-primary {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 0.5rem;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
        }

        /* Overlay backdrop untuk mobile */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-show {
                transform: translateX(0);
                width: var(--sidebar-width) !important;
                box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('favicon.ico') }}" alt="Logo Agape" class="brand-icon">
            <h5
                style="white-space: normal; line-height: 1.3; font-size: 1.1rem; flex: 1; color: var(--text-color); font-weight: 800;">
                PANTI KASIH<br>AGAPE</h5>
        </div>
        <nav class="nav flex-column">
            @php 
                $currentRoute = Route::currentRouteName();
                $userRole = Auth::user()->role ?? 'guest';
            @endphp
            
            {{-- ADMIN NAVIGATION --}}
            @if($userRole === 'admin')
            <div class="nav-item mt-3">
                <a class="nav-link {{ $currentRoute == 'admin.dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'profile.panti') || $currentRoute == 'children.index' ? 'active' : '' }}" href="{{ route('admin.profile.panti') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Data Anak</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'manage.users') || str_contains($currentRoute ?? '', 'users.') ? 'active' : '' }}" href="{{ route('admin.manage.users') }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Manajemen Pengguna</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'attendance') ? 'active' : '' }}" href="{{ route('admin.attendance') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Kehadiran</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'dashboard.cctv' ? 'active' : '' }}" href="{{ route('dashboard.cctv') }}">
                    <i class="fas fa-video"></i>
                    <span>Monitoring CCTV</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'gallery') ? 'active' : '' }}" href="{{ route('admin.gallery.index') }}">
                    <i class="fas fa-images"></i>
                    <span>Galeri</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'admin.donasi' ? 'active' : '' }}" href="{{ route('admin.donasi') }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Donasi</span>
                </a>
            </div>
            
            {{-- PENGASUH NAVIGATION --}}
            @elseif($userRole === 'pengasuh')
            <div class="nav-item mt-3">
                <a class="nav-link {{ $currentRoute == 'pengasuh.dashboard' ? 'active' : '' }}" href="{{ route('pengasuh.dashboard') }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'pengasuh.profile.panti') ? 'active' : '' }}" href="{{ route('pengasuh.profile.panti') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Data Anak</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'pengasuh.attendance') ? 'active' : '' }}" href="{{ route('pengasuh.attendance') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Kehadiran</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'dashboard.cctv' ? 'active' : '' }}" href="{{ route('dashboard.cctv') }}">
                    <i class="fas fa-video"></i>
                    <span>Monitoring CCTV</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ str_contains($currentRoute ?? '', 'gallery') ? 'active' : '' }}" href="{{ route('pengasuh.gallery.index') }}">
                    <i class="fas fa-images"></i>
                    <span>Galeri</span>
                </a>
            </div>
            
            {{-- SPONSOR NAVIGATION --}}
            @elseif($userRole === 'sponsor')
            <div class="nav-item mt-3">
                <a class="nav-link {{ $currentRoute == 'sponsor.dashboard' ? 'active' : '' }}" href="{{ route('sponsor.dashboard') }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'sponsor.profile.panti' ? 'active' : '' }}" href="{{ route('sponsor.profile.panti') }}">
                    <i class="fas fa-user-graduate"></i>
                    <span>Data Anak</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'sponsor.pengasuh' ? 'active' : '' }}" href="{{ route('sponsor.pengasuh') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Data Pengasuh</span>
                </a>
            </div>
            <div class="nav-item">
                <a class="nav-link {{ $currentRoute == 'sponsor.attendance' ? 'active' : '' }}" href="{{ route('sponsor.attendance') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Kehadiran</span>
                </a>
            </div>
            @endif
            
            {{-- COMMON (all roles) --}}
            <div class="nav-item border-top mt-3 pt-3">
                <a class="nav-link {{ $currentRoute == 'profile.edit' ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
            <div class="nav-item">
                <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <div class="main-content" id="main-content">
        <header class="header">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark p-0 me-3" id="sidebarToggle">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <h4 class="mb-0 fw-bold">@yield('header-title', 'Dashboard')</h4>
            </div>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" href="#"
                        role="button" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://ui-avatars.com/api/?name=' . Auth::user()->name }}"
                            alt="Admin" class="rounded-circle me-2" width="35" height="35" style="object-fit: cover;">
                        <div class="d-none d-md-block">
                            <span class="fw-semibold d-block" style="font-size: 0.9rem;">{{ Auth::user()->name }}</span>
                            <small class="text-primary text-capitalize" style="font-size: 0.75rem;">{{ Auth::user()->role }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            if (window.innerWidth <= 991.98) {
                // Mobile behavior: show full width sidebar
                sidebar.classList.toggle('mobile-show');
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                backdrop.classList.toggle('show');
            } else {
                // Desktop behavior: collapse sidebar
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                sidebar.classList.remove('mobile-show');
                backdrop.classList.remove('show');
            }
        }

        document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);

        // Close sidebar on mobile when backdrop clicked
        backdrop.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.remove('mobile-show');
                backdrop.classList.remove('show');
            }
        });

        // Close sidebar on mobile when window resized
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('mobile-show');
                backdrop.classList.remove('show');
            }
        });
    </script>
    @stack('scripts')
</body>

</html>