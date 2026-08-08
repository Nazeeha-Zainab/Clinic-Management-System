<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Care Plus Clinic</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Doctor CSS -->
    <link rel="stylesheet" href="/css/doctor.css">
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-heart-pulse" style="color: var(--secondary);"></i> Care Plus
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('doctor.dashboard') }}"
                class="nav-item {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>

            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Clinical</div>
            <a href="{{ route('doctor.appointments') }}"
                class="nav-item {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i> Today's Appointments
            </a>


            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Patient Management</div>
            <a href="{{ route('doctor.patients') }}"
                class="nav-item {{ request()->routeIs('doctor.patients') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open"></i> Patient Records
            </a>

            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Personal</div>
            <a href="{{ route('doctor.schedule') }}"
                class="nav-item {{ request()->routeIs('doctor.schedule') ? 'active' : '' }}">
                <i class="fa-regular fa-calendar-days"></i> My Schedule
            </a>
            <a href="{{ route('doctor.notifications') }}"
                class="nav-item {{ request()->routeIs('doctor.notifications') ? 'active' : '' }}">
                <i class="fa-regular fa-bell"></i> Notifications
            </a>

            <div style="border-top: 1px solid var(--border); margin: 1rem 0;"></div>
            <a href="{{ route('doctor.profile') }}"
                class="nav-item {{ request()->routeIs('doctor.profile') ? 'active' : '' }}">
                <i class="fa-solid fa-user-doctor"></i> Profile
            </a>
            <a href="#" class="nav-item" id="doctor-logout" style="color: var(--danger);">
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
                    <img src="https://ui-avatars.com/api/?name=Doctor&background=2563EB&color=fff" alt="Doctor Profile">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 600; font-size: 0.875rem;" id="doctor-name-header">Smith</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Physician</span>
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

    <!-- Doctor JS -->
    <script src="/js/doctor.js"></script>
    @stack('scripts')
</body>

</html>