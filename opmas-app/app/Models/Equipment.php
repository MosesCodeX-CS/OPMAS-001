<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['code', 'name', 'status', 'last_service', 'next_service', 'notes'];

    protected $casts = [
        'last_service' => 'date',
        'next_service' => 'date',
    ];
}