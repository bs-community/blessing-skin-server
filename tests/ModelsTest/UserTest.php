<?php

namespace Tests;

use Schema;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use Tests\Concerns\User as ExtendedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testConvertQuery()
    {
        if (config('database.default') == 'mysql') {
            $this->assertStringContainsString(
                'where `bs_email`',
                ExtendedUser::where('email', '')->toSql()
            );
            $this->assertStringContainsString(
                'select `bs_email` from',
                ExtendedUser::select(['email'])->toSql()
            );
            $this->assertStringContainsString(
                'order by `bs_score`',
                ExtendedUser::orderBy('score')->toSql()
            );
            $this->assertStringContainsString(
                'group by `bs_permission`',
                ExtendedUser::groupBy('permission')->toSql()
            );
            $this->assertStringContainsString(
                'having `bs_permission`',
                ExtendedUser::having('permission')->toSql()
            );
        } else {
            $this->assertStringContainsString(
                'where "bs_email"',
                ExtendedUser::where('email', '')->toSql()
            );
            $this->assertStringContainsString(
                'select "bs_email" from',
                ExtendedUser::select(['email'])->toSql()
            );
            $this->assertStringContainsString(
                'order by "bs_score"',
                ExtendedUser::orderBy('score')->toSql()
            );
            $this->assertStringContainsString(
                'group by "bs_permission"',
                ExtendedUser::groupBy('permission')->toSql()
            );
            $this->assertStringContainsString(
                'having "bs_permission"',
                ExtendedUser::having('permission')->toSql()
            );
        }
    }

    public function testGetUidAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uid', 'bs_uid');
        });
        $users = get_class(new class extends User {
            protected $primaryKey = 'bs_uid';

            protected $table = 'users';

            protected static $mappings = ['uid' => 'bs_uid'];
        });
        $user = $users::first();
        $this->assertEquals($user->getAttribute('bs_uid'), $user->uid);
    }

    public function testEmailAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('email', 'bs_email');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_email'), $user->email);

        $user->email = 'a@b.c';
        $user->save();
        $this->assertDatabaseHas('users', ['bs_email' => 'a@b.c']);
    }

    public function testNicknameAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nickname', 'bs_nickname');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_nickname'), $user->nickname);

        $user->nickname = 'name';
        $user->save();
        $this->assertDatabaseHas('users', ['bs_nickname' => 'name']);
    }

    public function testScoreAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('score', 'bs_score');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_score'), $user->score);

        $user->score = 50;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_score' => 50]);
    }

    public function testAvatarAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('avatar', 'bs_avatar');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_avatar'), $user->avatar);

        $user->avatar = 5;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_avatar' => 5]);
    }

    public function testPasswordAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('password', 'bs_password');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_password'), $user->password);

        $user->password = '123';
        $user->save();
        $this->assertDatabaseHas('users', ['bs_password' => '123']);
    }

    public function testIpAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('ip', 'bs_ip');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_ip'), $user->ip);

        $user->ip = '255.255.255.255';
        $user->save();
        $this->assertDatabaseHas('users', ['bs_ip' => '255.255.255.255']);
    }

    public function testPermissionAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('permission', 'bs_permission');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_permission'), $user->permission);

        $user->permission = 1;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_permission' => 1]);
    }

    public function testLastSignAtAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('last_sign_at', 'bs_last_sign_at');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_last_sign_at'), $user->last_sign_at);

        $time = Carbon::now()->toDateTimeString();
        $user->last_sign_at = $time;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_last_sign_at' => $time]);
    }

    public function testRegisterAtAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('register_at', 'bs_register_at');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_register_at'), $user->register_at);

        $time = Carbon::now()->toDateTimeString();
        $user->register_at = $time;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_register_at' => $time]);
    }

    public function testVerifiedAttribute()
    {
        factory(User::class)->create();
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('verified', 'bs_verified');
        });
        $user = ExtendedUser::first();
        $this->assertEquals($user->getAttribute('bs_verified'), $user->verified);

        $user->verified = 0;
        $user->save();
        $this->assertDatabaseHas('users', ['bs_verified' => 0]);

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bs_verified', 'verified');
        });
    }

    public function testGetPlayerNameAttribute()
    {
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $this->assertEquals($player->name, $user->player_name);
    }

    public function testSetPlayerNameAttribute()
    {
        $user = factory(User::class)->create();
        $player = factory(Player::class)->create(['uid' => $user->uid]);
        $user->player_name = 'a';
        $player->refresh();
        $this->assertEquals('a', $player->name);
    }
}
