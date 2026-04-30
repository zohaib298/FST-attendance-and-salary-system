<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{

    public function store(Request $request)
    {
        $date = date('Y-m-d');

        foreach ($request->attendance as $employee_id => $status) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'date' => $date
                ],
                [
                    'status' => $status
                ]
            );
        }

        return back();
    }
}
