<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();

        $today = now()->toDateString();

        $present = Attendance::whereDate('date', $today)->where('status', 'present')->count();
        $absent  = Attendance::whereDate('date', $today)->where('status', 'absent')->count();
        $leave   = Attendance::whereDate('date', $today)->where('status', 'leave')->count();

        return view('employees.index', compact('employees', 'present', 'absent', 'leave'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'department' => 'required',
            'branch' => 'required',
            'basic_salary' => 'required|numeric',
        ]);

        Employee::create($request->all());

        return redirect('/employees')->with('success', 'Employee added successfully');
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'department' => 'required|string',
        'branch' => 'required|string',
        'basic_salary' => 'required|numeric|min:0',
    ]);

    $employee = Employee::findOrFail($id);

    $employee->update([
        'name' => $request->name,
        'cnic' => $request->cnic,
        'department' => $request->department,
        'branch' => $request->branch,
        'basic_salary' => $request->basic_salary,

        'bike_allowance' => $request->bike_allowance ?? 0,
        'mobile_allowance' => $request->mobile_allowance ?? 0,
        'overtime_rate' => $request->overtime_rate ?? 0,
        'commission' => $request->commission ?? 0,
        'other_allowance' => $request->other_allowance ?? 0,

        'late_deduction' => $request->late_deduction ?? 0,
        'absent_deduction' => $request->absent_deduction ?? 0,
        'advance' => $request->advance,

    ]);

    return redirect('/employees/profiles')->with('success', 'Employee updated successfully');
}
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();
        return redirect('/employees');
    }

    public function profiles()
    {
        $employees = Employee::all();
        return view('employees.profiles', compact('employees'));
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }
}