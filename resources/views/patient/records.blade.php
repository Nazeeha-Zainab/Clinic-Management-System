@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>Medical Records</span>
    </div>

    <!-- Health Overview Cards -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="display: flex; align-items: flex-start; gap: 1rem;">
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--primary);"><i class="fa-solid fa-notes-medical"></i></div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Medical History & Notes</div>
                <div id="medical-history-content" style="font-size: 1rem; font-weight: 500; margin-top: 0.5rem; line-height: 1.5; color: var(--text-dark);">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <!-- Medical History Timeline -->
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 2rem;">Consultation History</h3>
        
        <div class="timeline" id="records-timeline">
            <!-- Dynamic Content -->
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <div>Loading records...</div>
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
            const res = await fetch('/api/patient/records', {
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
            
            // Populate Medical History
            const historyEl = document.getElementById('medical-history-content');
            if (data.patient && data.patient.medical_history) {
                historyEl.textContent = data.patient.medical_history;
            } else {
                historyEl.textContent = 'No medical history provided.';
            }
            
            // Populate Timeline
            const timelineEl = document.getElementById('records-timeline');
            timelineEl.innerHTML = ''; // Clear loading
            
            if (!data.appointments || data.appointments.length === 0) {
                timelineEl.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
                        <div style="font-size: 1rem;">No medical records found.</div>
                    </div>
                `;
                return;
            }

            data.appointments.forEach(apt => {
                const serviceName = apt.doctor && apt.doctor.service ? apt.doctor.service.name : 'Consultation';
                const doctorName = apt.doctor && apt.doctor.user ? apt.doctor.user.name : 'Unknown Doctor';
                const date = new Date(apt.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                
                let consultationHtml = '';
                if (apt.consultation) {
                    consultationHtml = `
                    <div style="background: var(--bg-main); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <h5 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Diagnosis & Notes</h5>
                        <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.5;">
                            <strong>Symptoms:</strong> ${apt.consultation.presenting_symptoms || 'N/A'}<br>
                            <strong>Diagnosis:</strong> ${apt.consultation.clinical_diagnosis || 'N/A'}<br>
                            <strong>Plan:</strong> ${apt.consultation.treatment_plan || 'N/A'}
                        </p>
                    </div>`;
                } else {
                    consultationHtml = `
                    <div style="background: var(--bg-main); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.5;">No detailed consultation notes available for this visit.</p>
                    </div>`;
                }
                
                let prescriptionBtn = '';
                if (apt.prescriptions && apt.prescriptions.length > 0) {
                    prescriptionBtn = `<a href="/patient/prescriptions" class="btn btn-outline btn-sm"><i class="fa-solid fa-pills"></i> View Prescriptions</a>`;
                }

                timelineEl.insertAdjacentHTML('beforeend', `
                    <div class="timeline-item">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h4 style="font-size: 1.125rem; font-weight: 600;">${serviceName}</h4>
                                <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">
                                    Consulted by <strong style="color: var(--text-dark);">${doctorName}</strong> • ${date}
                                </p>
                            </div>
                            <span class="badge completed">Completed</span>
                        </div>
                        ${consultationHtml}
                        <div style="display: flex; gap: 1rem;">
                            ${prescriptionBtn}
                        </div>
                    </div>
                `);
            });
            
        } catch(e) {
            console.error(e);
            document.getElementById('records-timeline').innerHTML = `<div style="text-align:center;color:red;padding:2rem;">Failed to load records.</div>`;
        }
    });
</script>
@endpush
