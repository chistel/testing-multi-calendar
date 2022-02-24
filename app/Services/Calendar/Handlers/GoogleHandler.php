<?php

namespace App\Services\Calendar\Handlers;

use App\Services\Google;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;

class GoogleHandler extends HandlerAbstract
{
    /** @var Google_Service_Calendar */
    protected $calendarService;

    public function __construct(protected $connectable)
    {
        parent::__construct($connectable);

        $this->calendarService = app(Google::class)
            ->connectWithSynchronizable($this->connectable)
            ->service('Calendar');

        $this->calendarId = 'primary';
    }

    public function list(
        CarbonInterface $startDateTime = null,
        CarbonInterface $endDateTime = null,
        array $queryParameters = []
    ) {
        $parameters = [
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ];

        if (is_null($startDateTime)) {
            $startDateTime = Carbon::now()->startOfDay();
        }

        $parameters['timeMin'] = $startDateTime->format(\DateTimeInterface::RFC3339);

        if (is_null($endDateTime)) {
            $endDateTime = Carbon::now()->addYear()->endOfDay();
        }
        $parameters['timeMax'] = $endDateTime->format(\DateTimeInterface::RFC3339);

        $parameters = array_merge($parameters, $queryParameters);

        $eventList = $this
            ->calendarService
            ->events
            ->listEvents($this->calendarId, $parameters);

        return collect($eventList->getItems())->map(fn($event) => $this->mapEventResponse($event));
    }

    public function get(string $eventId): Google_Service_Calendar_Event
    {
        return $this->calendarService->events->get($this->calendarId, $eventId);
    }

    /**
     * @param array $data
     * @param array $optParams
     * @return array
     */
    public function create(array $data = [], $optParams = []): array
    {
        $event = $this->prepEventData($data);
        $createdEvent = $this->calendarService->events->insert($this->calendarId, $event, $optParams);

        return $this->mapEventResponse($createdEvent);
    }

    /**
     * @param array $data
     * @return array
     */
    public function update(array $data = []): array
    {
        $event = $this->prepEventData($data);

        $updatedEvent = $this->calendarService->events->update($this->calendarId, $event->id, $event);

        return $this->mapEventResponse($updatedEvent);
    }

    /**
     * @param $eventId
     */
    public function delete($eventId): void
    {
        $this->calendarService->events->delete($this->calendarId, $eventId);
    }

    /**
     * @param array $data
     * @return Google_Service_Calendar_Event
     */
    private function prepEventData(array $data = []): Google_Service_Calendar_Event
    {
        $eventDateTime = new Google_Service_Calendar_EventDateTime;

        $event = new \Google_Service_Calendar_Event();
        $event->setSummary($data['title']);
        if (array_key_exists('start_time', $data)) {
            $startDatetime = $data['start_time'];
            if (!$startDatetime instanceof Carbon) {
                $startDatetime = Carbon::createFromFormat('Y/m/d H:i', $startDatetime);
            }
            $eventDateTime->setDateTime($startDatetime->toAtomString());
            $event->setStart($eventDateTime);
        }
        if (array_key_exists('end_time', $data)) {
            $endDatetime = $data['end_time'];
            if (!$endDatetime instanceof Carbon) {
                $endDatetime = Carbon::createFromFormat('Y/m/d H:i', $endDatetime);
            }
            $eventDateTime->setDateTime($endDatetime->toAtomString());
            $event->setEnd($eventDateTime);
        }

        if (isset($data['body'])) {
            $event->setDescription($data['body']);
        }

        return $event;
    }

    /**
     * @param $response
     * @return array
     */
    protected function mapEventResponse($response): array
    {
        return [
            'id' => $response->id,
            'subject' => $response->getSummary(),
            'body' => $response->getDescription() ?? '',
            'start_time' => $response->getStart()->getDateTime(),
            'end_time' => $response->getEnd()->getDateTime(),
        ];
    }
}
