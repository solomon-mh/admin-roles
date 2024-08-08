@extends('layouts.app')

@section('head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Task Adder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
    @if (session()->has('delete-msg'))
        <p>{{ session('message') }}</p>
    @endif
    <div class="container mx-auto mt-5">
        {{-- For Search --}}
        <div class="flex flex-wrap mb-6">
            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                <div class="flex">
                    <input type="text" id="searchInput"
                        class="flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Search schedules">

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
                <h2 id="singleScheduleTitle" class="text-xl font-semibold mb-4">Schedule Title</h2>
                <p id="scheduleStartDate" class="text-gray-700">Schedule Start Date</p>
                <p id="scheduleEndDate" class="text-gray-700">Schedule End Date</p>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendarEl = document.getElementById('calendar');
        var schedules = [];
        var calendar = new FullCalendar.Calendar(calendarEl, {
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
                    url: '/schedule/check',
                    method: 'GET',
                    data: {
                        date: clickedDate
                    },
                    success: function(response) {
                        if (response.hasSchedule) {
                            openScheduleDetailsModal(response.schedule);
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
            // Deleting The Schedule
            eventContent: function(info) {
                var scheduleTitle = info.event.title;
                var scheduleDesc = info.event.extendedProps.description;
                var scheduleElement = document.createElement('div');

                scheduleElement.innerHTML = `
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
                                ${scheduleTitle}
                            </span>
                    </div>
                `;

                scheduleElement.querySelector('#deleteBtn').addEventListener('click', function() {
                    var scheduleId = info.event.id;
                    if (confirm("Are you sure you want to delete this schedule? " + scheduleId)) {
                        $.ajax({
                            method: 'get',
                            url: '/schedule/delete/' + scheduleId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                calendar
                            .refetchEvents(); // Refresh schedules after deletion
                            },
                            error: function(error) {
                                console.error('Error deleting schedule:', error);
                            }
                        });
                    }
                });
                return {
                    domNodes: [scheduleElement]
                };
            },

            // Drag And Drop
            eventDrop: function(info) {
                var scheduleId = info.event.id;
                var newStartDate = info.event.start;
                var newEndDate = info.event.end || newStartDate;
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null;
                }
                var newStartDateUTC = newStartDate.toISOString().slice(0, 10);
                $.ajax({
                    method: 'post',
                    url: `/schedule/${scheduleId}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        '_token': "{{ csrf_token() }}",
                        start_date: newStartDateUTC,
                        end_date: newEndDateUTC,
                    },
                    success: function() {
                        console.log('Schedule moved successfully.');
                    },
                    error: function(error) {
                        console.error('Error moving schedule:', error);
                    }
                });
            },

            // Schedule Resizing
            eventResize: function(info) {
                var scheduleId = info.event.id;
                var newEndDate = info.event.end;
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null;
                }
                $.ajax({
                    method: 'post',
                    url: `/schedule/${scheduleId}/resize`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        end_date: newEndDateUTC
                    },
                    success: function() {
                        console.log('Schedule resized successfully.');
                    },
                    error: function(error) {
                        console.error('Error resizing schedule:', error);
                    }
                });
            },
        });

        calendar.render();

        document.getElementById('searchButton').addEventListener('click', function() {
            var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
            filterAndDisplaySchedules(searchKeywords);
        });
        document.getElementById('searchInput').addEventListener('keydown', function(event) {
            if (event.code == 'Enter') {
                var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
                filterAndDisplaySchedules(searchKeywords);
            }
        });

        function filterAndDisplaySchedules(keywords) {
            var schedules = calendar.getEvents();
            schedules.forEach(function(schedule) {
                var title = schedule.title.toLowerCase();
                var description = schedule.extendedProps.description ? schedule.extendedProps.description
                    .toLowerCase() : '';
                if (title.includes(keywords) || description.includes(keywords)) {
                    schedule.setProp('display', 'auto');
                } else {
                    schedule.setProp('display', 'none');
                }
            });
        }

        document.getElementById('exportButton').addEventListener('click', function() {
            exportCalendarToExcel();
        });

        function exportCalendarToExcel() {
            var schedules = calendar.getEvents();
            var data = [
                ['Title', 'Start Date', 'End Date', 'Description']
            ];
            schedules.forEach(function(schedule) {
                data.push([
                    schedule.title,
                    schedule.start.toISOString().slice(0, 10),
                    schedule.end ? schedule.end.toISOString().slice(0, 10) : '',
                    schedule.extendedProps.description || ''
                ]);
            });

            var ws = XLSX.utils.aoa_to_sheet(data);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Schedules');
            XLSX.writeFile(wb, 'schedules.xlsx');
        }

        function openAddScheduleModal(date) {
            // logic to open the add schedule modal
        }

        function openScheduleDetailsModal(schedule) {
            // Populate the modal with schedule details
            document.getElementById('singleScheduleTitle').innerText = schedule.title;
            document.getElementById('scheduleStartDate').innerText = `Start Date: ${schedule.start_date}`;
            document.getElementById('scheduleEndDate').innerText = `End Date: ${schedule.end_date}`;
            document.getElementById('scheduleDescription').innerText = `Description: ${schedule.description}`;

            // Display the modal
            var modal = document.getElementById('scheduleDetailModal');
            modal.classList.remove('opacity-0');
            modal.classList.remove('pointer-events-none');
        }

        document.getElementById('closeModalBtn').addEventListener('click', function() {
            var modal = document.getElementById('scheduleDetailModal');
            modal.classList.add('opacity-0');
            modal.classList.add('pointer-events-none');
        });
    </script>
@endsection
@extends('layouts.app')

@section('head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Task Adder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endsection

@section('content')
    @if (session()->has('delete-msg'))
        <p>{{ session('message') }}</p>
    @endif
    <div class="container mx-auto mt-5">
        {{-- For Search --}}
        <div class="flex flex-wrap mb-6">
            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                <div class="flex">
                    <input type="text" id="searchInput"
                        class="flex-1 px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Search schedules">

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
                <h2 id="singleScheduleTitle" class="text-xl font-semibold mb-4">Schedule Title</h2>
                <p id="scheduleStartDate" class="text-gray-700">Schedule Start Date</p>
                <p id="scheduleEndDate" class="text-gray-700">Schedule End Date</p>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendarEl = document.getElementById('calendar');
        var schedules = [];
        var calendar = new FullCalendar.Calendar(calendarEl, {
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
                    url: '/schedule/check',
                    method: 'GET',
                    data: {
                        date: clickedDate
                    },
                    success: function(response) {
                        if (response.hasSchedule) {
                            openScheduleDetailsModal(response.schedule);
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
            // Deleting The Schedule
            eventContent: function(info) {
                var scheduleTitle = info.event.title;
                var scheduleDesc = info.event.extendedProps.description;
                var scheduleElement = document.createElement('div');

                scheduleElement.innerHTML = `
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
                                ${scheduleTitle}
                            </span>
                    </div>
                `;

                scheduleElement.querySelector('#deleteBtn').addEventListener('click', function() {
                    var scheduleId = info.event.id;
                    if (confirm("Are you sure you want to delete this schedule? " + scheduleId)) {
                        $.ajax({
                            method: 'get',
                            url: '/schedule/delete/' + scheduleId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                calendar
                            .refetchEvents(); // Refresh schedules after deletion
                            },
                            error: function(error) {
                                console.error('Error deleting schedule:', error);
                            }
                        });
                    }
                });
                return {
                    domNodes: [scheduleElement]
                };
            },

            // Drag And Drop
            eventDrop: function(info) {
                var scheduleId = info.event.id;
                var newStartDate = info.event.start;
                var newEndDate = info.event.end || newStartDate;
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null;
                }
                var newStartDateUTC = newStartDate.toISOString().slice(0, 10);
                $.ajax({
                    method: 'post',
                    url: `/schedule/${scheduleId}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        '_token': "{{ csrf_token() }}",
                        start_date: newStartDateUTC,
                        end_date: newEndDateUTC,
                    },
                    success: function() {
                        console.log('Schedule moved successfully.');
                    },
                    error: function(error) {
                        console.error('Error moving schedule:', error);
                    }
                });
            },

            // Schedule Resizing
            eventResize: function(info) {
                var scheduleId = info.event.id;
                var newEndDate = info.event.end;
                if (newEndDate) {
                    newEndDate.setUTCDate(newEndDate.getUTCDate());
                    var newEndDateUTC = newEndDate.toISOString().slice(0, 10);
                } else {
                    var newEndDateUTC = null;
                }
                $.ajax({
                    method: 'post',
                    url: `/schedule/${scheduleId}/resize`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        end_date: newEndDateUTC
                    },
                    success: function() {
                        console.log('Schedule resized successfully.');
                    },
                    error: function(error) {
                        console.error('Error resizing schedule:', error);
                    }
                });
            },
        });

        calendar.render();

        document.getElementById('searchButton').addEventListener('click', function() {
            var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
            filterAndDisplaySchedules(searchKeywords);
        });
        document.getElementById('searchInput').addEventListener('keydown', function(event) {
            if (event.code == 'Enter') {
                var searchKeywords = document.getElementById('searchInput').value.toLowerCase();
                filterAndDisplaySchedules(searchKeywords);
            }
        });

        function filterAndDisplaySchedules(keywords) {
            var schedules = calendar.getEvents();
            schedules.forEach(function(schedule) {
                var title = schedule.title.toLowerCase();
                var description = schedule.extendedProps.description ? schedule.extendedProps.description
                    .toLowerCase() : '';
                if (title.includes(keywords) || description.includes(keywords)) {
                    schedule.setProp('display', 'auto');
                } else {
                    schedule.setProp('display', 'none');
                }
            });
        }

        document.getElementById('exportButton').addEventListener('click', function() {
            exportCalendarToExcel();
        });

        function exportCalendarToExcel() {
            var schedules = calendar.getEvents();
            var data = [
                ['Title', 'Start Date', 'End Date', 'Description']
            ];
            schedules.forEach(function(schedule) {
                data.push([
                    schedule.title,
                    schedule.start.toISOString().slice(0, 10),
                    schedule.end ? schedule.end.toISOString().slice(0, 10) : '',
                    schedule.extendedProps.description || ''
                ]);
            });

            var ws = XLSX.utils.aoa_to_sheet(data);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Schedules');
            XLSX.writeFile(wb, 'schedules.xlsx');
        }

        function openAddScheduleModal(date) {
            // logic to open the add schedule modal
        }

        function openScheduleDetailsModal(schedule) {
            // Populate the modal with schedule details
            document.getElementById('singleScheduleTitle').innerText = schedule.title;
            document.getElementById('scheduleStartDate').innerText = `Start Date: ${schedule.start_date}`;
            document.getElementById('scheduleEndDate').innerText = `End Date: ${schedule.end_date}`;
            document.getElementById('scheduleDescription').innerText = `Description: ${schedule.description}`;

            // Display the modal
            var modal = document.getElementById('scheduleDetailModal');
            modal.classList.remove('opacity-0');
            modal.classList.remove('pointer-events-none');
        }

        document.getElementById('closeModalBtn').addEventListener('click', function() {
            var modal = document.getElementById('scheduleDetailModal');
            modal.classList.add('opacity-0');
            modal.classList.add('pointer-events-none');
        });
    </script>
@endsection
