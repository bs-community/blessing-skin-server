<?php

namespace App\Events;

use Illuminate\Http\UploadedFile;

class HashingFile extends Event
{
    public $file;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UploadedFile $file)
    {
        $this->file = $file;
    }
}
