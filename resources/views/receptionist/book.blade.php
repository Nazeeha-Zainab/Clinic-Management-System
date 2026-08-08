@extends('layouts.patient')

@section('content')
    <div class="page-title">
        <span>Book Appointment</span>
    </div>

    <div class="step-indicator">
        <div class="step completed" id="step-1">1</div>
        <div class="step active" id="step-2">2</div>
        <div class="step" id="step-3">3</div>
        <div class="step" id="step-4">4</div>
    </div>
    <div
        style="display: flex; justify-content: space-between; margin-bottom: 2rem; color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">
        <span id="label-1" style="color: var(--primary);">Service</span>
        <span id="label-2" style="color: var(--primary);">Doctor</span>
        <span id="label-3">Date & Time</span>
        <span id="label-4">Confirm</span>
    </div>

    <form action="{{ route('patient.appointments.store') }}" method="POST">
        @csrf

        <!-- Step 2: Select Doctor -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Select a Doctor</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">Choose a specialist for your
                consultation.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                @foreach($doctors as $doctor)
                    <label class="selectable-card"
                        style="display: flex; align-items: center; gap: 1rem; cursor: pointer; border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                        <input type="radio" name="doctor_id" value="{{ $doctor->id }}" required style="display: none;">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($doctor->user->name) }}&background=random"
                            alt="{{ $doctor->user->name }}" class="avatar">
                        <div>
                            <h4 style="font-weight: 600; color: var(--text-dark);">{{ $doctor->user->name }}</h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                {{ $doctor->specialization }} • {{ $doctor->qualifications }}
                            </p>
                            <div style="font-size: 0.75rem; color: var(--secondary); font-weight: 500;"><i
                                    class="fa-solid fa-calendar-check"></i> Available</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Step 3: Date & Time -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Select Date & Time</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">Choose your preferred
                consultation time.</p>

            <div id="schedule-container" style="display: none;">
                <h4 style="font-size: 1rem; font-weight: 500; margin-bottom: 1rem;">Available Dates</h4>
                <div id="dates-container" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;"></div>

                <div id="times-section" style="display: none;">
                    <h4 style="font-size: 1rem; font-weight: 500; margin-bottom: 1rem;">Available Sessions</h4>
                    <div id="times-container" style="display: flex; gap: 0.5rem; flex-wrap: wrap;"></div>
                </div>
            </div>

            <div id="no-schedule-message"
                style="display: none; padding: 2rem; text-align: center; color: var(--text-muted); background: var(--bg-alt); border-radius: 8px;">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><br>
                No upcoming available schedules for this doctor.
            </div>

            <div id="select-doctor-message"
                style="padding: 2rem; text-align: center; color: var(--text-muted); background: var(--bg-alt); border-radius: 8px;">
                <i class="fa-solid fa-user-doctor" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><br>
                Please select a doctor above to view their available schedules.
            </div>

            <input type="hidden" name="appointment_date" id="appointment_date">
            <input type="hidden" name="appointment_time" id="appointment_time">
        </div>

        <div style="display: flex; justify-content: space-between;">
            <a href="{{ route('patient.appointments') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" id="confirm-btn" class="btn">Confirm Appointment <i
                    class="fa-solid fa-check"></i></button>
        </div>
    </form>
@endsection

