<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use App\Traits\HasCreatorAndUpdater;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory, NodeTrait, SoftDeletes, HasCreatorAndUpdater;

    public function isOwnedBy($userId)
    {
        return $this->created_by === $userId;
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(){
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function isRoot(){
        return $this->parent_id === null;
    }

    // For folder/file path
    protected static function boot(){
        parent::boot();

        static::creating(function($model){
            if(!$model->parent){
                return;
            }

            $model->path = (!$model->parent->isRoot() ? $model->parent->path . '/' : '') . Str::slug($model->name);
        });
    }
}
