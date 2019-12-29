<?php

namespace Tests\Fakes;

use Blessing\Filter as BaseFilter;
use PHPUnit\Framework\Assert;

class Filter extends BaseFilter
{
    protected $applied = [];

    public function apply(string $hook, $init, $args = [])
    {
        $this->applied[$hook] = array_merge([$init], $args);

        return parent::apply($hook, $init, $args);
    }

    public static function fake(): Filter
    {
        $fake = resolve(Filter::class);

        app()->instance(BaseFilter::class, $fake);

        return $fake;
    }

    public function assertApplied(string $hook, $predicate = null)
    {
        Assert::assertArrayHasKey(
            $hook, $this->applied,
            "Expected Filter '$hook' was not applied."
        );

        if (!empty($predicate)) {
            Assert::assertTrue(
                call_user_func_array($predicate, $this->applied[$hook]),
                "Arguments of Filter '$hook' does not satisfies the predicate."
            );
        }
    }
}
