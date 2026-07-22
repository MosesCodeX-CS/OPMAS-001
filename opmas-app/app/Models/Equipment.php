<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['code', 'name', 'status', 'last_service', 'next_service', 'notes', 'site_id', 'driver_id', 'manufacturer', 'model', 'device_type', 'location', 'ip_address', 'port', 'unit_id', 'poll_interval', 'enabled', 'last_seen'];

    protected $casts = [
        'last_service' => 'date',
        'next_service' => 'date',
        'last_seen' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function registerGroups()
    {
        return $this->hasMany(RegisterGroup::class);
    }

    public function registerDefinitions()
    {
        return $this->hasMany(RegisterDefinition::class);
    }
}