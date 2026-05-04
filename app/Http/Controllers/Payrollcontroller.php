<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function getBranches(): array
    {
        $dbBranches      = Employee::select('branch')
            ->distinct()->whereNotNull('branch')->pluck('branch')->toArray();
        $defaultBranches = ['Rawalpindi', 'Lahore', 'Karachi'];
        return array_values(array_unique(array_merge($defaultBranches, $dbBranches)));
    }

    private function countSundays(int $month, int $year): int
    {
        $count = 0;
        $days  = Carbon::create($year, $month)->daysInMonth;
        for ($d = 1; $d <= $days; $d++) {
            if (Carbon::create($year, $month, $d)->isSunday()) $count++;
        }
        return $count;
    }

    private function sundaysSoFar(int $month, int $year): int
    {
        $count = 0;
        $today = Carbon::today();
        $days  = Carbon::create($year, $month)->daysInMonth;
        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create($year, $month, $d);
            if ($date->isSunday() && $date->lte($today)) $count++;
        }
        return $count;
    }

    /**
     * Build a fake attendance collection from an AttendanceSummary row
     * so the blade can use ->where('status','present')->count() etc.
     */
    private function buildSummaryCollection(AttendanceSummary $s): \Illuminate\Support\Collection
    {
        $records   = collect();
        $lateCount = (int) $s->late_count;
        $lateAdded = 0;

        for ($i = 0; $i < (int)$s->present_days; $i++) {
            $isLate = ($lateAdded < $lateCount) ? 1 : 0;
            if ($isLate) $lateAdded++;
            $records->push((object)[
                'status'          => 'present',
                'late'            => $isLate,
                'night'           => 0,
                'overtime'        => 0,
                'advance'         => 0,
                'bonus'           => 0,
                'deductions'      => 0,
                'bike_allowance'  => (float)($s->bike_allowance  ?? 0),
                'mobile_allowance'=> (float)($s->mobile_allowance ?? 0),
                'other_allowance' => (float)($s->other_allowance  ?? 0),
                'commission'      => (float)($s->commission       ?? 0),
            ]);
        }

        for ($i = 0; $i < (int)$s->absent_days; $i++) {
            $records->push((object)[
                'status' => 'absent', 'late' => 0,
                'night' => 0, 'overtime' => 0, 'advance' => 0,
                'bonus' => 0, 'deductions' => 0,
                'bike_allowance' => 0, 'mobile_allowance' => 0,
                'other_allowance' => 0, 'commission' => 0,
            ]);
        }

        for ($i = 0; $i < (int)$s->leave_days; $i++) {
            $records->push((object)[
                'status' => 'leave', 'late' => 0,
                'night' => 0, 'overtime' => 0, 'advance' => 0,
                'bonus' => 0, 'deductions' => 0,
                'bike_allowance' => 0, 'mobile_allowance' => 0,
                'other_allowance' => 0, 'commission' => 0,
            ]);
        }

        // summary row — carries the aggregated numeric data
        $records->push((object)[
            'status'          => '__summary',
            'late'            => 0,
            'night'           => (float)$s->night_duties,
            'overtime'        => (float)$s->overtime_hours,
            'advance'         => (float)$s->advance,
            'bonus'           => (float)$s->bonus,
            'deductions'      => (float)$s->deductions,
            'bike_allowance'  => 0,
            'mobile_allowance'=> 0,
            'other_allowance' => 0,
            'commission'      => 0,
        ]);

        return $records;
    }

    // ─── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $allBranchNames = $this->getBranches();

        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year',  now()->year);
        $search = $request->get('search', '');
        $branch = $request->get('branch', '');

        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        if (!$branch || !in_array($branch, $allBranchNames)) {
            $branch = $allBranchNames[0] ?? 'Rawalpindi';
        }

        $lateToAbsentRule = (int)(Setting::where('key', 'late_to_absent')->value('value') ?? 3);
        $isPastMonth      = Carbon::create($year, $month)->lt(now()->startOfMonth());
        $isCurrentMonth   = ($month == now()->month && $year == now()->year);

        // ── Employees ──
        $empQuery = Employee::where('branch', $branch);
        if ($search) {
            $empQuery->where(function ($q) use ($search) {
                $q->where('name',       'like', "%{$search}%")
                  ->orWhere('department','like', "%{$search}%")
                  ->orWhere('cnic',      'like', "%{$search}%");
            });
        }
        $employees   = $empQuery->get();
        $employeeIds = $employees->pluck('id');

        // ── Attendance & Summaries ──
        $monthlyAtt = Attendance::where('branch', $branch)
            ->where('month', $month)->where('year', $year)
            ->whereIn('employee_id', $employeeIds)
            ->get()->groupBy('employee_id');

        $summaries = AttendanceSummary::where('branch', $branch)
            ->where('month', $month)->where('year', $year)
            ->whereIn('employee_id', $employeeIds)
            ->get()->keyBy('employee_id');

        // Build allAttendances: keyed by employee_id
        $allAttendances = collect();
        foreach ($employees as $emp) {
            $summary = $summaries->get($emp->id);
            if ($summary) {
                $allAttendances->put($emp->id, $this->buildSummaryCollection($summary));
                // Sync basic_salary from summary → employee object so blade gets correct value
                if ($summary->basic_salary > 0) {
                    $emp->basic_salary = $summary->basic_salary;
                }
            } else {
                $allAttendances->put($emp->id, $monthlyAtt->get($emp->id, collect()));
            }
        }

        // ── Working Days ──
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $workingDays = collect();
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            if (!$date->isSunday()) $workingDays->push($date);
        }
        $totalWorkingDays = $workingDays->count();

        // ── Today's Attendance (for dashboard stats if needed) ──
        $todayAttendance = Attendance::where('branch', $branch)
            ->whereDate('date', now()->format('Y-m-d'))
            ->whereIn('employee_id', $employeeIds)
            ->get()->keyBy('employee_id');

        // ── Settings array ──
        $settings = ['late_to_absent' => $lateToAbsentRule];

        $activeBranch = $branch;

        return view('payroll.index', compact(
            'employees',
            'allAttendances',
            'allBranchNames',
            'activeBranch',
            'month',
            'year',
            'settings',
            'lateToAbsentRule',
            'isPastMonth',
            'isCurrentMonth',
            'totalWorkingDays',
            'daysInMonth',
            'todayAttendance',
            'summaries',
            'search'
        ));
    }

    // ─── SALARY SLIP ───────────────────────────────────────────────────────────

    public function salarySlip($id, $month = null, $year = null)
    {
        $month = (int)($month ?? request('month', now()->month));
        $year  = (int)($year  ?? request('year',  now()->year));

        $lateToAbsentRule = (int)(Setting::where('key', 'late_to_absent')->value('value') ?? 3);
        $totalSundays     = $this->countSundays($month, $year);
        $employee         = Employee::findOrFail($id);

        // Prefer summary, fall back to raw attendance
        $summary = AttendanceSummary::where('employee_id', $id)
            ->where('month', $month)->where('year', $year)->first();

        $attendances = Attendance::where('employee_id', $id)
            ->where('month', $month)->where('year', $year)->get();

        if ($summary) {
            $presentDays   = (int)   $summary->present_days;
            $absentDays    = (int)   $summary->absent_days;
            $leaveDays     = (int)   $summary->leave_days;
            $lateCount     = (int)   $summary->late_count;
            $lateAbsents   = (int)   $summary->late_absents;
            $nightDuties   = (float) $summary->night_duties;
            $overtimeHours = (float) $summary->overtime_hours;
            $advance       = (float) $summary->advance;
            $bonus         = (float) $summary->bonus;
            $deductions    = (float) $summary->deductions;
            $basic         = (float)($summary->basic_salary > 0 ? $summary->basic_salary : ($employee->basic_salary ?? 0));
            $manualNet     = ($summary->manual_net_salary !== null) ? (float)$summary->manual_net_salary : null;
        } else {
            $presentDays   = $attendances->whereIn('status',['present','halfday'])->count();
            $absentDays    = $attendances->where('status','absent')->count();
            $leaveDays     = $attendances->where('status','leave')->count();
            $lateCount     = $attendances->where('late',1)->count();
            $lateAbsents   = (int)floor($lateCount / max(1,$lateToAbsentRule));
            $nightDuties   = (float)$attendances->sum('night');
            $overtimeHours = (float)$attendances->sum('overtime');
            $advance       = (float)$attendances->sum('advance');
            $bonus         = (float)$attendances->sum('bonus');
            $deductions    = (float)$attendances->sum('deductions');
            $basic         = (float)($employee->basic_salary ?? 0);
            $manualNet     = null;
        }

        // Calculate all required variables for the blade
        $perDay  = $basic > 0 ? round($basic / 26, 2) : 0;
        $perHour = $perDay  > 0 ? round($perDay / 8, 2) : 0;

        // Allowances
        $bikeAllowance   = (float)($employee->bike_allowance   ?? 0);
        $mobileAllowance = (float)($employee->mobile_allowance ?? 0);
        $commission      = (float)($employee->commission       ?? 0);
        $otherBonus      = (float)($employee->other_allowance  ?? 0);
        
        $totalAllowances = $bikeAllowance + $mobileAllowance + $commission + $otherBonus;

        $nightRate   = (float)($employee->night_rate ?? 500);
        $nightAmount  = $nightDuties * $nightRate;
        $overtimeAmount = $overtimeHours * $perHour;
        
        $earnedBasic = max(0, ($presentDays + $leaveDays - $lateAbsents)) * $perDay;
        $grossEarnings = $earnedBasic + $totalAllowances + $nightAmount + $overtimeAmount + $bonus;

        $lateDeductPer     = (float)($employee->late_deduction   ?? 0);
        $absentDeductPer   = (float)($employee->absent_deduction ?? 0);
        $lateDeduction   = $lateCount * $lateDeductPer;
        $absentDeduction = ($absentDays + $lateAbsents) * $absentDeductPer;
        $otherDeductions = $deductions;
        $totalDeductions = $lateDeduction + $absentDeduction + $otherDeductions;

        $isPast  = Carbon::create($year, $month)->lt(now()->startOfMonth());
        $autoNet = $grossEarnings - $totalDeductions - $advance;
        $netPayable = ($isPast && $manualNet !== null) ? $manualNet : $autoNet;

        // Advance details
        $totalAdvance = $advance;
        $thisMonthAdvanceDeduction = $advance;
        $remainingAdvance = 0;
        
        $lateRule = $lateToAbsentRule;

        return view('payroll.reportslip', compact(
            'employee',
            'attendances',
            'presentDays',
            'absentDays',
            'leaveDays',
            'lateCount',
            'lateAbsents',
            'basic',
            'perDay',
            'bikeAllowance',
            'mobileAllowance',
            'commission',
            'otherBonus',
            'overtimeHours',
            'overtimeAmount',
            'nightDuties',
            'nightAmount',
            'grossEarnings',
            'advance',
            'lateDeduction',
            'absentDeduction',
            'otherDeductions',
            'totalDeductions',
            'netPayable',
            'totalAdvance',
            'thisMonthAdvanceDeduction',
            'remainingAdvance',
            'lateRule',
            'month',
            'year'
        ));
    }

    // ─── REPORT ────────────────────────────────────────────────────────────────

    public function report(Request $request)
    {
        $month  = $request->get('month', now()->format('Y-m'));
        $search = $request->get('search');
        [$year, $mon] = explode('-', $month);

        $query = Employee::query();
        if ($search) $query->where('name', 'like', "%$search%");
        $employees = $query->get();

        $summaries = $employees->map(function ($emp) use ($year, $mon) {
            $atts = Attendance::where('employee_id', $emp->id)
                ->whereYear('date', $year)->whereMonth('date', $mon)->get();
            return [
                'employee_id' => $emp->id,
                'name'        => $emp->name,
                'present'     => $atts->where('status','present')->count(),
                'absent'      => $atts->where('status','absent')->count(),
                'leave'       => $atts->where('status','leave')->count(),
            ];
        });

        $months = collect(range(0,11))->map(fn($i) => now()->subMonths($i)->format('Y-m'));

        return view('payroll.report', compact('summaries','months'));
    }
}