<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Texture extends Model
{
    public $primaryKey = 'tid';
    public $timestamps = false;

    public function setPrivacy($public)
    {
        $this->public = $public ? "1" : "0";
        return $this->save();
    }

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }
}
