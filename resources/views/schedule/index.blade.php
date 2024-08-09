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
                    <input type="text" id="searchInput"
                        class="flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Search events">

                    <button id="searchButton"
                        class="btn btn btn bg-lime-300 px-3 py-2 rounded-lg rounded-l-none hover:bg-lime-500 btn-success">{{ __('Search') }}</button>
                </div>
            </div>
            <div class="w-full md:w-1/2 flex justify-end space-x-2">
                <button id="exportButton"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{ __('Export Calendar') }}</button>
                <button onclick="openAddScheduleModal()" id="openModal"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">
                    {{ __('Add Schedule') }}
                </button>

                <a href="{{ URL('add-schedule') }}"
                    class="btn btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{ __('Add Task') }}</a>
            </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-4">
            <div id="calendar" class="w-3/4 mx-auto h-screen"></div>
        </div>
        <div id="scheduleDetailModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 transition-opacity opacity-0 pointer-events-none">
            <div id="scheduleDetailModalContent"
                class="bg-white rounded-lg shadow-md max-w-lg w-full mx-4 p-6 max-h-[80vh] flex flex-col relative overflow-y-auto">
                <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
                <h2 id="singleScheduleTitle" class="text-xl font-semibold mb-4">Schudule Title</h2>
                <p id="scheduleStartDate" class="text-gray-700">Schedule Date</p>
                <p id="scheduleEndDate" class="text-gray-700">Schedule Date</p>
                <p id="scheduleDescription" class="text-gray-600">Schedule Description</p>
            </div>
        </div>
    </div>
    {{-- Schedule Modal --}}
    <x-schedule-modal />


    {{-- <script src="../../js/schedule-modal.js"></script> --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    {{-- CSRF Token Setup for AJAX --}}
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
            editable: true,
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
                var dateStr = info.dateStr;
                var events = calendar.getEvents();
                var hasEvent = events.some(event => {
                    var eventStart = FullCalendar.formatDate(event.start, {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                    return eventStart === dateStr;
                });
                alert(hasEvent)

                if (!hasEvent) {
                    openAddEventModal(dateStr);
                } else {
                    alert('There is already an event on ' + dateStr);
                }
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
                } else {
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
                } else {
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
            if (event.key == 'Enter') {
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

        function openAddEventModal(dateStr) {
            const modal = document.getElementById('scheduleModal');
            const startDateInput = document.getElementById('start');
            const endDateInput = document.getElementById('end');
            const dateToUse = clickedDate || new Date().toISOString().split('T')[0];

            startDateInput.value = dateToUse;
            endDateInput.value = dateToUse;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
        }

        function openEventDetailsModal(eventData) {
            const eventEndDateStr = eventData.end;
            const eventEndDate = new Date(eventEndDateStr);
            eventEndDate.setDate(eventEndDate.getDate());
            const formattedEndDate = eventEndDate.toISOString().split('T')[0];
            // Set event details in the modal
            document.getElementById('singleEventTitle').textContent = `Title: ${eventData.title}`;
            document.getElementById('eventStartDate').textContent = `Start Date: ${eventData.start}`;
            document.getElementById('eventEndDate').textContent = `End Date: ${formattedEndDate}`;
            document.getElementById('eventDescription').textContent =
                `${eventData.description ? 'Description :' : ''}  ${eventData.description || ""}`

            // Open the modal
            const modal = document.getElementById('eventModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            document.getElementById('eventModalContent').style.backgroundColor = eventData.color;
        }
        document.addEventListener('DOMContentLoaded', () => {
            const closeModalBtn = document.getElementById('closeModalBtn');
            const closeAddModalBtn = document.getElementById('closeAddModalBtn');
            const eventModal = document.getElementById('eventModal');

            // Function to close the modal
            function closeModal() {
                eventModal.classList.remove('opacity-100', 'pointer-events-auto');
                eventModal.classList.add('opacity-0', 'pointer-events-none');
                scheduleModal.classList.remove('opacity-100', 'pointer-events-auto');
                scheduleModal.classList.add('opacity-0', 'pointer-events-none');
            }

            // Close modal on button click
            closeModalBtn.addEventListener('click', closeModal);
            closeAddModalBtn.addEventListener('click', closeModal);

            // Close modal when clicking outside of the modal content
            eventModal.addEventListener('click', (e) => {
                if (e.target === eventModal) {
                    closeModal();
                }
            });
        });
    </script>
@endsection
