<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PayRoll extends Model
{
    protected $table = 'payroll';
    protected $primaryKey = 'payroll_id';

    protected $fillable = [
        'payroll_id',
        'employee',
        'finger_print_id',
        'month',
        'year',
        'unit',
        'tom',
        'service_provider',
        'department',
        'no_day_wages',
        'ph',
        'total_days',
        'per_day_basic_da',
        'per_day_basic',
        'per_day_da',
        'per_day_hra',
        'per_day_wages',
        'basic_da_amount',
        'basic_amount',
        'da_amount',
        'hra_amount',
        'wages_amount',
        'attendance_bonus',
        'ot_hours',
        'ot_per_hours',
        'ot_amount',
        'gross_salary',
        'employee_pf',
        'employee_pf_percentage',
        'employee_esic',
        'employee_esic_percentage',
        'canteen',
        'net_salary',
        'employer_pf',
        'employer_pf_percentage',
        'employer_esic',
        'employer_esic_percentage',
        'service_charge_percentage',
        'service_charge',
        'bonus_percentage',
        'bonus_amount',
        'earned_leave_balance',
        'earned_leave',
        'leave_amount',
        'manhours',
        'manhours_amount',
        'manhour_days',
        'salary', 'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'el_bonus',
        'retained_bonus',
        'retained_service_charge',
        'retained_attendance_bonus',
        'retained_leave_amount',
        'lwf',
        'employee_total_deduction',
        'employer_total_deduction',
        'branch',
        'costcenter',
        'date',
        'fdate',
        'tdate',
        'company_holiday',
    ];

    public function employeeinfo()
    {
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
