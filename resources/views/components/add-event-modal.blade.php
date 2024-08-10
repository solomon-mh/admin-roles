<!-- Add Task Modal -->
<div id="addEventModalForm"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 transition-opacity opacity-0 pointer-events-none">
    <div class="bg-white rounded-lg shadow-md max-w-lg w-full mx-4 p-6 flex flex-col relative">
        <button id="closeAddTaskModalBtn" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>
        </button>
        <h2 class="text-xl font-semibold mb-4">Add New Event</h2>
        <form id="addTaskForm">
            <div class="mb-4">
                <label for="taskTitle" class="block text-gray-700 font-medium mb-2">Title</label>
                <input type="text" id="taskTitle" name="title" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label for="taskDescription" class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea id="taskDescription" name="description" rows="4" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <div class="mb-4">
                <label for="taskDate" class="block text-gray-700 font-medium mb-2">Event Date</label>
                <input type="date" id="taskDate" name="event_date" required
                    class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex justify-end gap-4">
                <button type="button" onclick="closeAddTaskModal()"
                    class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Cancel</button>
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Add
                    Event</button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function openAddTaskModal() {
        const modal = document.getElementById('addTaskModal');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeAddTaskModal() {
        const modal = document.getElementById('addTaskModal');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
    }

    document.getElementById('closeAddTaskModalBtn').addEventListener('click', closeAddTaskModal);
    // ADD-EVENT
    document.getElementById('addEventModalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var eventTitle = document.getElementById('eventTitle').value;
        var eventDescription = document.getElementById('eventDescription').value;
        var eventStartDate = document.getElementById('eventStartDate').value;
        var eventEndDate = document.getElementById('eventEndDate').value;

        $.ajax({
            method: 'POST',
            url: '/schedules/add', // Adjust the URL to your route
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                title: eventTitle,
                description: eventDescription,
                start_date: eventStartDate,
                end_date: eventEndDate,
            },
            success: function(response) {
                calendar.refetchEvents(); // Refresh the calendar to show the new event
                closeModal('addEventModal');
            },
            error: function(error) {
                console.error('Error adding event:', error);
            }
        });
    });
</script>
