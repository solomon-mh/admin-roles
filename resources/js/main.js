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
                var clickedDate = info.dateStr;
                $.ajax({
                    url: '/schedule/check',
                    method: 'GET',
                    data: {
                        date: clickedDate
                    },
                    success: function(response) {
                        if (response.hasEvent) {
                            openEventDetailsModal(response.event);
                        } else {
                            openAddScheduleModal(clickedDate);
                        }
                    },
                    error: function(error) {
                        console.error('Error checking date:', error);
                    }
                });
            },
            select: function(info) {
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
            const modal = document.getElementById('schedule');
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        });
