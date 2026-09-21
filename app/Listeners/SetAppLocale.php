<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SetAppLocale
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($event)
    {
        /** @var User */
        $user = $event->user;

        if ($this->request->has('lang')) {
            // Never persist an unchecked value: a locale without a matching
            // resources/lang/<locale> directory keeps every later page render
            // of this account failing in filemtime(). See #684.
            $locale = $this->normalizeLocale($this->request->input('lang'));

            if ($locale !== null) {
                $user->locale = $locale;
                $user->save();
            }

            return;
        }

        $locale = $this->normalizeLocale($user->locale);
        if ($locale !== null) {
            app()->setLocale($locale);
        }
    }

    /**
     * Accept only locales declared in config/locales.php, resolving an alias
     * ("zh_HANS_CN" => "zh_CN") to the real key. Anything else yields null.
     */
    protected function normalizeLocale($locale): ?string
    {
        if (!is_string($locale) || $locale === '') {
            return null;
        }

        $available = config('locales', []);

        if (!array_key_exists($locale, $available)) {
            return null;
        }

        $info = $available[$locale];
        $alias = is_array($info) ? Arr::get($info, 'alias') : null;

        return is_string($alias) && array_key_exists($alias, $available) ? $alias : $locale;
    }
}
