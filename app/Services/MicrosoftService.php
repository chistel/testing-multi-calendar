<?php

namespace App\Services;

use App\Events\ExternalAccountAccessTokenWasRefreshed;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Microsoft\Graph\Graph;

class MicrosoftService extends AbstractExternalService
{
    protected Graph $graph;

    /**
     * MicrosoftService constructor.
     */
    public function __construct()
    {
        $this->graph = new Graph();
    }

    /**
     * @return GenericProvider
     */
    public function getClient(): GenericProvider
    {
        $authorizeUrl = 'https://login.microsoftonline.com/common';

        return new GenericProvider([
            'clientId' => config('services.microsoft.client_id'),
            'clientSecret' => config('services.microsoft.client_secret'),
            'urlAuthorize' => $authorizeUrl . '/oauth2/v2.0/authorize',
            'urlAccessToken' => $authorizeUrl . '/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
        ]);
    }

    /**
     * @return Graph
     */
    public function getGraph(): Graph
    {
        return $this->graph;
    }

    /**
     * @param $token
     * @return $this
     */
    public function connectUsing($token): static
    {
        $this->graph->setAccessToken($token);

        return $this;
    }

    /**
     * @param $connectable
     * @return AccessToken|AccessTokenInterface|null
     * @throws IdentityProviderException
     */
    public function refreshAccessToken($connectable)
    {
        if ($connectable->expires_at->isPast()) {
            $refreshTokenSaved = $connectable->refresh_token;

            $accessToken = $this->getClient()->getAccessToken('refresh_token', [
                'refresh_token' => $refreshTokenSaved
            ]);
            event(new ExternalAccountAccessTokenWasRefreshed($connectable, [
                'expires_in' => $accessToken->getExpires(),
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken()
            ]));

            return $accessToken;
        }

        return null;
    }

    /**
     * @param null $token
     * @return bool
     */
    public function revokeToken($token = null): bool
    {

    }

}
