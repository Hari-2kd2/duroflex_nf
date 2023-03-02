<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DailyCostToCompany extends Model
{
    protected $table = 'daily_cost_to_company';
    protected $primaryKey = 'daily_cost_to_company_id';

    protected $fillable = [
        'date',
        'contractor',
        'staff',
        'employee',
        'present',
        'absent',
        'contractor_ctc',
        'staff_ctc',
        'total_ctc'
    ];
}