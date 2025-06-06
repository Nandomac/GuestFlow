<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterPart extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'partno_id',
        'workcenter_structure_id',
        'user_create_id',
    ];

    protected $dates = ['deleted_at'];

    public function workcenterStructure()
    {
        return $this->belongsTo(WorkcenterStructure::class, 'workcenter_structure_id');
    }

    public function workcenterPartCharacteristics()
    {
        return $this->hasMany(WorkcenterPartCharacteristic::class, 'workcenter_part_id');
    }

    public function getCharacteristics()
    {
        return $this->workcenterPartCharacteristics()->with('characteristic')->get();
    }

}
