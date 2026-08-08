@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Doctor Profile & Settings</span>
    </div>

    <!-- Loading state -->
    <div id="profile-loading" style="text-align: center; padding: 4rem; color: var(--text-muted);">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
        <div>Loading your profile...</div>
    </div>

    <div id="profile-content" style="display:none; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Profile Sidebar -->
        <div class="card" style="text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                <img id="profile-avatar" src="" alt="Doctor Profile" style="border-radius: 50%; width:120px; height:120px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <button class="btn-icon" style="position: absolute; bottom: 0; right: 0; background: var(--bg-white); border: 1px solid var(--border); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><i class="fa-solid fa-camera"></i></button>
            </div>
            <h3 id="profile-name-display" style="font-size: 1.25rem; font-weight: 600;"></h3>
            <p id="profile-specialization-display" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;"></p>
            
            <div style="text-align: left; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Employee ID</span>
                    <span id="profile-employee-id" style="font-size: 0.875rem; font-weight: 500;"></span>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="card">
            <!-- Success / Error alert -->
            <div id="profile-alert" style="display:none; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;"></div>

            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">Professional Information</h3>
            
            <form id="profile-form">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Full Name (with Title)</label>
                        <input type="text" name="name" id="pf-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="pf-email" class="form-control" disabled style="background-color: var(--bg-main);">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" id="pf-phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" id="pf-specialization" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label>Qualifications</label>
                    <input type="text" name="qualifications" id="pf-qualifications" class="form-control">
                </div>

                <h3 style="font-size: 1.125rem; font-weight: 600; margin-top: 2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">Security</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" id="pf-password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="pf-password-confirm" class="form-control" placeholder="Confirm password">
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn" id="pf-save-btn">Save Changes</button>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
(async function loadProfile() {
    const token = localStorage.getItem('token');
    if (!token) { window.location.href = '/login'; return; }

    try {
        const res = await fetch('/api/doctor/my-profile', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (res.status === 401) { window.location.href = '/login'; return; }

        const data = await res.json();
        if (data.status !== 'success') { return; }

        const user   = data.user;
        const doctor = data.doctor || {};

        // Sidebar
        document.getElementById('profile-avatar').src = 
            `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=2563EB&color=fff&size=120`;
        document.getElementById('profile-name-display').textContent = user.name;
        document.getElementById('profile-specialization-display').textContent = doctor.specialization || 'Not Specified';
        document.getElementById('profile-employee-id').textContent  = doctor.doctor_id || 'N/A';

        // Form fields
        document.getElementById('pf-name').value           = user.name || '';
        document.getElementById('pf-email').value          = user.email || '';
        document.getElementById('pf-phone').value          = doctor.phone || '';
        document.getElementById('pf-specialization').value = doctor.specialization || '';
        document.getElementById('pf-qualifications').value = doctor.qualifications || '';

        // Show profile
        document.getElementById('profile-loading').style.display = 'none';
        const content = document.getElementById('profile-content');
        content.style.display = 'grid';

    } catch (err) {
        console.error('Failed to load profile:', err);
        document.getElementById('profile-loading').innerHTML = 
            '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--danger); margin-bottom:1rem;"></i><div>Failed to load profile. Please refresh.</div>';
    }
})();

// Handle profile form submit
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const token = localStorage.getItem('token');
    if (!token) { window.location.href = '/login'; return; }

    const saveBtn = document.getElementById('pf-save-btn');
    const alert   = document.getElementById('profile-alert');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const payload = {
        name:                  document.getElementById('pf-name').value,
        phone:                 document.getElementById('pf-phone').value,
        specialization:        document.getElementById('pf-specialization').value,
        qualifications:        document.getElementById('pf-qualifications').value,
        password:              document.getElementById('pf-password').value,
        password_confirmation: document.getElementById('pf-password-confirm').value,
    };

    try {
        const res = await fetch('/api/doctor/update-profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (res.ok && data.status === 'success') {
            alert.style.cssText = 'display:block; background:rgba(34,197,94,0.12); border:1px solid #16a34a; color:#15803d; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;';
            alert.textContent = 'Profile updated successfully!';
            
            // Update sidebar + header
            const newName = payload.name;
            const newSpec = payload.specialization;
            document.getElementById('profile-name-display').textContent = newName;
            document.getElementById('profile-specialization-display').textContent = newSpec || 'Not Specified';
            document.getElementById('profile-avatar').src = 
                `https://ui-avatars.com/api/?name=${encodeURIComponent(newName)}&background=2563EB&color=fff&size=120`;
            const hdr = document.getElementById('doctor-name-header');
            if (hdr) hdr.textContent = newName;

            // Clear password fields
            document.getElementById('pf-password').value = '';
            document.getElementById('pf-password-confirm').value = '';
        } else {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Update failed.');
            alert.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;';
            alert.textContent = msg;
        }
    } catch (err) {
        alert.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;';
        alert.textContent = 'Network error. Please try again.';
    }

    saveBtn.disabled = false;
    saveBtn.textContent = 'Save Changes';
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
@endpush
