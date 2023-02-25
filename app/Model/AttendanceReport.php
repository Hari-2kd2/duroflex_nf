<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AttendanceReport extends Model
{
    protected $table ="attendance_report";
    protected $fillable = [

        'finger_print_id',
        'in_time',
        'working_time',
        'status',
        'created_at',
        'updated_at',
    ];
}
