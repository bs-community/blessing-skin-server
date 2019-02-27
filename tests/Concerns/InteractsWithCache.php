<?php

namespace Tests\Concerns;

/**
 * Add ability to interact with cache in tests.
 *
 * @see \Illuminate\Foundation\Testing\Concerns\InteractsWithSession
 */
trait InteractsWithCache
{
    /**
     * Set the cache to the given array.
     *
     * @param  array  $data
     * @param  int    $seconds
     * @return $this
     */
    public function withCache(array $data, $seconds = 3600)
    {
        $this->cache($data, $seconds);

        return $this;
    }

    /**
     * Set the cache to the given array.
     *
     * @param  array  $data
     * @param  int    $seconds
     * @return void
     */
    public function cache(array $data, $seconds = 3600)
    {
        foreach ($data as $key => $value) {
            $this->app['cache']->put($key, $value, $seconds);
        }
    }

    /**
     * Flush all of the current cache data.
     *
     * @return void
     */
    public function flushCache()
    {
        $this->app['cache']->flush();
    }

    /**
     * Assert that the cache has a given value.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function seeInCache($key, $value = null)
    {
        $this->assertCacheHas($key, $value);

        return $this;
    }

    /**
     * Assert that the cache has a given value.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function assertCacheHas($key, $value = null)
    {
        if (is_array($key)) {
            $this->assertCacheHasAll($key);
            return $this;
        }

        if (is_null($value)) {
            $this->assertTrue($this->app['cache.store']->has($key), "Cache missing key: $key");
        } else {
            $this->assertEquals($value, $this->app['cache.store']->get($key));
        }
        return $this;
    }

    /**
     * Assert that the cache has a given list of values.
     *
     * @param  array  $bindings
     * @return void
     */
    public function assertCacheHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertCacheHas($value);
            } else {
                $this->assertCacheHas($key, $value);
            }
        }
    }

    /**
     * Assert that the cache does not have a given key.
     *
     * @param  string|array  $key
     * @return void
     */
    public function assertCacheMissing($key)
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                $this->assertCacheMissing($k);
            }
        } else {
            $this->assertFalse($this->app['cache.store']->has($key), "Cache has unexpected key: $key");
        }
    }
}
