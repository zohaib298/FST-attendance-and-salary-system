<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    private function countSundays($month, $year)
    {
        $sundays = 0;
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (Carbon::create($year, $month, $day)->isSunday()) {
                $sundays++;
            }
        }
        return $sundays;
    }

    private function sundaysSoFar($month, $year)
    {
        $sundays = 0;
        $today = Carbon::today();
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            if ($date->isSunday() && $date->lte($today)) {
                $sundays++;
            }
        }
        return $sundays;
    }

    public function index(Request $request)
    {
        $month  = $request->month ?? now()->month;
        $year   = $request->year ?? now()->year;
        $search = $request->search;

        $sundaysSoFar = $this->sundaysSoFar($month, $year);

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
        $payrolls  = [];

        foreach ($employees as $emp) {

            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();

            $present = $attendances->where('status', 'present')->count();
            $absent  = $attendances->where('status', 'absent')->count();
            $leave   = $attendances->where('status', 'leave')->count();

            $basic  = (float) ($emp->basic_salary ?? 0);
            $perDay = $basic / 30;

            // ✅ Running = sirf present * perDay
            $runningSalary = round($present * $perDay);

            // ✅ Net = present + leave + sundays so far - absent
            $paidDays  = $present + $leave + $sundaysSoFar;
            $gross     = $paidDays * $perDay;
            $deduction = $absent * $perDay;

            $night   = (float) $attendances->sum('night');
            $advance = (float) $attendances->sum('advance');

            $allowances =
                (float) ($emp->bike_allowance ?? 0) +
                (float) ($emp->mobile_allowance ?? 0) +
                (float) ($emp->commission ?? 0) +
                (float) ($emp->other_allowance ?? 0);

            $netSalary = $gross - $deduction + $night + $allowances - $advance;

            $payrolls[] = (object)[
                'employee'   => $emp,
                'present'    => $present,
                'absent'     => $absent,
                'leave'      => $leave,
                'gross'      => $gross,
                'deduction'  => $deduction,
                'bonus'      => $night,
                'advance'    => $advance,
                'allowances' => $allowances,
                'net'        => round($netSalary), // ✅ minus bhi show hoga
                'running'    => $runningSalary,
            ];
        }

        return view('payroll.index', compact('payrolls', 'month', 'year'));
    }

    public function salarySlip($id, Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $sundaysSoFar = $this->sundaysSoFar($month, $year);
        $totalSundays = $this->countSundays($month, $year);

        $employee = Employee::findOrFail($id);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $present = $attendances->where('status', 'present')->count();
        $absent  = $attendances->where('status', 'absent')->count();
        $leave   = $attendances->where('status', 'leave')->count();

        $basic  = (float) ($employee->basic_salary ?? 0);
        $perDay = $basic / 30;

        $paidDays  = $present + $leave + $sundaysSoFar;
        $gross     = $paidDays * $perDay;
        $deduction = $absent * $perDay;

        $night   = (float) $attendances->sum('night');
        $advance = (float) $attendances->sum('advance');

        $allowances =
            (float) ($employee->bike_allowance ?? 0) +
            (float) ($employee->mobile_allowance ?? 0) +
            (float) ($employee->commission ?? 0) +
            (float) ($employee->other_allowance ?? 0);

        $netSalary = $gross - $deduction + $night + $allowances - $advance;

        return view('payroll.reportslip', [
            'employee'    => $employee,
            'attendances' => $attendances,
            'present'     => $present,
            'absent'      => $absent,
            'leave'       => $leave,
            'basic'       => $basic,
            'perDay'      => round($perDay),
            'gross'       => $gross,
            'deduction'   => $deduction,
            'bonus'       => $night,
            'advance'     => $advance,
            'allowances'  => $allowances,
            'net'         => round($netSalary), // ✅ minus bhi show hoga
            'month'       => $month,
            'year'        => $year,
            'sundays'     => $totalSundays,
        ]);
    }
}