<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Care Plus Clinic</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Patient CSS -->
    <link rel="stylesheet" href="/css/patient.css">
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-heart-pulse" style="color: var(--secondary);"></i> Care Plus
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('patient.dashboard') }}"
                class="nav-item {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="{{ route('patient.book') }}"
                class="nav-item {{ request()->routeIs('patient.book') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-plus"></i> Book Appointment
            </a>
            <a href="{{ route('patient.appointments') }}"
                class="nav-item {{ request()->routeIs('patient.appointments') ? 'active' : '' }}">
                <i class="fa-regular fa-calendar-check"></i> My Appointments
            </a>
            <a href="{{ route('patient.records') }}"
                class="nav-item {{ request()->routeIs('patient.records') ? 'active' : '' }}">
                <i class="fa-solid fa-file-medical"></i> Medical Records
            </a>
            <a href="{{ route('patient.prescriptions') }}"
                class="nav-item {{ request()->routeIs('patient.prescriptions') ? 'active' : '' }}">
                <i class="fa-solid fa-pills"></i> Prescriptions
            </a>
            <a href="{{ route('patient.notifications') }}"
                class="nav-item {{ request()->routeIs('patient.notifications') ? 'active' : '' }}">
                <i class="fa-regular fa-bell"></i> Notifications
            </a>
            <div style="border-top: 1px solid var(--border); margin: 1rem 0;"></div>
            <a href="{{ route('patient.profile') }}"
                class="nav-item {{ request()->routeIs('patient.profile') ? 'active' : '' }}">
                <i class="fa-regular fa-user"></i> Profile
            </a>
            <a href="#" class="nav-item" id="patient-logout" style="color: var(--danger);">
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
                    <img src="https://ui-avatars.com/api/?name=Patient+User&background=14B8A6&color=fff" alt="Patient">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 600; font-size: 0.875rem;" id="patient-name-header">Patient
                            User</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Patient Account</span>
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

    <!-- Patient JS -->
    <script src="/js/patient.js"></script>
    @stack('scripts')
</body>

</html>