<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\AttendanceSummary;
use App\Models\Setting;

class EmployeeController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    // INDEX — HR Dashboard
    // ════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        return app(AttendanceController::class)->index($request);
    }

    // ════════════════════════════════════════════════════════════════
    // CREATE
    // ════════════════════════════════════════════════════════════════
    public function create()
    {
        return view('employees.create');
    }

    // ════════════════════════════════════════════════════════════════
    // STORE — Basic employee save
    // ════════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'branch' => 'required|string',
        ]);

        Employee::create([
            'name'             => $request->input('name'),
            'department'       => $request->input('designation', ''),
            'branch'           => $request->input('branch'),
            'basic_salary'     => (float) $request->input('basic_salary',     0),
            'bike_allowance'   => (float) $request->input('bike_allowance',   0),
            'mobile_allowance' => (float) $request->input('mobile_allowance', 0),
            'other_allowance'  => (float) $request->input('other_allowance',  0),
            'commission'       => (float) $request->input('commission',       0),
            'night_rate'       => (float) $request->input('night_rate',       500),
            'late_deduction'   => (float) $request->input('late_deduction',   0),
            'absent_deduction' => (float) $request->input('absent_deduction', 0),
            'overtime_rate'    => (float) $request->input('overtime_rate',    0),
        ]);

        return redirect()->back()->with('success', 'Employee add ho gaya!');
    }

    // ════════════════════════════════════════════════════════════════
    // STORE WITH MONTHLY — Dashboard modal se employee + us month ka data
    // ════════════════════════════════════════════════════════════════
    public function storeWithMonthly(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'branch' => 'required|string',
        ]);

        $branch = $request->input('branch');
        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);

        // ── 1. Employee banao ──
        $employee = Employee::create([
            'name'             => $request->input('name'),
            'department'       => $request->input('designation', ''),
            'branch'           => $branch,
            'basic_salary'     => (float) $request->input('basic_salary',     0),
            'bike_allowance'   => (float) $request->input('bike_allowance',   0),
            'mobile_allowance' => (float) $request->input('mobile_allowance', 0),
            'other_allowance'  => (float) $request->input('other_allowance',  0),
            'commission'       => (float) $request->input('commission',       0),
            'night_rate'       => (float) $request->input('night_rate',       500),
            'late_deduction'   => (float) $request->input('late_deduction',   0),
            'absent_deduction' => (float) $request->input('absent_deduction', 0),
            'overtime_rate'    => (float) $request->input('overtime_rate',    0),
        ]);

        // ── 2. Us month ka summary save karo ──
        $presentDays   = (int)   $request->input('present_days',   0);
        $absentDays    = (int)   $request->input('absent_days',    0);
        $leaveDays     = (int)   $request->input('leave_days',     0);
        $lateCount     = (int)   $request->input('late_count',     0);
        $nightDuties   = (int)   $request->input('night_duties',   0);
        $overtimeHours = (float) $request->input('overtime_hours', 0);
        $bonus         = (float) $request->input('bonus',          0);
        $advance       = (float) $request->input('advance',        0);
        $deductions    = (float) $request->input('deductions',     0);

        $lateToAbsent = (int) (Setting::where('key', 'late_to_absent')->value('value') ?? 3);
        $lateAbsents  = (int) floor($lateCount / max(1, $lateToAbsent));

        AttendanceSummary::updateOrCreate(
            [
                'employee_id' => $employee->id,
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
                'bonus'          => $bonus,
                'advance'        => $advance,
                'deductions'     => $deductions,
                'basic_salary'   => $employee->basic_salary,
            ]
        );

        $monthName = Carbon::create($year, $month)->format('F Y');

        return redirect()
            ->to("/?branch={$branch}&month={$month}&year={$year}")
            ->with('success', "{$employee->name} add ho gaya — {$branch}, {$monthName}");
    }

    // ════════════════════════════════════════════════════════════════
    // EDIT
    // ════════════════════════════════════════════════════════════════
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    // ════════════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════════════
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'branch' => 'required|string',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update([
            'name'             => $request->input('name'),
            'department'       => $request->input('designation',   $employee->department),
            'branch'           => $request->input('branch'),
            'basic_salary'     => (float) $request->input('basic_salary',     $employee->basic_salary),
            'bike_allowance'   => (float) $request->input('bike_allowance',   $employee->bike_allowance),
            'mobile_allowance' => (float) $request->input('mobile_allowance', $employee->mobile_allowance),
            'other_allowance'  => (float) $request->input('other_allowance',  $employee->other_allowance),
            'commission'       => (float) $request->input('commission',        $employee->commission),
            'night_rate'       => (float) $request->input('night_rate',        $employee->night_rate ?? 500),
            'late_deduction'   => (float) $request->input('late_deduction',   $employee->late_deduction),
            'absent_deduction' => (float) $request->input('absent_deduction', $employee->absent_deduction),
            'overtime_rate'    => (float) $request->input('overtime_rate',    $employee->overtime_rate),
        ]);

        return redirect()->back()->with('success', 'Employee update ho gaya!');
    }

    // ════════════════════════════════════════════════════════════════
    // DESTROY
    // ════════════════════════════════════════════════════════════════
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Employee delete ho gaya!');
    }

    // ════════════════════════════════════════════════════════════════
    // PROFILES
    // ════════════════════════════════════════════════════════════════
    public function profiles(Request $request)
    {
        $employees = Employee::all();
        return view('employees.profiles', compact('employees'));
    }
}