<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employee';
    protected $primaryKey = 'employee_id';
    protected $with = ['branch:branch_id,branch_name', 'department:department_id,department_name', 'designation:designation_id,designation_name', 'subdepartment:sub_department_id,sub_department_name', 'costcenter:cost_center_id,cost_center_number'];
    protected $fillable = [
        'employee_id', 'user_id', 'finger_id', 'department_id', 'designation_id', 'branch_id', 'supervisor_id', 'work_shift_id', 'email', 'first_name',
        'last_name', 'date_of_birth', 'date_of_joining', 'date_of_leaving', 'gender', 'marital_status',
        'photo', 'address', 'emergency_contacts', 'phone', 'document_title', 'document_name', 'document_expiry', 'document_title2', 'document_name2', 'document_expiry2', 'document_title3', 'document_name3', 'document_expiry3',
        'document_title4', 'document_name4', 'document_expiry4', 'document_title5', 'document_name5', 'document_expiry5', 'status', 'created_by',
        'updated_by', 'religion', 'pay_grade_id', 'hourly_salaries_id', 'esi_card_number', 'pf_account_number', 'device_employee_id',
        'bank_name',
        'bank_branch',
        'bank_account_no',
        'bank_of_the_city',
        'ifsc_no',
        'pan_no',
        'official_email',
        'aadhar_no',
        'father_name',
        'weekoff_updated_at',
        'leave_balance',
        'cost_center_id',
        'basic_amt',
        'da_amt',
        'hra_amt',
        'daily_wage',
        'sub_department_id',
        'leave_updated_at',
        'service_charge',
    ];
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFilter($query, $request)
    {
        return $query->where('department_id', $request['department_id'])->where('branch_id', $request['branch_id'])->where('employee_id', $request['employee_id']);
    }

    public function userName()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function subdepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function payGrade()
    {
        return $this->belongsTo(PayGrade::class, 'pay_grade_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function hourlySalaries()
    {
        return $this->belongsTo(HourlySalary::class, 'hourly_salaries_id');
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function payroll()
    {
        return $this->hasOne(PayRoll::class, 'employee', 'employee_id');
    }

    public function scopePayroll($query, $request)
    {
        return $query->where('employee', $request->employee_id)->orWhere('finger_print_id', $request->finger_print_id);
    }
}
