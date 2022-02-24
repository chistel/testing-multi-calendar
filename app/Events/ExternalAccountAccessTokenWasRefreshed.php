<?php

namespace App\Events;

use App\Models\ExternalAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExternalAccountAccessTokenWasRefreshed
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExternalAccount $connectable, public array $newToken = [])
    {

    }
}
