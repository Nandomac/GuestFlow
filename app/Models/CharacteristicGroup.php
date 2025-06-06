<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacteristicGroup extends Model
{
    use SoftDeletes;
    protected $table = 'characteristic_groups';

    protected $fillable = [
        'name',
        'user_create_id',
        'user_update_id',
    ];

    public function characteristics()
    {
        return $this->hasMany(Characteristic::class);
    }

    
}


