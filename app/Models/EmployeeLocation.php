<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLocation extends Model
{
    use HasFactory;
   protected $fillable = [
    'check_in_lat',
    'check_in_long',
    'break_in_lat',
    'break_in_long',
    'break_out_lat',
    'break_out_long',
    'check_out_lat',
    'check_out_long',
    'attendance_date',
    'attendance_time',
    'meeting_lat',
    'meeting_long',
    'meeting_date',
    'meeting_time',
    'locationcode',
];

}
