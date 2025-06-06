<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopOrder extends Model
{
    use SoftDeletes;

    public $timestamps = true;
    protected $table = 'shop_orders';

    protected $fillable = [
        'op_id',
        'workcenter_id',
        'order_no',
        'release_no',
        'sequence_no',
        'date',
        'state',
        'user_create_id',
        'user_update_id',
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
