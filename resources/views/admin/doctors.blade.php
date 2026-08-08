@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Manage Doctors</span>
        <button class="btn" data-modal="addDoctorModal"><i class="fa-solid fa-plus"></i> Add Doctor</button>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search doctors...">
            </div>
            <div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Doctor ID</th>
                        <th>Doctor Info</th>
                        <th>Specialization</th>
                        <th>Contact Number</th>
                        <th>Email Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($doctors as $doctor)
                    <tr>
                        <td>{{ $doctor->doctor_id ?? '—' }}</td>
                        <td>
                            <div>
                                <div style="font-weight: 600; color: var(--text-dark);">{{ $doctor->user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $doctor->qualifications }}</div>
                            </div>
                        </td>
                        <td>{{ $doctor->specialization }}</td>
                        <td>{{ $doctor->phone }}</td>
                        <td>{{ $doctor->user->email }}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-user-id="{{ $doctor->user->id }}" {{ $doctor->user->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <button class="btn-icon view-doctor-btn" 
                                    data-modal="viewDoctorModal" 
                                    data-id="{{ $doctor->id }}"
                                    data-doctor-id="{{ $doctor->doctor_id }}"
                                    data-name="{{ $doctor->user->name }}"
                                    data-email="{{ $doctor->user->email }}"
                                    data-phone="{{ $doctor->phone }}"
                                    data-specialization="{{ $doctor->specialization }}"
                                    data-service-name="{{ $doctor->service->name ?? 'N/A' }}"
                                    data-qualifications="{{ $doctor->qualifications }}"
                                    title="View"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-icon edit-doctor-btn" 
                                    data-modal="editDoctorModal" 
                                    data-id="{{ $doctor->id }}"
                                    data-doctor-id="{{ $doctor->doctor_id }}"
                                    data-name="{{ $doctor->user->name }}"
                                    data-email="{{ $doctor->user->email }}"
                                    data-phone="{{ $doctor->phone }}"
                                    data-specialization="{{ $doctor->specialization }}"
                                    data-service-id="{{ $doctor->service_id }}"
                                    data-qualifications="{{ $doctor->qualifications }}"
                                    title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this doctor?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" style="color: var(--danger);" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination Placeholder -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.875rem;">
                <span>Showing {{ $doctors->count() }} entries</span>
            </div>
        </div>
    </div>

    <!-- Add Doctor Modal -->
    <div class="modal-overlay" id="addDoctorModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Doctor</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.doctors.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Dr. Full Name" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="doctor@careplus.com" value="" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+94 77 XXX XXXX" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Specialization</label>
                            <input type="text" name="specialization" class="form-control" placeholder="E.g., General Physician" required>
                        </div>
                        <div class="form-group">
                            <label>Clinic Service</label>
                            <select name="service_id" class="form-control" required>
                                <option value="" disabled selected>Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Qualifications</label>
                            <input type="text" name="qualifications" class="form-control" placeholder="MBBS, MD" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create temporary password" value="" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Save Doctor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Doctor Modal -->
    <div class="modal-overlay" id="viewDoctorModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Doctor Details</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem; gap: 1rem;">
                    <div>
                        <h4 id="view-doc-name" style="margin-bottom: 0.25rem;"></h4>
                        <span id="view-doc-id" style="display: inline-block; background: var(--primary-light, #ede9fe); color: var(--primary, #7c3aed); font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 6px; letter-spacing: 0.05em;"></span>
                        <span class="badge active" style="margin-left: 0.5rem;">Available</span>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Email Address</label>
                        <div id="view-doc-email" style="font-weight: 500;"></div>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--text-muted);">Phone Number</label>
                        <div id="view-doc-phone" style="font-weight: 500;"></div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Specialization</p>
                        <p id="view-doc-specialization" style="font-weight: 500;"></p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Clinic Service</p>
                        <p id="view-doc-service-name" style="font-weight: 500;"></p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Qualifications</p>
                        <p id="view-doc-qualifications" style="font-weight: 500;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-cancel">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div class="modal-overlay" id="editDoctorModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Doctor</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editDoctorForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Doctor ID <span style="font-size: 0.75rem; color: var(--text-muted);">(auto-generated, read-only)</span></label>
                        <input type="text" id="edit-doc-doctor-id" class="form-control" readonly style="background: var(--bg-light, #f8fafc); cursor: not-allowed; color: var(--text-muted);">
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="edit-doc-name" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" id="edit-doc-email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" id="edit-doc-phone" class="form-control" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Specialization</label>
                            <input type="text" name="specialization" id="edit-doc-specialization" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Clinic Service</label>
                            <select name="service_id" id="edit-doc-service" class="form-control" required>
                                <option value="" disabled>Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Qualifications</label>
                            <input type="text" name="qualifications" id="edit-doc-qualifications" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Update Doctor</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleCheckboxes = document.querySelectorAll('.toggle-status');
        
        toggleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', async (e) => {
                const userId = e.target.dataset.userId;
                
                try {
                    const response = await fetch(`/admin/users/${userId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    if (data.status !== 'success') {
                        throw new Error('Failed to update status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    e.target.checked = !e.target.checked;
                    alert('An error occurred while updating the status.');
                }
            });
        });

        // Modals
        const viewBtns = document.querySelectorAll('.view-doctor-btn');
        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const doctorId       = this.getAttribute('data-doctor-id');
                const name           = this.getAttribute('data-name');
                const email          = this.getAttribute('data-email');
                const phone          = this.getAttribute('data-phone');
                const specialization = this.getAttribute('data-specialization');
                const serviceName    = this.getAttribute('data-service-name');
                const qualifications = this.getAttribute('data-qualifications');
                
                document.getElementById('view-doc-id').textContent             = doctorId || '—';
                document.getElementById('view-doc-name').textContent           = name;
                document.getElementById('view-doc-email').textContent          = email;
                document.getElementById('view-doc-phone').textContent          = phone;
                document.getElementById('view-doc-specialization').textContent = specialization;
                document.getElementById('view-doc-service-name').textContent   = serviceName;
                document.getElementById('view-doc-qualifications').textContent = qualifications;
                document.getElementById('view-doc-avatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random`;
            });
        });

        const editBtns = document.querySelectorAll('.edit-doctor-btn');
        const editForm = document.getElementById('editDoctorForm');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                document.getElementById('edit-doc-doctor-id').value         = this.getAttribute('data-doctor-id') || '—';
                document.getElementById('edit-doc-name').value              = this.getAttribute('data-name');
                document.getElementById('edit-doc-email').value             = this.getAttribute('data-email');
                document.getElementById('edit-doc-phone').value             = this.getAttribute('data-phone');
                document.getElementById('edit-doc-specialization').value    = this.getAttribute('data-specialization');
                document.getElementById('edit-doc-service').value           = this.getAttribute('data-service-id');
                document.getElementById('edit-doc-qualifications').value    = this.getAttribute('data-qualifications');
                
                editForm.action = `/admin/doctors/${id}`;
            });
        });
    });
</script>
@endpush
