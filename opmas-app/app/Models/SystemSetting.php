<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key, with a fallback value.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value): bool
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
        return (bool) $setting;
    }
}
