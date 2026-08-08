@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>My Appointments</span>
        <a href="{{ route('patient.book') }}" class="btn"><i class="fa-solid fa-plus"></i> Book New</a>
    </div>

    {{-- Success banner shown after booking --}}
    <div id="booking-success-banner"
        style="display:none; background: rgba(34,197,94,0.12); border: 1px solid #16a34a; color: #15803d; border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; align-items: center; gap: 0.75rem; font-weight: 500;">
        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
        Appointment booked successfully! Your appointment has been scheduled.
        <button onclick="this.parentElement.style.display='none'"
            style="margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; font-size:1.1rem;">&#x2715;</button>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search appointments..." id="apt-search">
            </div>
            <div>
            </div>
        </div>

        <!-- Loading state -->
        <div id="apt-loading" style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>Loading your appointments...</div>
        </div>

        <div class="table-responsive" id="apt-table-wrapper" style="display:none;">
            <table>
                <thead>
                    <tr>
                        <th>Apt. No</th>
                        <th>Doctor Info</th>
                        <th>Consultation Date &amp; Time</th>
                        <th>Service</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apt-tbody">
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div id="apt-empty" style="display:none; text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fa-regular fa-calendar-xmark" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
            <div style="font-size: 1rem; margin-bottom: 0.5rem;">No appointments found.</div>
            <a href="{{ route('patient.book') }}" class="btn" style="margin-top: 1rem;">Book Your First Appointment</a>
        </div>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div id="view-apt-modal" class="modal" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div class="modal-content" style="background: #fff; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; position: relative;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Appointment Details</h2>
                <button class="close-modal" onclick="document.getElementById('view-apt-modal').style.display='none'" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-muted);"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="view-apt-body">
                <!-- Populated dynamically -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let allAppointments = [];

        (async function loadAppointments() {
            const token = localStorage.getItem('token');
            if (!token) { window.location.href = '/login'; return; }

            try {
                const res = await fetch('/api/patient/my-appointments', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (res.status === 401) { window.location.href = '/login'; return; }

                const data = await res.json();
                const loading = document.getElementById('apt-loading');
                const wrapper = document.getElementById('apt-table-wrapper');
                const empty = document.getElementById('apt-empty');
                const tbody = document.getElementById('apt-tbody');

                loading.style.display = 'none';

                if (!data.appointments || data.appointments.length === 0) {
                    empty.style.display = 'block';
                    return;
                }

                allAppointments = data.appointments;
                wrapper.style.display = 'block';

                data.appointments.forEach(apt => {
                    const date = new Date(apt.appointment_date);
                    const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                    let statusBadge = '';
                    if (apt.status === 'confirmed') {
                        statusBadge = `<span class="badge confirmed" style="background: rgba(34,197,94,0.1); color: #16a34a;">Confirmed (Paid)</span>`;
                    } else if (apt.status === 'scheduled') {
                        statusBadge = `<span class="badge" style="background: rgba(245,158,11,0.1); color: #f59e0b;">Pending Payment</span>`;
                    } else if (apt.status === 'completed') {
                        statusBadge = `<span class="badge completed" style="background: rgba(59,130,246,0.1); color: #2563eb;">Completed</span>`;
                    } else if (apt.status === 'cancelled') {
                        statusBadge = `<span class="badge cancelled" style="background: rgba(239,68,68,0.1); color: #dc2626;">Cancelled</span>`;
                    } else {
                        statusBadge = `<span class="badge" style="background: var(--bg-alt); color: var(--text-muted);">${apt.status}</span>`;
                    }

                    const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(apt.doctor_name)}&background=random`;

                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td style="font-weight: 600;">${apt.apt_number}</td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-dark);">${apt.doctor_name}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">${apt.specialization}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">${dateStr}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">${timeStr}</div>
                            </td>
                            <td>General Consultation</td>
                            <td>
                                <div style="font-weight: 700; font-size: 1.125rem; color: var(--primary);">
                                    #${apt.token_number ?? '-'}
                                </div>
                            </td>
                            <td>${statusBadge}</td>
                            <td>
                            <button class="btn-icon" title="View Details" onclick="viewAppointment(${apt.id})"><i class="fa-solid fa-eye"></i></button>
                            ${apt.status === 'scheduled' ? `<button class="btn-icon" style="color:var(--danger);" title="Cancel Appointment" onclick="cancelAppointment(${apt.id})"><i class="fa-solid fa-xmark"></i></button>` : ''}
                        </td>
                        </tr>
                    `);
                });

            } catch (err) {
                console.error('Failed to load appointments:', err);
                document.getElementById('apt-loading').innerHTML =
                    '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--danger); margin-bottom:1rem;"></i><div>Failed to load appointments. Please refresh.</div>';
            }
        })();

        function viewAppointment(id) {
            const apt = allAppointments.find(a => a.id === id);
            if(!apt) return;
            
            const date    = new Date(apt.appointment_date);
            const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            const html = `
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Appointment Number</div>
                    <div style="font-size: 1.25rem; font-weight: 600;">${apt.apt_number}</div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Doctor</div>
                    <div style="font-weight: 600;">${apt.doctor_name}</div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">${apt.specialization}</div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Schedule</div>
                    <div style="font-weight: 600;">${dateStr}</div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">${timeStr}</div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Token Number</div>
                    <div style="font-weight: 700; font-size: 1.25rem; color: var(--primary);">#${apt.token_number ?? '-'}</div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Status</div>
                    <div style="font-weight: 600; text-transform: capitalize;">${apt.status}</div>
                </div>
            `;
            document.getElementById('view-apt-body').innerHTML = html;
            document.getElementById('view-apt-modal').style.display = 'flex';
        }

        async function cancelAppointment(id) {
            if (!confirm("Are you sure you want to cancel this appointment?")) return;
            
            const token = localStorage.getItem('token');
            try {
                const res = await fetch('/api/patient/appointments/'+id+'/cancel', {
                    method: 'PUT',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok) {
                    alert('Appointment cancelled successfully.');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel appointment.');
                }
            } catch(e) {
                console.error(e);
                alert('An error occurred while cancelling.');
            }
        }

        // Show success banner if redirected from booking
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('booked') === '1') {
            const banner = document.getElementById('booking-success-banner');
            if (banner) {
                banner.style.display = 'flex';
                window.history.replaceState({}, document.title, window.location.pathname);
                setTimeout(() => { banner.style.display = 'none'; }, 5000);
            }
        }
    </script>
@endpush