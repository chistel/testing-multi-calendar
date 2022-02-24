<?php

namespace App\Services;

use App\Models\ExternalAccount;
use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractExternalService
{
    abstract public function connectUsing($token);

    abstract public function refreshAccessToken($connectable);

    abstract public function revokeToken($token = null): bool;

    /**
     * @param $connectable
     * @return mixed
     * @throws Exception
     */
    public function connectWithSynchronizable($connectable)
    {
        $token = $this->refreshAccessToken($connectable);
        if (is_null($token)) {
            $token = $this->getTokenFromSynchronizable($connectable);
        }
        return $this->connectUsing($token);
    }

    /**
     * @param Model $connectable
     * @return mixed
     * @throws Exception
     */
    protected function getTokenFromSynchronizable(Model $connectable)
    {
        return match (true) {
            $connectable instanceof ExternalAccount => $connectable->token,
            default => throw new Exception("Invalid connectable"),
        };
    }
}
