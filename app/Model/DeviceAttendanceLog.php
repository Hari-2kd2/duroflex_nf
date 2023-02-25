<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeviceAttendanceLog extends Model
{
    protected $table='ms_sql';
    protected $primaryKey='primary_id';

    protected $fillable = [
        'primary_id',
        'evtlguid',
        'ID',
        'device_employee_id',
        'employee',
        'devdt',
        'datetime',
        'punching_time',
        'type',
        'sms_log',
        'devuid',
        'device',
        'device_name',
        'live_status',
        'status',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'devdt', 
	'evtlguid', 
	'local_primary_id'
    ];


    public function deviceinfo(){
        return $this->belongsTo(Device::class,'id');
    }

    public function employeeinfo(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }




}
