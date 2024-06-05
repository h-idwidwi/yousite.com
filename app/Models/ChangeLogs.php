<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLogs extends Model
{
    protected $fillable = ['entity_type', 'entity_id', 'before', 'after', 'changed_by'];
}

