<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'employee_id',
        'branch',
        'month',
        'year',
        'basic_salary',
        'present_days',
        'absent_days',
        'leave_days',
        'late_count',
        'night_duties',
        'overtime_hours',
        'bonus',
        'advance',
        'deductions',
    ];
}