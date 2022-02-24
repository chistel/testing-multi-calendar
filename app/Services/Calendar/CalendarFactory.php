<?php

namespace App\Services\Calendar;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CalendarFactory
{
    public function create($class, Model $connectable)
    {
        $class = $this->getHandlerClassName($class);

        if (!class_exists($class)) {
            throw new RuntimeException("Class '$class' not found");
        }

        return new $class($connectable);
    }

    private function getHandlerClassName($shortName)
    {
        // If the class starts with \, assume it's a FQCN
        if (str_starts_with($shortName, '\\') || str_starts_with($shortName, 'App\\Services\\Calendar\\Handlers\\')) {
            return $shortName;
        }

        return '\\App\\Services\\Calendar\\Handlers\\'.$shortName.'Handler';
    }
}
