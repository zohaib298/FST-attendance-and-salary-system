<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryController;

Route::get('/', [EmployeeController::class, 'index']);

//employees route
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

// profiles route
Route::get('/employees/profiles', [EmployeeController::class, 'profiles'])
    ->name('employees.profiles');


    //attendance route
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
//payroll route
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');


// salary slip route

Route::get('/salary-slip/{id}/{month}', [SalaryController::class, 'generateSlip'])->name('salary.slip');

Route::get('/employees/profiles', [EmployeeController::class, 'profiles'])
    ->name('employees.profiles');

    Route::get('/attendance-report', [AttendanceReportController::class, 'index'])
    ->name('attendance.report');