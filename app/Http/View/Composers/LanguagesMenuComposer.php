<?php

namespace App\Http\View\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class LanguagesMenuComposer
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view)
    {
        $query = $this->request->query();
        $url = $this->request->url();

        $langs = collect(config('locales'))
            ->reject(function ($locale) {
                return Arr::has($locale, 'alias');
            })
            ->map(function ($locale, $id) use ($query, $url) {
                $query = array_merge($query, ['lang' => $id]);
                $locale['url'] = $url.'?'.http_build_query($query);

                return $locale;
            });

        $view->with([
            'current' => config('locales.'.app()->getLocale().'.short_name'),
            'langs' => $langs,
        ]);
    }
}
