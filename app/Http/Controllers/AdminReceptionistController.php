<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Receptionist;
use Illuminate\Support\Facades\Hash;

class AdminReceptionistController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'phone'     => 'required|string|max:20',
            'shift'     => 'required|string|max:255',
            'password'  => 'required|string|min:6',
        ]);

        // Auto-generate unique Receptionist ID: REC-001, REC-002 …
        $lastRec = Receptionist::whereNotNull('receptionist_id')->orderByDesc('id')->first();
        if ($lastRec && preg_match('/REC-(\d+)/', $lastRec->receptionist_id, $m)) {
            $nextNum = intval($m[1]) + 1;
        } else {
            $nextNum = 1;
        }
        $receptionistId = 'REC-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'receptionist',
        ]);

        Receptionist::create([
            'receptionist_id' => $receptionistId,
            'user_id'         => $user->id,
            'full_name'       => $request->full_name,
            'phone'           => $request->phone,
            'shift'           => $request->shift,
        ]);

        return redirect()->back()->with('success', 'Receptionist added successfully');
    }

    public function update(Request $request, Receptionist $receptionist)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $receptionist->user_id,
            'phone' => 'required|string|max:20',
            'shift' => 'required|string|max:255',
        ]);

        $receptionist->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        if ($request->filled('password')) {
            $receptionist->user->update(['password' => Hash::make($request->password)]);
        }

        $receptionist->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'shift' => $request->shift,
        ]);

        return redirect()->back()->with('success', 'Receptionist updated successfully');
    }

    public function destroy(Receptionist $receptionist)
    {
        $receptionist->user->delete(); // Cascades and deletes the receptionist record
        return redirect()->back()->with('success', 'Receptionist deleted successfully');
    }
}
