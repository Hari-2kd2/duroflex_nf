<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PaidLeaveRule extends Model
{
    protected $table = 'paid_leave_rules';
    protected $primaryKey = 'paid_leave_rule_id';

    protected $fillable = [
        'paid_leave_rule_id', 'for_year','day_of_paid_leave'
    ];
}
