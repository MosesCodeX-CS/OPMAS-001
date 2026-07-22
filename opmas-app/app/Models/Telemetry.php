<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telemetry extends Model
{
    protected $table = 'telemetry';
    protected $fillable = ['poll_cycle_id', 'register_definition_id', 'raw_value', 'device_timestamp', 'collector_timestamp', 'quality', 'poll_duration_ms'];

    public function pollCycle()
    {
        return $this->belongsTo(PollCycle::class);
    }

    public function definition()
    {
        return $this->belongsTo(RegisterDefinition::class, 'register_definition_id');
    }
}
