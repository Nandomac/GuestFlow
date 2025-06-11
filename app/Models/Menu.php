<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = array('parent_id', 'title', 'route', 'order');

    public function parent()
    {
        return $this->belongsTo('App\Models\Menu', 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany('App\Models\Menu', 'parent_id')->with('childs')->orderBy('ordering');
    }

    public function childsAllRecursive()
    {
        return $this->childs()->with('childsAllRecursive');
    }
}
