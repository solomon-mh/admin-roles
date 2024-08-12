<!-- Add Event Modal -->
<div id="addEventModalForm"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 transition-opacity opacity-0 pointer-events-none">
    <div id="addEventModal"
        class="bg-white rounded-lg shadow-md max-w-lg w-full mx-4 p-6 max-h-[80vh] flex flex-col relative overflow-y-auto">
        <button id="closeAddEventModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <h2 class="text-xl font-semibold mb-4">Add New Event</h2>
        <form action="{{ URL('/events/add') }}" id="addEventForm">
            @csrf
            <div class="mb-4">
                <label for="eventTitle" class="block text-gray-700 font-medium mb-2">Title</label>
                <input type="text" id="eventTitle" name="title" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="eventDescription" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea id="eventDescription" name="description" rows="4" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <div class="mb-4">
                <label for="eventDate" class="block text-gray-700 font-medium mb-2">Event Date</label>
                <input type="date" id="eventDate" name="event_date" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex justify-end gap-4">
                <button type="button" onclick="closeAddEventModal()"
                    class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Cancel</button>
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Add
                    Event</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    function openAddEventModal() {
        const modal = document.getElementById('addEventModalForm');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeAddEventModal() {
        const modal = document.getElementById('addEventModalForm');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    document.getElementById('closeAddEventModalBtn').addEventListener('click', closeAddEventModal);


    // ADD-EVENT

    document.getElementById('addEventForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var eventTitle = document.getElementById('eventTitle').value;
        var eventDescription = document.getElementById('eventDescription').value;
        var eventDate = document.getElementById('eventDate').value;
        // alert(`eventTitle: ${eventTitle} - eventDate: ${eventDate}`)
        $.ajax({
            method: 'post',
            url: '/events/add',
            data: {
                title: eventTitle,
                description: eventDescription,
                event_date: eventDate,
                '_token': "{{ csrf_token() }}",
            },
            success: function(response) {
                console.log(response)
                alert(response.title);
                if (typeof calendar !== 'undefined') {
                    console.log(calendar)
                    calendar.addEvent({
                        id: response.id,
                        title: response.title,
                        start: response.event_date,
                        end: response.event_date,
                        description: response.description
                    });
                    window.dispatchEvent(new CustomEvent('eventAdded', {
                        detail: response
                    }));
                    // console.log('Event added:', response);
                    window.calendar.refetchEvents();
                } else {
                    console.error('Calendar instance not found.');
                }
                closeAddEventModal();
            },
            error: function(error) {
                console.error('Error adding event:', error);
            }
        });

    });
</script>
