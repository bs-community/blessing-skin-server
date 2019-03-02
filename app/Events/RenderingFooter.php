<?php

namespace App\Events;

class RenderingFooter extends Event
{
    public $contents;

    /**
     * Create a new event instance.
     *
     * @param  array $contents
     * @return void
     */
    public function __construct(array &$contents)
    {
        // Pass array by reference
        $this->contents = &$contents;
    }

    /**
     * Add content to page footer.
     *
     * @param  string $content
     * @return void
     */
    public function addContent($content)
    {
        if ($content) {
            if (! is_string($content)) {
                throw new \Exception('Can not add non-string content', 1);
            }

            $this->contents[] = $content;
        }
    }
}
