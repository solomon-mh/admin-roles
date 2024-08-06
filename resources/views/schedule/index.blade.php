@extends('layouts.app')

@section('head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Task Adder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container mx-auto mt-5">
        {{-- For Search --}}
        <div class="flex flex-wrap mb-6">
            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                <div class="flex">
                    <input type="text" id="searchInput" class="flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search events">

                    <button id="searchButton" class="btn btn btn bg-lime-300 px-3 py-2 rounded-lg rounded-l-none hover:bg-lime-500 btn-success">{{__('Search')}}</button>
                </div>
            </div>
            <div class="w-full md:w-1/2 flex justify-end space-x-2">
                <button id="exportButton" class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{__('Export Calendar')}}</button>
                <a href="{{ URL('add-schedule') }}" class="btn btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{__('Add Event')}}</a>
                <a href="{{ URL('add-schedule') }}" class="btn btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{__('Add Task')}}</a>
            </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-4">
            <div id="calendar" class="w-3/4 mx-auto h-screen"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendarEl = document.getElementById('calendar');
        var events = [];
        var calendar = new FullCalendar.Calendar(calendarEl, {
            selectable: true,
            editable:true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            initialView: 'dayGridMonth',
            timeZone: 'UTC',
            events: '/events',
            dateClick: function(info) {
                alert('clicked ' + info.dateStr);
                },
                select: function(info) {
                alert('selected ' + info.startStr + ' to ' + info.endStr);
                },
            // Deleting The Event
            eventContent: function(info) {  
                // console.log(`info is ${info.event.title}`)
                var eventTitle = info.event.title;
    var eventDesc = info.event.extendedProps.description; // Access description
    var eventElement = document.createElement('div');

    eventElement.innerHTML = `
                    <div style="
                        display: flex;
                        align-items:center;
                        gap:3px;
                        font-weight: bold;
                    ">
                    <div style="display:flex; justify-content:space-between;">
                        <span id="deleteBtn" style="
                            cursor: pointer;
                            font-weight: bold;
                            border-radius: 4px;
                            margin-right: 8px;
                        ">
                            ❌
                        </span> 
                    </div>
                            <span style="
                                font-weight: bold;
                                color: #000;
                            ">
                                ${eventTitle}
                            </span>
                    </div>
                `;

                eventElement.querySelector('#deleteBtn').addEventListener('click', function() {
                    var eventId = info.event.id;
                    if (confirm("Are you sure you want to delete this event? " + eventId)) {
                        $.ajax({
                            method: 'get',
                            url: '/schedule/delete/' + eventId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                alert('Event deleted successfully.');
                                calendar.refetchEvents(); // Refresh events after deletion
                            },
                            error: function(error) {
                                console.error('Error deleting event:', error);
                            }
                        });
                    }
                });
                return {
                    domNodes: [eventElement]
                };
            },

            // Drag And Drop
            eventDrop: function(info) {
                var eventId = info.event.id;
                var newStartDate = info.event.start;
                var newEndDate = info.event.end || newStartDate;
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate() - 1);
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                }
                else{
                    var newEndDateUTC = null
                }
                var newStartDateUTC = newStartDate.toISOString().slice(0, 10);
                var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                //  alert(newStartDateUTC)
                //  alert(`/schedule/${eventId}`)
                $.ajax({
                    method: 'post',
                    url: `/schedule/${eventId}`,
                    data: {
                        '_token': "{{ csrf_token() }}",
                        start_date: newStartDateUTC,
                        end_date: newEndDateUTC,
                    },
                    success: function() {
                        console.log('Event moved successfully.');
                    },
                    error: function(error) {
                        console.error('Error moving event:', error);
                    }
                });
            },

            // Event Resizing
            eventResize: function(info) {
                // alert('Resize')
                var eventId = info.event.id;
                var newEndDate = info.event.end;
                alert(newEndDate)
                // Adjust the end date to be the end of the day
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate() - 1);
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                }
                else{
                    var newEndDateUTC = null
                }
                alert(newEndDate)
                
                $.ajax({
                    method: 'post',
                    url: `/schedule/${eventId}/resize`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        end_date: newEndDateUTC
                    },
                    success: function() {
                        console.log('Event resized successfully.');
                    },
                    error: function(error) {
                        console.error('Error resizing event:', error);
                    }
                });
            },
        });

        calendar.render();

        document.getElementById('searchButton').addEventListener('click', function() {
            var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
            filterAndDisplayEvents(searchKeywords);
        });
        document.getElementById('searchInput').addEventListener('keydown', function() {
            if(event.key == 'Enter'){
                var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
                filterAndDisplayEvents(searchKeywords);
            }
        });

        function filterAndDisplayEvents(searchKeywords) {
            $.ajax({
                method: 'GET',
                url: `/events/search?title=${searchKeywords}`,
                success: function(response) {
                    calendar.removeAllEvents();
                    calendar.addEventSource(response);
                },
                error: function(error) {
                    console.error('Error searching events:', error);
                }
            });
        }

        // Exporting Function
        document.getElementById('exportButton').addEventListener('click', function() {
            var events = calendar.getEvents().map(function(event) {
                return {
                    title: event.title,
                    start: event.start ? event.start.toISOString() : null,
                    end: event.end ? event.end.toISOString() : null,
                    color: event.backgroundColor,
                };
            });

            var wb = XLSX.utils.book_new();

            var ws = XLSX.utils.json_to_sheet(events);

            XLSX.utils.book_append_sheet(wb, ws, 'Events');

            var arrayBuffer = XLSX.write(wb, {
                bookType: 'xlsx',
                type: 'array'
            });

            var blob = new Blob([arrayBuffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });

            var downloadLink = document.createElement('a');
            downloadLink.href = URL.createObjectURL(blob);
            downloadLink.download = 'events.xlsx';
            downloadLink.click();
        });
    </script>
@endsection
