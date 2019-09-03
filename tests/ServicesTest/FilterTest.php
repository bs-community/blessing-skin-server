<?php

namespace Tests;

use App\Services\Filter;

class FilterTest extends TestCase
{
    public function testAdd()
    {
        $filter = new Filter();
        $filter->add('hook', function () {});
        $filter->add('hook', function () {}, 10);
        $this->assertCount(2, $filter->getListeners('hook'));
    }

    public function testApply()
    {
        $filter = new Filter();
        $this->assertEquals('value', $filter->apply('hook', 'value', ['add']));

        $filter->add('hook', function ($value, $addition) {
            $this->assertEquals('add', $addition);
            return $value.'_medium';
        });
        $filter->add('hook', function ($value) {
            return $value.'_low';
        }, 10);
        $filter->add('hook', function ($value) {
            return $value.'_high';
        }, 30);
        $this->assertEquals('value_high_medium_low', $filter->apply('hook', 'value', ['add']));
    }

    public function testRemove()
    {
        $filter = new Filter();
        $filter->remove('hook');
        $this->assertCount(0, $filter->getListeners('hook'));

        $filter->add('hook', function () {});
        $this->assertCount(1, $filter->getListeners('hook'));
        $filter->remove('hook');
        $this->assertCount(0, $filter->getListeners('hook'));
    }

    public function testGetListeners()
    {
        $filter = new Filter();
        $this->assertCount(0, $filter->getListeners('hook'));
    }
}
