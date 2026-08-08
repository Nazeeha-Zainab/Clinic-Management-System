@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>Patient Records</span>
    </div>

    <!-- Search Container -->
    <div class="card" style="margin-bottom: 2rem; text-align: center;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Select a Patient</h3>
        <form action="{{ route('doctor.patients') }}" method="GET" style="max-width: 500px; margin: 0 auto; display: flex; gap: 1rem;">
            <div style="flex: 1;">
                <input type="text" class="form-control" name="patient_search" list="patientList" placeholder="Search by Patient Name or ID..." autocomplete="off" onchange="extractPatientId(this)" value="{{ request('patient_search') }}">
                <input type="hidden" name="patient_id" id="patientIdHidden" value="{{ request('patient_id') }}">
                <datalist id="patientList">
                    @foreach($patients as $patient)
                        <option value="PT-{{ str_pad($patient->id, 3, '0', STR_PAD_LEFT) }} - {{ $patient->user->name }}" data-id="{{ $patient->id }}"></option>
                    @endforeach
                </datalist>
            </div>
            <button type="submit" class="btn"><i class="fa-solid fa-search"></i> View Record</button>
        </form>
    </div>

    <script>
        function extractPatientId(input) {
            const list = document.getElementById('patientList');
            const options = list.options;
            let hiddenInput = document.getElementById('patientIdHidden');
            
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
                    hiddenInput.value = options[i].getAttribute('data-id');
                    return;
                }
            }
            hiddenInput.value = ''; // clear if not found
        }
    </script>

    @if($selectedPatient)
        <!-- Comprehensive Medical History View -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="queue-number" style="background: var(--bg-main); color: var(--text-muted); font-size: 1rem; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: 700;">{{ $selectedPatient->user->name }}</h3>
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            PT-{{ str_pad($selectedPatient->id, 3, '0', STR_PAD_LEFT) }} • 
                            {{ $selectedPatient->dob ? \Carbon\Carbon::parse($selectedPatient->dob)->age . ' yrs' : 'Age unknown' }} • 
                            {{ ucfirst($selectedPatient->gender ?? 'Unknown') }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- Health Snapshot -->
            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Health Snapshot</h4>
            <div style="background: rgba(37, 99, 235, 0.05); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(37, 99, 235, 0.1);">
                <div style="font-size: 0.875rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Medical History</div>
                <div style="font-size: 0.95rem; line-height: 1.6; color: var(--text-dark);">
                    {{ $selectedPatient->medical_history ?? 'No known medical history provided.' }}
                </div>
            </div>

            <!-- Timeline -->
            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Consultation History</h4>
            <div style="position: relative; border-left: 2px solid var(--border); padding-left: 1.5rem; margin-left: 0.5rem;">
                
                @forelse($consultationHistory as $index => $history)
                <div style="position: relative; margin-bottom: 2rem;">
                    <div style="position: absolute; left: -1.9rem; top: 0; width: 12px; height: 12px; border-radius: 50%; background: {{ $index === 0 ? 'var(--primary)' : 'var(--text-muted)' }}; border: 2px solid white;"></div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <div>
                            <h5 style="font-size: 1rem; font-weight: 600;">{{ $history->consultation->clinical_diagnosis ?? 'General Consultation' }}</h5>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Consulted by <strong style="color: var(--text-dark);">Dr. {{ $history->doctor->user->name }}</strong> • {{ \Carbon\Carbon::parse($history->appointment_date)->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div style="background: var(--bg-main); padding: 1rem; border-radius: 8px; font-size: 0.875rem;">
                        <p style="margin-bottom: 0.5rem;"><strong>Symptoms:</strong> {{ $history->consultation->presenting_symptoms ?? 'None reported' }}</p>
                        <p style="color: var(--text-muted); line-height: 1.5;">{{ $history->consultation->treatment_plan ?? 'No treatment plan recorded.' }}</p>
                    </div>
                </div>
                @empty
                <div style="color: var(--text-muted); padding: 1rem;">No past consultations found for this patient.</div>
                @endforelse

            </div>
        </div>
    @endif
@endsection
