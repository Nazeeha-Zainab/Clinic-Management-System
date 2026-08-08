@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Appointment Management</span>
        <button class="btn" data-modal="bookAppointmentModal"><i class="fa-solid fa-calendar-plus"></i> New
            Appointment</button>
    </div>

    <!-- Filters & Search -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Search Patient</label>
                <div class="header-search" style="width: 100%;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search-input" placeholder="Name or ID...">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Date</label>
                <input type="date" id="filter-date" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Status</label>
                <select id="filter-status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Doctor</label>
                <select id="filter-doctor" class="form-control">
                    <option value="">All Doctors</option>
                </select>
            </div>
            <button class="btn btn-outline" id="btn-apply-filters" style="height: 42px;"><i class="fa-solid fa-filter"></i>
                Apply</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div id="apt-loading" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem; margin-bottom:1rem;"></i>
            <div>Loading appointments...</div>
        </div>

        <div class="table-responsive" id="apt-table-wrapper" style="display:none;">
            <table>
                <thead>
                    <tr>
                        <th>Apt. No</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Consultation Time</th>
                        <th>Token No</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apt-tbody"></tbody>
            </table>
        </div>

        <div id="apt-empty" style="display:none; text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-regular fa-calendar-xmark" style="font-size:3rem; margin-bottom:1rem; opacity:0.35;"></i>
            <div style="font-size:1rem; font-weight:500;">No appointments found.</div>
        </div>
    </div>

    <!-- Quick Book Modal -->
    <div class="modal-overlay" id="bookAppointmentModal">
        <div class="modal">
            <div class="modal-header">
                <h3>New Appointment</h3>
                <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form>
                <div class="modal-body">
                    <div
                        style="padding:1rem; background:rgba(37,99,235,0.05); border-radius:8px; margin-bottom:1rem; font-size:0.875rem;">
                        <i class="fa-solid fa-info-circle" style="color:var(--primary); margin-right:0.5rem;"></i>
                        Quick booking via receptionist is coming soon.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="modal-overlay" id="viewAppointmentModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Appointment Details</h3>
                <button class="modal-close" onclick="closeAptModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewAppointmentBody">
                <!-- Populated by JS -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeAptModal()">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const token = localStorage.getItem('token');
        let allAppointments = [];

        async function loadAppointments() {
            if (!token) { window.location.href = '/login'; return; }

            document.getElementById('apt-loading').style.display = 'block';
            document.getElementById('apt-table-wrapper').style.display = 'none';
            document.getElementById('apt-empty').style.display = 'none';

            const date = document.getElementById('filter-date').value;
            const status = document.getElementById('filter-status').value;
            const doctorId = document.getElementById('filter-doctor').value;

            let url = `/api/reception/appointments?`;
            if (date) url += `date=${date}&`;
            if (status) url += `status=${status}&`;
            if (doctorId) url += `doctor_id=${doctorId}&`;

            try {
                const res = await fetch(url, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (res.status === 401) { window.location.href = '/login'; return; }

                const data = await res.json();
                document.getElementById('apt-loading').style.display = 'none';

                if (data.status === 'success') {
                    allAppointments = data.appointments;

                    // Populate doctors dropdown if empty
                    const docSelect = document.getElementById('filter-doctor');
                    if (docSelect.options.length <= 1) {
                        data.doctors.forEach(doc => {
                            const opt = document.createElement('option');
                            opt.value = doc.id;
                            opt.textContent = doc.name;
                            docSelect.appendChild(opt);
                        });
                    }

                    renderTable();
                }
            } catch (err) {
                console.error(err);
                document.getElementById('apt-loading').innerHTML =
                    '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load appointments.</div>';
            }
        }

        function renderTable() {
            const tbody = document.getElementById('apt-tbody');
            tbody.innerHTML = '';

            const search = document.getElementById('search-input').value.toLowerCase();

            const filtered = allAppointments.filter(apt => {
                if (!search) return true;
                return apt.patient_name.toLowerCase().includes(search) ||
                    apt.patient_id_label.toLowerCase().includes(search) ||
                    apt.apt_number.toLowerCase().includes(search);
            });

            if (filtered.length === 0) {
                document.getElementById('apt-table-wrapper').style.display = 'none';
                document.getElementById('apt-empty').style.display = 'block';
                return;
            }

            document.getElementById('apt-table-wrapper').style.display = 'block';
            document.getElementById('apt-empty').style.display = 'none';

            filtered.forEach(apt => {
                let statusOptions = '';
                let selectStyle = '';

                if (apt.status === 'scheduled') {
                    selectStyle = 'border: 1px solid #F59E0B; color: #B45309; background: #FEF3C7;';
                    statusOptions = `
                        <option value="scheduled" selected>Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    `;
                } else if (apt.status === 'completed') {
                    selectStyle = 'border: 1px solid #10B981; color: #047857; background: #D1FAE5;';
                    statusOptions = `<option value="completed" selected>Completed</option>`;
                } else {
                    selectStyle = 'border: 1px solid #EF4444; color: #B91C1C; background: #FEE2E2;';
                    statusOptions = `<option value="cancelled" selected>Cancelled</option>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 600;">${apt.apt_number}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-dark);">${apt.patient_name}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">${apt.doctor_name}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${apt.doctor_specialty}</div>
                    </td>
                    <td>
                        <div style="font-weight: 500;">${new Date(apt.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                        <div style="font-size: 0.75rem; color: var(--primary); font-weight: 600;">${apt.time_label}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-dark); text-align: center; background: var(--bg-light); border-radius: 4px; padding: 0.25rem;">${apt.token_number || '-'}</div>
                    </td>
                    <td>
                        <select class="form-control apt-status-select" data-id="${apt.id}" style="padding: 0.25rem; font-size: 0.75rem; width: auto; border-radius: 4px; font-weight: 600; ${selectStyle}" ${apt.status !== 'scheduled' ? 'disabled' : ''}>
                            ${statusOptions}
                        </select>
                    </td>
                    <td>
                        <button class="btn-icon" title="View Details" onclick="viewAppointment(${apt.id})"><i class="fa-solid fa-eye"></i></button>
                        ${apt.status === 'scheduled' ? `<button class="btn-icon cancel-btn" data-id="${apt.id}" style="color: var(--danger);" title="Cancel"><i class="fa-solid fa-xmark"></i></button>` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Add event listeners for status updates
            document.querySelectorAll('.apt-status-select').forEach(select => {
                select.addEventListener('change', async function () {
                    const newStatus = this.value;
                    const aptId = this.getAttribute('data-id');
                    if (confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
                        await updateStatus(aptId, newStatus);
                    } else {
                        this.value = 'scheduled'; // Revert back if cancelled
                    }
                });
            });

            document.querySelectorAll('.cancel-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const aptId = this.getAttribute('data-id');
                    if (confirm(`Are you sure you want to cancel this appointment?`)) {
                        await updateStatus(aptId, 'cancelled');
                    }
                });
            });
        }

        async function updateStatus(aptId, status) {
            try {
                const res = await fetch(`/api/reception/appointments/${aptId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ status })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    loadAppointments(); // Reload to reflect changes
                } else {
                    alert(data.message || 'Failed to update status');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while updating status.');
            }
        }

        window.viewAppointment = function (id) {
            const apt = allAppointments.find(x => x.id === id);
            if (!apt) return;

            document.getElementById('viewAppointmentBody').innerHTML = `
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark);">${apt.apt_number}</div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">${new Date(apt.appointment_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })} at ${apt.time_label}</div>
                    <span class="badge" style="margin-top: 0.5rem; text-transform: capitalize;">${apt.status}</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Patient Name</label>
                        <div style="font-weight: 600;">${apt.patient_name}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Patient Phone</label>
                        <div style="font-weight: 500;">${apt.patient_phone}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Patient ID</label>
                        <div style="font-weight: 500;">${apt.patient_id_label}</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Doctor</label>
                        <div style="font-weight: 600;">${apt.doctor_name}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Specialty</label>
                        <div style="font-weight: 500;">${apt.doctor_specialty}</div>
                    </div>
                </div>
            `;
            document.getElementById('viewAppointmentModal').classList.add('active');
        };

        window.closeAptModal = function () {
            document.getElementById('viewAppointmentModal').classList.remove('active');
        };

        document.getElementById('btn-apply-filters').addEventListener('click', loadAppointments);
        document.getElementById('search-input').addEventListener('input', renderTable);

        loadAppointments();
    </script>
@endpush