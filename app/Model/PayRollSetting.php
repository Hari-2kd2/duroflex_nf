<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PayRollSetting extends Model
{
    protected $table = 'payroll_settings';
    protected $primaryKey = 'payset_id';

    protected $fillable = [
        'payset_id', 'attendance_bonus', 'ot_per_hour', 'employee_esic', 'employer_esic', 'employee_pf', 'employer_pf', 'service_charge', 'bonus', 'el_amount', 'el_day_limit', 'lwf',
        'year_closing', 'other_allowance', 'other_deduction', 'created_at', 'created_by', 'updated_at', 'updated_by'
    ];
}
