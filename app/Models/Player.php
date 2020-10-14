<?php

namespace App\Models;

use App\Events\PlayerProfileUpdated;
use App\Models;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lorisleiva\LaravelSearchString\Concerns\SearchString;

/**
 * @property int     $pid
 * @property int     $uid
 * @property string  $name
 * @property int     $tid_skin
 * @property int     $tid_cape
 * @property Carbon  $last_modified
 * @property User    $user
 * @property Texture $skin
 * @property Texture $cape
 * @property string  $model
 */
class Player extends Model
{
    use HasFactory;
    use SearchString;

    public const CREATED_AT = null;
    public const UPDATED_AT = 'last_modified';

    public $primaryKey = 'pid';
    protected $fillable = [
        'uid', 'name', 'tid_skin', 'tid_cape', 'last_modified',
    ];

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

    protected $searchStringColumns = [
        'pid', 'uid',
        'tid_skin' => '/^(?:tid_)?skin$/',
        'tid_cape' => '/^(?:tid_)?cape$/',
        'name' => ['searchable' => true],
        'last_modified' => ['date' => true],
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
