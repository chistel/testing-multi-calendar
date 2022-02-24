<?php

return [

    'calendars' => [
        'google_calendar' =>[
            'name' => 'Google Calendar',
            'scopes' => 'https://www.googleapis.com/auth/calendar',
            'provider' => 'google'
        ],
        'outlook_calender'=>[
            'name' => 'Outlook',
            'scopes' => 'Calendars.ReadWrite',
            'provider' => 'microsoft'
        ],
    ],

];
