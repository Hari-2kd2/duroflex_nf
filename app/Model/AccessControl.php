<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AccessControl extends Model{
    
    protected $table = 'employee_access_control';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'employee', 'department', 'device', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];

}
