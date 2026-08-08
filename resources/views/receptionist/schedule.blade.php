@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Doctor Schedules</span>
        <div class="header-search" style="width: 260px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="doc-search" placeholder="Search doctor...">
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;" id="week-title">Loading...</h3>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button class="btn-icon" onclick="changeWeek(-1)" title="Previous week"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="btn btn-outline btn-sm" onclick="goToToday()">Today</button>
                <button class="btn-icon" onclick="changeWeek(1)" title="Next week"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <!-- Loading -->
        <div id="sch-loading" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem; margin-bottom:1rem;"></i>
            <div>Loading schedules...</div>
        </div>

        <div id="sch-content" style="display:none;">
            <!-- Day headers -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align:center; border-bottom: 1px solid var(--border);" id="week-headers"></div>
            <!-- Day columns -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); min-height: 420px;" id="week-days"></div>
        </div>

        <!-- Empty -->
        <div id="sch-empty" style="display:none; text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-regular fa-calendar-xmark" style="font-size:3rem; margin-bottom:1rem; opacity:0.35;"></i>
            <div style="font-size:1rem; font-weight:500;" id="sch-empty-msg">No schedules found for this week.</div>
        </div>
    </div>

    <!-- Legend -->
    <div id="sch-legend" style="display:none; margin-top:1rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
        <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">DOCTORS:</span>
        <div id="legend-items" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
    </div>
@endsection

