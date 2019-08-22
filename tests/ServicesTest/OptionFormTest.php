<?php

namespace Tests;

use Illuminate\Support\Str;
use App\Services\OptionForm;
use Symfony\Component\DomCrawler\Crawler;

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
        $crawler = new Crawler($form->render());
        $this->assertCount(0, $crawler->filter('td.key'));
        $this->assertCount(1, $crawler->filter('td.value'));
    }

    public function testRenderWithoutSubmitButton()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $form->renderWithoutSubmitButton();
        $crawler = new Crawler($form->render());
        $this->assertCount(0, $crawler->filter('button'));
    }

    public function testDisallowInvalidType()
    {
        $this->expectException(\BadMethodCallException::class);
        $form = new OptionForm('test', 'test');
        $form->nope();
    }

    public function testAddMessage()
    {
        $form = new OptionForm('test', 'test');
        $form->addMessage();
        $form->addMessage('greeting', 'warning');

        $crawler = new Crawler($form->render());
        $this->assertEquals(trans('options.test.message'), $crawler->filter('.callout-info')->text());
        $this->assertEquals('greeting', $crawler->filter('.callout-warning')->text());
    }
}
