@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Doctor Dashboard</span>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" id="dashboard-stats" style="display: none;">
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Today's Appointments</h3>
                <p id="stat-today-total">0</p>
            </div>
            <div class="stat-icon blue">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>
        
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Completed</h3>
                <p id="stat-today-completed">0</p>
            </div>
            <div class="stat-icon teal">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Upcoming</h3>
                <p id="stat-today-upcoming">0</p>
            </div>
            <div class="stat-icon orange">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Total Patients Treated</h3>
                <p id="stat-total-treated">0</p>
            </div>
            <div class="stat-icon purple">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="dashboard-loading" style="text-align: center; padding: 3rem; color: var(--text-muted);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
        <div>Loading dashboard...</div>
    </div>

    <!-- Today's Appointment Queue -->
    <div class="card" id="dashboard-queue-card" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Today's Queue</h3>
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="queue-search" placeholder="Search patient...">
            </div>
        </div>

        <!-- Empty state -->
        <div id="queue-empty" style="display:none; text-align: center; padding: 2rem; color: var(--text-muted);">
            <div style="font-size: 1rem; margin-bottom: 0.5rem;" id="queue-empty-msg">No appointments in the queue today.</div>
        </div>

        <div class="table-responsive" id="queue-table-wrapper" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>Queue #</th>
                        <th>Patient Name</th>
                        <th>Age / Gender</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="queue-tbody">
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let allQueue = [];

    function updateTime() {
        const now = new Date();
        const timeEl = document.getElementById('current-time');
        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
    }
    setInterval(updateTime, 1000);
    updateTime();

    const token = localStorage.getItem('token');

    async function loadDashboard() {
        if (!token) { window.location.href = '/login'; return; }

        const loading = document.getElementById('dashboard-loading');
        const statsCard = document.getElementById('dashboard-stats');
        const queueCard = document.getElementById('dashboard-queue-card');
        const queueWrapper = document.getElementById('queue-table-wrapper');
        const emptyState = document.getElementById('queue-empty');

        loading.style.display = 'block';
        statsCard.style.display = 'none';
        queueCard.style.display = 'none';

        try {
            // Get today's date in YYYY-MM-DD
            const tzoffset = (new Date()).getTimezoneOffset() * 60000; // offset in milliseconds
            const localISOTime = (new Date(Date.now() - tzoffset)).toISOString().slice(0, 10);
            
            const res = await fetch(`/api/doctor/today-appointments?date=${localISOTime}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            loading.style.display = 'none';

            if (data.status === 'success') {
                const apts = data.appointments || [];
                allQueue = apts;

                // Update stats
                document.getElementById('stat-today-total').textContent = apts.length;
                document.getElementById('stat-today-completed').textContent = apts.filter(a => a.status === 'completed').length;
                document.getElementById('stat-today-upcoming').textContent = apts.filter(a => a.status === 'scheduled').length;
                document.getElementById('stat-total-treated').textContent = data.total_patients_treated || 0;

                statsCard.style.display = 'grid';
                queueCard.style.display = 'block';
                
                renderQueue(allQueue);
            }

        } catch (err) {
            console.error(err);
            loading.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load dashboard data.</div>';
        }
    }

    function renderQueue(apts) {
        const tbody = document.getElementById('queue-tbody');
        const wrapper = document.getElementById('queue-table-wrapper');
        const empty = document.getElementById('queue-empty');
        const searchStr = document.getElementById('queue-search').value.toLowerCase();
        
        tbody.innerHTML = '';

        const filtered = apts.filter(apt => {
            return !searchStr || apt.patient_name.toLowerCase().includes(searchStr);
        });

        if (filtered.length === 0) {
            wrapper.style.display = 'none';
            empty.style.display = 'block';
            if (apts.length > 0) {
                document.getElementById('queue-empty-msg').textContent = 'No patients match your search.';
            } else {
                document.getElementById('queue-empty-msg').textContent = 'No appointments in the queue today.';
            }
            return;
        }

        empty.style.display = 'none';
        wrapper.style.display = 'block';

        filtered.forEach(apt => {
            const ageStr = apt.age !== null ? `${apt.age} yrs` : 'N/A';
            // Wait, we don't have gender from API yet, but we can just use age for now.
            const ageGenderStr = `${ageStr}`;

            let statusHtml = '';
            let actionHtml = '';
            let rowStyle = '';
            let qBadgeStyle = 'background: var(--bg-main); color: var(--text-muted);';

            if (apt.status === 'confirmed') {
                rowStyle = 'style="background: rgba(20, 184, 166, 0.03);"';
                qBadgeStyle = 'background: rgba(20, 184, 166, 0.1); color: var(--secondary);';
                statusHtml = '<span class="badge" style="background:rgba(34,197,94,0.12);color:#16a34a;">Confirmed (Paid)</span>';
                actionHtml = `<button class="btn btn-secondary btn-sm" onclick="startConsultation(${apt.id})"><i class="fa-solid fa-play"></i> Start</button>`;
            } else if (apt.status === 'scheduled') {
                statusHtml = '<span class="badge" style="background:rgba(245,158,11,0.12);color:#f59e0b;">Scheduled (Unpaid)</span>';
                actionHtml = `<button class="btn btn-outline btn-sm" disabled title="Pending Payment at Reception">Unpaid</button>`;
            } else if (apt.status === 'completed') {
                statusHtml = '<span class="badge completed">Completed</span>';
                actionHtml = `<a href="{{ route('doctor.consultation') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>`;
            } else if (apt.status === 'cancelled') {
                statusHtml = '<span class="badge cancelled">Cancelled</span>';
                actionHtml = `<button class="btn btn-outline btn-sm" disabled>Cancelled</button>`;
            } else {
                statusHtml = `<span class="badge">${apt.status}</span>`;
                actionHtml = `<button class="btn btn-outline btn-sm" disabled>N/A</button>`;
            }

            tbody.insertAdjacentHTML('beforeend', `
                <tr ${rowStyle}>
                    <td>
                        <div class="queue-number" style="${qBadgeStyle}">${apt.token_number ?? '-'}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-dark);">${apt.patient_name}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${apt.patient_id_label}</div>
                    </td>
                    <td>${ageGenderStr}</td>
                    <td style="font-weight: 600; color: var(--primary);">${apt.time_label}</td>
                    <td>${statusHtml}</td>
                    <td>${actionHtml}</td>
                </tr>
            `);
        });
    }

    function startConsultation(aptId) {
        // You can redirect to consultation page with this aptId or open a modal
        window.location.href = `{{ route('doctor.consultation') }}?appointment_id=${aptId}`;
    }

    document.getElementById('queue-search').addEventListener('input', () => {
        renderQueue(allQueue);
    });

    // Load data on start
    loadDashboard();
</script>
@endpush
