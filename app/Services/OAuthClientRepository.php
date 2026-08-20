<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

/**
 * Resolves a user's OAuth clients through the polymorphic "owner" relation.
 *
 * Passport 12 moved oauth_clients from a "user_id" column to a nullable
 * "owner" morph, but its own forUser() and findForUser() still query the
 * deprecated clients() relation, which reads the column that migration
 * dropped. The JSON endpoints backing the "user/oauth/manage" page go
 * through those two methods, so they are pointed at oauthApps() instead.
 * Everything else in ClientRepository already picks the right column.
 */
class OAuthClientRepository extends ClientRepository
{
    public function forUser(Authenticatable $user): Collection
    {
        return $user->oauthApps()->where('revoked', false)->orderBy('name')->get();
    }

    public function findForUser(string|int $clientId, Authenticatable $user): ?Client
    {
        return $user->oauthApps()->where('revoked', false)->find($clientId);
    }
}
