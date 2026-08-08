<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;

class AdminDoctorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'full_name'      => 'nullable|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'phone'          => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'service_id'     => 'required|exists:services,id',
            'qualifications' => 'required|string|max:255',
            'password'       => 'required|string|min:6',
        ]);

        // Auto-generate unique Doctor ID: DOC-001, DOC-002 …
        $lastDoctor = Doctor::whereNotNull('doctor_id')->orderByDesc('id')->first();
        if ($lastDoctor && preg_match('/DOC-(\d+)/', $lastDoctor->doctor_id, $m)) {
            $nextNum = intval($m[1]) + 1;
        } else {
            $nextNum = 1;
        }
        $doctorId = 'DOC-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'doctor',
        ]);

        Doctor::create([
            'doctor_id'      => $doctorId,
            'user_id'        => $user->id,
            'full_name'      => $request->full_name,
            'phone'          => $request->phone,
            'specialization' => $request->specialization,
            'service_id'     => $request->service_id,
            'qualifications' => $request->qualifications,
        ]);

        return redirect()->back()->with('success', 'Doctor added successfully');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $doctor->user_id,
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'qualifications' => 'required|string|max:255',
        ]);

        $doctor->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        if ($request->filled('password')) {
            $doctor->user->update(['password' => Hash::make($request->password)]);
        }

        $doctor->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'specialization' => $request->specialization,
            'service_id' => $request->service_id,
            'qualifications' => $request->qualifications,
        ]);

        return redirect()->back()->with('success', 'Doctor updated successfully');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete(); // This will cascade and delete the doctor record
        return redirect()->back()->with('success', 'Doctor deleted successfully');
    }
}
