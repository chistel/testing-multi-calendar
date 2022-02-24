@extends('layouts.main')
@push('css')
    <link rel="stylesheet" href="{{asset('assets/vendor/fullcalendar/dist/fullcalendar.min.css')}}"/>
    <style type="text/css">
        .fc-header-toolbar {
            display: block;
        }
    </style>
@endpush
@section('content')
    <div class="row mt-8">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                Calender
                            </h3>
                        </div>
                    </div>
                </div>

                <div id="calendar" class="p-2"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="{{asset('assets/vendor/fullcalendar/dist/fullcalendar.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var calendar = $('#calendar').fullCalendar({
                defaultView: 'month',
                header: {
                    // left: 'prev,next today',
                    // center: 'title',
                    //right: 'month'
                },
                selectable: true,
                events: "{{ route('calender.json') }}",
                displayEventTime: false,
                timeFormat: 'h(:mm)',
                eventBackgroundColor: '#522',
                eventBorderColor: '#522',
                eventRender: function(event, element, view) {
                    if (event.allDay === 'true') {
                        event.allDay = true;
                    } else {
                        event.allDay = false;
                    }
                    if (event.description != null) {
                        //element.find('.fc-title').append("<br/>" + event.description);
                    }
                }
            });
        });

    </script>
@endpush
