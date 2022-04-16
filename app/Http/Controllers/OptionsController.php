<?php

namespace App\Http\Controllers;

use App\Services\Facades\Option;
use App\Services\OptionForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OptionsController extends Controller
{
    public function customize(Request $request)
    {
        $homepage = Option::form('homepage', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('home_pic_url')->hint();

            $form->text('favicon_url')->hint()->description();

            $form->checkbox('transparent_navbar')->label();

            $form->checkbox('hide_intro')->label();

            $form->checkbox('fixed_bg')->label();

            $form->select('copyright_prefer')
                    ->option('0', 'Powered with ❤ by Blessing Skin Server.')
                    ->option('1', 'Powered by Blessing Skin Server.')
                    ->option('2', 'Proudly powered by Blessing Skin Server.')
                    ->option('3', '由 Blessing Skin Server 强力驱动。')
                    ->option('4', '采用 Blessing Skin Server 搭建。')
                    ->option('5', '使用 Blessing Skin Server 稳定运行。')
                    ->option('6', '自豪地采用 Blessing Skin Server。')
                ->description();

            $form->textarea('copyright_text')->rows(6)->description();
        })->handle(function () {
            Option::set('copyright_prefer_'.config('app.locale'), request('copyright_prefer'));
            Option::set('copyright_text_'.config('app.locale'), request('copyright_text'));
        });

        $customJsCss = Option::form('customJsCss', OptionForm::AUTO_DETECT, function ($form) {
            $form->textarea('custom_css', 'CSS')->rows(6);
            $form->textarea('custom_js', 'JavaScript')->rows(6);
        })->addMessage()->handle();

        if ($request->isMethod('post') && $request->input('action') === 'color') {
            $navbar = $request->input('navbar');
            if ($navbar) {
                option(['navbar_color' => $navbar]);
            }

            $sidebar = $request->input('sidebar');
            if ($sidebar) {
                option(['sidebar_color' => $sidebar]);
            }
        }

        return view('admin.customize', [
            'colors' => [
                'navbar' => [
                    'primary', 'secondary', 'success', 'danger', 'indigo',
                    'purple', 'pink', 'teal', 'cyan', 'dark', 'gray',
                    'fuchsia', 'maroon', 'olive', 'navy',
                    'lime', 'light', 'warning', 'white', 'orange',
                ],
                'sidebar' => [
                    'primary', 'warning', 'info', 'danger', 'success', 'indigo',
                    'navy', 'purple', 'fuchsia', 'pink', 'maroon', 'orange',
                    'lime', 'teal', 'olive',
                ],
            ],
            'forms' => [
                'homepage' => $homepage,
                'custom_js_css' => $customJsCss,
            ],
            'extra' => [
                'navbar' => option('navbar_color'),
                'sidebar' => option('sidebar_color'),
            ],
        ]);
    }

    public function score()
    {
        $rate = Option::form('rate', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('score_per_storage')->text('score_per_storage')->addon();

            $form->group('private_score_per_storage')
                ->text('private_score_per_storage')->addon()->hint();

            $form->group('score_per_closet_item')
                ->text('score_per_closet_item')->addon();

            $form->checkbox('return_score')->label();

            $form->group('score_per_player')->text('score_per_player')->addon();

            $form->text('user_initial_score');
        })->handle();

        $report = Option::form('report', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('reporter_score_modification')->description();

            $form->text('reporter_reward_score');
        })->handle();

        $sign = Option::form('sign', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('sign_score')
                ->text('sign_score_from')->addon(trans('options.sign.sign_score.addon1'))
                ->text('sign_score_to')->addon(trans('options.sign.sign_score.addon2'));

            $form->group('sign_gap_time')->text('sign_gap_time')->addon();

            $form->checkbox('sign_after_zero')->label()->hint();
        })->after(function () {
            $sign_score = request('sign_score_from').','.request('sign_score_to');
            Option::set('sign_score', $sign_score);
        })->with([
            'sign_score_from' => @explode(',', option('sign_score'))[0],
            'sign_score_to' => @explode(',', option('sign_score'))[1],
        ])->handle();

        $sharing = Option::form('sharing', OptionForm::AUTO_DETECT, function ($form) {
            $form->group('score_award_per_texture')
                ->text('score_award_per_texture')
                ->addon(trans('general.user.score'));
            $form->checkbox('take_back_scores_after_deletion')->label();
            $form->group('score_award_per_like')
                ->text('score_award_per_like')
                ->addon(trans('general.user.score'));
        })->handle();

        return view('admin.score', ['forms' => compact('rate', 'report', 'sign', 'sharing')]);
    }

    public function options()
    {
        $general = Option::form('general', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('site_name');
            $form->text('site_description')->description();

            $form->text('site_url')
                ->hint()
                ->format(function ($url) {
                    if (Str::endsWith($url, '/')) {
                        $url = substr($url, 0, -1);
                    }

                    if (Str::endsWith($url, '/index.php')) {
                        $url = substr($url, 0, -10);
                    }

                    return $url;
                });

            $form->checkbox('register_with_player_name')->label();
            $form->checkbox('require_verification')->label();

            $form->text('regs_per_ip');

            $form->group('max_upload_file_size')
                    ->text('max_upload_file_size')->addon('KB')
                    ->hint(trans('options.general.max_upload_file_size.hint', ['size' => ini_get('upload_max_filesize')]));

            $form->select('player_name_rule')
                    ->option('official', trans('options.general.player_name_rule.official'))
                    ->option('cjk', trans('options.general.player_name_rule.cjk'))
                    ->option('utf8', trans('options.general.player_name_rule.utf8'))
                    ->option('custom', trans('options.general.player_name_rule.custom'));

            $form->text('custom_player_name_regexp')->hint()->placeholder();

            $form->group('player_name_length')
                ->text('player_name_length_min')
                ->addon('~')
                ->text('player_name_length_max')
                ->addon(trans('options.general.player_name_length.suffix'));

            $form->checkbox('auto_del_invalid_texture')->label()->hint();

            $form->checkbox('allow_downloading_texture')->label();

            $form->select('status_code_for_private')
                ->option('403', '403 Forbidden')
                ->option('404', '404 Not Found');

            $form->text('texture_name_regexp')->hint()->placeholder();

            $form->textarea('content_policy')->rows(3)->description();
        })->handle(function () {
            Option::set('site_name_'.config('app.locale'), request('site_name'));
            Option::set('site_description_'.config('app.locale'), request('site_description'));
            Option::set('content_policy_'.config('app.locale'), request('content_policy'));
        });

        $announ = Option::form('announ', OptionForm::AUTO_DETECT, function ($form) {
            $form->textarea('announcement')->rows(10)->description();
        })->renderWithoutTable()->handle(function () {
            Option::set('announcement_'.config('app.locale'), request('announcement'));
        });

        $meta = Option::form('meta', OptionForm::AUTO_DETECT, function ($form) {
            $form->text('meta_keywords')->hint();
            $form->text('meta_description')->hint();
            $form->textarea('meta_extras')->rows(6);
        })->handle();

        $recaptcha = Option::form('recaptcha', 'reCAPTCHA', function ($form) {
            $form->text('recaptcha_sitekey', 'sitekey');
            $form->text('recaptcha_secretkey', 'secretkey');
            $form->checkbox('recaptcha_invisible')->label();
        })->handle();

        return view('admin.options')
            ->with('forms', compact('general', 'announ', 'meta', 'recaptcha'));
    }

    public function resource(Request $request)
    {
        $resources = Option::form('resources', OptionForm::AUTO_DETECT, function ($form) {
            $form->checkbox('force_ssl')->label()->hint();
            $form->checkbox('auto_detect_asset_url')->label()->description();

            $form->text('cache_expire_time')->hint(OptionForm::AUTO_DETECT);
            $form->text('cdn_address')
                ->hint(OptionForm::AUTO_DETECT)
                ->description(OptionForm::AUTO_DETECT);
        })
            ->type('primary')
            ->hint(OptionForm::AUTO_DETECT)
            ->after(function () {
                $cdnAddress = request('cdn_address');
                if ($cdnAddress == null) {
                    $cdnAddress = '';
                }
                if (Str::endsWith($cdnAddress, '/')) {
                    $cdnAddress = substr($cdnAddress, 0, -1);
                }
                Option::set('cdn_address', $cdnAddress);
            })
            ->handle();

        $cache = Option::form('cache', OptionForm::AUTO_DETECT, function ($form) {
            $form->checkbox('enable_avatar_cache')->label();
            $form->checkbox('enable_preview_cache')->label();
        })
            ->type('warning')
            ->addButton([
                'text' => trans('options.cache.clear'),
                'type' => 'a',
                'class' => 'float-right',
                'style' => 'warning',
                'href' => '?clear-cache',
            ])
            ->addMessage(trans('options.cache.driver', ['driver' => config('cache.default')]), 'info');

        if ($request->has('clear-cache')) {
            Cache::flush();
            $cache->addMessage(trans('options.cache.cleared'), 'success');
        }
        $cache->handle();

        return view('admin.resource')->with('forms', compact('resources', 'cache'));
    }
}
