@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Walk-in Appointment</span>
    </div>

    <!-- Main Alert -->
    <div id="walkin-alert" style="display:none; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;"></div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Search or Register Patient -->
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">1. Select Patient</h3>
            
            <div class="form-group">
                <label>Search Existing Patient</label>
                <div class="header-search" style="width: 100%;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="patient-search" placeholder="Search by name, ID, or phone...">
                </div>
            </div>

            <div id="patient-results" style="max-height: 250px; overflow-y: auto; margin-top: 1rem; border: 1px solid var(--border); border-radius: 8px; display: none;">
                <!-- Search results will appear here -->
            </div>

            <!-- Selected Patient Card -->
            <div id="selected-patient-card" style="display:none; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h4 id="sp-name" style="font-weight: 600; color: var(--primary);"></h4>
                        <p id="sp-id" style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;"></p>
                        <p id="sp-phone" style="font-size: 0.875rem; color: var(--text-muted);"></p>
                    </div>
                    <button class="btn btn-sm" onclick="clearPatient()"><i class="fa-solid fa-xmark"></i> Clear</button>
                </div>
            </div>

            <div style="text-align: center; margin: 1.5rem 0;">
                <span style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500; background: var(--bg-white); padding: 0 1rem; position: relative; z-index: 2;">OR</span>
                <div style="height: 1px; background: var(--border); margin-top: -10px;"></div>
            </div>

            <a href="{{ route('receptionist.register') }}" class="btn btn-outline" style="width: 100%; justify-content: center; display: flex;"><i class="fa-solid fa-user-plus"></i> Register New Patient</a>
        </div>

        <!-- Book Slot -->
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">2. Assign Doctor & Time</h3>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Select Doctor Available Today</label>
                    <select class="form-control" id="doctor-select" disabled>
                        <option value="">Loading doctors...</option>
                    </select>
                </div>
            </div>

            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Available Slots</h4>
            <div id="slots-container" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem;">
                <div style="color: var(--text-muted); font-size: 0.875rem;">Please select a doctor to view slots.</div>
            </div>

            <div style="background: var(--bg-main); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Booking Summary</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">Patient</span>
                    <span id="summary-patient" style="font-weight: 600;">Not Selected</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">Doctor</span>
                    <span id="summary-doctor" style="font-weight: 600;">Not Selected</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                    <span style="color: var(--text-muted);">Time</span>
                    <span id="summary-time" style="font-weight: 600; color: var(--primary);">Not Selected</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <button class="btn btn-outline" onclick="window.location.reload()">Cancel</button>
                <button class="btn" id="btn-confirm-walkin" disabled>Confirm Walk-in Booking</button>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const token = localStorage.getItem('token');
    
    let allPatients = [];
    let todaySchedules = [];
    
    let selectedPatientId = null;
    let selectedPatientName = null;
    let selectedDoctorId = null;
    let selectedDoctorName = null;
    let selectedTime = null;
    let selectedDate = null;

    async function initWalkin() {
        if (!token) { window.location.href = '/login'; return; }
        
        try {
            // Fetch patients
            const pRes = await fetch('/api/reception/patients', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            const pData = await pRes.json();
            if (pData.status === 'success') {
                allPatients = pData.patients;
                
                const urlParams = new URLSearchParams(window.location.search);
                const pId = urlParams.get('patient_id');
                if (pId) {
                    const p = allPatients.find(x => x.id == pId);
                    if (p) {
                        selectPatient(p.id, p.full_name, p.patient_id, p.phone || '');
                    }
                }
            }

            // Fetch schedules for today
            const today = new Date();
            const offset = today.getTimezoneOffset() * 60000;
            const todayStr = new Date(today.getTime() - offset).toISOString().split('T')[0];
            selectedDate = todayStr;
            
            const sRes = await fetch(`/api/reception/schedules?date=${todayStr}`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            const sData = await sRes.json();
            if (sData.status === 'success') {
                // Filter for just today
                todaySchedules = sData.schedules.filter(s => s.date === todayStr);
                populateDoctors();
            }

        } catch (err) {
            console.error(err);
            showAlert('Failed to load data. Please refresh.', 'error');
        }
    }

    function populateDoctors() {
        const select = document.getElementById('doctor-select');
        select.innerHTML = '<option value="" disabled selected>-- Select a Doctor --</option>';
        
        if (todaySchedules.length === 0) {
            select.innerHTML = '<option value="" disabled selected>No doctors scheduled for today</option>';
            return;
        }

        todaySchedules.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = `${s.doctor_name} (${s.start_fmt} - ${s.end_fmt})`;
            select.appendChild(opt);
        });
        
        select.disabled = false;
    }

    // Patient Search
    document.getElementById('patient-search').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const resultsDiv = document.getElementById('patient-results');
        
        if (!query) {
            resultsDiv.style.display = 'none';
            return;
        }

        const filtered = allPatients.filter(p => 
            p.full_name.toLowerCase().includes(query) || 
            p.patient_id.toLowerCase().includes(query) ||
            (p.phone && p.phone.toLowerCase().includes(query))
        ).slice(0, 5); // top 5

        if (filtered.length > 0) {
            resultsDiv.innerHTML = filtered.map(p => `
                <div class="patient-result-item" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); cursor: pointer;" onclick="selectPatient(${p.id}, '${p.full_name}', '${p.patient_id}', '${p.phone || ''}')">
                    <div style="font-weight: 600;">${p.full_name}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">${p.patient_id} | ${p.phone || 'No phone'}</div>
                </div>
            `).join('');
            resultsDiv.style.display = 'block';
        } else {
            resultsDiv.innerHTML = `<div style="padding: 0.75rem 1rem; color: var(--text-muted); font-size: 0.875rem;">No patient found.</div>`;
            resultsDiv.style.display = 'block';
        }
    });

    window.selectPatient = function(id, name, pIdStr, phone) {
        selectedPatientId = id;
        selectedPatientName = name;
        
        document.getElementById('patient-results').style.display = 'none';
        document.getElementById('patient-search').value = '';
        document.getElementById('patient-search').parentElement.parentElement.style.display = 'none';
        
        document.getElementById('sp-name').textContent = name;
        document.getElementById('sp-id').textContent = `ID: ${pIdStr}`;
        document.getElementById('sp-phone').textContent = `Phone: ${phone}`;
        document.getElementById('selected-patient-card').style.display = 'block';
        
        updateSummary();
    };

    window.clearPatient = function() {
        selectedPatientId = null;
        selectedPatientName = null;
        document.getElementById('selected-patient-card').style.display = 'none';
        document.getElementById('patient-search').parentElement.parentElement.style.display = 'block';
        updateSummary();
    };

    // Doctor Selection
    document.getElementById('doctor-select').addEventListener('change', function(e) {
        const scheduleId = this.value;
        const schedule = todaySchedules.find(s => s.id == scheduleId);
        
        if (!schedule) {
            selectedDoctorId = null;
            selectedDoctorName = null;
            selectedTime = null;
            document.getElementById('slots-container').innerHTML = '<div style="color: var(--text-muted); font-size: 0.875rem;">Please select a doctor to view slots.</div>';
        } else {
            selectedDoctorId = schedule.doctor_id;
            selectedDoctorName = `${schedule.doctor_name}`;
            selectedTime = null;
            generateSlots(schedule.start_time, schedule.end_time);
        }
        updateSummary();
    });

    function generateSlots(start, end) {
        const slotsDiv = document.getElementById('slots-container');
        slotsDiv.innerHTML = '';

        // Immediate Walk-in Slot
        slotsDiv.innerHTML += `
            <div class="slot-item" data-time="immediate" style="padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-weight: 500; cursor: pointer; color: var(--text-muted);" onclick="selectSlot('immediate', this)">
                Immediate / Queue
            </div>
        `;
    }

    window.selectSlot = function(timeVal, el, label) {
        // Remove active class from all
        document.querySelectorAll('.slot-item').forEach(item => {
            item.style.borderColor = 'var(--border)';
            item.style.backgroundColor = 'transparent';
            item.style.color = 'var(--text-muted)';
            item.style.fontWeight = '500';
        });

        // Add active to selected
        el.style.borderColor = 'var(--primary)';
        el.style.backgroundColor = 'rgba(37, 99, 235, 0.05)';
        el.style.color = 'var(--primary)';
        el.style.fontWeight = '600';

        if (timeVal === 'immediate') {
            const now = new Date();
            selectedTime = now.toTimeString().substring(0, 5);
        } else {
            selectedTime = timeVal;
        }

        updateSummary();
    };

    function updateSummary() {
        document.getElementById('summary-patient').textContent = selectedPatientName || 'Not Selected';
        document.getElementById('summary-doctor').textContent = selectedDoctorName || 'Not Selected';
        
        let tLabel = 'Not Selected';
        if (selectedTime) {
            const d = new Date(`2000-01-01T${selectedTime}`);
            tLabel = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }
        document.getElementById('summary-time').textContent = tLabel;

        const btn = document.getElementById('btn-confirm-walkin');
        if (selectedPatientId && selectedDoctorId && selectedTime) {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }

    function showAlert(msg, type = 'success') {
        const alertDiv = document.getElementById('walkin-alert');
        if (type === 'success') {
            alertDiv.style.cssText = 'display:block; background:rgba(34,197,94,0.12); border:1px solid #16a34a; color:#15803d; border-radius:8px; padding:0.9rem 1.25rem; margin-bottom:1.5rem; font-weight:500;';
            alertDiv.innerHTML = `<i class="fa-solid fa-circle-check" style="font-size:1.1rem; margin-right:0.5rem;"></i> ${msg}`;
        } else {
            alertDiv.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.9rem 1.25rem; margin-bottom:1.5rem; font-weight:500;';
            alertDiv.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="font-size:1.1rem; margin-right:0.5rem;"></i> ${msg}`;
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Submit Booking
    document.getElementById('btn-confirm-walkin').addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Booking...';

        try {
            const res = await fetch('/api/reception/book-walkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    patient_id: selectedPatientId,
                    doctor_id: selectedDoctorId,
                    appointment_date: selectedDate,
                    appointment_time: selectedTime
                })
            });

            const data = await res.json();
            
            if (res.ok && data.status === 'success') {
                showAlert(`Walk-in booked successfully! Token Number: ${data.appointment.token_number}`, 'success');
                // Reset form after 3 seconds
                setTimeout(() => window.location.reload(), 3000);
            } else {
                showAlert(data.message || 'Failed to book walk-in appointment.', 'error');
                this.disabled = false;
                this.innerHTML = 'Confirm Walk-in Booking';
            }
        } catch (err) {
            console.error(err);
            showAlert('Network error. Please try again.', 'error');
            this.disabled = false;
            this.innerHTML = 'Confirm Walk-in Booking';
        }
    });

    // Hover styles for search results and slots using injected CSS
    const style = document.createElement('style');
    style.innerHTML = `
        .patient-result-item:hover { background: rgba(37, 99, 235, 0.05); }
        .slot-item:hover { border-color: var(--primary) !important; color: var(--primary) !important; }
    `;
    document.head.appendChild(style);

    initWalkin();
</script>
@endpush
