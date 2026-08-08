@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Dashboard Overview</span>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Total Patients</h3>
                <p>{{ number_format($totalPatients) }}</p>
            </div>
            <div class="stat-icon blue">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
        </div>
        
        <div class="card stat-card">
            <div class="stat-info">
                <h3>Total Doctors</h3>
                <p>{{ number_format($totalDoctors) }}</p>
            </div>
            <div class="stat-icon teal">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Today's Appointments</h3>
                <p>{{ number_format($todayAppointments) }}</p>
            </div>
            <div class="stat-icon orange">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        <div class="card stat-card">
            <div class="stat-info">
                <h3>Today's Revenue</h3>
                <p>LKR {{ number_format($todayRevenue) }}</p>
            </div>
            <div class="stat-icon purple">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Appointment Statistics (Bar/Line Chart) -->
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Appointment Statistics</h3>
            <canvas id="appointmentsChart" height="100"></canvas>
        </div>

        <!-- Doctor Workload (Pie Chart) -->
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Doctor Workload (Patients)</h3>
            <canvas id="workloadChart" height="220"></canvas>
        </div>
    </div>
    
    <!-- Monthly Revenue (Line Chart) -->
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Monthly Revenue Trend (Last 6 Months)</h3>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctxAppt = document.getElementById('appointmentsChart').getContext('2d');
        new Chart(ctxAppt, {
            type: 'bar',
            data: {
                labels: {!! json_encode($apptStatsLabels) !!},
                datasets: [{
                    label: 'Completed',
                    data: {!! json_encode($completedData) !!},
                    backgroundColor: '#2563EB',
                    borderRadius: 4
                },
                {
                    label: 'Cancelled',
                    data: {!! json_encode($cancelledData) !!},
                    backgroundColor: '#F87171',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Doctor Workload Pie Chart
        const ctxWork = document.getElementById('workloadChart').getContext('2d');
        new Chart(ctxWork, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($docLabels) !!},
                datasets: [{
                    data: {!! json_encode($docData) !!},
                    backgroundColor: ['#2563EB', '#14B8A6', '#9333EA', '#F97316'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, cutout: '70%' }
        });

        // Revenue Line Chart
        const ctxRev = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctxRev.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(20, 184, 166, 0.5)');
        gradient.addColorStop(1, 'rgba(20, 184, 166, 0.0)');
        
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: {!! json_encode($revLabels) !!},
                datasets: [{
                    label: 'Revenue (LKR)',
                    data: {!! json_encode($revData) !!},
                    borderColor: '#14B8A6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });
    });
</script>
@endpush
