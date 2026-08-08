@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Front Desk Overview</span>
        <div>
            <a href="{{ route('receptionist.walkin') }}" class="btn btn-secondary"><i
                    class="fa-solid fa-person-walking"></i> New Walk-in</a>
            <a href="{{ route('receptionist.register') }}" class="btn"><i class="fa-solid fa-user-plus"></i> Register
                Patient</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Today's Appointments</h3>
                <p>{{ $todaysAppointments }}</p>

            </div>
            <div class="stat-icon blue">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Waiting Patients</h3>
                <p>{{ $waitingPatients }}</p>

            </div>
            <div class="stat-icon orange">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Completed</h3>
                <p>{{ $completedAppointments }}</p>

            </div>
            <div class="stat-icon teal">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Today's Revenue</h3>
                <p>LKR {{ number_format($todaysRevenue) }}</p>

            </div>
            <div class="stat-icon purple">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Today's Appointment Queue -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Live Appointment Queue</h3>
            <div class="header-search" style="width: 250px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search queue...">
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Queue #</th>
                        <th>Patient Info</th>
                        <th>Doctor</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queue as $index => $appointment)
                        @php
                            $isNext = $appointment->status == 'pending'; // Assuming next in queue is pending
                            $isInProgress = $appointment->status == 'scheduled' && $index == 0; // Simple logic: first scheduled is in progress

                            $rowStyle = $isInProgress ? 'background: rgba(20, 184, 166, 0.05);' : '';
                            $numberStyle = $isInProgress ? 'background: var(--secondary); color: white;' : ($isNext ? '' : 'background: var(--bg-main); color: var(--text-muted);');
                        @endphp
                        <tr style="{{ $rowStyle }}">
                            <td>
                                <div class="queue-number" style="{{ $numberStyle }}">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-dark);">
                                    {{ $appointment->patient->user->name ?? 'Unknown' }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $appointment->doctor->user->name ?? 'Unknown' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $appointment->doctor->specialization ?? 'Specialist' }}</div>
                            </td>
                            <td style="font-weight: 600; {{ $isInProgress ? 'color: var(--primary);' : '' }}">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}
                            </td>
                            <td>
                                @if($appointment->status == 'scheduled')
                                    <span class="badge active" style="background: #CCFBF1; color: #115E59;">In Consultation</span>
                                @elseif($appointment->status == 'pending')
                                    <span class="badge pending">Waiting in Lobby</span>
                                @elseif($appointment->status == 'completed')
                                    <span class="badge active"
                                        style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">Completed</span>
                                @else
                                    <span class="badge confirmed"
                                        style="background: var(--bg-main); color: var(--text-muted);">{{ ucfirst($appointment->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('receptionist.appointments') }}" class="btn btn-outline btn-sm">View
                                    Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No appointments
                                in queue for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection