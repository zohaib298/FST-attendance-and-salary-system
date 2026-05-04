<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\Setting;

class AttendanceController extends Controller
{
    protected function getBranches(): array
    {
        $dbBranches = Employee::select('branch')
            ->distinct()
            ->whereNotNull('branch')
            ->pluck('branch')
            ->toArray();

        $defaultBranches = ['Rawalpindi', 'Lahore', 'Karachi'];
        $allBranches = array_unique(array_merge($defaultBranches, $dbBranches));

        return array_values($allBranches);
    }

    protected function getEmployeesByBranch(string $branch)
    {
        return Employee::where('branch', $branch)->get();
    }

    private function buildSummaryCollection(AttendanceSummary $s): \Illuminate\Support\Collection
    {
        $records = collect();

        $lateCount   = (int) $s->late_count;
        $presentDays = (int) $s->present_days;
        $absentDays  = (int) $s->absent_days;
        $leaveDays   = (int) $s->leave_days;

        $lateAdded = 0;
        for ($i = 0; $i < $presentDays; $i++) {
            $isLate = ($lateAdded < $lateCount) ? 1 : 0;
            if ($isLate) $lateAdded++;
            $records->push((object)[
                'status'     => 'present',
                'late'       => $isLate,
                'night'      => 0,
                'overtime'   => 0,
                'advance'    => 0,
                'bonus'      => 0,
                'deductions' => 0,
            ]);
        }

        for ($i = 0; $i < $absentDays; $i++) {
            $records->push((object)[
                'status'     => 'absent',
                'late'       => 0,
                'night'      => 0,
                'overtime'   => 0,
                'advance'    => 0,
                'bonus'      => 0,
                'deductions' => 0,
            ]);
        }

        for ($i = 0; $i < $leaveDays; $i++) {
            $records->push((object)[
                'status'     => 'leave',
                'late'       => 0,
                'night'      => 0,
                'overtime'   => 0,
                'advance'    => 0,
                'bonus'      => 0,
                'deductions' => 0,
            ]);
        }

        $records->push((object)[
            'status'     => '__summary',
            'late'       => 0,
            'night'      => (float) $s->night_duties,
            'overtime'   => (float) $s->overtime_hours,
            'advance'    => (float) $s->advance,
            'bonus'      => (float) $s->bonus,
            'deductions' => (float) $s->deductions,
        ]);

        return $records;
    }

    private function rebuildSummaryFromAttendance(int $empId, string $branch, int $month, int $year): void
    {
        $isPast = Carbon::create($year, $month)->lt(now()->startOfMonth());
        if ($isPast) {
            return;
        }

        $lateToAbsent = (int) (Setting::where('key', 'late_to_absent')->value('value') ?? 3);

        $records = Attendance::where('employee_id', $empId)
            ->where('branch', $branch)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $presentDays   = $records->where('status', 'present')->count()
                       + $records->where('status', 'halfday')->count();
        $absentDays    = $records->where('status', 'absent')->count();
        $leaveDays     = $records->where('status', 'leave')->count();
        $lateCount     = $records->where('late', 1)->count();
        $lateAbsents   = (int) floor($lateCount / max(1, $lateToAbsent));
        $nightDuties   = (int)   $records->sum('night');
        $overtimeHours = (float) $records->sum('overtime');
        $advance       = (float) $records->sum('advance');
        $bonus         = (float) $records->sum('bonus');
        $deductions    = (float) $records->sum('deductions');

        $basicSalary = (float) (Employee::find($empId)?->basic_salary ?? 0);

        $existing = AttendanceSummary::where('employee_id', $empId)
            ->where('branch', $branch)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->basic_salary > 0) {
            $basicSalary = (float) $existing->basic_salary;
        }

