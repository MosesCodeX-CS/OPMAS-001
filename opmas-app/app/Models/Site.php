<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = ['hospital_id', 'code', 'name', 'location', 'enabled'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
