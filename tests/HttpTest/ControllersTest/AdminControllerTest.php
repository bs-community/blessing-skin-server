<?php

namespace Tests;

use App\Models\Texture;
use App\Models\User;
use App\Services\Plugin;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        // Do not use `WithoutMiddleware` trait
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function testIndex()
    {
        $filter = Fakes\Filter::fake();

        $this->get('/admin')->assertSuccessful();
        $filter->assertApplied('grid:admin.index');
    }

    public function testChartData()
    {
        User::factory()->create();
        User::factory()->create(['register_at' => '2019-01-01 00:00:00']);
        Texture::factory()->create();
        $this->getJson('/admin/chart')
            ->assertJson(['labels' => [
                trans('admin.index.user-registration'),
                trans('admin.index.texture-uploads'),
            ]])
            ->assertJsonStructure(['labels', 'xAxis', 'data']);
    }

    public function testStatus()
    {
        $this->mock(\App\Services\PluginManager::class, function ($mock) {
            $mock->shouldReceive('getEnabledPlugins')
                ->andReturn(collect([
                    'a' => new Plugin('', ['title' => 'MyPlugin', 'version' => '0.0.0']),
                ]));
        });
        $filter = Fakes\Filter::fake();

        $this->get('/admin/status')
            ->assertSee(PHP_VERSION)
            ->assertSee('(1)')
            ->assertSee('MyPlugin')
            ->assertSee('0.0.0');
        $filter->assertApplied('grid:admin.status');
    }
}
