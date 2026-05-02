<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // FILTER: employee
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // FILTER: search
        if ($request->search) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }

        // FILTER: month
        if ($request->month) {
            $month = Carbon::parse($request->month);
            $query->whereMonth('date', $month->month)
                  ->whereYear('date', $month->year);
        }

        $attendances = $query->latest()->get();
        $employees = Employee::all();

        return view('payroll.report', compact('attendances', 'employees'));
    }
}