@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Today's Appointments</span>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <input type="date" id="apt-date-picker" class="form-control"
                   style="display: inline-block; width: auto;"
                   value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <!-- Stats bar -->
    <div id="stats-bar" style="display: none; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <div class="card" style="flex:1; min-width:140px; padding: 1rem 1.25rem; text-align:center;">
            <div id="stat-total" style="font-size: 2rem; font-weight: 700; color: var(--primary);">0</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total</div>
        </div>
        <div class="card" style="flex:1; min-width:140px; padding: 1rem 1.25rem; text-align:center;">
            <div id="stat-scheduled" style="font-size: 2rem; font-weight: 700; color: #f59e0b;">0</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Scheduled</div>
        </div>
        <div class="card" style="flex:1; min-width:140px; padding: 1rem 1.25rem; text-align:center;">
            <div id="stat-confirmed" style="font-size: 2rem; font-weight: 700; color: #16a34a;">0</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Confirmed</div>
        </div>
        <div class="card" style="flex:1; min-width:140px; padding: 1rem 1.25rem; text-align:center;">
            <div id="stat-completed" style="font-size: 2rem; font-weight: 700; color: #2563eb;">0</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Completed</div>
        </div>
        <div class="card" style="flex:1; min-width:140px; padding: 1rem 1.25rem; text-align:center;">
            <div id="stat-cancelled" style="font-size: 2rem; font-weight: 700; color: #dc2626;">0</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Cancelled</div>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 300px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="apt-search" placeholder="Search patient name...">
            </div>
            <div>
                <span style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">Filter:</span>
                <select id="apt-filter" class="form-control"
                        style="display: inline-block; width: auto; margin-left: 0.5rem; padding: 0.4rem 1rem;">
                    <option value="">All Appointments</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- Loading state -->
        <div id="apt-loading" style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>Loading appointments...</div>
        </div>

        <!-- Table -->
        <div class="table-responsive" id="apt-table-wrapper" style="display:none;">
            <table>
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Time Slot</th>
                        <th>Patient</th>
                        <th>Apt. No</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apt-tbody"></tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div id="apt-empty" style="display:none; text-align: center; padding: 3rem; color: var(--text-muted);">
            <div style="font-size: 1rem; margin-bottom: 0.5rem;" id="apt-empty-msg">No appointments found for this date.</div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let allAppointments = [];

    const token = localStorage.getItem('token');

    async function loadAppointments(date) {
        if (!token) { window.location.href = '/login'; return; }

        const loading = document.getElementById('apt-loading');
        const wrapper = document.getElementById('apt-table-wrapper');
        const empty   = document.getElementById('apt-empty');
        const stats   = document.getElementById('stats-bar');
        const tbody   = document.getElementById('apt-tbody');

        loading.style.display = 'block';
        wrapper.style.display = 'none';
        empty.style.display   = 'none';
        stats.style.display   = 'none';
        tbody.innerHTML       = '';

        try {
            const res  = await fetch(`/api/doctor/today-appointments?date=${date}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            loading.style.display = 'none';

            if (!data.appointments || data.appointments.length === 0) {
                document.getElementById('apt-empty-msg').textContent = `No appointments found for ${date}.`;
                empty.style.display = 'block';
                return;
            }

            allAppointments = data.appointments;
            renderStats(data.appointments);
            renderRows(data.appointments);
            stats.style.display  = 'flex';
            wrapper.style.display = 'block';

        } catch (err) {
            console.error(err);
            loading.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load appointments. Please refresh.</div>';
        }
    }

    function renderStats(apts) {
        document.getElementById('stat-total').textContent     = apts.length;
        document.getElementById('stat-scheduled').textContent  = apts.filter(a => a.status === 'scheduled').length;
        document.getElementById('stat-confirmed').textContent  = apts.filter(a => a.status === 'confirmed').length;
        document.getElementById('stat-completed').textContent  = apts.filter(a => a.status === 'completed').length;
        document.getElementById('stat-cancelled').textContent  = apts.filter(a => a.status === 'cancelled').length;
    }

    function statusBadge(status, is_paid) {
        if (status === 'scheduled') {
            return is_paid
                ? `<span class="badge" style="background:rgba(34,197,94,0.12);color:#16a34a;font-size:0.78rem;padding:0.3rem 0.7rem;border-radius:6px;">Scheduled (Paid)</span>`
                : `<span class="badge" style="background:rgba(245,158,11,0.12);color:#f59e0b;font-size:0.78rem;padding:0.3rem 0.7rem;border-radius:6px;">Scheduled (Unpaid)</span>`;
        }
        const map = {
            confirmed: `<span class="badge" style="background:rgba(34,197,94,0.12);color:#16a34a;font-size:0.78rem;padding:0.3rem 0.7rem;border-radius:6px;">Confirmed (Paid)</span>`,
            completed: `<span class="badge" style="background:rgba(59,130,246,0.12);color:#2563eb;font-size:0.78rem;padding:0.3rem 0.7rem;border-radius:6px;">Completed</span>`,
            cancelled: `<span class="badge" style="background:rgba(239,68,68,0.1);color:#dc2626;font-size:0.78rem;padding:0.3rem 0.7rem;border-radius:6px;">Cancelled</span>`,
        };
        return map[status] || `<span class="badge">${status}</span>`;
    }

    function actionButtons(apt) {
        let btns = `<a href="{{ route('doctor.patients') }}" class="btn-icon" title="View Record"><i class="fa-solid fa-folder-open"></i></a>`;
        if (apt.status === 'scheduled' && apt.is_paid) {
            btns += `<button class="btn btn-secondary btn-sm" style="margin-left:0.25rem;" onclick="window.location.href='{{ route('doctor.consultation') }}?appointment_id=${apt.id}'" title="Start Consultation"><i class="fa-solid fa-play"></i> Start</button>`;
        }
        if (['scheduled', 'confirmed'].includes(apt.status)) {
            btns += `
                <button class="btn btn-sm" style="margin-left:0.25rem;" onclick="updateStatus(${apt.id}, 'completed')" title="Mark Completed">
                    <i class="fa-solid fa-circle-check"></i> Complete
                </button>
                <button class="btn btn-outline btn-sm" style="margin-left:0.25rem; color:var(--danger); border-color:var(--danger);" onclick="updateStatus(${apt.id}, 'cancelled')" title="Cancel">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
        } else if (apt.status === 'completed') {
            btns += `<a href="{{ route('doctor.consultation') }}" class="btn btn-sm" style="margin-left:0.25rem;"><i class="fa-solid fa-stethoscope"></i> View</a>`;
        }
        return btns;
    }

    function renderRows(apts) {
        const tbody = document.getElementById('apt-tbody');
        const empty = document.getElementById('apt-empty');
        const wrapper = document.getElementById('apt-table-wrapper');
        tbody.innerHTML = '';

        const filtered = apts.filter(apt => {
            const search = document.getElementById('apt-search').value.toLowerCase();
            const filter = document.getElementById('apt-filter').value;
            const matchSearch = !search || apt.patient_name.toLowerCase().includes(search);
            const matchFilter = !filter || apt.status === filter;
            return matchSearch && matchFilter;
        });

        if (filtered.length === 0) {
            wrapper.style.display = 'none';
            document.getElementById('apt-empty-msg').textContent = 'No appointments match your search/filter.';
            empty.style.display = 'block';
            return;
        }

        empty.style.display   = 'none';
        wrapper.style.display = 'block';

        filtered.forEach(apt => {
            const ageLabel = apt.age !== null ? ` • ${apt.age} yrs` : '';
            const highlightRow = apt.status === 'scheduled' ? 'style="background: rgba(37,99,235,0.04);"' : '';

            tbody.insertAdjacentHTML('beforeend', `
                <tr id="row-${apt.id}" ${highlightRow}>
                    <td>
                        <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">#${apt.token_number ?? '-'}</span>
                    </td>
                    <td style="font-weight: 600; color: var(--primary);">${apt.time_label}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-dark);">${apt.patient_name}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${apt.patient_id_label}${ageLabel}</div>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">${apt.apt_number}</td>
                    <td id="status-${apt.id}">${statusBadge(apt.status, apt.is_paid)}</td>
                    <td id="actions-${apt.id}">${actionButtons(apt)}</td>
                </tr>
            `);
        });
    }

    async function updateStatus(aptId, newStatus) {
        if (!token) { window.location.href = '/login'; return; }

        const label = newStatus === 'completed' ? 'mark this appointment as completed' : 'cancel this appointment';
        if (!confirm(`Are you sure you want to ${label}?`)) return;

        try {
            const res = await fetch(`/api/doctor/appointments/${aptId}/status`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await res.json();
            if (res.ok && data.status === 'success') {
                // Update the in-memory data
                const apt = allAppointments.find(a => a.id === aptId);
                if (apt) apt.status = newStatus;

                // Update the row in-place without full reload
                document.getElementById(`status-${aptId}`).innerHTML  = statusBadge(newStatus);
                document.getElementById(`actions-${aptId}`).innerHTML = actionButtons({ ...apt, status: newStatus });

                // Refresh stats
                renderStats(allAppointments);
            } else {
                alert(data.message || 'Failed to update status.');
            }
        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
        }
    }

    // Initial load
    const today = document.getElementById('apt-date-picker').value;
    loadAppointments(today);

    // Date picker change
    document.getElementById('apt-date-picker').addEventListener('change', function() {
        loadAppointments(this.value);
    });

    // Search & filter
    document.getElementById('apt-search').addEventListener('input', () => renderRows(allAppointments));
    document.getElementById('apt-filter').addEventListener('change', () => renderRows(allAppointments));
</script>
@endpush
