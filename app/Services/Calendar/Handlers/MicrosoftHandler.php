<?php

namespace App\Services\Calendar\Handlers;

use App\Services\MicrosoftService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Microsoft\Graph\Model\Event;

class MicrosoftHandler extends HandlerAbstract
{
    protected $service;

    public function __construct(protected $connectable)
    {
        parent::__construct($connectable);

        $this->service = app(MicrosoftService::class)
            ->connectWithSynchronizable($this->connectable);

        $this->calendarId = 'default';
    }

    /**
     * @throws \Exception
     */
    public function list(
        CarbonInterface $startDateTime = null,
        CarbonInterface $endDateTime = null,
        array $queryParameters = []
    ) {
        $timeZone =  new \DateTimeZone(config('app.timezone'));
        $atomDateFormat = \DateTimeInterface::ATOM;

        if (is_null($startDateTime)) {
            $startDateTime = Carbon::now()->startOfDay();
        }
        if (is_null($endDateTime)) {
            $endDateTime = Carbon::now()->addYear()->endOfDay();
        }

        $queryParameters = array_merge($queryParameters, [
            'startDateTime' => $startDateTime->format($atomDateFormat),
            'endDateTime' => $endDateTime->format($atomDateFormat),
            // Only request the properties used by the app
            '$select' => 'subject,body,organizer,start,end',
            // Sort them by start time
            '$orderby' => 'start/dateTime',
            // Limit results to 25
            '$top' => 25
        ]);

        $getEventsUrl = '/me/calendarView?' . http_build_query($queryParameters);

       $events = $this->service->getGraph()->createRequest('GET', $getEventsUrl)
            ->setReturnType(Event::class)
            ->execute();

       return collect($events)->map(fn($event) => $this->mapEventResponse($event));

    }

    public function create(array $data = [])
    {
        $event = $this->prepEventData($data);
        $response = $this->service->getGraph()->createRequest('POST', '/me/events')
            ->attachBody($event)
            ->setReturnType(Event::class)
            ->execute();
        return $this->mapEventResponse($response);
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepEventData(array $data = [])
    {
        $timeZone = config('app.timezone');
        $parsedData['subject'] = $data['title'];

        if (array_key_exists('start_time', $data)) {
            $startDatetime = $data['start_time'];
            $parsedData['start'] = [
                'dateTime' => $startDatetime,
                'timeZone' => $timeZone
            ];
        }
        if (array_key_exists('end_time', $data)) {
            $endDatetime = $data['end_time'];
            $parsedData['end'] = [
                'dateTime' => $endDatetime,
                'timeZone' => $timeZone
            ];
        }

        $parsedData['body'] = [
            'content' => $data['body'] ?? '',
            'contentType' => 'text'
        ];

        return $parsedData;
    }

    protected function mapEventResponse($response)
    {
        return [
            'id' => $response->getId(),
            'subject'=> $response->getSubject(),
            'body'=> $response->getBody(),
            'start_time'=> $response->getStart()->getDateTime(),
            'end_time'=> $response->getEnd()->getDateTime(),
        ];
    }
}
