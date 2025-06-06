<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkcenterDowntimeEmail extends Model
{
    use SoftDeletes;

    protected $table = 'workcenter_downtime_emails';

    protected $fillable = [
        'workcenter_downtime_id',
        'email',
        'user_create_id'
    ];

    public function getCreatedAtFormatted()
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}
