<?php

namespace App\Http\View\Composers;

use App\Services\Translations\JavaScript;
use App\Services\Webpack;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FootComposer
{
    /** @var Request */
    protected $request;

    /** @var Webpack */
    protected $webpack;

    /** @var JavaScript */
    protected $javascript;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Filter */
    protected $filter;

    public function __construct(
        Request $request,
        Webpack $webpack,
        JavaScript $javascript,
        Dispatcher $dispatcher
    ) {
        $this->request = $request;
        $this->webpack = $webpack;
        $this->javascript = $javascript;
        $this->dispatcher = $dispatcher;
    }

    public function compose(View $view)
    {
        $this->injectJavaScript($view);
        $this->addExtra($view);
    }

    public function injectJavaScript(View $view)
    {
        $scripts = [];

        $locale = app()->getLocale();
        $scripts[] = [
            'src' => $this->javascript->generate($locale),
        ];
        if ($pluginI18n = $this->javascript->plugin($locale)) {
            $scripts[] = [
                'src' => $pluginI18n,
            ];
        }
        if (Str::startsWith(config('app.asset.env'), 'dev')) {
            $scripts[] = [
                'src' => $this->webpack->url('style.js'),
                'async' => true,
                'defer' => true,
            ];
        } elseif (!$this->request->is('/')) {
            $scripts[] = [
                'src' => 'https://cdn.jsdelivr.net/npm/react@16.13.1/umd/react.production.min.js',
                'integrity' => 'sha256-yUhvEmYVhZ/GGshIQKArLvySDSh6cdmdcIx0spR3UP4=',
                'crossorigin' => 'anonymous',
            ];
            $scripts[] = [
                'src' => 'https://cdn.jsdelivr.net/npm/react-dom@16.13.1/umd/react-dom.production.min.js',
                'integrity' => 'sha256-vFt3l+illeNlwThbDUdoPTqF81M8WNSZZZt3HEjsbSU=',
                'crossorigin' => 'anonymous',
            ];
        }
        $scripts[] = [
            'src' => 'https://cdn.jsdelivr.net/npm/@blessing-skin/admin-lte@3.0/dist/admin-lte.min.js',
            'integrity' => 'sha256-lEpMZPtZ0RgHYZFOcJeX94WMegvHJ+beF5V7XtCcWxY=',
            'crossorigin' => 'anonymous',
        ];
        if ($this->request->is('/')) {
            $scripts[] = [
                'src' => $this->webpack->url('home.js'),
            ];
        } else {
            $scripts[] = [
                'src' => $this->webpack->url('app.js'),
            ];
        }

        $scripts = $this->filter->apply('scripts', $scripts);

        $view->with([
            'scripts' => $scripts,
            'inline_js' => option('custom_js'),
        ]);
    }

    public function addExtra(View $view)
    {
        $content = [];
        $this->dispatcher->dispatch(new \App\Events\RenderingFooter($content));
        $view->with('extra_foot', $content);
    }
}
