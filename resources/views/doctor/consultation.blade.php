@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Active Consultation</span>

    </div>

    <!-- Patient Header Strip -->
    <div class="card" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--primary);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($appointment->patient->user->name ?? 'U') }}&background=2563EB&color=fff" alt="Patient" style="width: 48px; height: 48px; border-radius: 50%;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 700;">{{ $appointment->patient->user->name ?? 'Unknown Patient' }} <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-muted); margin-left: 0.5rem;">PT-{{ str_pad($appointment->patient_id, 3, '0', STR_PAD_LEFT) }}</span></h3>
                <span style="font-size: 0.875rem; color: var(--text-muted);">
                    {{ ucfirst($appointment->patient->gender ?? 'Unknown') }}, 
                    {{ $appointment->patient->dob ? \Carbon\Carbon::parse($appointment->patient->dob)->age . ' years old' : 'Age unknown' }}
                </span>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Appointment Time</div>
            <div style="font-weight: 600; color: var(--primary);">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }} (Queue #{{ $appointment->token_number ?? '--' }})</div>
        </div>
    </div>

    <form action="{{ route('doctor.consultation.store') }}" method="POST">
        @csrf
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        <input type="hidden" name="action" id="formAction" value="save">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <!-- Left Column: Clinical Notes -->
            <div>
                <!-- Vitals -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Vital Signs</h3>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label>Blood Pressure</label>
                            <input type="text" name="blood_pressure" class="form-control" placeholder="e.g. 120/80" value="{{ $appointment->consultation->blood_pressure ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Heart Rate (bpm)</label>
                            <input type="text" name="heart_rate" class="form-control" placeholder="e.g. 72" value="{{ $appointment->consultation->heart_rate ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Temperature (°C)</label>
                            <input type="text" name="temperature" class="form-control" placeholder="e.g. 37.2" value="{{ $appointment->consultation->temperature ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="text" name="weight" class="form-control" placeholder="e.g. 68" value="{{ $appointment->consultation->weight ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Consultation Notes -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Clinical Notes</h3>
                    
                    <div class="form-group">
                        <label>Presenting Symptoms</label>
                        <textarea name="presenting_symptoms" class="form-control" rows="3" placeholder="Describe the symptoms reported by the patient...">{{ $appointment->consultation->presenting_symptoms ?? '' }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Clinical Diagnosis</label>
                        <input type="text" name="clinical_diagnosis" class="form-control" placeholder="e.g. Viral Pharyngitis" value="{{ $appointment->consultation->clinical_diagnosis ?? '' }}">
                    </div>
                    
                    <div class="form-group">
                        <label>Treatment Plan & Notes</label>
                        <textarea name="treatment_plan" class="form-control" rows="4" placeholder="Detailed treatment plan, procedures performed, or general notes...">{{ $appointment->consultation->treatment_plan ?? '' }}</textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="submit" class="btn btn-outline" onclick="document.getElementById('formAction').value='save';"><i class="fa-solid fa-floppy-disk"></i> Save Draft</button>
                    <button type="submit" class="btn" onclick="document.getElementById('formAction').value='complete';"><i class="fa-solid fa-check"></i> Complete Consultation</button>
                </div>
            </div>

            <!-- Right Column: Quick Actions & History -->
            <div>
                <!-- Add Prescription -->
                <div class="card" style="margin-bottom: 1.5rem; background: rgba(37, 99, 235, 0.03); border: 1px dashed var(--primary);">
                    <div style="text-align: center; padding: 1rem;">
                        <i class="fa-solid fa-pills" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <h4 style="font-weight: 600; margin-bottom: 0.5rem;">Prescribe Medication</h4>
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">Add medicines, lab tests, or follow-up instructions.</p>
                        <a href="{{ route('doctor.prescriptions', ['appointment_id' => $appointment->id]) }}" class="btn btn-sm" style="width: 100%; justify-content: center;"><i class="fa-solid fa-plus"></i> Add Prescription</a>
                    </div>
                </div>

                <!-- History Snapshot -->
                <div class="card">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Medical History Snapshot</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <span style="font-size: 0.75rem; color: var(--primary); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 0.25rem;">Medical History</span>
                        <span style="font-size: 0.875rem; font-weight: 500;">{{ $appointment->patient->medical_history ?? 'No known history provided.' }}</span>
                    </div>

                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Past Visits</h4>
                    @forelse($pastAppointments as $past)
                    <div style="background: var(--bg-main); padding: 0.75rem; border-radius: 6px; margin-bottom: 0.5rem; font-size: 0.875rem;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $past->consultation->clinical_diagnosis ?? 'General Consultation' }}</div>
                        <div style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($past->appointment_date)->format('M d, Y') }} • Dr. {{ $past->doctor->user->name ?? 'Unknown' }}</div>
                    </div>
                    @empty
                    <div style="color: var(--text-muted); font-size: 0.875rem; text-align: center; padding: 1rem;">No past visits found.</div>
                    @endforelse
                    
                    <a href="{{ route('doctor.patients') }}" style="display: block; text-align: center; font-size: 0.875rem; font-weight: 600; color: var(--primary); text-decoration: none; margin-top: 1rem;">View Full Medical Record <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>

        </div>
    </form>
@endsection
