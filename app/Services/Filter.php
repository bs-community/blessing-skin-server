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
        if (! isset($this->listeners[$hook])) {
            $this->listeners[$hook] = collect();
        }

        $this->listeners[$hook]->push([
            'callback' => $callback,
            'priority' => $priority,
        ]);
    }

    public function apply(string $hook, array $payload)
    {
        $listeners = $this->getListeners($hook);
        if ($listeners->isNotEmpty()) {
            $value = $payload[0];
            unset($payload[0]);
            $args = array_values($payload);

            return $this->listeners[$hook]
                ->sortByDesc('priority')
                ->reduce(function ($carry, $item) use ($args) {
                    return call_user_func($item['callback'], $carry, ...$args);
                }, $value);
        } else {
            return $payload[0];
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
