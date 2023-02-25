<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $table = 'cost_centers';
    protected $primaryKey = 'cost_center_id';

    protected $fillable = [
      'cost_center_id', 'sub_department_id', 'cost_center_number'
    ];

}
