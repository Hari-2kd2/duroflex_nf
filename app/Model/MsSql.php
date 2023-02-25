<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MsSql extends Model
{
    protected $table = "ms_sql";
    protected $primaryKey = 'primary_id';
    // protected $primaryKey = null;
    // public $incrementing = false;
    protected $fillable = [
        'primary_id',
        'ID',
        'datetime',
        'punching_time',
        'device_employee_id',
        'sms_log',
        'devuid',
        'device',
        'device_name',
        'live_status',
        'employee',
        'status',
        'type',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'devdt', 'evtlguid', 'local_primary_id'
    ];

    protected $cast = [
        'devdt', 'evtlguid', 'live_status', 'type', 'sms_log',
        'devuid', 'device', 'device_employee_id', 'punching_time',
    ];

    public function deviceinfo()
    {
        return $this->belongsTo(Device::class, 'id');
    }

    public function employeeinfo()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}
