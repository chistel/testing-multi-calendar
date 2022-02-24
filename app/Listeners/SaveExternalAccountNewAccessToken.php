<?php

namespace App\Listeners;

use App\Events\ExternalAccountAccessTokenWasRefreshed;
use Illuminate\Support\Arr;

class SaveExternalAccountNewAccessToken
{
    /**
     * @param ExternalAccountAccessTokenWasRefreshed $accessTokenRefresh
     */
    public function handle(ExternalAccountAccessTokenWasRefreshed $accessTokenRefresh): void
    {
        if (Arr::has($accessTokenRefresh->newToken, 'refresh_token')) {
            $accessTokenRefresh->connectable->refresh_token = $accessTokenRefresh->newToken['refresh_token'];
        }
        if (Arr::has($accessTokenRefresh->newToken, 'expires_in')) {
            $accessTokenRefresh->connectable->expires_at = now()->addSeconds($accessTokenRefresh->newToken['expires_in']);
        }
        $accessTokenRefresh->connectable->token = Arr::get($accessTokenRefresh->newToken, 'access_token');
        $accessTokenRefresh->connectable->save();
    }
}