        AttendanceSummary::updateOrCreate(
            [
                'employee_id' => $empId,
                'branch'      => $branch,
                'month'       => $month,
                'year'        => $year,
            ],
            [
                'present_days'   => $presentDays,
                'absent_days'    => $absentDays,
                'leave_days'     => $leaveDays,
                'late_count'     => $lateCount,
                'late_absents'   => $lateAbsents,
                'night_duties'   => $nightDuties,
                'overtime_hours' => $overtimeHours,
                'advance'        => $advance,
                'bonus'          => $bonus,
                'deductions'     => $deductions,
                'basic_salary'   => $basicSalary,
            ]
        );
    }

    public function index(Request $request)
    {
        $allBranchNames = $this->getBranches();

        $activeBranch = $request->get('branch');
        $month        = (int) $request->get('month', now()->month);
        $year         = (int) $request->get('year',  now()->year);

        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        if (!$activeBranch || !in_array($activeBranch, $allBranchNames)) {
            $activeBranch = $allBranchNames[0] ?? 'Rawalpindi';
        }

        $employees   = $this->getEmployeesByBranch($activeBranch);
        $employeeIds = $employees->pluck('id');

        $lateToAbsentRule = (int) (Setting::where('key', 'late_to_absent')->value('value') ?? 3);

        $today           = now()->format('Y-m-d');
        $todayAttendance = Attendance::where('branch', $activeBranch)
                            ->whereDate('date', $today)
                            ->whereIn('employee_id', $employeeIds)
                            ->get()
                            ->keyBy('employee_id');

        $monthlyAtt = Attendance::where('branch', $activeBranch)
                        ->where('month', $month)
                        ->where('year',  $year)
                        ->whereIn('employee_id', $employeeIds)
                        ->get()
                        ->groupBy('employee_id');

        $summaries = AttendanceSummary::where('branch', $activeBranch)
                        ->where('month', $month)
                        ->where('year',  $year)
                        ->whereIn('employee_id', $employeeIds)
                        ->get()
                        ->keyBy('employee_id');

        $summaryBasicSalaries = $summaries->mapWithKeys(function ($s) {
            return [$s->employee_id => (float) $s->basic_salary];
        });

        $summaryExtras = $summaries->mapWithKeys(function ($s) {
            return [$s->employee_id => [
                'bonus'      => (float) $s->bonus,
                'deductions' => (float) $s->deductions,
                'advance'    => (float) $s->advance,
            ]];
        });

        $allAttendances = collect();
        foreach ($employees as $emp) {
            $summary = $summaries->get($emp->id);
            if ($summary) {
                $allAttendances->put($emp->id, $this->buildSummaryCollection($summary));
            } else {
                $allAttendances->put($emp->id, $monthlyAtt->get($emp->id, collect()));
            }
        }

        $present = $todayAttendance->where('status', 'present')->count();
        $absent  = $todayAttendance->where('status', 'absent')->count();
        $leave   = $todayAttendance->where('status', 'leave')->count();
        $late    = $todayAttendance->where('late', 1)->count();

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $workingDays = collect();
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            if (!$date->isSunday()) $workingDays->push($date);
        }
        $totalWorkingDays = $workingDays->count();

        $allBranches = [];
        foreach ($allBranchNames as $br) {
            $allBranches[$br] = Employee::where('branch', $br)->get()->toArray();
        }

        return view('employees.index', [
            'employees'            => $employees,
            'useEmployees'         => $employees,
            'allBranchNames'       => $allBranchNames,
            'allBranches'          => $allBranches,
            'activeBranch'         => $activeBranch,
            'month'                => $month,
            'year'                 => $year,
            'allAttendances'       => $allAttendances,
            'todayAttendance'      => $todayAttendance,
            'settings'             => ['late_to_absent' => $lateToAbsentRule],
            'lateToAbsentRule'     => $lateToAbsentRule,
            'present'              => $present,
            'absent'               => $absent,
            'leave'                => $leave,
            'late'                 => $late,
            'totalWorkingDays'     => $totalWorkingDays,
            'daysInMonth'          => $daysInMonth,
            'summaryBasicSalaries' => $summaryBasicSalaries,
            'summaryExtras'        => $summaryExtras,
            'summaries'            => $summaries,
        ]);
    }

    public function getSummaryData(Request $request)
    {
        $branch = $request->get('branch');
        $month  = (int) $request->get('month', now()->month);
        $year   = (int) $request->get('year',  now()->year);

        $employees = Employee::where('branch', $branch)->get();
        $empIds    = $employees->pluck('id')->toArray();

        $summaries = AttendanceSummary::where('branch', $branch)
            ->where('month', $month)
            ->where('year',  $year)
            ->whereIn('employee_id', $empIds)
            ->get()
            ->keyBy('employee_id');

        $result = [];
        foreach ($employees as $emp) {
            $s = $summaries->get($emp->id);
            $result[] = [
                'id'             => $emp->id,
                'name'           => $emp->name,
                'designation'    => $emp->designation ?? $emp->department ?? '',
                'basic_salary'   => $s ? (float) $s->basic_salary   : (float) ($emp->basic_salary ?? 0),
                'present_days'   => $s ? (int)   $s->present_days   : null,
                'absent_days'    => $s ? (int)   $s->absent_days    : null,
                'leave_days'     => $s ? (int)   $s->leave_days     : null,
                'late_count'     => $s ? (int)   $s->late_count     : null,
                'late_absents'   => $s ? (int)   $s->late_absents   : 0,
                'night_duties'   => $s ? (float) $s->night_duties   : null,
                'overtime_hours' => $s ? (float) $s->overtime_hours : null,
                'bonus'          => $s ? (float) $s->bonus          : null,
                'advance'        => $s ? (float) $s->advance        : null,
                'deductions'     => $s ? (float) $s->deductions     : null,
            ];
        }

        return response()->json([
            'employees' => $result,
            'lateRule'  => (int) (Setting::where('key', 'late_to_absent')->value('value') ?? 3),
        ]);
    }

    public function store(Request $request)
    {
        $branch = $request->input('branch');
        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);
        $date   = $request->input('date', now()->format('Y-m-d'));

        $officeStart = Carbon::createFromTime(9, 30, 0);

        foreach ($request->input('attendance', []) as $empId => $status) {
            $checkIn  = $request->input("checkin.{$empId}");
            $checkOut = $request->input("checkout.{$empId}");

            $late = 0;
            if ($checkIn && $status !== 'absent') {
                $late = Carbon::parse($checkIn)->gt($officeStart) ? 1 : 0;
            }
            if ($request->has("late.{$empId}")) {
                $late = (int) $request->input("late.{$empId}");
            }

            Attendance::updateOrCreate(
                ['employee_id' => $empId, 'date' => $date, 'branch' => $branch],
                [
                    'status'     => $status,
                    'check_in'   => ($status !== 'absent') ? ($checkIn ?: null) : null,
                    'check_out'  => ($status !== 'absent') ? ($checkOut ?: null) : null,
                    'late'       => $late,
                    'overtime'   => (float) $request->input("overtime.{$empId}",   0),
                    'night'      => (int)   $request->input("night.{$empId}",      0),
                    'bonus'      => (float) $request->input("bonus.{$empId}",      0),
                    'advance'    => (float) $request->input("advance.{$empId}",    0),
                    'deductions' => (float) $request->input("deductions.{$empId}", 0),
                    'notes'      => $request->input("notes.{$empId}", ''),
                    'month'      => $month,
                    'year'       => $year,
                ]
            );
        }

        $isPast = Carbon::create($year, $month)->lt(now()->startOfMonth());
        if (!$isPast) {
            foreach (array_keys($request->input('attendance', [])) as $empId) {
                $this->rebuildSummaryFromAttendance((int) $empId, $branch, $month, $year);
            }
        }

        return redirect()->back()->with('success', "Attendance save ho gayi — {$date} ({$branch})");
    }

    public function bulkStore(Request $request)
    {
        $branch       = $request->input('branch');
        $month        = (int) $request->input('month', now()->month);
        $year         = (int) $request->input('year',  now()->year);
        $lateToAbsent = (int) $request->input('late_to_absent_rule', 3);

        Setting::updateOrCreate(
            ['key' => 'late_to_absent'],
            ['value' => $lateToAbsent]
        );

        $presentArray = $request->input('present', []);

        foreach ($presentArray as $empId => $presentDays) {
            $lateCount   = (int)   $request->input("late.{$empId}",         0);
            $lateAbsents = (int)   floor($lateCount / max(1, $lateToAbsent));
            $basicSalary = (float) $request->input("basic_salary.{$empId}", 0);

            AttendanceSummary::updateOrCreate(
                [
                    'employee_id' => $empId,
                    'branch'      => $branch,
                    'month'       => $month,
                    'year'        => $year,
                ],
                [
                    'present_days'   => (int)   $presentDays,
                    'absent_days'    => (int)   $request->input("absent.{$empId}",     0),
                    'leave_days'     => (int)   $request->input("leave.{$empId}",      0),
                    'late_count'     => $lateCount,
                    'late_absents'   => $lateAbsents,
                    'night_duties'   => (int)   $request->input("night.{$empId}",      0),
                    'overtime_hours' => (float) $request->input("overtime.{$empId}",   0),
                    'bonus'          => (float) $request->input("bonus.{$empId}",      0),
                    'advance'        => (float) $request->input("advance.{$empId}",    0),
                    'deductions'     => (float) $request->input("deductions.{$empId}", 0),
                    'basic_salary'   => $basicSalary,
                ]
            );

            if ($basicSalary > 0) {
                Employee::where('id', $empId)->update(['basic_salary' => $basicSalary]);
            }
        }

        $monthName = Carbon::create($year, $month)->format('F Y');

        return redirect()
            ->to("/?branch={$branch}&month={$month}&year={$year}")
            ->with('success', "Data save ho gaya — {$branch}, {$monthName}");
    }

    public function monthlySummarySave(Request $request)
    {
        $branch       = $request->input('branch');
        $month        = (int) $request->input('month', now()->month);
        $year         = (int) $request->input('year',  now()->year);
        $empIds       = $request->input('emp_ids', []);
        $lateToAbsent = (int) (Setting::where('key', 'late_to_absent')->value('value') ?? 3);

        foreach ($empIds as $empId) {
            $lateCount   = (int) $request->input("late.{$empId}", 0);
            $lateAbsents = (int) floor($lateCount / max(1, $lateToAbsent));

            $existingSummary = AttendanceSummary::where('employee_id', $empId)
                ->where('branch', $branch)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $basicSalary = (float) ($existingSummary->basic_salary
                ?? Employee::find($empId)?->basic_salary
                ?? 0);

            AttendanceSummary::updateOrCreate(
                [
                    'employee_id' => $empId,
                    'branch'      => $branch,
                    'month'       => $month,
                    'year'        => $year,
                ],
                [
                    'present_days'   => (int)   $request->input("present.{$empId}",    0),
                    'absent_days'    => (int)   $request->input("absent.{$empId}",     0),
                    'leave_days'     => (int)   $request->input("leave.{$empId}",      0),
                    'late_count'     => $lateCount,
                    'late_absents'   => $lateAbsents,
                    'night_duties'   => (int)   $request->input("night.{$empId}",      0),
                    'overtime_hours' => (float) $request->input("overtime.{$empId}",   0),
                    'bonus'          => (float) $request->input("bonus.{$empId}",      0),
                    'advance'        => (float) $request->input("advance.{$empId}",    0),
                    'deductions'     => (float) $request->input("deductions.{$empId}", 0),
                    'basic_salary'   => $basicSalary,
                ]
            );
        }

        $monthName = Carbon::create($year, $month)->format('F Y');

        return redirect()
            ->to("/?branch={$branch}&month={$month}&year={$year}")
            ->with('success', "Monthly summary save ho gayi — {$branch}, {$monthName}");
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['late_to_absent' => 'required|integer|min:1|max:10']);

        Setting::updateOrCreate(
            ['key' => 'late_to_absent'],
            ['value' => $request->input('late_to_absent')]
        );

        return redirect()->back()->with('success', 'Late rule update ho gai!');
    }

    public function downloadTemplate(Request $request)
    {
        $branch      = $request->get('branch');
        $month       = (int) $request->get('month', now()->month);
        $year        = (int) $request->get('year',  now()->year);
        $employees   = Employee::where('branch', $branch)->get();
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $monthName   = Carbon::create($year, $month)->format('F_Y');
        $filename    = "{$branch}_{$monthName}_Attendance_Template.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($employees, $month, $year, $daysInMonth) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'employee_id', 'name', 'designation', 'date', 'status',
                'check_in', 'check_out', 'late', 'overtime_hours',
                'night_duty', 'bonus', 'advance', 'deductions', 'basic_salary', 'notes',
            ]);
            foreach ($employees as $emp) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = Carbon::create($year, $month, $d);
                    if ($date->isSunday()) continue;
                    fputcsv($handle, [
                        $emp->id, $emp->name, $emp->designation ?? '',
                        $date->format('Y-m-d'), 'present',
                        '09:00', '19:00', '0', '0', '0', '0', '0', '0',
                        $emp->basic_salary ?? '', '',
                    ]);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function employeeMonthly($id)
    {
        $month = (int) request('month', now()->month);
        $year  = (int) request('year',  now()->year);

        $attendance = Attendance::where('employee_id', $id)
            ->where('month', $month)
            ->where('year',  $year)
            ->get();

        return response()->json($attendance);
    }

    public function report(Request $request)
    {
        $month  = $request->get('month', now()->format('Y-m'));
        $search = $request->get('search');

        [$year, $mon] = explode('-', $month);

        $query = Employee::query();
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $employees = $query->get();

        $summaries = $employees->map(function ($emp) use ($year, $mon) {
            $atts = Attendance::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $mon)
                ->get();

            return [
                'employee_id' => $emp->id,
                'name'        => $emp->name,
                'present'     => $atts->where('status', 'present')->count(),
                'absent'      => $atts->where('status', 'absent')->count(),
                'leave'       => $atts->where('status', 'leave')->count(),
            ];
        });

        $months = collect(range(0, 11))->map(fn($i) => now()->subMonths($i)->format('Y-m'));

        return view('payroll.report', compact('summaries', 'months'));
    }

   public function getDates(Request $request)
{
    [$year, $mon] = explode('-', $request->month);

    $daysInMonth = Carbon::create($year, $mon)->daysInMonth;
    $dates = [];

    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::create($year, $mon, $d);

        // ❌ REMOVE THIS
        // if ($date->isSunday()) continue;

        $dates[] = $date->format('Y-m-d');
    }

    $existing = Attendance::where('employee_id', $request->employee_id)
        ->whereYear('date', $year)
        ->whereMonth('date', $mon)
        ->get()
        ->keyBy('date');

    $result = collect($dates)->map(function ($date) use ($existing) {
        $rec = $existing->get($date);
        return [
            'date'   => $date,
            'status' => $rec ? $rec->status : 'present',
        ];
    });

    return response()->json($result);
}

    public function bulkUpdate(Request $request)
    {
        foreach ($request->updates as $u) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'date'        => $u['date'],
                ],
                [
                    'status' => $u['status'],
                    'month'  => (int) date('m', strtotime($u['date'])),
                    'year'   => (int) date('Y', strtotime($u['date'])),
                    'branch' => Employee::find($request->employee_id)?->branch ?? '',
                ]
            );
        }

        return response()->json(['ok' => true]);
    }
}