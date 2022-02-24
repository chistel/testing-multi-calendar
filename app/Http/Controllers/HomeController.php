<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard');
    }

    public function calender(Request $request)
    {
        return view('account.calender');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function calenderJson(Request $request): JsonResponse
    {
        //Carbon::parse()->toDa
        $user = $request->user();
        $startDate = $request->start;
        $endDate = $request->end;

        $events = $user->events()->whereDate('start_at', '>=', $request->start)
            ->whereDate('end_at', '<=', $request->end)->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'allDay' => (bool)$event->allday,
                    'start' => $event->start_at->format('Y-m-d H:i:s'),
                    //'end' => $event->end_at->toDateTimeString(),
                    'description' => $event->description,
                    'url' => $event->getMeta('event_html_link'),
                    //'backgroundColor' => $event->calendar->color
                ];
            })->toArray();

        return response()->json($events);

    }
}
