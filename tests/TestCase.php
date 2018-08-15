<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use MockGuzzleClient;
    use InteractsWithCache;

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
        return $this->withSession(['uid' => $role->uid, 'token' => $role->getToken()]);
    }

    /**
     * Disable Laravel's exception handling.
     *
     * @see https://laracasts.com/discuss/channels/testing/testing-that-exception-was-thrown
     * @return $this
     */
    protected function disableExceptionHandling()
    {
        $this->app->instance(App\Exceptions\Handler::class, new FakeExceptionHandler);

        return $this;
    }

    /**
     * Set an expected exception.
     *
     * @param string $class
     * @return $this
     */
    protected function expectException($class)
    {
        $this->disableExceptionHandling();
        $this->setExpectedException($class);

        return $this;
    }

    protected function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });
        parent::tearDown();
    }
}
