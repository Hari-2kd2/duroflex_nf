<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ManualAttendance extends Model
{
    protected $table      = 'manual_attendance';
    protected $primaryKey = 'primary_id';
    protected $fillable = [
        'primary_id',
        'ID',
        'type',
        'datetime',
        'status',
        'device_name',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
   
}
