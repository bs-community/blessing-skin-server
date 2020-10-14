<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int        $tid
 * @property string     $name
 * @property string     $type
 * @property string     $hash
 * @property int        $size
 * @property int        $uploader
 * @property bool       $public
 * @property Carbon     $upload_at
 * @property int        $likes
 * @property string     $model
 * @property User       $owner
 * @property Collection $likers
 */
class Texture extends Model
{
    use HasFactory;

    public $primaryKey = 'tid';
    public const CREATED_AT = 'upload_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'name', 'type', 'uploader', 'public', 'likes',
    ];

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

    public function owner()
    {
        return $this->belongsTo(User::class, 'uploader');
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'user_closet')->withPivot('item_name');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
