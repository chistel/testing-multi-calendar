<?php

namespace App\Services;

use App\Events\ExternalAccountAccessTokenWasRefreshed;
use App\Models\ExternalAccount;
use Exception;
use Google_Client;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Google
 *
 * @package App\Services
 */
class Google extends AbstractExternalService
{
    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * Google constructor.
     */
    public function __construct()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $this->client = $client;
    }

    /**
     * @param $token
     * @return $this
     */
    public function connectUsing($token): Google
    {
        $this->client->setAccessToken($token);

        return $this;
    }

    /**
     * @param $connectable
     * @return array|null
     */
    public function refreshAccessToken($connectable)
    {
        if ($connectable->expires_at->isPast()) {
            $this->client->setAccessToken($connectable->token);
            $refreshTokenSaved = $connectable->refresh_token ?? $this->client->getRefreshToken();

            $accessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            event(new ExternalAccountAccessTokenWasRefreshed($connectable, $accessToken));

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
        $token = $token ?? $this->client->getAccessToken();

        return $this->client->revokeToken($token);
    }

    /**
     * @param $service
     * @return mixed
     */
    public function service($service)
    {
        $classname = "Google_Service_$service";

        return new $classname($this->client);
    }

    /**
     * @param $method
     * @param $args
     * @return false|mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->client, $method)) {
            throw new \RuntimeException("Call to undefined method '{$method}'");
        }

        return call_user_func_array([$this->client, $method], $args);
    }
}
