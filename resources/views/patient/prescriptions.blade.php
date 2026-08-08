@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>My Prescriptions</span>
    </div>

    <!-- Active Prescriptions -->
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Medications</h3>
    <div id="prescriptions-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Loading State -->
        <div id="prescriptions-loading" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>Loading prescriptions...</div>
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
            const res = await fetch('/api/patient/prescriptions', {
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
            const gridEl = document.getElementById('prescriptions-grid');
            gridEl.innerHTML = ''; // Clear loading
            
            if (!data.appointments || data.appointments.length === 0) {
                gridEl.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fa-solid fa-pills" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;"></i>
                        <div style="font-size: 1rem;">No prescriptions found.</div>
                    </div>
                `;
                return;
            }

            data.appointments.forEach(apt => {
                const doctorName = apt.doctor && apt.doctor.user ? apt.doctor.user.name : 'Unknown Doctor';
                const date = new Date(apt.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                
                apt.prescriptions.forEach(rx => {
                    const html = `
                    <div class="card" style="border-top: 4px solid var(--secondary);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px dashed var(--border); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <div>
                                <h4 style="font-size: 1.125rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.25rem;">${rx.medicine || 'Unknown Medicine'}</h4>
                                <span style="font-size: 0.75rem; color: var(--text-muted); background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: 4px;">${rx.type || 'Medication'}</span>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">Prescribed On</div>
                                <div style="font-size: 0.875rem; font-weight: 500;">${date}</div>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Dosage</div>
                                <div style="font-size: 0.875rem; font-weight: 600;"><i class="fa-solid fa-pills" style="color: var(--secondary); margin-right: 0.5rem;"></i>${rx.dosage || 'N/A'}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Frequency</div>
                                <div style="font-size: 0.875rem; font-weight: 600;"><i class="fa-regular fa-clock" style="color: var(--secondary); margin-right: 0.5rem;"></i>${rx.frequency || 'N/A'}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Duration</div>
                                <div style="font-size: 0.875rem; font-weight: 600;">${rx.duration || 'N/A'}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Instructions</div>
                                <div style="font-size: 0.875rem; font-weight: 600;">${rx.instructions || 'N/A'}</div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Dr. ${doctorName}</div>
                            <button class="btn btn-outline btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                        </div>
                    </div>
                    `;
                    gridEl.insertAdjacentHTML('beforeend', html);
                });
            });
            
        } catch(e) {
            console.error(e);
            document.getElementById('prescriptions-grid').innerHTML = `<div style="grid-column: 1 / -1; text-align:center;color:red;padding:2rem;">Failed to load prescriptions.</div>`;
        }
    });
</script>
@endpush
