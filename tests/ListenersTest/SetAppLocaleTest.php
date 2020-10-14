<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SetAppLocaleTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdateUserLocale()
    {
        $user = User::factory()->create();

        // This is a hacky way.
        // We must call `get` first before set authentication,
        // since this let Laravel update the `Request` instance,
        // otherwise the event handler will be called first and
        // it won't be able to retrieve request information.
        $this->get('/?lang=en');
        $this->actingAs($user);

        $this->assertEquals('en', $user->fresh()->locale);
    }

    public function testSetAppLocale()
    {
        $user = User::factory()->create(['locale' => 'zh_CN']);

        event(new \Illuminate\Auth\Events\Authenticated('web', $user));
        $this->assertEquals('zh_CN', app()->getLocale());

        app()->setLocale('en');
    }
}
