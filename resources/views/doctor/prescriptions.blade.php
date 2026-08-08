@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Prescriptions & Medications</span>
        <a href="{{ route('doctor.consultation', ['appointment_id' => $appointment->id]) }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Consultation</a>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Add New Prescription</h3>
        
        <form action="{{ route('doctor.prescriptions.store') }}" method="POST">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Medicine Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="medicine" class="form-control" placeholder="e.g. Amoxicillin" required>
                </div>
                <div class="form-group">
                    <label>Dosage</label>
                    <input type="text" name="dosage" class="form-control" placeholder="e.g. 500mg">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control" onchange="handleCustomSelect(this, 'type_custom')">
                        <option value="">Select Type...</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Injection">Injection</option>
                        <option value="Cream">Cream</option>
                        <option value="Ointment">Ointment</option>
                        <option value="Inhaler">Inhaler</option>
                        <option value="Custom">Custom...</option>
                    </select>
                    <input type="text" id="type_custom" data-name="type" class="form-control" placeholder="Type custom..." style="display: none; margin-top: 0.5rem;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Frequency</label>
                    <select name="frequency" class="form-control" onchange="handleCustomSelect(this, 'frequency_custom')">
                        <option value="">Select Frequency...</option>
                        <option value="Twice a day">Twice a day</option>
                        <option value="Three times a day">Three times a day</option>
                        <option value="Once daily">Once daily</option>
                        <option value="As needed">As needed</option>
                        <option value="Custom">Custom...</option>
                    </select>
                    <input type="text" id="frequency_custom" data-name="frequency" class="form-control" placeholder="Type custom frequency..." style="display: none; margin-top: 0.5rem;">
                </div>
                <div class="form-group">
                    <label>Duration</label>
                    <input type="text" name="duration" class="form-control" placeholder="e.g. 5 Days">
                </div>
                <div class="form-group">
                    <label>Instructions</label>
                    <select name="instructions" class="form-control" onchange="handleCustomSelect(this, 'instructions_custom')">
                        <option value="">Select Instructions...</option>
                        <option value="After Meals">After Meals</option>
                        <option value="Before Meals">Before Meals</option>
                        <option value="With Food">With Food</option>
                        <option value="Custom">Custom...</option>
                    </select>
                    <input type="text" id="instructions_custom" data-name="instructions" class="form-control" placeholder="Type custom instructions..." style="display: none; margin-top: 0.5rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Additional Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Any specific instructions for the pharmacist or patient..."></textarea>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Add to Prescription List</button>
            </div>
        </form>

        <script>
            function handleCustomSelect(selectEl, inputId) {
                var inputEl = document.getElementById(inputId);
                var originalName = inputEl.getAttribute('data-name');
                
                if (selectEl.value === 'Custom') {
                    inputEl.style.display = 'block';
                    inputEl.name = originalName;
                    inputEl.required = true;
                    selectEl.name = '';
                } else {
                    inputEl.style.display = 'none';
                    inputEl.name = '';
                    inputEl.required = false;
                    selectEl.name = originalName;
                }
            }
        </script>
    </div>

    <!-- Current Prescription List -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Current Medications for {{ $appointment->patient->user->name ?? 'Patient' }}</h3>
            <button class="btn btn-sm btn-outline"><i class="fa-solid fa-print"></i> Print Prescription</button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Type & Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Instructions</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointment->prescriptions as $prescription)
                    <tr>
                        <td style="font-weight: 600;">{{ $prescription->medicine }}</td>
                        <td>{{ $prescription->type ?? '-' }} - {{ $prescription->dosage ?? '-' }}</td>
                        <td>{{ $prescription->frequency ?? '-' }}</td>
                        <td>{{ $prescription->duration ?? '-' }}</td>
                        <td>{{ $prescription->instructions ?? '-' }}</td>
                        <td>
                            <form action="{{ route('doctor.prescriptions.destroy', $prescription->id) }}" method="POST" onsubmit="return confirm('Remove this medication?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" style="color: var(--danger);"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 1rem;">No medications added yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
            <a href="{{ route('doctor.consultation', ['appointment_id' => $appointment->id]) }}" class="btn btn-secondary"><i class="fa-solid fa-check-double"></i> Finalize Prescription</a>
        </div>
    </div>
@endsection
