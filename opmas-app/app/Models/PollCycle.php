<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollCycle extends Model
{
    protected $fillable = ['equipment_id', 'started_at', 'finished_at', 'status', 'duration'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
