<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterGroup extends Model
{
    protected $fillable = ['equipment_id', 'name', 'display_order'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
