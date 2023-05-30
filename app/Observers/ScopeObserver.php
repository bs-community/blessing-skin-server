<?php

namespace App\Observers;

use App\Models\Scope;
use Illuminate\Support\Facades\Cache;

class ScopeObserver
{
    /**
     * Handle the Scope "saved" event.
     */
    public function saved(): void
    {
        $this->refreshCachedScopes();
    }

    /**
     * Handle the Scope "deleted" event.
     */
    public function deleted(): void
    {
        $this->refreshCachedScopes();
    }

    protected function refreshCachedScopes(): void
    {
        Cache::forget('scopes');
        Cache::rememberForever('scopes', function () {
            return Scope::pluck('description', 'name')->toArray();
        });
    }
}
