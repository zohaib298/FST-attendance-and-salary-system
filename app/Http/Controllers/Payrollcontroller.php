<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Bonus;
use App\Models\Advance;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // 📊 PAYROLL LIST
    public function index()
    {
        $employees = Employee::all();

        $payrolls = [];

        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        foreach ($employees as $emp) {

            // 📅 Attendance
            $present = Attendance::where('employee_id', $emp->id)
                ->where('status', 'present')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count();

            $absent = Attendance::where('employee_id', $emp->id)
                ->where('status', 'absent')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count();

            // 💰 Daily salary calculation
            $dailySalary = $emp->basic_salary / 30;

            $earned = $present * $dailySalary;
            $deduction = $absent * $dailySalary;

            // 🎁 Bonus
            $bonus = Bonus::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            // 💸 Advance
            $advance = Advance::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            // 🧾 Net Salary
            $netSalary = ($earned + $bonus) - ($deduction + $advance);

            $payrolls[] = (object)[
                'employee' => $emp,
                'present' => $present,
                'absent' => $absent,
                'bonus' => $bonus,
                'advance' => $advance,
                'net' => round($netSalary, 2)
            ];
        }

        return view('payroll.index', compact('payrolls'));
    }
}