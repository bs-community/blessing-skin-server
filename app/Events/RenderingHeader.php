<?php

namespace App\Events;

class RenderingHeader extends Event
{
    public $contents;

    public function __construct(array &$contents)
    {
        $this->contents = &$contents;
    }

    public function addContent(string $content)
    {
        if ($content) {
            if (!is_string($content)) {
                throw new \Exception('Can not add non-string content', 1);
            }

            $this->contents[] = $content;
        }
    }
}
