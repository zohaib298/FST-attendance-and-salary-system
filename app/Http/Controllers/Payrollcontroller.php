<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Bonus;
use App\Models\Advance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ?? Carbon::now()->month;
        $year  = $request->year ?? Carbon::now()->year;
        $search = $request->search;

        $query = Employee::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cnic', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();

        $payrolls = [];

        foreach ($employees as $emp) {

            // 📊 Attendance
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

            // 💰 Salary base
            $basic = $emp->basic_salary ?? 0;
            $perDay = $basic / 30;

            // ✔ earnings
            $paidDays = $present + $leave;
            $gross = $paidDays * $perDay;

            // ❌ deduction
            $deduction = $absent * $perDay;

            // 🎁 bonus
            $bonus = Bonus::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            // 💸 advance
            $advance = Advance::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            // 🧾 allowances
            $allowances =
                ($emp->bike_allowance ?? 0) +
                ($emp->mobile_allowance ?? 0) +
                ($emp->commission ?? 0) +
                ($emp->other_allowance ?? 0);

            // 💼 net salary
            $netSalary = $gross - $deduction + $bonus + $allowances - $advance;

            $payrolls[] = (object)[
                'employee' => $emp,
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'bonus' => $bonus,
                'advance' => $advance,
                'net' => round(max(0, $netSalary)),
            ];
        }

        return view('payroll.index', compact('payrolls', 'month', 'year'));
    }
}