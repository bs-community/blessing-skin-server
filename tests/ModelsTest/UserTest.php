<?php

namespace Tests;

use App\Models\User;
use App\Services\Utils;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testSign()
    {
        $user = factory(User::class)->make([
            'last_sign_at' => Utils::getTimeFormatted(time())
        ]);
        $user->sign();
        $this->assertFalse($user->sign());
    }

    public function testGetNickName()
    {
        $user = new User();
        $this->assertEquals(
            trans('general.unexistent-user'),
            $user->getNickName()
        );
    }
}
