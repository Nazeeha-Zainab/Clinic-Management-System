<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - Care Plus Clinic</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Receptionist CSS -->
    <link rel="stylesheet" href="/css/receptionist.css">
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-heart-pulse" style="color: var(--secondary);"></i> Care Plus
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('receptionist.dashboard') }}"
                class="nav-item {{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Patient Management</div>
            <a href="{{ route('receptionist.register') }}"
                class="nav-item {{ request()->routeIs('receptionist.register') ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus"></i> Register Patient
            </a>
            <a href="{{ route('receptionist.patients') }}"
                class="nav-item {{ request()->routeIs('receptionist.patients') ? 'active' : '' }}">
                <i class="fa-solid fa-hospital-user"></i> Patients
            </a>

            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Appointments</div>
            <a href="{{ route('receptionist.appointments') }}"
                class="nav-item {{ request()->routeIs('receptionist.appointments') ? 'active' : '' }}">
                <i class="fa-regular fa-calendar-check"></i> All Appointments
            </a>
            <a href="{{ route('receptionist.walkin') }}"
                class="nav-item {{ request()->routeIs('receptionist.walkin') ? 'active' : '' }}">
                <i class="fa-solid fa-person-walking"></i> Walk-in
            </a>
            <a href="{{ route('receptionist.schedule') }}"
                class="nav-item {{ request()->routeIs('receptionist.schedule') ? 'active' : '' }}">
                <i class="fa-solid fa-user-doctor"></i> Doctor Schedule
            </a>

            <div
                style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: 1rem 1rem 0.5rem 1rem;">
                Finance</div>
            <a href="{{ route('receptionist.billing') }}"
                class="nav-item {{ request()->routeIs('receptionist.billing') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Billing & Payments
            </a>

            <div style="border-top: 1px solid var(--border); margin: 1rem 0;"></div>
            <a href="{{ route('receptionist.notifications') }}"
                class="nav-item {{ request()->routeIs('receptionist.notifications') ? 'active' : '' }}">
                <i class="fa-regular fa-bell"></i> Notifications
            </a>
            <a href="{{ route('receptionist.profile') }}"
                class="nav-item {{ request()->routeIs('receptionist.profile') ? 'active' : '' }}">
                <i class="fa-regular fa-user"></i> Profile
            </a>
            <a href="#" class="nav-item" id="receptionist-logout" style="color: var(--danger);">
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
                    <img src="https://ui-avatars.com/api/?name=Receptionist&background=2563EB&color=fff"
                        alt="Receptionist">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 600; font-size: 0.875rem;" id="receptionist-name-header">Front
                            Desk</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Receptionist</span>
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

    <!-- Receptionist JS -->
    <script src="/js/receptionist.js"></script>
    @stack('scripts')
</body>

</html>