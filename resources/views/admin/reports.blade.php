@extends('layouts.admin')

@section('content')
    <div class="page-title">
        <span>Analytics & Reports</span>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <select id="export-type" class="form-control" style="width: auto;">
                <option value="">Select Report...</option>
                <option value="financial">Financial Report</option>
                <option value="appointments">Appointments Report</option>
                <option value="doctors">Doctor Workload Report</option>
                <option value="patients">Patient Growth Report</option>
            </select>
            <button class="btn" onclick="exportReport()">Export</button>
        </div>
    </div>

    <form action="{{ route('admin.reports') }}" method="GET" style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
        <select name="date_filter" id="date_filter" class="form-control" style="width: 200px;" onchange="toggleCustomDates()">
            <option value="last_7_days" {{ $dateFilter == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
            <option value="last_30_days" {{ $dateFilter == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
            <option value="this_month" {{ $dateFilter == 'this_month' ? 'selected' : '' }}>This Month</option>
            <option value="this_year" {{ $dateFilter == 'this_year' ? 'selected' : '' }}>This Year</option>
            <option value="custom" {{ $dateFilter == 'custom' ? 'selected' : '' }}>Custom Range</option>
        </select>
        
        <div id="custom-dates" style="display: {{ $dateFilter == 'custom' ? 'flex' : 'none' }}; gap: 0.5rem; align-items: center;">
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            <span style="color: var(--text-muted);">to</span>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>

        <button type="submit" class="btn btn-outline" id="apply-btn" style="display: {{ $dateFilter == 'custom' ? 'inline-block' : 'none' }};">Apply</button>
    </form>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(37, 99, 235, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Appointments</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-dark);">{{ number_format($totalAppointments) }}</div>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(20, 184, 166, 0.1); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">New Patients</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-dark);">{{ number_format($newPatients) }}</div>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(234, 179, 8, 0.1); color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Revenue</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-dark);">LKR {{ number_format($totalRevenue) }}</div>
            </div>
        </div>
        <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Cancellations</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-dark);">{{ number_format($cancellations) }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div class="card">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--text-dark);">Revenue Trend</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="card" style="flex: 1;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--text-dark);">Appointment Status</h3>
                <div style="height: 200px; position: relative;">
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-top: 1.5rem;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--text-dark);">Appointments by Doctor</h3>
        <div style="height: 300px; position: relative;">
            <canvas id="doctorWorkloadChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleCustomDates() {
        var select = document.getElementById('date_filter');
        var customDates = document.getElementById('custom-dates');
        var applyBtn = document.getElementById('apply-btn');
        if(select.value === 'custom') {
            customDates.style.display = 'flex';
            applyBtn.style.display = 'inline-block';
        } else {
            customDates.style.display = 'none';
            applyBtn.style.display = 'none';
            select.form.submit();
        }
    }

    function exportReport() {
        let type = document.getElementById('export-type').value;
        let dateFilter = document.querySelector('[name=date_filter]').value;
        let queryParams = '?type=' + type + '&date_filter=' + dateFilter;
        
        if (dateFilter === 'custom') {
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;
            queryParams += '&start_date=' + startDate + '&end_date=' + endDate;
        }
        
        if(type) {
            window.open('{{ route('admin.reports.export') }}' + queryParams, '_blank');
        } else {
            alert('Please select a report type to export.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Shared chart config
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. Revenue Trend Chart (Line)
        const ctxRev = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: @json($revenueTrendLabels),
                datasets: [{
                    label: 'Revenue (LKR)',
                    data: @json($revenueTrendData),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#2563eb',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e8f0', drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Appointment Status Chart (Doughnut)
        const ctxStatus = document.getElementById('appointmentStatusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: @json($statusData),
                    backgroundColor: ['#14b8a6', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                }
            }
        });

        // 3. Appointments by Doctor (Bar)
        const ctxDoc = document.getElementById('doctorWorkloadChart').getContext('2d');
        new Chart(ctxDoc, {
            type: 'bar',
            data: {
                labels: @json($doctorLabels),
                datasets: [{
                    label: 'Appointments',
                    data: @json($doctorData),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e8f0', drawBorder: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endpush
