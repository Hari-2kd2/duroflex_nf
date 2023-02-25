<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    protected $table = 'company_holiday';
    protected $primaryKey = 'company_holiday_id';

    protected $fillable = [
        'employee_id', 'fdate', 'tdate', 'comment', 'created_by', 'updated_by',
    ];
}
