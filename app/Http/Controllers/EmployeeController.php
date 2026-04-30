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
            'name' => 'required',
            'department' => 'required',
            'branch' => 'required',
            'basic_salary' => 'required|numeric',
        ]);

        $employee = Employee::findOrFail($id);

        $employee->update($request->all());

        return redirect('/employees')->with('success', 'Employee updated successfully');
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