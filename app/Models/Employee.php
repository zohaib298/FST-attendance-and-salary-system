<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Advance;

class Employee extends Model
{
    protected $fillable = [
    'name',
    'cnic',
    'department',
    'branch',
    'basic_salary',
    'bike_allowance',
    'mobile_allowance',
    'overtime_rate',
    'commission',
    'other_allowance',
    'late_deduction',
    'absent_deduction',
    'advance',
    ];

    public function advances()
    {
        return $this->hasMany(Advance::class, 'employee_id');
    }
}