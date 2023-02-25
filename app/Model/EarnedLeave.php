<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EarnedLeave extends Model
{
    protected $table = 'earned_leave';
    protected $primaryKey = 'earned_leave_id';

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'el_balance',
        'el',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function employeeinfo()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
