<?php

namespace App\Events;

class RenderingFooter extends Event
{
    public $contents;

    public function __construct(array &$contents)
    {
        $this->contents = &$contents;
    }

    public function addContent(string $content)
    {
        $this->contents[] = $content;
    }
}
