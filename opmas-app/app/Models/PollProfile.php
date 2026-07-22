<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollProfile extends Model
{
    protected $fillable = ['name', 'interval_seconds', 'priority', 'enabled'];
}
