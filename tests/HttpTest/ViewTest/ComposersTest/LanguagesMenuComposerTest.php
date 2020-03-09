<?php

namespace Tests;

class LanguagesMenuComposerTest extends TestCase
{
    public function testCompose()
    {
        $this->get('/')->assertSee('?lang=en')->assertDontSee('en_US');
        $this->get('/?key=value')->assertSee('?key=value&lang=en');
    }
}
