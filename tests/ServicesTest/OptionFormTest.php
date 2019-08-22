<?php

namespace Tests;

use Illuminate\Support\Str;
use App\Services\OptionForm;

class OptionFormTest extends TestCase
{
    public function testHookBefore()
    {
        $called = false;
        $form = new OptionForm('test', 'test');
        $form->before(function () use (&$called) {
            $called = true;
        });

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle();
        $this->assertTrue($called);
    }

    public function testHookAfter()
    {
        $called = false;
        $form = new OptionForm('test', 'test');
        $form->after(function () use (&$called) {
            $called = true;
        });

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle();
        $this->assertTrue($called);
    }

    public function testDirectHook()
    {
        $called = false;
        $form = new OptionForm('test', 'test');

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle(function () use (&$called) {
            $called = true;
        });
        $this->assertTrue($called);
    }

    public function testHookAlways()
    {
        $called = false;
        $form = new OptionForm('test', 'test');
        $form->always(function () use (&$called) {
            $called = true;
        });

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle();
        $this->assertFalse($called);

        $form->render();
        $this->assertTrue($called);
    }

    public function testRenderInputTagsOnly()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $form->renderInputTagsOnly();
        $html = $form->render();
        $this->assertFalse(Str::contains($html, '<td class="key">'));
        $this->assertTrue(Str::contains($html, '<td class="value">'));
    }

    public function testRenderWithoutSubmitButton()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $form->renderWithoutSubmitButton();
        $html = $form->render();
        $this->assertFalse(Str::contains($html, '<button'));
    }
}
