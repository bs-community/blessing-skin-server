<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\Webpack;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Events\Dispatcher;

class HeadComposer
{
    /** @var Webpack */
    protected $webpack;

    /** @var Dispatcher */
    protected $dispatcher;

    public function __construct(Webpack $webpack, Dispatcher $dispatcher)
    {
        $this->webpack = $webpack;
        $this->dispatcher = $dispatcher;
    }

    public function compose(View $view)
    {
        $this->addFavicon($view);
        $this->applyThemeColor($view);
        $this->seo($view);
        $this->injectStyles($view);
        $this->addExtra($view);
    }

    public function addFavicon(View $view)
    {
        $url = option('favicon_url', config('options.favicon_url'));
        $url = Str::startsWith($url, 'http') ? $url : url($url);
        $view->with('favicon', $url);
    }

    public function applyThemeColor(View $view)
    {
        $colors = [
            'blue' => '#3c8dbc',
            'yellow' => '#f39c12',
            'green' => '#00a65a',
            'purple' => '#605ca8',
            'red' => '#dd4b39',
            'black' => '#ffffff',
        ];
        preg_match('/skin-(\w+)?(?:-light)?/', option('color_scheme'), $matches);
        $view->with('theme_color', Arr::get($colors, $matches[1]));
    }

    public function seo(View $view)
    {
        $view->with('seo', [
            'keywords' => option('meta_keywords'),
            'description' => option('meta_description'),
            'extra' => option('meta_extras'),
        ]);
    }

    public function injectStyles(View $view)
    {
        $view->with('styles', [
            $this->webpack->url('style.css'),
            $this->webpack->url('skins/'.option('color_scheme').'.min.css'),
        ]);
        $view->with('inline_css', option('custom_css'));
    }

    public function addExtra(View $view)
    {
        $content = [];
        $this->dispatcher->dispatch(new \App\Events\RenderingHeader($content));
        $view->with('extra_head', $content);
    }
}
