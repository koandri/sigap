@extends('layouts.app')

@section('title', 'Maintenance Calendar')

@section('content')
<div class="page-header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Maintenance Management
                </div>
                <h2 class="page-title">
                    Maintenance Calendar
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/fullcalendar/index.global.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: '{{ route("maintenance.calendar.events") }}',
            method: 'GET'
        },
        eventClick: function(info) {
            if (info.event.extendedProps.url) {
                window.location.href = info.event.extendedProps.url;
            }
        },
        eventContent: function(arg) {
            return {
                html: '<div class="fc-event-title">' + arg.event.title + '</div>'
            };
        },
        height: 'auto',
        dayMaxEvents: true,
        moreLinkClick: 'popover'
    });
    calendar.render();
});
</script>
@endpush

@push('styles')
<link href="{{ asset('assets/tabler/libs/fullcalendar/index.global.css') }}" rel="stylesheet">
@endpush
