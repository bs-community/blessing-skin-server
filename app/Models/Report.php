<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lorisleiva\LaravelSearchString\Concerns\SearchString;

/**
 * @property int     $id
 * @property int     $tid
 * @property int     $uploader
 * @property int     $reporter
 * @property string  $reason
 * @property int     $status
 * @property Carbon  $report_at
 * @property Texture $texture
 * @property User    $informer  The reporter.
 */
class Report extends Model
{
    use SearchString;

    public const CREATED_AT = 'report_at';
    public const UPDATED_AT = null;

    public const PENDING = 0;
    public const RESOLVED = 1;
    public const REJECTED = 2;

    protected $fillable = [
        'uploader', 'reporter', 'reason', 'status',
    ];

    protected $casts = [
        'tid' => 'integer',
        'uploader' => 'integer',
        'reporter' => 'integer',
        'status' => 'integer',
    ];

    protected $searchStringColumns = [
        'id', 'tid', 'uploader', 'reporter',
        'reason', 'status',
        'report_at' => ['date' => true],
    ];

    public function texture()
    {
        return $this->belongsTo(Texture::class, 'tid', 'tid');
    }

    public function informer()
    {
        return $this->belongsTo(User::class, 'reporter', 'uid');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
