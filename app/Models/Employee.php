<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    'allowed_leaves'
];
}
