<?php

namespace Tests\HttpTest\ViewTest\ComposersTest;

use Tests\TestCase;

class LanguagesMenuComposerTest extends TestCase
{
    public function testCompose()
    {
        $this->get('/')->assertSee('?lang=en')->assertDontSee('en_US');
        $this->get('/?key=value')->assertSee('?key=value&lang=en');
    }
}
