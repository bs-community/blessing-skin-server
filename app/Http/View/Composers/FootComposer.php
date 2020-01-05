<?php

namespace App\Http\View\Composers;

use App\Models\User;
use App\Services\Translations\JavaScript;
use App\Services\Webpack;
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
        $scripts[] = $this->javascript->generate($locale);
        if ($pluginI18n = $this->javascript->plugin($locale)) {
            $scripts[] = $pluginI18n;
        }
        if (Str::startsWith(config('app.asset.env'), 'dev')) {
            $scripts[] = $this->webpack->url('style.js');
        }
        $scripts[] = $this->webpack->url('app.js');

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
