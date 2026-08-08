@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Doctor Schedules</span>
        <button class="btn" data-modal="assignScheduleModal">Assign Schedule</button>
    </div>

    <!-- Calendar Card -->
    <div class="card" style="padding: 0;">
        <div
            style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Weekly Overview</h3>
            <div
                style="display: flex; gap: 0.5rem; align-items: center; background: var(--bg-alt); padding: 0.25rem; border-radius: 8px;">
                <a href="{{ route('admin.schedules', ['week' => $prevWeek]) }}" class="btn-icon"
                    style="background: white; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"><i
                        class="fa-solid fa-chevron-left"></i></a>
                <span style="font-weight: 600; padding: 0 1rem; color: var(--text-dark);">
                    {{ $startOfWeek->format('M d') }} - {{ $endOfWeek->format('M d, Y') }}
                </span>
                <a href="{{ route('admin.schedules', ['week' => $nextWeek]) }}" class="btn-icon"
                    style="background: white; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"><i
                        class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>

        <div
            style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; border-bottom: 1px solid var(--border);">
            @php
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            @endphp
            @foreach($days as $index => $day)
                @php
                    $currentDate = clone $startOfWeek;
                    $currentDate->addDays($index);
                    $isToday = $currentDate->isToday();
                @endphp
                <div
                    style="padding: 1rem; {{ $index < 6 ? 'border-right: 1px solid var(--border);' : 'background: rgba(248, 250, 252, 0.5);' }}">
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">
                        {{ $day }}</div>
                    <div
                        style="font-size: 1.25rem; font-weight: 700; margin-top: 0.25rem; {{ $isToday ? 'color: var(--primary);' : '' }}">
                        {{ $currentDate->format('d') }}
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $schedulesByDay = [];
            foreach ($schedules as $schedule) {
                $dayOfWeek = date('w', strtotime($schedule->date));
                $schedulesByDay[$dayOfWeek][] = $schedule;
            }
            $colors = [
                ['bg' => 'rgba(37, 99, 235, 0.1)', 'border' => 'var(--primary)', 'text' => 'var(--primary)'],
                ['bg' => 'rgba(20, 184, 166, 0.1)', 'border' => 'var(--secondary)', 'text' => 'var(--secondary)'],
                ['bg' => 'rgba(147, 51, 234, 0.1)', 'border' => '#9333EA', 'text' => '#9333EA']
            ];
        @endphp

        <div style="display: grid; grid-template-columns: repeat(7, 1fr); min-height: 400px;">
            @for ($i = 1; $i <= 7; $i++)
                @php $dayIndex = $i === 7 ? 0 : $i; @endphp
                <div
                    style="padding: 0.5rem; {{ $i < 7 ? 'border-right: 1px solid var(--border);' : 'background: rgba(248, 250, 252, 0.5);' }}">
                    @if(isset($schedulesByDay[$dayIndex]) && count($schedulesByDay[$dayIndex]) > 0)
                        @foreach($schedulesByDay[$dayIndex] as $index => $schedule)
                            @php $c = $colors[$index % count($colors)]; @endphp
                            <div
                                style="background: {{ $c['bg'] }}; border-left: 3px solid {{ $c['border'] }}; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem;">
                                <div style="font-size: 0.75rem; font-weight: 600; color: {{ $c['text'] }};">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </div>
                                <div style="font-size: 0.8rem; font-weight: 600; margin-top: 0.25rem;">
                                    {{ $schedule->doctor->user->name }}
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                    {{ \Carbon\Carbon::parse($schedule->date)->format('M d, Y') }}
                                </div>
                                <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <button type="button" class="btn-icon edit-schedule-btn" data-modal="editScheduleModal"
                                            data-id="{{ $schedule->id }}" data-doctor="{{ $schedule->doctor_id }}"
                                            data-date="{{ $schedule->date }}"
                                            data-start="{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}"
                                            data-end="{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}"
                                            data-interval="{{ $schedule->interval }}"
                                            style="color: var(--text-muted); font-size: 0.75rem; padding: 0;" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="submit" class="btn-icon"
                                            style="color: var(--danger); font-size: 0.75rem; padding: 0;" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- Assign Schedule Modal -->
    <div class="modal-overlay" id="assignScheduleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Assign Doctor Schedule</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Doctor</label>
                        <select name="doctor_id" class="form-control" required>
                            <option value="" disabled selected>-- Select Doctor --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->user->name }} ({{ $doctor->specialization }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal-overlay" id="editScheduleModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Doctor Schedule</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editScheduleForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Doctor</label>
                        <select name="doctor_id" id="edit-sch-doctor" class="form-control" required>
                            <option value="" disabled selected>-- Select Doctor --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->user->name }} ({{ $doctor->specialization }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" id="edit-sch-date" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" id="edit-sch-start" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" id="edit-sch-end" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editBtns = document.querySelectorAll('.edit-schedule-btn');
            const editForm = document.getElementById('editScheduleForm');

            editBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');

                    document.getElementById('edit-sch-doctor').value = this.getAttribute('data-doctor');
                    document.getElementById('edit-sch-date').value = this.getAttribute('data-date');
                    document.getElementById('edit-sch-start').value = this.getAttribute('data-start');
                    document.getElementById('edit-sch-end').value = this.getAttribute('data-end');

                    editForm.action = `/admin/schedules/${id}`;
                });
            });
            /*const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value) {
                        const date = new Date(this.value);
                        // getDay() returns 0 for Sunday
                        if (date.getDay() === 0) {
                            alert('Scheduling on Sunday is not allowed as it is an off day.');
                            this.value = '';
                        }
                    }
                });
            });*/
        });
    </script>
@endpush