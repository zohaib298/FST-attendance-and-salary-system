<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'attendance' => 'required|array',
        'checkin' => 'nullable|array',
        'checkout' => 'nullable|array',
    ]);

    $today = now()->toDateString();

    foreach ($request->attendance as $employee_id => $status) {

        // only allowed values
        if (!in_array($status, ['present', 'absent', 'leave'])) {
            continue;
        }

        Attendance::updateOrCreate(
            [
                'employee_id' => $employee_id,
                'date' => $today,
            ],
            [
                // MAIN STATUS
                'status' => $status,

                // CHECK IN / OUT
                'check_in' => $request->checkin[$employee_id] ?? null,
                'check_out' => $request->checkout[$employee_id] ?? null,
            ]
        );
    }

    return back()->with('success', 'Attendance saved successfully');
}
}