@extends('layouts.main')
@section('content')
    <div class="row mt-8">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                Calendar Services
                            </h3>
                        </div>
                    </div>
                </div>
                @foreach ($calendarProviders as $calendarProvider => $calendarServices)
                    <div class="d-flex align-items-center px-3 py-2 bg-gradient-gray">
                        <h3>
                            {{ ucfirst($calendarProvider) }}
                        </h3>
                    </div>
                    <hr class="py-1 my-1">
                    @foreach($calendarServices as $calendarServiceKey => $calendarService)
                        <div class="d-flex justify-content-between px-3 py-2">
                            <div>
                                <h4>
                                    {{ $calendarService['name'] }}
                                </h4>
                            </div>
                            <div>
                                @php
                                    $providerScopes = $userExternalProviders[$calendarProvider] ?? [];
                                    $calendarServiceScopes = get_provider_scopes($calendarServiceKey, $calendarProvider);
                                @endphp
                                @if (has_scopes($providerScopes,$calendarServiceScopes))
                                    <a class="btn btn-sm btn-danger"
                                       href=""
                                       onclick="event.preventDefault(); document.getElementById('calendar_service_{{ $calendarProvider }}_{{ $calendarServiceKey }}').submit();">
                                        <span>Disconnect</span>
                                    </a>
                                @else
                                    <a href="{{ route('external-account.add',['provider'=> $calendarProvider, 'flow_state' => 'calendar', 'flow_service' => $calendarServiceKey]) }}"
                                       class="btn btn-sm btn-primary">
                                        Connect
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if (has_scopes($providerScopes,$calendarServiceScopes))
                            <form action="{{ route('external-account.disconnect', ['provider'=> $calendarProvider]) }}" id="calendar_service_{{ $calendarProvider . '_' . $calendarServiceKey }}"
                                  method="POST"
                                  class="d-none">
                                @method('DELETE')
                                @csrf
                                <input type="hidden" name="flow_state" value="calendar">
                                <input type="hidden" name="flow_service" value="{{ $calendarServiceKey }}">
                            </form>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
@endsection
