<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Care Plus Clinic</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #10B981;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #10B981;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        .slider.round {
            border-radius: 24px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-heart-pulse" style="color: var(--secondary);"></i> Care Plus
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="{{ route('admin.doctors') }}"
                class="nav-item {{ request()->routeIs('admin.doctors') ? 'active' : '' }}">
                <i class="fa-solid fa-user-doctor"></i> Doctors
            </a>
            <a href="{{ route('admin.receptionists') }}"
                class="nav-item {{ request()->routeIs('admin.receptionists') ? 'active' : '' }}">
                <i class="fa-solid fa-user-tie"></i> Receptionists
            </a>
            <a href="{{ route('admin.patients') }}"
                class="nav-item {{ request()->routeIs('admin.patients') ? 'active' : '' }}">
                <i class="fa-solid fa-hospital-user"></i> Patients
            </a>
            <a href="{{ route('admin.services') }}"
                class="nav-item {{ request()->routeIs('admin.services') ? 'active' : '' }}">
                <i class="fa-solid fa-notes-medical"></i> Clinic Services
            </a>
            <a href="{{ route('admin.schedules') }}"
                class="nav-item {{ request()->routeIs('admin.schedules') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Doctor Schedule
            </a>
            <a href="{{ route('admin.appointments') }}"
                class="nav-item {{ request()->routeIs('admin.appointments') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i> Appointments
            </a>
            <a href="{{ route('admin.reports') }}"
                class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Reports
            </a>
            <a href="{{ route('admin.settings') }}"
                class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i> Settings
            </a>
            <a href="#" class="nav-item" id="admin-logout" style="margin-top: auto; color: var(--danger);">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <!-- Top Navigation -->
        <header class="top-header">
            <div style="flex: 1;"></div>
            <div class="header-actions">
                <i class="fa-regular fa-bell"></i>
                <div class="profile-dropdown">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=2563EB&color=fff" alt="Admin">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 600; font-size: 0.875rem;">Admin User</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Administrator</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; margin-left: 0.25rem;"></i>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <!-- Admin JS -->
    <script src="/js/admin.js"></script>
    @stack('scripts')
</body>

</html>