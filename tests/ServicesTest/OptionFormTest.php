<?php

namespace Tests;

use App\Services\OptionForm;
use Symfony\Component\DomCrawler\Crawler;

class OptionFormTest extends TestCase
{
    public function testRenderTitle()
    {
        $form = new OptionForm('test');
        $crawler = new Crawler($form->render());
        $this->assertEquals(trans('options.test.title'), trim($crawler->filter('.box-title')->text()));

        $form = new OptionForm('test', 'test');
        $crawler = new Crawler($form->render());
        $this->assertEquals('test', trim($crawler->filter('.box-title')->text()));
    }

    public function testDisallowInvalidType()
    {
        $this->expectException(\BadMethodCallException::class);
        $form = new OptionForm('test', 'test');
        $form->nope();
    }

    public function testRenderBoxType()
    {
        $form = new OptionForm('test');
        $crawler = new Crawler($form->render());
        $this->assertCount(1, $crawler->filter('.box-primary'));

        $form = new OptionForm('test');
        $returned = $form->type('warning');
        $this->assertSame($form, $returned);
        $crawler = new Crawler($form->render());
        $this->assertCount(1, $crawler->filter('.box-warning'));
    }

    public function testRenderHint()
    {
        $form = new OptionForm('test');
        $returned = $form->hint();
        $this->assertSame($form, $returned);
        $crawler = new Crawler($form->render());
        $this->assertEquals(trans('options.test.hint'), $crawler->filter('.fa-question-circle')->attr('title'));

        $form = new OptionForm('test');
        $form->hint('this is hint');
        $crawler = new Crawler($form->render());
        $this->assertEquals('this is hint', $crawler->filter('.fa-question-circle')->attr('title'));
    }

    public function testPassValues()
    {
        $form = new OptionForm('test');
        $form->text('k');
        $returned = $form->with('k', 'v');
        $this->assertSame($form, $returned);
        $form->handle();
        $crawler = new Crawler($form->render());
        $this->assertEquals('v', $crawler->filter('[name=k]')->attr('value'));

        $form = new OptionForm('test');
        $form->text('k1');
        $form->text('k2');
        $form->with(['k1' => 'v1', 'k2' => 'v2']);
        $form->handle();
        $crawler = new Crawler($form->render());
        $this->assertEquals('v1', $crawler->filter('[name=k1]')->attr('value'));
        $this->assertEquals('v2', $crawler->filter('[name=k2]')->attr('value'));
    }

    public function testAddButton()
    {
        $form = new OptionForm('test');
        $returned = $form->addButton(['href' => 'http://example.com', 'class' => ['a', 'b'], 'text' => 'link']);
        $form->addButton(['style' => 'primary', 'text' => 'press me', 'name' => 'btn']);
        $form->addButton(['style' => 'warning', 'type' => 'submit']);
        $this->assertSame($form, $returned);

        $crawler = new Crawler($form->render());
        $a = $crawler->filter('a');
        $this->assertEquals('http://example.com', $a->attr('href'));
        $this->assertEquals('el-button el-button--default a b', $a->attr('class'));
        $this->assertEquals('link', $a->text());

        $button = $crawler->filter('button.el-button--primary');
        $this->assertEquals('press me', $button->text());
        $this->assertEquals('btn', $button->attr('name'));
        $this->assertEquals('button', $button->attr('type'));

        $button = $crawler->filter('button.el-button--warning');
        $this->assertEquals('submit', $button->attr('type'));
    }

    public function testAddMessage()
    {
        $form = new OptionForm('test', 'test');
        $returned = $form->addMessage();
        $form->addMessage('greeting', 'warning');
        $this->assertSame($form, $returned);

        $crawler = new Crawler($form->render());
        $this->assertEquals(trans('options.test.message'), $crawler->filter('.callout-info')->text());
        $this->assertEquals('greeting', $crawler->filter('.callout-warning')->text());
    }

    public function testHookBefore()
    {
        $called = false;
        $form = new OptionForm('test', 'test');
        $returned = $form->before(function () use (&$called) {
            $called = true;
        });
        $this->assertSame($form, $returned);

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
        $returned = $form->after(function () use (&$called) {
            $called = true;
        });
        $this->assertSame($form, $returned);

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle();
        $this->assertTrue($called);
    }

    public function testHookAlways()
    {
        $called = false;
        $form = new OptionForm('test', 'test');
        $returned = $form->always(function () use (&$called) {
            $called = true;
        });
        $this->assertSame($form, $returned);

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test']);

        $form->handle();
        $this->assertFalse($called);

        $form->render();
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

    public function testRenderWithoutTable()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $returned = $form->renderWithoutTable();
        $this->assertSame($form, $returned);
        $crawler = new Crawler($form->render());
        $this->assertCount(0, $crawler->filter('table'));
    }

    public function testRenderInputTagsOnly()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $returned = $form->renderInputTagsOnly();
        $this->assertSame($form, $returned);
        $crawler = new Crawler($form->render());
        $this->assertCount(0, $crawler->filter('td.key'));
        $this->assertCount(1, $crawler->filter('td.value'));
    }

    public function testRenderWithoutSubmitButton()
    {
        $form = new OptionForm('test', 'test');
        $form->text('text');
        $returned = $form->renderWithoutSubmitButton();
        $this->assertSame($form, $returned);
        $crawler = new Crawler($form->render());
        $this->assertCount(0, $crawler->filter('button'));
    }

    public function testDefaultRender()
    {
        $form = new OptionForm('test');
        $form->handle();
        $crawler = new Crawler($form->render());
        $button = $crawler->filter('button');
        $this->assertStringContainsString('el-button--primary', $button->attr('class'));
        $this->assertEquals(trans('general.submit'), $button->text());
        $this->assertEquals('submit', $button->attr('type'));
        $this->assertEquals('submit_test', $button->attr('name'));
    }

    public function testHandle()
    {
        $form = new OptionForm('test');
        $form->text('t')->format(function ($data) {
            return "formatted $data";
        });

        $request = request();
        $request->setMethod('POST');
        $request->merge(['option' => 'test', 't' => 'value']);

        $form->handle();
        $crawler = new Crawler($form->render());
        $this->assertEquals(trans('options.option-saved'), $crawler->filter('.callout-success')->text());
        $this->assertEquals('formatted value', option('t'));
    }

    public function testToString()
    {
        $form = new OptionForm('test');
        $crawler = new Crawler(sprintf('%s', $form));
        $this->assertCount(1, $crawler->filter('div.box'));
    }
}
