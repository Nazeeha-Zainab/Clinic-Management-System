<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard', function () {
    return view('app');
});

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PatientController;

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
    Route::get('/doctors', [AdminController::class, 'doctors'])->name('admin.doctors');
    Route::post('/doctors', [\App\Http\Controllers\AdminDoctorController::class, 'store'])->name('admin.doctors.store');
    Route::put('/doctors/{doctor}', [\App\Http\Controllers\AdminDoctorController::class, 'update'])->name('admin.doctors.update');
    Route::delete('/doctors/{doctor}', [\App\Http\Controllers\AdminDoctorController::class, 'destroy'])->name('admin.doctors.destroy');
    Route::get('/receptionists', [AdminController::class, 'receptionists'])->name('admin.receptionists');
    Route::post('/receptionists', [\App\Http\Controllers\AdminReceptionistController::class, 'store'])->name('admin.receptionists.store');
    Route::put('/receptionists/{receptionist}', [\App\Http\Controllers\AdminReceptionistController::class, 'update'])->name('admin.receptionists.update');
    Route::delete('/receptionists/{receptionist}', [\App\Http\Controllers\AdminReceptionistController::class, 'destroy'])->name('admin.receptionists.destroy');
    Route::get('/patients', [AdminController::class, 'patients'])->name('admin.patients');
    Route::put('/patients/{patient}', [AdminController::class, 'updatePatient'])->name('admin.patients.update');
    Route::delete('/patients/{patient}', [AdminController::class, 'destroyPatient'])->name('admin.patients.destroy');
    Route::get('/services', [AdminController::class, 'services'])->name('admin.services');
    Route::post('/services', [\App\Http\Controllers\ServiceController::class, 'store'])->name('admin.services.store');
    Route::put('/services/{service}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/services/{service}', [\App\Http\Controllers\ServiceController::class, 'destroy'])->name('admin.services.destroy');
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('admin.schedules');
    Route::post('/schedules', [\App\Http\Controllers\ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::put('/schedules/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('admin.appointments');
    Route::put('/appointments/{appointment}', [AdminController::class, 'updateAppointment'])->name('admin.appointments.update');
    Route::delete('/appointments/{appointment}', [AdminController::class, 'cancelAppointment'])->name('admin.appointments.cancel');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/export', [AdminController::class, 'exportReport'])->name('admin.reports.export');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});

Route::prefix('patient')->group(function () {
    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('patient.dashboard');
    Route::get('/book', [PatientController::class, 'book'])->name('patient.book');
    Route::get('/doctor-schedules/{doctorId}', [PatientController::class, 'getDoctorSchedules'])->name('patient.doctor-schedules');
    Route::post('/book', [PatientController::class, 'storeAppointment'])->name('patient.appointments.store');
    Route::get('/appointments', [PatientController::class, 'appointments'])->name('patient.appointments');
    Route::get('/records', [PatientController::class, 'records'])->name('patient.records');
    Route::get('/prescriptions', [PatientController::class, 'prescriptions'])->name('patient.prescriptions');
    Route::get('/notifications', [PatientController::class, 'notifications'])->name('patient.notifications');
    Route::get('/profile', [PatientController::class, 'profile'])->name('patient.profile');
    Route::put('/profile', [PatientController::class, 'updateProfile'])->name('patient.profile.update');
});

use App\Http\Controllers\ReceptionistController;

Route::prefix('receptionist')->group(function () {
    Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->name('receptionist.dashboard');
    Route::get('/register', [ReceptionistController::class, 'register'])->name('receptionist.register');
    Route::get('/patients', [ReceptionistController::class, 'patients'])->name('receptionist.patients');
    Route::get('/appointments', [ReceptionistController::class, 'appointments'])->name('receptionist.appointments');
    Route::get('/walkin', [ReceptionistController::class, 'walkin'])->name('receptionist.walkin');
    Route::get('/schedule', [ReceptionistController::class, 'schedule'])->name('receptionist.schedule');
    Route::get('/billing', [ReceptionistController::class, 'billing'])->name('receptionist.billing');
    Route::post('/billing', [ReceptionistController::class, 'storeBilling'])->name('receptionist.billing.store');
    Route::get('/billing/{payment}/print', [ReceptionistController::class, 'printReceipt'])->name('receptionist.billing.print');
    Route::get('/notifications', [ReceptionistController::class, 'notifications'])->name('receptionist.notifications');
    Route::get('/profile', [ReceptionistController::class, 'profile'])->name('receptionist.profile');
});

use App\Http\Controllers\DoctorController;

Route::prefix('doctor')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    Route::get('/appointments', [DoctorController::class, 'appointments'])->name('doctor.appointments');
    Route::get('/patients', [DoctorController::class, 'patients'])->name('doctor.patients');
    Route::get('/consultation', [DoctorController::class, 'consultation'])->name('doctor.consultation');
    Route::post('/consultation', [DoctorController::class, 'storeConsultation'])->name('doctor.consultation.store');
    Route::get('/prescriptions', [DoctorController::class, 'prescriptions'])->name('doctor.prescriptions');
    Route::post('/prescriptions', [DoctorController::class, 'storePrescription'])->name('doctor.prescriptions.store');
    Route::delete('/prescriptions/{prescription}', [DoctorController::class, 'destroyPrescription'])->name('doctor.prescriptions.destroy');
    Route::get('/schedule', [DoctorController::class, 'schedule'])->name('doctor.schedule');
    Route::get('/notifications', [DoctorController::class, 'notifications'])->name('doctor.notifications');
    Route::get('/profile', [DoctorController::class, 'profile'])->name('doctor.profile');
});
