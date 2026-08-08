<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorController extends Controller
{
    public function dashboard()
    {
        return view('doctor.dashboard');
    }

    public function appointments()
    {
        return view('doctor.appointments');
    }

    public function patients(Request $request)
    {
        $patients = \App\Models\Patient::with('user')->get();
        $selectedPatient = null;
        $consultationHistory = collect();

        if ($request->has('patient_id') && !empty($request->patient_id)) {
            $selectedPatient = \App\Models\Patient::with('user')->find($request->patient_id);
            if ($selectedPatient) {
                // Fetch completed appointments with consultations for this patient
                $consultationHistory = Appointment::with(['doctor.user', 'consultation'])
                    ->where('patient_id', $selectedPatient->id)
                    ->where('status', 'completed')
                    ->whereHas('consultation')
                    ->orderBy('appointment_date', 'desc')
                    ->get();
            }
        }

        return view('doctor.patients', compact('patients', 'selectedPatient', 'consultationHistory'));
    }

    public function consultation(Request $request)
    {
        $appointmentId = $request->query('appointment_id');
        
        if (!$appointmentId) {
            $appointment = Appointment::with(['patient.user', 'consultation'])
                ->where('doctor_id', auth()->user()->doctor->id ?? null)
                ->where('status', 'scheduled')
                ->whereDate('appointment_date', Carbon::today())
                ->orderBy('appointment_date', 'asc')
                ->first();
                
            if (!$appointment) {
                return redirect()->route('doctor.dashboard')->with('error', 'No active or upcoming consultations for today. Please select a patient from the dashboard.');
            }
        } else {
            $appointment = Appointment::with(['patient.user', 'consultation'])->findOrFail($appointmentId);
        }
        
        $pastAppointments = Appointment::with('doctor.user')
            ->where('patient_id', $appointment->patient_id)
            ->where('id', '!=', $appointment->id)
            ->where('status', 'completed')
            ->orderBy('appointment_date', 'desc')
            ->take(5)
            ->get();

        return view('doctor.consultation', compact('appointment', 'pastAppointments'));
    }

    public function storeConsultation(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'action' => 'required|in:save,complete',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);

        $consultation = \App\Models\Consultation::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $request->only([
                'blood_pressure',
                'heart_rate',
                'temperature',
                'weight',
                'presenting_symptoms',
                'clinical_diagnosis',
                'treatment_plan',
            ])
        );

        if ($request->action === 'complete') {
            $appointment->update(['status' => 'completed']);
            return redirect()->route('doctor.dashboard')->with('success', 'Consultation completed successfully.');
        }

        return redirect()->back()->with('success', 'Consultation draft saved.');
    }

    public function prescriptions(Request $request)
    {
        $appointmentId = $request->query('appointment_id');
        if (!$appointmentId) {
            return redirect()->route('doctor.dashboard')->with('error', 'No appointment selected for prescriptions.');
        }

        $appointment = Appointment::with(['patient.user', 'prescriptions'])->findOrFail($appointmentId);

        return view('doctor.prescriptions', compact('appointment'));
    }

    public function storePrescription(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'medicine' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        \App\Models\Prescription::create($request->all());

        return redirect()->back()->with('success', 'Prescription added successfully.');
    }

    public function destroyPrescription(\App\Models\Prescription $prescription)
    {
        $prescription->delete();
        return redirect()->back()->with('success', 'Prescription removed.');
    }

    public function schedule()
    {
        return view('doctor.schedule');
    }

    public function notifications()
    {
        return view('doctor.notifications');
    }

    public function profile()
    {
        return view('doctor.profile');
    }

    /**
     * API: Returns today's (or selected date's) appointments for the logged-in doctor.
     * GET /api/doctor/today-appointments?date=YYYY-MM-DD
     */
    public function todayAppointmentsApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $doctor = Doctor::where('user_id', $authUser->id)->first();
        if (!$doctor) {
            return response()->json(['status' => 'error', 'message' => 'Doctor profile not found.'], 404);
        }

        // Accept optional ?date= param; default to today
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $appointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->orderBy('appointment_date', 'asc')
            ->get()
            ->map(function ($apt) {
                $patientUser    = $apt->patient->user ?? null;
                $patientModel   = $apt->patient ?? null;

                // Calculate age from patient dob if available
                $age = null;
                if ($patientModel && $patientModel->dob) {
                    $age = Carbon::parse($patientModel->dob)->age;
                }

                return [
                    'id'               => $apt->id,
                    'apt_number'       => 'APT-' . str_pad($apt->id, 3, '0', STR_PAD_LEFT),
                    'patient_name'     => $patientUser ? $patientUser->name : 'Unknown',
                    'patient_id_label' => $patientModel ? 'PT-' . str_pad($patientModel->id, 3, '0', STR_PAD_LEFT) : 'N/A',
                    'age'              => $age,
                    'appointment_date' => $apt->appointment_date,
                    'time_label'       => Carbon::parse($apt->appointment_date)->format('h:i A'),
                    'token_number'     => $apt->token_number,
                    'status'           => $apt->status,
                    'notes'            => $apt->notes,
                ];
            });

        $totalPatientsTreated = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'status'                 => 'success',
            'date'                   => $date,
            'appointments'           => $appointments,
            'total'                  => $appointments->count(),
            'total_patients_treated' => $totalPatientsTreated,
        ]);
    }

    /**
     * API: Update an appointment's status (e.g. scheduled → completed / cancelled).
     * PUT /api/doctor/appointments/{appointment}/status
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $doctor = Doctor::where('user_id', $authUser->id)->first();
        if (!$doctor || $appointment->doctor_id !== $doctor->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Appointment status updated.',
        ]);
    }

    /**
     * API: Returns schedules for the logged-in doctor for a given week.
     * GET /api/doctor/my-schedules?date=YYYY-MM-DD
     */
    public function mySchedulesApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $doctor = Doctor::where('user_id', $authUser->id)->first();
        if (!$doctor) {
            return response()->json(['status' => 'error', 'message' => 'Doctor profile not found.'], 404);
        }

        $dateParam = $request->query('date', Carbon::today()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($dateParam)->startOfWeek();
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->endOfWeek();

        $schedules = \App\Models\Schedule::where('doctor_id', $doctor->id)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'status'       => 'success',
            'startOfWeek'  => $startOfWeek->format('Y-m-d'),
            'endOfWeek'    => $endOfWeek->format('Y-m-d'),
            'schedules'    => $schedules,
        ]);
    }

    /**
     * API: returns current doctor's profile as JSON.
     * GET /api/doctor/my-profile
     */
    public function myProfileApi(Request $request)
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $doctor = Doctor::where('user_id', $authUser->id)->first();

        return response()->json([
            'status' => 'success',
            'user'   => $authUser,
            'doctor' => $doctor,
        ]);
    }

    /**
     * API: updates current doctor's profile.
     * PUT /api/doctor/update-profile
     */
    public function updateProfileApi(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string|max:255',
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

        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor) {
            $doctor->update([
                'phone'          => $request->phone,
                'specialization' => $request->specialization,
                'qualifications' => $request->qualifications,
                // full_name might be kept in sync with name or kept separate
                'full_name'      => $request->name, 
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully!',
            'user'    => $user,
            'doctor'  => $doctor,
        ]);
    }

    /**
     * API: Get all notifications for the logged-in doctor.
     * GET /api/doctor/notifications
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
                    'id'         => $n->id,
                    'type'       => $n->type,
                    'title'      => $n->title,
                    'message'    => $n->message,
                    'data'       => $n->data,
                    'is_unread'  => is_null($n->read_at),
                    'created_at' => $n->created_at,
                    'time_ago'   => $n->created_at->diffForHumans(),
                ];
            });

        $unreadCount = $notifications->where('is_unread', true)->count();

        return response()->json([
            'status'        => 'success',
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * API: Mark a single notification as read.
     * PUT /api/doctor/notifications/{notification}/read
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
     * API: Mark all notifications as read for the logged-in doctor.
     * PUT /api/doctor/notifications/mark-all-read
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

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read.']);
    }
}


