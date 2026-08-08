@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Manage Patients</span>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 350px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search by name, NIC, or phone...">
            </div>
            <div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Contact Number</th>
                        <th>Gender</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td style="font-weight: 500;">PT-{{ str_pad($patient->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-dark);">{{ $patient->user->name }}</div>
                        </td>
                        <td>{{ $patient->phone ?? 'N/A' }}</td>
                        <td>{{ $patient->gender ?? 'N/A' }}</td>
                        <td>{{ $patient->created_at ? $patient->created_at->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-user-id="{{ $patient->user->id }}" {{ $patient->user->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <button class="btn-icon" title="View Details" onclick="viewPatient({{ $patient->id }})"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-icon" title="Edit Patient" onclick="editPatient({{ $patient->id }})"><i class="fa-solid fa-pen"></i></button>
                            <form action="{{ route('admin.patients.destroy', $patient) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this patient? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Delete Patient" style="color: var(--danger);"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            
                            <!-- Hidden data to be used by JS -->
                            <div id="patient-data-{{ $patient->id }}" style="display: none;" 
                                data-patient="{{ json_encode([
                                    'name' => $patient->user->name,
                                    'email' => $patient->user->email,
                                    'phone' => $patient->phone,
                                    'gender' => $patient->gender,
                                    'dob' => $patient->date_of_birth,
                                    'address' => $patient->address,
                                    'medical' => $patient->medical_history,
                                    'registered' => $patient->created_at ? $patient->created_at->format('M d, Y h:i A') : 'N/A',
                                    'id' => $patient->id
                                ]) }}">
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No patients registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Patient Details</h2>
                <span class="modal-close" onclick="closeModal('viewModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label class="form-label">Full Name</label>
                        <div id="view_name" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <div id="view_email" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                    <div>
                        <label class="form-label">Phone Number</label>
                        <div id="view_phone" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                    <div>
                        <label class="form-label">Gender</label>
                        <div id="view_gender" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                    <div>
                        <label class="form-label">Date of Birth</label>
                        <div id="view_dob" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                    <div>
                        <label class="form-label">Registered Date</label>
                        <div id="view_registered" style="font-weight: 500; color: var(--text-dark);"></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label">Address</label>
                    <div id="view_address" style="font-weight: 500; color: var(--text-dark); background: var(--bg-alt); padding: 1rem; border-radius: 6px;"></div>
                </div>

                <div>
                    <label class="form-label">Medical History</label>
                    <div id="view_medical" style="font-weight: 500; color: var(--text-dark); background: rgba(239, 68, 68, 0.05); padding: 1rem; border-radius: 6px; border: 1px solid rgba(239, 68, 68, 0.2);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Edit Patient Profile</h2>
                <span class="modal-close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select name="gender" id="edit_gender" class="form-control">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="edit_dob" class="form-control">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Medical History</label>
                        <textarea name="medical_history" id="edit_medical" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('show');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    function viewPatient(id) {
        let rawData = document.getElementById('patient-data-' + id).dataset.patient;
        let data = JSON.parse(rawData);
        
        document.getElementById('view_name').innerText = data.name;
        document.getElementById('view_email').innerText = data.email || 'N/A';
        document.getElementById('view_phone').innerText = data.phone || 'N/A';
        document.getElementById('view_gender').innerText = data.gender || 'N/A';
        document.getElementById('view_dob').innerText = data.dob || 'N/A';
        document.getElementById('view_registered').innerText = data.registered;
        document.getElementById('view_address').innerText = data.address || 'No address provided.';
        document.getElementById('view_medical').innerText = data.medical || 'No medical history recorded.';

        openModal('viewModal');
    }

    function editPatient(id) {
        let rawData = document.getElementById('patient-data-' + id).dataset.patient;
        let data = JSON.parse(rawData);
        
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_phone').value = data.phone;
        document.getElementById('edit_gender').value = data.gender;
        document.getElementById('edit_dob').value = data.dob;
        document.getElementById('edit_address').value = data.address;
        document.getElementById('edit_medical').value = data.medical;
        
        // Set form action dynamically
        document.getElementById('editForm').action = '/admin/patients/' + id;

        openModal('editModal');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('show');
        }
    }

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
    });
</script>
@endpush
