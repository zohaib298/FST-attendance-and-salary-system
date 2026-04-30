<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Payrollcontroller;
use App\Http\Controllers\SalaryController;
use Illuminate\Support\Facades\Route;



Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.list');

Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');

Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');



Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');

Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');

Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');



Route::get('/employees/profiles', [EmployeeController::class, 'profiles'])->name('employees.profiles');



Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

Route::get('/attendance-report', [AttendanceReportController::class, 'index'])->name('attendance.report');



Route::get('/payroll', [Payrollcontroller::class, 'index'])->name('payroll.index');


Route::get('/salary-slip/{id}/{month}', [SalaryController::class, 'generateSlip'])->name('salary.slip');