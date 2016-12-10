<?php

namespace App\Events;

class RenderingFooter extends Event
{
    public $contents;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array &$contents)
    {
        // pass array by reference
        $this->contents = &$contents;
    }

    public function addContent($content)
    {
        if ($content) {
            if (!is_string($content)) {
                throw new \Exception("Can not add non-string content", 1);
            }

            $this->contents[] = $content;
        }
    }
}
