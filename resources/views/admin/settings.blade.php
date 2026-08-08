@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>System Settings</span>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
        <!-- Settings Content -->
        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--text-dark); border-bottom: 1px solid var(--border); padding-bottom: 1rem;">Clinic Profile</h3>
            
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-alt); display: flex; align-items: center; justify-content: center; border: 2px dashed var(--border); position: relative; cursor: pointer;">
                        <i class="fa-solid fa-camera" style="font-size: 1.5rem; color: var(--text-muted);"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Clinic Logo</h4>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">Upload a high-resolution logo in PNG or JPG format (max 2MB).</p>
                        <button type="button" class="btn btn-outline btn-sm">Choose File</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label>Clinic Name</label>
                        <input type="text" name="clinic_name" class="form-control" value="{{ old('clinic_name', $settings['clinic_name'] ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $settings['registration_number'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Primary Email</label>
                        <input type="email" name="primary_email" class="form-control" value="{{ old('primary_email', $settings['primary_email'] ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="tel" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3" required>{{ old('address', $settings['address'] ?? '') }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                    <button type="submit" class="btn">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
@endsection
