<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 20px;
            color: #000;
            background: #fff;
            width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .clinic-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .clinic-address {
            font-size: 0.85rem;
            margin: 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 15px 0;
            text-transform: uppercase;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .details {
            width: 100%;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .details td {
            padding: 4px 0;
        }
        .details .label {
            font-weight: bold;
            width: 40%;
        }
        .details .value {
            text-align: right;
            width: 60%;
        }
        .total-section {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin: 15px 0;
            font-size: 1rem;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .token-section {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border: 2px solid #000;
            border-radius: 5px;
        }
        .token-label {
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .token-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 0.8rem;
            margin-top: 20px;
        }
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="clinic-name">MedCare Clinic</h1>
        <p class="clinic-address">123 Health Ave, Medical District<br>Tel: 011-234-5678</p>
    </div>

    <div class="title">Payment Receipt</div>

    <table class="details">
        <tr>
            <td class="label">Invoice ID:</td>
            <td class="value">{{ $payment->invoice_number }}</td>
        </tr>
        <tr>
            <td class="label">Date:</td>
            <td class="value">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Patient Name:</td>
            <td class="value">{{ $payment->patient->user->name ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <td class="label">Doctor Name:</td>
            <td class="value">{{ $payment->appointment->doctor->user->name ?? 'Unknown' }}</td>
        </tr>
        <tr>
            <td class="label">Appt ID:</td>
            <td class="value">APT-{{ str_pad($payment->appointment_id, 3, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">Payment Mode:</td>
            <td class="value">{{ ucfirst($payment->payment_method) }}</td>
        </tr>
    </table>

    <div class="total-section">
        <span>TOTAL AMOUNT</span>
        <span>LKR {{ number_format($payment->amount) }}</span>
    </div>

    @if($payment->appointment && $payment->appointment->token_number)
    <div class="token-section">
        <div class="token-label">Your Token Number</div>
        <div class="token-number">{{ $payment->appointment->token_number }}</div>
    </div>
    @else
    <div class="token-section">
        <div class="token-label">Appointment ID</div>
        <div class="token-number">APT-{{ str_pad($payment->appointment_id, 3, '0', STR_PAD_LEFT) }}</div>
    </div>
    @endif

    <div class="footer">
        Thank you for choosing MedCare Clinic!<br>
        Please wait for your token number to be called.
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
