@extends('layouts.receptionist')

@section('content')
    <div class="page-title">
        <span>Billing & Payments</span>
        <button class="btn" data-modal="invoiceModal"><i class="fa-solid fa-file-invoice"></i> Generate Invoice</button>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">

        <!-- Billing History -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600;">Recent Transactions</h3>
                <div class="header-search" style="width: 200px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Invoice ID..." id="invoiceSearch">
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Patient Name</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                            <tr class="transaction-row" data-invoice="{{ $payment->invoice_number }}">
                                <td style="font-weight: 600;">{{ $payment->invoice_number }}</td>
                                <td>{{ $payment->patient->user->name ?? 'Unknown' }}</td>
                                <td style="font-weight: 600; color: var(--text-dark);">LKR {{ number_format($payment->amount) }}
                                </td>
                                <td>
                                    @if($payment->payment_method === 'cash')
                                        <i class="fa-solid fa-money-bill-wave"
                                            style="color: var(--secondary); margin-right: 0.25rem;"></i> Cash
                                    @else
                                        <i class="fa-solid fa-credit-card"
                                            style="color: var(--primary); margin-right: 0.25rem;"></i> Card
                                    @endif
                                </td>
                                <td><span class="badge confirmed">{{ ucfirst($payment->status) }}</span></td>
                                <td><a href="{{ route('receptionist.billing.print', $payment->id) }}" target="_blank"
                                        class="btn-icon" title="Print Receipt"
                                        style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"><i
                                            class="fa-solid fa-print"></i></a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No recent
                                    transactions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="card" style="align-self: flex-start;">
            <h3
                style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                Today's Summary</h3>

            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span style="color: var(--text-muted);">Cash Collected</span>
                <span style="font-weight: 600;">LKR {{ number_format($cashCollected) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span style="color: var(--text-muted);">Card Payments</span>
                <span style="font-weight: 600;">LKR {{ number_format($cardCollected) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: var(--danger);">
                <span>Pending Payments</span>
                <span style="font-weight: 600;">LKR {{ number_format($pendingPayments) }}</span>
            </div>

            <div
                style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border); padding-top: 1rem; font-size: 1.125rem;">
                <span style="font-weight: 600;">Total Revenue</span>
                <span style="font-weight: 700; color: var(--primary);">LKR {{ number_format($totalRevenue) }}</span>
            </div>
        </div>

        <!-- Pending Bills -->
        <div class="card" style="grid-column: 1 / -1;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Pending Bills</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Appointment ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingAppointments as $appointment)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-dark);">
                                        {{ $appointment->patient->user->name ?? 'Unknown' }}</div>
                                </td>
                                <td style="color: var(--text-muted);">APT-{{ str_pad($appointment->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                                @php
                                    $fee = $appointment->doctor && $appointment->doctor->service ? $appointment->doctor->service->fee : 1500;
                                @endphp
                                <td style="font-weight: 600;">LKR {{ number_format($fee) }}</td>
                                <td><button class="btn btn-sm"
                                        onclick="openInvoiceModal({{ $appointment->id }}, '{{ $appointment->patient->user->name ?? 'Unknown' }}', '{{ $appointment->doctor->user->name ?? 'Unknown' }}', {{ $fee }}, '{{ $appointment->doctor->service->name ?? 'General Consultation' }}')">Pay
                                        Now</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No pending
                                    bills found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generate Invoice Modal -->
        <div class="modal-overlay" id="invoiceModal">
            <div class="modal">
                <div class="modal-header">
                    <h3>Generate Invoice & Process Payment</h3>
                    <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form action="{{ route('receptionist.billing.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Appointment / Patient</label>
                            <input list="appointmentOptions" class="form-control" id="appointmentSearchInput"
                                placeholder="-- Search pending appointment --" autocomplete="off" required>
                            <datalist id="appointmentOptions">
                                @foreach($pendingAppointments as $appointment)
                                    <option data-value="{{ $appointment->id }}"
                                        value="APT-{{ str_pad($appointment->id, 3, '0', STR_PAD_LEFT) }} - {{ $appointment->patient->user->name ?? 'Unknown' }} - {{ $appointment->doctor->user->name ?? 'Unknown' }}">
                                    </option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="appointment_id" id="modalAppointmentIdHidden" required>
                        </div>

                        <div style="background: var(--bg-main); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; align-items: center;">
                                <span style="color: var(--text-muted);">Consultation Service</span>
                                <div style="text-align: right;">
                                    <div id="serviceNameDisplay" style="font-weight: 600; margin-bottom: 0.25rem;">Select an Appointment</div>
                                    <div id="serviceFeeDisplay" style="color: var(--text-muted);">LKR 0</div>
                                    <input type="hidden" name="service_fee" id="service_fee" value="0">
                                </div>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; align-items: center;">
                                <span style="color: var(--text-muted);">Additional Charges</span>
                                <div>
                                    <span style="font-weight: 600;">LKR </span>
                                    <input type="number" id="additional_charges" name="additional_charges"
                                        style="width: 80px; padding: 0.2rem; border: 1px solid var(--border); border-radius: 4px; text-align: right;"
                                        value="0" min="0" oninput="updateTotalAmount()">
                                </div>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border); font-size: 1.125rem;">
                                <span style="font-weight: 600;">Total Amount</span>
                                <span style="font-weight: 700; color: var(--primary);" id="totalAmountDisplay">LKR 0</span>
                                <input type="hidden" name="amount" id="totalAmountInput" value="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Payment Method</label>
                            <div style="display: flex; gap: 1rem;">
                                <label
                                    style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem 1rem; border: 1px solid var(--primary); border-radius: 8px; background: rgba(37, 99, 235, 0.05); color: var(--primary); font-weight: 600; flex: 1; justify-content: center;"
                                    id="labelCash" onclick="selectPaymentMethod('cash')">
                                    <input type="radio" name="payment_method" value="cash" checked style="display: none;"
                                        id="radioCash">
                                    <i class="fa-solid fa-money-bill-wave"></i> Cash
                                </label>
                                <label
                                    style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); font-weight: 500; flex: 1; justify-content: center;"
                                    id="labelCard" onclick="selectPaymentMethod('card')">
                                    <input type="radio" name="payment_method" value="card" style="display: none;"
                                        id="radioCard">
                                    <i class="fa-solid fa-credit-card"></i> Card
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline modal-cancel modal-close">Cancel</button>
                        <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Complete Payment</button>
                    </div>
                </form>
            </div>
        </div>
@endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Search filter for Invoice ID
                document.getElementById('invoiceSearch').addEventListener('input', function () {
                    let val = this.value.toLowerCase();
                    document.querySelectorAll('.transaction-row').forEach(function (row) {
                        let invoiceId = String(row.dataset.invoice).toLowerCase();
                        if (invoiceId.includes(val)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });

                // Datalist to hidden input sync
                document.getElementById('appointmentSearchInput').addEventListener('input', function () {
                    var val = this.value;
                    var opts = document.getElementById('appointmentOptions').childNodes;
                    document.getElementById('modalAppointmentIdHidden').value = ''; // reset
                    for (var i = 0; i < opts.length; i++) {
                        if (opts[i].value === val) {
                            document.getElementById('modalAppointmentIdHidden').value = opts[i].dataset.value;
                            break;
                        }
                    }
                });
            });

            function openInvoiceModal(appointmentId, patientName, doctorName, fee, serviceName) {
                document.getElementById('modalAppointmentIdHidden').value = appointmentId;
                document.getElementById('appointmentSearchInput').value = `APT-${String(appointmentId).padStart(3, '0')} - ${patientName} - ${doctorName}`;
                
                document.getElementById('service_fee').value = fee;
                document.getElementById('serviceNameDisplay').textContent = serviceName;
                document.getElementById('serviceFeeDisplay').textContent = 'LKR ' + fee.toLocaleString('en-US', {minimumFractionDigits: 0});
                
                document.getElementById('additional_charges').value = 0;
                
                updateTotalAmount();
                
                const invoiceModal = document.getElementById('invoiceModal');
                invoiceModal.classList.add('active');
            }

            function selectPaymentMethod(method) {
                document.getElementById('radio' + (method === 'cash' ? 'Cash' : 'Card')).checked = true;

                const cashLabel = document.getElementById('labelCash');
                const cardLabel = document.getElementById('labelCard');

                if (method === 'cash') {
                    cashLabel.style.background = 'rgba(37, 99, 235, 0.05)';
                    cashLabel.style.borderColor = 'var(--primary)';
                    cashLabel.style.color = 'var(--primary)';

                    cardLabel.style.background = 'transparent';
                    cardLabel.style.borderColor = 'var(--border)';
                    cardLabel.style.color = 'var(--text-muted)';
                } else {
                    cardLabel.style.background = 'rgba(37, 99, 235, 0.05)';
                    cardLabel.style.borderColor = 'var(--primary)';
                    cardLabel.style.color = 'var(--primary)';

                    cashLabel.style.background = 'transparent';
                    cashLabel.style.borderColor = 'var(--border)';
                    cashLabel.style.color = 'var(--text-muted)';
                }
            }

            function updateTotalAmount() {
                const serviceSelect = document.getElementById('service_fee');
                const serviceFee = parseFloat(serviceSelect.value) || 0;
                const additionalCharges = parseFloat(document.getElementById('additional_charges').value) || 0;

                const total = serviceFee + additionalCharges;

                document.getElementById('totalAmountDisplay').innerText = 'LKR ' + total.toLocaleString('en-US', { minimumFractionDigits: 0 });
                document.getElementById('totalAmountInput').value = total;
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Initialize total amount
                updateTotalAmount();
            });
        </script>
    @endpush