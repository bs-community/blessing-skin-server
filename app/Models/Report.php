<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public const CREATED_AT = 'report_at';
    public const UPDATED_AT = null;

    public const PENDING  = 0;
    public const RESOLVED = 1;
    public const REJECTED = 2;

    protected $casts = [
        'tid' => 'integer',
        'uploader' => 'integer',
        'reporter' => 'integer',
        'status' => 'integer',
    ];

    public function texture()
    {
        return $this->belongsTo(Texture::class, 'tid', 'tid');
    }

    public function informer()
    {
        return $this->belongsTo(User::class, 'reporter', 'uid');
    }
}
