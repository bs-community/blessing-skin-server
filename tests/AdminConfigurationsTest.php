<?php

namespace Tests;

use Cache;
use Redis;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminConfigurationsTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        // Do not use `WithoutMiddleware` trait
        parent::setUp();
        $this->actAs('admin');
    }

    public function testCustomize()
    {
        // Change color
        $this->visit('/admin/customize')
            ->select('skin-purple', 'color')
            ->press('submit_color');
        $this->assertEquals('skin-purple', option('color_scheme'));

        $this->visit('/admin/customize')
            ->type('url', 'home_pic_url')
            ->type('url', 'favicon_url')
            ->check('transparent_navbar')
            ->check('hide_intro')
            ->check('fixed_bg')
            ->select('1', 'copyright_prefer')
            ->type('copyright', 'copyright_text')
            ->press('submit_homepage');
        $this->assertEquals('url', option('home_pic_url'));
        $this->assertEquals('url', option('favicon_url'));
        $this->assertTrue(option('transparent_navbar'));
        $this->assertTrue(option('hide_intro'));
        $this->assertTrue(option('fixed_bg'));
        $this->assertEquals('1', option('copyright_prefer'));
        $this->assertEquals('copyright', option('copyright_text'));

        $this->visit('/admin/customize')
            ->type('css', 'custom_css')
            ->type('js', 'custom_js')
            ->press('submit_customJsCss');
        $this->assertEquals('css', option('custom_css'));
        $this->assertEquals('js', option('custom_js'));
    }

    public function testScore()
    {
        $this->visit('/admin/score')
            ->type('4', 'score_per_storage')
            ->type('6', 'private_score_per_storage')
            ->type('8', 'score_per_closet_item')
            ->uncheck('return_score')
            ->type('12', 'score_per_player')
            ->type('500', 'user_initial_score')
            ->press('submit_rate');
        $this->assertEquals('4', option('score_per_storage'));
        $this->assertEquals('6', option('private_score_per_storage'));
        $this->assertEquals('8', option('score_per_closet_item'));
        $this->assertFalse(option('return_score'));
        $this->assertEquals('12', option('score_per_player'));
        $this->assertEquals('500', option('user_initial_score'));

        $this->visit('/admin/score')
            ->type('1', 'reporter_score_modification')
            ->type('2', 'reporter_reward_score')
            ->press('submit_report');
        $this->assertEquals('1', option('reporter_score_modification'));
        $this->assertEquals('2', option('reporter_reward_score'));

        $this->visit('/admin/score')
            ->type('233', 'sign_score_from')
            ->type('666', 'sign_score_to')
            ->type('7', 'sign_gap_time')
            ->check('sign_after_zero')
            ->press('submit_sign');
        $this->assertEquals('233,666', option('sign_score'));
        $this->assertEquals('7', option('sign_gap_time'));
        $this->assertTrue(option('sign_after_zero'));

        $this->visit('/admin/score')
            ->type('1', 'score_award_per_texture')
            ->uncheck('take_back_scores_after_deletion')
            ->type('1', 'score_award_per_like')
            ->press('submit_sharing');
        $this->assertEquals('1', option('score_award_per_texture'));
        $this->assertFalse(option('take_back_scores_after_deletion'));
        $this->assertEquals('1', option('score_award_per_like'));
    }

    public function testOptions()
    {
        $this->visit('/admin/options')
            ->type('My Site', 'site_name')
            ->type('hi', 'site_description')
            ->type('http://blessing.skin/', 'site_url')
            ->uncheck('user_can_register')
            ->type('8', 'regs_per_ip')
            ->select('1', 'ip_get_method')
            ->type('2048', 'max_upload_file_size')
            ->see(trans(
                'options.general.max_upload_file_size.hint',
                ['size' => ini_get('upload_max_filesize')]
            ))
            ->select('cjk', 'player_name_rule')
            ->type('/^([0-9]+)$/', 'custom_player_name_regexp')
            ->select('1', 'api_type')
            ->check('auto_del_invalid_texture')
            ->uncheck('allow_downloading_texture')
            ->select('404', 'status_code_for_private')
            ->type('abc', 'texture_name_regexp')
            ->type('policy', 'content_policy')
            ->type('code', 'comment_script')
            ->press('submit_general');
        $this->assertEquals('My Site', option_localized('site_name'));
        $this->assertEquals('hi', option_localized('site_description'));
        $this->assertEquals('http://blessing.skin', option('site_url'));
        $this->assertFalse(option('user_can_register'));
        $this->assertEquals('8', option('regs_per_ip'));
        $this->assertEquals('1', option('ip_get_method'));
        $this->assertEquals('2048', option('max_upload_file_size'));
        $this->assertEquals('cjk', option('player_name_rule'));
        $this->assertEquals('/^([0-9]+)$/', option('custom_player_name_regexp'));
        $this->assertEquals('1', option('api_type'));
        $this->assertTrue(option('auto_del_invalid_texture'));
        $this->assertFalse(option('allow_downloading_texture'));
        $this->assertEquals('404', option('status_code_for_private'));
        $this->assertEquals('abc', option('texture_name_regexp'));
        $this->assertEquals('policy', option_localized('content_policy'));
        $this->assertEquals('code', option('comment_script'));

        $this->visit('/admin/options')
            ->type('http://blessing.skin/index.php', 'site_url')
            ->press('submit_general');
        $this->assertEquals('http://blessing.skin', option('site_url'));

        $this->visit('/admin/options')
            ->type('announcement', 'announcement')
            ->press('submit_announ');
        $this->assertEquals('announcement', option_localized('announcement'));

        $this->visit('/admin/options')
            ->type('kw', 'meta_keywords')
            ->type('desc', 'meta_description')
            ->type('<!-- nothing -->', 'meta_extras')
            ->press('submit_meta');
        $this->visit('/')
            ->see('<meta name="keywords" content="kw">')
            ->see('<meta name="description" content="desc">')
            ->see('<!-- nothing -->');

        $this->visit('/admin/options')
            ->type('key', 'recaptcha_sitekey')
            ->type('secret', 'recaptcha_secretkey')
            ->check('recaptcha_invisible')
            ->press('submit_recaptcha');
        $this->assertEquals('key', option('recaptcha_sitekey'));
        $this->assertEquals('secret', option('recaptcha_secretkey'));
        $this->assertTrue(option('recaptcha_invisible'));
    }

    public function testResource()
    {
        $this->visit('/admin/resource')
            ->check('force_ssl')
            ->uncheck('auto_detect_asset_url')
            ->check('return_204_when_notfound')
            ->type('0', 'cache_expire_time')
            ->type('url/', 'cdn_address')
            ->press('submit_resources');
        $this->assertTrue(option('force_ssl'));
        $this->assertFalse(option('auto_detect_asset_url'));
        $this->assertTrue(option('return_204_when_notfound'));
        $this->assertEquals('0', option('cache_expire_time'));
        $this->visit('/')->see('url/app/');

        $this->visit('/admin/resource')
            ->type('', 'cdn_address')
            ->press('submit_resources');
        $this->visit('/')->dontSee('url/app/index.js');

        $this->visit('/admin/resource')
            ->check('enable_redis')
            ->press('submit_redis');
        $this->assertTrue(option('enable_redis'));

        Redis::shouldReceive('ping')->once()->andReturn(true);
        $this->visit('/admin/resource')->see(trans('options.redis.connect.success'));

        Redis::shouldReceive('ping')->once()->andThrow(new \Exception('fake'));
        $this->visit('/admin/resource')
            ->see(trans('options.redis.connect.failed', ['msg' => 'fake']));

        option(['enable_redis' => false]);

        $this->visit('/admin/resource')
            ->see(trans('options.cache.driver', ['driver' => config('cache.default')]))
            ->check('enable_avatar_cache')
            ->check('enable_preview_cache')
            ->check('enable_json_cache')
            ->check('enable_notfound_cache')
            ->press('submit_cache');
        $this->assertTrue(option('enable_avatar_cache'));
        $this->assertTrue(option('enable_preview_cache'));
        $this->assertTrue(option('enable_json_cache'));
        $this->assertTrue(option('enable_notfound_cache'));

        Cache::shouldReceive('flush');
        $this->visit('/admin/resource')
            ->click(trans('options.cache.clear'))
            ->see(trans('options.cache.cleared'));
    }
}
