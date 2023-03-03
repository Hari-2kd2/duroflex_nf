<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    protected $table = 'company_holiday';
    protected $primaryKey = 'company_holiday_id';

    protected $fillable = [
        'employee_id', 'fdate', 'tdate', 'comment', 'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id')->without('branch', 'department', 'designation', 'costcenter', 'subdepartment');
    }

    public function created_user()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'employee_id')->without('branch', 'department', 'designation', 'costcenter', 'subdepartment');
    }

    public function updated_user()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'employee_id')->without('branch', 'department', 'designation', 'costcenter', 'subdepartment');
    }
}
