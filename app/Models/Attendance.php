<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
       protected $fillable = [
     'employee_id',
    'branch',
    'date',
    'status',
    'check_in',
    'check_out',
    'late',
    'overtime',
    'night',
    'bonus',
    'advance',
    'deductions',  // ← yeh add karo
    'notes',
    'month',
    'year',
    'basic_salary_override',
    ];

    public function employee()
{
    return $this->belongsTo(\App\Models\Employee::class);
}
}
