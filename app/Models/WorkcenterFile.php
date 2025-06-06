<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterFile extends Model
{
    protected $table = 'workcenter_files';

    protected $fillable = [
        'workcenter_structure_id',
        'path',
        'user_create_id',
        'user_update_id',
    ];
}
