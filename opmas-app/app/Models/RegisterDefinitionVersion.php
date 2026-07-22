<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterDefinitionVersion extends Model
{
    protected $fillable = ['register_definition_id', 'name', 'description', 'scale', 'offset', 'unit', 'decimals', 'effective_from'];

    public function definition()
    {
        return $this->belongsTo(RegisterDefinition::class, 'register_definition_id');
    }
}
