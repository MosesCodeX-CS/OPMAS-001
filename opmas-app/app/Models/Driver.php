<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name',
        'protocol',
        'supports_holding_registers',
        'supports_input_registers',
        'supports_coils',
        'supports_discrete_inputs',
        'supports_writes',
        'max_registers_per_request',
        'max_concurrent_requests',
    ];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
