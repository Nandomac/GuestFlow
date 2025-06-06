<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Characteristic extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'description',
        'type',
        'uom',
        'datetype',
        'id_bdlab',
        'user_create_id'
    ];

    public function workcenters()
    {
        return $this->belongsToMany(
            WorkcenterStructure::class,
            'workcenter_template',
            'characteristic_id',
            'workcenter_structure_id',
            )->withPivot('id','cols', 'order')->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function characteristicGroup()
    {
        return $this->belongsTo(CharacteristicGroup::class, 'characteristic_group_id');
    }

    public function workcenterPartCharacteristics()
    {
        return $this->hasMany(WorkcenterPartCharacteristic::class, 'characteristic_id');
    }
}
