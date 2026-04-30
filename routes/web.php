<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Payrollcontroller;
use Illuminate\Support\Facades\Route;

Route::view('/','welcome');

// EMPLOYEES
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');

Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit']);
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');

// ATTENDANCE
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

// PAYROLL
Route::get('/payroll', [Payrollcontroller::class, 'index'])->name('payroll.index');

// PROFILES
Route::get('/employees/profiles', [EmployeeController::class, 'profiles']);
