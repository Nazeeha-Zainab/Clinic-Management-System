<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PatientController extends Controller
{
    public function dashboard()
    {
        return view('patient.dashboard');
    }

    public function dashboardApi(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $patient = $user->patient;
        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found.'], 404);
        }

        // Stats
        $nextAppointment = \App\Models\Appointment::with('doctor.user')
            ->where('patient_id', $patient->id)
            ->where('status', 'scheduled')
            ->where('appointment_date', '>=', now()->format('Y-m-d'))
            ->orderBy('appointment_date', 'asc')
            ->first();

        $totalAppointments = \App\Models\Appointment::where('patient_id', $patient->id)->count();
        $completedVisits = \App\Models\Appointment::where('patient_id', $patient->id)
            ->where('status', 'completed')->count();

        // Count appointments with prescriptions
        $activePrescriptions = \App\Models\Appointment::where('patient_id', $patient->id)
            ->whereHas('prescriptions')->count();

        // Recent Activity (Completed appointments, recent notifications)
        $recentActivity = \App\Models\Appointment::with('doctor.user')
            ->where('patient_id', $patient->id)
            ->where('status', 'completed')
            ->orderBy('appointment_date', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'next_appointment' => $nextAppointment,
            'stats' => [
                'total_appointments' => $totalAppointments,
                'completed_visits' => $completedVisits,
                'active_prescriptions' => $activePrescriptions
            ],
            'recent_activity' => $recentActivity
        ]);
    }

    public function book()
    {
        $doctors = \App\Models\Doctor::with('user')->get();
        return view('patient.book', compact('doctors'));
    }

    public function getDoctorSchedules($doctorId)
    {
        $schedules = \App\Models\Schedule::where('doctor_id', $doctorId)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $availableBlocks = [];

        foreach ($schedules as $schedule) {
            $date = $schedule->date;
            $start = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
            $end = \Carbon\Carbon::parse($schedule->end_time)->format('H:i');

            if (!isset($availableBlocks[$date])) {
                $availableBlocks[$date] = [];
            }

            $availableBlocks[$date][] = [
                'start' => $start,
                'end' => $end,
                'label' => \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('h:i A')
            ];
        }

        return response()->json($availableBlocks);
    }

    public function storeAppointment(Request $request)
    {
        // Use API guard since this is called via JWT-authenticated fetch
        $authUser = auth('api')->user();

        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $patient = null;
        if (in_array($authUser->role, ['receptionist', 'admin']) && $request->has('patient_id')) {
            $patient = \App\Models\Patient::find($request->patient_id);
        } else {
            $patient = \App\Models\Patient::where('user_id', $authUser->id)->first();
        }

        if (!$patient) {
            return response()->json(['status' => 'error', 'message' => 'Patient profile not found. Please complete your profile.'], 422);
        }

        $appointmentDateTime = $request->appointment_date . ' ' . $request->appointment_time;
        $dateOnly = \Carbon\Carbon::parse($appointmentDateTime)->format('Y-m-d');

        $maxToken = \App\Models\Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $dateOnly)
            ->max('token_number');

        $nextToken = $maxToken ? $maxToken + 1 : 1;

        $appointment = \App\Models\Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $appointmentDateTime,
            'status' => 'scheduled',
            'token_number' => $nextToken,
        ]);

        if ($request->has('edit_apt_id')) {
            \App\Models\Appointment::where('id', $request->edit_apt_id)->delete();
        }

        // Fire a notification to the doctor
        $doctor = \App\Models\Doctor::with('user')->find($request->doctor_id);
        if ($doctor && $doctor->user) {
            \App\Models\Notification::create([
                'user_id' => $doctor->user->id,
                'type' => 'appointment_booked',
                'title' => 'New Appointment Booked',
                'message' => "Patient {$authUser->name} booked an appointment on " .
                    \Carbon\Carbon::parse($appointmentDateTime)->format('M d, Y \a\t h:i A') .
                    " (Token #{$nextToken}).",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'patient_name' => $authUser->name,
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment booked successfully!',
            'appointment' => $appointment,
        ]);
    }

    /**
     * My Appointments page — rendered freely; auth handled by patient.js on the client.
     * Appointments are fetched via GET /api/patient/my-appointments using the JWT token.
     */
    public function appointments()
    {
        return view('patient.appointments');
    }

    /**
     * API: returns current patient's appointments as JSON (auth:api guard).
     */
    public function myAppointmentsApi()
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $patient = \App\Models\Patient::where('user_id', $authUser->id)->first();
        if (!$patient) {
            return response()->json(['status' => 'success', 'appointments' => []]);
        }

        $appointments = \App\Models\Appointment::with('doctor.user')
            ->where('patient_id', $patient->id)
            ->orderBy('appointment_date', 'desc')
            ->get()
            ->map(function ($apt) {
                return [
                    'id' => $apt->id,
                    'apt_number' => 'APT-' . str_pad($apt->id, 3, '0', STR_PAD_LEFT),
                    'doctor_name' => $apt->doctor->user->name ?? 'Unknown',
                    'specialization' => $apt->doctor->specialization ?? '',
                    'appointment_date' => $apt->appointment_date,
                    'token_number' => $apt->token_number,
                    'status' => $apt->status,
                ];
            });

        return response()->json(['status' => 'success', 'appointments' => $appointments]);
    }

    /**
     * API: Cancel an appointment by the patient
     * PUT /api/patient/appointments/{appointment}/cancel
     */
    public function cancelAppointmentApi(Request $request, \App\Models\Appointment $appointment)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $patient = \App\Models\Patient::where('user_id', $authUser->id)->first();
        if (!$patient || $appointment->patient_id !== $patient->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        if ($appointment->status === 'cancelled' || $appointment->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Cannot cancel this appointment.'], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json(['status' => 'success', 'message' => 'Appointment cancelled successfully.']);
    }

    /**
     * API: returns current patient's profile as JSON (auth:api guard).
     */
    public function myProfileApi()
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $patient = \App\Models\Patient::where('user_id', $authUser->id)->first();

        return response()->json([
            'status' => 'success',
            'user' => $authUser,
            'patient' => $patient,
        ]);
    }

    public function records()
    {
        return view('patient.records');
    }

    public function recordsApi(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $patient = $user->patient;
        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found.'], 404);
        }

        $appointments = \App\Models\Appointment::with(['doctor.user', 'consultation', 'prescriptions'])
            ->where('patient_id', $patient->id)
            ->where('status', 'completed')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'patient' => $patient,
            'appointments' => $appointments
        ]);
    }

    public function prescriptions()
    {
        return view('patient.prescriptions');
    }

    public function prescriptionsApi(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $patient = $user->patient;
        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found.'], 404);
        }

        $appointments = \App\Models\Appointment::with(['doctor.user', 'prescriptions'])
            ->where('patient_id', $patient->id)
            ->whereHas('prescriptions') // Only get appointments with prescriptions
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }

    public function notifications()
    {
        return view('patient.notifications');
    }

    public function profile()
    {
        return view('patient.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        $patient = \App\Models\Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json(['status' => 'error', 'message' => 'Patient record not found.'], 404);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
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

        $patient->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'address' => $request->address,
        ]);

        $user->update(['name' => $request->full_name]);

        if ($request->filled('password')) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Profile updated successfully!']);
    }

    public function updateByReceptionist(Request $request, \App\Models\Patient $patient)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        $patient->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'address' => $request->address,
        ]);

        if ($patient->user) {
            $patient->user->update(['name' => $request->full_name]);
        }

        return response()->json(['status' => 'success', 'message' => 'Patient updated successfully']);
    }

    /**
     * API: list all patients for the receptionist.
     * GET /api/reception/patients
     */
    public function patientsApi()
    {
        $patients = \App\Models\Patient::with([
            'user',
            'appointments' => function ($q) {
                $q->latest()->limit(1);
            }
        ])
            ->get()
            ->map(function ($p) {
                $lastApt = $p->appointments->first();
                return [
                    'id' => $p->id,
                    'patient_id' => 'PT-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
                    'full_name' => $p->full_name ?: ($p->user->name ?? 'N/A'),
                    'email' => $p->user->email ?? '',
                    'phone' => $p->phone ?? '',
                    'gender' => $p->gender ?? '',
                    'dob' => $p->dob,
                    'address' => $p->address ?? '',
                    'last_visit' => $lastApt ? \Carbon\Carbon::parse($lastApt->appointment_date)->format('M d, Y') : null,
                    'last_status' => $lastApt ? ucfirst($lastApt->status) : null,
                    'registered' => $p->created_at->format('M d, Y'),
                ];
            });

        return response()->json(['status' => 'success', 'patients' => $patients]);
    }

    /**
     * API: register a new patient by the receptionist.
     * POST /api/reception/patients
     * Creates a User account + Patient record. The patient can later set their own password.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        // Create the user account (default password = phone number)
        $user = \App\Models\User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->phone),
            'role' => 'patient',
        ]);

        // Create the patient record
        $patient = \App\Models\Patient::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'address' => $request->address,
            'medical_history' => $request->medical_notes ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Patient registered successfully!',
            'patient' => [
                'id' => $patient->id,
                'patient_id' => 'PT-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT),
                'full_name' => $patient->full_name,
            ],
        ], 201);
    }

    /**
     * API: Get all notifications for the logged-in patient.
     * GET /api/patient/notifications
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
     * PUT /api/patient/notifications/{notification}/read
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
     * API: Mark all notifications as read for the logged-in patient.
     * PUT /api/patient/notifications/mark-all-read
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
}

