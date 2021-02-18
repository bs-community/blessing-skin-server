<?php

namespace App\Http\View\Composers;

use App\Services\Translations\JavaScript;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FootComposer
{
    protected Request $request;

    protected JavaScript $javascript;

    protected Dispatcher $dispatcher;

    protected Filter $filter;

    public function __construct(
        Request $request,
        JavaScript $javascript,
        Dispatcher $dispatcher,
        Filter $filter
    ) {
        $this->request = $request;
        $this->javascript = $javascript;
        $this->dispatcher = $dispatcher;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        $this->injectJavaScript($view);
        $this->addExtra($view);
    }

    public function injectJavaScript(View $view)
    {
        $scripts = [];
        $scripts = $this->filter->apply('scripts', $scripts);

        $view->with([
            'i18n' => $this->javascript->generate(app()->getLocale()),
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
