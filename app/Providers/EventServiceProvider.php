<?php

namespace App\Providers;

use App\Events\ExternalAccountAccessTokenWasRefreshed;
use App\Events\ModelEventWasCreated;
use App\Listeners\CreateCalendarEventListener;
use App\Listeners\SaveExternalAccountNewAccessToken;
use App\Services\SocialiteProviders\Microsoft\MicrosoftExtendSocialite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SocialiteWasCalled::class => [
            MicrosoftExtendSocialite::class . '@handle',
        ],
        ExternalAccountAccessTokenWasRefreshed::class => [
            SaveExternalAccountNewAccessToken::class
        ],
        ModelEventWasCreated::class => [
            CreateCalendarEventListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
