<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class UsersManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function testList()
    {
        $user = User::factory()->create();

        $this->getJson(route('admin.users.list'))
            ->assertJson(['data' => [[/* admin is here */], $user->toArray()]]);
    }

    public function testAccessControl()
    {
        // an administrator operating on other administrator should be forbidden
        $otherAdmin = User::factory()->admin()->create();

        $this->putJson(route('admin.users.email', ['user' => $otherAdmin->uid]))
            ->assertJson([
                'code' => 1,
                'message' => trans('admin.users.operations.no-permission'),
            ])
            ->assertForbidden();
    }

    public function testEmail()
    {
        $user = User::factory()->create();

        // without `email` field
        $this->putJson(route('admin.users.email', ['user' => $user]))
            ->assertJsonValidationErrors(['email']);

        // with an invalid email address
        $this->putJson(
            route('admin.users.email', ['user' => $user]),
            ['email' => 'invalid']
        )->assertJsonValidationErrors(['email']);

        // use an existed email address
        $other = User::factory()->create();
        $this->putJson(
            route('admin.users.email', ['user' => $user]),
            ['email' => $other->email]
        )->assertJsonValidationErrors(['email']);

        // update successfully
        Event::fake();
        $this->putJson(
            route('admin.users.email', ['user' => $user]),
            ['email' => 'a@b.c']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.email.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'email' => 'a@b.c',
        ]);
        Event::assertDispatched('user.email.updating', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));
            $this->assertEquals('a@b.c', $payload[1]);

            return true;
        });
        Event::assertDispatched('user.email.updated', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));
            $this->assertEquals($user->email, $payload[1]->email);

            return true;
        });
    }

    public function testVerification()
    {
        Event::fake();
        $user = User::factory()->create();

        $this->putJson(
            route('admin.users.verification', ['user' => $user])
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.verification.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'verified' => false,
        ]);
        Event::assertDispatched('user.verification.updating', function ($eventName, $payload) use ($user) {
            $this->assertInstanceOf(User::class, $payload[0]);
            $this->assertEquals($user->uid, $payload[0]->uid);

            return true;
        });
        Event::assertDispatched('user.verification.updated', function ($eventName, $payload) {
            $this->assertFalse($payload[0]->verified);

            return true;
        });
    }

    public function testNickname()
    {
        $user = User::factory()->create();

        // without `nickname` field
        $this->putJson(
            route('admin.users.nickname', ['user' => $user])
        )->assertJsonValidationErrors(['nickname']);

        // update successfully
        Event::fake();
        $this->putJson(
            route('admin.users.nickname', ['user' => $user]),
            ['nickname' => 'nickname']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.nickname.success', ['new' => 'nickname']),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'nickname' => 'nickname',
        ]);
        Event::assertDispatched('user.nickname.updating', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));
            $this->assertEquals('nickname', $payload[1]);

            return true;
        });
        Event::assertDispatched('user.nickname.updated', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));
            $this->assertEquals($user->nickname, $payload[1]->nickname);

            return true;
        });
    }

    public function testPassword()
    {
        $user = User::factory()->create();

        // without `password` field
        $this->putJson(
            route('admin.users.password', ['user' => $user])
        )->assertJsonValidationErrors(['password']);

        // too short password
        $this->putJson(
            route('admin.users.password', ['user' => $user]),
            ['password' => '1']
        )->assertJsonValidationErrors(['password']);

        // too long password
        $this->putJson(
            route('admin.users.password', ['user' => $user]),
            ['password' => Str::random(17)]
        )->assertJsonValidationErrors(['password']);

        // update successfully
        Event::fake();
        $this->putJson(
            route('admin.users.password', ['user' => $user]),
            ['password' => '12345678']
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.password.success'),
        ]);
        $this->assertTrue($user->fresh()->verifyPassword('12345678'));
        Event::assertDispatched('user.password.updating', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));
            $this->assertEquals('12345678', $payload[1]);

            return true;
        });
        Event::assertDispatched('user.password.updated', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));

            return true;
        });
    }

    public function testScore()
    {
        $user = User::factory()->create();

        // without `score` field
        $this->putJson(
            route('admin.users.score', ['user' => $user])
        )->assertJsonValidationErrors(['score']);

        // with an non-integer value
        $this->putJson(
            route('admin.users.score', ['user' => $user]),
            ['score' => 'string']
        )->assertJsonValidationErrors(['score']);

        // update successfully
        Event::fake();
        $this->putJson(
            route('admin.users.score', ['user' => $user]),
            ['score' => 123]
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.score.success'),
        ]);
        $this->assertDatabaseHas('users', [
            'uid' => $user->uid,
            'score' => 123,
        ]);
        Event::assertDispatched('user.score.updating', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));
            $this->assertEquals(123, $payload[1]);

            return true;
        });
        Event::assertDispatched('user.score.updated', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));
            $this->assertEquals($user->score, $payload[1]->score);

            return true;
        });
    }

    public function testPermission()
    {
        $user = User::factory()->create();

        // without `permission` field
        $this->putJson(route('admin.users.permission', ['user' => $user]))
            ->assertJsonValidationErrors(['permission']);

        // invalid permission value
        $this->putJson(
            route('admin.users.permission', ['user' => $user]),
            ['permission' => -2]
        )->assertJsonValidationErrors(['permission']);

        // non-super administrator can't set normal user as administrator
        $this->putJson(
            route('admin.users.permission', ['user' => $user]),
            ['permission' => User::ADMIN]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.no-permission'),
        ])->assertForbidden();

        // administrator can't modify his/her permission
        $this->putJson(
            route('admin.users.permission', ['user' => auth()->user()]),
            ['permission' => User::NORMAL]
        )->assertJson([
            'code' => 1,
            'message' => trans('admin.users.operations.no-permission'),
        ])->assertForbidden();

        // update successfully
        Event::fake();
        $this->putJson(
            route('admin.users.permission', ['user' => $user]),
            ['permission' => User::BANNED]
        )->assertJson([
            'code' => 0,
            'message' => trans('admin.users.operations.permission'),
        ]);
        $this->assertEquals(User::BANNED, $user->fresh()->permission);
        Event::assertDispatched('user.permission.updating', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));
            $this->assertEquals(User::BANNED, $payload[1]);

            return true;
        });
        Event::assertDispatched('user.permission.updated', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));
            $this->assertEquals($user->permission, $payload[1]->permission);

            return true;
        });
        Event::assertDispatched('user.banned', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->fresh()->is($payload[0]));

            return true;
        });
    }

    public function testDelete()
    {
        Event::fake();
        $user = User::factory()->create();

        $this->deleteJson(route('admin.users.delete', ['user' => $user]))
            ->assertJson([
                'code' => 0,
                'message' => trans('admin.users.operations.delete.success'),
            ]);
        $this->assertNull(User::find($user->uid));
        Event::assertDispatched('user.deleting', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));

            return true;
        });
        Event::assertDispatched('user.deleted', function ($eventName, $payload) use ($user) {
            $this->assertTrue($user->is($payload[0]));

            return true;
        });
    }
}
