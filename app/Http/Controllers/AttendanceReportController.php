<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::all();

        $query = Attendance::query()->with('employee');

        
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('month')) {
            $query->whereYear('date', substr($request->month, 0, 4))
                  ->whereMonth('date', substr($request->month, 5, 2));
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return view('payroll.report', compact(
            'employees',
            'attendances'
        ));
    }
}