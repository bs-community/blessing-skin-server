<?php

namespace Tests;

use App\Services\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function testHas()
    {
        $repo = new UserRepository();
        $this->assertFalse($repo->has('not_found', 'invalid'));
    }

    public function testGet()
    {
        $repo = new UserRepository();
        $this->assertNull($repo->get('not_found', 'username'));
    }
}
