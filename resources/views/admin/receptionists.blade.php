@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Manage Receptionists</span>
        <button class="btn" data-modal="addReceptionistModal"><i class="fa-solid fa-plus"></i> Add Receptionist</button>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search receptionists...">
            </div>
            <div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Receptionist ID</th>
                        <th>Receptionist Name</th>
                        <th>Contact Number</th>
                        <th>Email Address</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receptionists as $receptionist)
                    <tr>
                        <td>{{ $receptionist->receptionist_id ?? '—' }}</td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-dark);">{{ $receptionist->user->name }}</div>
                        </td>
                        <td>{{ $receptionist->phone }}</td>
                        <td>{{ $receptionist->user->email }}</td>
                        <td>{{ $receptionist->shift }}</td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-user-id="{{ $receptionist->user->id }}" {{ $receptionist->user->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <button class="btn-icon edit-receptionist-btn" 
                                    data-modal="editReceptionistModal"
                                    data-id="{{ $receptionist->id }}"
                                    data-receptionist-id="{{ $receptionist->receptionist_id }}"
                                    data-name="{{ $receptionist->user->name }}"
                                    data-email="{{ $receptionist->user->email }}"
                                    data-phone="{{ $receptionist->phone }}"
                                    data-shift="{{ $receptionist->shift }}"
                                    title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <form action="{{ route('admin.receptionists.destroy', $receptionist) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this receptionist?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" style="color: var(--danger);" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Receptionist Modal -->
    <div class="modal-overlay" id="addReceptionistModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Receptionist</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.receptionists.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="receptionist@careplus.com" value="" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+94 77 XXX XXXX" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Preferred Shift</label>
                        <select name="shift" class="form-control" required>
                            <option value="" disabled selected>Select Shift</option>
                            <option>Morning (08:00 AM - 03:00 PM)</option>
                            <option>Evening (02:00 PM - 09:00 PM)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create temporary password" value="" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Save Receptionist</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Receptionist Modal -->
    <div class="modal-overlay" id="editReceptionistModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Receptionist</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editReceptionistForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Receptionist ID <span style="font-size: 0.75rem; color: var(--text-muted);">(auto-generated, read-only)</span></label>
                        <input type="text" id="edit-rec-receptionist-id" class="form-control" readonly style="background: var(--bg-light, #f8fafc); cursor: not-allowed; color: var(--text-muted);">
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="edit-rec-name" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" id="edit-rec-email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" id="edit-rec-phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Preferred Shift</label>
                        <select name="shift" id="edit-rec-shift" class="form-control" required>
                            <option value="" disabled>Select Shift</option>
                            <option>Morning (08:00 AM - 03:00 PM)</option>
                            <option>Evening (02:00 PM - 09:00 PM)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Update Receptionist</button>
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
        const modals = document.querySelectorAll('.modal-overlay');
        const editBtns = document.querySelectorAll('.edit-receptionist-btn');
        const editForm = document.getElementById('editReceptionistForm');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                document.getElementById('edit-rec-receptionist-id').value = this.getAttribute('data-receptionist-id') || '—';
                document.getElementById('edit-rec-name').value             = this.getAttribute('data-name');
                document.getElementById('edit-rec-email').value            = this.getAttribute('data-email');
                document.getElementById('edit-rec-phone').value            = this.getAttribute('data-phone');
                document.getElementById('edit-rec-shift').value            = this.getAttribute('data-shift');
                
                editForm.action = `/admin/receptionists/${id}`;
            });
        });
    });
</script>
@endpush
