@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Clinic Services</span>
        <button class="btn" data-modal="addServiceModal"><i class="fa-solid fa-plus"></i> Add Service</button>
    </div>

    <!-- Services Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        @foreach($services as $service)
        <div class="card" style="display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div class="stat-icon blue" style="margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn-icon edit-service-btn" 
                            data-modal="editServiceModal" 
                            data-id="{{ $service->id }}"
                            data-name="{{ $service->name }}"
                            data-fee="{{ $service->fee }}"
                            data-description="{{ $service->description }}"
                            title="Edit Service"><i class="fa-solid fa-pen"></i></button>
                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service?');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon" style="color: var(--danger);" title="Delete Service"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $service->name }}</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem; flex-grow: 1;">{{ $service->description }}</p>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1rem;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Consultation Fee</span>
                <span style="font-size: 1.125rem; font-weight: 700; color: var(--primary);">LKR {{ number_format($service->fee, 2) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Service Modal -->
    <div class="modal-overlay" id="addServiceModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Service</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Service Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Vaccination" required>
                    </div>
                    <div class="form-group">
                        <label>Consultation Fee (LKR)</label>
                        <input type="number" step="0.01" name="fee" class="form-control" placeholder="e.g. 1500" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief description of the service..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal-overlay" id="editServiceModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Service</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editServiceForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Service Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Consultation Fee (LKR)</label>
                        <input type="number" step="0.01" name="fee" id="edit-fee" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="edit-description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Update Service</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editBtns = document.querySelectorAll('.edit-service-btn');
        const editForm = document.getElementById('editServiceForm');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const fee = this.getAttribute('data-fee');
                const desc = this.getAttribute('data-description');
                
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-fee').value = fee;
                document.getElementById('edit-description').value = desc;
                
                editForm.action = `/admin/services/${id}`;
            });
        });
    });
</script>
@endpush
