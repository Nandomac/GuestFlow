<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterTemplate extends Model
{
    use SoftDeletes;
    protected $table = 'workcenter_template';
    protected $fillable = [
        'workcenter_structure_id',
        'characteristic_id',
        'cols',
        'order',
        'parent_id',
        'characteristic_group_id',
        'characteristic_group_order',
        'user_create_id',
        'user_update_id'
    ];
}
