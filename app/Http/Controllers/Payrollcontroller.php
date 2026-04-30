<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Bonus;
use App\Models\Advance;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index()
    {
        $employees = Employee::all();

        $payrolls = [];

        $month = request('month', Carbon::now()->month);
        $year  = request('year', Carbon::now()->year);

        foreach ($employees as $emp) {

            // 📅 Attendance counts
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

            $leave = Attendance::where('employee_id', $emp->id)
                ->where('status', 'leave')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count();

            // 💰 Per day salary
            $perDay = $emp->basic_salary / 30;

            // ✅ Paid days (present + leave)
            $paidDays = $present + $leave;

            // 💵 Earnings
            $gross = $paidDays * $perDay;

            // 💸 Deduction (only absent)
            $deduction = $absent * $perDay;

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

            // 💼 Allowances (from employee table)
            $allowances =
                $emp->bike_allowance +
                $emp->mobile_allowance +
                $emp->commission +
                $emp->other_allowance;

            // 🧾 Net Salary
            $netSalary = $gross - $deduction + $bonus + $allowances - $advance;

            // ❌ Never allow negative
            $netSalary = max(0, $netSalary);

            $payrolls[] = (object)[
                'employee' => $emp,
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'bonus' => $bonus,
                'advance' => $advance,
                'net' => round($netSalary),
            ];
        }

        return view('payroll.index', compact('payrolls', 'month', 'year'));
    }
}