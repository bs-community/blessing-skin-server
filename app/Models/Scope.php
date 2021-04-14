<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property string $name
 * @property string $description
 */
class Scope extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'description',
    ];
}
