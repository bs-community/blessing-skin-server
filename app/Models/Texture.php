<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Texture extends Model
{
    public $primaryKey = 'tid';
    public const CREATED_AT = 'upload_at';
    public const UPDATED_AT = null;

    protected $casts = [
        'tid' => 'integer',
        'size' => 'integer',
        'uploader' => 'integer',
        'public' => 'boolean',
        'likes' => 'integer',
    ];

    protected $dispatchesEvents = [
        'deleting' => \App\Events\TextureDeleting::class,
    ];

    public function getModelAttribute()
    {
        // Don't worry about cape...
        return $this->type === 'alex' ? 'slim' : 'default';
    }

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'user_closet')->withPivot('item_name');
    }
}