@push('scripts')
<script>
    let currentDate = new Date();
    let allSchedules = [];
    let doctorColors = {};
    const token = localStorage.getItem('token');

    function formatDateForApi(date) {
        const offset = date.getTimezoneOffset() * 60000;
        return new Date(date.getTime() - offset).toISOString().split('T')[0];
    }

    async function loadSchedules() {
        if (!token) { window.location.href = '/login'; return; }

        document.getElementById('sch-loading').style.display = 'block';
        document.getElementById('sch-content').style.display = 'none';
        document.getElementById('sch-empty').style.display   = 'none';
        document.getElementById('sch-legend').style.display  = 'none';

        try {
            const dateStr = formatDateForApi(currentDate);
            const res = await fetch(`/api/reception/schedules?date=${dateStr}`, {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.status === 401) { window.location.href = '/login'; return; }

            const data = await res.json();
            document.getElementById('sch-loading').style.display = 'none';

            allSchedules = data.schedules || [];

            // Build doctorColors map from response
            doctorColors = {};
            allSchedules.forEach(s => {
                if (!doctorColors[s.doctor_name]) {
                    doctorColors[s.doctor_name] = s.color;
                }
            });

            renderCalendar(data.startOfWeek, data.endOfWeek, allSchedules);

        } catch (err) {
            console.error(err);
            document.getElementById('sch-loading').innerHTML =
                '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load schedules.</div>';
        }
    }

    function renderCalendar(startStr, endStr, schedules) {
        const startDate = new Date(startStr);
        const endDate   = new Date(endStr);

        // Week title
        const opts = { month: 'short', day: 'numeric', year: 'numeric' };
        document.getElementById('week-title').textContent =
            `Weekly Availability: ${startDate.toLocaleDateString('en-US', opts)} – ${endDate.toLocaleDateString('en-US', opts)}`;

        const headersEl = document.getElementById('week-headers');
        const daysEl    = document.getElementById('week-days');
        headersEl.innerHTML = '';
        daysEl.innerHTML    = '';

        const dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        const todayStr = formatDateForApi(new Date());

        // Filter by search
        const searchStr = document.getElementById('doc-search').value.toLowerCase();

        for (let i = 0; i < 7; i++) {
            const d = new Date(startDate);
            d.setDate(startDate.getDate() + i);
            const dStr    = formatDateForApi(d);
            const isToday = (dStr === todayStr);
            const isSun   = (i === 6);

            // Header
            const hBorder = isSun ? '' : 'border-right:1px solid var(--border);';
            const hBg     = isSun ? 'background:rgba(248,250,252,0.5);' : '';
            const numColor = isToday ? 'color:var(--primary);' : (isSun ? 'color:var(--text-muted);' : '');
            headersEl.insertAdjacentHTML('beforeend', `
                <div style="padding:1rem;${hBorder}${hBg}">
                    <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">${dayNames[i]}</div>
                    <div style="font-size:1.25rem;font-weight:700;margin-top:0.25rem;${numColor}">${d.getDate()}</div>
                </div>
            `);

            // Day column
            const dBorder = isSun ? '' : 'border-right:1px solid var(--border);';
            const dBg     = isToday ? 'background:rgba(37,99,235,0.02);' : (isSun ? 'background:rgba(248,250,252,0.5);' : '');
            let colHtml = `<div style="padding:0.5rem;${dBorder}${dBg}">`;

            const dayScheds = schedules.filter(s => {
                const matchDate   = s.date === dStr;
                const matchSearch = !searchStr || s.doctor_name.toLowerCase().includes(searchStr);
                return matchDate && matchSearch;
            });

            if (dayScheds.length > 0) {
                dayScheds.forEach(s => {
                    const c = s.color;
                    const bookedLabel = s.booked > 0
                        ? `<div style="font-size:0.7rem;color:var(--text-muted);">${s.booked} appointment${s.booked !== 1 ? 's' : ''} booked</div>`
                        : `<div style="font-size:0.7rem;color:var(--text-muted);">No bookings yet</div>`;

                    colHtml += `
                        <div style="background:${c.bg};border-left:3px solid ${c.border};padding:0.5rem;border-radius:4px;margin-bottom:0.5rem;cursor:pointer;" title="${s.doctor_name}: ${s.start_fmt} – ${s.end_fmt}">
                            <div style="font-size:0.72rem;font-weight:600;color:${c.text};">${s.start_fmt} – ${s.end_fmt}</div>
                            <div style="font-size:0.8rem;font-weight:600;margin-top:0.2rem;color:var(--text-dark);">${s.doctor_name}</div>
                            ${bookedLabel}
                        </div>
                    `;
                });
            } else if (isSun) {
                // no Off Day label
            }

            colHtml += '</div>';
            daysEl.insertAdjacentHTML('beforeend', colHtml);
        }

        // Show/hide empty
        const hasVisible = schedules.filter(s => {
            const inWeek    = s.date >= startStr && s.date <= endStr;
            const matchSearch = !searchStr || s.doctor_name.toLowerCase().includes(searchStr);
            return inWeek && matchSearch;
        }).length;

        if (hasVisible === 0) {
            document.getElementById('sch-content').style.display = 'none';
            document.getElementById('sch-empty').style.display   = 'block';
            document.getElementById('sch-empty-msg').textContent = searchStr
                ? `No schedules found for "${searchStr}" this week.`
                : 'No schedules found for this week.';
        } else {
            document.getElementById('sch-content').style.display = 'block';
            document.getElementById('sch-empty').style.display   = 'none';
        }

        // Render legend
        renderLegend();
    }

    function renderLegend() {
        const legendEl = document.getElementById('legend-items');
        legendEl.innerHTML = '';
        const seen = {};
        allSchedules.forEach(s => {
            if (!seen[s.doctor_name]) {
                seen[s.doctor_name] = s.color;
                legendEl.insertAdjacentHTML('beforeend', `
                    <span style="display:inline-flex;align-items:center;gap:0.3rem;font-size:0.78rem;background:${s.color.bg};border:1px solid ${s.color.border};color:${s.color.text};padding:0.2rem 0.6rem;border-radius:999px;font-weight:600;">
                        <span style="width:8px;height:8px;border-radius:50%;background:${s.color.border};display:inline-block;"></span>
                        ${s.doctor_name}
                    </span>
                `);
            }
        });
        if (Object.keys(seen).length > 0) {
            document.getElementById('sch-legend').style.display = 'flex';
        }
    }

    function changeWeek(offset) {
        currentDate.setDate(currentDate.getDate() + offset * 7);
        loadSchedules();
    }

    function goToToday() {
        currentDate = new Date();
        loadSchedules();
    }

    document.getElementById('doc-search').addEventListener('input', () => {
        // Re-render with current data and new search term
        const startDate = new Date(currentDate);
        startDate.setDate(startDate.getDate() - ((startDate.getDay() + 6) % 7)); // Mon
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 6);
        const startStr = formatDateForApi(startDate);
        const endStr   = formatDateForApi(endDate);
        renderCalendar(startStr, endStr, allSchedules);
    });

    loadSchedules();
</script>
@endpush
