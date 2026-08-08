@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>My Dashboard</span>
        <a href="{{ route('patient.book') }}" class="btn"><i class="fa-solid fa-plus"></i> Book New Appointment</a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Next Appointment</h3>
                <p id="stat-next-date">Loading...</p>
            </div>
            <div class="stat-icon blue">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>
        
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Total Appointments</h3>
                <p id="stat-total">--</p>
            </div>
            <div class="stat-icon teal">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Completed Visits</h3>
                <p id="stat-completed">--</p>
            </div>
            <div class="stat-icon purple">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Active Prescriptions</h3>
                <p id="stat-prescriptions">--</p>
            </div>
            <div class="stat-icon orange">
                <i class="fa-solid fa-pills"></i>
            </div>
        </div>
    </div>

    <!-- Upcoming Appointment Section -->
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Upcoming Appointment</h3>
    <div id="upcoming-appointment-container">
        <div class="card" style="text-align: center; padding: 2rem; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>Loading upcoming appointment...</div>
        </div>
    </div>

    <!-- Recent Medical History Summary -->
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-top: 2rem; margin-bottom: 1rem;">Recent Activity</h3>
    <div class="card">
        <div class="timeline" id="recent-activity-timeline">
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <div>Loading activity...</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            const res = await fetch('/api/patient/dashboard', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            
            if (res.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return;
            }

            const data = await res.json();
            
            // Populate Stats
            document.getElementById('stat-total').textContent = data.stats.total_appointments;
            document.getElementById('stat-completed').textContent = data.stats.completed_visits;
            document.getElementById('stat-prescriptions').textContent = data.stats.active_prescriptions;

            const upcomingContainer = document.getElementById('upcoming-appointment-container');
            
            if (data.next_appointment) {
                const apt = data.next_appointment;
                const aptDate = new Date(apt.appointment_date);
                
                document.getElementById('stat-next-date').textContent = aptDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                
                const month = aptDate.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
                const day = aptDate.getDate();
                const dayName = aptDate.toLocaleDateString('en-US', { weekday: 'long' });
                const doctorName = apt.doctor && apt.doctor.user ? apt.doctor.user.name : 'Unknown Doctor';
                const serviceName = apt.doctor && apt.doctor.service ? apt.doctor.service.name : 'Consultation';

                upcomingContainer.innerHTML = `
                <div class="card" style="display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, rgba(37,99,235,0.05) 0%, rgba(20,184,166,0.05) 100%); border-left: 4px solid var(--primary);">
                    <div style="display: flex; gap: 2rem; align-items: center;">
                        <div style="text-align: center; padding-right: 2rem; border-right: 1px solid var(--border);">
                            <div style="font-size: 0.875rem; color: var(--primary); font-weight: 600; text-transform: uppercase;">${month}</div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--text-dark); line-height: 1;">${day}</div>
                            <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500; margin-top: 0.25rem;">${dayName}</div>
                        </div>
                        
                        <div>
                            <h4 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">${doctorName}</h4>
                            <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.75rem;">${serviceName}</div>
                            <div style="display: flex; gap: 1.5rem;">
                                <span style="font-size: 0.875rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">Token #${apt.token_number || '--'}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end;">
                        <span class="badge confirmed" style="font-size: 0.875rem; padding: 0.4rem 1rem; text-transform: capitalize;">${apt.status}</span>
                    </div>
                </div>
                `;
            } else {
                document.getElementById('stat-next-date').textContent = 'None';
                
                upcomingContainer.innerHTML = `
                <div class="card" style="text-align: center; padding: 2rem; background: var(--bg-main);">
                    <i class="fa-regular fa-calendar" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <h4 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">No Upcoming Appointments</h4>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">You don't have any appointments scheduled.</p>
                    <a href="/patient/book" class="btn btn-sm"><i class="fa-solid fa-plus"></i> Book Now</a>
                </div>
                `;
            }

            // Populate Recent Activity
            const activityTimeline = document.getElementById('recent-activity-timeline');
            activityTimeline.innerHTML = '';
            
            if (!data.recent_activity || data.recent_activity.length === 0) {
                activityTimeline.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No recent activity found.
                    </div>
                `;
            } else {
                data.recent_activity.forEach(apt => {
                    const doctorName = apt.doctor && apt.doctor.user ? apt.doctor.user.name : 'Doctor';
                    const date = new Date(apt.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    
                    activityTimeline.insertAdjacentHTML('beforeend', `
                        <div class="timeline-item">
                            <h4 style="font-size: 1rem; font-weight: 600;">Consultation Completed</h4>
                            <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Visited Dr. ${doctorName} on ${date}.</p>
                            <a href="/patient/records" style="font-size: 0.875rem; color: var(--primary); font-weight: 500; text-decoration: none; display: inline-block; margin-top: 0.5rem;">View Record <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i></a>
                        </div>
                    `);
                });
            }
            
        } catch(e) {
            console.error(e);
            document.getElementById('upcoming-appointment-container').innerHTML = `<div style="color:red; text-align:center; padding:1rem;">Failed to load dashboard data.</div>`;
        }
    });
</script>
@endpush
