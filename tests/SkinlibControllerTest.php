<?php

use App\Models\User;
use App\Models\Closet;
use App\Models\Player;
use App\Models\Texture;
use org\bovigo\vfs\vfsStream;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SkinlibControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $vfs_root;

    protected function setUp()
    {
        parent::setUp();
        $this->vfs_root = vfsStream::setup();
    }

    protected function serializeTextures($textures) {
        return $textures
            ->map(function ($texture) {
                return [
                    'tid' => $texture->tid,
                    'name' => $texture->name,
                    'type' => $texture->type,
                    'likes' => $texture->likes,
                    'hash' => $texture->hash,
                    'size' => $texture->size,
                    'uploader' => $texture->uploader,
                    'public' => $texture->public ? 1 : 0,
                    'upload_at' => $texture->upload_at->format('Y-m-d H:i:s')
                ];
            })
            ->all();
    }

    public function testIndex()
    {
        $this->visit('/skinlib')
            ->seePageIs('/skinlib')
            ->assertViewHas('user');
    }

    public function testGetSkinlibFiltered()
    {
        $this->get('/skinlib/data')
            ->seeJson([
                'items' => [],
                'anonymous' => true,
                'total_pages' => 0
            ]);

        $steves = factory(Texture::class)->times(5)->create();
        $alexs = factory(Texture::class, 'alex')->times(5)->create();
        $skins = $steves->merge($alexs);
        $capes = factory(Texture::class, 'cape')->times(5)->create();

        // Default arguments
        $expected = $skins
            ->sortByDesc('upload_at')
            ->values();     // WTF! DO NOT FORGET IT!!
        $this->get('/skinlib/data')
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Only steve
        $expected =  $steves
            ->sortByDesc('upload_at')
            ->values();
        $this->get('/skinlib/data?filter=steve')
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Invalid type
        $this->get('/skinlib/data?filter=what')
            ->seeJson([
                'items' => [],
                'anonymous' => true,
                'total_pages' => 0
            ]);

        // Only capes
        $expected = $capes
            ->sortByDesc('upload_at')
            ->values();
        $this->get('/skinlib/data?filter=cape')
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Only specified uploader
        $uid = $skins->random()->uploader;
        $expected = $skins
            ->filter(function ($texture) use ($uid) {
                return $texture->uploader == $uid;
            })
            ->sortByDesc('upload_at')
            ->values();
        $this->get('/skinlib/data?uploader='.$uid)
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Sort by `likes`
        $expected = $skins
            ->sortByDesc('likes')
            ->values();
        $this->get('/skinlib/data?sort=likes')
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Search
        $keyword = str_limit($skins->random()->name, 1, '');
        $expected = $skins
            ->filter(function ($texture) use ($keyword) {
                return str_contains($texture->name, $keyword) ||
                    str_contains($texture->name, strtolower($keyword));
            })
            ->sortByDesc('upload_at')
            ->values();
        $this->get('/skinlib/data?keyword='.$keyword)
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // More than one argument
        $keyword = str_limit($skins->random()->name, 1, '');
        $expected = $skins
            ->filter(function ($texture) use ($keyword) {
                return str_contains($texture->name, $keyword) ||
                    str_contains($texture->name, strtolower($keyword));
            })
            ->sortByDesc('likes')
            ->values();
        $this->get('/skinlib/data?sort=likes&keyword='.$keyword)
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 1
            ]);

        // Pagination
        $steves = factory(Texture::class)
            ->times(15)
            ->create()
            ->merge($steves);
        $skins = $steves->merge($alexs);
        $expected = $skins
            ->sortByDesc('upload_at')
            ->values()
            ->forPage(1, 20);
        $expected = $this->serializeTextures($expected);
        $this->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => true,
                'total_pages' => 2
            ]);
        $this->get('/skinlib/data?page=-5')
            ->seeJson([
                'items' => $expected,
                'anonymous' => true,
                'total_pages' => 2
            ]);
        $expected = $skins
            ->sortByDesc('upload_at')
            ->values()
            ->forPage(2, 20)
            ->values();
        $expected = $this->serializeTextures($expected);
        $this->get('/skinlib/data?page=2')
            ->seeJson([
                'items' => $expected,
                'anonymous' => true,
                'total_pages' => 2
            ]);
        $this->get('/skinlib/data?page=8')
            ->seeJson([
                'items' => [],
                'anonymous' => true,
                'total_pages' => 2
            ]);
        $this->get('/skinlib/data?items_per_page=-6&page=2')
            ->seeJson([
                'items' => $expected,
                'anonymous' => true,
                'total_pages' => 2
            ]);
        $expected = $skins
            ->sortByDesc('upload_at')
            ->values()
            ->forPage(3, 8)
            ->values();
        $this->get('/skinlib/data?page=3&items_per_page=8')
            ->seeJson([
                'items' => $this->serializeTextures($expected),
                'anonymous' => true,
                'total_pages' => 4
            ]);

        // Add some private textures
        $uploader = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $private = factory(Texture::class)
            ->times(5)
            ->create(['public' => false, 'uploader' => $uploader->uid]);

        // If not logged in, private textures should not be shown
        $expected = $skins
            ->sortByDesc('upload_at')
            ->values()
            ->forPage(1, 20);
        $expected = $this->serializeTextures($expected);
        $this->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => true,
                'total_pages' => 2
            ]);

        // Other users should not see someone's private textures
        for ($i = 0; $i < count($expected); $i++) {
            $expected[$i]['liked'] = false;
        }
        $this->actAs($otherUser)
            ->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => false,
                'total_pages' => 2
            ]);

        // A user has added a texture from skin library to his closet
        $texture = $skins
            ->sortByDesc('upload_at')
            ->values()
            ->first();
        $closet = new Closet($otherUser->uid);
        $closet->add($texture->tid, $texture->name);
        $closet->save();
        for ($i = 0; $i < count($expected); $i++) {
            if ($expected[$i]['tid'] == $texture->tid) {
                $expected[$i]['liked'] = true;
            } else {
                $expected[$i]['liked'] = false;
            }
        }
        $this->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => false,
                'total_pages' => 2
            ]);

        // Uploader can see his private textures
        $expected = $skins
            ->merge($private)
            ->sortByDesc('upload_at')
            ->values()
            ->forPage(1, 20);
        $expected = $this->serializeTextures($expected);
        for ($i = 0; $i < count($expected); $i++) {
            // The reason we use `false` here is that some textures just were
            // uploaded by this user, but these textures are not in his closet.
            // By default(not in testing like now), when you uploaded a texture,
            // that texture will be added to your closet.
            // So here, we can assume that a user upload some textures, but he
            // has deleted them from his closet.
            $expected[$i]['liked'] = false;
        }
        $this->actAs($uploader)
            ->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => false,
                'total_pages' => 2
            ]);

        // Administrators can see private textures
        $admin = factory(User::class, 'admin')->create();
        $this->actAs($admin)
            ->get('/skinlib/data')
            ->seeJson([
                'items' => $expected,
                'anonymous' => false,
                'total_pages' => 2
            ]);
    }

    public function testShow()
    {
        // Cannot find texture
        $this->get('/skinlib/show/1')
            ->see(trans('skinlib.show.deleted'));

        // Invalid texture
        option(['auto_del_invalid_texture' => false]);
        $texture = factory(Texture::class)->create();
        $this->get('/skinlib/show/'.$texture->tid)
            ->see(trans('skinlib.show.deleted').trans('skinlib.show.contact-admin'));
        $this->assertNotNull(Texture::find($texture->tid));

        option(['auto_del_invalid_texture' => true]);
        $this->get('/skinlib/show/'.$texture->tid)
            ->see(trans('skinlib.show.deleted'));
        $this->assertNull(Texture::find($texture->tid));

        // Show a texture
        $texture = factory(Texture::class)->create();
        Storage::disk('textures')->put($texture->hash, '');
        $this->get('/skinlib/show/'.$texture->tid)
            ->assertViewHas('texture')
            ->assertViewHas('with_out_filter', true)
            ->assertViewHas('user');

        // Guest should not see private texture
        $uploader = factory(User::class)->create();
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false
        ]);
        Storage::disk('textures')->put($texture->hash, '');
        $this->get('/skinlib/show/'.$texture->tid)
            ->see(trans('skinlib.show.private'));

        // Other user should not see private texture
        $this->actAs('normal')
            ->get('/skinlib/show/'.$texture->tid)
            ->see(trans('skinlib.show.private'));

        // Administrators should be able to see private textures
        $this->actAs('admin')
            ->get('/skinlib/show/'.$texture->tid)
            ->assertViewHas('texture');

        // Uploader should be able to see private textures
        $this->actAs($uploader)
            ->get('/skinlib/show/'.$texture->tid)
            ->assertViewHas('texture');
    }

    public function testInfo()
    {
        // Non-existed texture
        $this->get('/skinlib/info/1')
            ->seeJson([]);

        $texture = factory(Texture::class)->create();
        $this->get('/skinlib/info/'.$texture->tid)
            ->seeJson([
                'tid' => $texture->tid,
                'name' => $texture->name,
                'type' => $texture->type,
                'likes' => $texture->likes,
                'hash' => $texture->hash,
                'size' => $texture->size,
                'uploader' => $texture->uploader,
                'public' => $texture->public ? 1 : 0,
                'upload_at' => $texture->upload_at->format('Y-m-d H:i:s')
            ]);
    }

    public function testUpload()
    {
        $this->actAs('normal')
            ->visit('/skinlib/upload')
            ->seePageIs('/skinlib/upload')
            ->assertViewHas('user')
            ->assertViewHas('with_out_filter', true);
    }

    public function testHandleUpload()
    {
        // Some error occurred when uploading file
        $file = vfsStream::newFile('test.png')
            ->at($this->vfs_root);
        $upload = new UploadedFile(
            $file->url(),
            $file->getName(),
            'image/png',
            50,
            UPLOAD_ERR_NO_TMP_DIR,
            true
        );
        $this->actAs('normal')
            ->call(
                'POST',
                '/skinlib/upload',
                [],
                [],
                ['file' => $upload]
            )
            ->getContent();
        $this->seeJson([
            'errno' => UPLOAD_ERR_NO_TMP_DIR,
            'msg' => App\Http\Controllers\SkinlibController::$phpFileUploadErrors[UPLOAD_ERR_NO_TMP_DIR]
        ]);

        // Without `name` field
        $this->post('/skinlib/upload', [], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'Name'])
        ]);

        // With some special chars
        $this->post('/skinlib/upload', [
            'name' => '\\'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.no_special_chars', ['attribute' => 'Name'])
        ]);

        // Without file
        $this->post('/skinlib/upload', [
            'name' => 'texture'
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'File'])
        ]);

        // Too large file
        option(['max_upload_file_size' => 2]);
        $this->post('/skinlib/upload', [
            'name' => 'texture',
            'file' => $upload
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.max.file', ['attribute' => 'File', 'max' => '2'])
        ]);
        option(['max_upload_file_size' => 1024]);

        // Without `public` field
        $this->post('/skinlib/upload', [
            'name' => 'texture',
            'file' => 'content'    // Though it is not a file, it is OK
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ])->seeJson([
            'errno' => 1,
            'msg' => trans('validation.required', ['attribute' => 'public'])
        ]);

        // Not a PNG image
        $upload = new UploadedFile(
            $file->url(),
            $file->getName(),
            'image/jpeg',
            2,
            null,
            true
        );
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 1,
            'msg' => trans('skinlib.upload.type-error')
        ]);

        // No texture type is specified
        $file = vfsStream::newFile('test.png')
            ->at($this->vfs_root);
        imagepng(imagecreatetruecolor(64, 32), $file->url());
        $upload = new UploadedFile($file->url(), $file->getName(), 'image/x-png', 2, null, true);
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 1,
            'msg' => trans('general.illegal-parameters')
        ]);

        // Invalid skin size
        imagepng(imagecreatetruecolor(64, 30), $file->url());
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'steve'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 1,
            'msg' => trans(
                'skinlib.upload.invalid-size',
                [
                    'type' => trans('general.skin'),
                    'width' => 64,
                    'height' => 30
                ]
            )
        ]);
        imagepng(imagecreatetruecolor(100, 50), $file->url());
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'alex'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 1,
            'msg' => trans(
                'skinlib.upload.invalid-hd-skin',
                [
                    'type' => trans('general.skin'),
                    'width' => 100,
                    'height' => 50
                ]
            )
        ]);
        imagepng(imagecreatetruecolor(64, 30), $file->url());
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'cape'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 1,
            'msg' => trans(
                'skinlib.upload.invalid-size',
                [
                    'type' => trans('general.cape'),
                    'width' => 64,
                    'height' => 30
                ]
            )
        ]);

        imagepng(imagecreatetruecolor(64, 32), $file->url());
        $upload = new UploadedFile($file->url(), $file->getName(), 'image/png', 1, null, true);

        // Score is not enough
        $user = factory(User::class)->create(['score' => 0]);
        $this->actAs($user)
            ->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'steve'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 7,
            'msg' => trans('skinlib.upload.lack-score')
        ]);

        $user->score =
            (int) option('score_per_closet_item') +
            (int) option('score_per_storage');
        $user->save();
        $this->call(
                'POST',
                '/skinlib/upload',
                [
                    'name' => 'texture',
                    'public' => 'false',    // Private texture cost more scores
                    'type' => 'steve'
                ],
                [],
                ['file' => $upload]
            );
        $this->seeJson([
            'errno' => 7,
            'msg' => trans('skinlib.upload.lack-score')
        ]);
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',    // Public texture
                'type' => 'steve'
            ],
            [],
            ['file' => $upload]
        );
        $uploaded = Texture::where('name', 'texture')->first();
        $this->seeJson([
            'errno' => 0,
            'msg' => trans('skinlib.upload.success', ['name' => 'texture']),
            'tid' => $uploaded->tid
        ]);
        $this->assertEquals(0, User::find($user->uid)->score);
        $this->assertTrue(Storage::disk('textures')->exists($uploaded->hash));
        $this->assertEquals('texture', $user->getCloset()->get($uploaded->tid)['name']);
        $this->assertEquals('texture', $uploaded->name);
        $this->assertEquals('steve', $uploaded->type);
        $this->assertEquals(1, $uploaded->likes);
        $this->assertEquals(1, $uploaded->size);
        $this->assertEquals('1', $uploaded->public);
        $this->assertEquals($user->uid, $uploaded->uploader);

        // Upload a duplicated texture
        $user->score = 1000;
        $user->save();
        $this->call(
            'POST',
            '/skinlib/upload',
            [
                'name' => 'texture',
                'public' => 'true',
                'type' => 'steve'
            ],
            [],
            ['file' => $upload]
        );
        $this->seeJson([
            'errno' => 0,
            'msg' => trans('skinlib.upload.repeated'),
            'tid' => $uploaded->tid
        ]);
    }

    public function testDelete()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        option(['return_score' => false]);

        // Non-existed texture
        $this->actAs($uploader)
            ->post('/skinlib/delete', ['tid' => -1])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.non-existent')
            ]);

        // Other user should not be able to delete
        $this->actAs($other)
            ->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.no-permission')
            ]);

        // Administrators can delete it
        $this->actAs('admin')
            ->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.delete.success')
            ]);
        $this->assertNull(Texture::find($texture->tid));

        $texture = factory(Texture::class)->create();
        factory(Texture::class)->create(['hash' => $texture->hash]);
        Storage::disk('textures')->put($texture->hash, '');

        // When file is occupied, the file should not be deleted
        $this->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.delete.success')
            ]);
        $this->assertNull(Texture::find($texture->tid));
        $this->assertTrue(Storage::disk('textures')->exists($texture->hash));

        $texture = factory(Texture::class)->create();
        factory(Texture::class)->create(['hash' => $texture->hash]);
        $this->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.delete.success')
            ]);
        $this->assertNull(Texture::find($texture->tid));
        $this->assertFalse(Storage::disk('textures')->exists($texture->hash));

        // Return score
        option(['return_score' => true]);
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $this->actAs($uploader)
            ->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.delete.success')
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('score_per_storage'),
            User::find($uploader->uid)->score
        );

        $uploader = User::find($uploader->uid);
        $texture = factory(Texture::class)->create([
            'uploader' => $uploader->uid,
            'public' => false
        ]);
        $this->actAs($uploader)
            ->post('/skinlib/delete', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.delete.success')
            ]);
        $this->assertEquals(
            $uploader->score + $texture->size * option('private_score_per_storage'),
            User::find($uploader->uid)->score
        );
    }

    public function testPrivacy()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Non-existed texture
        $this->actAs($uploader)
            ->post('/skinlib/privacy', ['tid' => -1])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.non-existent')
            ]);

        // Other user should not be able to set privacy
        $this->actAs($other)
            ->post('/skinlib/privacy', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.no-permission')
            ]);

        // Administrators can change it
        $uploader->score += $texture->size * option('private_score_per_storage');
        $uploader->save();
        $this->actAs('admin')
            ->post('/skinlib/privacy', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
                'public' => 0
            ]);
        $this->assertEquals(0, Texture::find($texture->tid)->public);

        // Setting a texture to be private needs more scores
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);
        $uploader->score = 0;
        $uploader->save();
        $this->actAs($uploader)
            ->post('/skinlib/privacy', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.upload.lack-score')
            ]);
        $this->assertEquals(1, Texture::find($texture->tid)->public);

        $texture->public = true;
        $texture->save();
        $uploader->score = $texture->size *
            (option('private_score_per_storage') - option('score_per_storage'));
        $uploader->save();
        $this->post('/skinlib/privacy', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
                'public' => 0
            ]);
        $this->assertEquals(0, User::find($uploader->uid)->score);

        // When setting a texture to be private,
        // other players should not be able to use it.
        $texture->public = '1';
        $texture->save();
        $uploader->score += $texture->size * option('private_score_per_storage');
        $uploader->save();
        $player = factory(Player::class)->create(['tid_steve' => $texture->tid]);
        $this->post('/skinlib/privacy', ['tid' => $texture->tid])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.privacy.success', ['privacy' => trans('general.private')]),
                'public' => 0
            ]);
        $this->assertEquals(0, Player::find($player->pid)->tid_steve);
    }

    public function testRename()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Without `tid` field
        $this->actAs($uploader)
            ->post('/skinlib/rename', [], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'tid'])
            ]);

        // `tid` is not a integer
        $this->post('/skinlib/rename', [
                'tid' => 'str'
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.integer', ['attribute' => 'tid'])
            ]);

        // Without `new_name` field
        $this->post('/skinlib/rename', [
                'tid' => $texture->tid
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.required', ['attribute' => 'new name'])
            ]);

        // `new_name` has special chars
        $this->post('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => '\\'
            ], [
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('validation.no_special_chars', ['attribute' => 'new name'])
            ]);

        // Non-existed texture
        $this->post('/skinlib/rename', [
                'tid' => -1,
                'new_name' => 'name'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.non-existent')
            ]);

        // Other user should not be able to rename
        $this->actAs($other)
            ->post('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'name'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.no-permission')
            ]);

        // Administrators should be able to rename
        $this->actAs('admin')
            ->post('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'name'
            ])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.rename.success', ['name' => 'name'])
            ]);
        $this->assertEquals('name', Texture::find($texture->tid)->name);

        // Uploader should be able to rename
        $this->actAs($uploader)
            ->post('/skinlib/rename', [
                'tid' => $texture->tid,
                'new_name' => 'new_name'
            ])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.rename.success', ['name' => 'new_name'])
            ]);
        $this->assertEquals('new_name', Texture::find($texture->tid)->name);
    }

    public function testChangeModel()
    {
        $uploader = factory(User::class)->create();
        $other = factory(User::class)->create();
        $texture = factory(Texture::class)->create(['uploader' => $uploader->uid]);

        // Non-existed texture
        $this->actAs($uploader)
            ->post('/skinlib/model', [
                'tid' => -1,
                'model' => 'alex'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.non-existent')
            ]);

        // Other user should not be able to change model
        $this->actAs($other)
            ->post('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.no-permission')
            ]);

        // Administrators should be able to change model
        $this->actAs('admin')
            ->post('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex'
            ])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.model.success', ['model' => 'alex'])
            ]);
        $this->assertEquals('alex', Texture::find($texture->tid)->type);

        // Uploader should be able to change model
        $this->actAs($uploader)
            ->post('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'steve'
            ])
            ->seeJson([
                'errno' => 0,
                'msg' => trans('skinlib.model.success', ['model' => 'steve'])
            ]);
        $this->assertEquals('steve', Texture::find($texture->tid)->type);

        $duplicate = factory(Texture::class, 'alex')->create([
            'uploader' => $other->uid,
            'hash' => $texture->hash
        ]);

        // Should fail if there is already a texture with same hash and chosen model
        $this->actAs($uploader)
            ->post('/skinlib/model', [
                'tid' => $texture->tid,
                'model' => 'alex'
            ])
            ->seeJson([
                'errno' => 1,
                'msg' => trans('skinlib.model.duplicate', ['tid' => $duplicate->tid])
            ]);
    }
}
