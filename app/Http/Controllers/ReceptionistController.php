<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ReceptionistController extends Controller
{
    public function dashboard()
    {
        $today = \Carbon\Carbon::today();

        $todaysAppointments = \App\Models\Appointment::whereDate('appointment_date', $today)->count();
        $waitingPatients = \App\Models\Appointment::whereDate('appointment_date', $today)->where('status', 'pending')->count();
        $completedAppointments = \App\Models\Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $todaysRevenue = \App\Models\Payment::whereDate('created_at', $today)->where('status', 'paid')->sum('amount');

        $queue = \App\Models\Appointment::with(['patient.user', 'doctor.user'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_date', 'asc')
            ->get();

        return view('receptionist.dashboard', compact(
            'todaysAppointments',
            'waitingPatients',
            'completedAppointments',
            'todaysRevenue',
            'queue'
        ));
    }

    public function register()
    {
        return view('receptionist.register');
    }

    public function patients()
    {
        return view('receptionist.patients');
    }

    public function appointments()
    {
        return view('receptionist.appointments');
    }

    public function walkin()
    {
        return view('receptionist.walkin');
    }

    public function schedule()
    {
        return view('receptionist.schedule');
    }

    public function billing()
    {
        $recentPayments = \App\Models\Payment::with(['patient.user', 'appointment.doctor.user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $pendingAppointments = \App\Models\Appointment::with(['patient.user', 'doctor.user'])
            ->whereIn('status', ['scheduled', 'completed'])
            ->doesntHave('payment')
            ->orderBy('appointment_date', 'asc')
            ->get();

        $today = \Carbon\Carbon::today();

        $cashCollected = \App\Models\Payment::whereDate('created_at', $today)
            ->where('payment_method', 'cash')
            ->where('status', 'paid')
            ->sum('amount');

        $cardCollected = \App\Models\Payment::whereDate('created_at', $today)
            ->where('payment_method', 'card')
            ->where('status', 'paid')
            ->sum('amount');

        // Calculate exact pending payments based on the doctor's assigned service fee
        $pendingPaymentsCount = \App\Models\Appointment::with('doctor.service')
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->doesntHave('payment')
            ->get();

        $pendingPayments = $pendingPaymentsCount->sum(function ($appointment) {
            return $appointment->doctor && $appointment->doctor->service
                ? $appointment->doctor->service->fee
                : 1500; // fallback if no service is assigned
        });

        $totalRevenue = $cashCollected + $cardCollected;

        return view('receptionist.billing', compact(
            'recentPayments',
            'pendingAppointments',
            'cashCollected',
            'cardCollected',
            'pendingPayments',
            'totalRevenue'
        ));
    }

    public function storeBilling(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card',
        ]);

        $appointment = \App\Models\Appointment::findOrFail($request->appointment_id);

        // Generate unique invoice number (e.g. INV-001)
        $latestPayment = \App\Models\Payment::orderBy('id', 'desc')->first();
        $nextId = $latestPayment ? $latestPayment->id + 1 : 1;
        $invoiceNumber = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $payment = \App\Models\Payment::create([
            'invoice_number' => $invoiceNumber,
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'paid',
        ]);

        $appointment->update(['status' => 'confirmed']);

        return redirect()->route('receptionist.billing')->with('success', 'Payment successful and invoice generated!');
    }

    public function printReceipt(\App\Models\Payment $payment)
    {
        $payment->load(['patient.user', 'appointment.doctor.user']);
        return view('receptionist.receipt', compact('payment'));
    }

    public function notifications()
    {
        return view('receptionist.notifications');
    }

    public function profile()
    {
        return view('receptionist.profile');
    }

    /**
     * API: Returns all doctor schedules for a given week.
     * GET /api/reception/schedules?date=YYYY-MM-DD
     */
    public function schedulesApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $dateParam = $request->query('date', \Carbon\Carbon::today()->format('Y-m-d'));
        $startOfWeek = \Carbon\Carbon::parse($dateParam)->startOfWeek();
        $endOfWeek = \Carbon\Carbon::parse($dateParam)->endOfWeek();

        $schedules = \App\Models\Schedule::with('doctor.user')
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Assign a consistent color per doctor
        $palette = [
            ['bg' => 'rgba(37,99,235,0.1)', 'border' => '#2563EB', 'text' => '#2563EB'],
            ['bg' => 'rgba(20,184,166,0.1)', 'border' => '#14B8A6', 'text' => '#14B8A6'],
            ['bg' => 'rgba(147,51,234,0.1)', 'border' => '#9333EA', 'text' => '#9333EA'],
            ['bg' => 'rgba(249,115,22,0.1)', 'border' => '#F97316', 'text' => '#F97316'],
            ['bg' => 'rgba(16,185,129,0.1)', 'border' => '#10B981', 'text' => '#10B981'],
            ['bg' => 'rgba(239,68,68,0.1)', 'border' => '#EF4444', 'text' => '#EF4444'],
        ];

        $doctorColors = [];
        $colorIndex = 0;

        $formatted = $schedules->map(function ($s) use (&$doctorColors, &$colorIndex, $palette) {
            $doctorId = $s->doctor_id;
            if (!isset($doctorColors[$doctorId])) {
                $doctorColors[$doctorId] = $palette[$colorIndex % count($palette)];
                $colorIndex++;
            }
            $color = $doctorColors[$doctorId];

            // Count appointments already booked in this slot
            $bookedCount = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $s->date)
                ->where('status', '!=', 'cancelled')
                ->count();

            return [
                'id' => $s->id,
                'doctor_id' => $doctorId,
                'date' => $s->date,
                'doctor_name' => $s->doctor->user->name ?? 'Unknown',
                'start_time' => \Carbon\Carbon::parse($s->start_time)->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($s->end_time)->format('H:i'),
                'start_fmt' => \Carbon\Carbon::parse($s->start_time)->format('h:i A'),
                'end_fmt' => \Carbon\Carbon::parse($s->end_time)->format('h:i A'),
                'booked' => $bookedCount,
                'color' => $color,
            ];
        });

        return response()->json([
            'status' => 'success',
            'startOfWeek' => $startOfWeek->format('Y-m-d'),
            'endOfWeek' => $endOfWeek->format('Y-m-d'),
            'schedules' => $formatted,
        ]);
    }

    public function appointmentsApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $query = \App\Models\Appointment::with(['patient.user', 'doctor.user'])
            ->orderBy('appointment_date', 'desc');

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('doctor_id') && !empty($request->doctor_id)) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->get()->map(function ($apt) {
            $patientModel = $apt->patient;
            $patientUser = $patientModel ? $patientModel->user : null;
            $doctorModel = $apt->doctor;
            $doctorUser = $doctorModel ? $doctorModel->user : null;

            return [
                'id' => $apt->id,
                'patient_id' => $patientModel ? $patientModel->id : null,
                'apt_number' => 'APT-' . str_pad($apt->id, 3, '0', STR_PAD_LEFT),
                'token_number' => $apt->token_number,
                'patient_name' => $patientUser ? $patientUser->name : 'Unknown',
                'patient_phone' => $patientModel ? $patientModel->phone : 'N/A',
                'patient_id_label' => $patientModel ? 'PT-' . str_pad($patientModel->id, 3, '0', STR_PAD_LEFT) : 'N/A',
                'doctor_name' => $doctorUser ? $doctorUser->name : 'Unknown',
                'doctor_specialty' => $doctorModel ? $doctorModel->specialization : 'N/A',
                'appointment_date' => $apt->appointment_date,
                'time_label' => \Carbon\Carbon::parse($apt->appointment_date)->format('h:i A'),
                'status' => $apt->status,
            ];
        });

        $doctors = \App\Models\Doctor::with('user')->get()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->user->name ?? 'Unknown',
            ];
        });

        return response()->json([
            'status' => 'success',
            'appointments' => $appointments,
            'doctors' => $doctors,
        ]);
    }

    public function updateAppointmentStatus(Request $request, \App\Models\Appointment $appointment)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update(['status' => $request->status]);

        // If cancelled by receptionist, we could send a notification to doctor and patient.
        if ($request->status === 'cancelled') {
            \App\Models\Notification::create([
                'user_id' => $appointment->doctor->user_id,
                'type' => 'appointment_cancelled',
                'title' => 'Appointment Cancelled',
                'message' => "An appointment on {$appointment->appointment_date} has been cancelled by Receptionist.",
                'data' => ['appointment_id' => $appointment->id],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment status updated successfully.',
        ]);
    }

    public function bookWalkinApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $appointmentDateTime = $request->appointment_date . ' ' . $request->appointment_time;
        $dateOnly = \Carbon\Carbon::parse($appointmentDateTime)->format('Y-m-d');

        $maxToken = \App\Models\Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $dateOnly)
            ->max('token_number');

        $nextToken = $maxToken ? $maxToken + 1 : 1;

        $appointment = \App\Models\Appointment::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $appointmentDateTime,
            'status' => 'scheduled',
            'token_number' => $nextToken,
        ]);

        // Fire a notification to the doctor
        $doctor = \App\Models\Doctor::with('user')->find($request->doctor_id);
        $patient = \App\Models\Patient::find($request->patient_id);
        if ($doctor && $doctor->user) {
            \App\Models\Notification::create([
                'user_id' => $doctor->user->id,
                'type' => 'appointment_booked',
                'title' => 'Walk-in Appointment Added',
                'message' => "Receptionist booked a walk-in for {$patient->full_name} on " .
                    \Carbon\Carbon::parse($appointmentDateTime)->format('M d, Y \a\t h:i A') .
                    " (Token #{$nextToken}).",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'patient_name' => $patient->full_name,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Walk-in booked successfully!',
            'appointment' => $appointment,
        ]);
    }

    public function myProfileApi()
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $receptionist = \App\Models\Receptionist::where('user_id', $authUser->id)->first();

        return response()->json([
            'status' => 'success',
            'user' => $authUser,
            'receptionist' => $receptionist,
        ]);
    }

    /**
     * API: Get all notifications for the logged-in receptionist.
     * GET /api/reception/notifications
     */
    public function notificationsApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $notifications = \App\Models\Notification::where('user_id', $authUser->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'data' => $n->data,
                    'is_unread' => is_null($n->read_at),
                    'created_at' => $n->created_at,
                    'time_ago' => $n->created_at->diffForHumans(),
                ];
            });

        $unreadCount = $notifications->where('is_unread', true)->count();

        return response()->json([
            'status' => 'success',
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * API: Mark a single notification as read.
     * PUT /api/reception/notifications/{notification}/read
     */
    public function markNotificationRead(Request $request, \App\Models\Notification $notification)
    {
        $authUser = auth('api')->user();
        if (!$authUser || $notification->user_id !== $authUser->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    /**
     * API: Mark all notifications as read for the logged-in receptionist.
     * PUT /api/reception/notifications/mark-all-read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        \App\Models\Notification::where('user_id', $authUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function updateProfileApi(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $user->update([
            'name' => $request->name,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);
        }

        \App\Models\Receptionist::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Profile updated successfully!']);
    }
}

