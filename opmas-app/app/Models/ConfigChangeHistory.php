<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigChangeHistory extends Model
{
    protected $fillable = ['table_name', 'record_id', 'field', 'old_value', 'new_value', 'changed_by', 'changed_at', 'reason'];
}
