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
                        class="btn btn bg-lime-300 px-3 py-2 rounded-lg rounded-l-none hover:bg-lime-500 btn-success">{{ __('Search') }}</button>
                </div>
            </div>
            <div class="w-full md:w-1/2 flex justify-end space-x-2">
                <button id="exportButton"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">{{ __('Export Calendar') }}</button>
                <button onclick="openAddScheduleModal()" id="openModal"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">
                    {{ __('Add Schedule') }}
                </button>

                <button onclick="openAddEventModal()"
                    class="btn bg-lime-300 px-3 py-2 rounded-lg hover:bg-lime-500 btn-success">
                    {{ __('Add Event') }}
                </button>

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
                <h2 id="singleScheduleTitle" class="text-xl font-semibold mb-4">Schedule Title</h2>
                <p id="scheduleStartDate" class="text-gray-700">Start Date</p>
                <p id="scheduleEndDate" class="text-gray-700">End Date</p>
                <p id="scheduleDescription" class="text-gray-600">Schedule Description</p>
            </div>
        </div>
    </div>
    {{-- Schedule Modal --}}
    <x-schedule-modal />
    <x-add-event-modal />

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
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            selectable: true,
            editable: true,
            headerToolbar: {
                left: 'prev,next today',
                right: 'title',
                // right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            initialView: 'dayGridMonth',
            timeZone: 'UTC',
            events: '/schedules',
            dateClick: function(info) {
                var clickedDate = info.dateStr;
                $.ajax({
                    url: '/schedules/check',
                    method: 'get',
                    data: {
                        date: clickedDate
                    },
                    success: function(response) {
                        if (response.hasEvent) {
                            openScheduleDetailsModal(response.event);
                        } else {
                            openAddScheduleModal(clickedDate);
                        }
                    },
                    error: function(error) {
                        console.error('Error checking date:', error);
                    }
                });
            },
            select: function(info) {},
            // Deleting The Event
            eventContent: function(info) {
                // console.log(info is ${info.event.title})
                var eventTitle = info.event.title;
                var eventDesc = info.event.extendedProps.description; // Access description
                var eventElement = document.createElement('div');

                eventElement.innerHTML = `
                    <div style="
                        display: flex;
                        flex-wrap:wrap;
                        align-items: center;
                        gap: 1px;
                        font-weight: bold;
                        backgorund-color:red;
                    ">
                        <div style="display: flex; justify-content: space-between;">
                            <span id="deleteBtn" style="
                                cursor: pointer;
                                font-weight: bold;
                                border-radius: 4px;
                                margin-right: 8px;
                            ">❌</span>
                            <span style="
                            font-weight: bold;
                            color: #000; // make the color to fit based on the bg-color?
                            ">
                            ${eventTitle}
                            </span>
                            </div>
                    </div>`;
                eventElement.querySelector('#deleteBtn').addEventListener('click', function() {
                    var eventId = info.event.id;
                    // alert(eventId)
                    if (confirm("Are you sure you want to delete this event? ")) {
                        $.ajax({
                            method: 'get',
                            url: `/schedule/delete/${eventId}`,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                calendar.refetchEvents();
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
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null
                }
                var newStartDateUTC = newStartDate.toISOString().slice(0, 10);
                var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                $.ajax({
                    method: 'post',
                    url: `/schedule/${eventId}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
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
                var eventId = info.event.id;
                var newEndDate = info.event.end;
                // Adjust the end date to be the end of the day
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null
                }
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
        document.getElementById('searchInput').addEventListener('keydown', function(event) {
            if (event.code == 'Enter') {
                var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
                filterAndDisplayEvents(searchKeywords);
            }
        });

        function filterAndDisplayEvents(searchKeywords) {
            $.ajax({
                method: 'GET',
                url: `/schedules/search?title=${searchKeywords}`,
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
            var events = calendar.getSchedules().map(function(event) {
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

        function openAddScheduleModal(clickedDate) {
            const modal = document.getElementById('scheduleModal');
            const startDateInput = document.getElementById('start');
            const endDateInput = document.getElementById('end');
            const dateToUse = clickedDate || new Date().toISOString().split('T')[0];

            startDateInput.value = dateToUse;
            endDateInput.value = dateToUse;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
        }

        function openScheduleDetailsModal(eventData) {
            const eventEndDate = new Date(eventData.end);
            eventEndDate.setDate(eventEndDate.getDate());
            const formattedEndDate = eventEndDate.toISOString().split('T')[0];

            // Set event details in the modal
            document.getElementById('singleScheduleTitle').textContent = `Title: ${eventData.title}`;
            document.getElementById('scheduleStartDate').textContent = `Start Date: ${eventData.start}`;
            document.getElementById('scheduleEndDate').textContent = `End Date: ${formattedEndDate}`;
            document.getElementById('scheduleDescription').textContent =
                `${eventData.description ? 'Description :' : ''}${eventData.description || ""}`

            // Open the modal
            const modal = document.getElementById('scheduleDetailModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            document.getElementById('scheduleDetailModalContent').style.backgroundColor = eventData.color;
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const closeModalBtn = document.getElementById('closeModalBtn');
            const closeAddScheduleBtn = document.getElementById('closeAddScheduleBtn');
            const scheduleDetailModal = document.getElementById('scheduleDetailModal');

            closeModalBtn.addEventListener('click', () => closeModal('scheduleDetailModal'));
            closeAddScheduleBtn.addEventListener('click', () => closeModal('scheduleModal'));

            scheduleDetailModal.addEventListener('click', (e) => {
                if (e.target === scheduleDetailModal) {
                    closeModal('scheduleDetailModal');
                }
            });
            // Handle the form submission in the Add Event modal
            // document.getElementById('addEventForm').addEventListener('submit', function(e) {
            //     e.preventDefault();

            //     var formData = new FormData(this);
            //     var eventTitle = formData.get('title');
            //     var eventDescription = formData.get('description');
            //     var eventDate = formData.get('event_date');

            //     $.ajax({
            //         method: 'POST',
            //         url: '/events/add',
            //         data: {
            //             title: eventTitle,
            //             description: eventDescription,
            //             event_date: eventDate,
            //         },
            //         success: function(response) {
            //             // Add the new event to the calendar
            //             calendar.addEvent({
            //                 id: response.id,
            //                 title: response.title,
            //                 start: response.event_date,
            //                 end: response.event_date,
            //                 description: response.description,
            //                 color: response.color // Optional: Set color if available
            //             });
            //             alert(response.title)

            //             // Close the modal
            //             closeModal('addEventModal');
            //         },
            //         error: function(error) {
            //             console.error('Error adding event:', error);
            //         }
            //     });
            // });
        });
    </script>
@endsection
