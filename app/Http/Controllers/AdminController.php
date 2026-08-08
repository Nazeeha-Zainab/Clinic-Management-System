<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Basic Stats
        $totalPatients = \App\Models\Patient::count();
        $totalDoctors = \App\Models\Doctor::count();
        $todayAppointments = \App\Models\Appointment::whereDate('appointment_date', today())->count();
        
        $todayCompleted = \App\Models\Appointment::whereDate('appointment_date', today())
                                                 ->where('status', 'completed')
                                                 ->count();
        $todayRevenue = $todayCompleted * 2500; // Mock 2500 LKR per appointment

        // Appointment Statistics (Last 7 Days)
        $last7Days = collect(range(6, 0))->map(fn($i) => today()->subDays($i));
        $apptStatsLabels = $last7Days->map->format('D')->toArray();
        
        $completedData = [];
        $cancelledData = [];
        foreach ($last7Days as $day) {
            $completedData[] = \App\Models\Appointment::whereDate('appointment_date', $day)
                ->where('status', 'completed')->count();
            $cancelledData[] = \App\Models\Appointment::whereDate('appointment_date', $day)
                ->where('status', 'cancelled')->count();
        }

        // Doctor Workload (Top 3 + Others)
        $workload = \App\Models\Appointment::selectRaw('doctor_id, count(*) as count')
            ->groupBy('doctor_id')
            ->orderByDesc('count')
            ->with('doctor.user')
            ->get();
            
        $docLabels = [];
        $docData = [];
        $otherCount = 0;
        
        foreach ($workload->values() as $index => $item) {
            if ($index < 3) {
                $docLabels[] = $item->doctor->user->name ?? 'Unknown';
                $docData[] = $item->count;
            } else {
                $otherCount += $item->count;
            }
        }
        
        if ($otherCount > 0) {
            $docLabels[] = 'Others';
            $docData[] = $otherCount;
        }
        
        // If no data
        if (empty($docLabels)) {
            $docLabels = ['No Data'];
            $docData = [1];
        }

        // Monthly Revenue Trend (Last 6 Months)
        $last6Months = collect(range(5, 0))->map(fn($i) => today()->startOfMonth()->subMonths($i));
        $revLabels = $last6Months->map->format('M')->toArray();
        $revData = [];
        
        foreach ($last6Months as $month) {
            $count = \App\Models\Appointment::whereYear('appointment_date', $month->year)
                ->whereMonth('appointment_date', $month->month)
                ->where('status', 'completed')
                ->count();
            $revData[] = $count * 2500;
        }

        return view('admin.dashboard', compact(
            'totalPatients', 'totalDoctors', 'todayAppointments', 'todayRevenue',
            'apptStatsLabels', 'completedData', 'cancelledData',
            'docLabels', 'docData',
            'revLabels', 'revData'
        ));
    }

    public function toggleUserStatus(\App\Models\User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'status' => 'success',
            'is_active' => $user->is_active,
            'message' => 'User status updated successfully.'
        ]);
    }

    public function doctors()
    {
        $doctors = \App\Models\Doctor::with('user', 'service')->get();
        $services = \App\Models\Service::all();
        return view('admin.doctors', compact('doctors', 'services'));
    }

    public function receptionists()
    {
        $receptionists = \App\Models\Receptionist::with('user')->get();
        return view('admin.receptionists', compact('receptionists'));
    }

    public function patients()
    {
        $patients = \App\Models\Patient::with('user')->get();
        return view('admin.patients', compact('patients'));
    }

    public function updatePatient(Request $request, \App\Models\Patient $patient)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $patient->user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string'
        ]);

        $patient->user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        $patient->update([
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'medical_history' => $request->medical_history
        ]);

        return redirect()->route('admin.patients')->with('success', 'Patient updated successfully.');
    }

    public function destroyPatient(\App\Models\Patient $patient)
    {
        if ($patient->user) {
            $patient->user->delete(); // This cascades and deletes the patient record
        } else {
            $patient->delete();
        }
        
        return redirect()->route('admin.patients')->with('success', 'Patient deleted successfully.');
    }

    public function services()
    {
        $services = \App\Models\Service::all();
        return view('admin.services', compact('services'));
    }

    public function schedules()
    {
        $weekParam = request()->query('week', now()->format('Y-m-d'));
        $startOfWeek = \Carbon\Carbon::parse($weekParam)->startOfWeek();
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->endOfWeek();

        $schedules = \App\Models\Schedule::with('doctor.user')
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();
        $doctors = \App\Models\Doctor::with('user')->get();
        
        $prevWeek = (clone $startOfWeek)->subWeek()->format('Y-m-d');
        $nextWeek = (clone $startOfWeek)->addWeek()->format('Y-m-d');

        return view('admin.schedules', compact('schedules', 'doctors', 'startOfWeek', 'endOfWeek', 'prevWeek', 'nextWeek'));
    }

    public function appointments(Request $request)
    {
        $query = \App\Models\Appointment::with(['patient.user', 'doctor.user']);

        if ($request->filled('status') && $request->status !== 'All Statuses') {
            $query->where('status', strtolower($request->status));
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('doctor.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->get();
        $doctors = \App\Models\Doctor::with('user')->get();
            
        return view('admin.appointments', compact('appointments', 'doctors'));
    }

    public function updateAppointment(Request $request, \App\Models\Appointment $appointment)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'status' => 'required|in:scheduled,completed,cancelled,pending,confirmed',
            'notes' => 'nullable|string'
        ]);

        $appointment->update([
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.appointments')->with('success', 'Appointment updated successfully.');
    }

    public function cancelAppointment(\App\Models\Appointment $appointment)
    {
        $appointment->load('patient.user', 'doctor.user');
        $appointment->update(['status' => 'cancelled']);

        // Notify the doctor
        if ($appointment->doctor && $appointment->doctor->user) {
            \App\Models\Notification::create([
                'user_id' => $appointment->doctor->user->id,
                'type'    => 'appointment_cancelled',
                'title'   => 'Appointment Cancelled',
                'message' => "The appointment with patient " .
                             ($appointment->patient->user->name ?? 'Unknown') .
                             " on " .
                             \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y \a\t h:i A') .
                             " has been cancelled.",
                'data'    => ['appointment_id' => $appointment->id],
            ]);
        }

        return redirect()->route('admin.appointments')->with('success', 'Appointment cancelled successfully.');
    }

    public function reports(Request $request)
    {
        $dateFilter = $request->query('date_filter', 'last_30_days');
        
        if ($dateFilter === 'custom') {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->query('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->query('end_date'))->endOfDay() : now()->endOfDay();
        } else {
            $startDate = match ($dateFilter) {
                'last_7_days' => now()->subDays(7)->startOfDay(),
                'this_month' => now()->startOfMonth(),
                'this_year' => now()->startOfYear(),
                default => now()->subDays(30)->startOfDay(), // last_30_days
            };
            $endDate = now()->endOfDay();
        }

        // KPIs
        $totalAppointments = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count();
        $newPatients = \App\Models\Patient::whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Mock revenue calculation (Completed Appointments * 2500)
        $completedAppointments = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed')->count();
        $totalRevenue = collect(\App\Models\Payment::whereBetween('created_at', [$startDate, $endDate])->get())->sum('amount') 
                        ?: ($completedAppointments * 2500); // Fallback to mock if payments not fully implemented
                        
        $cancellations = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'cancelled')->count();

        // Revenue Trend Chart (Group by Date)
        $trendDates = collect();
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $trendDates->push($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }
        
        $revenueTrendLabels = $trendDates->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray();
        $revenueTrendData = [];
        foreach ($trendDates as $date) {
            $dailyCompleted = \App\Models\Appointment::whereDate('appointment_date', $date)
                ->where('status', 'completed')->count();
            $dailyRevenue = \App\Models\Payment::whereDate('created_at', $date)->sum('amount') 
                            ?: ($dailyCompleted * 2500);
            $revenueTrendData[] = $dailyRevenue;
        }

        // Appointment Status Breakdown
        $scheduledCount = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['scheduled', 'pending', 'confirmed'])->count();
        $statusLabels = ['Completed', 'Scheduled', 'Cancelled'];
        $statusData = [$completedAppointments, $scheduledCount, $cancellations];

        // Appointments by Doctor
        $doctorWorkload = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->selectRaw('doctor_id, count(*) as count')
            ->groupBy('doctor_id')
            ->with('doctor.user')
            ->get();
            
        $doctorLabels = [];
        $doctorData = [];
        foreach ($doctorWorkload as $workload) {
            $doctorLabels[] = $workload->doctor->user->name ?? 'Unknown';
            $doctorData[] = $workload->count;
        }

        return view('admin.reports', compact(
            'dateFilter', 'totalAppointments', 'newPatients', 'totalRevenue', 'cancellations',
            'revenueTrendLabels', 'revenueTrendData',
            'statusLabels', 'statusData',
            'doctorLabels', 'doctorData'
        ));
    }

    public function exportReport(Request $request)
    {
        $dateFilter = $request->query('date_filter', 'last_30_days');
        $type = $request->query('type');
        
        if ($dateFilter === 'custom') {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->query('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->query('end_date'))->endOfDay() : now()->endOfDay();
        } else {
            $startDate = match ($dateFilter) {
                'last_7_days' => now()->subDays(7)->startOfDay(),
                'this_month' => now()->startOfMonth(),
                'this_year' => now()->startOfYear(),
                default => now()->subDays(30)->startOfDay(),
            };
            $endDate = now()->endOfDay();
        }
        
        $reportData = [];

        if ($type === 'financial') {
            $trendDates = collect();
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $trendDates->push($currentDate->format('Y-m-d'));
                $currentDate->addDay();
            }
            
            $totalRevenue = 0;
            foreach ($trendDates as $date) {
                $dailyCompleted = \App\Models\Appointment::whereDate('appointment_date', $date)->where('status', 'completed')->count();
                $dailyRevenue = \App\Models\Payment::whereDate('created_at', $date)->sum('amount') ?: ($dailyCompleted * 2500);
                $totalRevenue += $dailyRevenue;
                
                $reportData[] = [
                    'date' => \Carbon\Carbon::parse($date)->format('M d, Y'),
                    'appointments' => $dailyCompleted,
                    'revenue' => $dailyRevenue
                ];
            }
            return view('admin.reports_export', compact('type', 'dateFilter', 'startDate', 'endDate', 'reportData', 'totalRevenue'));
        } 
        
        elseif ($type === 'appointments') {
            $appointments = \App\Models\Appointment::with(['patient.user', 'doctor.user'])
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->orderBy('appointment_date', 'asc')
                ->get();
            return view('admin.reports_export', compact('type', 'dateFilter', 'startDate', 'endDate', 'appointments'));
        }
        
        elseif ($type === 'doctors') {
            $doctorWorkload = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                ->selectRaw('doctor_id, count(*) as total_appointments, sum(case when status="completed" then 1 else 0 end) as completed, sum(case when status="cancelled" then 1 else 0 end) as cancelled')
                ->groupBy('doctor_id')
                ->with('doctor.user')
                ->get();
            return view('admin.reports_export', compact('type', 'dateFilter', 'startDate', 'endDate', 'doctorWorkload'));
        }
        
        elseif ($type === 'patients') {
            $newPatients = \App\Models\Patient::with('user')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'asc')
                ->get();
            return view('admin.reports_export', compact('type', 'dateFilter', 'startDate', 'endDate', 'newPatients'));
        }

        return redirect()->route('admin.reports')->with('error', 'Invalid report type requested.');
    }

    public function settings()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'primary_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        foreach ($data as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully.');
    }
}
