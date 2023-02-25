<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeInOutData extends Model
{

    protected $table = 'view_employee_in_out_data';
    protected $primaryKey = 'employee_attendance_id';
    protected $fillable = [
        "employee_attendance_id", "finger_print_id", "date", "in_time_from", "in_time", "out_time", "out_time_upto", "working_time", "working_hour", "in_out_time", "status", "created_at", "updated_at",
        "over_time", "early_by", "late_by", "shift_name", "device_name", "live_status", 'created_by', 'updated_by', 'over_time_status' , 'attendance_status'
    ];

}
