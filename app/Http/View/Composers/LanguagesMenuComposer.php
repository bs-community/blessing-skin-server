<?php

namespace App\Http\View\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class LanguagesMenuComposer
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view)
    {
        $query = $this->request->query();
        $path = $this->request->path();

        $langs = collect(config('locales'))
            ->reject(fn ($locale) => Arr::has($locale, 'alias'))
            ->map(function ($locale, $id) use ($query, $path) {
                $query = array_merge($query, ['lang' => $id]);
                $locale['url'] = url($path.'?'.http_build_query($query));

                return $locale;
            });

        $current = 'locales.'.app()->getLocale();
        $view->with([
            'current' => config($current.'.name'),
            'langs' => $langs,
        ]);
    }
}
