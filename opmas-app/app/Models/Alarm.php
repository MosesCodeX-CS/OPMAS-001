<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $fillable = ['type', 'severity', 'message', 'resolved', 'resolved_at'];

    protected $casts = [
        'resolved'    => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('resolved', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'CRITICAL');
    }
}