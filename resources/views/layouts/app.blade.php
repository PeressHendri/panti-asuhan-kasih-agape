<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panti Asuhan Kasih Agape')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

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

        /* Collapsed logic for desktop */
        @media (min-width: 992px) {
            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar.collapsed:hover {
                width: var(--sidebar-width);
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            }

            .main-content.expanded {
                margin-left: var(--sidebar-collapsed-width);
            }

            .sidebar.collapsed:hover~#main-content.expanded {
                margin-left: var(--sidebar-width);
            }
        }

        .sidebar .brand {
            height: var(--header-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--sidebar-border);
            white-space: nowrap;
            overflow: hidden;
            transition: padding 0.3s, justify-content 0.3s;
        }

        .sidebar .brand-icon {
            min-width: 40px;
            width: 40px;
            height: 40px;
            object-fit: contain;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: margin 0.3s;
        }

        .sidebar .brand h5 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: opacity 0.3s, max-width 0.3s, transform 0.3s, margin 0.3s;
            overflow: hidden;
            max-width: 200px;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 12px 15px;
            display: flex;
            align-items: center;
            white-space: nowrap;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 4px 15px;
            font-weight: 500;
            overflow: hidden;
        }

        .sidebar .nav-link i {
            min-width: 30px;
            text-align: center;
            font-size: 1.2rem;
            margin-right: 15px;
            display: flex;
            justify-content: center;
            transition: margin 0.3s;
        }

        .sidebar .nav-link span {
            transition: opacity 0.3s, max-width 0.3s, transform 0.3s, margin 0.3s;
            overflow: hidden;
            max-width: 200px;
        }

        @media (min-width: 992px) {
            .sidebar.collapsed:not(:hover) .brand {
                padding: 20px 0;
                justify-content: center;
            }

            .sidebar.collapsed:not(:hover) .brand h5 {
                opacity: 0;
                max-width: 0;
                margin: 0;
                transform: translateX(-10px);
            }

            .sidebar.collapsed:not(:hover) .nav-link {
                margin: 4px 15px;
                padding: 12px 0;
                justify-content: center;
            }

            .sidebar.collapsed:not(:hover) .nav-link i {
                margin-right: 0;
                width: auto;
            }

            .sidebar.collapsed:not(:hover) .nav-link span {
                opacity: 0;
                max-width: 0;
                margin: 0;
                pointer-events: none;
                transform: translateX(-10px);
            }
        }

        .sidebar .nav-link:hover {
            color: var(--text-color);
            background-color: var(--sidebar-hover);
            transform: translateX(3px);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: #ffffff;
            height: var(--header-height);
            padding: 0 30px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .hamburger {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
            margin-right: 20px;
        }

        .hamburger:hover {
            background: #f1f5f9;
            color: var(--sidebar-bg);
        }

        .profile-img {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .dropdown-toggle:hover .profile-img {
            border-color: var(--primary-color);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 10px 20px;
            font-size: 0.9rem;
            color: #475569;
        }

        .dropdown-item:hover {
            background-color: #f8fafc;
            color: #0f172a;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1099;
            opacity: 0;
            transition: opacity 0.3s;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar-overlay.active {
                display: block;
                opacity: 1;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('favicon.ico') }}" alt="Logo Agape" class="brand-icon">
            <h5
                style="white-space: normal; line-height: 1.3; font-size: 1.1rem; flex: 1; color: var(--text-color); font-weight: 800;">
                PANTI KASIH<br>AGAPE</h5>
        </div>
        <nav class="nav flex-column">
            @php 
                $role = auth()->user()->role ?? 'guest'; 
                $todayUnknown = 0;
                if (auth()->check()) {
                    try {
                        $todayUnknown = \App\Models\FaceRecognitionLog::whereDate('waktu_deteksi', today())
                                                                      ->where('status', 'tidak_dikenal')
                                                                      ->count();
                    } catch (\Exception $e) {}
                }
            @endphp

            @if($role === 'admin')
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('admin.profile.panti') ? 'active' : '' }}"
                    href="{{ route('admin.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>
                <a class="nav-link {{ request()->routeIs('admin.manage.users') ? 'active' : '' }}"
                    href="{{ route('admin.manage.users') }}"><i class="fas fa-users"></i><span>Manajemen Pengguna</span></a>

                <a class="nav-link {{ request()->routeIs('admin.attendance') ? 'active' : '' }}"
                    href="{{ route('admin.attendance') }}"><i class="fas fa-clipboard-check"></i><span>Kehadiran</span></a>
                <a class="nav-link {{ request()->routeIs('dashboard.face-log') ? 'active' : '' }}"
                    href="{{ route('dashboard.face-log') }}">
                    <i class="fas fa-eye"></i><span>Face Recognition</span>
                    @if($todayUnknown > 0)
                        <span class="badge bg-danger ms-2 pulse" style="font-size:0.75rem; border-radius:50%;">{{ $todayUnknown }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('dashboard.cctv') ? 'active' : '' }}"
                    href="{{ route('dashboard.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
                <a class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}"
                    href="{{ route('admin.gallery.index') }}"><i class="fas fa-images"></i><span>Galeri</span></a>
                <a class="nav-link {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"
                    href="{{ route('admin.profile.edit') }}"><i class="fas fa-user-cog"></i><span>Profil Saya</span></a>
            @elseif($role === 'pengasuh')
                <a class="nav-link {{ request()->routeIs('pengasuh.dashboard') ? 'active' : '' }}"
                    href="{{ route('pengasuh.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('pengasuh.profile.panti') ? 'active' : '' }}"
                    href="{{ route('pengasuh.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>

                <a class="nav-link {{ request()->routeIs('pengasuh.attendance') ? 'active' : '' }}"
                    href="{{ route('pengasuh.attendance') }}"><i
                        class="fas fa-clipboard-check"></i><span>Kehadiran</span></a>
                <a class="nav-link {{ request()->routeIs('dashboard.face-log') ? 'active' : '' }}"
                    href="{{ route('dashboard.face-log') }}">
                    <i class="fas fa-eye"></i><span>Face Recognition</span>
                    @if($todayUnknown > 0)
                        <span class="badge bg-danger ms-2 pulse" style="font-size:0.75rem; border-radius:50%;">{{ $todayUnknown }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('dashboard.cctv') ? 'active' : '' }}"
                    href="{{ route('dashboard.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
                <a class="nav-link {{ request()->routeIs('pengasuh.gallery.*') ? 'active' : '' }}"
                    href="{{ route('pengasuh.gallery.index') }}"><i class="fas fa-images"></i><span>Galeri</span></a>
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                    href="{{ route('profile.edit') }}"><i class="fas fa-user"></i><span>Profil Saya</span></a>
            @elseif($role === 'sponsor')
                <a class="nav-link {{ request()->routeIs('sponsor.dashboard') ? 'active' : '' }}"
                    href="{{ route('sponsor.dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('sponsor.profile.panti') ? 'active' : '' }}"
                    href="{{ route('sponsor.profile.panti') }}"><i class="fas fa-child"></i><span>Data Anak</span></a>
                <a class="nav-link {{ request()->routeIs('sponsor.pengasuh') ? 'active' : '' }}"
                    href="{{ route('sponsor.pengasuh') }}"><i class="fas fa-user-nurse"></i><span>Data Pengasuh</span></a>
                <a class="nav-link {{ request()->routeIs('sponsor.attendance') ? 'active' : '' }}"
                    href="{{ route('sponsor.attendance') }}"><i class="fas fa-calendar-check"></i><span>Kehadiran
                        Anak</span></a>
                <a class="nav-link {{ request()->routeIs('dashboard.face-log') ? 'active' : '' }}"
                    href="{{ route('dashboard.face-log') }}">
                    <i class="fas fa-eye"></i><span>Face Recognition</span>
                    @if($todayUnknown > 0)
                        <span class="badge bg-danger ms-2 pulse" style="font-size:0.75rem; border-radius:50%;">{{ $todayUnknown }}</span>
                    @endif
                </a>
                <a class="nav-link {{ request()->routeIs('dashboard.cctv') ? 'active' : '' }}"
                    href="{{ route('dashboard.cctv') }}"><i class="fas fa-video"></i><span>Monitoring CCTV</span></a>
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

    <div class="main-content" id="main-content">
        <header class="header">
            <div class="d-flex align-items-center w-100 justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="hamburger" id="sidebarToggle" type="button" aria-label="Toggle Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color: #0f172a; font-size: 1.25rem;">
                            @yield('header-title', 'Dashboard')</h4>
                    </div>
                </div>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                        id="navbarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(Auth::user() && Auth::user()->photo)
                            <img src="{{ asset('storage/' . Auth::user()->photo) }}" class="profile-img me-2"
                                alt="Foto Profil">
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
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    const isMobile = window.innerWidth < 992;
                    if (isMobile) {
                        // Di mode HP, buka panel pull-over
                        sidebar.classList.add('mobile-active');
                        sidebarOverlay.classList.add('active');
                    } else {
                        // Di mode Desktop, toggle expand/collapse
                        sidebar.classList.toggle('collapsed');
                        mainContent.classList.toggle('expanded');
                    }
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.remove('mobile-active');
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