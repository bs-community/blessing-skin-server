<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Rejection;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class SkinlibControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testAccessControl()
    {
        Storage::fake('textures');

        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create();

        // other user should not be able to delete
        $this->actingAs($other)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 1])
            ->assertForbidden();

        // administrators can delete it
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
    }

    public function testLibrary()
    {
        $steve = factory(Texture::class)->create([
            'name' => 'ab',
            'upload_at' => Carbon::now()->subDays(2),
            'likes' => 80,
        ]);
        $alex = factory(Texture::class)->states('alex')->create([
            'name' => 'cd',
            'upload_at' => Carbon::now()->subDays(1),
            'likes' => 60,
        ]);
        $private = factory(Texture::class)->states('private')->create([
            'upload_at' => Carbon::now(),
        ]);

        // default
        $this->getJson('/skinlib/list')
            ->assertJson([
                'data' => [
                    ['tid' => $alex->tid, 'nickname' => $alex->owner->nickname],
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                ],
            ]);

        // with filter
        $this->getJson('/skinlib/list?filter=steve')
            ->assertJson([
                'data' => [
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                ],
            ]);

        // with keyword
        $this->getJson('/skinlib/list?keyword=a')
            ->assertJson([
                'data' => [
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                ],
            ]);
        $user = factory(User::class)->create();
        $list = $this->actingAs($user)
            ->getJson('/skinlib/list?keyword=a')
            ->decodeResponseJson('data');
        $this->assertCount(1, $list);

        // with uploader
        $this->getJson('/skinlib/list?uploader='.$steve->uploader)
            ->assertJson([
                'data' => [
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                ],
            ]);

        // sort by likes
        $this->getJson('/skinlib/list?sort=likes')
            ->assertJson([
                'data' => [
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                    ['tid' => $alex->tid, 'nickname' => $alex->owner->nickname],
                ],
            ]);

        // private textures are not available for other user
        $this->actingAs(factory(User::class)->create())
            ->getJson('/skinlib/list')
            ->assertJson([
                'data' => [
                    ['tid' => $alex->tid, 'nickname' => $alex->owner->nickname],
                    ['tid' => $steve->tid, 'nickname' => $steve->owner->nickname],
                ],
            ]);

        // private textures are available for uploader
        $this->actingAs($private->owner)
            ->getJson('/skinlib/list')
            ->assertJson([
                'data' => [
                    ['tid' => $private->tid],
                    ['tid' => $alex->tid],
                    ['tid' => $steve->tid],
                ],
            ]);

        // private textures are available for administrators
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->getJson('/skinlib/list')
            ->assertJson([
                'data' => [
                    ['tid' => $private->tid],
                    ['tid' => $alex->tid],
                    ['tid' => $steve->tid],
                ],
            ]);
    }

    public function testShow()
    {
        Storage::fake('textures');
        $filter = Fakes\Filter::fake();

        // Cannot find texture
        $this->get('/skinlib/show/1')
            ->assertSee(trans('skinlib.show.deleted'));

        // Invalid texture
        option(['auto_del_invalid_texture' => false]);
        $texture = factory(Texture::class)->create();
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertSee(trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        $this->assertNotNull(Texture::find($texture->tid));

        option(['auto_del_invalid_texture' => true]);
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertSee(trans('skinlib.show.deleted'));
        $this->assertNull(Texture::find($texture->tid));

        // Show a texture
        $texture = factory(Texture::class)->create();
        Storage::disk('textures')->put($texture->hash, '');
        $this->get('/skinlib/show/'.$texture->tid)->assertViewHas('texture');
        $filter->assertApplied('grid:skinlib.show');

        // Guest should not see private texture
        $uploader = factory(User::class)->create();
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        Storage::disk('textures')->put($texture->hash, '');
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertForbidden()
            ->assertSee(trans('skinlib.show.private'));

        option(['status_code_for_private' => 404]);
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertNotFound()
            ->assertSee(trans('skinlib.show.private'));

        // Other user should not see private texture
        $this->actingAs(factory(User::class)->create())
            ->get('/skinlib/show/'.$texture->tid)
            ->assertSee(trans('skinlib.show.private'));

        // Administrators should be able to see private textures
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->get('/skinlib/show/'.$texture->tid)
            ->assertViewHas('texture');

        // Uploader should be able to see private textures
        $this->actingAs($uploader)
            ->get('/skinlib/show/'.$texture->tid)
            ->assertViewHas('texture');

        // Badges.
        $uploader->permission = User::ADMIN;
        $uploader->save();
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertSee('primary')
            ->assertSee('STAFF');
        $uid = $uploader->uid;
        $filter->add('user_badges', function ($badges, $uploader) use ($uid) {
            $this->assertEquals($uid, $uploader->uid);
            $badges[] = ['text' => 'badge-test', 'color' => 'maroon'];

            return $badges;
        });
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertSee('badge-test')
            ->assertSee('maroon');
    }

    public function testInfo()
    {
        $texture = factory(Texture::class)->create();
        $this->get(route('texture.info', ['texture' => $texture]))
            ->assertJson($texture->toArray());
    }

    public function testUpload()
    {
        $filter = Fakes\Filter::fake();

        $this->actingAs(factory(User::class)->create())->get('/skinlib/upload');
        $filter->assertApplied('grid:skinlib.upload');

        option(['texture_name_regexp' => 'abc']);
        $this->get('/skinlib/upload')->assertViewHas('extra');
    }

    public function testHandleUpload()
    {
        Event::fake();
        /** @var FilesystemAdapter */
        $disk = Storage::fake('textures');
        $filter = Fakes\Filter::fake();
        $user = factory(User::class)->create();

        // without file
        $this->actingAs($user)
            ->postJson(route('texture.upload'), [
                'name' => 'name',
                'type' => 'steve',
                'public' => true,
            ])->assertJsonValidationErrors('file');

        // some error occurred when uploading file
        $file = UploadedFile::fake()->image('test.png');
        $upload = new UploadedFile(
            $file->path(),
            'test.png',
            'image/png',
            UPLOAD_ERR_NO_TMP_DIR,
            true
        );
        $this->postJson(route('texture.upload'), [
            'name' => 'name',
            'file' => $upload,
            'type' => 'steve',
            'public' => true,
        ])->assertJsonValidationErrors('file');

        // without `name` field
        $this->postJson(route('texture.upload'))
            ->assertJsonValidationErrors('name');

        // specified regular expression for texture name
        option(['texture_name_regexp' => '/\\d+/']);
        $this->postJson(route('texture.upload'), ['name' => 'abc'])
            ->assertJsonValidationErrors('name');
        option(['texture_name_regexp' => null]);

        // not a PNG file
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'file' => UploadedFile::fake()->create('fake', 5),
        ])->assertJsonValidationErrors('file');

        // too large file
        option(['max_upload_file_size' => 2]);
        $upload = UploadedFile::fake()->create('large.png', 5);
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'file' => $upload,
        ])->assertJsonValidationErrors('file');
        option(['max_upload_file_size' => 1024]);

        // no texture type is specified
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'file' => $file,
        ])->assertJsonValidationErrors('type');

        // invalid texture type
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'file' => $file,
            'type' => 'nope',
        ])->assertJsonValidationErrors('type');

        // without `public` field
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'file' => $file,
            'type' => 'steve',
        ])->assertJsonValidationErrors('public');

        // invalid skin size
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'public' => true,
            'type' => 'steve',
            'file' => UploadedFile::fake()->image('texture.png', 64, 30),
        ])->assertJson([
            'code' => 1,
            'message' => trans('skinlib.upload.invalid-size', [
                'type' => trans('general.skin'),
                'width' => 64,
                'height' => 30,
            ]),
        ]);
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'public' => true,
            'type' => 'alex',
            'file' => UploadedFile::fake()->image('texture.png', 100, 50),
        ])->assertJson([
            'code' => 1,
            'message' => trans('skinlib.upload.invalid-hd-skin', [
                'type' => trans('general.skin'),
                'width' => 100,
                'height' => 50,
            ]),
        ]);
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'public' => true,
            'type' => 'cape',
            'file' => UploadedFile::fake()->image('texture.png', 64, 30),
        ])->assertJson([
            'code' => 1,
            'message' => trans('skinlib.upload.invalid-size', [
                'type' => trans('general.cape'),
                'width' => 64,
                'height' => 30,
            ]),
        ]);

        $upload = UploadedFile::fake()->image('texture.png', 64, 32);

        // score is not enough
        $user = factory(User::class)->create(['score' => 0]);
        $this->actingAs($user)
            ->postJson(route('texture.upload'), [
                'name' => 'texture',
                'public' => true,
                'type' => 'steve',
                'file' => $upload,
            ])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.upload.lack-score'),
            ]);

        $user = factory(User::class)->create([
            'score' => (int) option('score_per_closet_item') + (int) option('score_per_storage'),
        ]);
        $this->actingAs($user)->postJson(
            route('texture.upload'),
            [
                'name' => 'texture',
                'public' => false,
                'type' => 'steve',
                'file' => $upload,
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans('skinlib.upload.lack-score'),
        ]);

        // success
        option(['score_award_per_texture' => 2]);
        $this->postJson(route('texture.upload'), [
            'name' => 'texture',
            'public' => true,
            'type' => 'steve',
            'file' => $upload,
        ])->assertJson([
            'code' => 0,
            'message' => trans('skinlib.upload.success', ['name' => 'texture']),
        ]);
        $texture = Texture::where('name', 'texture')->first();
        $disk->assertExists($texture->hash);
        $user->refresh();
        $this->assertEquals(2, $user->score);
        $this->assertEquals('texture', $texture->name);
        $this->assertEquals('steve', $texture->type);
        $this->assertEquals(1, $texture->likes);
        $this->assertEquals(1, $texture->size);
        $this->assertEquals($user->uid, $texture->uploader);
        $this->assertTrue($texture->public);
        $filter->assertApplied('uploaded_texture_file', function ($file) {
            $this->assertInstanceOf(UploadedFile::class, $file);

            return true;
        });
        $filter->assertApplied('uploaded_texture_name', function ($name) {
            $this->assertEquals('texture', $name);

            return true;
        });
        $filter->assertApplied(
            'uploaded_texture_hash',
            function ($hash, $file) use ($texture) {
                $this->assertEquals($texture->hash, $hash);
                $this->assertInstanceOf(UploadedFile::class, $file);

                return true;
            }
        );
        Event::assertDispatched(
            'texture.uploading',
            function ($eventName, $payload) use ($texture) {
                $this->assertInstanceOf(UploadedFile::class, $payload[0]);
                $this->assertEquals($texture->name, $payload[1]);
                $this->assertEquals($texture->hash, $payload[2]);

                return true;
            }
        );
        Event::assertDispatched(
            'texture.uploaded',
            function ($eventName, $payload) use ($texture) {
                $this->assertTrue($texture->is($payload[0]));
                $this->assertInstanceOf(UploadedFile::class, $payload[1]);

                return true;
            }
        );

        // upload a duplicated texture
        $user = factory(User::class)->create();
        $this->actingAs($user)
            ->postJson(route('texture.upload'), [
                'name' => 'texture',
                'public' => true,
                'type' => 'steve',
                'file' => $upload,
            ])->assertJson([
                'code' => 2,
                'message' => trans('skinlib.upload.repeated'),
                'data' => ['tid' => $texture->tid],
            ]);

        // rejected
        $filter->add('can_upload_texture', function ($can, $file, $name) {
            $this->assertInstanceOf(UploadedFile::class, $file);
            $this->assertEquals('texture', $name);

            return new Rejection('rejected');
        });
        $this->postJson(route('texture.upload'), [
                'name' => 'texture',
                'public' => true,
                'type' => 'steve',
                'file' => $upload,
            ])->assertJson(['code' => 1, 'message' => 'rejected']);

        $disk->delete($texture->hash);
    }

    public function testDelete()
    {
        /** @var FilesystemAdapter */
        $disk = Storage::fake('textures');

        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        option(['return_score' => false]);

        $duplicate = factory(Texture::class)->create([
            'hash' => $texture->hash,
            'uploader' => $uploader->uid,
        ]);
        $disk->put($texture->hash, '');

        // when file is occupied, the file should not be deleted
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $duplicate]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertNull(Texture::find($duplicate->tid));
        $disk->assertExists($texture->hash);

        $this->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertNull(Texture::find($texture->tid));
        $disk->assertMissing($texture->hash);

        // return score
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('score_per_storage'),
            $uploader->fresh()->score
        );

        $uploader->refresh();
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('private_score_per_storage'),
            $uploader->fresh()->score
        );

        option(['return_score' => false]);

        // return the award
        option(['score_award_per_texture' => 5]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score - 5, $uploader->fresh()->score);
        // option disabled
        option(['take_back_scores_after_deletion' => false]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score, $uploader->fresh()->score);
        // private texture
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score, $uploader->fresh()->score);

        // remove from closet
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $other->score = 0;
        $other->save();
        $this->actingAs($uploader)
            ->deleteJson(route('texture.delete', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $other->refresh();
        $this->assertEquals(option('score_per_closet_item'), $other->score);
    }

    public function testPrivacy()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // setting a texture to be private needs more scores
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score = 0;
        $uploader->save();
        $this->actingAs($uploader)
            ->putJson(route('texture.privacy', ['texture' => $texture]))
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.upload.lack-score'),
            ]);
        $this->assertTrue($texture->fresh()->public);

        $texture->public = true;
        $texture->save();
        $uploader->score = $texture->size *
            (option('private_score_per_storage') - option('score_per_storage'));
        $uploader->save();
        $this->putJson(route('texture.privacy', ['texture' => $texture]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
            ]);
        $this->assertEquals(0, $uploader->fresh()->score);
        $this->assertFalse($texture->fresh()->public);

        // When setting a texture to be private,
        // other players should not be able to use it.
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score += $texture->size * option('private_score_per_storage');
        $uploader->save();
        $player = factory(Player::class)->create(['tid_skin' => $texture->tid]);
        $other = factory(User::class)->create();
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $this->putJson(route('texture.privacy', ['texture' => $texture]))
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
            ]);
        $this->assertEquals(0, $player->fresh()->tid_skin);
        $this->assertEquals(0, $other->closet()->count());
        $this->assertEquals(
            $other->score + option('score_per_closet_item'),
            $other->fresh()->score
        );

        // take back the score
        option(['score_award_per_texture' => 5]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score = $texture->size * (
            option('private_score_per_storage') - option('score_per_storage')
        );
        $uploader->score += option('score_award_per_texture');
        $uploader->save();
        $this->putJson(route('texture.privacy', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $this->assertEquals(0, $uploader->fresh()->score);

        // without returning score
        option(['return_score' => false, 'private_score_per_storage' => 0]);
        $uploader->score += 1000;
        $uploader->save();
        $texture = factory(Texture::class)->create(['public' => 'false', 'uploader' => $uploader->uid]);
        $other = factory(User::class)->create();
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $this->putJson(route('texture.privacy', ['texture' => $texture]))
            ->assertJson(['code' => 0]);
        $this->assertEquals($other->score, $other->fresh()->score);
    }

    public function testRename()
    {
        $uploader = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // without `name` field
        $this->actingAs($uploader)
            ->putJson(route('texture.name', ['texture' => $texture]))
            ->assertJsonValidationErrors('name');

        // specified regular expression for texture name
        option(['texture_name_regexp' => '/\\d+/']);
        $this->putJson(
            route('texture.name', ['texture' => $texture]),
            ['name' => 'abc']
        )->assertJsonValidationErrors('name');
        option(['texture_name_regexp' => null]);

        // success
        $this->putJson(
            route('texture.name', ['texture' => $texture]),
            ['name' => 'abc']
        )->assertJson([
            'code' => 0,
            'message' => trans('skinlib.rename.success', ['name' => 'abc']),
        ]);
        $this->assertEquals('abc', $texture->fresh()->name);
    }

    public function testType()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // missing `type` field
        $this->actingAs($uploader)
            ->putJson(route('texture.type', ['texture' => $texture]))
            ->assertJsonValidationErrors('type');

        // invalid type
        $this->putJson(
            route('texture.type', ['texture' => $texture]),
            ['type' => 'nope']
        )->assertJsonValidationErrors('type');

        // success
        $this->putJson(
            route('texture.type', ['texture' => $texture]),
            ['type' => 'steve']
        )->assertJson([
            'code' => 0,
            'message' => trans('skinlib.model.success', ['model' => 'steve']),
        ]);
        $this->assertEquals('steve', $texture->fresh()->type);

        $duplicate = factory(Texture::class)->states('alex')->create([
            'uploader' => $other->uid,
            'hash' => $texture->hash,
        ]);

        // allow private texture
        $duplicate->public = false;
        $duplicate->save();
        $this->putJson(
            route('texture.type', ['texture' => $texture]),
            ['type' => 'alex']
        )->assertJson([
            'code' => 0,
            'message' => trans('skinlib.model.success', ['model' => 'alex']),
        ]);
    }
}
