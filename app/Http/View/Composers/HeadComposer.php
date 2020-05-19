<?php

namespace App\Http\View\Composers;

use App\Services\Webpack;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
            'primary' => '#007bff',
            'secondary' => '#6c757d',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'navy' => '#001f3f',
            'olive' => '#3d9970',
            'lime' => '#01ff70',
            'fuchsia' => '#f012be',
            'maroon' => '#d81b60',
            'indigo' => '#6610f2',
            'purple' => '#6f42c1',
            'pink' => '#e83e8c',
            'orange' => '#fd7e14',
            'teal' => '#20c997',
            'cyan' => '#17a2b8',
            'gray' => '#6c757d',
        ];
        $view->with('theme_color', Arr::get($colors, option('navbar_color')));
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
        $links = [];
        $links[] = [
            'rel' => 'stylesheet',
            'href' => 'https://cdn.jsdelivr.net/npm/@blessing-skin/admin-lte@3.0.4/dist/admin-lte.min.css',
            'integrity' => 'sha256-nr8InsK/i0Skb3n3yHCVwEnsmblkae4Rs9aFJ4/JTWE=',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'preload',
            'as' => 'font',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/webfonts/fa-solid-900.woff2',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'preload',
            'as' => 'font',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/webfonts/fa-regular-400.woff2',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'stylesheet',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/all.min.css',
            'integrity' => 'sha256-h20CPZ0QyXlBuAw7A+KluUYx/3pK+c7lYEpqLTlxjYQ=',
            'crossorigin' => 'anonymous',
        ];
        $links[] = ['rel' => 'stylesheet', 'href' => $this->webpack->url('style.css')];
        $view->with('links', $links);
        $view->with('inline_css', option('custom_css'));
    }

    public function addExtra(View $view)
    {
        $content = [];
        $this->dispatcher->dispatch(new \App\Events\RenderingHeader($content));
        $view->with('extra_head', $content);
    }
}
