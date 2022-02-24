<?php

namespace App\Services\Calendar\Handlers;

abstract class HandlerAbstract
{
    /** @var string */
    protected string $calendarId;

    public function __construct(protected $connectable)
    {

    }

    /**
     *
     * @param $response
     * @return mixed
     */
    abstract protected function mapEventResponse($response);

    public function getConnectable(): string
    {
        return $this->connectable;
    }

    public function getCalendarId(): string
    {
        return $this->calendarId;
    }

    /**
     * @param string $calendarId
     * @return $this
     */
    public function setCalendarId(string $calendarId): static
    {
        $this->calendarId = $calendarId;

        return $this;
    }
}
