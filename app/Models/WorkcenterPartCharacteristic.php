<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterPartCharacteristic extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'workcenter_part_id',
        'characteristic_id',
        'cols',
        'order',
        'characteristic_group_id',
        'nominal_value',
        'tolerance_value',
        'user_create_id',
    ];

    public function workcenterPart()
    {
        return $this->belongsTo(WorkcenterPart::class, 'workcenter_part_id');
    }

    public function characteristic()
    {
        return $this->belongsTo(Characteristic::class, 'characteristic_id');
    }

    public function characteristicGroup()
    {
        return $this->belongsTo(CharacteristicGroup::class, 'characteristic_group_id');
    }

    public function getCharacteristicGroup()
    {
        return $this->characteristicGroup()->with('characteristicGroup')->get();
    }
}
