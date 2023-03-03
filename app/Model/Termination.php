<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Termination extends Model
{
    protected $table = 'termination';
    protected $primaryKey = 'termination_id';

    protected $fillable = [
        'termination_id', 'terminate_to', 'finger_print_id', 'terminate_by', 'termination_type', 'subject', 'notice_date', 'termination_date', 'description', 'status'
    ];

    public function terminateTo()
    {
        return $this->belongsTo(Employee::class, 'terminate_to')->without('branch', 'department', 'designation', 'costcenter', 'subdepartment');
    }

    public function terminateBy()
    {
        return $this->belongsTo(Employee::class, 'terminate_by')->without('branch', 'department', 'designation', 'costcenter', 'subdepartment');
    }
}
