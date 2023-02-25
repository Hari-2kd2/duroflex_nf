<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MonthlyWorkingDay extends Model
{
    protected $table = 'monthly_workingdays';
    protected $primaryKey = 'working_id';

    protected $fillable = [
        'working_id',
        'year',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'july',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
        'payroll_month',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
}
