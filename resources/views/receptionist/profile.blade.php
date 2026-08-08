@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>My Profile</span>
    </div>

    <div id="profile-alert" style="display:none; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.5rem; font-weight:500;"></div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        
        <!-- Profile Sidebar -->
        <div class="card" style="text-align: center;">
            <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
                <img id="profile-avatar" src="https://ui-avatars.com/api/?name=Receptionist&background=2563EB&color=fff&size=120" alt="Profile" style="border-radius: 50%; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <button class="btn-icon" style="position: absolute; bottom: 0; right: 0; background: var(--bg-white); border: 1px solid var(--border); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"><i class="fa-solid fa-camera"></i></button>
            </div>
            <h3 id="display-name" style="font-size: 1.25rem; font-weight: 600;">Loading...</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">Role: Receptionist</p>
            
            <div style="text-align: left; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Employee ID</span>
                    <span id="display-emp-id" style="font-size: 0.875rem; font-weight: 500;">Loading...</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Account Status</span>
                    <span class="badge active" style="margin-top: 0.25rem; display: inline-block; background: #D1FAE5; color: #065F46;">Active</span>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">Personal Information</h3>
            
            <form id="profile-form">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Full name</label>
                        <input type="text" id="input-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="input-email" class="form-control" disabled style="background-color: var(--bg-main);">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="input-phone" class="form-control" placeholder="Optional">
                    </div>
                </div>

                <h3 style="font-size: 1.125rem; font-weight: 600; margin-top: 2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">Change Password</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" id="input-password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="input-password-confirmation" class="form-control" placeholder="Confirm password">
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn" id="btn-save-profile">Save Changes</button>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const token = localStorage.getItem('token');

    async function loadProfile() {
        if (!token) { window.location.href = '/login'; return; }

        try {
            const res = await fetch('/api/reception/my-profile', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            if (data.status === 'success') {
                const user = data.user;
                const rec = data.receptionist;

                document.getElementById('display-name').textContent = user.name;
                document.getElementById('profile-avatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=2563EB&color=fff&size=120`;
                
                let empId = 'N/A';
                if (rec) {
                    empId = 'REC-' + String(rec.id).padStart(3, '0');
                }
                document.getElementById('display-emp-id').textContent = empId;

                document.getElementById('input-name').value = user.name;
                document.getElementById('input-email').value = user.email;
                document.getElementById('input-phone').value = rec ? rec.phone : '';
            }
        } catch (err) {
            console.error(err);
            showAlert('Failed to load profile data.', 'error');
        }
    }

    function showAlert(msg, type = 'success') {
        const alertDiv = document.getElementById('profile-alert');
        if (type === 'success') {
            alertDiv.style.cssText = 'display:block; background:rgba(34,197,94,0.12); border:1px solid #16a34a; color:#15803d; border-radius:8px; padding:0.9rem 1.25rem; margin-bottom:1.5rem; font-weight:500;';
            alertDiv.innerHTML = `<i class="fa-solid fa-circle-check" style="font-size:1.1rem; margin-right:0.5rem;"></i> ${msg}`;
        } else {
            alertDiv.style.cssText = 'display:block; background:rgba(239,68,68,0.1); border:1px solid #dc2626; color:#dc2626; border-radius:8px; padding:0.9rem 1.25rem; margin-bottom:1.5rem; font-weight:500;';
            alertDiv.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="font-size:1.1rem; margin-right:0.5rem;"></i> ${msg}`;
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-profile');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        const nameVal = document.getElementById('input-name').value;
        const payload = {
            name: nameVal,
            full_name: nameVal,
            phone: document.getElementById('input-phone').value,
        };

        const pwd = document.getElementById('input-password').value;
        const pwdConf = document.getElementById('input-password-confirmation').value;

        if (pwd) {
            payload.password = pwd;
            payload.password_confirmation = pwdConf;
        }

        try {
            const res = await fetch('/api/reception/update-profile', {
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
                showAlert(data.message, 'success');
                // Clear password fields
                document.getElementById('input-password').value = '';
                document.getElementById('input-password-confirmation').value = '';
                // Reload profile data to update sidebar
                loadProfile();
            } else {
                let errorMsg = data.message || 'Failed to update profile.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                showAlert(errorMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showAlert('A network error occurred. Please try again.', 'error');
        }

        btn.disabled = false;
        btn.innerHTML = 'Save Changes';
    });

    loadProfile();
</script>
@endpush
