@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Register New Patient</span>
        <a href="{{ route('receptionist.patients') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>
    </div>

    <div class="card">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Patient Information</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Please fill in all mandatory fields to register a new patient in the system.</p>
        </div>

        <!-- Alert -->
        <div id="reg-alert" style="display:none; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;"></div>

        <form id="register-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Full Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="full_name" id="reg-full-name" class="form-control" placeholder="Enter patient's full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address <span style="color: var(--danger);">*</span></label>
                    <input type="email" name="email" id="reg-email" class="form-control" placeholder="Used for patient portal login" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" id="reg-dob" class="form-control">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" id="reg-gender" class="form-control">
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number <span style="color: var(--danger);">*</span></label>
                    <input type="tel" name="phone" id="reg-phone" class="form-control" placeholder="e.g. 077 123 4567" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Residential Address</label>
                <textarea name="address" id="reg-address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 1.5rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Medical Notes (Optional)</h4>
                <div class="form-group">
                    <label>Known Allergies / Medical Notes</label>
                    <textarea name="medical_notes" id="reg-medical-notes" class="form-control" rows="3" placeholder="List any known allergies or important medical history..."></textarea>
                </div>
            </div>

            <div style="background: rgba(37,99,235,0.05); border: 1px solid rgba(37,99,235,0.15); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 0.4rem;"></i>
                The patient's default login password will be set to their <strong>contact number</strong>. They can change it after logging in.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('register-form').reset();">Clear Form</button>
                <button type="submit" class="btn" id="reg-submit-btn">Complete Registration</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('register-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const token = localStorage.getItem('token');
        if (!token) { window.location.href = '/login'; return; }

        const submitBtn = document.getElementById('reg-submit-btn');
        const alert     = document.getElementById('reg-alert');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registering...';

        const payload = {
            full_name:     document.getElementById('reg-full-name').value,
            email:         document.getElementById('reg-email').value,
            dob:           document.getElementById('reg-dob').value,
            gender:        document.getElementById('reg-gender').value,
            phone:         document.getElementById('reg-phone').value,
            address:       document.getElementById('reg-address').value,
            medical_notes: document.getElementById('reg-medical-notes').value,
        };

        try {
            const res = await fetch('/api/reception/patients', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                // Redirect to patients list with success flag
                window.location.href = '{{ route("receptionist.patients") }}?registered=1';
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Registration failed.');
                alert.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;';
                alert.textContent = msg;
            }
        } catch (err) {
            alert.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;';
            alert.textContent = 'Network error. Please try again.';
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Complete Registration';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
@endpush
