<?php

namespace App\Services;

use DB;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class Option
{
    protected $items;

    public function __construct(Filesystem $filesystem)
    {
        $cachePath = storage_path('options.php');
        if ($filesystem->exists($cachePath)) {
            $this->items = collect($filesystem->getRequire($cachePath));

            return;
        }

        try {
            $this->items = DB::table('options')
                ->get()
                ->mapWithKeys(fn ($item) => [$item->option_name => $item->option_value]);
        } catch (QueryException $e) {
            $this->items = collect();
        }
    }

    public function get($key, $default = null, $raw = false)
    {
        if (!$this->items->has($key) && Arr::has(config('options'), $key)) {
            $this->set($key, config("options.$key"));
        }

        $value = $this->items->get($key, $default);
        if ($raw) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;

            default:
                return $value;
        }
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->items->put($key, $value);
            try {
                DB::table('options')->updateOrInsert(
                    ['option_name' => $key],
                    ['option_value' => $value]
                );
            } catch (QueryException $e) {
            }
        }
    }

    public function all(): array
    {
        return $this->items->all();
    }
}
