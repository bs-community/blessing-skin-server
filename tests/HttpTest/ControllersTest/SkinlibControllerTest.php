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
        $this->get(route('skinlib.info', ['texture' => $texture]))
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
            ->postJson('/skinlib/upload', [
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
        $this->postJson('/skinlib/upload', [
            'name' => 'name',
            'file' => $upload,
            'type' => 'steve',
            'public' => true,
        ])->assertJsonValidationErrors('file');

        // without `name` field
        $this->postJson('/skinlib/upload')->assertJsonValidationErrors('name');

        // specified regular expression for texture name
        option(['texture_name_regexp' => '/\\d+/']);
        $this->postJson('/skinlib/upload', ['name' => 'abc'])
            ->assertJsonValidationErrors('name');
        option(['texture_name_regexp' => null]);

        // not a PNG file
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => UploadedFile::fake()->create('fake', 5),
        ])->assertJsonValidationErrors('file');

        // too large file
        option(['max_upload_file_size' => 2]);
        $upload = UploadedFile::fake()->create('large.png', 5);
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => $upload,
        ])->assertJsonValidationErrors('file');
        option(['max_upload_file_size' => 1024]);

        // no texture type is specified
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => $file,
        ])->assertJsonValidationErrors('type');

        // invalid texture type
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => $file,
            'type' => 'nope',
        ])->assertJsonValidationErrors('type');

        // without `public` field
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => $file,
            'type' => 'steve',
        ])->assertJsonValidationErrors('public');

        // invalid skin size
        $this->postJson('/skinlib/upload', [
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
        $this->postJson('/skinlib/upload', [
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
        $this->postJson('/skinlib/upload', [
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
            ->postJson('/skinlib/upload', [
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
            '/skinlib/upload',
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
        $this->postJson('/skinlib/upload', [
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
            ->postJson('/skinlib/upload', [
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
        $this->postJson('/skinlib/upload', [
                'name' => 'texture',
                'public' => true,
                'type' => 'steve',
                'file' => $upload,
            ])->assertJson(['code' => 1, 'message' => 'rejected']);

        $disk->delete($texture->hash);
    }

    public function testDelete()
    {
        $disk = Storage::fake('textures');

        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        option(['return_score' => false]);

        // Non-existed texture
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // Other user should not be able to delete
        $this->actingAs($other)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.no-permission'),
            ]);

        // Administrators can delete it
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertNull(Texture::find($texture->tid));

        $texture = factory(Texture::class)->create();
        factory(Texture::class)->create(['hash' => $texture->hash]);
        $disk->put($texture->hash, '');

        // When file is occupied, the file should not be deleted
        $this->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertNull(Texture::find($texture->tid));
        $this->assertTrue(Storage::disk('textures')->exists($texture->hash));

        $texture = factory(Texture::class)->create();
        factory(Texture::class)->create(['hash' => $texture->hash]);
        $this->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertNull(Texture::find($texture->tid));
        $this->assertFalse(Storage::disk('textures')->exists($texture->hash));

        // Return score
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('score_per_storage'),
            User::find($uploader->uid)->score
        );

        $uploader = User::find($uploader->uid);
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.delete.success'),
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('private_score_per_storage'),
            User::find($uploader->uid)->score
        );

        option(['return_score' => false]);

        // Return the award
        option(['score_award_per_texture' => 5]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score - 5, User::find($uploader->uid)->score);
        // Option disabled
        option(['take_back_scores_after_deletion' => false]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score, User::find($uploader->uid)->score);
        // Private texture
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false,
        ]);
        $uploader->refresh();
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $this->assertEquals($uploader->score, User::find($uploader->uid)->score);

        // Remove from closet
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $other->score = 0;
        $other->save();
        $this->actingAs($uploader)
            ->postJson('/skinlib/delete', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $other->refresh();
        $this->assertEquals(option('score_per_closet_item'), $other->score);
    }

    public function testPrivacy()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Non-existed texture
        $this->actingAs($uploader)
            ->postJson('/skinlib/privacy', ['tid' => -1])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // Other user should not be able to set privacy
        $this->actingAs($other)
            ->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.no-permission'),
            ]);

        // Administrators can change it
        $uploader->score += $texture->size * option('private_score_per_storage');
        $uploader->save();
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
            ]);
        $this->assertEquals(0, Texture::find($texture->tid)->public);

        // Setting a texture to be private needs more scores
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score = 0;
        $uploader->save();
        $this->actingAs($uploader)
            ->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.upload.lack-score'),
            ]);
        $this->assertEquals(1, Texture::find($texture->tid)->public);

        $texture->public = true;
        $texture->save();
        $uploader->score = $texture->size *
            (option('private_score_per_storage') - option('score_per_storage'));
        $uploader->save();
        $this->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
            ]);
        $this->assertEquals(0, User::find($uploader->uid)->score);

        // When setting a texture to be private,
        // other players should not be able to use it.
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score += $texture->size * option('private_score_per_storage');
        $uploader->save();
        $player = factory(Player::class)->create(['tid_skin' => $texture->tid]);
        $other = factory(User::class)->create();
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $this->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_skin);
        $this->assertEquals(0, $other->closet()->count());
        $this->assertEquals(
            $other->score + option('score_per_closet_item'),
            User::find($other->uid)->score
        );

        // Take back the score
        option(['score_award_per_texture' => 5]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score = $texture->size * (
            option('private_score_per_storage') - option('score_per_storage')
        );
        $uploader->score += option('score_award_per_texture');
        $uploader->save();
        $this->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $this->assertEquals(0, User::find($uploader->uid)->score);

        // Without returning score
        option(['return_score' => false, 'private_score_per_storage' => 0]);
        $uploader->score += 1000;
        $uploader->save();
        $texture = factory(Texture::class)->create(['public' => 'false', 'uploader' => $uploader->uid]);
        $other = factory(User::class)->create();
        $other->closet()->attach($texture->tid, ['item_name' => 'a']);
        $this->postJson('/skinlib/privacy', ['tid' => $texture->tid])
            ->assertJson(['code' => 0]);
        $this->assertEquals($other->score, User::find($other->uid)->score);
    }

    public function testRename()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Without `tid` field
        $this->actingAs($uploader)
            ->postJson('/skinlib/rename')
            ->assertJsonValidationErrors('tid');

        // `tid` is not a integer
        $this->postJson('/skinlib/rename', [
                'tid' => 'str',
            ])
            ->assertJsonValidationErrors('tid');

        // Without `new_name` field
        $this->postJson('/skinlib/rename', [
                'tid' => $texture->tid,
            ])
            ->assertJsonValidationErrors('new_name');

        // Non-existed texture
        $this->postJson('/skinlib/rename', [
                'tid' => -1,
                'new_name' => 'name',
            ])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // Other user should not be able to rename
        $this->actingAs($other)
            ->postJson('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'name',
            ])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.no-permission'),
            ]);

        // Administrators should be able to rename
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->postJson('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'name',
            ])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.rename.success', ['name' => 'name']),
            ]);
        $this->assertEquals('name', Texture::find($texture->tid)->name);

        // Uploader should be able to rename
        $this->actingAs($uploader)
            ->postJson('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'new_name',
            ])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.rename.success', ['name' => 'new_name']),
            ]);
        $this->assertEquals('new_name', Texture::find($texture->tid)->name);
    }

    public function testModel()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Non-existed texture
        $this->actingAs($uploader)
            ->postJson('/skinlib/model', [
                'tid' => -1,
                'model' => 'alex',
            ])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.non-existent'),
            ]);

        // Other user should not be able to change model
        $this->actingAs($other)
            ->postJson('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex',
            ])
            ->assertJson([
                'code' => 1,
                'message' => trans('skinlib.no-permission'),
            ]);

        // Administrators should be able to change model
        $this->actingAs(factory(User::class)->states('admin')->create())
            ->postJson('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex',
            ])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.model.success', ['model' => 'alex']),
            ]);
        $this->assertEquals('alex', Texture::find($texture->tid)->type);

        // Uploader should be able to change model
        $this->actingAs($uploader)
            ->postJson('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'steve',
            ])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.model.success', ['model' => 'steve']),
            ]);
        $this->assertEquals('steve', Texture::find($texture->tid)->type);

        $duplicate = factory(Texture::class)->states('alex')->create([
            'uploader' => $other->uid,
            'hash' => $texture->hash,
        ]);

        // Allow private texture
        $duplicate->public = false;
        $duplicate->save();
        $this->actingAs($uploader)
            ->postJson('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex',
            ])
            ->assertJson([
                'code' => 0,
                'message' => trans('skinlib.model.success', ['model' => 'alex']),
            ]);
    }
}
