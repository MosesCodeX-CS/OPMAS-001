<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterDefinition extends Model
{
    protected $fillable = ['equipment_id', 'register_group_id', 'poll_profile_id', 'address', 'register_type', 'data_type', 'display_order', 'enabled', 'graph_enabled'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function group()
    {
        return $this->belongsTo(RegisterGroup::class, 'register_group_id');
    }

    public function versions()
    {
        return $this->hasMany(RegisterDefinitionVersion::class);
    }

    public function activeVersion(): ?RegisterDefinitionVersion
    {
        return $this->versions()
            ->where('effective_from', '<=', now())
            ->orderByDesc('effective_from')
            ->first();
    }
}
