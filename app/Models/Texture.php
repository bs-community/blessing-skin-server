<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Texture extends Model
{
    public $primaryKey = 'tid';
    public $timestamps = false;

    protected $casts = [
        'tid' => 'integer',
        'size' => 'integer',
        'uploader' => 'integer',
        'public' => 'boolean',
    ];

    protected $appends = [
        'likes',
    ];

    public function getLikesAttribute()
    {
        return $this->likers()->count();
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
