function openAddEventModal(clickedDate) {
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
            eventEndDate.setDate(eventEndDate.getDate() - 1);
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