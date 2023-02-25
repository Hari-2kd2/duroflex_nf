<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
    protected $table = 'sub_departments';
    protected $primaryKey = 'sub_department_id';

    protected $fillable = [
      'sub_department_id',  'department_id', 'sub_department_name'
    ];

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'sub_department_id', 'sub_department_id');
    }

}
