<?php

namespace App\Http\Controllers\Integration;

use App\Events\ModelEventWasCreated;
use App\Http\Controllers\Controller;
use App\Services\Calendar\CalendarFactory;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;


class CalendarController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request)
    {
        $calendarProviders =  calendar_providers();
        $calendarFactory = new CalendarFactory();

       //event( new ModelEventWasCreated($request->user(), 'A new title', 'next event', Carbon::now(), Carbon::now()->addHour()));
        //$ddd = $calendarFactory->create('Google', $request->user()->externalAccounts()->where('provider_name', 'google')->first());
        //dd($ddd->list());
        /*dd($ddd->create([
            'title' => 'test',
            'body' => 'Oya',
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHour(),
        ]));*/
        /*$userExternalProviders = $request->user()->externalAccounts()->pluck('provider_name')->toArray();*/
        $userExternalProviders = $request->user()->externalAccounts()->get()->mapWithKeys(function ($ex){
          return [$ex->provider_name => json_decode($ex->scopes)];
        })->all();
        return view('calendar-services', compact('calendarProviders', 'userExternalProviders'));
    }
}
