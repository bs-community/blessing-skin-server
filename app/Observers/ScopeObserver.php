<?php

namespace App\Observers;

use App\Models\Scope;
use Illuminate\Support\Facades\Cache;

class ScopeObserver
{
    /**
     * Handle the Scope "saved" event.
     *
     * @return void
     */
    public function saved()
    {
        $this->refreshCachedScopes();
    }

    /**
     * Handle the Scope "deleted" event.
     *
     * @return void
     */
    public function deleted()
    {
        $this->refreshCachedScopes();
    }

    protected function refreshCachedScopes()
    {
        Cache::forget('scopes');
        Cache::rememberForever('scopes', function () {
            return Scope::pluck('description', 'name')->toArray();
        });
    }
}
