<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');

        $employees = Employee::query();

        if ($request->search) {
            $employees->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('cnic', 'like', '%' . $request->search . '%')
                      ->orWhere('department', 'like', '%' . $request->search . '%');
        }

        if ($request->branch) {
            $employees->where('branch', $request->branch);
        }

        $employees = $employees->latest()->get();

        $present = Attendance::whereDate('date', $today)
            ->where('status', 'present')
            ->count();

        $absent = Attendance::whereDate('date', $today)
            ->where('status', 'absent')
            ->count();

        $leave = Attendance::whereDate('date', $today)
            ->where('status', 'leave')
            ->count();

        $todayAttendance = Attendance::whereDate('date', $today)
            ->get()
            ->keyBy('employee_id');

        return view('employees.index', compact(
            'employees',
            'present',
            'absent',
            'leave',
            'todayAttendance'
        ));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'cnic' => 'required' ,
            'department' => 'required',
            'branch' => 'required',
            'basic_salary' => 'required'
        ]);

        Employee::create($request->all());

        return redirect('/employees')->with('success', 'Employee added');
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
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
            'advance' => $request->advance ?? 0,
        ]);

        return redirect('/employees')->with('success', 'Employee updated');
    }

    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();

        return redirect('/employees')->with('success', 'Employee deleted');
    }

    public function profiles(Request $request)
    {
        $employees = Employee::query();

        if ($request->search) {
            $employees->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('cnic', 'like', '%' . $request->search . '%')
                      ->orWhere('department', 'like', '%' . $request->search . '%')
                      ->orWhere('branch', 'like', '%' . $request->search . '%');
        }

        $employees = $employees->latest()->get();

        return view('employees.profiles', compact('employees'));
    }

    // 📌 single employee page
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }
}