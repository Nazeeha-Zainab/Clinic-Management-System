@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Notifications <span id="unread-badge" style="display:none; background: var(--primary); color: #fff; font-size: 0.75rem; font-weight: 700; border-radius: 999px; padding: 0.15rem 0.55rem; margin-left: 0.5rem; vertical-align: middle;"></span></span>
        <button class="btn btn-outline btn-sm" id="mark-all-btn" onclick="markAllRead()" style="display:none;">
            <i class="fa-solid fa-check-double"></i> Mark all as read
        </button>
    </div>

    <!-- Loading state -->
    <div id="notif-loading" style="text-align: center; padding: 4rem; color: var(--text-muted);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
        <div>Loading notifications...</div>
    </div>

    <!-- Notifications list -->
    <div class="card" id="notif-list" style="display:none; padding: 0;"></div>

    <!-- Empty state -->
    <div id="notif-empty" style="display:none; text-align: center; padding: 4rem; color: var(--text-muted);">
        <i class="fa-regular fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.35;"></i>
        <div style="font-size: 1rem; font-weight: 500;">No notifications yet.</div>
        <div style="font-size: 0.875rem; margin-top: 0.4rem;">You'll be notified when appointments are booked or cancelled.</div>
    </div>
@endsection

@push('scripts')
<script>
    const token = localStorage.getItem('token');
    let notifications = [];

    const iconMap = {
        appointment_booked:    { icon: 'fa-calendar-plus',       cls: 'blue'   },
        appointment_cancelled: { icon: 'fa-triangle-exclamation', cls: 'orange' },
        lab_results:           { icon: 'fa-flask-vial',           cls: 'purple' },
        default:               { icon: 'fa-bell',                 cls: 'teal'   },
    };

    async function loadNotifications() {
        if (!token) { window.location.href = '/login'; return; }

        try {
            const res  = await fetch('/api/doctor/notifications', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            document.getElementById('notif-loading').style.display = 'none';

            if (!data.notifications || data.notifications.length === 0) {
                document.getElementById('notif-empty').style.display = 'block';
                return;
            }

            notifications = data.notifications;
            renderNotifications();

            // Unread badge & button
            if (data.unread_count > 0) {
                const badge = document.getElementById('unread-badge');
                badge.textContent = data.unread_count;
                badge.style.display = 'inline';
                document.getElementById('mark-all-btn').style.display = 'inline-flex';
            }

        } catch (err) {
            console.error(err);
            document.getElementById('notif-loading').innerHTML =
                '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load notifications.</div>';
        }
    }

    function renderNotifications() {
        const list = document.getElementById('notif-list');
        list.innerHTML = '';
        list.style.display = 'block';

        notifications.forEach((n, idx) => {
            const iconCfg = iconMap[n.type] || iconMap.default;
            const isLast  = idx === notifications.length - 1;
            const unreadBg = n.is_unread ? 'background: rgba(37, 99, 235, 0.03);' : '';
            const dot = n.is_unread
                ? '<div style="width:10px;height:10px;border-radius:50%;background:var(--primary);margin-top:0.4rem;flex-shrink:0;"></div>'
                : '<div style="width:10px;height:10px;margin-top:0.4rem;flex-shrink:0;"></div>';

            list.insertAdjacentHTML('beforeend', `
                <div id="notif-${n.id}"
                     style="display:flex;align-items:flex-start;gap:1rem;padding:1.25rem 1.5rem;
                            ${isLast ? '' : 'border-bottom:1px solid var(--border);'}
                            ${unreadBg} cursor:pointer; transition: background 0.2s;"
                     onclick="markRead(${n.id})"
                     onmouseenter="this.style.background='rgba(37,99,235,0.06)'"
                     onmouseleave="this.style.background='${n.is_unread ? 'rgba(37,99,235,0.03)' : ''}'">
                    ${dot}
                    <div class="stat-icon ${iconCfg.cls}" style="width:40px;height:40px;font-size:1.1rem;flex-shrink:0;">
                        <i class="fa-regular ${iconCfg.icon}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <h4 style="font-size:0.95rem;font-weight:${n.is_unread ? '700' : '600'};margin-bottom:0.2rem;">
                            ${n.title}
                        </h4>
                        <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:0.4rem;line-height:1.5;">
                            ${n.message}
                        </p>
                        <span style="font-size:0.75rem;color:var(--text-muted);font-weight:500;">
                            <i class="fa-regular fa-clock" style="margin-right:0.2rem;"></i>${n.time_ago}
                        </span>
                    </div>
                </div>
            `);
        });
    }

    async function markRead(id) {
        // Only hit API if still unread
        const n = notifications.find(x => x.id === id);
        if (!n || !n.is_unread) return;

        try {
            await fetch(`/api/doctor/notifications/${id}/read`, {
                method: 'PUT',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            // Update in-memory
            n.is_unread = false;
            const el = document.getElementById(`notif-${id}`);
            if (el) {
                // remove blue background
                el.style.background = '';
                // remove blue dot
                const dot = el.querySelector('div[style*="background:var(--primary)"]');
                if (dot) dot.style.background = 'transparent';
                // lighten title weight
                const title = el.querySelector('h4');
                if (title) title.style.fontWeight = '600';
            }

            // Update badge count
            const unread = notifications.filter(x => x.is_unread).length;
            const badge  = document.getElementById('unread-badge');
            if (unread === 0) {
                badge.style.display = 'none';
                document.getElementById('mark-all-btn').style.display = 'none';
            } else {
                badge.textContent = unread;
            }

        } catch (err) {
            console.error('Failed to mark notification as read:', err);
        }
    }

    async function markAllRead() {
        try {
            const res = await fetch('/api/doctor/notifications/mark-all-read', {
                method: 'PUT',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                notifications.forEach(n => n.is_unread = false);
                renderNotifications();
                document.getElementById('unread-badge').style.display = 'none';
                document.getElementById('mark-all-btn').style.display = 'none';
            }
        } catch (err) {
            console.error('Failed to mark all as read:', err);
        }
    }

    loadNotifications();
</script>
@endpush
