<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Filter
{
    protected $listeners = [];

    public function add(string $hook, Closure $callback, $priority = 20)
    {
        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = collect();
        }

        $this->listeners[$hook]->push([
            'callback' => $callback,
            'priority' => $priority,
        ]);
    }

    public function apply(string $hook, $init, $args = [])
    {
        $listeners = $this->getListeners($hook);
        if ($listeners->isNotEmpty()) {
            return $this->listeners[$hook]
                ->sortBy('priority')
                ->reduce(function ($carry, $item) use ($args) {
                    return call_user_func($item['callback'], $carry, ...$args);
                }, $init);
        } else {
            return $init;
        }
    }

    public function remove(string $hook)
    {
        unset($this->listeners[$hook]);
    }

    public function getListeners(string $hook): Collection
    {
        return Arr::get($this->listeners, $hook, collect());
    }
}
