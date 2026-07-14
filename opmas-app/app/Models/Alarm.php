<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $fillable = ['type', 'severity', 'message', 'resolved', 'resolved_at', 'resolved_by'];

    protected $casts = [
        'resolved'    => 'boolean',
        'resolved_at' => 'datetime',
        'resolved_by' => 'integer',
    ];

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('resolved', true);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'CRITICAL');
    }
}