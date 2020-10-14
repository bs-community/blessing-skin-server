<?php

namespace Tests\Fakes;

use Blessing\Filter as BaseFilter;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;

class Filter extends BaseFilter
{
    protected $applied = [];

    public function apply(string $hook, $init, $args = [])
    {
        if (!Arr::has($this->applied, $hook)) {
            $this->applied[$hook] = [];
        }
        $this->applied[$hook][] = [$init, ...$args];

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
                call_user_func_array(
                    $predicate,
                    Arr::last($this->applied[$hook])
                ),
                "Arguments of Filter '$hook' does not satisfies the predicate."
            );
        }
    }

    public function assertHaveBeenApplied(string $hook, $predicate = null)
    {
        Assert::assertArrayHasKey(
            $hook, $this->applied,
            "Expected Filter '$hook' was not applied."
        );

        $result = Arr::first(
            $this->applied[$hook],
            fn ($arguments) => call_user_func_array($predicate, $arguments),
        );
        Assert::assertNotNull(
            $result,
            "None of applies of Filter '$hook' satisfy the predicate."
        );
    }
}
