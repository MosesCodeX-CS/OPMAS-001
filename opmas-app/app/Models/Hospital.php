<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $fillable = ['code', 'name', 'country', 'notes'];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}
