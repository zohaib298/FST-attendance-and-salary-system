<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::all();
        $attendances = collect();

        if ($request->employee_id && $request->month) {

            $attendances = Attendance::where('employee_id', $request->employee_id)
                ->whereYear('date', substr($request->month, 0, 4))
                ->whereMonth('date', substr($request->month, 5, 2))
                ->get();
        }

        return view('payroll.report', compact('employees', 'attendances'));
}
}