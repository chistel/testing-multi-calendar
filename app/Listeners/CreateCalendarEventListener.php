<?php

namespace App\Listeners;

use App\Events\ModelEventWasCreated;
use App\Services\Calendar\CalendarFactory;

class CreateCalendarEventListener
{
    public function __construct(protected CalendarFactory $calendarFactory)
    {

    }

    public function handle(ModelEventWasCreated $eventWasCreated)
    {
        $calendarServices = config('system.calendars');

        foreach ($calendarServices as $calendarServiceKey => $calendarService) {
            $provider = $calendarService['provider'];
            $this->processCalendarEvent($eventWasCreated->user, $calendarServiceKey, $provider,
                $eventWasCreated);
        }
    }

    /**
     * @param $user
     * @param $calendarServiceKey
     * @param $provider
     * @param $event
     */
    private function processCalendarEvent($user, $calendarServiceKey, $provider, $event)
    {
        $calendarServiceScopes = get_provider_scopes($calendarServiceKey, $provider);
        $userExternalServices = $user->externalAccounts()->where('provider_name',
            $provider)->get();

        $userExternalServices->filter(fn($service) => has_scopes($service->scopes,
            $calendarServiceScopes))->each(function ($service) use ($event) {
            $calendarService = $this->calendarFactory->create(ucfirst($service->provider_name), $service);
            $calendarService->create([
                'title' => $event->title,
                'body' => $event->body,
                'start_time' => $event->startTime,
                'end_time' => $event->endTime,
            ]);
        });
    }
}
