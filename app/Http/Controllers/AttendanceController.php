<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        // Validation (important)
        $request->validate([
            'attendance' => 'required|array',
        ]);

        // Use Laravel helper (better than date())
        $today = now()->toDateString();

        foreach ($request->attendance as $employee_id => $status) {

            // Extra safety (optional but good)
            if (!in_array($status, ['present', 'absent', 'leave'])) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'date' => $today,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return back()->with('success', 'Attendance saved successfully');
    }
}