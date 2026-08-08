<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Care Plus Clinic</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
</head>
<body>

    <!-- Dashboard View -->
    <div id="dashboard-view" class="dashboard-layout">
        <aside class="sidebar glass-panel" style="border-radius: 0; border-right: 1px solid var(--surface-border); border-top: none; border-bottom: none; border-left: none;">
            <div style="padding: 1rem 0;">
                <h2 style="margin: 0; color: #4F46E5;">CMS</h2>
                <div style="font-size: 0.8rem; color: #6B7280; margin-top: 0.5rem;" id="user-role">ROLE</div>
            </div>
            <div id="nav-menu" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="#" class="nav-item active">Dashboard</a>
            </div>
            <div style="margin-top: auto;">
                <button id="logout-btn" class="btn btn-secondary">Logout</button>
            </div>
        </aside>
        
        <main class="main-content">
            <header class="header">
                <h2>Dashboard</h2>
                <div>
                    Welcome, <strong id="user-name">User</strong>
                </div>
            </header>
            
            <div class="glass-panel card" id="dashboard-content">
                Loading...
            </div>
        </main>
    </div>

</body>
</html>
