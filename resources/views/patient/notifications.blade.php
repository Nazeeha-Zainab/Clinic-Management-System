@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>Notifications</span>
        <button class="btn btn-outline btn-sm" id="btn-mark-all">Mark all as read</button>
    </div>

    <div class="card" style="padding: 0;" id="notif-container">
        <!-- Loading -->
        <div id="notif-loading" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem; margin-bottom:1rem;"></i>
            <div>Loading notifications...</div>
        </div>

        <!-- Empty -->
        <div id="notif-empty" style="display:none; text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-regular fa-bell-slash" style="font-size:3rem; margin-bottom:1rem; opacity:0.35;"></i>
            <div style="font-size:1rem; font-weight:500;">No notifications found.</div>
        </div>

        <!-- List -->
        <div id="notif-list"></div>
    </div>
@endsection

@push('scripts')
<script>
    const token = localStorage.getItem('token');
    
    async function loadNotifications() {
        if (!token) return window.location.href = '/login';
        
        try {
            const res = await fetch('/api/patient/notifications', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.status === 401) { window.location.href = '/login'; return; }
            
            const data = await res.json();
            
            document.getElementById('notif-loading').style.display = 'none';
            
            if (data.status === 'success') {
                renderNotifications(data.notifications);
            }
        } catch (err) {
            console.error(err);
            document.getElementById('notif-loading').innerHTML = '<div style="color:var(--danger);"><i class="fa-solid fa-triangle-exclamation"></i> Failed to load notifications.</div>';
        }
    }
    
    function renderNotifications(notifications) {
        const list = document.getElementById('notif-list');
        const empty = document.getElementById('notif-empty');
        list.innerHTML = '';
        
        if (!notifications || notifications.length === 0) {
            empty.style.display = 'block';
            return;
        }
        
        empty.style.display = 'none';
        
        notifications.forEach(n => {
            const isUnread = n.is_unread;
            const bg = isUnread ? 'rgba(37, 99, 235, 0.03)' : 'transparent';
            const dot = isUnread ? `<div style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary); margin-top: 0.5rem;"></div>` : `<div style="width: 10px; height: 10px; margin-top: 0.5rem;"></div>`;
            
            // Icon logic based on type (you can expand this)
            let iconStr = '<i class="fa-solid fa-bell"></i>';
            let iconColor = 'blue';
            if (n.type.includes('cancel')) { iconStr = '<i class="fa-solid fa-triangle-exclamation"></i>'; iconColor = 'orange'; }
            if (n.type.includes('result') || n.type.includes('lab')) { iconStr = '<i class="fa-solid fa-flask-vial"></i>'; iconColor = 'teal'; }
            if (n.type.includes('completed')) { iconStr = '<i class="fa-solid fa-clipboard-check"></i>'; iconColor = 'purple'; }
            
            const html = `
                <div class="notif-item" data-id="${n.id}" data-unread="${isUnread}" style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid var(--border); background: ${bg}; cursor: ${isUnread ? 'pointer' : 'default'};">
                    ${dot}
                    <div class="stat-icon ${iconColor}" style="width: 40px; height: 40px; font-size: 1.2rem;">
                        ${iconStr}
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.25rem;">${n.title}</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">${n.message}</p>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">${n.time_ago}</span>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        });
        
        // Add click listeners to mark as read
        document.querySelectorAll('.notif-item[data-unread="true"]').forEach(item => {
            item.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                try {
                    const res = await fetch(`/api/patient/notifications/${id}/read`, {
                        method: 'PUT',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        this.style.background = 'transparent';
                        this.style.cursor = 'default';
                        this.setAttribute('data-unread', 'false');
                        this.querySelector('div').style.background = 'transparent'; // Hide dot
                    }
                } catch(e) { console.error(e); }
            });
        });
    }

    document.getElementById('btn-mark-all').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Marking...';
        btn.disabled = true;
        
        try {
            const res = await fetch('/api/patient/notifications/mark-all-read', {
                method: 'PUT',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                loadNotifications();
            }
        } catch(e) { console.error(e); }
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
    
    loadNotifications();
</script>
@endpush
