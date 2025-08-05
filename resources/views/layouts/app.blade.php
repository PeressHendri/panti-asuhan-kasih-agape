<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panti Asuhan Kasih Agape')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        body {
            color: #333;
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            background-color: #f0f2f5;
            background-image:
                radial-gradient(circle at 80% 80%, #d4dbe3 0, transparent 10%),
                radial-gradient(circle at 20% 90%, #a7c5eb 0, transparent 10%),
                radial-gradient(circle at 90% 20%, #fdebd0 0, transparent 10%),
                radial-gradient(circle at 30% 30%, #e8daef 0, transparent 10%),
                radial-gradient(circle at 50% 50%, #d5f5e3 0, transparent 10%);
            background-size: 60px 60px;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #34495e 0%, #2c3e50 100%);
            color: #ecf0f1;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            z-index: 1100;
        }

        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .brand .brand-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: block;
            color: #3498db;
        }

        .sidebar .brand h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            margin: 4px 0;
        }

        .sidebar .nav-link i {
            width: 30px;
            margin-right: 15px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar .nav-link:hover {
            background-color: #34495e;
            border-left-color: #3498db;
        }

        .sidebar .nav-link.active {
            background-color: rgb(0, 64, 255);
            border-left-color: #3498db;
            font-weight: 500;
        }

        .main-content {
            margin-left: 250px;
            transition: margin-left 0.3s ease-in-out;
        }

        .header {
            background: #ffffff;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #3498db;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.8rem;
            color: #2c3e50;
            margin-right: 15px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1099;
        }

        .sidebar-overlay.active {
            display: block;
        }

        .dropdown-menu {
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .toast-container {
            z-index: 2000;
        }

    
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger {
                display: inline-flex;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="brand">
                <i class="fas fa-home brand-icon"></i>
                <h5>PANTI KASIH AGAPE</h5>
        </div>
        <nav class="nav flex-column">
            @php $role = auth()->user()->role ?? 'guest'; @endphp

            @if($role === 'admin')
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('admin.profile.panti') ? 'active' : '' }}"
                    href="{{ route('admin.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>
                <a class="nav-link {{ request()->routeIs('admin.manage.users') ? 'active' : '' }}"
                    href="{{ route('admin.manage.users') }}"><i class="fas fa-users"></i><span>Manajemen Pengguna</span></a>
                <a class="nav-link {{ request()->routeIs('admin.cctv') ? 'active' : '' }}"
                    href="{{ route('admin.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
                <a class="nav-link {{ request()->routeIs('admin.attendance') ? 'active' : '' }}"
                    href="{{ route('admin.attendance') }}"><i class="fas fa-clipboard-check"></i><span>Kehadiran</span></a>
                <a class="nav-link {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"
                    href="{{ route('admin.profile.edit') }}"><i class="fas fa-user-cog"></i><span>Profil Saya</span></a>
            @elseif($role === 'pengasuh')
                <a class="nav-link {{ request()->routeIs('pengasuh.dashboard') ? 'active' : '' }}"
                    href="{{ route('pengasuh.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('pengasuh.profile.panti') ? 'active' : '' }}"
                    href="{{ route('pengasuh.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>
                <a class="nav-link {{ request()->routeIs('pengasuh.cctv') ? 'active' : '' }}"
                    href="{{ route('pengasuh.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
                <a class="nav-link {{ request()->routeIs('pengasuh.attendance') ? 'active' : '' }}"
                    href="{{ route('pengasuh.attendance') }}"><i
                        class="fas fa-clipboard-check"></i><span>Kehadiran</span></a>
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                    href="{{ route('profile.edit') }}"><i class="fas fa-user"></i><span>Profil Saya</span></a>
            @elseif($role === 'donatur')
                <a class="nav-link {{ request()->routeIs('donatur.dashboard') ? 'active' : '' }}"
                    href="{{ route('donatur.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('donatur.profile.panti') ? 'active' : '' }}"
                    href="{{ route('donatur.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>
                <a class="nav-link {{ request()->routeIs('donatur.pengasuh') ? 'active' : '' }}"
                    href="{{ route('donatur.pengasuh') }}"><i class="fas fa-user-nurse"></i><span>Data Pengasuh</span></a>
                <a class="nav-link {{ request()->routeIs('donatur.attendance') ? 'active' : '' }}"
                    href="{{ route('donatur.attendance') }}"><i class="fas fa-calendar-check"></i><span>Kehadiran
                        Anak</span></a>
                <a class="nav-link {{ request()->routeIs('donatur.cctv') ? 'active' : '' }}"
                    href="{{ route('donatur.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                    href="{{ route('profile.edit') }}"><i class="fas fa-user"></i><span>Profil Saya</span></a>
            @endif

            <a class="nav-link" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="main-content">
        <header class="header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="hamburger" id="sidebarToggle" type="button" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h4 class="mb-0 fw-bold">@yield('header-title', 'Dashboard')</h4>
                    <p class="mb-0 text-muted small">
                        Selamat datang, {{ Auth::check() ? Auth::user()->name : 'Tamu' }}!
                    </p>
                </div>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                    id="navbarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(Auth::user() && Auth::user()->photo)
                        <img src="{{ asset('storage/' . Auth::user()->photo) }}" class="profile-img me-2" alt="Foto Profil">
                    @endif
                    <span class="fw-semibold d-none d-md-inline">{{ Auth::user()->name ?? '' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                    @if (Auth::user()->role == 'admin')
                        <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                            <i class="fas fa-user-cog me-2"></i>Profil Saya
                        </a>
                    @else
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-cog me-2"></i>Profil Saya
                        </a>

                    @endif
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="container-fluid p-4">
            @yield('content')
        </main>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        @if(session('success'))
            <div class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="fas fa-times-circle me-2"></i>{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.add('active');
                    sidebarOverlay.classList.add('active');
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                });
            }

            const toastElList = document.querySelectorAll('.toast');
            const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl, { delay: 5000 }));
            toastList.forEach(toast => toast.show());
        });
    </script>

    @stack('scripts')
</body>

</html>