<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StarredFile extends Model
{
    protected $fillable = ['file_id', 'user_id', 'created_at', 'updated_at'];
}
