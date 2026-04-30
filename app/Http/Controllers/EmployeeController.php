<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // 📄 LIST EMPLOYEES
    public function index()
    {
        $employees = Employee::all();
        return view('employees.index', compact('employees'));
    }

    // ➕ CREATE PAGE
    public function create()
    {
        return view('employees.create');
    }

    // 💾 STORE EMPLOYEE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'basic_salary' => 'required|numeric',
        ]);

        Employee::create([
            'name' => $request->name,
            'cnic' => $request->cnic,
            'department' => $request->department,
            'branch' => $request->branch,
            'basic_salary' => $request->basic_salary,

            // 💰 Allowances (default 0)
            'bike_allowance' => $request->bike_allowance ?? 0,
            'mobile_allowance' => $request->mobile_allowance ?? 0,
            'overtime_rate' => $request->overtime_rate ?? 0,
            'commission' => $request->commission ?? 0,
            'other_allowance' => $request->other_allowance ?? 0,

            // ❌ Deductions (default 0)
            'late_deduction' => $request->late_deduction ?? 0,
            'absent_deduction' => $request->absent_deduction ?? 0,
            'allowed_leaves' => $request->allowed_leaves ?? 0,
        ]);

        return redirect('/employees')->with('success', 'Employee added successfully');
    }

    // ✏️ EDIT PAGE
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    // 🔄 UPDATE EMPLOYEE
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
            'allowed_leaves' => $request->allowed_leaves ?? 0,
        ]);

        return redirect('/employees')->with('success', 'Employee updated successfully');
    }

    // ❌ DELETE EMPLOYEE
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();

        return redirect('/employees')->with('success', 'Employee deleted successfully');
    }

    // 👤 PROFILES PAGE
    public function profiles()
    {
        $employees = Employee::all();
        return view('employees.profiles', compact('employees'));
    }
}