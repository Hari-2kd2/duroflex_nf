<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table      = "view_employee_in_out_data_new";
    protected $primaryKey = "employee_attendance_id";
    protected $fillable  = [
        'finger_print_id', 'in_time', 'out_time', 'date', 'working_time', 'inout_status', 'status' . 'created_at', 'updated_at',

    ];

}
