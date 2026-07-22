<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlarmRule extends Model
{
    protected $fillable = ['register_definition_id', 'condition', 'threshold', 'severity', 'enabled'];

    public function definition()
    {
        return $this->belongsTo(RegisterDefinition::class, 'register_definition_id');
    }
}
