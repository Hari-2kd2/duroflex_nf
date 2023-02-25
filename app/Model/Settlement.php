<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $table = 'el_bonus';
    protected $primaryKey = 'elb_id';

    protected $fillable = [
        'elb_id',
        'employee',
        'finger_print_id',
        'amount',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'pay_status',
        'lwf_amount',
        'other_deduction',
        'dection_amount',
        'net_amount',
        'paid_at',
        'paid_on',
        'remarks',
        'department',
        'branch',
        'costcenter',
        'unit'
    ];

    public function employeeinfo(){
        return $this->belongsTo(Employee::class, 'employee');
    }

    public function departmentinfo()
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function branchInfo()
    {
        return $this->belongsTo(Branch::class, 'branch');
    }

    public function costcenterInfo()
    {
        return $this->belongsTo(CostCenter::class, 'costcenter');
    }

    public function subunitInfo()
    {
        return $this->belongsTo(SubDepartment::class, 'unit');
    }

}
