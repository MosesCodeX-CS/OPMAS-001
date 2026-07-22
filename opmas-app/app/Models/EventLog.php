<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $fillable = ['event_type', 'category', 'message', 'related_equipment_id', 'related_user_id', 'occurred_at'];
}
