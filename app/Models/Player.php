<?php

namespace App\Models;

use App\Events\PlayerProfileUpdated;
use App\Models;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = 'last_modified';

    public $primaryKey = 'pid';
    protected $fillable = ['uid', 'name', 'last_modified'];

    protected $casts = [
        'pid' => 'integer',
        'uid' => 'integer',
        'tid_skin' => 'integer',
        'tid_cape' => 'integer',
    ];

    protected $dispatchesEvents = [
        'retrieved' => \App\Events\PlayerRetrieved::class,
        'updated' => PlayerProfileUpdated::class,
    ];

    public function user()
    {
        return $this->belongsTo(Models\User::class, 'uid');
    }

    public function skin()
    {
        return $this->belongsTo(Models\Texture::class, 'tid_skin');
    }

    public function cape()
    {
        return $this->belongsTo(Models\Texture::class, 'tid_cape');
    }

    public function getModelAttribute()
    {
        return optional($this->skin)->model ?? 'default';
    }

    /**
     * CustomSkinAPI R1.
     */
    public function toJson($options = 0)
    {
        $model = $this->model;
        $profile = [
            'username' => $this->name,
            'skins' => [
                $model => optional($this->skin)->hash,
            ],
            'cape' => optional($this->cape)->hash,
        ];

        return json_encode($profile, $options | JSON_UNESCAPED_UNICODE);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
