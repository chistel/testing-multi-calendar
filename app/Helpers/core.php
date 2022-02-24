<?php

if (!function_exists('calender_providers')) {
    function calendar_providers(): array
    {
        $items = config('system.calendars');
        $result = [];

        foreach ($items as $key => $value) {
            if (array_key_exists('provider', $value)) {
                $result[$value['provider']][$key] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('get_provider_scopes')) {
    function get_provider_scopes($flowService, $provider): array
    {
        $scopes = [];
        $calendarProviders = calendar_providers();
        if (isset($calendarProviders[$provider][$flowService]['scopes'])) {
            $scopes = explode(',', $calendarProviders[$provider][$flowService]['scopes']);
        }

        return $scopes;
    }
}

if (!function_exists('has_scopes')) {
    function has_scopes($dbScopes, $calendarScopes): bool
    {
        if (is_json($dbScopes)) {
            $dbScopes = json_decode($dbScopes);
        }
        return collect($dbScopes)->contains(fn($value, $key) => in_array($value, $calendarScopes, true));
    }
}

if (!function_exists('is_json')) {
    function is_json($string): bool
    {
        return is_string($string) && is_array(json_decode($string, true, 512,
                JSON_THROW_ON_ERROR)) && (json_last_error() === JSON_ERROR_NONE);
    }
}
