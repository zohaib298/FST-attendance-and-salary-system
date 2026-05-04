<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryController;

Route::get('/', [EmployeeController::class, 'index'])->name('dashboard');

// Employees
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/profiles', [EmployeeController::class, 'profiles'])->name('employees.profiles');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
Route::post('/employee/store-monthly', [EmployeeController::class, 'storeWithMonthly'])->name('employee.store');

// Attendance
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
Route::post('/attendance/bulk-store', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk-store');
Route::post('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
Route::get('/attendance/template', [AttendanceController::class, 'downloadTemplate'])->name('attendance.template');
Route::get('/attendance/employee/{id}', [AttendanceController::class, 'employeeMonthly']);

// Attendance Report
Route::get('/attendance-report', [AttendanceController::class, 'report'])->name('attendance.report');
Route::get('/attendance/dates', [AttendanceController::class, 'getDates'])->name('attendance.dates');
Route::post('/attendance/bulk-update', [AttendanceController::class, 'bulkUpdate'])->name('attendance.bulk-update');

// Settings
Route::post('/settings/update', [AttendanceController::class, 'updateSettings'])->name('settings.update');
Route::post('/monthly/summary/save', [AttendanceController::class, 'monthlySummarySave'])->name('monthly.summary.save');

// Payroll
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::get('/salary-slip/{id}/{month?}/{year?}', [PayrollController::class, 'salarySlip']);
Route::get('/payroll/reportslip/{id}', [PayrollController::class, 'salarySlip'])->name('payroll.reportslip');

// Salary Slip
Route::get('/salary-slip/{id}/{month}', [SalaryController::class, 'generateSlip'])->name('salary.slip');

use App\Http\Controllers\AttendanceImportController;

Route::post('/attendance/import', [AttendanceImportController::class, 'import'])->name('attendance.import');