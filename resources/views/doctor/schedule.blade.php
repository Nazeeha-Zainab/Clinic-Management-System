@extends('layouts.doctor')

@section('content')
    <div class="page-title">
        <span>My Schedule</span>
    </div>

    <!-- Calendar Card -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;" id="week-title">Loading...</h3>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <button class="btn-icon" onclick="changeWeek(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="btn btn-outline btn-sm" onclick="goToToday()">Today</button>
                <button class="btn-icon" onclick="changeWeek(1)"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div id="schedule-loading" style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <div>Loading schedule...</div>
        </div>

        <div id="schedule-content" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; border-bottom: 1px solid var(--border);" id="week-headers">
                <!-- Headers injected via JS -->
            </div>

            <div style="display: grid; grid-template-columns: repeat(7, 1fr); min-height: 400px;" id="week-days">
                <!-- Days injected via JS -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let currentDate = new Date();
    const token = localStorage.getItem('token');

    function formatDateForApi(date) {
        const offset = date.getTimezoneOffset() * 60000;
        return new Date(date.getTime() - offset).toISOString().split('T')[0];
    }

    async function loadSchedule() {
        if (!token) { window.location.href = '/login'; return; }

        document.getElementById('schedule-loading').style.display = 'block';
        document.getElementById('schedule-content').style.display = 'none';
        
        const dateStr = formatDateForApi(currentDate);

        try {
            const res = await fetch(`/api/doctor/my-schedules?date=${dateStr}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            if (data.status === 'success') {
                renderSchedule(data.startOfWeek, data.endOfWeek, data.schedules);
            }
        } catch (err) {
            console.error('Failed to load schedule:', err);
            document.getElementById('schedule-loading').innerHTML = 
                '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem; color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load schedule.</div>';
        }
    }

    function renderSchedule(startStr, endStr, schedules) {
        document.getElementById('schedule-loading').style.display = 'none';
        document.getElementById('schedule-content').style.display = 'block';

        const startDate = new Date(startStr);
        const endDate = new Date(endStr);
        
        // Update title
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        document.getElementById('week-title').textContent = 
            `Weekly Availability: ${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;

        const headersContainer = document.getElementById('week-headers');
        const daysContainer = document.getElementById('week-days');
        
        headersContainer.innerHTML = '';
        daysContainer.innerHTML = '';

        const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        const todayStr = formatDateForApi(new Date());

        for (let i = 0; i < 7; i++) {
            // Calculate date for this column
            const iterDate = new Date(startDate);
            iterDate.setDate(startDate.getDate() + i);
            const iterDateStr = formatDateForApi(iterDate);
            const isToday = (iterDateStr === todayStr);

            // Render Header
            const hBg = (i === 6) ? 'background: rgba(248, 250, 252, 0.5);' : '';
            const hBorder = (i < 6) ? 'border-right: 1px solid var(--border);' : '';
            const dayNumColor = isToday ? 'color: var(--primary);' : (i === 6 ? 'color: var(--text-muted);' : '');
            
            headersContainer.insertAdjacentHTML('beforeend', `
                <div style="padding: 1rem; ${hBorder} ${hBg}">
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">${dayNames[i]}</div>
                    <div style="font-size: 1.25rem; font-weight: 700; margin-top: 0.25rem; ${dayNumColor}">${iterDate.getDate()}</div>
                </div>
            `);

            // Render Day Column
            const dBg = (i === 6) ? 'background: rgba(248, 250, 252, 0.5);' : (isToday ? 'background: rgba(37, 99, 235, 0.02);' : '');
            const dBorder = (i < 6) ? 'border-right: 1px solid var(--border);' : '';
            
            let dayHtml = `<div style="padding: 0.5rem; ${dBorder} ${dBg}">`;

            // Filter schedules for this date
            const daySchedules = schedules.filter(s => s.date === iterDateStr);
            
            if (daySchedules.length > 0) {
                daySchedules.forEach(schedule => {
                    const startFmt = formatTime(schedule.start_time);
                    const endFmt = formatTime(schedule.end_time);
                    
                    dayHtml += `
                        <div style="background: rgba(37, 99, 235, 0.1); border-left: 3px solid var(--primary); padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem; text-align: left;">
                            <div style="font-size: 0.75rem; font-weight: 600; color: var(--primary);">${startFmt} - ${endFmt}</div>
                            <div style="font-size: 0.8rem; font-weight: 600; margin-top: 0.25rem;">Consultation</div>
                        </div>
                    `;
                });
            } else {
                if (i === 6) {
                    // no Off Day label
                }
            }

            dayHtml += `</div>`;
            daysContainer.insertAdjacentHTML('beforeend', dayHtml);
        }
    }

    function formatTime(timeStr) {
        // timeStr is like "09:00:00"
        const [hour, min] = timeStr.split(':');
        const d = new Date();
        d.setHours(hour);
        d.setMinutes(min);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function changeWeek(offset) {
        currentDate.setDate(currentDate.getDate() + (offset * 7));
        loadSchedule();
    }

    function goToToday() {
        currentDate = new Date();
        loadSchedule();
    }

    // Initialize
    loadSchedule();
</script>
@endpush
