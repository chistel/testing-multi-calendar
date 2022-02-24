<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelEventWasCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $body,
        public $startTime,
        public $endTime
    ) {

    }
}
