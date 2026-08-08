@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Patient Database</span>
        <a href="{{ route('receptionist.register') }}" class="btn"><i class="fa-solid fa-user-plus"></i> Register
            Patient</a>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="header-search" style="width: 350px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="pt-search" placeholder="Search by name or phone...">
            </div>
        </div>

        <!-- Loading -->
        <div id="pt-loading" style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem; margin-bottom:1rem;"></i>
            <div>Loading patients...</div>
        </div>

        <div class="table-responsive" id="pt-table-wrapper" style="display:none;">
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Contact Info</th>
                        <th>Last Visit</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pt-tbody"></tbody>
            </table>
            <div
                style="display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; color:var(--text-muted); font-size:0.875rem;">
                <span id="pt-count-label">Showing 0 patients</span>
            </div>
        </div>

        <div id="pt-empty" style="display:none; text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fa-solid fa-users-slash" style="font-size:3rem; margin-bottom:1rem; opacity:0.35;"></i>
            <div style="font-size:1rem; font-weight:500;">No patients found.</div>
            <a href="{{ route('receptionist.register') }}" class="btn" style="margin-top:1rem;">Register First Patient</a>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div class="modal-overlay" id="viewPatientModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Patient Details</h3>
                <button class="modal-close" onclick="closeModal('viewPatientModal')"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewPatientBody">
                <!-- Populated by JS -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('viewPatientModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div class="modal-overlay" id="editPatientModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Patient</h3>
                <button class="modal-close" onclick="closeModal('editPatientModal')"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editPatientForm">
                <input type="hidden" id="edit_patient_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="edit_phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" id="edit_dob" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select id="edit_gender" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="edit_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editPatientModal')">Cancel</button>
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const token = localStorage.getItem('token');
        let allPatients = [];

        async function loadPatients() {
            if (!token) { window.location.href = '/login'; return; }

            try {
                const res = await fetch('/api/reception/patients', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (res.status === 401) { window.location.href = '/login'; return; }

                const data = await res.json();
                document.getElementById('pt-loading').style.display = 'none';

                allPatients = data.patients || [];
                renderTable(allPatients);

            } catch (err) {
                console.error(err);
                document.getElementById('pt-loading').innerHTML =
                    '<i class="fa-solid fa-triangle-exclamation" style="font-size:2rem;color:var(--danger);"></i><div style="margin-top:0.5rem;">Failed to load patients.</div>';
            }
        }

        function renderTable(patients) {
            const tbody = document.getElementById('pt-tbody');
            const wrapper = document.getElementById('pt-table-wrapper');
            const empty = document.getElementById('pt-empty');
            tbody.innerHTML = '';

            const search = document.getElementById('pt-search').value.toLowerCase();
            const filtered = patients.filter(p =>
                !search ||
                p.full_name.toLowerCase().includes(search) ||
                (p.phone && p.phone.toLowerCase().includes(search))
            );

            if (filtered.length === 0) {
                wrapper.style.display = 'none';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            wrapper.style.display = 'block';
            document.getElementById('pt-count-label').textContent = `Showing ${filtered.length} patient${filtered.length !== 1 ? 's' : ''}`;

            filtered.forEach(p => {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td style="font-weight:500;">${p.patient_id}</td>
                        <td>
                            <div style="font-weight:600; color:var(--text-dark);">${p.full_name}</div>
                        </td>
                        <td>
                            <div>${p.phone || '<span style="color:var(--text-muted)">—</span>'}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted);">${p.email || ''}</div>
                        </td>
                        <td>
                            ${p.last_visit
                        ? `<div>${p.last_visit}</div><div style="font-size:0.75rem; color:var(--text-muted);">${p.last_status}</div>`
                        : '<span style="color:var(--text-muted); font-size:0.8rem;">No visits yet</span>'}
                        </td>
                        <td style="font-size:0.85rem; color:var(--text-muted);">${p.registered}</td>
                        <td>
                            <button class="btn-icon" title="View Profile" onclick="viewPatient(${p.id})"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-icon" title="Edit Info" onclick="editPatient(${p.id})"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-icon" style="color:var(--primary);" title="Book Appointment" onclick="window.location.href='/receptionist/walkin?patient_id=${p.id}'"><i class="fa-solid fa-calendar-plus"></i></button>
                        </td>
                    </tr>
                `);
            });
        }

        document.getElementById('pt-search').addEventListener('input', () => renderTable(allPatients));

        // Show success banner if redirected from register
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('registered') === '1') {
            const banner = document.createElement('div');
            banner.style.cssText = 'background:rgba(34,197,94,0.12);border:1px solid #16a34a;color:#15803d;border-radius:10px;padding:0.9rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;font-weight:500;';
            banner.innerHTML = '<i class="fa-solid fa-circle-check" style="font-size:1.2rem;"></i> Patient registered successfully! <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;font-size:1.1rem;">&#x2715;</button>';
            document.querySelector('.page-title').insertAdjacentElement('afterend', banner);
            window.history.replaceState({}, '', window.location.pathname);
            setTimeout(() => banner.remove(), 5000);
        }

        // Modals
        window.viewPatient = function (id) {
            const p = allPatients.find(x => x.id === id);
            if (!p) return;

            document.getElementById('viewPatientBody').innerHTML = `
                <div style="margin-bottom: 1rem;">
                    <label style="color: var(--text-muted); font-size: 0.875rem;">Full Name</label>
                    <div style="font-weight: 600; font-size: 1.125rem;">${p.full_name}</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Patient ID</label>
                        <div style="font-weight: 500;">${p.patient_id}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Gender</label>
                        <div style="font-weight: 500;">${p.gender || 'N/A'}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Phone</label>
                        <div style="font-weight: 500;">${p.phone || 'N/A'}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Email</label>
                        <div style="font-weight: 500;">${p.email || 'N/A'}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Date of Birth</label>
                        <div style="font-weight: 500;">${p.dob || 'N/A'}</div>
                    </div>
                    <div>
                        <label style="color: var(--text-muted); font-size: 0.875rem;">Registered On</label>
                        <div style="font-weight: 500;">${p.registered}</div>
                    </div>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-size: 0.875rem;">Address</label>
                    <div style="font-weight: 500;">${p.address || 'N/A'}</div>
                </div>
            `;
            document.getElementById('viewPatientModal').classList.add('active');
        };

        window.editPatient = function (id) {
            const p = allPatients.find(x => x.id === id);
            if (!p) return;

            document.getElementById('edit_patient_id').value = p.id;
            document.getElementById('edit_full_name').value = p.full_name || '';
            document.getElementById('edit_phone').value = p.phone || '';
            document.getElementById('edit_dob').value = p.dob || '';
            document.getElementById('edit_gender').value = p.gender || 'Other';
            document.getElementById('edit_address').value = p.address || '';

            document.getElementById('editPatientModal').classList.add('active');
        };

        window.closeModal = function (modalId) {
            document.getElementById(modalId).classList.remove('active');
        };

        document.getElementById('editPatientForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('edit_patient_id').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

            try {
                const res = await fetch(`/api/reception/patients/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        full_name: document.getElementById('edit_full_name').value,
                        phone: document.getElementById('edit_phone').value,
                        dob: document.getElementById('edit_dob').value,
                        gender: document.getElementById('edit_gender').value,
                        address: document.getElementById('edit_address').value
                    })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    closeModal('editPatientModal');
                    loadPatients();
                } else {
                    alert(data.message || 'Failed to update patient');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while updating.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        loadPatients();
    </script>
@endpush