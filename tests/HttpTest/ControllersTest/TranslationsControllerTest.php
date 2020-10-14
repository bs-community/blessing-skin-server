<?php

namespace Tests;

use App\Models\User;
use App\Services\Translations\JavaScript;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\TranslationLoader\LanguageLine;

class TranslationsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function testList()
    {
        $line = LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->getJson('/admin/i18n/list')
            ->assertJson(['data' => [$line->toArray()]]);
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
        $this->spy(JavaScript::class, function ($spy) {
            $spy->shouldReceive('resetTime')->with('en')->once();
        });
        $line1 = LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);
        $line2 = LanguageLine::create([
            'group' => 'front-end',
            'key' => 'general.submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->putJson('/admin/i18n/'.$line1->id)
            ->assertJsonValidationErrors('text');
        $this->putJson('/admin/i18n/'.$line1->id, ['id' => 1, 'text' => 's'])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.updated')]);
        $this->putJson('/admin/i18n/'.$line2->id, ['id' => 2, 'text' => 's'])
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.updated')]);
        $this->assertEquals('s', trans('general.submit'));
        $this->assertEquals('s', trans('front-end.general.submit'));
    }

    public function testDelete()
    {
        $this->spy(JavaScript::class, function ($spy) {
            $spy->shouldReceive('resetTime')->with('en')->once();
        });
        $line1 = LanguageLine::create([
            'group' => 'general',
            'key' => 'submit',
            'text' => ['en' => 'submit'],
        ]);
        $line2 = LanguageLine::create([
            'group' => 'front-end',
            'key' => 'general.submit',
            'text' => ['en' => 'submit'],
        ]);

        $this->deleteJson('/admin/i18n/'.$line1->id)
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.deleted')]);
        $this->deleteJson('/admin/i18n/'.$line2->id)
            ->assertJson(['code' => 0, 'message' => trans('admin.i18n.deleted')]);
        $this->assertEquals('Submit', trans('general.submit'));
        $this->assertEquals('Submit', trans('front-end.general.submit'));
    }
}
