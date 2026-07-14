<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'pressure', 'purity', 'flow_rate', 'temperature',
        'tank_level', 'compressor_status', 'bed_a_status', 'bed_b_status',
    ];

    protected $casts = [
        'pressure'          => 'float',
        'purity'            => 'float',
        'flow_rate'         => 'float',
        'temperature'       => 'float',
        'tank_level'        => 'float',
        'compressor_status' => 'integer',
        'bed_a_status'      => 'integer',
        'bed_b_status'      => 'integer',
    ];

    public static function latest_reading(): ?self
    {
        return self::latest()->first();
    }

    public function compressorStatusLabel(): string
    {
        return match($this->compressor_status) {
            1       => 'RUNNING',
            2       => 'FAULT',
            default => 'OFF',
        };
    }
}