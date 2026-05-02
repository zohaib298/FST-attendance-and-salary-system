<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function store(Request $request)
    {
        $today = now()->toDateString();

        $officeStart = Carbon::createFromTime(9, 30, 0);
        $officeEnd   = Carbon::createFromTime(19, 0, 0);
        $nightLimit  = Carbon::createFromTime(22, 0, 0);

        foreach ($request->attendance as $employee_id => $status) {

            $checkInRaw  = $request->checkin[$employee_id] ?? null;
            $checkOutRaw = $request->checkout[$employee_id] ?? null;

            $late = 0;
            $overtimeMinutes = 0;
            $nightMinutes = 0;

            // SAFE PARSE
            $checkIn  = $checkInRaw  ? Carbon::parse($today . ' ' . $checkInRaw) : null;
            $checkOut = $checkOutRaw ? Carbon::parse($today . ' ' . $checkOutRaw) : null;

            // ✅ LATE FIX (MAIN ISSUE WAS HERE)
            if ($checkIn && $checkIn->gt($officeStart)) {
                $late = 1;
            }

            // OVERTIME
            if ($checkOut && $checkOut->gt($officeEnd)) {
                $overtimeMinutes = $officeEnd->diffInMinutes($checkOut);
            }

            // NIGHT
            if ($checkOut && $checkOut->gt($nightLimit)) {
                $nightMinutes = $nightLimit->diffInMinutes($checkOut);
            }

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee_id,
                    'date' => $today,
                ],
                [
                    'status' => $status,
                    'check_in' => $checkInRaw,
                    'check_out' => $checkOutRaw,
                    'late' => $late,
                    'overtime_minutes' => $overtimeMinutes,
                    'night_minutes' => $nightMinutes,
                ]
            );
        }

        return back()->with('success', 'Attendance Saved');
    }
}