<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
      public function generateSlip($id, $month)
    {
        $employee = Employee::findOrFail($id);

        // total present days
        $presentDays = Attendance::where('employee_id', $id)
            ->where('status', 'present')
            ->whereMonth('date', $month)
            ->count();

        // total absent days
        $absentDays = Attendance::where('employee_id', $id)
            ->where('status', 'absent')
            ->whereMonth('date', $month)
            ->count();

        $totalDays = $presentDays + $absentDays;

        // salary calculation
        $basic = $employee->basic_salary;

        $dailySalary = $totalDays > 0 ? $basic / $totalDays : 0;
        $finalSalary = $dailySalary * $presentDays;

        return view('payroll.salaryslip', compact(
            'employee',
            'presentDays',
            'absentDays',
            'basic',
            'finalSalary',
            'month'
        ));
    }
}