@push('scripts')
    <style>
        .selectable-card.selected {
            border-color: var(--primary) !important;
            background-color: rgba(37, 99, 235, 0.05);
        }

        .btn-primary {
            background-color: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
    </style>
    <script>
        let currentSchedules = {};
        const datesContainer = document.getElementById('dates-container');
        const timesContainer = document.getElementById('times-container');
        const scheduleContainer = document.getElementById('schedule-container');
        const timesSection = document.getElementById('times-section');
        const noScheduleMsg = document.getElementById('no-schedule-message');
        const selectDocMsg = document.getElementById('select-doctor-message');

        const inputDate = document.getElementById('appointment_date');
        const inputTime = document.getElementById('appointment_time');

        document.querySelectorAll('input[name="doctor_id"]').forEach(radio => {
            radio.addEventListener('change', async function () {
                // UI visual update
                document.querySelectorAll('.selectable-card').forEach(card => card.classList.remove('selected'));
                this.closest('.selectable-card').classList.add('selected');

                // Reset state
                selectDocMsg.style.display = 'none';
                scheduleContainer.style.display = 'none';
                noScheduleMsg.style.display = 'none';
                timesSection.style.display = 'none';
                inputDate.value = '';
                inputTime.value = '';

                updateStepper();

                const doctorId = this.value;

                try {
                    const response = await fetch(`/patient/doctor-schedules/${doctorId}`);
                    const data = await response.json();
                    currentSchedules = data;

                    if (Object.keys(data).length === 0) {
                        noScheduleMsg.style.display = 'block';
                        return;
                    }

                    renderDates();
                    scheduleContainer.style.display = 'block';
                } catch (error) {
                    console.error("Error fetching schedules:", error);
                    alert("Failed to load schedules. Please try again.");
                }
            });
        });

        function renderDates() {
            datesContainer.innerHTML = '';
            Object.keys(currentSchedules).forEach(date => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline btn-sm date-btn';

                const dateObj = new Date(date);
                // Fix timezone issue when displaying date string
                const offset = dateObj.getTimezoneOffset();
                dateObj.setMinutes(dateObj.getMinutes() + offset);

                btn.textContent = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                btn.dataset.date = date;

                btn.addEventListener('click', function () {
                    document.querySelectorAll('.date-btn').forEach(b => {
                        b.classList.remove('btn-primary');
                    });
                    this.classList.add('btn-primary');

                    inputDate.value = this.dataset.date;
                    inputTime.value = ''; // Reset time

                    renderTimes(this.dataset.date);
                });

                datesContainer.appendChild(btn);
            });
        }

        function renderTimes(date) {
            timesContainer.innerHTML = '';
            timesSection.style.display = 'block';
            const blocks = currentSchedules[date];

            if (!blocks || blocks.length === 0) {
                timesContainer.innerHTML = '<span style="color: var(--text-muted); font-size: 0.875rem;">No sessions available for this date.</span>';
                return;
            }

            blocks.forEach(block => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline btn-sm time-btn';

                btn.textContent = block.label;
                btn.dataset.time = block.start;

                btn.addEventListener('click', function () {
                    document.querySelectorAll('.time-btn').forEach(b => {
                        b.classList.remove('btn-primary');
                    });
                    this.classList.add('btn-primary');
                    inputTime.value = this.dataset.time;
                    updateStepper();
                });

                timesContainer.appendChild(btn);
            });
        }

        function updateStepper() {
            const hasDoctor = document.querySelector('input[name="doctor_id"]:checked') !== null;
            const hasDate = inputDate.value !== '';
            const hasTime = inputTime.value !== '';

            // Reset steps 2, 3, 4
            document.getElementById('step-2').className = 'step';
            document.getElementById('step-3').className = 'step';
            document.getElementById('step-4').className = 'step';
            document.getElementById('label-2').style.color = 'var(--text-muted)';
            document.getElementById('label-3').style.color = 'var(--text-muted)';
            document.getElementById('label-4').style.color = 'var(--text-muted)';

            if (hasDoctor && hasDate && hasTime) {
                // All selected, we are ready to confirm
                document.getElementById('step-2').className = 'step completed';
                document.getElementById('label-2').style.color = 'var(--text-muted)';
                document.getElementById('step-3').className = 'step completed';
                document.getElementById('label-3').style.color = 'var(--text-muted)';
                document.getElementById('step-4').className = 'step active';
                document.getElementById('label-4').style.color = 'var(--primary)';
            } else if (hasDoctor) {
                // Doctor selected, waiting for date & time
                document.getElementById('step-2').className = 'step completed';
                document.getElementById('label-2').style.color = 'var(--text-muted)';
                document.getElementById('step-3').className = 'step active';
                document.getElementById('label-3').style.color = 'var(--primary)';
            } else {
                // Nothing selected, waiting for doctor
                document.getElementById('step-2').className = 'step active';
                document.getElementById('label-2').style.color = 'var(--primary)';
            }
        }

        document.querySelector('form').addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!document.querySelector('input[name="doctor_id"]:checked')) {
                alert("Please select a doctor.");
                return;
            }
            if (!inputDate.value || !inputTime.value) {
                alert("Please select an available session date and time.");
                return;
            }

            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const doctorId = document.querySelector('input[name="doctor_id"]:checked').value;
            const confirmBtn = document.getElementById('confirm-btn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Booking...';

            try {
                const urlParams = new URLSearchParams(window.location.search);
                const patientId = urlParams.get('patient_id');
                const editAptId = urlParams.get('edit_apt_id');

                const payload = {
                    doctor_id: doctorId,
                    appointment_date: inputDate.value,
                    appointment_time: inputTime.value,
                };

                if (patientId) {
                    payload.patient_id = patientId;
                }
                if (editAptId) {
                    payload.edit_apt_id = editAptId;
                }

                const response = await fetch('/api/patient/book-appointment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    window.location.href = '/patient/appointments?booked=1';
                } else {
                    alert(data.message || 'Failed to book appointment. Please try again.');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Confirm Appointment';
                }
            } catch (err) {
                console.error(err);
                alert('Network error. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm Appointment';
            }
        });
    </script>
@endpush