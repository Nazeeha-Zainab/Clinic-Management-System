document.addEventListener('DOMContentLoaded', () => {
    // Check Auth
    const token = localStorage.getItem('token');
    const userString = localStorage.getItem('user');
    
    if (!token || !userString) {
        window.location.href = '/login';
        return;
    }

    const user = JSON.parse(userString);
    if (user.role !== 'patient') {
        alert('Access denied. Patient role required.');
        window.location.href = '/login';
        return;
    }

    // Set Patient Name in Header
    const patientNameEl = document.getElementById('patient-name-header');
    if (patientNameEl) {
        patientNameEl.textContent = user.name;
    }

    // Handle Patient Logout
    const logoutBtn = document.getElementById('patient-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
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

    // Interactive booking selection logic (Dummy implementation for UI)
    const selectableCards = document.querySelectorAll('.selectable-card');
    selectableCards.forEach(card => {
        card.addEventListener('click', () => {
            // Unselect siblings
            const siblings = card.parentElement.querySelectorAll('.selectable-card');
            siblings.forEach(s => s.classList.remove('selected'));
            card.classList.add('selected');
        });
    });

    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            if (slot.classList.contains('disabled')) return;
            const siblings = slot.parentElement.querySelectorAll('.time-slot');
            siblings.forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
        });
    });
});
