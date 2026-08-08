document.addEventListener('DOMContentLoaded', () => {
    const dashboardView = document.getElementById('dashboard-view');
    const logoutBtn = document.getElementById('logout-btn');
    
    // Check if user is logged in
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    // Redirect admin to specific admin dashboard
    const userString = localStorage.getItem('user');
    if (userString) {
        const user = JSON.parse(userString);
        if (user.role === 'admin') {
            window.location.href = '/admin/dashboard';
            return;
        } else if (user.role === 'patient') {
            window.location.href = '/patient/dashboard';
            return;
        } else if (user.role === 'receptionist') {
            window.location.href = '/receptionist/dashboard';
            return;
        } else if (user.role === 'doctor') {
            window.location.href = '/doctor/dashboard';
            return;
        }
    }

    // Initialize Dashboard
    showDashboard();

    // Handle Logout
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            if (token) {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
            }
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        });
    }
});

async function showDashboard() {
    const dashboardView = document.getElementById('dashboard-view');
    const userRoleEl = document.getElementById('user-role');
    const userNameEl = document.getElementById('user-name');
    const dashboardContent = document.getElementById('dashboard-content');
    const navMenu = document.getElementById('nav-menu');
    
    if (dashboardView) dashboardView.style.display = 'flex';
    
    const userString = localStorage.getItem('user');
    if (!userString) return;
    
    const user = JSON.parse(userString);
    userRoleEl.textContent = user.role.toUpperCase();
    userNameEl.textContent = user.name;
    
    navMenu.innerHTML = ''; // reset navigation 
    
    const token = localStorage.getItem('token');
    
    try {
        const response = await fetch(`/api/${user.role === 'receptionist' ? 'reception' : user.role}/dashboard`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            dashboardContent.innerHTML = `<h3>Welcome to your Dashboard</h3><p>${data.message || ''}</p>`;
        } else {
            // Token invalid or expired
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
    } catch (err) {
        console.error(err);
    }
}
