<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        Artisan::call('migrate:refresh');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        return $app;
    }

    /**
     * @param \App\Models\User|string $role
     * @return $this
     */
    public function actAs($role)
    {
        if (is_string($role)) {
            if ($role == 'normal') {
                $role = factory(\App\Models\User::class)->create();
            } else {
                $role = factory(\App\Models\User::class, $role)->create();
            }
        }
        return $this->actingAs($role);
    }

    /**
     * Set the cache to the given array.
     *
     * @param  array  $data
     * @param  int    $minutes
     * @return $this
     */
    public function withCache(array $data, $minutes = 60)
    {
        foreach ($data as $key => $value) {
            $this->app['cache.store']->put($key, $value, $minutes);
        }
        return $this;
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

    /**
     * Flush all of the current cache data.
     *
     * @return void
     */
    public function flushCache()
    {
        $this->app['cache.store']->flush();
    }

    protected function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
    }
}
