@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Manage Appointments</span>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <form action="{{ route('admin.appointments') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Search appointments..." value="{{ request('search') }}">
            </div>
            <div style="display: flex; gap: 1rem;">
                <select name="doctor_id" class="form-control" style="width: auto; padding: 0.25rem 0.5rem; height: auto;">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                             {{ $doctor->user->name ?? 'Unknown' }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width: auto; padding: 0.25rem 0.5rem; height: auto;">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="date" class="form-control" style="width: auto; padding: 0.25rem 0.5rem; height: auto;" value="{{ request('date') }}">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Apt. No</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Date & Time</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    <tr>
                        <td style="font-weight: 600;">APT-{{ str_pad($appointment->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-dark);">{{ $appointment->patient->user->name ?? 'Unknown Patient' }}</div>
                        </td>
                        <td>{{ $appointment->doctor->user->name ?? 'Unknown Doctor' }} <br><small style="color: var(--text-muted);">{{ $appointment->doctor->specialization ?? '' }}</small></td>
                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }} <br><small style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}</small></td>
                        <td>
                            <div style="font-weight: 700; font-size: 1.125rem; color: var(--primary);">
                                #{{ $appointment->token_number ?? '-' }}
                            </div>
                        </td>
                        <td>
                            @if($appointment->status === 'scheduled')
                                <span class="badge active" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">Scheduled</span>
                            @elseif($appointment->status === 'completed')
                                <span class="badge active" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">Completed</span>
                            @elseif($appointment->status === 'cancelled')
                                <span class="badge active" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">Cancelled</span>
                            @else
                                <span class="badge active" style="background: var(--bg-alt); color: var(--text-muted);">{{ ucfirst($appointment->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn-icon" title="View Details" 
                                    data-modal="viewAppointmentModal"
                                    data-id="{{ $appointment->id }}"
                                    data-patient="{{ $appointment->patient->user->name ?? 'Unknown' }}"
                                    data-doctor="{{ $appointment->doctor->user->name ?? 'Unknown' }}"
                                    data-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y h:i A') }}"
                                    data-status="{{ ucfirst($appointment->status) }}"
                                    data-notes="{{ $appointment->notes }}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="btn-icon" title="Edit"
                                    data-modal="editAppointmentModal"
                                    data-id="{{ $appointment->id }}"
                                    data-doctor_id="{{ $appointment->doctor_id }}"
                                    data-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d\TH:i') }}"
                                    data-status="{{ $appointment->status }}"
                                    data-notes="{{ $appointment->notes }}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            @if($appointment->status === 'scheduled')
                                <form action="{{ route('admin.appointments.cancel', $appointment) }}" method="POST" onsubmit="return confirm('Cancel this appointment?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" style="color: var(--danger);" title="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No appointments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="modal-overlay" id="viewAppointmentModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Appointment Details</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <strong>Patient:</strong> <span id="viewPatient"></span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Doctor:</strong> <span id="viewDoctor"></span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Date & Time:</strong> <span id="viewDate"></span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Status:</strong> <span id="viewStatus"></span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Notes:</strong>
                    <p id="viewNotes" style="margin-top: 0.5rem; color: var(--text-muted);"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div class="modal-overlay" id="editAppointmentModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Appointment</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editAppointmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Doctor</label>
                        <select name="doctor_id" id="editDoctorId" class="form-control" required>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->user->name ?? 'Unknown' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date & Time</label>
                        <input type="datetime-local" name="appointment_date" id="editDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close">Cancel</button>
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Modal Logic
        const modals = document.querySelectorAll('.modal-overlay');
        const modalTriggers = document.querySelectorAll('[data-modal]');
        const closeButtons = document.querySelectorAll('.modal-close');

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        }

        function closeModal() {
            modals.forEach(m => m.classList.remove('active'));
        }

        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                const modalId = trigger.dataset.modal;
                
                if (modalId === 'viewAppointmentModal') {
                    document.getElementById('viewPatient').textContent = trigger.dataset.patient;
                    document.getElementById('viewDoctor').textContent = trigger.dataset.doctor;
                    document.getElementById('viewDate').textContent = trigger.dataset.date;
                    document.getElementById('viewStatus').textContent = trigger.dataset.status;
                    document.getElementById('viewNotes').textContent = trigger.dataset.notes || 'None';
                }
                
                if (modalId === 'editAppointmentModal') {
                    const form = document.getElementById('editAppointmentForm');
                    form.action = `/admin/appointments/${trigger.dataset.id}`;
                    
                    document.getElementById('editDoctorId').value = trigger.dataset.doctor_id;
                    document.getElementById('editDate').value = trigger.dataset.date;
                    document.getElementById('editStatus').value = trigger.dataset.status;
                    document.getElementById('editNotes').value = trigger.dataset.notes;
                }
                
                openModal(modalId);
            });
        });

        closeButtons.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        modals.forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        });
    });
</script>
@endpush
