<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dashboard', function () {
            return response()->json(['message' => 'Admin Dashboard']);
        });
        // We would add more routes here
    });

    // Receptionist Routes
    Route::middleware('role:receptionist,admin')->prefix('reception')->group(function () {
        Route::get('dashboard', function () {
            return response()->json(['message' => 'Reception Dashboard']);
        });
        Route::get('patients', [PatientController::class, 'patientsApi']);
        Route::post('patients', [PatientController::class, 'store']);
        Route::put('patients/{patient}', [PatientController::class, 'updateByReceptionist']);
        Route::get('schedules', [\App\Http\Controllers\ReceptionistController::class, 'schedulesApi']);
        Route::get('appointments', [\App\Http\Controllers\ReceptionistController::class, 'appointmentsApi']);
        Route::put('appointments/{appointment}/status', [\App\Http\Controllers\ReceptionistController::class, 'updateAppointmentStatus']);
        Route::post('book-walkin', [\App\Http\Controllers\ReceptionistController::class, 'bookWalkinApi']);
        Route::get('my-profile', [\App\Http\Controllers\ReceptionistController::class, 'myProfileApi']);
        Route::put('update-profile', [\App\Http\Controllers\ReceptionistController::class, 'updateProfileApi']);
        Route::get('notifications', [\App\Http\Controllers\ReceptionistController::class, 'notificationsApi']);
        Route::put('notifications/{notification}/read', [\App\Http\Controllers\ReceptionistController::class, 'markNotificationRead']);
        Route::put('notifications/mark-all-read', [\App\Http\Controllers\ReceptionistController::class, 'markAllNotificationsRead']);
    });

    // Doctor Routes
    Route::middleware('role:doctor,admin')->prefix('doctor')->group(function () {
        Route::get('dashboard', function () {
            return response()->json(['message' => 'Doctor Dashboard']);
        });
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('today-appointments', [DoctorController::class, 'todayAppointmentsApi']);
        Route::put('appointments/{appointment}/status', [DoctorController::class, 'updateAppointmentStatus']);
        Route::get('my-schedules', [DoctorController::class, 'mySchedulesApi']);
        Route::get('my-profile', [DoctorController::class, 'myProfileApi']);
        Route::put('update-profile', [DoctorController::class, 'updateProfileApi']);
        Route::get('notifications', [DoctorController::class, 'notificationsApi']);
        Route::put('notifications/{notification}/read', [DoctorController::class, 'markNotificationRead']);
        Route::put('notifications/mark-all-read', [DoctorController::class, 'markAllNotificationsRead']);
    });

    // Patient Routes
    Route::middleware('role:patient,admin')->prefix('patient')->group(function () {
        Route::get('dashboard', [PatientController::class, 'dashboardApi']);
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('records', [PatientController::class, 'recordsApi']);
        Route::get('prescriptions', [PatientController::class, 'prescriptionsApi']);
        Route::post('book-appointment', [PatientController::class, 'storeAppointment']);
        Route::get('my-appointments', [PatientController::class, 'myAppointmentsApi']);
        Route::get('my-profile', [PatientController::class, 'myProfileApi']);
        Route::put('update-profile', [PatientController::class, 'updateProfile']);
        Route::get('notifications', [PatientController::class, 'notificationsApi']);
        Route::put('notifications/{notification}/read', [PatientController::class, 'markNotificationRead']);
        Route::put('notifications/mark-all-read', [PatientController::class, 'markAllNotificationsRead']);
    });
});
