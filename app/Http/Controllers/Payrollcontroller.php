<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Bonus;
use App\Models\Advance;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    // =======================
    // PAYROLL LIST
    // =======================
    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;
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

            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();

            $present = $attendances->where('status', 'present')->count();
            $absent  = $attendances->where('status', 'absent')->count();
            $leave   = $attendances->where('status', 'leave')->count();

            $basic = (float) ($emp->basic_salary ?? 0);
            $perDay = $basic > 0 ? $basic / 30 : 0;

            $paidDays = $present + $leave;

            $gross = $paidDays * $perDay;
            $deduction = $absent * $perDay;

            $bonus = Bonus::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $advance = Advance::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $allowances =
                (float) ($emp->bike_allowance ?? 0) +
                (float) ($emp->mobile_allowance ?? 0) +
                (float) ($emp->commission ?? 0) +
                (float) ($emp->other_allowance ?? 0);

            $netSalary = $gross - $deduction + $bonus + $allowances - $advance;

            $payrolls[] = (object)[
                'employee'   => $emp,
                'present'    => $present,
                'absent'     => $absent,
                'leave'      => $leave,
                'gross'      => $gross,
                'deduction'  => $deduction,
                'bonus'      => $bonus,
                'advance'    => $advance,
                'allowances' => $allowances,
                'net'        => round(max(0, $netSalary)),
            ];
        }

        return view('payroll.index', compact('payrolls', 'month', 'year'));
    }

    // =======================
    // SALARY SLIP (FINAL FIXED)
    // =======================
    public function salarySlip($id, Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $employee = Employee::findOrFail($id);

        // 🔥 IMPORTANT: attendance pass to view (FIXED)
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $present = $attendances->where('status', 'present')->count();
        $absent  = $attendances->where('status', 'absent')->count();
        $leave   = $attendances->where('status', 'leave')->count();

        $basic = (float) ($employee->basic_salary ?? 0);
        $perDay = $basic > 0 ? $basic / 30 : 0;

        $paidDays = $present + $leave;

        $gross = $paidDays * $perDay;
        $deduction = $absent * $perDay;

        $bonus = Bonus::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $advance = Advance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');

        $allowances =
            (float) ($employee->bike_allowance ?? 0) +
            (float) ($employee->mobile_allowance ?? 0) +
            (float) ($employee->commission ?? 0) +
            (float) ($employee->other_allowance ?? 0);

        $netSalary = $gross - $deduction + $bonus + $allowances - $advance;

        return view('payroll.reportslip', [
            'employee'    => $employee,
            'attendances' => $attendances, // ✅ FIX IMPORTANT
            'present'     => $present,
            'absent'      => $absent,
            'leave'       => $leave,
            'basic'       => $basic,
            'gross'       => $gross,
            'deduction'   => $deduction,
            'bonus'       => $bonus,
            'advance'     => $advance,
            'allowances'  => $allowances,
            'net'         => round(max(0, $netSalary)),
            'month'       => $month,
            'year'        => $year,
        ]);
    }
}