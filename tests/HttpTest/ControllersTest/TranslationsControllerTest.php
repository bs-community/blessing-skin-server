<?php

namespace Tests;

use App\Services\Translations\JavaScript;
use Spatie\TranslationLoader\LanguageLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TranslationsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAs('admin');
    }

    public function testList()
    {
        LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->getJson('/admin/i18n/list')
            ->assertJson([
                [
                    'group' => 'general',
                    'key' => 'submit',
                    'text' => 'submit',
                ],
            ]);
    }

    public function testCreate()
    {
        // Request validation
        $this->post('/admin/i18n', [])->assertRedirect('/');
        $this->post('/admin/i18n', ['group' => 'general'])
            ->assertRedirect('/');
        $this->post('/admin/i18n', ['group' => 'general', 'key' => 'submit'])
            ->assertRedirect('/');

        $this->spy(JavaScript::class, function ($spy) {
            $spy->shouldReceive('resetTime')->with('en')->once();
        });

        $this->post('/admin/i18n', [
            'group' => 'front-end',
            'key' => 'general.submit',
            'text' => 'submit',
        ])->assertRedirect('/admin/i18n')->assertSessionHas('success', true);

        $this->post('/admin/i18n', [
            'group' => 'general',
            'key' => 'submit',
            'text' => 'submit',
        ])->assertRedirect('/admin/i18n');
        $this->get('/admin/i18n')
            ->assertSee(trans('admin.i18n.added'))
            ->assertSessionMissing('success');
    }

    public function testUpdate()
    {
        // Request validation
        $this->putJson('/admin/i18n', [])->assertJsonValidationErrors('id');
        $this->putJson('/admin/i18n', ['id' => 'a'])
            ->assertJsonValidationErrors('id');
        $this->putJson('/admin/i18n', ['id' => 1])
            ->assertJsonValidationErrors('text');

        $this->putJson('/admin/i18n', ['id' => 1, 'text' => 's'])->assertNotFound();

        $this->spy(JavaScript::class, function ($spy) {
            $spy->shouldReceive('resetTime')->with('en')->once();
        });
        LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);
        LanguageLine::create([
            'group' => 'front-end',
            'key' => 'general.submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->putJson('/admin/i18n', ['id' => 1, 'text' => 's'])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.updated')]);
        $this->putJson('/admin/i18n', ['id' => 2, 'text' => 's'])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.updated')]);
        $this->assertEquals('s', trans('general.submit'));
        $this->assertEquals('s', trans('front-end.general.submit'));
    }

    public function testDelete()
    {
        // Request validation
        $this->deleteJson('/admin/i18n', [])->assertJsonValidationErrors('id');
        $this->deleteJson('/admin/i18n', ['id' => 'a'])
            ->assertJsonValidationErrors('id');

        $this->deleteJson('/admin/i18n', ['id' => 1])->assertNotFound();

        $this->spy(JavaScript::class, function ($spy) {
            $spy->shouldReceive('resetTime')->with('en')->once();
        });
        LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);
        LanguageLine::create([
            'group' => 'front-end',
            'key' => 'general.submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->deleteJson('/admin/i18n', ['id' => 1])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.deleted')]);
        $this->deleteJson('/admin/i18n', ['id' => 2])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.deleted')]);
        $this->assertEquals('Submit', trans('general.submit'));
        $this->assertEquals('Submit', trans('front-end.general.submit'));
    }
}
