<?php

namespace Tests;

use App\Models\Player;
use App\Models\Texture;
use App\Models\User;
use Blessing\Filter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        resolve(Filter::class)->add('user_badges', function ($badges, $uploader) use ($uid) {
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
        // Non-existed texture
        $this->get('/skinlib/info/1')->assertNotFound();

        $texture = factory(Texture::class)->create();
        $this->get('/skinlib/info/'.$texture->tid)
            ->assertJson(['data' => [
                'tid' => $texture->tid,
                'name' => $texture->name,
                'type' => $texture->type,
                'likes' => $texture->likes,
                'hash' => $texture->hash,
                'size' => $texture->size,
                'uploader' => $texture->uploader,
                'public' => $texture->public,
                'upload_at' => $texture->upload_at->format('Y-m-d H:i:s'),
            ]]);
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
        Storage::fake('textures');

        // Some error occurred when uploading file
        $file = UploadedFile::fake()->image('test.png');
        $upload = new UploadedFile(
            $file->path(),
            'test.png',
            'image/png',
            UPLOAD_ERR_NO_TMP_DIR,
            true
        );
        $this->actingAs(factory(User::class)->create())
            ->postJson(
                '/skinlib/upload',
                ['file' => $upload]
            )->assertJson([
                'code' => UPLOAD_ERR_NO_TMP_DIR,
                'message' => \App\Http\Controllers\SkinlibController::$phpFileUploadErrors[UPLOAD_ERR_NO_TMP_DIR],
            ]);

        // Without `name` field
        $this->postJson('/skinlib/upload')->assertJsonValidationErrors('name');

        // Specified regular expression for texture name
        option(['texture_name_regexp' => '/\\d+/']);
        $this->postJson('/skinlib/upload', [
            'name' => 'abc',
        ])->assertJsonValidationErrors('name');
        option(['texture_name_regexp' => null]);

        // Without file
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
        ])->assertJsonValidationErrors('file');

        // Too large file
        option(['max_upload_file_size' => 2]);
        $upload = UploadedFile::fake()->create('large.png', 5);
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => $upload,
        ])->assertJsonValidationErrors('file');
        option(['max_upload_file_size' => 1024]);

        // Without `public` field
        $this->postJson('/skinlib/upload', [
            'name' => 'texture',
            'file' => 'content',    // Though it is not a file, it is OK
        ])->assertJsonValidationErrors('public');

        // Not a PNG image
        $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'file' => UploadedFile::fake()->create('fake', 5),
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans('skinlib.upload.type-error'),
        ]);

        // No texture type is specified
        $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'file' => UploadedFile::fake()->image('texture.png', 64, 32),
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans('general.illegal-parameters'),
        ]);

        // Invalid skin size
        $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'steve',
                'file' => UploadedFile::fake()->image('texture.png', 64, 30),
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans(
                'skinlib.upload.invalid-size',
                [
                    'type' => trans('general.skin'),
                    'width' => 64,
                    'height' => 30,
                ]
            ),
        ]);
        $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'alex',
                'file' => UploadedFile::fake()->image('texture.png', 100, 50),
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans(
                'skinlib.upload.invalid-hd-skin',
                [
                    'type' => trans('general.skin'),
                    'width' => 100,
                    'height' => 50,
                ]
            ),
        ]);
        $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'cape',
                'file' => UploadedFile::fake()->image('texture.png', 64, 30),
            ]
        )->assertJson([
            'code' => 1,
            'message' => trans(
                'skinlib.upload.invalid-size',
                [
                    'type' => trans('general.cape'),
                    'width' => 64,
                    'height' => 30,
                ]
            ),
        ]);

        $upload = UploadedFile::fake()->image('texture.png', 64, 32);

        // Score is not enough
        $user = factory(User::class)->create(['score' => 0]);
        $this->actingAs($user)
            ->postJson(
                '/skinlib/upload',
                [
                    'name' => 'texture',
                    'public' => 'true',
                    'type' => 'steve',
                    'file' => $upload,
                ]
            )
            ->assertJson([
                'code' => 7,
                'message' => trans('skinlib.upload.lack-score'),
            ]);

        $user = factory(User::class)->create([
            'score' => (int) option('score_per_closet_item') + (int) option('score_per_storage'),
        ]);
        $this->actingAs($user)->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'false',  // Private texture cost more scores
                'type' => 'steve',
                'file' => $upload,
            ]
        )->assertJson([
            'code' => 7,
            'message' => trans('skinlib.upload.lack-score'),
        ]);

        // Success
        option(['score_award_per_texture' => 2]);
        $response = $this->postJson(
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',    // Public texture
                'type' => 'steve',
                'file' => $upload,
            ]
        );
        $t = Texture::where('name', 'texture')->first();
        $response->assertJson([
            'code' => 0,
            'message' => trans('skinlib.upload.success', ['name' => 'texture']),
            'data' => ['tid' => $t->tid],
        ]);
        Storage::disk('textures')->assertExists($t->hash);
        $user = User::find($user->uid);
        $this->assertEquals(2, $user->score);
        $this->assertEquals('texture', $t->name);
        $this->assertEquals('steve', $t->type);
        $this->assertEquals(1, $t->likes);
        $this->assertEquals(1, $t->size);
        $this->assertEquals($user->uid, $t->uploader);
        $this->assertTrue($t->public);

        // Upload a duplicated texture
        $user = factory(User::class)->create();
        $this->actingAs($user)
            ->postJson(
                '/skinlib/upload',
                [
                    'name' => 'texture',
                    'public' => 'true',
                    'type' => 'steve',
                    'file' => $upload,
                ]
            )->assertJson([
                'code' => 2,
                'message' => trans('skinlib.upload.repeated'),
                'data' => ['tid' => $t->tid],
            ]);

        unlink(storage_path('framework/testing/disks/textures/'.$t->hash));
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
