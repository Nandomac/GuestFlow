<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterDowntime extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'downtime_cause_id',
        'workcenter_structure_id',
        'user_create_id',
    ];

    protected $dates = ['deleted_at'];

    public function workcenterStructure()
    {
        return $this->belongsTo(WorkcenterStructure::class, 'workcenter_structure_id');
    }

    public function email()
    {
        return $this->hasMany(WorkcenterDowntimeEmail::class, 'workcenter_downtime_id');
    }
}
